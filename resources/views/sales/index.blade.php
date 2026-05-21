@extends('layouts.app')
@section('title','Historique ventes')
@section('crumbs')
  <span>Boutique</span><span class="sep">›</span><span class="here">Historique des ventes</span>
@endsection
@section('topbar-actions')
  <span class="pill pill-primary pill-no-dot" style="padding:6px 14px;font-size:11px">HISTORIQUE VENTES</span>
@endsection
@section('content')

<div class="page-head">
  <div>
    <h1>Historique des ventes</h1>
    <div class="sub">{{ $sales->total() }} ventes ·
      @switch($period)
        @case('day') journée du {{ $debut->isoFormat('D MMMM YYYY') }} @break
        @case('week') semaine du {{ $debut->isoFormat('D MMM') }} au {{ $fin->isoFormat('D MMM YYYY') }} @break
        @case('month') mois de {{ $debut->isoFormat('MMMM YYYY') }} @break
        @case('year') année {{ $debut->format('Y') }} @break
      @endswitch
    </div>
  </div>
  <div style="display:flex;gap:10px;align-items:center">
    <div class="tab-group">
      <a href="?period=day" class="{{ $period==='day' ? 'active' : '' }}">Jour</a>
      <a href="?period=week" class="{{ $period==='week' ? 'active' : '' }}">Semaine</a>
      <a href="?period=month" class="{{ $period==='month' ? 'active' : '' }}">Mois</a>
      <a href="?period=year" class="{{ $period==='year' ? 'active' : '' }}">Année</a>
    </div>
    @if(auth()->user()->isGerant())
    <a href="{{ route('reports.csv', request()->query()) }}" class="btn btn-outline"><i class="bi bi-download"></i> Exporter</a>
    @endif
  </div>
</div>

<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-bottom:24px">
  <div class="stat-card">
    <div class="top"><span class="label">Ventes ({{ $period === 'day' ? 'jour' : ($period === 'week' ? 'semaine' : ($period === 'month' ? 'mois' : 'année')) }})</span><div class="ic i"><i class="bi bi-receipt"></i></div></div>
    <div class="value">{{ $nbVentes }}</div>
  </div>
  <div class="stat-card">
    <div class="top"><span class="label">Chiffre d'affaires</span><div class="ic p"><i class="bi bi-coin"></i></div></div>
    <div class="value">{{ number_format($caTotal,0,',',' ') }} <small>F</small></div>
  </div>
  <div class="stat-card">
    <div class="top"><span class="label">Panier moyen</span><div class="ic a"><i class="bi bi-basket-fill"></i></div></div>
    <div class="value">{{ number_format($panierMoyen,0,',',' ') }} <small>F</small></div>
  </div>
</div>

<div class="md-layout">
  <div>
    <div style="display:flex;justify-content:space-between;align-items:center;gap:14px;margin-bottom:14px;flex-wrap:wrap">
      <div class="tab-group">
        <a href="?period={{ $period }}&mode=all" class="{{ !$modeFilter || $modeFilter === 'all' ? 'active' : '' }}">Toutes</a>
        <a href="?period={{ $period }}&mode=especes" class="{{ $modeFilter === 'especes' ? 'active' : '' }}">Espèces</a>
        <a href="?period={{ $period }}&mode=mobile" class="{{ $modeFilter === 'mobile' ? 'active' : '' }}">Mobile</a>
        <a href="?period={{ $period }}&mode=credit" class="{{ $modeFilter === 'credit' ? 'active' : '' }}">Crédit</a>
      </div>
      <form method="GET" style="display:flex;gap:6px">
        <input type="hidden" name="period" value="{{ $period }}">
        <input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="N° vente, client..." style="width:230px;height:38px">
      </form>
    </div>

    @forelse($sales as $v)
      <a href="?period={{ $period }}&mode={{ $modeFilter ?? 'all' }}&selected={{ $v->id }}" class="sale-row {{ $selected && $selected->id === $v->id ? 'active' : '' }}">
        <div class="num">{{ $v->numero }}<div class="d">{{ $v->created_at->isoFormat('D MMM · HH:mm') }}</div></div>
        <div class="cl">
          @php $cust = $v->customer; @endphp
          @if($cust)
            <div class="av-circle sm" style="background:{{ $cust->couleurAvatar() }}">{{ $cust->initiales() }}</div>
            <span>{{ $cust->nom }}</span>
          @else
            <div class="av-circle sm" style="background:var(--text-soft)">??</div>
            <span class="muted">Client occasionnel</span>
          @endif
        </div>
        <div style="text-align:center"><span class="muted" style="font-size:12px">{{ $v->items_count ?? $v->items()->count() }} art.</span></div>
        <div>
          @switch($v->mode_paiement)
            @case('especes') <span class="pill pill-success">espèces</span> @break
            @case('wave') <span class="pill pill-info">wave</span> @break
            @case('orange_money') <span class="pill pill-warning">orange</span> @break
            @case('carte') <span class="pill pill-info">carte</span> @break
          @endswitch
        </div>
        <div class="money text-end" style="font-size:15px">{{ number_format($v->montant_total,0,',',' ') }} F</div>
        <div class="text-end"><i class="bi bi-chevron-right" style="color:var(--text-soft)"></i></div>
      </a>
    @empty
      <div class="empty-state"><div class="ic"><i class="bi bi-receipt"></i></div>Aucune vente sur cette période</div>
    @endforelse

    <div>{{ $sales->links() }}</div>
  </div>

  @if($selected)
  @php
    $rendu = max(0, (float)$selected->montant_paye - (float)$selected->montant_total);
    $waText = "🛍️ *SamaBoutique* — Reçu {$selected->numero}\n💰 Total : ".number_format((float)$selected->montant_total,0,',',' ')." FCFA\n📄 ".url('/sales/'.$selected->id);
    $waPhone = preg_replace('/\D+/', '', $selected->client_tel ?? '');
    if ($waPhone && !str_starts_with($waPhone, '221')) $waPhone = '221'.$waPhone;
    $waUrl = ($waPhone ? "https://wa.me/{$waPhone}?text=" : "https://wa.me/?text=") . rawurlencode($waText);
  @endphp
  <div class="detail-panel">
    <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:16px">
      <div>
        <div style="font-family:'Plus Jakarta Sans';font-weight:800;font-size:17px">Détail vente {{ $selected->numero }}</div>
        <div class="muted" style="font-size:13px;margin-top:2px">{{ $selected->created_at->isoFormat('D MMMM YYYY') }} · {{ $selected->created_at->format('H:i') }}</div>
      </div>
      @if($selected->statut==='annulee')<span class="pill pill-danger">annulée</span>
      @elseif($selected->statut==='payee')<span class="pill pill-success">validée</span>
      @elseif($selected->statut==='partielle')<span class="pill pill-warning">partielle</span>
      @else<span class="pill pill-danger">crédit</span>@endif
    </div>

    <div style="background:var(--surface);border-radius:12px;padding:12px;display:flex;align-items:center;gap:10px;margin-bottom:18px">
      @if($selected->customer)
        <div class="av-circle" style="background:{{ $selected->customer->couleurAvatar() }}">{{ $selected->customer->initiales() }}</div>
        <div style="flex:1;min-width:0">
          <a href="{{ route('customers.show',$selected->customer) }}" style="color:var(--text-primary);text-decoration:none;font-weight:700">{{ $selected->customer->nom }}</a>
          <div class="muted" style="font-size:12px">{{ $selected->client_tel ?: '—' }}</div>
        </div>
      @else
        <div class="av-circle" style="background:var(--text-soft)">??</div>
        <div><strong>Client occasionnel</strong></div>
      @endif
    </div>

    <div class="label" style="margin-bottom:8px">Articles</div>
    @foreach($selected->items as $it)
      <div class="cart-row" style="padding:8px 0;border-bottom:1px solid var(--border)">
        <div class="ic">@if($it->product && $it->product->image)<img src="{{ asset('storage/'.$it->product->image) }}" alt="">@else<span>{{ $it->product?->emojiAffiche() ?: '📦' }}</span>@endif</div>
        <div class="nm"><div class="t">{{ $it->produit_nom }}</div><div class="s">{{ $it->quantite }} × {{ number_format($it->prix_unitaire,0,',',' ') }} F</div></div>
        <div class="px">{{ number_format($it->sous_total,0,',',' ') }} F</div>
      </div>
    @endforeach

    <div style="margin-top:14px">
      <div class="tot-row"><span>Sous-total</span><span class="money">{{ number_format($selected->montant_total + (float)$selected->remise,0,',',' ') }} F</span></div>
      @if($selected->remise > 0)
        <div class="tot-row discount"><span>Remise</span><span class="money">−{{ number_format($selected->remise,0,',',' ') }} F</span></div>
      @endif
      <div class="tot-row total"><span>Total</span><span class="v">{{ number_format($selected->montant_total,0,',',' ') }} F</span></div>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-top:16px">
      <a href="{{ route('sales.show', $selected) }}" class="btn btn-outline btn-sm"><i class="bi bi-eye"></i> Reçu</a>
      <a href="{{ $waUrl }}" target="_blank" class="btn btn-outline btn-sm" style="border-color:#25D366;color:#128C7E"><i class="bi bi-whatsapp"></i> WhatsApp</a>
      @if(auth()->user()->isGerant() && $selected->statut !== 'annulee')
        <form method="POST" action="{{ route('sales.cancel', $selected) }}" onsubmit="return confirm('Annuler cette vente ? Le stock sera restitué.')" style="grid-column:1/-1">
          @csrf @method('PATCH')
          <button class="btn btn-danger-soft btn-sm btn-block"><i class="bi bi-x-circle"></i> Annuler la vente</button>
        </form>
      @endif
    </div>
  </div>
  @endif
</div>
@endsection
