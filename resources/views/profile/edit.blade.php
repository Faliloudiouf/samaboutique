@extends('layouts.app')
@section('title','Mon profil')
@section('crumbs')
  <span>Compte</span><span class="sep">›</span><span class="here">Mon profil</span>
@endsection
@section('content')

<div class="page-head">
  <div>
    <h1>Mon profil</h1>
    <div class="sub">Gérez vos informations personnelles et votre mot de passe</div>
  </div>
</div>

<div class="row g-3" style="max-width:1000px">
  <div class="col-md-6">
    <form method="POST" enctype="multipart/form-data" action="{{ route('profile.update') }}" class="card-soft">
      @csrf @method('PATCH')

      <div class="card-title">Informations personnelles</div>

      <div class="d-flex align-items-center gap-3 mb-3 p-3" style="background:var(--surface);border-radius:14px">
        @include('partials.user-avatar', ['user'=>$userModel,'size'=>'xl'])
        <div style="flex:1">
          <input type="file" name="photo" class="form-control form-control-sm" accept="image/*">
          <div class="form-text" style="font-size:11px">Photo JPG/PNG max 2 Mo</div>
          @if($userModel->photo)
            <button type="button" onclick="document.getElementById('rm-photo').submit()" class="btn btn-ghost btn-sm mt-1" style="padding:2px 8px;font-size:11px"><i class="bi bi-trash"></i> Retirer</button>
          @endif
        </div>
      </div>

      <div class="mb-3">
        <label class="form-label">Nom complet</label>
        <input type="text" name="name" class="form-control" value="{{ old('name', $userModel->name) }}" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Email</label>
        <input type="email" name="email" class="form-control" value="{{ old('email', $userModel->email) }}" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Téléphone</label>
        <input type="text" name="telephone" class="form-control" value="{{ old('telephone', $userModel->telephone) }}" placeholder="77 xxx xx xx">
      </div>

      <button class="btn btn-primary"><i class="bi bi-check-lg"></i> Enregistrer</button>
    </form>
  </div>

  <div class="col-md-6">
    <form method="POST" action="{{ route('profile.password') }}" class="card-soft">
      @csrf @method('PATCH')
      <div class="card-title">Changer le mot de passe</div>
      <div class="mb-3">
        <label class="form-label">Mot de passe actuel</label>
        <input type="password" name="current_password" class="form-control" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Nouveau mot de passe</label>
        <input type="password" name="password" class="form-control" minlength="6" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Confirmer</label>
        <input type="password" name="password_confirmation" class="form-control" minlength="6" required>
      </div>
      <button class="btn btn-primary"><i class="bi bi-shield-lock"></i> Changer le mot de passe</button>
    </form>

    <div class="card-soft mt-3" style="background:var(--surface-2)">
      <div class="card-title" style="font-size:14px">Informations du compte</div>
      <div class="d-flex justify-content-between mb-2"><span class="muted">Rôle</span><strong>{{ $userModel->role === 'gerant' ? '🔑 Gérant' : '👨‍💼 Vendeur' }}</strong></div>
      <div class="d-flex justify-content-between mb-2"><span class="muted">Inscrit le</span><strong>{{ $userModel->created_at->format('d/m/Y') }}</strong></div>
      <div class="d-flex justify-content-between"><span class="muted">Dernière connexion</span><strong>{{ $userModel->updated_at->diffForHumans() }}</strong></div>
    </div>
  </div>
</div>

@if($userModel->photo)
<form id="rm-photo" method="POST" action="{{ route('profile.photo.delete') }}" class="d-none">@csrf @method('DELETE')</form>
@endif
@endsection
