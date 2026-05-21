@extends('layouts.app')
@section('title','Rapports')
@section('content')

<div class="page-head">
  <div>
    <h1>Rapport de ventes</h1>
    <div class="sub">Du <strong>{{ $debut->format('d/m/Y') }}</strong> au <strong>{{ $fin->format('d/m/Y') }}</strong></div>
  </div>
  <a href="{{ route('reports.csv', request()->query()) }}" class="btn btn-accent"><i class="bi bi-download"></i> Export CSV</a>
</div>

<form method="GET" class="card-soft mb-3">
  <div class="row g-2 align-items-end">
    <div class="col-md-3"><label class="form-label">Date début</label><input type="date" name="debut" value="{{ $debut->format('Y-m-d') }}" class="form-control"></div>
    <div class="col-md-3"><label class="form-label">Date fin</label><input type="date" name="fin" value="{{ $fin->format('Y-m-d') }}" class="form-control"></div>
    <div class="col-md-3">
      <label class="form-label">Raccourcis</label>
      <div class="d-flex gap-1">
        <a class="btn btn-ghost btn-sm" href="?debut={{ now()->format('Y-m-d') }}&fin={{ now()->format('Y-m-d') }}">Auj.</a>
        <a class="btn btn-ghost btn-sm" href="?debut={{ now()->startOfWeek()->format('Y-m-d') }}&fin={{ now()->endOfWeek()->format('Y-m-d') }}">Sem.</a>
        <a class="btn btn-ghost btn-sm" href="?debut={{ now()->startOfMonth()->format('Y-m-d') }}&fin={{ now()->endOfMonth()->format('Y-m-d') }}">Mois</a>
        <a class="btn btn-ghost btn-sm" href="?debut={{ now()->startOfYear()->format('Y-m-d') }}&fin={{ now()->endOfYear()->format('Y-m-d') }}">Année</a>
      </div>
    </div>
    <div class="col-md-3"><button class="btn btn-primary w-100"><i class="bi bi-funnel-fill"></i> Appliquer</button></div>
  </div>
</form>

<div class="row g-3 mb-3">
  <div class="col-md-3"><div class="stat-card"><div class="icon bg-p"><i class="bi bi-coin"></i></div><div class="label">CA total</div><div class="value">{{ number_format($caTotal,0,',',' ') }} <small style="font-size:14px;color:var(--text-muted)">F</small></div></div></div>
  <div class="col-md-3"><div class="stat-card"><div class="icon bg-s"><i class="bi bi-cash-stack"></i></div><div class="label">Encaissé</div><div class="value">{{ number_format($caEncaisse,0,',',' ') }} <small style="font-size:14px;color:var(--text-muted)">F</small></div></div></div>
  <div class="col-md-3"><div class="stat-card"><div class="icon bg-a"><i class="bi bi-receipt"></i></div><div class="label">Nb ventes</div><div class="value">{{ $nbVentes }}</div></div></div>
  <div class="col-md-3"><div class="stat-card"><div class="icon bg-d"><i class="bi bi-graph-up"></i></div><div class="label">Panier moyen</div><div class="value">{{ number_format($panierMoyen,0,',',' ') }} <small style="font-size:14px;color:var(--text-muted)">F</small></div></div></div>
</div>

<div class="row g-3">
  <div class="col-lg-8">
    <div class="card-soft mb-3">
      <div class="card-title">Ventes par jour</div>
      @if($parJour->isEmpty())
        <div class="empty-state"><div class="ic"><i class="bi bi-bar-chart"></i></div>Aucune vente sur la période</div>
      @else
        @php $max = $parJour->max('total') ?: 1; @endphp
        @foreach($parJour as $j)
          <div class="mb-2">
            <div class="d-flex justify-content-between mb-1">
              <span style="font-weight:600">{{ \Carbon\Carbon::parse($j->jour)->isoFormat('ddd D MMM') }}</span>
              <span class="money">{{ number_format($j->total,0,',',' ') }} F · {{ $j->n }} vtes</span>
            </div>
            <div style="height:10px;background:var(--surface-2);border-radius:99px;overflow:hidden">
              <div style="height:100%;width:{{ ($j->total/$max)*100 }}%;background:linear-gradient(90deg,var(--primary),var(--accent))"></div>
            </div>
          </div>
        @endforeach
      @endif
    </div>

    <div class="card-soft">
      <div class="card-title">Top produits</div>
      @if($topProduits->isEmpty())
        <div class="muted">Pas de données</div>
      @else
      <table class="table">
        <thead><tr><th>#</th><th>Produit</th><th class="text-center">Qté</th><th class="text-end">CA</th></tr></thead>
        <tbody>
          @foreach($topProduits as $i => $p)
          <tr><td>{{ $i+1 }}</td><td><strong>{{ $p->nom }}</strong><div class="muted" style="font-size:12px">{{ $p->reference }}</div></td>
              <td class="text-center money">{{ $p->qte }}</td>
              <td class="text-end money">{{ number_format($p->ca,0,',',' ') }} F</td></tr>
          @endforeach
        </tbody>
      </table>
      @endif
    </div>
  </div>

  <div class="col-lg-4">
    <div class="card-soft mb-3">
      <div class="card-title">Par mode de paiement</div>
      @forelse($parMode as $m)
        <div class="d-flex justify-content-between py-2" style="border-bottom:1px solid var(--border)">
          <span class="pill pill-info">{{ str_replace('_',' ',$m->mode_paiement) }}</span>
          <span class="money">{{ number_format($m->total,0,',',' ') }} F</span>
        </div>
      @empty <div class="muted">—</div> @endforelse
    </div>

    <div class="card-soft mb-3">
      <div class="card-title">Par statut</div>
      @forelse($parStatut as $s)
        <div class="d-flex justify-content-between py-2" style="border-bottom:1px solid var(--border)">
          <span>
            @if($s->statut==='payee')<span class="pill pill-success">Payée</span>
            @elseif($s->statut==='partielle')<span class="pill pill-warning">Partielle</span>
            @else<span class="pill pill-danger">Crédit</span>@endif
          </span>
          <span class="money">{{ number_format($s->total,0,',',' ') }} F</span>
        </div>
      @empty <div class="muted">—</div> @endforelse
    </div>

    <div class="card-soft">
      <div class="card-title">Par vendeur</div>
      @forelse($parVendeur as $v)
        <div class="d-flex justify-content-between py-2" style="border-bottom:1px solid var(--border)">
          <span style="font-weight:600">{{ $v->user->name ?? '?' }}</span>
          <span class="money">{{ number_format($v->total,0,',',' ') }} F <small class="muted">({{ $v->n }})</small></span>
        </div>
      @empty <div class="muted">—</div> @endforelse
    </div>
  </div>
</div>
@endsection
