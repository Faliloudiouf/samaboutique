@extends('layouts.app')
@section('title','Fournisseurs')
@section('content')

<div class="page-head">
  <div><h1>Fournisseurs</h1><div class="sub">{{ $suppliers->total() }} fournisseur(s)</div></div>
  <a href="{{ route('suppliers.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Nouveau fournisseur</a>
</div>

<div class="table-wrap">
@if($suppliers->isEmpty())
  <div class="empty-state"><div class="ic"><i class="bi bi-truck"></i></div>Aucun fournisseur</div>
@else
<table class="table">
  <thead><tr><th>Nom</th><th>Contact</th><th>Téléphone</th><th>Email</th><th class="text-center">Cmd</th><th></th></tr></thead>
  <tbody>
    @foreach($suppliers as $s)
    <tr>
      <td><strong>{{ $s->nom }}</strong></td>
      <td>{{ $s->contact ?: '—' }}</td>
      <td>{{ $s->telephone ?: '—' }}</td>
      <td>{{ $s->email ?: '—' }}</td>
      <td class="text-center"><span class="pill pill-muted">{{ $s->orders_count }}</span></td>
      <td class="text-end">
        <a href="{{ route('suppliers.edit', $s) }}" class="btn btn-ghost btn-sm"><i class="bi bi-pencil"></i></a>
        <form method="POST" action="{{ route('suppliers.destroy', $s) }}" class="d-inline" onsubmit="return confirm('Supprimer ?')">
          @csrf @method('DELETE')
          <button class="btn btn-danger-soft btn-sm"><i class="bi bi-trash"></i></button>
        </form>
      </td>
    </tr>
    @endforeach
  </tbody>
</table>
@endif
</div>
<div class="mt-3">{{ $suppliers->links() }}</div>
@endsection
