<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    protected $fillable = ['nom', 'telephone', 'adresse', 'notes', 'etiquette'];

    public function initiales(): string
    {
        $parts = preg_split('/\s+/', trim($this->nom));
        $i = '';
        foreach ($parts as $p) {
            if ($p !== '') $i .= mb_strtoupper(mb_substr($p, 0, 1));
            if (mb_strlen($i) >= 2) break;
        }
        return $i ?: '?';
    }

    public function couleurAvatar(): string
    {
        $colors = ['#C84B31','#457B9D','#2A9D5C','#946A0F','#7B3F00','#5E548E','#9C3420','#1A8E5F','#D4922A','#3D348B'];
        return $colors[abs(crc32($this->nom)) % count($colors)];
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(CustomerPayment::class);
    }

    /** Total dû = somme(montant_total) - somme(montant_paye sur sales) - somme(remboursements indépendants) */
    public function soldeDu(): float
    {
        $totalVentes = (float)$this->sales()->sum('montant_total');
        $payeSurVentes = (float)$this->sales()->sum('montant_paye');
        $remboursementsLibres = (float)$this->payments()->whereNull('sale_id')->sum('montant');
        return max(0, $totalVentes - $payeSurVentes - $remboursementsLibres);
    }
}
