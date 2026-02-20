<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gastos', function (Blueprint $table) {
            $table->id();
            $table->date('fecha');
            $table->foreignId('categoria_gasto_id')->constrained('categorias_gasto')->onDelete('restrict');
            $table->decimal('monto', 12, 2);
            $table->text('descripcion');
            $table->foreignId('user_id')->constrained('users')->onDelete('restrict');
            $table->string('comprobante_path')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gastos');
    }
};
