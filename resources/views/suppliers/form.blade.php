@extends('layouts.app')
@section('title', $supplier->exists ? 'Modifier fournisseur' : 'Nouveau fournisseur')
@section('content')

<div class="page-head">
  <div><h1>{{ $supplier->exists ? 'Modifier' : 'Nouveau' }} fournisseur</h1><div class="sub">{{ $supplier->exists ? $supplier->nom : 'Ajoutez un fournisseur' }}</div></div>
  <a href="{{ route('suppliers.index') }}" class="btn btn-ghost"><i class="bi bi-arrow-left"></i> Retour</a>
</div>

<form method="POST" action="{{ $supplier->exists ? route('suppliers.update',$supplier) : route('suppliers.store') }}" class="card-soft" style="max-width:720px">
  @csrf @if($supplier->exists) @method('PUT') @endif
  <div class="row g-3">
    <div class="col-md-6"><label class="form-label">Nom *</label><input type="text" name="nom" class="form-control" value="{{ old('nom',$supplier->nom) }}" required></div>
    <div class="col-md-6"><label class="form-label">Personne contact</label><input type="text" name="contact" class="form-control" value="{{ old('contact',$supplier->contact) }}"></div>
    <div class="col-md-6"><label class="form-label">Téléphone</label><input type="text" name="telephone" class="form-control" value="{{ old('telephone',$supplier->telephone) }}"></div>
    <div class="col-md-6"><label class="form-label">Email</label><input type="email" name="email" class="form-control" value="{{ old('email',$supplier->email) }}"></div>
    <div class="col-12"><label class="form-label">Adresse</label><input type="text" name="adresse" class="form-control" value="{{ old('adresse',$supplier->adresse) }}"></div>
  </div>
  <div class="mt-4"><button class="btn btn-primary"><i class="bi bi-check-lg"></i> {{ $supplier->exists ? 'Enregistrer' : 'Créer' }}</button></div>
</form>
@endsection
