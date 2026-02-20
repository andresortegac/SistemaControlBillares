<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Movimiento extends Model
{
    use HasFactory;

    public const TIPO_CONSUMO = 'CONSUMO';
    public const TIPO_CARGO_PERDEDOR = 'CARGO_PERDEDOR';
    public const TIPO_PAGO = 'PAGO';

    protected $table = 'movimientos';

    protected $fillable = [
        'cuenta_mesa_id',
        'jugador_mesa_id',
        'tipo',
        'monto',
        'descripcion',
        'lote_id',
        'liquidado_en',
        'meta',
    ];

    protected $casts = [
        'monto' => 'decimal:2',
        'liquidado_en' => 'datetime',
        'meta' => 'array',
    ];

    public function cuentaMesa(): BelongsTo
    {
        return $this->belongsTo(CuentaMesa::class);
    }

    public function jugadorMesa(): BelongsTo
    {
        return $this->belongsTo(JugadorMesa::class);
    }
}

