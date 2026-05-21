<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseOrder extends Model
{
    protected $fillable = [
        'numero', 'supplier_id', 'user_id', 'statut',
        'montant_total', 'date_commande', 'date_reception', 'notes',
    ];

    protected $casts = [
        'date_commande' => 'date',
        'date_reception' => 'date',
        'montant_total' => 'decimal:2',
    ];

    public function supplier(): BelongsTo { return $this->belongsTo(Supplier::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function items(): HasMany { return $this->hasMany(PurchaseOrderItem::class); }

    public static function genererNumero(): string
    {
        $date = now()->format('Ymd');
        $count = static::whereDate('created_at', today())->count() + 1;
        return 'CMD-' . $date . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
    }
}
