<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cliente extends Model
{
    use HasFactory;

    protected $table = 'clientes';

    protected $fillable = [
        'nombre',
        'telefono',
        'email',
        'direccion',
        'fecha_nacimiento',
        'tipo_membresia',
        'puntos_fidelidad',
        'saldo_favor',
        'notas',
        'activo'
    ];

    protected $casts = [
        'fecha_nacimiento' => 'date',
        'puntos_fidelidad' => 'integer',
        'saldo_favor' => 'decimal:2',
        'activo' => 'boolean',
    ];

    public function ventas(): HasMany
    {
        return $this->hasMany(Venta::class);
    }

    public function usosMesas(): HasMany
    {
        return $this->hasMany(UsoMesa::class);
    }

    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    public function getDescuentoMembresiaAttribute()
    {
        return match($this->tipo_membresia) {
            'basica' => 5,
            'premium' => 10,
            'vip' => 15,
            default => 0,
        };
    }

    public function agregarPuntos(int $puntos): void
    {
        $this->puntos_fidelidad += $puntos;
        $this->save();
    }

    public function getTotalVisitasAttribute()
    {
        return $this->usosMesas()->count();
    }

    public function getTotalGastadoAttribute()
    {
        return $this->ventas()->where('estado', 'pagada')->sum('total');
    }
}
