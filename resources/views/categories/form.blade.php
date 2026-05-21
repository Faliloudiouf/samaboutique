@extends('layouts.app')
@section('title', $category->exists ? 'Modifier catégorie' : 'Nouvelle catégorie')
@section('content')

<div class="page-head">
  <div>
    <h1>{{ $category->exists ? 'Modifier' : 'Nouvelle' }} catégorie</h1>
    <div class="sub">{{ $category->exists ? $category->nom : 'Ajoutez une famille de produits' }}</div>
  </div>
  <a href="{{ route('categories.index') }}" class="btn btn-ghost"><i class="bi bi-arrow-left"></i> Retour</a>
</div>

<div class="card-soft" style="max-width:640px">
  <form method="POST" enctype="multipart/form-data" action="{{ $category->exists ? route('categories.update',$category) : route('categories.store') }}">
    @csrf
    @if($category->exists) @method('PUT') @endif

    <div class="d-flex align-items-center gap-3 mb-3 p-3" style="background:var(--surface);border-radius:12px">
      <div style="width:72px;height:72px;border-radius:14px;background:{{ $category->couleur_fond ?: '#F5EFE6' }};display:grid;place-items:center;font-size:36px;overflow:hidden;flex-shrink:0">
        @if($category->image)
          <img src="{{ asset('storage/'.$category->image) }}" style="width:100%;height:100%;object-fit:cover" alt="">
        @else
          <span>{{ $category->emoji ?: '📦' }}</span>
        @endif
      </div>
      <div style="flex:1">
        <label class="form-label">Image personnalisée (optionnel)</label>
        <input type="file" name="image" class="form-control" accept="image/*">
        <div class="form-text">Si vous uploadez une image, elle remplacera l'emoji. JPG/PNG max 2 Mo.</div>
      </div>
    </div>

    <div class="row g-3 mb-3">
      <div class="col-md-8">
        <label class="form-label">Nom *</label>
        <input type="text" name="nom" class="form-control" value="{{ old('nom', $category->nom) }}" required>
      </div>
      <div class="col-md-4">
        <label class="form-label">Emoji</label>
        <input type="text" name="emoji" class="form-control" value="{{ old('emoji', $category->emoji ?: '📦') }}" maxlength="6" style="font-size:22px;text-align:center">
      </div>
    </div>
    <div class="row g-3 mb-3">
      <div class="col-md-6">
        <label class="form-label">Couleur de fond (pastel)</label>
        <input type="color" name="couleur_fond" class="form-control" value="{{ old('couleur_fond', $category->couleur_fond ?: '#F5EFE6') }}" style="height:48px">
      </div>
      <div class="col-md-6">
        <label class="form-label">Couleur d'accent</label>
        <input type="color" name="couleur_accent" class="form-control" value="{{ old('couleur_accent', $category->couleur_accent ?: '#C84B31') }}" style="height:48px">
      </div>
    </div>
    <div class="mb-4">
      <label class="form-label">Description</label>
      <input type="text" name="description" class="form-control" value="{{ old('description', $category->description) }}">
    </div>

    <button class="btn btn-primary"><i class="bi bi-check-lg"></i> {{ $category->exists ? 'Enregistrer' : 'Créer' }}</button>
  </form>
</div>
@endsection
