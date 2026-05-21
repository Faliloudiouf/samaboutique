@extends('layouts.app')
@section('title','Tableau de bord')
@section('content')

<div class="page-head">
  <div>
    <h1>Bonjour, {{ auth()->user()->name }} 👋</h1>
    <div class="sub">Voici l'activité de votre boutique aujourd'hui — {{ now()->isoFormat('dddd D MMMM YYYY') }}</div>
  </div>
  <a href="{{ route('sales.create') }}" class="btn btn-primary"><i class="bi bi-cart-plus-fill"></i> Nouvelle vente</a>
</div>

<div class="row g-3 mb-4">
  <div class="col-md-6 col-lg-3">
    <div class="stat-card">
      <div class="icon bg-p"><i class="bi bi-coin"></i></div>
      <div class="label">Ventes du jour</div>
      <div class="value">{{ number_format($ventesJour,0,',',' ') }} <small style="font-size:14px;color:var(--text-muted)">FCFA</small></div>
      <div class="trend">{{ $nbVentesJour }} transaction(s)</div>
    </div>
  </div>
  <div class="col-md-6 col-lg-3">
    <div class="stat-card">
      <div class="icon bg-a"><i class="bi bi-graph-up-arrow"></i></div>
      <div class="label">Ventes du mois</div>
      <div class="value">{{ number_format($ventesMois,0,',',' ') }} <small style="font-size:14px;color:var(--text-muted)">FCFA</small></div>
      <div class="trend">{{ now()->isoFormat('MMMM YYYY') }}</div>
    </div>
  </div>
  <div class="col-md-6 col-lg-3">
    <div class="stat-card">
      <div class="icon bg-d"><i class="bi bi-box-seam-fill"></i></div>
      <div class="label">Produits actifs</div>
      <div class="value">{{ $nbProduits }}</div>
      <div class="trend">{{ $nbAlerte }} en alerte · {{ $nbRupture }} en rupture</div>
    </div>
  </div>
  <div class="col-md-6 col-lg-3">
    <div class="stat-card">
      <div class="icon bg-s"><i class="bi bi-shield-check"></i></div>
      <div class="label">Statut</div>
      <div class="value" style="color:{{ $nbRupture > 0 ? 'var(--danger)' : 'var(--success)' }}">
        {{ $nbRupture > 0 ? 'À surveiller' : 'OK' }}
      </div>
      <div class="trend">Approvisionnement</div>
    </div>
  </div>
</div>

<div class="row g-3">
  <div class="col-lg-7">
    <div class="card-soft mb-3">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <div class="card-title mb-0">Dernières ventes</div>
        <a href="{{ route('sales.index') }}" class="btn btn-ghost btn-sm">Voir tout <i class="bi bi-arrow-right"></i></a>
      </div>
      @if($dernieresVentes->isEmpty())
        <div class="empty-state"><div class="ic"><i class="bi bi-receipt"></i></div>Aucune vente pour l'instant</div>
      @else
        <div class="table-responsive">
          <table class="table">
            <thead><tr><th>N°</th><th>Vendeur</th><th>Client</th><th class="text-end">Montant</th><th>Statut</th></tr></thead>
            <tbody>
            @foreach($dernieresVentes as $v)
              <tr>
                <td><a href="{{ route('sales.show', $v) }}" style="color:var(--primary);font-weight:700;text-decoration:none">{{ $v->numero }}</a><div class="muted" style="font-size:12px">{{ $v->created_at->diffForHumans() }}</div></td>
                <td>{{ $v->user->name }}</td>
                <td>{{ $v->client_nom ?: '—' }}</td>
                <td class="text-end money">{{ number_format($v->montant_total,0,',',' ') }}</td>
                <td>
                  @if($v->statut==='payee')<span class="pill pill-success">Payée</span>
                  @elseif($v->statut==='partielle')<span class="pill pill-warning">Partielle</span>
                  @else<span class="pill pill-danger">Crédit</span>@endif
                </td>
              </tr>
            @endforeach
            </tbody>
          </table>
        </div>
      @endif
    </div>

    <div class="card-soft">
      <div class="card-title">Top 5 produits vendus</div>
      @if($topProduits->isEmpty())
        <div class="muted">Pas encore de données</div>
      @else
        @foreach($topProduits as $p)
          @php $max = $topProduits->max('qte') ?: 1; $w = ($p->qte / $max) * 100; @endphp
          <div class="mb-2">
            <div class="d-flex justify-content-between mb-1"><span style="font-weight:600">{{ $p->nom }}</span><span class="money">{{ $p->qte }}</span></div>
            <div style="height:8px;background:var(--surface-2);border-radius:99px;overflow:hidden">
              <div style="height:100%;width:{{ $w }}%;background:linear-gradient(90deg,var(--primary),var(--accent))"></div>
            </div>
          </div>
        @endforeach
      @endif
    </div>
  </div>

  <div class="col-lg-5">
    <div class="card-soft" style="background:linear-gradient(135deg,#1a0f0a,#2A1D17);color:#fff;border:none">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <div class="card-title mb-0" style="color:var(--accent)">⚠ Alertes Stock</div>
        <span class="pill pill-accent">{{ $produitsAlerte->count() }}</span>
      </div>
      @if($produitsAlerte->isEmpty())
        <div class="empty-state" style="color:#cdb9ad"><div class="ic" style="color:var(--accent)"><i class="bi bi-check-circle"></i></div>Aucune alerte. Stock OK 👌</div>
      @else
        @foreach($produitsAlerte as $p)
          <div class="d-flex align-items-center gap-3 py-2" style="border-bottom:1px solid #2a1d17">
            @include('partials.product-thumb', ['product'=>$p,'size'=>'sm'])
            <div class="flex-grow-1 min-w-0">
              <div style="font-weight:700;color:#fff">{{ $p->nom }}</div>
              <div style="font-size:12px;color:#cdb9ad">{{ $p->category->nom ?? '' }} · seuil {{ $p->seuil_alerte }}</div>
            </div>
            @if($p->enRupture())
              <span class="pill pill-danger">Rupture</span>
            @else
              <span class="pill pill-warning">{{ $p->stock }} restant</span>
            @endif
          </div>
        @endforeach
        @if(auth()->user()->isGerant())
        <a href="{{ route('products.index', ['stock'=>'alerte']) }}" class="btn btn-accent w-100 mt-3"><i class="bi bi-box-arrow-up-right"></i> Gérer le stock</a>
        @endif
      @endif
    </div>
  </div>
</div>

@endsection
