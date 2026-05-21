<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseOrderController extends Controller
{
    public function index(Request $request)
    {
        $query = PurchaseOrder::with('supplier','user')->latest();
        if ($s = $request->input('statut')) $query->where('statut', $s);
        $orders = $query->paginate(15)->withQueryString();
        return view('purchase_orders.index', compact('orders'));
    }

    public function create()
    {
        $suppliers = Supplier::orderBy('nom')->get();
        $products = Product::orderBy('nom')->get(['id','nom','reference','prix_achat','stock']);
        if ($suppliers->isEmpty()) {
            return redirect()->route('suppliers.create')->with('err', 'Créez d\'abord un fournisseur.');
        }
        return view('purchase_orders.create', compact('suppliers','products'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'supplier_id' => ['required','exists:suppliers,id'],
            'date_commande' => ['required','date'],
            'notes' => ['nullable','string'],
            'items' => ['required','array','min:1'],
            'items.*.product_id' => ['required','exists:products,id'],
            'items.*.quantite' => ['required','integer','min:1'],
            'items.*.prix_unitaire' => ['required','numeric','min:0'],
        ]);

        $order = DB::transaction(function () use ($data, $request) {
            $total = 0;
            $order = PurchaseOrder::create([
                'numero' => PurchaseOrder::genererNumero(),
                'supplier_id' => $data['supplier_id'],
                'user_id' => $request->user()->id,
                'statut' => 'en_cours',
                'montant_total' => 0,
                'date_commande' => $data['date_commande'],
                'notes' => $data['notes'] ?? null,
            ]);
            foreach ($data['items'] as $row) {
                $st = (float)$row['prix_unitaire'] * (int)$row['quantite'];
                $total += $st;
                PurchaseOrderItem::create([
                    'purchase_order_id' => $order->id,
                    'product_id' => $row['product_id'],
                    'quantite' => $row['quantite'],
                    'prix_unitaire' => $row['prix_unitaire'],
                    'sous_total' => $st,
                ]);
            }
            $order->update(['montant_total' => $total]);
            return $order;
        });

        return redirect()->route('purchase-orders.show', $order)->with('ok', 'Commande créée : ' . $order->numero);
    }

    public function show(PurchaseOrder $purchase_order)
    {
        $purchase_order->load('supplier','user','items.product');
        return view('purchase_orders.show', ['order' => $purchase_order]);
    }

    public function receive(Request $request, PurchaseOrder $purchase_order)
    {
        if ($purchase_order->statut !== 'en_cours') {
            return back()->with('err', 'Cette commande ne peut plus être reçue.');
        }
        DB::transaction(function () use ($purchase_order) {
            foreach ($purchase_order->items as $it) {
                $it->product->increment('stock', $it->quantite);
                // Optionnel : maj prix achat
                $it->product->update(['prix_achat' => $it->prix_unitaire]);
            }
            $purchase_order->update(['statut' => 'recue', 'date_reception' => now()]);
        });
        return back()->with('ok', 'Livraison réceptionnée — stock mis à jour.');
    }

    public function cancel(PurchaseOrder $purchase_order)
    {
        if ($purchase_order->statut === 'recue') return back()->with('err', 'Commande déjà reçue.');
        $purchase_order->update(['statut' => 'annulee']);
        return back()->with('ok', 'Commande annulée.');
    }
}
