{{-- $product (Product) ou $image+$nom ; $size (sm|md|lg) ; $rounded --}}
@php
  $img = $product->image ?? ($image ?? null);
  $nom = $product->nom ?? ($nom ?? '?');
  $sizes = ['xs'=>28,'sm'=>40,'md'=>56,'lg'=>80,'xl'=>120];
  $px = $sizes[$size ?? 'sm'] ?? 40;
  $r = $rounded ?? '10px';
@endphp
@if($img)
  <img src="{{ asset('storage/'.$img) }}"
       alt="{{ $nom }}"
       style="width:{{ $px }}px;height:{{ $px }}px;border-radius:{{ $r }};object-fit:cover;border:1.5px solid var(--accent);box-shadow:0 4px 12px rgba(244,185,66,.25);background:#fff">
@else
  <div style="width:{{ $px }}px;height:{{ $px }}px;border-radius:{{ $r }};background:var(--surface-2);color:var(--primary);display:grid;place-items:center;font-weight:800;font-size:{{ max(12, $px/2.5) }}px;font-family:'Plus Jakarta Sans'">
    {{ mb_strtoupper(mb_substr($nom,0,1)) }}
  </div>
@endif
