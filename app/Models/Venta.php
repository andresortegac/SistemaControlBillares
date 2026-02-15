<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Venta extends Model
{
    use HasFactory;

    protected $table = 'ventas';

    protected $fillable = [
        'folio',
        'user_id',
        'cliente_id',
        'tipo',
        'subtotal',
        'descuento',
        'total',
        'metodo_pago',
        'efectivo_recibido',
        'cambio',
        'estado',
        'notas'
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'descuento' => 'decimal:2',
        'total' => 'decimal:2',
        'efectivo_recibido' => 'decimal:2',
        'cambio' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($venta) {
            if (empty($venta->folio)) {
                $venta->folio = 'V-' . date('Ymd') . '-' . str_pad(static::count() + 1, 4, '0', STR_PAD_LEFT);
            }
        });
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(DetalleVenta::class);
    }

    public function usoMesa()
    {
        return $this->hasOne(UsoMesa::class);
    }

    public function scopeHoy($query)
    {
        return $query->whereDate('created_at', today());
    }

    public function scopePagadas($query)
    {
        return $query->where('estado', 'pagada');
    }

    public function scopePorPeriodo($query, $fechaInicio, $fechaFin)
    {
        return $query->whereBetween('created_at', [$fechaInicio, $fechaFin]);
    }

    public function estaPagada(): bool
    {
        return $this->estado === 'pagada';
    }

    public function calcularTotales(): void
    {
        $this->subtotal = $this->detalles->sum('subtotal');
        $this->total = $this->subtotal - $this->descuento;
        $this->save();
    }

    public function getCantidadProductosAttribute()
    {
        return $this->detalles->sum('cantidad');
    }
}
