@extends('layouts.app')
@section('title','Reçu '.$sale->numero)
@section('crumbs')
  <span>Caisse</span><span class="sep">›</span><a href="{{ route('sales.index') }}" style="color:inherit;text-decoration:none">Vente {{ $sale->numero }}</a><span class="sep">›</span><span class="here">Reçu</span>
@endsection
@section('topbar-actions')
  <span class="pill pill-success pill-no-dot" style="padding:6px 14px;font-size:11px">✓ VENTE VALIDÉE</span>
@endsection
@section('content')

@php
  $boutique = ['nom'=>'SamaBoutique','sous'=>'Boutique de '.$sale->user->name,'adr'=>'Rufisque · Dakar','tel'=>'77 523 00 72'];
  $rendu = max(0, (float)$sale->montant_paye - (float)$sale->montant_total);
  // Lien public reçu (pour partage)
  $lienRecu = url('/sales/'.$sale->id.'/receipt');
  $waText = "🛍️ *SamaBoutique* — Reçu {$sale->numero}\n"
          . "💰 Total : ".number_format((float)$sale->montant_total,0,',',' ')." FCFA\n"
          . ($sale->statut==='credit' ? "📅 À régler avant le ".$sale->echeance?->format('d/m/Y')."\n" : "")
          . "📄 Reçu PDF : {$lienRecu}\n\nMerci pour votre achat 🙏";
  $waPhone = preg_replace('/\D+/', '', $sale->client_tel ?? '');
  if ($waPhone && !str_starts_with($waPhone, '221')) $waPhone = '221'.$waPhone;
  $waUrl = ($waPhone ? "https://wa.me/{$waPhone}?text=" : "https://wa.me/?text=") . rawurlencode($waText);
  $mailSubject = "Reçu SamaBoutique {$sale->numero}";
  $mailUrl = "mailto:?subject=".rawurlencode($mailSubject)."&body=".rawurlencode($waText);
  $smsUrl = "sms:{$sale->client_tel}?body=".rawurlencode($waText);
@endphp

<div class="receipt-layout">
  <div class="receipt-paper" id="receipt">
    <div class="head">
      <div class="logo"><i class="bi bi-house-door-fill"></i></div>
      <h2>{{ $boutique['nom'] }}</h2>
      <div class="addr">{{ $boutique['sous'] }}<br>{{ $boutique['adr'] }} · {{ $boutique['tel'] }}</div>
    </div>
    <div class="ln"><span class="l">Reçu N°</span><strong>{{ $sale->numero }}</strong></div>
    <div class="ln"><span class="l">Date</span><strong>{{ $sale->created_at->format('d/m/Y') }} · {{ $sale->created_at->format('H:i') }}</strong></div>
    <div class="ln"><span class="l">Vendeur</span><strong>{{ $sale->user->name }}</strong></div>
    <div class="ln"><span class="l">Client</span><strong>{{ $sale->client_nom ?: 'Client occasionnel' }}</strong></div>
    <hr>
    @foreach($sale->items as $it)
      <div class="item-line">
        <span class="it">{{ $it->produit_nom }} × {{ $it->quantite }}</span>
        <span class="money">{{ number_format($it->sous_total,0,',',' ') }}</span>
      </div>
    @endforeach
    <hr>
    <div class="ln"><span class="l">Sous-total</span><span class="money">{{ number_format($sale->montant_total + (float)$sale->remise, 0, ',', ' ') }}</span></div>
    @if($sale->remise > 0)
      <div class="ln" style="color:var(--success)"><span class="l">Remise</span><span class="money">−{{ number_format($sale->remise,0,',',' ') }}</span></div>
    @endif
    <div class="total-line"><span>TOTAL</span><span>{{ number_format($sale->montant_total,0,',',' ') }} F CFA</span></div>
    <div class="ln"><span class="l">Mode de paiement</span><strong style="color:var(--success)">
      @switch($sale->mode_paiement)
        @case('especes') 💵 Espèces @break
        @case('wave') 🌊 Wave @break
        @case('orange_money') 🟠 Orange Money @break
        @case('carte') 💳 Carte @break
      @endswitch
    </strong></div>
    @if($sale->montant_paye > 0)
      <div class="ln"><span class="l">Reçu</span><strong>{{ number_format($sale->montant_paye,0,',',' ') }}</strong></div>
    @endif
    @if($rendu > 0)
      <div class="ln"><span class="l">Rendu</span><strong>{{ number_format($rendu,0,',',' ') }}</strong></div>
    @endif
    @if($sale->statut === 'credit')
      <div class="ln" style="color:var(--danger)"><span class="l">À CRÉDIT</span><strong>À régler avant le {{ $sale->echeance?->format('d/m/Y') }}</strong></div>
    @elseif($sale->statut === 'partielle')
      <div class="ln" style="color:#946A0F"><span class="l">Reste dû</span><strong>{{ number_format($sale->resteAPayer(),0,',',' ') }}</strong></div>
    @endif

    <svg class="qr" width="120" height="120" viewBox="0 0 120 120" style="margin:20px auto 6px">
      {{-- Faux QR esthétique : pattern grid déterministe depuis le numéro --}}
      @php $seed = crc32($sale->numero); @endphp
      <rect width="120" height="120" fill="#fff"/>
      @for($y=0;$y<12;$y++)
        @for($x=0;$x<12;$x++)
          @php $bit = ($seed >> (($x*$y) % 30)) & 1; @endphp
          @if($bit) <rect x="{{ $x*10 }}" y="{{ $y*10 }}" width="10" height="10" fill="#1A0F0A"/> @endif
        @endfor
      @endfor
      <rect x="0" y="0" width="30" height="30" fill="#fff"/><rect x="5" y="5" width="20" height="20" fill="#1A0F0A"/><rect x="10" y="10" width="10" height="10" fill="#fff"/>
      <rect x="90" y="0" width="30" height="30" fill="#fff"/><rect x="95" y="5" width="20" height="20" fill="#1A0F0A"/><rect x="100" y="10" width="10" height="10" fill="#fff"/>
      <rect x="0" y="90" width="30" height="30" fill="#fff"/><rect x="5" y="95" width="20" height="20" fill="#1A0F0A"/><rect x="10" y="100" width="10" height="10" fill="#fff"/>
    </svg>
    <div style="text-align:center;font-size:11px;color:var(--text-muted);font-family:'DM Mono'">{{ url('/r/'.$sale->numero) }}</div>

    <div class="thanks">Merci pour votre achat 🙏<br><span style="font-weight:500;font-size:13px;color:var(--text-muted)">À bientôt dans votre boutique</span></div>
  </div>

  <div style="display:flex;flex-direction:column;gap:16px">
    <div class="success-panel">
      <div class="ic"><i class="bi bi-check-lg"></i></div>
      <div class="lbl">Vente enregistrée</div>
      <div class="ts">{{ $sale->created_at->diffForHumans() }}</div>
      <div class="amt">{{ number_format($sale->montant_total,0,',',' ') }} F CFA</div>
      <div class="sm">{{ $sale->items->sum('quantite') }} article(s) ·
        @switch($sale->mode_paiement)
          @case('especes') Espèces @break
          @case('wave') Wave @break
          @case('orange_money') Orange Money @break
          @case('carte') Carte @break
        @endswitch
        · {{ $sale->client_nom ?: 'Client occasionnel' }}</div>
    </div>

    <div class="send-card">
      <div class="ttl">Diffuser le reçu</div>
      <div class="sub">Le client peut le recevoir par plusieurs canaux</div>

      <a class="send-btn print" href="{{ route('sales.receipt', $sale) }}" target="_blank">
        <i class="bi bi-printer-fill"></i> Imprimer (PDF ticket)
      </a>
      <a class="send-btn wa" href="{{ $waUrl }}" target="_blank">
        <i class="bi bi-whatsapp"></i> Envoyer par WhatsApp
      </a>
      <a class="send-btn" href="{{ $mailUrl }}">
        <i class="bi bi-envelope-fill"></i> Envoyer par email
      </a>
      @if($sale->client_tel)
      <a class="send-btn" href="{{ $smsUrl }}">
        <i class="bi bi-chat-dots-fill"></i> Envoyer par SMS
      </a>
      @endif
    </div>

    <a href="{{ route('sales.create') }}" class="btn btn-primary btn-lg btn-block"><i class="bi bi-plus-lg"></i> Nouvelle vente</a>
    @if($sale->customer_id)
      <a href="{{ route('customers.show', $sale->customer_id) }}" class="btn btn-outline btn-block">
        <i class="bi bi-person-fill"></i> Voir la fiche client
      </a>
    @endif
  </div>
</div>

@push('scripts')
<script>
window.print && (window.onbeforeprint = ()=>document.title='Recu_{{ $sale->numero }}');
</script>
@endpush
@endsection
