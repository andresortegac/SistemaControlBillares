<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
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

    public function usoActivo()
    {
        return $this->usos()
            ->where('estado', 'en_curso')
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
