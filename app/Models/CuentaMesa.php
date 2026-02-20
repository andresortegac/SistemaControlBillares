<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CuentaMesa extends Model
{
    use HasFactory;

    protected $table = 'cuentas_mesa';

    protected $fillable = [
        'mesa_id',
        'abierta_por',
        'cerrada_por',
        'estado',
        'abierta_en',
        'cerrada_en',
        'notas',
    ];

    protected $casts = [
        'abierta_en' => 'datetime',
        'cerrada_en' => 'datetime',
    ];

    public function mesa(): BelongsTo
    {
        return $this->belongsTo(Mesa::class);
    }

    public function usuarioApertura(): BelongsTo
    {
        return $this->belongsTo(User::class, 'abierta_por');
    }

    public function usuarioCierre(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cerrada_por');
    }

    public function jugadores(): HasMany
    {
        return $this->hasMany(JugadorMesa::class);
    }

    public function movimientos(): HasMany
    {
        return $this->hasMany(Movimiento::class);
    }

    public function scopeActivas($query)
    {
        return $query->where('estado', 'activa');
    }

    public function consumoPendiente(): float
    {
        return (float) $this->movimientos()
            ->where('tipo', Movimiento::TIPO_CONSUMO)
            ->whereNull('jugador_mesa_id')
            ->whereNull('liquidado_en')
            ->sum('monto');
    }

    public function saldoTotalJugadores(): float
    {
        return (float) $this->jugadores
            ->sum(fn (JugadorMesa $jugador) => $jugador->saldo());
    }

    public function puedeCerrar(): bool
    {
        return $this->saldoTotalJugadores() <= 0.00001 && $this->consumoPendiente() <= 0.00001;
    }
}

