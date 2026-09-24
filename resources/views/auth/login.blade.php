@extends('layouts.auth')

@section('content')
<div class="auth-login-page" style="background-color: #f4f7fb;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-sm-10 col-md-6 col-lg-4 auth-login-panel">
                <div class="card border-0 shadow-sm auth-login-card" style="border-radius: 14px;">
                    <div class="card-body p-4">
                        <div class="text-center mb-4">
                            <img src="{{ asset(setting('logo', 'images/logo.png')) }}" alt="Logo" style="height: 58px; width: auto;" class="mb-2">
                            <h5 class="fw-bold text-dark mb-1">Portal Admin SPMI</h5>
                            <p class="text-muted small mb-0">Silakan masuk untuk mengelola sistem mutu</p>
                        </div>

                        <form method="POST" action="{{ route('login') }}">
                            @csrf

                            <div class="mb-3">
                                <label for="email" class="form-label small fw-medium text-secondary">{{ __('Alamat Email') }}</label>
                                <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" 
                                       name="email" value="{{ old('email') }}" required autocomplete="email" autofocus
                                       placeholder="nama@lembaga.ac.id">
                                @error('email')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <label for="password" class="form-label small fw-medium text-secondary mb-0">{{ __('Password') }}</label>
                                    @if (Route::has('password.request'))
                                        <a class="text-decoration-none small text-primary" href="{{ route('password.request') }}">
                                            {{ __('Lupa Password?') }}
                                        </a>
                                    @endif
                                </div>
                                <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" 
                                       name="password" required autocomplete="current-password" placeholder="••••••••">
                                @error('password')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                                    <label class="form-check-label small text-muted" for="remember">
                                        {{ __('Ingat Saya') }}
                                    </label>
                                </div>
                            </div>

                            <div class="d-grid pt-1">
                                <button type="submit" class="btn btn-primary fw-semibold" style="background-color: #0f3a8b; border-color: #0f3a8b;">
                                    {{ __('Masuk Sekarang') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="text-center mt-3">
                    <a href="{{ route('home') }}" class="text-decoration-none text-muted small">
                        <i class="fa-solid fa-arrow-left me-1"></i> Kembali ke Halaman Utama
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .auth-login-page {
        min-height: 100vh;
        min-height: 100dvh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 1.5rem;
    }
    .auth-login-card {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .form-control:focus {
        border-color: #0f3a8b;
        box-shadow: 0 0 0 0.2rem rgba(15, 58, 139, 0.15);
    }
</style>
@endsection