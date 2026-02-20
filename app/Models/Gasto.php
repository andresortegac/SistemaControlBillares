<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Gasto extends Model
{
    use HasFactory;

    protected $table = 'gastos';

    protected $fillable = [
        'fecha',
        'categoria_gasto_id',
        'monto',
        'descripcion',
        'user_id',
        'comprobante_path',
    ];

    protected $casts = [
        'fecha' => 'date',
        'monto' => 'decimal:2',
    ];

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(CategoriaGasto::class, 'categoria_gasto_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function scopeHoy($query)
    {
        return $query->whereDate('fecha', today());
    }
}
