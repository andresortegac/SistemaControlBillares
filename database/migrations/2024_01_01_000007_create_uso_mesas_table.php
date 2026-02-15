<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('uso_mesas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mesa_id')->constrained('mesas')->onDelete('restrict');
            $table->foreignId('cliente_id')->nullable()->constrained('clientes')->onDelete('set null');
            $table->foreignId('user_id')->constrained('users')->onDelete('restrict');
            $table->foreignId('venta_id')->nullable()->constrained('ventas')->onDelete('set null');
            $table->dateTime('hora_inicio');
            $table->dateTime('hora_fin')->nullable();
            $table->integer('minutos_totales')->nullable();
            $table->decimal('precio_hora', 10, 2);
            $table->decimal('total', 10, 2)->nullable();
            $table->enum('estado', ['en_curso', 'pausada', 'finalizada', 'cancelada'])->default('en_curso');
            $table->text('notas')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('uso_mesas');
    }
};
