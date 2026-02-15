<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mesas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 50)->unique();
            $table->enum('tipo', ['pool', 'snooker', 'carambola'])->default('pool');
            $table->enum('estado', ['disponible', 'ocupada', 'mantenimiento', 'reservada'])->default('disponible');
            $table->decimal('precio_por_hora', 10, 2)->default(50.00);
            $table->text('descripcion')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mesas');
    }
};
