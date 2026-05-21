@extends('layouts.app')
@section('title', $customer->exists ? 'Modifier client' : 'Nouveau client')
@section('content')

<div class="page-head">
  <div>
    <h1>{{ $customer->exists ? 'Modifier' : 'Nouveau' }} client</h1>
    <div class="sub">{{ $customer->exists ? $customer->nom : 'Ajoutez une fiche client' }}</div>
  </div>
  <a href="{{ route('customers.index') }}" class="btn btn-ghost"><i class="bi bi-arrow-left"></i> Retour</a>
</div>

<form method="POST" action="{{ $customer->exists ? route('customers.update',$customer) : route('customers.store') }}" class="card-soft" style="max-width:640px">
  @csrf
  @if($customer->exists) @method('PUT') @endif

  <div class="mb-3">
    <label class="form-label">Nom *</label>
    <input type="text" name="nom" class="form-control" value="{{ old('nom', $customer->nom) }}" required>
  </div>
  <div class="row g-3 mb-3">
    <div class="col-md-6">
      <label class="form-label">Téléphone</label>
      <input type="text" name="telephone" class="form-control" value="{{ old('telephone', $customer->telephone) }}" placeholder="77 xxx xx xx">
    </div>
    <div class="col-md-6">
      <label class="form-label">Adresse</label>
      <input type="text" name="adresse" class="form-control" value="{{ old('adresse', $customer->adresse) }}">
    </div>
  </div>
  <div class="mb-4">
    <label class="form-label">Notes</label>
    <textarea name="notes" class="form-control" rows="2">{{ old('notes', $customer->notes) }}</textarea>
  </div>

  <button class="btn btn-primary"><i class="bi bi-check-lg"></i> {{ $customer->exists ? 'Enregistrer' : 'Créer le client' }}</button>
</form>
@endsection
