<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomerPayment;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $filter = $request->input('filter', 'all');
        $query = Customer::query()->with(['sales','payments'])->withCount('sales');

        if ($s = $request->input('q')) {
            $query->where(fn($q) => $q->where('nom','like',"%$s%")->orWhere('telephone','like',"%$s%"));
        }
        if ($filter === 'retard') {
            $query->whereHas('sales', fn($q) => $q->whereIn('statut',['credit','partielle'])
                ->whereNotNull('echeance')->whereDate('echeance','<',now()));
        } elseif ($filter === 'recent') {
            $query->whereHas('sales', fn($q) => $q->where('created_at','>=',now()->subDays(7)));
        }

        $customers = $query->orderBy('nom')->paginate(15)->withQueryString();
        $customers->getCollection()->transform(function ($c) {
            $c->solde = $c->soldeDu();
            return $c;
        });

        return view('customers.index', compact('customers','filter'));
    }

    public function create()
    {
        return view('customers.form', ['customer' => new Customer()]);
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $c = Customer::create($data);
        return redirect()->route('customers.show', $c)->with('ok', 'Client créé.');
    }

    public function show(Customer $customer)
    {
        $customer->load([
            'sales' => fn($q) => $q->latest(),
            'payments' => fn($q) => $q->latest()->with('user'),
        ]);
        $solde = $customer->soldeDu();
        return view('customers.show', compact('customer','solde'));
    }

    public function repayment(Customer $customer)
    {
        $customer->load(['sales' => fn($q) => $q->whereIn('statut',['credit','partielle'])->oldest(),
                         'payments' => fn($q) => $q->latest()->with('user')]);
        $solde = $customer->soldeDu();
        $totalInitial = (float) $customer->sales->sum('montant_total');
        $dejaPaye = $totalInitial - $solde;
        $progression = $totalInitial > 0 ? (int) round(($dejaPaye / $totalInitial) * 100) : 0;
        return view('customers.repayment', compact('customer','solde','totalInitial','dejaPaye','progression'));
    }

    public function edit(Customer $customer)
    {
        return view('customers.form', compact('customer'));
    }

    public function update(Request $request, Customer $customer)
    {
        $customer->update($this->validateData($request));
        return redirect()->route('customers.show', $customer)->with('ok', 'Client mis à jour.');
    }

    public function destroy(Customer $customer)
    {
        if ($customer->sales()->exists()) {
            return back()->with('err', 'Impossible : ce client a des ventes liées.');
        }
        $customer->delete();
        return redirect()->route('customers.index')->with('ok', 'Client supprimé.');
    }

    public function payment(Request $request, Customer $customer)
    {
        $data = $request->validate([
            'sale_id' => ['nullable', 'exists:sales,id'],
            'montant' => ['required', 'numeric', 'min:1'],
            'mode_paiement' => ['required', 'in:especes,wave,orange_money,carte'],
            'notes' => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($data, $customer, $request) {
            $montant = (float)$data['montant'];

            CustomerPayment::create([
                'customer_id' => $customer->id,
                'sale_id' => $data['sale_id'] ?? null,
                'user_id' => $request->user()->id,
                'montant' => $montant,
                'mode_paiement' => $data['mode_paiement'],
                'notes' => $data['notes'] ?? null,
            ]);

            // Si lié à une vente : mettre à jour montant_paye + statut
            if (!empty($data['sale_id'])) {
                $sale = Sale::lockForUpdate()->find($data['sale_id']);
                $nouveauPaye = min((float)$sale->montant_total, (float)$sale->montant_paye + $montant);
                $sale->montant_paye = $nouveauPaye;
                $sale->statut = $nouveauPaye >= (float)$sale->montant_total ? 'payee' : 'partielle';
                $sale->save();
            } else {
                // Remboursement libre : appliquer FIFO aux ventes non soldées
                $reste = $montant;
                $ventes = $customer->sales()->whereIn('statut', ['credit','partielle'])->oldest()->lockForUpdate()->get();
                foreach ($ventes as $s) {
                    if ($reste <= 0) break;
                    $du = max(0, (float)$s->montant_total - (float)$s->montant_paye);
                    $aImputer = min($du, $reste);
                    $s->montant_paye = (float)$s->montant_paye + $aImputer;
                    $s->statut = $s->montant_paye >= (float)$s->montant_total ? 'payee' : 'partielle';
                    $s->save();
                    $reste -= $aImputer;
                }
            }
        });

        return back()->with('ok', 'Paiement de ' . number_format((float)$data['montant'],0,',',' ') . ' FCFA enregistré.');
    }

    private function validateData(Request $r): array
    {
        return $r->validate([
            'nom' => ['required', 'string', 'max:120'],
            'telephone' => ['nullable', 'string', 'max:30'],
            'adresse' => ['nullable', 'string', 'max:200'],
            'notes' => ['nullable', 'string'],
        ]);
    }
}
