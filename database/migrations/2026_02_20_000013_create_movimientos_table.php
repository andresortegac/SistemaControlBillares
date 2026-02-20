<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('movimientos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cuenta_mesa_id')->constrained('cuentas_mesa')->onDelete('restrict');
            $table->foreignId('jugador_mesa_id')->nullable()->constrained('jugadores_mesa')->onDelete('restrict');
            $table->enum('tipo', ['CONSUMO', 'CARGO_PERDEDOR', 'PAGO']);
            $table->decimal('monto', 12, 2);
            $table->string('descripcion', 150)->nullable();
            $table->uuid('lote_id')->nullable();
            $table->timestamp('liquidado_en')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('movimientos');
    }
};

