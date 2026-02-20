<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Mesa extends Model
{
    use HasFactory;

    protected $table = 'mesas';

    protected $fillable = [
        'nombre',
        'tipo',
        'estado',
        'precio_por_hora',
        'descripcion'
    ];

    protected $casts = [
        'precio_por_hora' => 'decimal:2',
    ];

    public function usos(): HasMany
    {
        return $this->hasMany(UsoMesa::class);
    }

    public function cuentas(): HasMany
    {
        return $this->hasMany(CuentaMesa::class);
    }

    public function cuentaActiva(): HasOne
    {
        return $this->hasOne(CuentaMesa::class)->where('estado', 'activa')->latestOfMany();
    }

    public function usoActivo()
    {
        return $this->usoEnCurso();
    }

    public function usoEnCurso()
    {
        return $this->usos()
            ->where('estado', 'en_curso')
            ->first();
    }

    public function usoPausado()
    {
        return $this->usos()
            ->where('estado', 'pausada')
            ->latest('id')
            ->first();
    }

    public function usoActual()
    {
        return $this->usos()
            ->whereIn('estado', ['en_curso', 'pausada'])
            ->latest('id')
            ->first();
    }

    public function estaOcupada(): bool
    {
        return $this->estado === 'ocupada';
    }

    public function estaDisponible(): bool
    {
        return $this->estado === 'disponible';
    }

    public function getTiempoTranscurridoAttribute()
    {
        $uso = $this->usoActivo();
        if (!$uso) {
            return null;
        }
        return $uso->getTiempoTranscurrido();
    }

    public function getCostoActualAttribute()
    {
        $uso = $this->usoActivo();
        if (!$uso) {
            return 0;
        }
        return $uso->getCostoActual();
    }
}
