@extends('layouts.app')
@section('title','Nouvelle commande')
@section('content')

<div class="page-head">
  <div><h1><i class="bi bi-truck"></i> Nouvelle commande fournisseur</h1><div class="sub">Préparez le réapprovisionnement</div></div>
  <a href="{{ route('purchase-orders.index') }}" class="btn btn-ghost"><i class="bi bi-arrow-left"></i> Retour</a>
</div>

<form method="POST" action="{{ route('purchase-orders.store') }}">
  @csrf
  <div class="row g-3">
    <div class="col-lg-8">
      <div class="card-soft mb-3">
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label">Fournisseur *</label>
            <select name="supplier_id" class="form-select" required>
              <option value="">— Choisir —</option>
              @foreach($suppliers as $s)
                <option value="{{ $s->id }}" @selected(old('supplier_id')==$s->id)>{{ $s->nom }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-md-6">
            <label class="form-label">Date de commande *</label>
            <input type="date" name="date_commande" class="form-control" value="{{ old('date_commande', now()->format('Y-m-d')) }}" required>
          </div>
          <div class="col-12">
            <label class="form-label">Notes</label>
            <textarea name="notes" class="form-control" rows="2"></textarea>
          </div>
        </div>
      </div>

      <div class="card-soft">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <div class="card-title mb-0">Articles à commander</div>
          <button type="button" class="btn btn-accent btn-sm" onclick="addLine()"><i class="bi bi-plus-lg"></i> Ajouter une ligne</button>
        </div>
        <table class="table" id="po-table">
          <thead><tr><th>Produit</th><th>Quantité</th><th>Prix achat U.</th><th class="text-end">Sous-total</th><th></th></tr></thead>
          <tbody id="po-tbody"></tbody>
          <tfoot>
            <tr><th colspan="3" class="text-end">TOTAL</th><th class="text-end money" id="po-total" style="color:var(--primary);font-size:18px">0 F</th><th></th></tr>
          </tfoot>
        </table>
      </div>
    </div>

    <div class="col-lg-4">
      <div class="card-soft" style="position:sticky;top:24px">
        <div class="card-title">Validation</div>
        <p class="muted" style="font-size:13px">Lorsque la commande sera <strong>réceptionnée</strong>, le stock des produits sera automatiquement mis à jour.</p>
        <button type="submit" class="btn btn-primary w-100 btn-lg"><i class="bi bi-check-circle-fill"></i> Créer la commande</button>
      </div>
    </div>
  </div>
</form>

<template id="row-tpl">
  <tr>
    <td>
      <select name="items[__I__][product_id]" class="form-select prod-sel" required onchange="onProd(this)">
        <option value="">— Produit —</option>
        @foreach($products as $p)
          <option value="{{ $p->id }}" data-prix="{{ $p->prix_achat }}" data-stock="{{ $p->stock }}">{{ $p->nom }} ({{ $p->reference }}) · stock: {{ $p->stock }}</option>
        @endforeach
      </select>
    </td>
    <td><input type="number" name="items[__I__][quantite]" min="1" value="1" class="form-control qte" onchange="recalc()" required></td>
    <td><input type="number" name="items[__I__][prix_unitaire]" min="0" step="1" value="0" class="form-control prix" onchange="recalc()" required></td>
    <td class="text-end money sst">0 F</td>
    <td class="text-end"><button type="button" class="btn btn-danger-soft btn-sm" onclick="rmLine(this)"><i class="bi bi-x-lg"></i></button></td>
  </tr>
</template>

@push('scripts')
<script>
let idx=0;
function addLine(){
  const tpl=document.getElementById('row-tpl').innerHTML.replaceAll('__I__', idx++);
  document.getElementById('po-tbody').insertAdjacentHTML('beforeend', tpl);
  recalc();
}
function rmLine(b){ b.closest('tr').remove(); recalc(); }
function onProd(sel){
  const opt=sel.selectedOptions[0];
  const tr=sel.closest('tr');
  if(opt.dataset.prix) tr.querySelector('.prix').value=opt.dataset.prix;
  recalc();
}
function recalc(){
  let t=0;
  document.querySelectorAll('#po-tbody tr').forEach(tr=>{
    const q=+tr.querySelector('.qte').value||0;
    const p=+tr.querySelector('.prix').value||0;
    const st=q*p; t+=st;
    tr.querySelector('.sst').textContent=new Intl.NumberFormat('fr-FR').format(st)+' F';
  });
  document.getElementById('po-total').textContent=new Intl.NumberFormat('fr-FR').format(t)+' F';
}
addLine();
</script>
@endpush
@endsection
