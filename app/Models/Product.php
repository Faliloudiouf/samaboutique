<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = [
        'reference', 'nom', 'emoji', 'description', 'category_id',
        'prix_achat', 'prix_vente', 'stock', 'seuil_alerte', 'image', 'actif',
    ];

    public function emojiAffiche(): string
    {
        return $this->emoji ?: ($this->category->emoji ?? '📦');
    }

    protected $casts = [
        'prix_achat' => 'decimal:2',
        'prix_vente' => 'decimal:2',
        'actif' => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function saleItems(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function enRupture(): bool
    {
        return $this->stock <= 0;
    }

    public function enAlerte(): bool
    {
        return $this->stock > 0 && $this->stock <= $this->seuil_alerte;
    }

    public function statutStock(): string
    {
        if ($this->enRupture()) return 'rupture';
        if ($this->enAlerte()) return 'alerte';
        return 'ok';
    }
}
