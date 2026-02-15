<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Sistema de Billar')</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #1a1a2e;
            --secondary-color: #16213e;
            --accent-color: #0f3460;
            --highlight-color: #e94560;
            --success-color: #00d9a5;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
            --light-bg: #f8f9fa;
        }
        
        * {
            font-family: 'Poppins', sans-serif;
        }
        
        body {
            background-color: var(--light-bg);
        }
        
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(180deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            box-shadow: 4px 0 10px rgba(0,0,0,0.1);
        }
        
        .sidebar .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 12px 20px;
            border-radius: 8px;
            margin: 4px 8px;
            transition: all 0.3s ease;
        }
        
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            background-color: var(--accent-color);
            color: white;
        }
        
        .sidebar .nav-link i {
            margin-right: 10px;
            font-size: 1.1rem;
        }
        
        .main-content {
            padding: 20px;
        }
        
        .navbar {
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.12);
        }
        
        .stat-card {
            background: white;
            border-left: 4px solid var(--highlight-color);
        }
        
        .stat-card.success {
            border-left-color: var(--success-color);
        }
        
        .stat-card.warning {
            border-left-color: var(--warning-color);
        }
        
        .stat-card.info {
            border-left-color: #17a2b8;
        }
        
        .btn-primary {
            background-color: var(--accent-color);
            border-color: var(--accent-color);
        }
        
        .btn-primary:hover {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
        }
        
        .btn-danger {
            background-color: var(--highlight-color);
            border-color: var(--highlight-color);
        }
        
        .table {
            background: white;
            border-radius: 15px;
            overflow: hidden;
        }
        
        .table thead th {
            background-color: var(--primary-color);
            color: white;
            font-weight: 500;
            border: none;
        }
        
        .badge-mesa {
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
        }
        
        .mesa-disponible {
            background-color: #d4edda;
            color: #155724;
        }
        
        .mesa-ocupada {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        .mesa-mantenimiento {
            background-color: #fff3cd;
            color: #856404;
        }
        
        .mesa-reservada {
            background-color: #d1ecf1;
            color: #0c5460;
        }
        
        .mesa-card {
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .mesa-card:hover {
            transform: scale(1.02);
        }
        
        .timer {
            font-family: 'Courier New', monospace;
            font-size: 1.5rem;
            font-weight: bold;
            color: var(--highlight-color);
        }
        
        .login-container {
            min-height: 100vh;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--accent-color) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .login-card {
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            width: 100%;
            max-width: 400px;
        }
        
        .form-control:focus {
            border-color: var(--accent-color);
            box-shadow: 0 0 0 0.2rem rgba(15, 52, 96, 0.25);
        }
        
        .alert {
            border-radius: 10px;
            border: none;
        }
    </style>
    
    @stack('styles')
</head>
<body>
    @auth
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 px-0 sidebar">
                <div class="d-flex flex-column">
                    <div class="p-4 text-center">
                        <h4 class="text-white mb-0">
                            <i class="bi bi-circle-square"></i> Billar Pro
                        </h4>
                    </div>
                    
                    <nav class="nav flex-column">
                        <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                            <i class="bi bi-speedometer2"></i> Dashboard
                        </a>
                        
                        <a class="nav-link {{ request()->routeIs('mesas.*') ? 'active' : '' }}" href="{{ route('mesas.index') }}">
                            <i class="bi bi-grid-3x3"></i> Mesas
                        </a>
                        
                        <a class="nav-link {{ request()->routeIs('ventas.*') ? 'active' : '' }}" href="{{ route('ventas.index') }}">
                            <i class="bi bi-cart3"></i> Ventas
                        </a>
                        
                        <a class="nav-link {{ request()->routeIs('productos.*') ? 'active' : '' }}" href="{{ route('productos.index') }}">
                            <i class="bi bi-box-seam"></i> Productos
                        </a>
                        
                        <a class="nav-link {{ request()->routeIs('categorias.*') ? 'active' : '' }}" href="{{ route('categorias.index') }}">
                            <i class="bi bi-tags"></i> Categorías
                        </a>
                        
                        <a class="nav-link {{ request()->routeIs('clientes.*') ? 'active' : '' }}" href="{{ route('clientes.index') }}">
                            <i class="bi bi-people"></i> Clientes
                        </a>
                        
                        <div class="mt-3 px-3">
                            <small class="text-white-50 text-uppercase">Reportes</small>
                        </div>
                        
                        <a class="nav-link {{ request()->routeIs('reportes.ventas') ? 'active' : '' }}" href="{{ route('reportes.ventas') }}">
                            <i class="bi bi-graph-up"></i> Ventas
                        </a>
                        
                        <a class="nav-link {{ request()->routeIs('reportes.productos') ? 'active' : '' }}" href="{{ route('reportes.productos') }}">
                            <i class="bi bi-box"></i> Productos
                        </a>
                        
                        <a class="nav-link {{ request()->routeIs('reportes.mesas') ? 'active' : '' }}" href="{{ route('reportes.mesas') }}">
                            <i class="bi bi-table"></i> Uso de Mesas
                        </a>
                        
                        @if(auth()->user()->esAdmin())
                        <div class="mt-3 px-3">
                            <small class="text-white-50 text-uppercase">Administración</small>
                        </div>
                        
                        <a class="nav-link {{ request()->routeIs('usuarios.*') ? 'active' : '' }}" href="{{ route('usuarios.index') }}">
                            <i class="bi bi-person-gear"></i> Usuarios
                        </a>
                        @endif
                        
                        <div class="mt-auto p-3">
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-outline-light w-100">
                                    <i class="bi bi-box-arrow-right"></i> Cerrar Sesión
                                </button>
                            </form>
                        </div>
                    </nav>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 main-content">
                <!-- Top Navbar -->
                <nav class="navbar navbar-expand-lg mb-4 rounded">
                    <div class="container-fluid">
                        <span class="navbar-text">
                            Bienvenido, <strong>{{ auth()->user()->name }}</strong>
                            <span class="badge bg-{{ auth()->user()->esAdmin() ? 'danger' : (auth()->user()->esGerente() ? 'warning' : 'info') }} ms-2">
                                {{ ucfirst(auth()->user()->rol) }}
                            </span>
                        </span>
                        <span class="navbar-text">
                            {{ now()->format('d/m/Y H:i') }}
                        </span>
                    </div>
                </nav>
                
                <!-- Alerts -->
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-triangle-fill"></i> {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                
                @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                
                <!-- Page Content -->
                @yield('content')
            </div>
        </div>
    </div>
    @else
        @yield('content')
    @endauth
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Auto-cerrar alertas después de 5 segundos
        setTimeout(() => {
            document.querySelectorAll('.alert').forEach(alert => {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);
    </script>
    
    @stack('scripts')
</body>
</html>
