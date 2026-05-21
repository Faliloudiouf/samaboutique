@extends('layouts.app')
@section('title','Utilisateurs')
@section('crumbs')
  <span>Compte</span><span class="sep">›</span><span class="here">Utilisateurs</span>
@endsection
@section('topbar-actions')
  <span class="pill pill-primary pill-no-dot" style="padding:6px 14px;font-size:11px">GÉRER LES COMPTES</span>
@endsection
@section('content')

<div class="page-head">
  <div>
    <h1>Comptes utilisateurs</h1>
    <div class="sub">{{ $stats['total'] }} utilisateur(s) · {{ $stats['gerants'] }} gérant(s) · {{ $stats['vendeurs'] }} vendeur(s)</div>
  </div>
  <a href="{{ route('users.create') }}" class="btn btn-primary"><i class="bi bi-person-plus-fill"></i> Nouvel utilisateur</a>
</div>

<div class="row g-3 mb-4">
  <div class="col-md-3"><div class="stat-card"><div class="top"><span class="label">Total comptes</span><div class="ic i"><i class="bi bi-people-fill"></i></div></div><div class="value">{{ $stats['total'] }}</div></div></div>
  <div class="col-md-3"><div class="stat-card"><div class="top"><span class="label">Gérants</span><div class="ic p"><i class="bi bi-shield-lock-fill"></i></div></div><div class="value">{{ $stats['gerants'] }}</div></div></div>
  <div class="col-md-3"><div class="stat-card"><div class="top"><span class="label">Vendeurs</span><div class="ic s"><i class="bi bi-person-badge-fill"></i></div></div><div class="value">{{ $stats['vendeurs'] }}</div></div></div>
  <div class="col-md-3"><div class="stat-card"><div class="top"><span class="label">Suspendus</span><div class="ic a"><i class="bi bi-pause-circle-fill"></i></div></div><div class="value" style="color:{{ $stats['suspendus']>0 ? 'var(--danger)' : 'var(--text-primary)' }}">{{ $stats['suspendus'] }}</div></div></div>
</div>

<div class="card-soft" style="padding:18px">
  <form method="GET" class="d-flex flex-wrap gap-2 align-items-center mb-3">
    <div class="tab-group">
      <a href="?status=all" class="{{ !request('status') || request('status')==='all' ? 'active' : '' }}">Tous</a>
      <a href="?status=active" class="{{ request('status')==='active' ? 'active' : '' }}">Actifs</a>
      <a href="?status=suspended" class="{{ request('status')==='suspended' ? 'active' : '' }}">Suspendus</a>
    </div>
    <select name="role" class="form-select" style="width:160px;height:38px" onchange="this.form.submit()">
      <option value="">Tous rôles</option>
      <option value="gerant" @selected(request('role')==='gerant')>Gérant</option>
      <option value="vendeur" @selected(request('role')==='vendeur')>Vendeur</option>
    </select>
    <input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="Nom ou email..." style="width:240px;height:38px">
    <button class="btn btn-outline btn-sm"><i class="bi bi-search"></i></button>
  </form>

<div class="table-wrap" style="box-shadow:none">
@if($users->isEmpty())
  <div class="empty-state"><div class="ic"><i class="bi bi-people"></i></div>Aucun utilisateur</div>
@else
<table class="table">
  <thead><tr>
    <th>Utilisateur</th><th>Email</th><th>Téléphone</th><th>Rôle</th><th class="text-center">Ventes</th><th>Statut</th><th class="text-end">Actions</th>
  </tr></thead>
  <tbody>
    @foreach($users as $u)
    <tr>
      <td>
        <div class="d-flex align-items-center gap-3">
          @include('partials.user-avatar', ['user'=>$u,'size'=>'md'])
          <div><strong>{{ $u->name }}</strong><div class="muted" style="font-size:12px">Créé le {{ $u->created_at->format('d/m/Y') }}</div></div>
        </div>
      </td>
      <td>{{ $u->email }}</td>
      <td>{{ $u->telephone ?: '—' }}</td>
      <td>
        @if($u->role==='gerant')<span class="pill pill-dark">Gérant</span>
        @else<span class="pill pill-info">Vendeur</span>@endif
      </td>
      <td class="text-center"><span class="pill pill-muted">{{ $u->sales_count }}</span></td>
      <td>
        @if($u->isSuspended())<span class="pill pill-danger">Suspendu</span>
        @elseif(!$u->actif)<span class="pill pill-muted">Inactif</span>
        @else<span class="pill pill-success">Actif</span>@endif
      </td>
      <td class="text-end">
        <div class="d-inline-flex gap-1">
          <a href="{{ route('users.edit', $u) }}" class="btn btn-ghost btn-sm" title="Modifier"><i class="bi bi-pencil"></i></a>
          @if($u->id !== auth()->id())
            <form method="POST" action="{{ route('users.suspend', $u) }}" class="d-inline">
              @csrf @method('PATCH')
              <button class="btn btn-ghost btn-sm" title="{{ $u->isSuspended() ? 'Réactiver' : 'Suspendre' }}">
                <i class="bi bi-{{ $u->isSuspended() ? 'play-circle' : 'pause-circle' }}"></i>
              </button>
            </form>
            <form method="POST" action="{{ route('users.destroy', $u) }}" class="d-inline" onsubmit="return confirm('Supprimer définitivement {{ $u->name }} ?')">
              @csrf @method('DELETE')
              <button class="btn btn-danger-soft btn-sm" title="Supprimer"><i class="bi bi-trash"></i></button>
            </form>
          @endif
        </div>
      </td>
    </tr>
    @endforeach
  </tbody>
</table>
@endif
</div>
  <div class="mt-3">{{ $users->links() }}</div>
</div>
@endsection
