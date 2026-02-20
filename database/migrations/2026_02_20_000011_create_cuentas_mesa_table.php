<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cuentas_mesa', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mesa_id')->constrained('mesas')->onDelete('restrict');
            $table->foreignId('abierta_por')->constrained('users')->onDelete('restrict');
            $table->foreignId('cerrada_por')->nullable()->constrained('users')->onDelete('set null');
            $table->enum('estado', ['activa', 'cerrada'])->default('activa');
            $table->timestamp('abierta_en')->useCurrent();
            $table->timestamp('cerrada_en')->nullable();
            $table->text('notas')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cuentas_mesa');
    }
};

