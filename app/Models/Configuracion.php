<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Configuracion extends Model
{
    use HasFactory;

    protected $table = 'configuracion';

    protected $fillable = [
        'clave',
        'valor',
        'descripcion',
        'tipo'
    ];

    public $timestamps = true;

    public static function get($clave, $default = null)
    {
        $config = static::where('clave', $clave)->first();
        
        if (!$config) {
            return $default;
        }

        return match($config->tipo) {
            'numero' => (float) $config->valor,
            'booleano' => (bool) $config->valor,
            'json' => json_decode($config->valor, true),
            default => $config->valor,
        };
    }

    public static function set($clave, $valor, $descripcion = null, $tipo = 'texto'): void
    {
        $valorGuardar = match($tipo) {
            'json' => json_encode($valor),
            'booleano' => $valor ? '1' : '0',
            default => (string) $valor,
        };

        static::updateOrCreate(
            ['clave' => $clave],
            [
                'valor' => $valorGuardar,
                'descripcion' => $descripcion,
                'tipo' => $tipo
            ]
        );
    }

    public static function getPrecioDefaultMesa()
    {
        return static::get('precio_default_mesa', 50.00);
    }

    public static function getNombreNegocio()
    {
        return static::get('nombre_negocio', 'Billar Premium');
    }

    public static function getImpuesto()
    {
        return static::get('impuesto_porcentaje', 16);
    }
}
