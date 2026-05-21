@extends('layouts.app')
@section('title','Commande '.$order->numero)
@section('content')

<div class="page-head">
  <div>
    <h1>Commande {{ $order->numero }}</h1>
    <div class="sub">{{ $order->date_commande->format('d/m/Y') }} · {{ $order->supplier->nom }} · créée par {{ $order->user->name }}</div>
  </div>
  <div class="d-flex gap-2">
    <a href="{{ route('purchase-orders.index') }}" class="btn btn-ghost"><i class="bi bi-arrow-left"></i> Retour</a>
    @if($order->statut==='en_cours')
      <form method="POST" action="{{ route('purchase-orders.cancel', $order) }}" onsubmit="return confirm('Annuler cette commande ?')">
        @csrf @method('PATCH')
        <button class="btn btn-danger-soft"><i class="bi bi-x-circle"></i> Annuler</button>
      </form>
      <form method="POST" action="{{ route('purchase-orders.receive', $order) }}" onsubmit="return confirm('Confirmer la réception ? Le stock sera mis à jour.')">
        @csrf @method('PATCH')
        <button class="btn btn-primary"><i class="bi bi-box-arrow-in-down"></i> Réceptionner</button>
      </form>
    @endif
  </div>
</div>

<div class="row g-3">
  <div class="col-lg-8">
    <div class="card-soft">
      <div class="card-title">Articles commandés</div>
      <table class="table">
        <thead><tr><th>Produit</th><th class="text-center">Qté</th><th class="text-end">Prix U.</th><th class="text-end">Sous-total</th></tr></thead>
        <tbody>
          @foreach($order->items as $it)
          <tr>
            <td>
              <div class="d-flex align-items-center gap-2">
                @include('partials.product-thumb', ['product'=>$it->product,'size'=>'sm'])
                <div>
                  <strong>{{ $it->product->nom }}</strong>
                  <div class="muted" style="font-size:12px">Stock actuel : {{ $it->product->stock }}</div>
                </div>
              </div>
            </td>
            <td class="text-center"><strong>{{ $it->quantite }}</strong></td>
            <td class="text-end money">{{ number_format($it->prix_unitaire,0,',',' ') }} F</td>
            <td class="text-end money">{{ number_format($it->sous_total,0,',',' ') }} F</td>
          </tr>
          @endforeach
        </tbody>
        <tfoot>
          <tr><th colspan="3" class="text-end" style="font-size:18px">TOTAL</th><th class="text-end money" style="font-size:20px;color:var(--primary)">{{ number_format($order->montant_total,0,',',' ') }} F</th></tr>
        </tfoot>
      </table>
    </div>
  </div>

  <div class="col-lg-4">
    <div class="card-soft">
      <div class="card-title">Statut</div>
      <div class="mb-3">
        @if($order->statut==='en_cours')<span class="pill pill-warning" style="font-size:14px;padding:8px 16px">⏳ En cours</span>
        @elseif($order->statut==='recue')<span class="pill pill-success" style="font-size:14px;padding:8px 16px">✓ Reçue</span>
        @else<span class="pill pill-danger" style="font-size:14px;padding:8px 16px">✗ Annulée</span>@endif
      </div>
      @if($order->date_reception)
        <div class="d-flex justify-content-between mb-2"><span class="muted">Reçue le</span><strong>{{ $order->date_reception->format('d/m/Y') }}</strong></div>
      @endif
      <div class="d-flex justify-content-between mb-2"><span class="muted">Fournisseur</span><strong>{{ $order->supplier->nom }}</strong></div>
      @if($order->supplier->telephone)<div class="d-flex justify-content-between mb-2"><span class="muted">Tél</span><strong>{{ $order->supplier->telephone }}</strong></div>@endif
      @if($order->notes)<hr><div class="muted" style="font-size:13px">{{ $order->notes }}</div>@endif
    </div>
  </div>
</div>
@endsection
