@extends('layouts.app')
@section('title', $product->exists ? 'Modifier produit' : 'Nouveau produit')
@section('content')

<div class="page-head">
  <div>
    <h1>{{ $product->exists ? 'Modifier' : 'Nouveau' }} produit</h1>
    <div class="sub">{{ $product->exists ? $product->nom : 'Ajoutez un article au catalogue' }}</div>
  </div>
  <a href="{{ route('products.index') }}" class="btn btn-ghost"><i class="bi bi-arrow-left"></i> Retour</a>
</div>

<form method="POST" enctype="multipart/form-data" action="{{ $product->exists ? route('products.update',$product) : route('products.store') }}" class="card-soft" style="max-width:880px">
  @csrf
  @if($product->exists) @method('PUT') @endif

  <div class="row g-3">
    <div class="col-md-3">
      <label class="form-label">Référence *</label>
      <input type="text" name="reference" class="form-control" value="{{ old('reference', $product->reference) }}" required>
    </div>
    <div class="col-md-7">
      <label class="form-label">Nom du produit *</label>
      <input type="text" name="nom" class="form-control" value="{{ old('nom', $product->nom) }}" required>
    </div>
    <div class="col-md-2">
      <label class="form-label">Emoji</label>
      <input type="text" name="emoji" class="form-control" value="{{ old('emoji', $product->emoji) }}" maxlength="6" placeholder="📦" style="font-size:22px;text-align:center">
    </div>
    <div class="col-md-6">
      <label class="form-label">Catégorie *</label>
      <select name="category_id" class="form-select" required>
        <option value="">— Choisir —</option>
        @foreach($categories as $c)
          <option value="{{ $c->id }}" @selected(old('category_id',$product->category_id)==$c->id)>{{ $c->nom }}</option>
        @endforeach
      </select>
    </div>
    <div class="col-md-6">
      <label class="form-label">Image</label>
      <input type="file" name="image" class="form-control" accept="image/*">
      @if($product->image)<div class="mt-2"><img src="{{ asset('storage/'.$product->image) }}" style="height:60px;border-radius:8px"></div>@endif
    </div>

    <div class="col-md-12"><label class="form-label">Description</label>
      <textarea name="description" class="form-control" rows="2">{{ old('description', $product->description) }}</textarea>
    </div>

    <div class="col-md-3">
      <label class="form-label">Prix d'achat (FCFA)</label>
      <input type="number" step="1" min="0" name="prix_achat" class="form-control" value="{{ old('prix_achat', $product->prix_achat) }}">
    </div>
    <div class="col-md-3">
      <label class="form-label">Prix de vente (FCFA) *</label>
      <input type="number" step="1" min="0" name="prix_vente" class="form-control" value="{{ old('prix_vente', $product->prix_vente) }}" required>
    </div>
    <div class="col-md-3">
      <label class="form-label">Stock initial *</label>
      <input type="number" min="0" name="stock" class="form-control" value="{{ old('stock', $product->stock) }}" required>
    </div>
    <div class="col-md-3">
      <label class="form-label">Seuil d'alerte *</label>
      <input type="number" min="0" name="seuil_alerte" class="form-control" value="{{ old('seuil_alerte', $product->seuil_alerte) }}" required>
    </div>

    <div class="col-12">
      <label><input type="checkbox" name="actif" value="1" {{ old('actif', $product->actif) ? 'checked' : '' }}> Produit actif (visible en caisse)</label>
    </div>
  </div>

  <div class="mt-4">
    <button class="btn btn-primary"><i class="bi bi-check-lg"></i> {{ $product->exists ? 'Enregistrer' : 'Créer le produit' }}</button>
  </div>
</form>
@endsection
