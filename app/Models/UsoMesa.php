<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class UsoMesa extends Model
{
    use HasFactory;

    protected $table = 'uso_mesas';

    protected $fillable = [
        'mesa_id',
        'cliente_id',
        'user_id',
        'venta_id',
        'hora_inicio',
        'hora_fin',
        'minutos_totales',
        'precio_hora',
        'total',
        'estado',
        'notas'
    ];

    protected $casts = [
        'hora_inicio' => 'datetime',
        'hora_fin' => 'datetime',
        'precio_hora' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function mesa(): BelongsTo
    {
        return $this->belongsTo(Mesa::class);
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function venta(): BelongsTo
    {
        return $this->belongsTo(Venta::class);
    }

    public function scopeEnCurso($query)
    {
        return $query->where('estado', 'en_curso');
    }

    public function scopeHoy($query)
    {
        return $query->whereDate('hora_inicio', today());
    }

    public function estaEnCurso(): bool
    {
        return $this->estado === 'en_curso';
    }

    public function getTiempoTranscurrido()
    {
        $inicio = Carbon::parse($this->hora_inicio);
        $fin = $this->hora_fin ? Carbon::parse($this->hora_fin) : now();
        
        $diff = $inicio->diff($fin);
        
        return [
            'horas' => $diff->h + ($diff->days * 24),
            'minutos' => $diff->i,
            'segundos' => $diff->s,
            'total_minutos' => $inicio->diffInMinutes($fin),
            'formateado' => sprintf('%02d:%02d:%02d', $diff->h + ($diff->days * 24), $diff->i, $diff->s)
        ];
    }

    public function getCostoActual()
    {
        $tiempo = $this->getTiempoTranscurrido();
        $horas = $tiempo['total_minutos'] / 60;
        
        return round($horas * $this->precio_hora, 2);
    }

    public function finalizar(): void
    {
        $this->hora_fin = now();
        $tiempo = $this->getTiempoTranscurrido();
        $this->minutos_totales = $tiempo['total_minutos'];
        $this->total = $this->getCostoActual();
        $this->estado = 'finalizada';
        $this->save();

        // Actualizar estado de la mesa
        $this->mesa->estado = 'disponible';
        $this->mesa->save();
    }

    public function pausar(): void
    {
        if ($this->estado !== 'en_curso') {
            return;
        }

        $this->estado = 'pausada';
        $this->save();
    }

    public function reanudar(): void
    {
        if ($this->estado !== 'pausada') {
            return;
        }

        // Excluir del cronometro el tiempo transcurrido durante la pausa.
        if ($this->updated_at) {
            $segundosPausada = $this->updated_at->diffInSeconds(now());
            $this->hora_inicio = Carbon::parse($this->hora_inicio)->addSeconds($segundosPausada);
        }

        $this->estado = 'en_curso';
        $this->save();
    }
}
