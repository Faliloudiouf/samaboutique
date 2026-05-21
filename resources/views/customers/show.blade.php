@extends('layouts.app')
@section('title', $customer->nom)
@section('crumbs')
  <span>Crédits</span><span class="sep">›</span><span class="here">{{ $customer->nom }}</span>
@endsection
@section('content')

<div class="page-head">
  <div style="display:flex;align-items:center;gap:14px">
    <div class="av-circle lg" style="background:{{ $customer->couleurAvatar() }}">{{ $customer->initiales() }}</div>
    <div>
      <h1>{{ $customer->nom }}</h1>
      <div class="sub">{{ $customer->etiquette ?: ($customer->telephone ?: '—') }}{{ $customer->adresse ? ' · '.$customer->adresse : '' }}</div>
    </div>
  </div>
  <div style="display:flex;gap:10px">
    @if($solde > 0)
      <a href="{{ route('customers.repayment', $customer) }}" class="btn btn-success"><i class="bi bi-cash-coin"></i> Remboursement</a>
    @endif
    <a href="{{ route('customers.edit', $customer) }}" class="btn btn-outline"><i class="bi bi-pencil"></i> Modifier</a>
  </div>
</div>

<div style="display:grid;grid-template-columns:1fr 2fr;gap:24px">
  <div class="card-soft" style="background:linear-gradient(135deg,#1a0f0a,#2A1D17);color:#fff;border:none">
    <div style="font-size:12px;color:var(--accent);font-weight:800;letter-spacing:.08em;text-transform:uppercase">Solde dû</div>
    <div class="money" style="font-size:36px;color:{{ $solde > 0 ? 'var(--accent)' : '#7ed99e' }};margin-top:8px">{{ number_format($solde,0,',',' ') }} <small style="font-size:16px;color:#cdb9ad">FCFA</small></div>
    <div style="font-size:13px;color:#cdb9ad;margin-top:4px">{{ $customer->sales->count() }} vente(s) · {{ $customer->payments->count() }} paiement(s)</div>
  </div>

  <div class="card-soft">
    <div class="card-title">Historique des ventes</div>
    @if($customer->sales->isEmpty())
      <div class="empty-state"><div class="ic"><i class="bi bi-receipt"></i></div>Aucune vente</div>
    @else
    <table class="table">
      <thead><tr><th>N°</th><th>Date</th><th class="text-end">Total</th><th class="text-end">Payé</th><th>Statut</th><th></th></tr></thead>
      <tbody>
      @foreach($customer->sales as $s)
      <tr>
        <td><strong>{{ $s->numero }}</strong></td>
        <td>{{ $s->created_at->format('d/m/Y') }}</td>
        <td class="text-end money">{{ number_format($s->montant_total,0,',',' ') }}</td>
        <td class="text-end money">{{ number_format($s->montant_paye,0,',',' ') }}</td>
        <td>
          @if($s->statut==='payee')<span class="pill pill-success">payée</span>
          @elseif($s->statut==='partielle')<span class="pill pill-warning">partielle</span>
          @else<span class="pill pill-danger">crédit</span>@endif
        </td>
        <td><a href="{{ route('sales.show', $s) }}" class="btn btn-ghost btn-sm"><i class="bi bi-eye"></i></a></td>
      </tr>
      @endforeach
      </tbody>
    </table>
    @endif
  </div>
</div>

@if($customer->payments->isNotEmpty())
<div class="card-soft" style="margin-top:24px">
  <div class="card-title">Historique des paiements</div>
  <table class="table">
    <thead><tr><th>Date</th><th>Vente</th><th>Mode</th><th>Par</th><th class="text-end">Montant</th></tr></thead>
    <tbody>
    @foreach($customer->payments as $p)
    <tr>
      <td>{{ $p->created_at->format('d/m/Y H:i') }}</td>
      <td>{{ $p->sale ? $p->sale->numero : '—' }}</td>
      <td><span class="pill pill-info">{{ str_replace('_',' ',$p->mode_paiement) }}</span></td>
      <td>{{ $p->user->name }}</td>
      <td class="text-end money" style="color:var(--success)">+{{ number_format($p->montant,0,',',' ') }} F</td>
    </tr>
    @endforeach
    </tbody>
  </table>
</div>
@endif
@endsection
