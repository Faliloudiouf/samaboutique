<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerPayment extends Model
{
    protected $fillable = ['customer_id', 'sale_id', 'user_id', 'montant', 'mode_paiement', 'notes'];

    protected $casts = ['montant' => 'decimal:2'];

    public function customer(): BelongsTo { return $this->belongsTo(Customer::class); }
    public function sale(): BelongsTo { return $this->belongsTo(Sale::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
}
