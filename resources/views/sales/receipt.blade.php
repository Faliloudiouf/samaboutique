<!doctype html>
<html><head><meta charset="utf-8"><title>Reçu {{ $sale->numero }}</title>
<style>
  body{font-family:'Courier New',monospace;font-size:11px;color:#000;margin:0;padding:10px}
  .center{text-align:center}
  .b{font-weight:bold}
  hr{border:0;border-top:1px dashed #000;margin:6px 0}
  table{width:100%;border-collapse:collapse;font-size:10px}
  td{padding:2px 0;vertical-align:top}
  .right{text-align:right}
  .big{font-size:14px}
</style>
</head><body>

<div class="center">
  <div class="b big">SAMABOUTIQUE</div>
  <div>Rufisque, Dakar — Sénégal</div>
  <div>Tél : 77 523 00 72</div>
</div>
<hr>
<div><span class="b">Reçu N°:</span> {{ $sale->numero }}</div>
<div><span class="b">Date:</span> {{ $sale->created_at->format('d/m/Y H:i') }}</div>
<div><span class="b">Vendeur:</span> {{ $sale->user->name }}</div>
@if($sale->client_nom)<div><span class="b">Client:</span> {{ $sale->client_nom }}</div>@endif
<hr>
<table>
  <thead>
    <tr style="border-bottom:1px solid #000"><td class="b">Article</td><td class="b right">Qté</td><td class="b right">P.U.</td><td class="b right">Total</td></tr>
  </thead>
  <tbody>
    @foreach($sale->items as $it)
    <tr>
      <td>{{ $it->produit_nom }}</td>
      <td class="right">{{ $it->quantite }}</td>
      <td class="right">{{ number_format($it->prix_unitaire,0,',',' ') }}</td>
      <td class="right">{{ number_format($it->sous_total,0,',',' ') }}</td>
    </tr>
    @endforeach
  </tbody>
</table>
<hr>
<table>
  <tr><td class="b big">TOTAL</td><td class="b big right">{{ number_format($sale->montant_total,0,',',' ') }} F</td></tr>
  <tr><td>Payé ({{ str_replace('_',' ',$sale->mode_paiement) }})</td><td class="right">{{ number_format($sale->montant_paye,0,',',' ') }} F</td></tr>
  @if($sale->montant_paye < $sale->montant_total)
  <tr><td class="b">RESTE DÛ</td><td class="b right">{{ number_format($sale->montant_total - $sale->montant_paye,0,',',' ') }} F</td></tr>
  @endif
</table>
<hr>
<div class="center">
  <div>Merci pour votre achat !</div>
  <div style="font-size:9px;margin-top:4px">Conservez ce reçu</div>
</div>
</body></html>
