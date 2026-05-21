@extends('layouts.app')
@section('title','Remboursement '.$customer->nom)
@section('crumbs')
  <span>Crédits</span><span class="sep">›</span><a href="{{ route('customers.show', $customer) }}" style="color:inherit;text-decoration:none">{{ $customer->nom }}</a><span class="sep">›</span><span class="here">Remboursement</span>
@endsection
@section('topbar-actions')
  <span class="pill pill-primary pill-no-dot" style="padding:6px 14px;font-size:11px">REMBOURSEMENT</span>
@endsection
@section('content')

@php
  $venteEnCours = $customer->sales->first();
  $stCredit = $venteEnCours ? $venteEnCours->statutCredit() : 'paye';
@endphp

<div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;align-items:start;max-width:1200px;margin:0 auto">
  <div class="card-soft">
    <h2 style="margin:0 0 4px;font-size:22px">Dette en cours</h2>
    @if($venteEnCours)
      <div class="muted" style="font-size:13px;margin-bottom:18px">Crédit ouvert le {{ $venteEnCours->created_at->isoFormat('D MMMM YYYY') }}</div>
    @endif

    <div style="background:#FFF7F2;border:1px solid var(--surface-2);border-radius:14px;padding:14px;display:flex;align-items:center;gap:14px;margin-bottom:18px">
      <div class="av-circle lg" style="background:{{ $customer->couleurAvatar() }}">{{ $customer->initiales() }}</div>
      <div style="flex:1;min-width:0">
        <div style="font-weight:800;font-size:16px">{{ $customer->nom }}</div>
        <div class="muted" style="font-size:12px">{{ $customer->telephone ? $customer->telephone : '—' }}{{ $customer->etiquette ? ' · '.$customer->etiquette : '' }}</div>
      </div>
      @if($stCredit==='en_retard')
        <span class="pill pill-danger">en retard</span>
      @elseif($stCredit==='a_venir')
        <span class="pill pill-warning">à venir {{ $venteEnCours->echeance?->isoFormat('D MMM') }}</span>
      @elseif($solde > 0)
        <span class="pill pill-info">en cours</span>
      @else
        <span class="pill pill-success">à jour</span>
      @endif
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:14px">
      <div style="background:var(--surface-2);border-radius:12px;padding:14px">
        <div class="label" style="font-size:11px;color:var(--text-muted);font-weight:800;text-transform:uppercase;letter-spacing:.06em">Total dû initial</div>
        <div class="money" style="font-size:22px;margin-top:4px">{{ number_format($totalInitial,0,',',' ') }} <small style="font-size:13px;color:var(--text-muted)">F</small></div>
      </div>
      <div style="background:var(--success-soft);border-radius:12px;padding:14px">
        <div class="label" style="font-size:11px;color:#1a5f3a;font-weight:800;text-transform:uppercase;letter-spacing:.06em">Déjà remboursé</div>
        <div class="money" style="font-size:22px;margin-top:4px;color:#1a5f3a">{{ number_format($dejaPaye,0,',',' ') }} <small style="font-size:13px">F</small></div>
      </div>
    </div>

    <div style="background:#1A0F0A;color:#fff;border-radius:14px;padding:18px;margin-bottom:18px">
      <div style="display:flex;justify-content:space-between;align-items:baseline;margin-bottom:6px">
        <span style="font-size:13px;color:var(--text-soft)">Reste à payer</span>
        <span style="font-size:12px;color:var(--accent);font-weight:700">{{ $progression }}% remboursé</span>
      </div>
      <div class="money" style="font-size:36px;color:#fff">{{ number_format($solde,0,',',' ') }} <small style="font-size:16px;color:var(--text-soft)">F CFA</small></div>
      <div class="progress-bar-c" style="margin-top:14px"><div style="width:{{ $progression }}%"></div></div>
    </div>

    <div class="label">Historique des paiements</div>
    @if($customer->payments->isEmpty())
      <div class="muted" style="text-align:center;padding:14px;font-size:13px">Aucun remboursement</div>
    @else
      @foreach($customer->payments as $p)
        <div style="display:flex;justify-content:space-between;align-items:center;padding:10px 0;border-bottom:1px solid var(--border)">
          <div>
            <strong>{{ $p->created_at->isoFormat('D MMM') }}</strong>
            <span class="muted" style="font-size:13px"> · @switch($p->mode_paiement)
              @case('especes') Espèces @break
              @case('wave') Wave @break
              @case('orange_money') Orange Money @break
              @case('carte') Carte @break
            @endswitch</span>
          </div>
          <span class="money" style="color:var(--success)">+{{ number_format($p->montant,0,',',' ') }} F</span>
        </div>
      @endforeach
    @endif
  </div>

  <form method="POST" action="{{ route('customers.payment', $customer) }}" class="card-soft" id="rb-form">
    @csrf
    <h2 style="margin:0 0 4px;font-size:22px">Encaisser un remboursement</h2>
    <div class="muted" style="font-size:13px;margin-bottom:20px">Saisissez le montant et le mode de paiement</div>

    <div>
      <label class="form-label">Montant à rembourser</label>
      <div style="position:relative">
        <input type="number" name="montant" id="rb-montant" class="form-control" min="1" max="{{ (int)$solde }}" required step="any"
               style="font-family:'Plus Jakarta Sans';font-weight:800;font-size:24px;padding:18px 80px 18px 18px;height:auto" placeholder="0">
        <span style="position:absolute;right:18px;top:50%;transform:translateY(-50%);color:var(--text-muted);font-weight:700;font-size:14px">F CFA</span>
      </div>
      <div class="amt-shortcuts">
        <button type="button" class="amt-shortcut" data-amt="1000">1 000 F</button>
        <button type="button" class="amt-shortcut" data-amt="2500">2 500 F</button>
        <button type="button" class="amt-shortcut" data-amt="5000">5 000 F</button>
        <button type="button" class="amt-shortcut" data-amt="{{ (int)$solde }}">Tout ({{ number_format($solde,0,',',' ') }})</button>
      </div>
    </div>

    <div style="margin-top:22px">
      <label class="form-label">Mode de paiement</label>
      <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:8px">
        <label class="pay-mode active"><input type="radio" name="mode_paiement" value="especes" checked style="display:none"><span class="em">💵</span>Espèces</label>
        <label class="pay-mode"><input type="radio" name="mode_paiement" value="wave" style="display:none"><span class="em">🌊</span>Wave</label>
        <label class="pay-mode"><input type="radio" name="mode_paiement" value="orange_money" style="display:none"><span class="em">🟠</span>Orange</label>
        <label class="pay-mode"><input type="radio" name="mode_paiement" value="carte" style="display:none"><span class="em">💳</span>Carte</label>
      </div>
    </div>

    <div style="margin-top:22px">
      <label class="form-label">Date du paiement</label>
      <div class="form-control" style="display:flex;align-items:center;gap:8px;background:var(--surface-2);border-color:var(--border)">
        <i class="bi bi-calendar3"></i>
        <span class="money">{{ now()->format('d / m / Y') }} · {{ now()->format('H:i') }}</span>
      </div>
    </div>

    <div style="background:var(--success-soft);border-radius:14px;padding:16px;margin-top:22px;display:flex;justify-content:space-between;align-items:center">
      <div>
        <div style="font-size:11px;color:#1a5f3a;font-weight:800;text-transform:uppercase;letter-spacing:.06em">Après remboursement</div>
        <div class="money" style="font-size:22px;color:#1a5f3a;margin-top:4px"><span id="rb-rest">{{ number_format($solde,0,',',' ') }}</span> F restants</div>
      </div>
      <div style="text-align:right">
        <div style="font-size:11px;color:#1a5f3a;font-weight:800;text-transform:uppercase;letter-spacing:.06em">Progression</div>
        <div class="money" style="font-size:22px;color:#1a5f3a;margin-top:4px"><span id="rb-prog">{{ $progression }}</span>%</div>
      </div>
    </div>

    <button type="submit" class="btn btn-success btn-lg btn-block" style="margin-top:22px"><i class="bi bi-check-circle-fill"></i> Confirmer le remboursement</button>
  </form>
</div>

@push('scripts')
<script>
const solde = {{ (int) $solde }};
const initial = {{ (int) $totalInitial }};
const dejaPaye = {{ (int) $dejaPaye }};
const fmt = n => new Intl.NumberFormat('fr-FR').format(Math.max(0,Math.round(n)));

document.querySelectorAll('.amt-shortcut').forEach(b => {
  b.addEventListener('click', () => {
    document.querySelectorAll('.amt-shortcut').forEach(x => x.classList.remove('active'));
    b.classList.add('active');
    document.getElementById('rb-montant').value = b.dataset.amt;
    updatePreview();
  });
});

document.getElementById('rb-montant').addEventListener('input', updatePreview);

function updatePreview() {
  const m = Math.min(solde, Math.max(0, +document.getElementById('rb-montant').value || 0));
  const reste = solde - m;
  const prog = initial > 0 ? Math.round(((dejaPaye + m) / initial) * 100) : 0;
  document.getElementById('rb-rest').textContent = fmt(reste);
  document.getElementById('rb-prog').textContent = prog;
}

document.querySelectorAll('.pay-mode').forEach(b => {
  b.addEventListener('click', () => {
    document.querySelectorAll('.pay-mode').forEach(x => x.classList.remove('active'));
    b.classList.add('active');
    b.querySelector('input').checked = true;
  });
});
</script>
@endpush
@endsection
