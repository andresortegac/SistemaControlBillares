<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CategoriaGasto extends Model
{
    use HasFactory;

    protected $table = 'categorias_gasto';

    protected $fillable = [
        'nombre',
        'grupo',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public function gastos(): HasMany
    {
        return $this->hasMany(Gasto::class);
    }

    public function scopeActivas($query)
    {
        return $query->where('activo', true);
    }
}
