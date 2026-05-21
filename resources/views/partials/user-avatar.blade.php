{{-- $user (User) ; $size (sm|md|lg|xl) --}}
@php
  $sz = ['xs'=>28,'sm'=>32,'md'=>40,'lg'=>56,'xl'=>96][$size ?? 'md'] ?? 40;
  $fs = max(11, $sz/2.8);
@endphp
@if($user->photo ?? false)
  <img src="{{ asset('storage/'.$user->photo) }}" alt="{{ $user->name }}"
       style="width:{{ $sz }}px;height:{{ $sz }}px;border-radius:50%;object-fit:cover;flex-shrink:0;border:2px solid #fff;box-shadow:0 2px 6px rgba(0,0,0,.1)">
@else
  <div class="av-circle" style="background:{{ $user->couleurAvatar() }};width:{{ $sz }}px;height:{{ $sz }}px;font-size:{{ $fs }}px;border-radius:{{ $sz >= 48 ? '14px' : '50%' }}">
    {{ $user->initiales() }}
  </div>
@endif
