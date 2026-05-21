@extends('layouts.app')
@section('title','Produits')
@section('content')

<div class="page-head">
  <div>
    <h1>Catalogue produits</h1>
    <div class="sub">{{ $products->total() }} produit(s) au total</div>
  </div>
  <a href="{{ route('products.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Nouveau produit</a>
</div>

<form method="GET" class="card-soft mb-3">
  <div class="row g-2 align-items-end">
    <div class="col-md-5">
      <label class="form-label">Recherche</label>
      <input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="Nom ou référence...">
    </div>
    <div class="col-md-3">
      <label class="form-label">Catégorie</label>
      <select name="category" class="form-select">
        <option value="">Toutes</option>
        @foreach($categories as $c)
          <option value="{{ $c->id }}" @selected(request('category')==$c->id)>{{ $c->nom }}</option>
        @endforeach
      </select>
    </div>
    <div class="col-md-2">
      <label class="form-label">Stock</label>
      <select name="stock" class="form-select">
        <option value="">Tous</option>
        <option value="alerte" @selected(request('stock')==='alerte')>En alerte</option>
        <option value="rupture" @selected(request('stock')==='rupture')>En rupture</option>
      </select>
    </div>
    <div class="col-md-2">
      <button class="btn btn-dark w-100"><i class="bi bi-funnel-fill"></i> Filtrer</button>
    </div>
  </div>
</form>

<div class="table-wrap">
@if($products->isEmpty())
  <div class="empty-state"><div class="ic"><i class="bi bi-box-seam"></i></div>Aucun produit trouvé</div>
@else
<table class="table">
  <thead><tr><th>Produit</th><th>Réf.</th><th>Catégorie</th><th class="text-end">Prix</th><th class="text-center">Stock</th><th class="text-end">Actions</th></tr></thead>
  <tbody>
    @foreach($products as $p)
    <tr>
      <td>
        <div style="display:flex;align-items:center;gap:12px">
          <div style="width:42px;height:42px;border-radius:10px;background:{{ $p->category->couleur_fond ?? '#F5EFE6' }};display:grid;place-items:center;font-size:22px;overflow:hidden">
            @if($p->image)
              <img src="{{ asset('storage/'.$p->image) }}" alt="" style="width:100%;height:100%;object-fit:cover">
            @else
              <span>{{ $p->emojiAffiche() }}</span>
            @endif
          </div>
          <div>
            <div style="font-weight:700">{{ $p->nom }}</div>
            @if(!$p->actif)<span class="pill pill-muted">Inactif</span>@endif
          </div>
        </div>
      </td>
      <td><code style="color:var(--primary)">{{ $p->reference }}</code></td>
      <td>{{ $p->category->nom ?? '—' }}</td>
      <td class="text-end money">{{ number_format($p->prix_vente,0,',',' ') }} F</td>
      <td class="text-center">
        @if($p->enRupture())
          <span class="pill pill-danger">Rupture</span>
        @elseif($p->enAlerte())
          <span class="pill pill-warning">{{ $p->stock }}</span>
        @else
          <span class="pill pill-success">{{ $p->stock }}</span>
        @endif
      </td>
      <td class="text-end">
        <a href="{{ route('products.edit', $p) }}" class="btn btn-ghost btn-sm"><i class="bi bi-pencil"></i></a>
        <form method="POST" action="{{ route('products.destroy', $p) }}" class="d-inline" onsubmit="return confirm('Supprimer ce produit ?')">
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
<div class="mt-3">{{ $products->links() }}</div>
@endsection
