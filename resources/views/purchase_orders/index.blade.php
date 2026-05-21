@extends('layouts.app')
@section('title','Commandes fournisseurs')
@section('content')

<div class="page-head">
  <div><h1>Commandes fournisseurs</h1><div class="sub">{{ $orders->total() }} commande(s)</div></div>
  <a href="{{ route('purchase-orders.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Nouvelle commande</a>
</div>

<form method="GET" class="card-soft mb-3">
  <div class="row g-2 align-items-end">
    <div class="col-md-4">
      <label class="form-label">Statut</label>
      <select name="statut" class="form-select">
        <option value="">Tous</option>
        <option value="en_cours" @selected(request('statut')==='en_cours')>En cours</option>
        <option value="recue" @selected(request('statut')==='recue')>Reçue</option>
        <option value="annulee" @selected(request('statut')==='annulee')>Annulée</option>
      </select>
    </div>
    <div class="col-md-2"><button class="btn btn-dark w-100"><i class="bi bi-funnel-fill"></i> Filtrer</button></div>
  </div>
</form>

<div class="table-wrap">
@if($orders->isEmpty())
  <div class="empty-state"><div class="ic"><i class="bi bi-box2-heart"></i></div>Aucune commande</div>
@else
<table class="table">
  <thead><tr><th>N°</th><th>Date</th><th>Fournisseur</th><th>Par</th><th class="text-end">Montant</th><th>Statut</th><th></th></tr></thead>
  <tbody>
    @foreach($orders as $o)
    <tr>
      <td><strong>{{ $o->numero }}</strong></td>
      <td>{{ $o->date_commande->format('d/m/Y') }}</td>
      <td>{{ $o->supplier->nom }}</td>
      <td>{{ $o->user->name }}</td>
      <td class="text-end money">{{ number_format($o->montant_total,0,',',' ') }} F</td>
      <td>
        @if($o->statut==='en_cours')<span class="pill pill-warning">En cours</span>
        @elseif($o->statut==='recue')<span class="pill pill-success">Reçue</span>
        @else<span class="pill pill-danger">Annulée</span>@endif
      </td>
      <td class="text-end"><a href="{{ route('purchase-orders.show', $o) }}" class="btn btn-ghost btn-sm"><i class="bi bi-eye"></i></a></td>
    </tr>
    @endforeach
  </tbody>
</table>
@endif
</div>
<div class="mt-3">{{ $orders->links() }}</div>
@endsection
