@extends('layouts.app')
@section('title','Nouvelle vente')
@section('crumbs')
  <span>Caisse</span><span class="sep">›</span><span class="here">Nouvelle vente</span>
@endsection
@section('topbar-actions')
  <span class="pill pill-primary pill-no-dot" style="padding:6px 14px;font-size:11px">VENTE EN COURS</span>
@endsection
@section('content')

<div class="pos-layout">
  <div>
    <div class="cat-chips" id="cat-chips">
      <a href="#" class="cat-chip active" data-cat="all">Tous <span class="n">({{ $products->count() }})</span></a>
      @foreach($categories as $c)
        @php $n = $products->where('category_id',$c->id)->count(); @endphp
        @if($n > 0)
        <a href="#" class="cat-chip" data-cat="{{ $c->id }}">
          <span class="dot" style="background:{{ $c->couleur_accent }}"></span>
          {{ $c->nom }} <span class="n">({{ $n }})</span>
        </a>
        @endif
      @endforeach
    </div>

    <div class="pos-grid" id="pos-list">
      @forelse($products as $p)
        <div class="pcard {{ $p->stock <= 0 ? 'out' : '' }}"
             data-id="{{ $p->id }}"
             data-cat="{{ $p->category_id }}"
             data-nom="{{ $p->nom }}"
             data-emoji="{{ $p->emojiAffiche() }}"
             data-prix="{{ $p->prix_vente }}"
             data-stock="{{ $p->stock }}"
             data-img="{{ $p->image ? asset('storage/'.$p->image) : '' }}"
             data-search="{{ strtolower($p->nom.' '.$p->reference) }}"
             style="--cat-fond: {{ $p->category->couleur_fond ?? '#F5EFE6' }}">
          <span class="qty-pill" style="display:none">×0</span>
          <div class="visual">
            @if($p->image)
              <img src="{{ asset('storage/'.$p->image) }}" alt="{{ $p->nom }}">
            @else
              <span class="emoji-host">{{ $p->emojiAffiche() }}</span>
            @endif
          </div>
          <div class="nm">{{ $p->nom }}</div>
          <div class="meta {{ $p->stock > 0 && $p->stock <= $p->seuil_alerte ? 'bas' : '' }}">
            @if($p->stock <= 0) Rupture
            @elseif($p->stock <= $p->seuil_alerte) ⚠ Stock bas : {{ $p->stock }}
            @else Stock : {{ $p->stock }}
            @endif
          </div>
          <div class="px-row">
            <span class="px">{{ number_format($p->prix_vente,0,',',' ') }} <small>F</small></span>
            <button type="button" class="add" onclick="event.stopPropagation();addToCart(this.closest('.pcard'))"><i class="bi bi-plus-lg"></i></button>
          </div>
        </div>
      @empty
        <div class="empty-state" style="grid-column:1/-1"><div class="ic"><i class="bi bi-inboxes"></i></div>Aucun produit. Ajoutez-en dans le catalogue.</div>
      @endforelse
    </div>
  </div>

  <form id="sale-form" method="POST" action="{{ route('sales.store') }}" class="cart-panel">
    @csrf
    <div class="cart-head">
      <div>
        <div class="t"><i class="bi bi-basket3-fill" style="color:var(--primary)"></i> Panier en cours</div>
        <div class="vente-num">Vente #{{ \App\Models\Sale::genererNumero() }}</div>
      </div>
      <span class="pill pill-info pill-no-dot" id="cart-count">0 article</span>
    </div>

    <div class="cart-section">
      <div class="lbl">Client</div>
      <select name="customer_id" class="form-select" id="customer-select" style="height:48px">
        <option value="">— Client occasionnel —</option>
        @foreach($customers as $c)
          <option value="{{ $c->id }}" data-nom="{{ $c->nom }}" data-tel="{{ $c->telephone }}" data-etiq="{{ $c->etiquette }}">{{ $c->nom }}{{ $c->telephone ? ' · '.$c->telephone : '' }}</option>
        @endforeach
      </select>
      <input type="hidden" name="client_nom" id="client-nom">
      <input type="hidden" name="client_tel" id="client-tel">
      <div id="client-info" style="display:none;font-size:12px;color:var(--text-muted);margin-top:8px;padding:8px 12px;background:var(--surface-2);border-radius:8px"></div>
    </div>

    <div class="cart-items" id="cart-items">
      <div class="empty-state" id="cart-empty" style="padding:40px 20px"><div class="ic"><i class="bi bi-cart"></i></div>Panier vide<br><small>Cliquez sur un produit pour l'ajouter</small></div>
    </div>

    <div class="cart-foot">
      <div class="tot-row"><span>Sous-total</span><span class="money" id="subtot-display">0 F</span></div>
      <div class="tot-row discount" id="row-remise" style="display:none">
        <span>Remise</span><span class="money" id="remise-display">−0 F</span>
      </div>
      <div class="d-flex" style="display:flex;gap:8px;margin-top:8px">
        <input type="number" name="remise" id="remise-input" class="form-control" placeholder="Remise (FCFA)" min="0" step="100" style="height:38px;font-size:13px" onchange="render()">
      </div>
      <div class="tot-row total"><span>Total à payer</span><span class="v" id="total-display">0 F</span></div>

      <div class="pay-modes" id="pay-modes">
        <div class="pay-mode active" data-mode="especes"><span class="em">💵</span>Espèces</div>
        <div class="pay-mode" data-mode="wave"><span class="em">🌊</span>Wave</div>
        <div class="pay-mode" data-mode="orange_money"><span class="em">🟠</span>Orange</div>
        <div class="pay-mode" data-mode="credit"><span class="em">🪪</span>Crédit</div>
      </div>
      <input type="hidden" name="mode_paiement" id="mode-paiement" value="especes">
      <input type="hidden" name="montant_paye" id="montant-paye-h" value="0">

      <button type="submit" class="btn btn-primary btn-lg btn-block" id="btn-validate" disabled style="margin-top:14px">
        <i class="bi bi-check-circle-fill"></i> Valider la vente
      </button>
    </div>
  </form>
</div>

@push('scripts')
<script>
const cart = new Map();
const items = document.querySelectorAll('.pcard');

function fmt(n){return new Intl.NumberFormat('fr-FR').format(Math.max(0,Math.round(n)))+' F'}

function addToCart(d){
  const id = +d.dataset.id;
  if (d.classList.contains('out')) return;
  if (cart.has(id)) {
    const c = cart.get(id);
    if (c.qte < c.stock) c.qte++;
  } else {
    cart.set(id, {
      nom: d.dataset.nom, prix: +d.dataset.prix, stock: +d.dataset.stock,
      emoji: d.dataset.emoji, img: d.dataset.img || '', qte: 1
    });
  }
  render();
}

function chg(id, delta) {
  const c = cart.get(id); if (!c) return;
  const n = c.qte + delta;
  if (n < 1) return rm(id);
  if (n > c.stock) return;
  c.qte = n; render();
}
function rm(id) { cart.delete(id); render(); }

function render() {
  const cartEl = document.getElementById('cart-items');
  const subtotEl = document.getElementById('subtot-display');
  const totalEl = document.getElementById('total-display');
  const remiseEl = document.getElementById('remise-display');
  const remiseRow = document.getElementById('row-remise');
  const countEl = document.getElementById('cart-count');
  const btnV = document.getElementById('btn-validate');

  // Reset qty pills on all pcards
  items.forEach(d => { d.querySelector('.qty-pill').style.display = 'none'; d.classList.remove('in-cart'); });

  if (cart.size === 0) {
    cartEl.innerHTML = '<div class="empty-state" style="padding:40px 20px"><div class="ic"><i class="bi bi-cart"></i></div>Panier vide<br><small>Cliquez sur un produit pour l\'ajouter</small></div>';
    subtotEl.textContent = '0 F'; totalEl.textContent = '0 F'; remiseRow.style.display = 'none';
    countEl.textContent = '0 article'; btnV.disabled = true;
    syncHidden(0, 0);
    return;
  }

  let html = '', subtot = 0, n = 0;
  cart.forEach((v, k) => {
    const st = v.qte * v.prix; subtot += st; n += v.qte;
    const card = document.querySelector('.pcard[data-id="' + k + '"]');
    if (card) {
      card.classList.add('in-cart');
      const pill = card.querySelector('.qty-pill');
      pill.style.display = 'block'; pill.textContent = '×' + v.qte;
    }
    const ic = v.img
      ? `<div class="ic"><img src="${v.img}" alt=""></div>`
      : `<div class="ic"><span>${v.emoji}</span></div>`;
    html += `<div class="cart-row">
      ${ic}
      <div class="nm"><div class="t">${v.nom}</div><div class="s">${v.qte} × ${fmt(v.prix)}</div></div>
      <div style="display:flex;align-items:center;gap:4px">
        <button type="button" class="rm" onclick="chg(${k},-1)" title="-"><i class="bi bi-dash-circle"></i></button>
        <button type="button" class="rm" onclick="chg(${k},1)" title="+"><i class="bi bi-plus-circle"></i></button>
      </div>
      <div class="px">${fmt(st)}</div>
      <button type="button" class="rm" onclick="rm(${k})" title="Supprimer"><i class="bi bi-x-lg"></i></button>
    </div>`;
  });
  cartEl.innerHTML = html;

  const remise = Math.max(0, Math.min(subtot, +document.getElementById('remise-input').value || 0));
  const total = Math.max(0, subtot - remise);

  subtotEl.textContent = fmt(subtot);
  if (remise > 0) { remiseRow.style.display = 'flex'; remiseEl.textContent = '−' + fmt(remise); }
  else remiseRow.style.display = 'none';
  totalEl.textContent = fmt(total);
  countEl.textContent = n + (n > 1 ? ' articles' : ' article');
  btnV.disabled = false;

  syncHidden(total, remise);
  if (window.parseEmoji) parseEmoji(cartEl);
}

function syncHidden(total, remise) {
  const mode = document.getElementById('mode-paiement').value;
  const paye = mode === 'credit' ? 0 : total;
  document.getElementById('montant-paye-h').value = paye;
  // Hidden items
  document.querySelectorAll('input[name^="items["]').forEach(e => e.remove());
  const form = document.getElementById('sale-form');
  let i = 0;
  cart.forEach((v, k) => {
    form.insertAdjacentHTML('beforeend',
      `<input type="hidden" name="items[${i}][product_id]" value="${k}">
       <input type="hidden" name="items[${i}][quantite]" value="${v.qte}">`);
    i++;
  });
}

// Click product = add (toute la card)
items.forEach(d => d.addEventListener('click', () => addToCart(d)));

// Cat chips filtering
document.querySelectorAll('.cat-chip').forEach(c => {
  c.addEventListener('click', e => {
    e.preventDefault();
    document.querySelectorAll('.cat-chip').forEach(x => x.classList.remove('active'));
    c.classList.add('active');
    const cat = c.dataset.cat;
    items.forEach(d => d.style.display = (cat === 'all' || d.dataset.cat === cat) ? '' : 'none');
  });
});

// Customer picker
const sel = document.getElementById('customer-select');
sel.addEventListener('change', e => {
  const opt = e.target.selectedOptions[0];
  const nom = opt.dataset.nom || '';
  const tel = opt.dataset.tel || '';
  const etiq = opt.dataset.etiq || '';
  document.getElementById('client-nom').value = nom;
  document.getElementById('client-tel').value = tel;
  const info = document.getElementById('client-info');
  if (nom) { info.style.display = 'block'; info.innerHTML = `<i class="bi bi-person-check"></i> <strong>${nom}</strong>${etiq ? ' · '+etiq : ''}${tel ? ' · '+tel : ''}`; }
  else info.style.display = 'none';
});

// Payment modes
document.querySelectorAll('.pay-mode').forEach(b => {
  b.addEventListener('click', () => {
    document.querySelectorAll('.pay-mode').forEach(x => x.classList.remove('active'));
    b.classList.add('active');
    document.getElementById('mode-paiement').value = b.dataset.mode;
    // En mode crédit, montant payé = 0 ; sinon = total
    render();
  });
});

// Validation check
document.getElementById('sale-form').addEventListener('submit', e => {
  if (cart.size === 0) { e.preventDefault(); return; }
  const mode = document.getElementById('mode-paiement').value;
  const sel = document.getElementById('customer-select');
  if (mode === 'credit' && !sel.value) {
    e.preventDefault();
    alert('Pour une vente à crédit, sélectionnez un client dans la liste.');
    sel.focus();
  }
});

// Init twemoji on visible cards
if (window.parseEmoji) parseEmoji(document.getElementById('pos-list'));
</script>
@endpush
@endsection
