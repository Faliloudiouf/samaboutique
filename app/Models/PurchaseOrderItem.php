<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseOrderItem extends Model
{
    protected $fillable = ['purchase_order_id', 'product_id', 'quantite', 'prix_unitaire', 'sous_total'];

    protected $casts = [
        'prix_unitaire' => 'decimal:2',
        'sous_total' => 'decimal:2',
    ];

    public function order(): BelongsTo { return $this->belongsTo(PurchaseOrder::class, 'purchase_order_id'); }
    public function product(): BelongsTo { return $this->belongsTo(Product::class); }
}
