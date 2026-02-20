<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JugadorMesa extends Model
{
    use HasFactory;

    protected $table = 'jugadores_mesa';

    protected $fillable = [
        'cuenta_mesa_id',
        'nombre',
        'activo',
        'inactivo_en',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'inactivo_en' => 'datetime',
    ];

    public function cuentaMesa(): BelongsTo
    {
        return $this->belongsTo(CuentaMesa::class);
    }

    public function movimientos(): HasMany
    {
        return $this->hasMany(Movimiento::class);
    }

    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    public function saldo(): float
    {
        $cargos = (float) $this->movimientos()
            ->where('tipo', Movimiento::TIPO_CARGO_PERDEDOR)
            ->sum('monto');

        $pagos = (float) $this->movimientos()
            ->where('tipo', Movimiento::TIPO_PAGO)
            ->sum('monto');

        return $cargos - $pagos;
    }
}

