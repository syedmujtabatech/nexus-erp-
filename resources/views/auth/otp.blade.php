@extends('layouts.app')

@section('title', 'Verify OTP - Nexus ERP & CRM')

@section('content')
<div class="card shadow-lg border-0 rounded-4" style="max-width: 440px; width: 100%; border: 1px solid rgba(37, 99, 235, 0.12) !important;">
    <div class="card-body p-4 p-sm-5 text-center">
        <!-- Brand Logo Header -->
        <div class="d-inline-flex align-items-center justify-content-center gap-2 mb-3">
            <img src="/assets/img/logo.svg" width="44" height="44" alt="Nexus Logo" />
            <div class="text-start">
                <span class="fw-bold fs-3 d-block lh-1" style="color: #0f172a; letter-spacing: -0.5px;">
                    NEXUS<span style="color: #2563eb;">ERP</span>
                </span>
                <small class="text-muted fw-semibold" style="font-size: 0.72rem; letter-spacing: 1px;">ENTERPRISE CLOUD</small>
            </div>
        </div>

        <!-- Shield Icon Badge -->
        <div class="avatar avatar-lg bg-label-primary rounded-circle mx-auto my-3 d-flex align-items-center justify-content-center" style="width: 56px; height: 56px;">
            <i class="bx bx-shield-quarter text-primary fs-2"></i>
        </div>

        <h4 class="fw-bold mb-1" style="color: #0f172a;">Two-Step Verification</h4>
        <p class="text-muted small mb-3">
            We sent a 6-digit security code to<br/>
            <strong class="text-dark">{{ auth()->user()->email }}</strong>
        </p>

        <div class="alert alert-info py-2 small mb-4 text-start">
            <i class="bx bx-info-circle me-1"></i> Testing Fallback: Enter <strong>123456</strong> or the OTP sent to your email.
        </div>

        @if($errors->any())
            <div class="alert alert-danger py-2 small mb-3 text-start">
                <ul class="mb-0 ps-3">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form id="formOtp" action="{{ route('otp.verify') }}" method="POST">
            @csrf
            <div class="mb-4">
                <label class="form-label fw-semibold text-muted small text-uppercase">Enter 6-Digit Code</label>
                <input
                    type="text"
                    class="form-control form-control-lg text-center fw-bold fs-2 text-primary shadow-none"
                    id="otp"
                    name="otp"
                    maxlength="6"
                    placeholder="······"
                    required
                    autofocus
                    style="letter-spacing: 10px; font-family: monospace; border: 2px solid #e2e8f0; border-radius: 10px;"
                />
            </div>
            <button class="btn btn-primary d-grid w-100 py-2 fw-semibold fs-6 mb-3" type="submit" style="border-radius: 8px;">
                <i class="bx bx-check-shield me-1"></i> Verify & Unlock Dashboard
            </button>
        </form>

        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-link text-muted small text-decoration-none">
                <i class="bx bx-log-out me-1"></i> Sign out & return to login
            </button>
        </form>
    </div>
</div>
@endsection
