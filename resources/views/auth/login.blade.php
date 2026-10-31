@extends('layouts.app')

@section('title', 'Sign In - Nexus ERP & CRM')

@section('content')
<div class="card shadow-lg border-0 rounded-4" style="max-width: 440px; width: 100%; border: 1px solid rgba(37, 99, 235, 0.12) !important;">
    <div class="card-body p-4 p-sm-5">
        <!-- Brand Logo Header -->
        <div class="text-center mb-4">
            <div class="d-inline-flex align-items-center justify-content-center gap-2 mb-3">
                <img src="{{ asset('assets/img/logo.svg') }}" width="46" height="46" alt="Nexus Logo" />
                <div class="text-start">
                    <span class="fw-bold fs-3 d-block lh-1" style="color: #0f172a; letter-spacing: -0.5px;">
                        NEXUS<span style="color: #2563eb;">ERP</span>
                    </span>
                    <small class="text-muted fw-semibold" style="font-size: 0.72rem; letter-spacing: 1px;">ENTERPRISE CLOUD</small>
                </div>
            </div>

            <h4 class="fw-bold mb-1" style="color: #0f172a;">Welcome Back! 👋</h4>
            <p class="text-muted small mb-0">Sign in to your CRM, ERP & Accounting workspace</p>
        </div>

        @if($errors->any())
            <div class="alert alert-danger py-2 small mb-3">
                <ul class="mb-0 ps-3">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Google OAuth Button -->
        <div class="mb-3">
            <a href="{{ route('oauth.google') }}" class="btn btn-outline-secondary w-100 py-2 d-flex align-items-center justify-content-center fw-semibold text-dark shadow-sm" style="border-radius: 8px; border-color: #e2e8f0; background: #ffffff;">
                <svg class="me-2" width="18" height="18" viewBox="0 0 48 48">
                    <path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"/>
                    <path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z"/>
                    <path fill="#FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z"/>
                    <path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.15 1.45-4.92 2.3-8.16 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z"/>
                </svg>
                Sign In with Google
            </a>
        </div>

        <div class="d-flex align-items-center my-3">
            <hr class="flex-grow-1 m-0">
            <span class="px-3 text-muted small fw-semibold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.5px;">Or sign in with email</span>
            <hr class="flex-grow-1 m-0">
        </div>

        <form action="{{ route('login') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label for="email" class="form-label fw-semibold text-muted small text-uppercase">Email Address</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0 text-muted"><i class="bx bx-envelope fs-5"></i></span>
                    <input
                        type="email"
                        class="form-control border-start-0 ps-1"
                        id="email"
                        name="email"
                        placeholder="owner@domain.com"
                        value="{{ old('email') }}"
                        required
                        autofocus
                    />
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold text-muted small text-uppercase" for="password">Password</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0 text-muted"><i class="bx bx-lock-alt fs-5"></i></span>
                    <input
                        type="password"
                        id="password"
                        class="form-control border-start-0 ps-1"
                        name="password"
                        placeholder="••••••••"
                        required
                    />
                </div>
            </div>

            <div class="mb-4 pt-1">
                <button class="btn btn-primary d-grid w-100 py-2 fw-semibold fs-6" type="submit" style="border-radius: 8px;">
                    <i class="bx bx-log-in me-1"></i> Sign In to Account
                </button>
            </div>
        </form>

        <div class="text-center pt-2 border-top">
            <span class="text-muted small">Don't have an account?</span>
            <a href="{{ route('register') }}" class="fw-semibold small text-primary ms-1">
                Register as Owner
            </a>
        </div>
    </div>
</div>
@endsection
