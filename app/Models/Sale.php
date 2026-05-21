<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sale extends Model
{
    protected $fillable = [
        'numero', 'user_id', 'customer_id', 'client_nom', 'client_tel',
        'montant_total', 'remise', 'montant_paye', 'mode_paiement', 'statut', 'echeance', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'montant_total' => 'decimal:2',
            'montant_paye' => 'decimal:2',
            'remise' => 'decimal:2',
            'echeance' => 'date',
        ];
    }

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function customer(): BelongsTo { return $this->belongsTo(Customer::class); }
    public function items(): HasMany { return $this->hasMany(SaleItem::class); }
    public function payments(): HasMany { return $this->hasMany(CustomerPayment::class); }

    public function resteAPayer(): float
    {
        return max(0, (float)$this->montant_total - (float)$this->montant_paye);
    }

    public function progressionPaiement(): int
    {
        $t = (float) $this->montant_total;
        if ($t <= 0) return 100;
        return (int) round(((float) $this->montant_paye / $t) * 100);
    }

    public function statutCredit(): string
    {
        if ($this->statut === 'payee') return 'paye';
        if (!$this->echeance) return 'en_cours';
        $diff = now()->startOfDay()->diffInDays($this->echeance, false);
        if ($diff < 0) return 'en_retard';
        if ($diff <= 5) return 'a_venir';
        return 'en_cours';
    }

    public function joursRetard(): int
    {
        if (!$this->echeance || $this->statut === 'payee') return 0;
        return (int) min(0, now()->startOfDay()->diffInDays($this->echeance, false));
    }

    /** Format V-MMDD-NNN (ex: V-2604-087) */
    public static function genererNumero(): string
    {
        $date = now()->format('md');
        $count = static::whereDate('created_at', today())->count() + 1;
        return 'V-' . $date . '-' . str_pad($count, 3, '0', STR_PAD_LEFT);
    }
}
