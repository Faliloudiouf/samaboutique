<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Sale;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $today = today();

        $ventesJour = Sale::whereDate('created_at', $today)->sum('montant_total');
        $nbVentesJour = Sale::whereDate('created_at', $today)->count();
        $ventesMois = Sale::whereMonth('created_at', $today->month)
            ->whereYear('created_at', $today->year)
            ->sum('montant_total');

        $nbProduits = Product::where('actif', true)->count();
        $nbRupture = Product::where('actif', true)->where('stock', '<=', 0)->count();
        $nbAlerte = Product::where('actif', true)
            ->where('stock', '>', 0)
            ->whereColumn('stock', '<=', 'seuil_alerte')
            ->count();

        $produitsAlerte = Product::with('category')
            ->where('actif', true)
            ->where(function ($q) {
                $q->where('stock', '<=', 0)
                  ->orWhereColumn('stock', '<=', 'seuil_alerte');
            })
            ->orderBy('stock')
            ->limit(8)
            ->get();

        $dernieresVentes = Sale::with('user')->latest()->limit(6)->get();

        // Ventes des 7 derniers jours pour mini-graph
        $ventes7j = Sale::select(DB::raw('DATE(created_at) as jour'), DB::raw('SUM(montant_total) as total'))
            ->where('created_at', '>=', now()->subDays(6)->startOfDay())
            ->groupBy('jour')
            ->orderBy('jour')
            ->get();

        $topProduits = DB::table('sale_items')
            ->join('products', 'products.id', '=', 'sale_items.product_id')
            ->select('products.nom', DB::raw('SUM(sale_items.quantite) as qte'))
            ->groupBy('products.id', 'products.nom')
            ->orderByDesc('qte')
            ->limit(5)
            ->get();

        return view('dashboard', compact(
            'ventesJour', 'nbVentesJour', 'ventesMois',
            'nbProduits', 'nbRupture', 'nbAlerte',
            'produitsAlerte', 'dernieresVentes', 'ventes7j', 'topProduits'
        ));
    }
}
