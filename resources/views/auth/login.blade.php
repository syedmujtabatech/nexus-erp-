<!DOCTYPE html>
<html lang="en" class="layout-wide">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
    <title>Enterprise Sign In — Nexus ERP &amp; CRM</title>

    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="{{ asset('assets/img/logo.svg') }}" />

    <!-- Google Fonts & Boxicons -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Public+Sans:wght@400;500;600;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/fonts/boxicons.css') }}" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />

    <!-- Core Bootstrap & Template CSS -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/core.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/theme-default.css') }}" />

    <style>
        :root {
            --brand-primary: #2563eb;
            --brand-dark: #0f172a;
            --brand-royal: #1e1b4b;
        }

        * {
            box-sizing: border-box;
        }

        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: #0b0f19;
            color: #1e293b;
            overflow-x: hidden;
        }

        .auth-wrapper {
            min-height: 100vh;
            display: flex;
            align-items: stretch;
        }

        /* Left Showcase Panel (50%) */
        .showcase-panel {
            flex: 1 1 50%;
            position: relative;
            background: 
                linear-gradient(135deg, rgba(15, 23, 42, 0.92) 0%, rgba(30, 27, 75, 0.88) 100%),
                url("{{ asset('assets/img/backgrounds/nexus-corporate.jpg') }}") center/cover no-repeat;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 3.5rem 4rem;
            color: #ffffff;
            overflow: hidden;
        }

        .showcase-panel::before {
            content: '';
            position: absolute;
            top: -20%;
            left: -20%;
            width: 70%;
            height: 70%;
            background: radial-gradient(circle, rgba(37, 99, 235, 0.25) 0%, transparent 70%);
            filter: blur(40px);
            pointer-events: none;
        }

        .brand-pill {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 6px 14px;
            border-radius: 9999px;
            background: rgba(37, 99, 235, 0.2);
            border: 1px solid rgba(59, 130, 246, 0.35);
            color: #93c5fd;
            font-size: 0.75rem;
            font-weight: 700;
            letter-spacing: 0.8px;
            text-transform: uppercase;
        }

        .feature-item {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 14px;
            padding: 1.1rem 1.25rem;
            margin-bottom: 0.85rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            transition: all 0.25s ease;
        }

        .feature-item:hover {
            background: rgba(255, 255, 255, 0.08);
            border-color: rgba(59, 130, 246, 0.4);
            transform: translateX(4px);
        }

        .feature-icon {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.35rem;
            flex-shrink: 0;
        }

        /* Right Form Panel (50%) */
        .form-panel {
            flex: 1 1 50%;
            background: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 3rem 2.5rem;
        }

        .form-container {
            width: 100%;
            max-width: 420px;
        }

        .btn-google {
            background: #ffffff;
            border: 1.5px solid #e2e8f0;
            color: #0f172a;
            font-weight: 600;
            font-size: 0.92rem;
            padding: 0.72rem 1rem;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            transition: all 0.2s ease;
            text-decoration: none;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }

        .btn-google:hover {
            background: #f8fafc;
            border-color: #cbd5e1;
            color: #0f172a;
            box-shadow: 0 3px 6px rgba(0,0,0,0.08);
            transform: translateY(-1px);
        }

        .btn-submit {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            border: none;
            color: #ffffff;
            font-weight: 600;
            font-size: 0.95rem;
            padding: 0.78rem 1rem;
            border-radius: 10px;
            box-shadow: 0 4px 14px rgba(37, 99, 235, 0.35);
            transition: all 0.2s ease;
        }

        .btn-submit:hover {
            background: linear-gradient(135deg, #1d4ed8 0%, #1e40af 100%);
            color: #ffffff;
            box-shadow: 0 6px 20px rgba(37, 99, 235, 0.45);
            transform: translateY(-1px);
        }

        .input-group-text {
            background-color: #f8fafc;
            border-color: #e2e8f0;
            color: #64748b;
        }

        .form-control {
            border-color: #e2e8f0;
            font-size: 0.92rem;
            padding: 0.72rem 0.85rem;
            color: #0f172a;
            font-weight: 500;
        }

        .form-control:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
        }

        @media (max-width: 991.98px) {
            .showcase-panel {
                display: none !important;
            }
            .form-panel {
                flex: 1 1 100%;
                padding: 2.5rem 1.5rem;
            }
        }
    </style>
</head>
<body>

<div class="auth-wrapper">
    <!-- Left Showcase Panel -->
    <div class="showcase-panel d-none d-lg-flex">
        <!-- Top Logo & Badges -->
        <div>
            <div class="d-flex align-items-center gap-3 mb-4">
                <img src="{{ asset('assets/img/logo.svg') }}" width="48" height="48" alt="Nexus Logo" style="filter: drop-shadow(0 4px 12px rgba(37, 99, 235, 0.5));" />
                <div>
                    <h3 class="mb-0 fw-bold text-white lh-1" style="letter-spacing: -0.5px;">
                        NEXUS<span style="color: #60a5fa;">ERP</span>
                    </h3>
                    <small class="text-white-50 fw-semibold" style="font-size: 0.72rem; letter-spacing: 1.2px; text-transform: uppercase;">
                        Enterprise Cloud Platform
                    </small>
                </div>
            </div>

            <div class="mb-4">
                <span class="brand-pill mb-3">
                    <i class="bx bxs-shield-check"></i> Multi-Tenant SaaS Cloud
                </span>
                <h1 class="fw-bolder text-white mt-3 mb-3 lh-sm" style="font-size: 2.15rem; letter-spacing: -0.5px;">
                    Enterprise CRM, ERP &amp; Financial Management
                </h1>
                <p class="text-white-50 mb-0" style="font-size: 0.94rem; line-height: 1.6; max-width: 520px;">
                    Centralized platform for customer pipelines, commercial invoicing, supply chain purchases, and real-time double-entry ledgers.
                </p>
            </div>

            <!-- Highlights -->
            <div class="mt-4" style="max-width: 520px;">
                <div class="feature-item">
                    <div class="feature-icon bg-primary text-white">
                        <i class="bx bx-user-voice"></i>
                    </div>
                    <div>
                        <h6 class="text-white mb-0 fw-semibold fs-6">Enterprise CRM &amp; Leads Pipeline</h6>
                        <small class="text-white-50" style="font-size: 0.78rem;">Track customer life-cycle from cold lead to quotation &amp; conversion</small>
                    </div>
                </div>

                <div class="feature-item">
                    <div class="feature-icon text-white" style="background: #0284c7;">
                        <i class="bx bx-receipt"></i>
                    </div>
                    <div>
                        <h6 class="text-white mb-0 fw-semibold fs-6">Automated Commercial Invoicing</h6>
                        <small class="text-white-50" style="font-size: 0.78rem;">Sub-second invoice creation, PDF generation &amp; WhatsApp share</small>
                    </div>
                </div>

                <div class="feature-item">
                    <div class="feature-icon text-white" style="background: #059669;">
                        <i class="bx bx-calculator"></i>
                    </div>
                    <div>
                        <h6 class="text-white mb-0 fw-semibold fs-6">Double-Entry Financial Accounting</h6>
                        <small class="text-white-50" style="font-size: 0.78rem;">Real-time Bank &amp; Cash ledgers, operating expenses, and P&amp;L</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bottom Status -->
        <div class="pt-4 border-top border-white-10 d-flex justify-content-between align-items-center" style="max-width: 520px;">
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-success rounded-pill p-1" style="width: 8px; height: 8px;"></span>
                <small class="text-white-50" style="font-size: 0.76rem;">Enterprise Engine Active • Multi-Company Ready</small>
            </div>
            <small class="text-white-50" style="font-size: 0.76rem;">v2.4 Enterprise Edition</small>
        </div>
    </div>

    <!-- Right Form Panel -->
    <div class="form-panel">
        <div class="form-container">
            <!-- Mobile Brand Header -->
            <div class="text-center d-lg-none mb-4">
                <img src="{{ asset('assets/img/logo.svg') }}" width="42" height="42" alt="Nexus Logo" />
                <h4 class="fw-bold mb-0 mt-2 text-dark">NEXUS<span class="text-primary">ERP</span></h4>
                <small class="text-muted text-uppercase" style="font-size: 0.72rem; letter-spacing: 1px;">Enterprise Cloud Platform</small>
            </div>

            <!-- Header -->
            <div class="mb-4">
                <h3 class="fw-bold mb-1 text-dark" style="letter-spacing: -0.5px;">Welcome Back! 👋</h3>
                <p class="text-muted small mb-0">Sign in to your CRM, ERP &amp; Accounting workspace</p>
            </div>

            @if(session('success'))
                <div class="alert alert-success py-2 small mb-3 d-flex align-items-center">
                    <i class="bx bx-check-circle fs-5 me-2"></i>
                    <div>{{ session('success') }}</div>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger py-2 small mb-3">
                    <ul class="mb-0 ps-3">
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- 1-Click Google OAuth SSO Button -->
            <div class="mb-3">
                <a href="{{ route('oauth.google') }}" class="btn-google w-100">
                    <svg width="18" height="18" viewBox="0 0 48 48">
                        <path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"/>
                        <path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z"/>
                        <path fill="#FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z"/>
                        <path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.15 1.45-4.92 2.3-8.16 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z"/>
                    </svg>
                    <span>Sign In with Google</span>
                </a>
            </div>

            <!-- Divider -->
            <div class="d-flex align-items-center my-3">
                <hr class="flex-grow-1 m-0" style="border-color: #e2e8f0;">
                <span class="px-3 text-muted small fw-semibold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.5px;">Or sign in with email</span>
                <hr class="flex-grow-1 m-0" style="border-color: #e2e8f0;">
            </div>

            <!-- Standard Auth Form -->
            <form action="{{ route('login') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label for="email" class="form-label fw-semibold text-dark small mb-1">Email Address</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bx bx-envelope fs-5"></i></span>
                        <input
                            type="email"
                            class="form-control"
                            id="email"
                            name="email"
                            value="{{ old('email', 'syedmujtaba.dev@gmail.com') }}"
                            placeholder="admin@nexus-erp.com"
                            required
                            autofocus
                        />
                    </div>
                </div>

                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <label class="form-label fw-semibold text-dark small mb-0" for="password">Password</label>
                    </div>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bx bx-lock-alt fs-5"></i></span>
                        <input
                            type="password"
                            id="password"
                            class="form-control"
                            name="password"
                            value="mujtaba123"
                            placeholder="••••••••"
                            required
                        />
                    </div>
                </div>

                <div class="mb-4 pt-1">
                    <button class="btn btn-submit w-100" type="submit">
                        <i class="bx bx-log-in me-1"></i> Sign In to Account
                    </button>
                </div>
            </form>

            <div class="text-center pt-3 border-top">
                <small class="text-muted d-flex align-items-center justify-content-center gap-1" style="font-size: 0.74rem;">
                    <i class="bx bx-shield-quarter text-success"></i> 256-Bit SSL Encrypted • Role-Based Access Control
                </small>
            </div>
        </div>
    </div>
</div>

</body>
</html>
