<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends Model
{
    protected $fillable = ['nom', 'contact', 'telephone', 'email', 'adresse'];

    public function orders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class);
    }
}
