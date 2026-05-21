@extends('layouts.app')
@section('title','Crédits clients')
@section('crumbs')
  <span>Caisse</span><span class="sep">›</span><span class="here">Crédits clients</span>
@endsection
@section('topbar-actions')
  <span class="pill pill-primary pill-no-dot" style="padding:6px 14px;font-size:11px">GÉRER LES CRÉDITS</span>
@endsection
@section('content')

@php
  $totalDu = \App\Models\Customer::with('sales','payments')->get()->sum(fn($c) => $c->soldeDu());
  $clientsActifs = \App\Models\Customer::whereHas('sales', fn($q) => $q->whereIn('statut',['credit','partielle']))->count();
  $enRetardMontant = \App\Models\Sale::whereIn('statut',['credit','partielle'])
    ->whereNotNull('echeance')->whereDate('echeance','<',now())
    ->get()->sum(fn($s) => $s->resteAPayer());
  $nbEnRetard = \App\Models\Sale::whereIn('statut',['credit','partielle'])
    ->whereNotNull('echeance')->whereDate('echeance','<',now())->count();
  $remboursesMois = \App\Models\CustomerPayment::whereMonth('created_at', now()->month)
    ->whereYear('created_at', now()->year)->sum('montant');
  $nbRemboursesMois = \App\Models\CustomerPayment::whereMonth('created_at', now()->month)
    ->whereYear('created_at', now()->year)->count();
@endphp

<div class="page-head">
  <div>
    <h1>Gérer les crédits</h1>
    <div class="sub">Vue d'ensemble des dettes clients · <strong>{{ $customers->total() }} client(s)</strong></div>
  </div>
  <div style="display:flex;gap:10px">
    <a href="{{ route('customers.create') }}" class="btn btn-primary"><i class="bi bi-person-plus-fill"></i> Nouveau client</a>
  </div>
</div>

<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:24px">
  <div class="stat-card">
    <div class="top"><span class="label">Total dû</span><div class="ic p"><i class="bi bi-currency-exchange"></i></div></div>
    <div class="value">{{ number_format($totalDu,0,',',' ') }} <small>F</small></div>
    <div class="trend">{{ $customers->total() }} client(s) à suivre</div>
  </div>
  <div class="stat-card">
    <div class="top"><span class="label">Clients actifs</span><div class="ic i"><i class="bi bi-person-fill"></i></div></div>
    <div class="value">{{ $clientsActifs }}</div>
    <div class="trend">avec un solde en cours</div>
  </div>
  <div class="stat-card">
    <div class="top"><span class="label">En retard</span><div class="ic p"><i class="bi bi-clock-fill"></i></div></div>
    <div class="value" style="color:var(--danger)">{{ $nbEnRetard }}</div>
    <div class="trend" style="color:var(--danger)">{{ number_format($enRetardMontant,0,',',' ') }} F à recouvrer</div>
  </div>
  <div class="stat-card">
    <div class="top"><span class="label">Remboursé (mois)</span><div class="ic s"><i class="bi bi-check2-circle"></i></div></div>
    <div class="value" style="color:var(--success)">{{ number_format($remboursesMois,0,',',' ') }} <small>F</small></div>
    <div class="trend up">{{ $nbRemboursesMois }} remboursement(s)</div>
  </div>
</div>

<div class="card-soft" style="padding:18px">
  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:18px;gap:14px;flex-wrap:wrap">
    <div class="tab-group">
      <a href="?filter=all" class="{{ $filter==='all' ? 'active' : '' }}">Tous ({{ $customers->total() }})</a>
      <a href="?filter=recent" class="{{ $filter==='recent' ? 'active' : '' }}">Récents</a>
      <a href="?filter=retard" class="{{ $filter==='retard' ? 'active' : '' }}">En retard ({{ $nbEnRetard }})</a>
    </div>
    <form method="GET" style="display:flex;gap:8px">
      <input type="hidden" name="filter" value="{{ $filter }}">
      <input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="Rechercher un client..." style="width:240px;height:38px">
      <button class="btn btn-outline btn-sm"><i class="bi bi-search"></i></button>
    </form>
  </div>

  <div class="table-wrap" style="box-shadow:none;border-radius:12px">
  @if($customers->isEmpty())
    <div class="empty-state"><div class="ic"><i class="bi bi-people"></i></div>Aucun client trouvé</div>
  @else
  <table class="table">
    <thead><tr>
      <th>Client</th><th>Téléphone</th><th>Dernier achat</th><th>Échéance</th><th class="text-end">Reste dû</th><th>Statut</th><th></th>
    </tr></thead>
    <tbody>
      @foreach($customers as $c)
      @php
        $derniereVente = $c->sales->sortByDesc('created_at')->first();
        $venteEnCours = $c->sales->whereIn('statut',['credit','partielle'])->sortBy('echeance')->first();
        $progression = $derniereVente ? $derniereVente->progressionPaiement() : 0;
        $stCredit = $venteEnCours ? $venteEnCours->statutCredit() : 'paye';
        $jR = $venteEnCours ? $venteEnCours->joursRetard() : 0;
      @endphp
      <tr>
        <td>
          <div style="display:flex;align-items:center;gap:12px">
            <div class="av-circle" style="background:{{ $c->couleurAvatar() }}">{{ $c->initiales() }}</div>
            <div>
              <a href="{{ route('customers.show', $c) }}" style="color:var(--text-primary);text-decoration:none;font-weight:700">{{ $c->nom }}</a>
              <div class="muted" style="font-size:12px">{{ $c->etiquette ?: '+'.$c->telephone }}</div>
            </div>
          </div>
        </td>
        <td><span class="money" style="font-weight:600">{{ $c->telephone ?: '—' }}</span></td>
        <td>
          @if($derniereVente)
            <strong>{{ $derniereVente->created_at->isoFormat('D MMM') }}</strong>
            <div class="muted" style="font-size:12px">{{ number_format($derniereVente->montant_total,0,',',' ') }} F</div>
          @else <span class="muted">—</span> @endif
        </td>
        <td>
          @if($venteEnCours && $venteEnCours->echeance)
            <strong>{{ $venteEnCours->echeance->isoFormat('D MMM') }}</strong>
            @if($jR < 0)<span style="color:var(--danger);font-weight:700"> ({{ $jR }}j)</span>@endif
          @else <span class="muted">—</span> @endif
        </td>
        <td class="text-end">
          @if($c->solde > 0)
            <div class="money" style="color:{{ $stCredit === 'en_retard' ? 'var(--danger)' : 'var(--text-primary)' }}">{{ number_format($c->solde,0,',',' ') }} F</div>
            <div class="muted" style="font-size:11px">remb. {{ $progression }}%</div>
          @else
            <span class="pill pill-success">À jour</span>
          @endif
        </td>
        <td>
          @if($c->solde <= 0)
            <span class="pill pill-success">à jour</span>
          @elseif($stCredit==='en_retard')
            <span class="pill pill-danger">en retard</span>
          @elseif($stCredit==='a_venir')
            <span class="pill pill-warning">à venir</span>
          @else
            <span class="pill pill-info">en cours</span>
          @endif
        </td>
        <td class="text-end">
          <a href="{{ route('customers.show', $c) }}" class="btn btn-ghost btn-sm" title="Détails"><i class="bi bi-three-dots-vertical"></i></a>
        </td>
      </tr>
      @endforeach
    </tbody>
  </table>
  @endif
  </div>
  <div>{{ $customers->links() }}</div>
</div>
@endsection
