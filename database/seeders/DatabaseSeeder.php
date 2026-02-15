<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Categoria;
use App\Models\Producto;
use App\Models\Mesa;
use App\Models\Cliente;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Usuario Admin
        User::create([
            'name' => 'Administrador',
            'email' => 'admin@billar.com',
            'password' => Hash::make('password'),
            'rol' => 'admin',
            'telefono' => '1234567890'
        ]);

        // Usuario Cajero
        User::create([
            'name' => 'Cajero',
            'email' => 'cajero@billar.com',
            'password' => Hash::make('password'),
            'rol' => 'cajero',
            'telefono' => '0987654321'
        ]);

        // Categorías
        $categorias = [
            ['nombre' => 'Cervezas', 'tipo' => 'bebida', 'descripcion' => 'Cervezas nacionales e importadas'],
            ['nombre' => 'Refrescos', 'tipo' => 'bebida', 'descripcion' => 'Refrescos y aguas'],
            ['nombre' => 'Botanas', 'tipo' => 'alimento', 'descripcion' => 'Botanas y snacks'],
            ['nombre' => 'Accesorios', 'tipo' => 'accesorio', 'descripcion' => 'Tizas, guantes, etc.'],
        ];

        foreach ($categorias as $cat) {
            Categoria::create($cat);
        }

        // Productos
        $productos = [
            ['codigo' => 'CERV001', 'nombre' => 'Cerveza Corona', 'categoria_id' => 1, 'precio_compra' => 15, 'precio_venta' => 35, 'stock' => 50],
            ['codigo' => 'CERV002', 'nombre' => 'Cerveza Modelo', 'categoria_id' => 1, 'precio_compra' => 16, 'precio_venta' => 38, 'stock' => 40],
            ['codigo' => 'CERV003', 'nombre' => 'Cerveza Pacífico', 'categoria_id' => 1, 'precio_compra' => 14, 'precio_venta' => 32, 'stock' => 45],
            ['codigo' => 'REF001', 'nombre' => 'Coca Cola 600ml', 'categoria_id' => 2, 'precio_compra' => 12, 'precio_venta' => 25, 'stock' => 30],
            ['codigo' => 'REF002', 'nombre' => 'Agua Natural 500ml', 'categoria_id' => 2, 'precio_compra' => 8, 'precio_venta' => 18, 'stock' => 60],
            ['codigo' => 'BOT001', 'nombre' => 'Papas Sabritas', 'categoria_id' => 3, 'precio_compra' => 12, 'precio_venta' => 28, 'stock' => 25],
            ['codigo' => 'BOT002', 'nombre' => 'Cacahuates', 'categoria_id' => 3, 'precio_compra' => 10, 'precio_venta' => 22, 'stock' => 20],
            ['codigo' => 'ACC001', 'nombre' => 'Tiza para Taco', 'categoria_id' => 4, 'precio_compra' => 5, 'precio_venta' => 15, 'stock' => 100],
            ['codigo' => 'ACC002', 'nombre' => 'Guante de Billar', 'categoria_id' => 4, 'precio_compra' => 50, 'precio_venta' => 120, 'stock' => 15],
        ];

        foreach ($productos as $prod) {
            Producto::create($prod + ['stock_minimo' => 10, 'unidad_medida' => 'unidad']);
        }

        // Mesas
        $mesas = [
            ['nombre' => 'Mesa 1', 'tipo' => 'pool', 'precio_por_hora' => 50, 'descripcion' => 'Mesa de Pool estándar'],
            ['nombre' => 'Mesa 2', 'tipo' => 'pool', 'precio_por_hora' => 50, 'descripcion' => 'Mesa de Pool estándar'],
            ['nombre' => 'Mesa 3', 'tipo' => 'pool', 'precio_por_hora' => 50, 'descripcion' => 'Mesa de Pool estándar'],
            ['nombre' => 'Mesa 4', 'tipo' => 'pool', 'precio_por_hora' => 50, 'descripcion' => 'Mesa de Pool estándar'],
            ['nombre' => 'Mesa 5', 'tipo' => 'snooker', 'precio_por_hora' => 80, 'descripcion' => 'Mesa de Snooker profesional'],
            ['nombre' => 'Mesa 6', 'tipo' => 'carambola', 'precio_por_hora' => 70, 'descripcion' => 'Mesa de Carambola'],
        ];

        foreach ($mesas as $mesa) {
            Mesa::create($mesa);
        }

        // Clientes de prueba
        $clientes = [
            ['nombre' => 'Juan Pérez', 'telefono' => '5551234567', 'tipo_membresia' => 'basica'],
            ['nombre' => 'María García', 'telefono' => '5559876543', 'tipo_membresia' => 'premium'],
            ['nombre' => 'Carlos López', 'telefono' => '5554567890', 'tipo_membresia' => 'ninguna'],
            ['nombre' => 'Ana Martínez', 'telefono' => '5557890123', 'tipo_membresia' => 'vip'],
        ];

        foreach ($clientes as $cliente) {
            Cliente::create($cliente);
        }
    }
}
