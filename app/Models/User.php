<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'rol',
        'telefono',
        'activo'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'activo' => 'boolean',
        ];
    }

    public function ventas(): HasMany
    {
        return $this->hasMany(Venta::class);
    }

    public function usosMesas(): HasMany
    {
        return $this->hasMany(UsoMesa::class);
    }

    public function esAdmin(): bool
    {
        return $this->rol === 'admin';
    }

    public function esCajero(): bool
    {
        return $this->rol === 'cajero';
    }

    public function esGerente(): bool
    {
        return $this->rol === 'gerente';
    }

    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    public function getTotalVentasHoyAttribute()
    {
        return $this->ventas()
            ->whereDate('created_at', today())
            ->where('estado', 'pagada')
            ->sum('total');
    }

    public function getCantidadVentasHoyAttribute()
    {
        return $this->ventas()
            ->whereDate('created_at', today())
            ->where('estado', 'pagada')
            ->count();
    }
}
