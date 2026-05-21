<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        [$debut, $fin] = $this->range($request);

        $salesQ = Sale::whereBetween('created_at', [$debut, $fin]);

        $caTotal = (float)(clone $salesQ)->sum('montant_total');
        $caEncaisse = (float)(clone $salesQ)->sum('montant_paye');
        $nbVentes = (clone $salesQ)->count();
        $panierMoyen = $nbVentes > 0 ? $caTotal / $nbVentes : 0;

        $parJour = (clone $salesQ)
            ->select(DB::raw('DATE(created_at) as jour'), DB::raw('SUM(montant_total) as total'), DB::raw('COUNT(*) as n'))
            ->groupBy('jour')->orderBy('jour')->get();

        $parMode = (clone $salesQ)
            ->select('mode_paiement', DB::raw('SUM(montant_total) as total'), DB::raw('COUNT(*) as n'))
            ->groupBy('mode_paiement')->get();

        $parStatut = (clone $salesQ)
            ->select('statut', DB::raw('SUM(montant_total) as total'), DB::raw('COUNT(*) as n'))
            ->groupBy('statut')->get();

        $parVendeur = (clone $salesQ)
            ->select('user_id', DB::raw('SUM(montant_total) as total'), DB::raw('COUNT(*) as n'))
            ->groupBy('user_id')->with('user')->get();

        $topProduits = DB::table('sale_items')
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->join('products', 'products.id', '=', 'sale_items.product_id')
            ->whereBetween('sales.created_at', [$debut, $fin])
            ->select(
                'products.nom',
                'products.reference',
                DB::raw('SUM(sale_items.quantite) as qte'),
                DB::raw('SUM(sale_items.sous_total) as ca')
            )
            ->groupBy('products.id','products.nom','products.reference')
            ->orderByDesc('ca')->limit(15)->get();

        return view('reports.index', compact(
            'debut','fin','caTotal','caEncaisse','nbVentes','panierMoyen',
            'parJour','parMode','parStatut','parVendeur','topProduits'
        ));
    }

    public function exportCsv(Request $request): StreamedResponse
    {
        [$debut, $fin] = $this->range($request);
        $ventes = Sale::with('user','customer','items')->whereBetween('created_at', [$debut, $fin])->orderBy('created_at')->get();

        $filename = 'ventes_' . $debut->format('Ymd') . '_' . $fin->format('Ymd') . '.csv';

        return response()->stream(function () use ($ventes) {
            $h = fopen('php://output', 'w');
            fwrite($h, "\xEF\xBB\xBF"); // BOM UTF-8 pour Excel
            fputcsv($h, ['Numero','Date','Vendeur','Client','Mode','Total','Paye','Statut','NbArticles'], ';');
            foreach ($ventes as $v) {
                fputcsv($h, [
                    $v->numero,
                    $v->created_at->format('d/m/Y H:i'),
                    $v->user->name,
                    $v->client_nom ?: '',
                    $v->mode_paiement,
                    $v->montant_total,
                    $v->montant_paye,
                    $v->statut,
                    $v->items->count(),
                ], ';');
            }
            fclose($h);
        }, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    private function range(Request $request): array
    {
        $debut = $request->input('debut') ? Carbon::parse($request->input('debut'))->startOfDay() : now()->startOfMonth();
        $fin = $request->input('fin') ? Carbon::parse($request->input('fin'))->endOfDay() : now()->endOfDay();
        if ($fin->lt($debut)) [$debut, $fin] = [$fin->copy()->startOfDay(), $debut->copy()->endOfDay()];
        return [$debut, $fin];
    }
}
