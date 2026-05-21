<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SaleController extends Controller
{
    public function index(Request $request)
    {
        $period = $request->input('period', 'week');
        [$debut, $fin] = match ($period) {
            'day' => [now()->startOfDay(), now()->endOfDay()],
            'month' => [now()->startOfMonth(), now()->endOfMonth()],
            'year' => [now()->startOfYear(), now()->endOfYear()],
            default => [now()->startOfWeek(), now()->endOfWeek()],
        };

        $query = Sale::with('user','customer')->whereBetween('created_at', [$debut, $fin])->latest();

        $modeFilter = $request->input('mode');
        if ($modeFilter === 'mobile') {
            $query->whereIn('mode_paiement', ['wave','orange_money']);
        } elseif ($modeFilter && $modeFilter !== 'all') {
            if ($modeFilter === 'credit') $query->whereIn('statut', ['credit','partielle']);
            else $query->where('mode_paiement', $modeFilter);
        }

        if ($q = $request->input('q')) {
            $query->where(fn($w) => $w->where('numero','like',"%$q%")
                ->orWhere('client_nom','like',"%$q%"));
        }

        $sales = $query->paginate(20)->withQueryString();

        // Stats période
        $totalRange = Sale::whereBetween('created_at', [$debut, $fin]);
        $nbVentes = (clone $totalRange)->count();
        $caTotal = (float)(clone $totalRange)->sum('montant_total');
        $panierMoyen = $nbVentes > 0 ? $caTotal / $nbVentes : 0;

        $selected = $request->input('selected') ? Sale::with('items.product','user','customer')->find($request->input('selected')) : $sales->first();
        if ($selected && !$selected->relationLoaded('items')) {
            $selected->load('items.product','user','customer');
        }

        return view('sales.index', compact('sales','period','debut','fin','nbVentes','caTotal','panierMoyen','selected','modeFilter'));
    }

    public function cancel(Sale $sale)
    {
        if (auth()->user()->role !== 'gerant') abort(403);
        if ($sale->statut === 'annulee') return back()->with('err', 'Vente déjà annulée.');

        DB::transaction(function () use ($sale) {
            foreach ($sale->items as $it) {
                if ($it->product) $it->product->increment('stock', $it->quantite);
            }
            $sale->update(['statut' => 'annulee']);
        });
        return back()->with('ok', 'Vente annulée. Stock restitué.');
    }

    public function create()
    {
        $products = Product::with('category')
            ->where('actif', true)
            ->orderBy('nom')
            ->get();
        $categories = \App\Models\Category::orderBy('nom')->get();
        $customers = Customer::orderBy('nom')->get();
        return view('sales.create', compact('products', 'categories', 'customers'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'customer_id' => ['nullable', 'exists:customers,id'],
            'client_nom' => ['nullable', 'string', 'max:120'],
            'client_tel' => ['nullable', 'string', 'max:30'],
            'mode_paiement' => ['required', 'in:especes,wave,orange_money,carte,credit'],
            'montant_paye' => ['required', 'numeric', 'min:0'],
            'remise' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantite' => ['required', 'integer', 'min:1'],
        ]);

        // "credit" est un mode UI : on force paye=0 et on garde un mode réel pour la BD
        $isCredit = $data['mode_paiement'] === 'credit';
        if ($isCredit) {
            $data['mode_paiement'] = 'especes';
            $data['montant_paye'] = 0;
        }
        $remiseInput = (float) ($data['remise'] ?? 0);

        $sale = DB::transaction(function () use ($data, $request, $remiseInput) {
            $sousTotalBrut = 0;
            $lignes = [];

            foreach ($data['items'] as $row) {
                $product = Product::lockForUpdate()->findOrFail($row['product_id']);
                if ($product->stock < $row['quantite']) {
                    abort(422, "Stock insuffisant pour : {$product->nom} (dispo: {$product->stock})");
                }
                $sousTotal = (float)$product->prix_vente * (int)$row['quantite'];
                $sousTotalBrut += $sousTotal;
                $lignes[] = [
                    'product' => $product,
                    'quantite' => (int)$row['quantite'],
                    'sous_total' => $sousTotal,
                ];
            }

            $remise = max(0, min($sousTotalBrut, $remiseInput));
            $total = $sousTotalBrut - $remise;
            $paye = (float)$data['montant_paye'];
            $statut = $paye >= $total ? 'payee' : ($paye > 0 ? 'partielle' : 'credit');

            // Crédit ou partielle → exiger/créer un client
            $customerId = $data['customer_id'] ?? null;
            if ($statut !== 'payee' && !$customerId) {
                if (empty($data['client_nom'])) {
                    abort(422, 'Pour une vente à crédit, un nom de client est obligatoire.');
                }
                $customer = Customer::create([
                    'nom' => $data['client_nom'],
                    'telephone' => $data['client_tel'] ?? null,
                ]);
                $customerId = $customer->id;
            } elseif ($customerId && empty($data['client_nom'])) {
                $c = Customer::find($customerId);
                if ($c) { $data['client_nom'] = $c->nom; $data['client_tel'] = $c->telephone; }
            }

            $echeance = ($statut !== 'payee') ? now()->addDays(30)->toDateString() : null;

            $sale = Sale::create([
                'numero' => Sale::genererNumero(),
                'user_id' => $request->user()->id,
                'customer_id' => $customerId,
                'client_nom' => $data['client_nom'] ?? null,
                'client_tel' => $data['client_tel'] ?? null,
                'montant_total' => $total,
                'remise' => $remise,
                'montant_paye' => min($paye, $total),
                'mode_paiement' => $data['mode_paiement'],
                'statut' => $statut,
                'echeance' => $echeance,
                'notes' => $data['notes'] ?? null,
            ]);

            foreach ($lignes as $l) {
                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $l['product']->id,
                    'produit_nom' => $l['product']->nom,
                    'quantite' => $l['quantite'],
                    'prix_unitaire' => $l['product']->prix_vente,
                    'sous_total' => $l['sous_total'],
                ]);
                $l['product']->decrement('stock', $l['quantite']);
            }

            return $sale;
        });

        return redirect()->route('sales.show', $sale)->with('ok', 'Vente enregistrée : ' . $sale->numero);
    }

    public function show(Sale $sale)
    {
        $sale->load('items.product', 'user', 'customer');
        return view('sales.show', compact('sale'));
    }

    public function receipt(Sale $sale)
    {
        $sale->load('items', 'user');
        $pdf = Pdf::loadView('sales.receipt', compact('sale'))->setPaper('a5', 'portrait');
        return $pdf->stream("recu-{$sale->numero}.pdf");
    }
}
