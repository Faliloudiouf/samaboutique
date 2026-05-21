<!doctype html>
<html lang="fr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>@yield('title','Tableau de bord') · SamaBoutique</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@600;700;800&family=DM+Sans:wght@400;500;600;700&family=DM+Mono:wght@500;600;700&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<link href="{{ asset('css/app.css') }}" rel="stylesheet">
</head>
<body>
@php
  $u = auth()->user();
  $nbCreditEnRetard = \App\Models\Sale::whereIn('statut', ['credit','partielle'])
        ->whereNotNull('echeance')->whereDate('echeance','<', now())->count();
@endphp

<div class="sidebar-backdrop" id="sb-backdrop" onclick="document.getElementById('sidebar').classList.remove('open');this.classList.remove('show')"></div>
<div class="app-shell">
  <aside class="sidebar" id="sidebar">
    <div class="brand">
      <div class="logo"><i class="bi bi-house-door-fill"></i></div>
      <div>
        <div class="t">SamaBoutique</div>
        <div class="s">{{ $u->isGerant() ? 'Espace Gérant' : 'Espace Vendeur' }}</div>
      </div>
    </div>

    <div class="nav-section">Caisse</div>
    <a href="{{ route('sales.create') }}" class="nav-link {{ request()->routeIs('sales.create') ? 'active' : '' }}">
      <i class="bi bi-cart-plus-fill"></i> Nouvelle vente
    </a>
    <a href="{{ route('customers.index') }}" class="nav-link {{ request()->routeIs('customers.*') ? 'active' : '' }}">
      <i class="bi bi-chat-square-text-fill"></i> Crédits clients
      @if($nbCreditEnRetard > 0)<span class="badge-n">{{ $nbCreditEnRetard }}</span>@endif
    </a>

    <div class="nav-section">Boutique</div>
    <a href="{{ route('sales.index') }}" class="nav-link {{ request()->routeIs('sales.index') || request()->routeIs('sales.show') ? 'active' : '' }}">
      <i class="bi bi-clock-history"></i> Historique ventes
    </a>
    @if($u->isGerant())
    <a href="{{ route('products.index') }}" class="nav-link {{ request()->routeIs('products.*') ? 'active' : '' }}">
      <i class="bi bi-box-seam-fill"></i> Stocks
    </a>
    <a href="{{ route('categories.index') }}" class="nav-link {{ request()->routeIs('categories.*') ? 'active' : '' }}">
      <i class="bi bi-tags-fill"></i> Catégories
    </a>

    <div class="nav-section">Approvisionnement</div>
    <a href="{{ route('suppliers.index') }}" class="nav-link {{ request()->routeIs('suppliers.*') ? 'active' : '' }}">
      <i class="bi bi-truck"></i> Fournisseurs
    </a>
    <a href="{{ route('purchase-orders.index') }}" class="nav-link {{ request()->routeIs('purchase-orders.*') ? 'active' : '' }}">
      <i class="bi bi-box2-heart-fill"></i> Commandes
    </a>

    <div class="nav-section">Analyses</div>
    <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
      <i class="bi bi-grid-1x2-fill"></i> Tableau de bord
    </a>
    <a href="{{ route('reports.index') }}" class="nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}">
      <i class="bi bi-bar-chart-line-fill"></i> Rapports
    </a>
    @endif

    <div class="nav-section">Compte</div>
    <a href="{{ route('profile.edit') }}" class="nav-link {{ request()->routeIs('profile.*') ? 'active' : '' }}">
      <i class="bi bi-person-circle"></i> Mon profil
    </a>
    @if($u->isGerant())
    <a href="{{ route('users.index') }}" class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}">
      <i class="bi bi-people-fill"></i> Utilisateurs
    </a>
    @endif
    <form method="POST" action="{{ route('logout') }}" style="margin:0">@csrf
      <button type="submit" class="nav-link" style="background:none;border:none;width:100%;text-align:left;color:#cdb9ad;cursor:pointer">
        <i class="bi bi-box-arrow-right"></i> Déconnexion
      </button>
    </form>

    <a href="{{ route('profile.edit') }}" class="user-card" style="text-decoration:none">
      @include('partials.user-avatar', ['user'=>$u,'size'=>'md'])
      <div style="min-width:0;flex:1">
        <div class="nm">{{ $u->name }}</div>
        <div class="rl">{{ $u->role }}</div>
      </div>
      <i class="bi bi-pencil" style="color:var(--text-soft);font-size:14px"></i>
    </a>
  </aside>

  <main class="main">
    <div class="topbar">
      <button class="btn btn-dark sb-toggle btn-sm" onclick="document.getElementById('sidebar').classList.toggle('open');document.getElementById('sb-backdrop').classList.toggle('show')"><i class="bi bi-list"></i></button>
      <div class="crumbs">
        @hasSection('crumbs')
          @yield('crumbs')
        @else
          <span>Accueil</span><span class="sep">›</span><span class="here">@yield('title','Tableau de bord')</span>
        @endif
      </div>
      <div class="search">
        <input type="text" placeholder="Rechercher (produit, client, vente)..." id="global-search">
      </div>
      <div class="right">
        @yield('topbar-actions')
      </div>
    </div>

    <div class="page">
      @if(session('ok'))
        <div class="alert alert-success"><i class="bi bi-check-circle-fill"></i> {{ session('ok') }}</div>
      @endif
      @if(session('err'))
        <div class="alert alert-danger"><i class="bi bi-exclamation-triangle-fill"></i> {{ session('err') }}</div>
      @endif
      @if($errors->any())
        <div class="alert alert-danger">
          <i class="bi bi-exclamation-triangle-fill"></i>
          <div><strong>Veuillez corriger les erreurs :</strong>
          <ul class="mb-0 mt-1" style="padding-left:18px">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
        </div>
      @endif

      @yield('content')
    </div>
  </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<!-- Twemoji pour des emojis 3D Apple-like cohérents sur Windows/Linux -->
<script src="https://cdn.jsdelivr.net/npm/twemoji@14.0.2/dist/twemoji.min.js" crossorigin="anonymous" defer></script>
<script>
  window.parseEmoji = (el)=>{
    if (window.twemoji) twemoji.parse(el || document.body, { folder:'svg', ext:'.svg' });
  };
  window.addEventListener('load', ()=> parseEmoji());

  // Global search → smart redirect
  document.getElementById('global-search')?.addEventListener('keydown', e => {
    if (e.key !== 'Enter') return;
    const q = e.target.value.trim();
    if (!q) return;
    if (/^V-\d/i.test(q)) location.href = '/sales?q=' + encodeURIComponent(q);
    else location.href = '/products?q=' + encodeURIComponent(q);
  });
</script>
@stack('scripts')
</body>
</html>
