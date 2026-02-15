# Sistema de Gestión para Billar

Sistema completo de gestión de inventario y control para salas de billar, desarrollado con Laravel 11, PHP 8.2 y MySQL.

## Características

- 🎱 **Gestión de Mesas**: Control de mesas de pool, snooker y carambola con temporizador en tiempo real
- 📦 **Inventario**: Administración de productos, categorías y control de stock
- 💰 **Punto de Venta**: Sistema de ventas con múltiples métodos de pago
- 👥 **Clientes**: Gestión de clientes con sistema de membresías y puntos de fidelidad
- 📊 **Reportes**: Reportes detallados de ventas, productos, uso de mesas y clientes
- 🔐 **Roles de Usuario**: Admin, Gerente y Cajero con diferentes permisos

## Requisitos

- PHP 8.2 o superior
- MySQL 5.7 o superior
- Composer
- Extensiones PHP: mbstring, xml, mysql, curl, zip, bcmath, tokenizer

## Instalación

### 1. Clonar o descargar el proyecto

```bash
cd /ruta/deseada
copia la carpeta billar-system
```

### 2. Instalar dependencias

```bash
cd billar-system
composer install
```

### 3. Configurar el archivo de entorno

```bash
cp .env.example .env
```

Edita el archivo `.env` con tus configuraciones de base de datos:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=billar_db
DB_USERNAME=root
DB_PASSWORD=tu_password
```

### 4. Generar la clave de la aplicación

```bash
php artisan key:generate
```

### 5. Crear la base de datos

Crea una base de datos llamada `billar_db` en tu servidor MySQL.

### 6. Ejecutar migraciones y seeders

```bash
php artisan migrate --seed
```

Esto creará todas las tablas y poblará la base de datos con datos de prueba.

### 7. Iniciar el servidor

```bash
php artisan serve
```

Visita: http://localhost:8000

## Credenciales de Prueba

### Administrador
- Email: `admin@billar.com`
- Password: `password`

### Cajero
- Email: `cajero@billar.com`
- Password: `password`

## Estructura del Proyecto

```
billar-system/
├── app/
│   ├── Http/
│   │   └── Controllers/    # Controladores MVC
│   ├── Models/             # Modelos Eloquent
│   └── Providers/          # Service Providers
├── config/                 # Archivos de configuración
├── database/
│   ├── migrations/         # Migraciones de base de datos
│   └── seeders/            # Seeders para datos de prueba
├── resources/
│   └── views/              # Vistas Blade
├── routes/
│   └── web.php             # Rutas de la aplicación
└── .env                    # Variables de entorno
```

## Funcionalidades Principales

### Dashboard
- Estadísticas en tiempo real
- Mesas en uso con temporizador
- Productos con stock bajo
- Ventas recientes
- Productos más vendidos

### Gestión de Mesas
- Control de estado (disponible, ocupada, mantenimiento, reservada)
- Inicio/pausa/fin de uso
- Cálculo automático de tiempo y costo
- Historial de uso por mesa

### Inventario
- Categorías de productos
- Control de stock mínimo
- Alertas de stock bajo
- Ajuste de inventario

### Ventas
- Punto de venta intuitivo
- Búsqueda rápida de productos
- Descuentos por membresía
- Múltiples métodos de pago
- Impresión de tickets

### Clientes
- Registro de clientes
- Sistema de membresías (Básica, Premium, VIP)
- Puntos de fidelidad
- Historial de compras y visitas

### Reportes
- Ventas por período
- Productos más vendidos
- Uso de mesas
- Clientes frecuentes

## Comandos Útiles

```bash
# Limpiar caché
php artisan cache:clear
php artisan config:clear
php artisan view:clear

# Crear nuevo usuario
php artisan tinker
>>> \App\Models\User::create(['name' => 'Nuevo', 'email' => 'nuevo@billar.com', 'password' => bcrypt('password'), 'rol' => 'cajero'])

# Backup de base de datos
mysqldump -u root -p billar_db > backup.sql
```

## Soporte

Para reportar problemas o solicitar nuevas características, por favor crea un issue en el repositorio.

## Licencia

Este proyecto es de código abierto y está disponible bajo la licencia MIT.
