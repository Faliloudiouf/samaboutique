<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = ['name', 'email', 'password', 'role', 'actif', 'photo', 'telephone', 'suspended_at'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'actif' => 'boolean',
            'suspended_at' => 'datetime',
        ];
    }

    public function initiales(): string
    {
        $parts = preg_split('/\s+/', trim($this->name));
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
        return $colors[abs(crc32($this->name)) % count($colors)];
    }

    public function isSuspended(): bool
    {
        return $this->suspended_at !== null;
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    public function isGerant(): bool
    {
        return $this->role === 'gerant';
    }

    public function isVendeur(): bool
    {
        return $this->role === 'vendeur';
    }
}
