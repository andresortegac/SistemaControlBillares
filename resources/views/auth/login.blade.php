@extends('layouts.app')

@section('title', 'Iniciar Sesión')

@section('content')
<div class="login-container">
    <div class="login-card">
        <div class="text-center mb-4">
            <i class="bi bi-circle-square" style="font-size: 4rem; color: var(--accent-color);"></i>
            <h3 class="mt-3 mb-1">Billar Pro</h3>
            <p class="text-muted">Sistema de Gestión</p>
        </div>
        
        <form method="POST" action="{{ route('login') }}">
            @csrf
            
            <div class="mb-3">
                <label for="email" class="form-label">Correo Electrónico</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                    <input type="email" class="form-control @error('email') is-invalid @enderror" 
                           id="email" name="email" value="{{ old('email') }}" required autofocus>
                </div>
                @error('email')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="mb-3">
                <label for="password" class="form-label">Contraseña</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                    <input type="password" class="form-control @error('password') is-invalid @enderror" 
                           id="password" name="password" required>
                </div>
                @error('password')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="mb-3 form-check">
                <input type="checkbox" class="form-check-input" id="remember" name="remember">
                <label class="form-check-label" for="remember">Recordarme</label>
            </div>
            
            <button type="submit" class="btn btn-primary w-100 py-2">
                <i class="bi bi-box-arrow-in-right"></i> Iniciar Sesión
            </button>
        </form>
        
        <hr class="my-4">
        
        <div class="text-center text-muted small">
            <p class="mb-1">Credenciales de prueba:</p>
            <p class="mb-0">admin@billar.com / password</p>
        </div>
    </div>
</div>
@endsection
