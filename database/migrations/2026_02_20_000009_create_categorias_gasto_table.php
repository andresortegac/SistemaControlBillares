<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categorias_gasto', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100)->unique();
            $table->string('grupo', 60)->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        $now = now();
        DB::table('categorias_gasto')->insert([
            ['nombre' => 'Energia electrica', 'grupo' => 'Servicios', 'activo' => true, 'created_at' => $now, 'updated_at' => $now],
            ['nombre' => 'Agua', 'grupo' => 'Servicios', 'activo' => true, 'created_at' => $now, 'updated_at' => $now],
            ['nombre' => 'Internet', 'grupo' => 'Servicios', 'activo' => true, 'created_at' => $now, 'updated_at' => $now],

            ['nombre' => 'Arriendo', 'grupo' => 'Gastos fijos', 'activo' => true, 'created_at' => $now, 'updated_at' => $now],
            ['nombre' => 'Sueldos', 'grupo' => 'Gastos fijos', 'activo' => true, 'created_at' => $now, 'updated_at' => $now],
            ['nombre' => 'Seguridad', 'grupo' => 'Gastos fijos', 'activo' => true, 'created_at' => $now, 'updated_at' => $now],
            ['nombre' => 'Impuestos', 'grupo' => 'Gastos fijos', 'activo' => true, 'created_at' => $now, 'updated_at' => $now],

            ['nombre' => 'Cambio de panos', 'grupo' => 'Mantenimiento', 'activo' => true, 'created_at' => $now, 'updated_at' => $now],
            ['nombre' => 'Reparacion de mesas', 'grupo' => 'Mantenimiento', 'activo' => true, 'created_at' => $now, 'updated_at' => $now],
            ['nombre' => 'Tacos nuevos', 'grupo' => 'Mantenimiento', 'activo' => true, 'created_at' => $now, 'updated_at' => $now],
            ['nombre' => 'Bolas', 'grupo' => 'Mantenimiento', 'activo' => true, 'created_at' => $now, 'updated_at' => $now],
            ['nombre' => 'Tiza', 'grupo' => 'Mantenimiento', 'activo' => true, 'created_at' => $now, 'updated_at' => $now],
            ['nombre' => 'Bombillos', 'grupo' => 'Mantenimiento', 'activo' => true, 'created_at' => $now, 'updated_at' => $now],

            ['nombre' => 'Cerveza', 'grupo' => 'Inventario', 'activo' => true, 'created_at' => $now, 'updated_at' => $now],
            ['nombre' => 'Gaseosas', 'grupo' => 'Inventario', 'activo' => true, 'created_at' => $now, 'updated_at' => $now],
            ['nombre' => 'Licores', 'grupo' => 'Inventario', 'activo' => true, 'created_at' => $now, 'updated_at' => $now],
            ['nombre' => 'Snacks', 'grupo' => 'Inventario', 'activo' => true, 'created_at' => $now, 'updated_at' => $now],

            ['nombre' => 'Aseo', 'grupo' => 'Otros', 'activo' => true, 'created_at' => $now, 'updated_at' => $now],
            ['nombre' => 'Publicidad', 'grupo' => 'Otros', 'activo' => true, 'created_at' => $now, 'updated_at' => $now],
            ['nombre' => 'Eventos', 'grupo' => 'Otros', 'activo' => true, 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('categorias_gasto');
    }
};
