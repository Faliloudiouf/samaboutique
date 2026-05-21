@extends('layouts.app')
@section('title','Catégories')
@section('content')

<div class="page-head">
  <div>
    <h1>Catégories</h1>
    <div class="sub">Organisez votre catalogue par familles de produits</div>
  </div>
  <a href="{{ route('categories.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Nouvelle catégorie</a>
</div>

<div class="table-wrap">
  @if($categories->isEmpty())
    <div class="empty-state"><div class="ic"><i class="bi bi-tags"></i></div>Aucune catégorie. Créez la première !</div>
  @else
  <table class="table">
    <thead><tr><th>Nom</th><th>Description</th><th>Produits</th><th class="text-end">Actions</th></tr></thead>
    <tbody>
      @foreach($categories as $c)
        <tr>
          <td>
            <div style="display:flex;align-items:center;gap:12px">
              <div style="width:42px;height:42px;border-radius:10px;background:{{ $c->couleur_fond }};display:grid;place-items:center;font-size:22px;overflow:hidden">
                @if($c->image)<img src="{{ asset('storage/'.$c->image) }}" style="width:100%;height:100%;object-fit:cover" alt="">@else{{ $c->emoji }}@endif
              </div>
              <div><strong>{{ $c->nom }}</strong><div style="display:flex;gap:6px;margin-top:2px"><span style="width:14px;height:14px;border-radius:4px;background:{{ $c->couleur_fond }};border:1px solid var(--border)"></span><span style="width:14px;height:14px;border-radius:4px;background:{{ $c->couleur_accent }}"></span></div></div>
            </div>
          </td>
          <td class="muted">{{ $c->description ?: '—' }}</td>
          <td><span class="pill pill-muted">{{ $c->products_count }}</span></td>
          <td class="text-end">
            <a href="{{ route('categories.edit', $c) }}" class="btn btn-ghost btn-sm"><i class="bi bi-pencil"></i></a>
            <form method="POST" action="{{ route('categories.destroy', $c) }}" class="d-inline" onsubmit="return confirm('Supprimer cette catégorie ?')">
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
<div class="mt-3">{{ $categories->links() }}</div>
@endsection
