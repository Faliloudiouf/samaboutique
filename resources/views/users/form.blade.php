@extends('layouts.app')
@section('title', $userModel->exists ? 'Modifier '.$userModel->name : 'Nouvel utilisateur')
@section('crumbs')
  <a href="{{ route('users.index') }}" style="color:inherit;text-decoration:none">Utilisateurs</a><span class="sep">›</span><span class="here">{{ $userModel->exists ? $userModel->name : 'Nouveau' }}</span>
@endsection
@section('content')

<div class="page-head">
  <div>
    <h1>{{ $userModel->exists ? 'Modifier l\'utilisateur' : 'Nouvel utilisateur' }}</h1>
    <div class="sub">{{ $userModel->exists ? 'Mettez à jour les informations de '.$userModel->name : 'Créez un nouveau compte (gérant ou vendeur)' }}</div>
  </div>
  <a href="{{ route('users.index') }}" class="btn btn-ghost"><i class="bi bi-arrow-left"></i> Retour</a>
</div>

<form method="POST" enctype="multipart/form-data" action="{{ $userModel->exists ? route('users.update', $userModel) : route('users.store') }}" class="card-soft" style="max-width:760px">
  @csrf
  @if($userModel->exists) @method('PUT') @endif

  <div class="d-flex align-items-center gap-4 mb-4 p-3" style="background:var(--surface);border-radius:14px">
    @include('partials.user-avatar', ['user'=>$userModel,'size'=>'xl'])
    <div style="flex:1">
      <label class="form-label">Photo de profil</label>
      <input type="file" name="photo" class="form-control" accept="image/*">
      <div class="form-text">JPG, PNG ou WebP. Max 2 Mo. Affichée partout dans l'app.</div>
    </div>
  </div>

  <div class="row g-3">
    <div class="col-md-6">
      <label class="form-label">Nom complet *</label>
      <input type="text" name="name" class="form-control" value="{{ old('name', $userModel->name) }}" required autofocus>
    </div>
    <div class="col-md-6">
      <label class="form-label">Email *</label>
      <input type="email" name="email" class="form-control" value="{{ old('email', $userModel->email) }}" required>
    </div>
    <div class="col-md-6">
      <label class="form-label">Téléphone</label>
      <input type="text" name="telephone" class="form-control" value="{{ old('telephone', $userModel->telephone) }}" placeholder="77 xxx xx xx">
    </div>
    <div class="col-md-6">
      <label class="form-label">Rôle *</label>
      <select name="role" class="form-select" required>
        <option value="vendeur" @selected(old('role', $userModel->role)==='vendeur')>👨‍💼 Vendeur (caisse uniquement)</option>
        <option value="gerant" @selected(old('role', $userModel->role)==='gerant')>🔑 Gérant (accès total)</option>
      </select>
    </div>
    <div class="col-md-6">
      <label class="form-label">{{ $userModel->exists ? 'Nouveau mot de passe (laisser vide pour ne pas changer)' : 'Mot de passe *' }}</label>
      <input type="password" name="password" class="form-control" {{ $userModel->exists ? '' : 'required' }} minlength="6" placeholder="••••••••">
    </div>
    <div class="col-md-6">
      <label class="form-label">Statut</label>
      <div class="d-flex align-items-center gap-3" style="padding:12px 14px;border:1.5px solid var(--border-strong);border-radius:12px;background:#fff">
        <label style="display:flex;gap:8px;align-items:center;cursor:pointer">
          <input type="checkbox" name="actif" value="1" {{ old('actif', $userModel->actif ?? true) ? 'checked' : '' }}>
          <span style="font-weight:600">Compte actif (peut se connecter)</span>
        </label>
      </div>
    </div>
  </div>

  <div class="mt-4 d-flex gap-2">
    <button class="btn btn-primary"><i class="bi bi-check-lg"></i> {{ $userModel->exists ? 'Enregistrer' : 'Créer le compte' }}</button>
    @if($userModel->exists && $userModel->photo)
      <a href="#" class="btn btn-outline" onclick="event.preventDefault();if(confirm('Supprimer la photo ?')){document.getElementById('rm-photo').submit()}">
        <i class="bi bi-x-lg"></i> Supprimer la photo
      </a>
    @endif
  </div>
</form>

@if($userModel->exists && $userModel->photo)
<form id="rm-photo" method="POST" action="{{ route('profile.photo.delete') }}" class="d-none">@csrf @method('DELETE')</form>
@endif
@endsection
