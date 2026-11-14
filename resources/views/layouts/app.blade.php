<!DOCTYPE html>
<html lang="en" class="light-style layout-menu-fixed" dir="ltr" data-theme="theme-default" data-assets-path="/assets/" data-template="vertical-menu-template-free">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
    <title>@yield('title', 'Nexus ERP & CRM — Enterprise Cloud Platform')</title>

    <meta name="description" content="Nexus ERP & CRM — Multi-Company Enterprise Cloud CRM & Accounting System" />

    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="/assets/img/logo.svg" />

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap" rel="stylesheet" />

    <!-- Icons: Boxicons & FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/boxicons/2.1.4/css/boxicons.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/fonts/boxicons.css') }}" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />

    <!-- Core CSS -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/core.css') }}" class="template-customizer-core-css" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/theme-default.css') }}" class="template-customizer-theme-css" />
    <link rel="stylesheet" href="{{ asset('assets/css/demo.css') }}" />

    <!-- Vendors CSS -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/apex-charts/apex-charts.css') }}" />

    <!-- Premier Theme Palette Strictly Matched with Official Vector Logo -->
    <style>
        :root {
            --bs-primary: #2563eb;
            --bs-primary-rgb: 37, 99, 235;
            --brand-royal: #4338ca;
            --brand-sapphire: #2563eb;
            --brand-electric: #3b82f6;
            --brand-sky: #0284c7;
            --brand-cyan: #06b6d4;
            --brand-emerald: #10b981;
            --brand-dark: #0f172a;
            --brand-midnight: #1e1b4b;
        }

        /* Avatar Precision Fix: Perfect circle without stretching or distortion */
        .avatar {
            width: 38px !important;
            height: 38px !important;
            min-width: 38px !important;
            min-height: 38px !important;
            max-width: 38px !important;
            max-height: 38px !important;
            position: relative;
            border-radius: 50% !important;
            display: inline-flex !important;
            align-items: center;
            justify-content: center;
            flex-shrink: 0 !important;
            overflow: visible !important;
        }
        .avatar img, .avatar-img-fixed {
            width: 38px !important;
            height: 38px !important;
            min-width: 38px !important;
            min-height: 38px !important;
            max-width: 38px !important;
            max-height: 38px !important;
            border-radius: 50% !important;
            object-fit: cover !important;
            object-position: center 20% !important;
            display: block !important;
            border: 2px solid #ffffff !important;
            box-shadow: 0 1px 4px rgba(15, 23, 42, 0.15) !important;
        }
        .avatar .avatar-initial {
            width: 38px !important;
            height: 38px !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            font-size: 0.95rem !important;
        }
        .avatar-online::after {
            bottom: 0px !important;
            right: 0px !important;
            width: 10px !important;
            height: 10px !important;
            background-color: #10b981 !important;
            border: 2px solid #ffffff !important;
            box-shadow: 0 0 0 1px rgba(16, 185, 129, 0.4) !important;
        }

        /* Buttons Matched to Logo Sapphire/Royal Gradient */
        .btn-primary {
            background: linear-gradient(135deg, #4338ca 0%, #2563eb 100%) !important;
            border: none !important;
            color: #ffffff !important;
            font-weight: 600 !important;
            box-shadow: 0 4px 12px 0 rgba(37, 99, 235, 0.35) !important;
            transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1) !important;
        }
        .btn-primary:hover, .btn-primary:focus, .btn-primary:active {
            background: linear-gradient(135deg, #3730a3 0%, #1d4ed8 100%) !important;
            box-shadow: 0 6px 18px 0 rgba(37, 99, 235, 0.45) !important;
            transform: translateY(-1px);
        }
        .btn-outline-primary {
            color: #2563eb !important;
            border-color: #2563eb !important;
            font-weight: 600;
        }
        .btn-outline-primary:hover, .btn-outline-primary:active {
            background: linear-gradient(135deg, #4338ca 0%, #2563eb 100%) !important;
            border-color: #2563eb !important;
            color: #fff !important;
        }

        /* Success & Emerald Action Buttons (Logo Node Accent) */
        .btn-success {
            background: linear-gradient(135deg, #059669 0%, #10b981 100%) !important;
            border: none !important;
            color: #ffffff !important;
            font-weight: 600 !important;
            box-shadow: 0 4px 12px 0 rgba(16, 185, 129, 0.3) !important;
        }
        .btn-success:hover {
            background: linear-gradient(135deg, #047857 0%, #059669 100%) !important;
            box-shadow: 0 6px 16px 0 rgba(16, 185, 129, 0.4) !important;
            transform: translateY(-1px);
        }

        /* Primary Text, Accents & Badges */
        .text-primary { color: #2563eb !important; }
        .bg-primary { background: linear-gradient(135deg, #4338ca 0%, #2563eb 100%) !important; }
        .bg-label-primary {
            background-color: rgba(37, 99, 235, 0.1) !important;
            color: #2563eb !important;
            font-weight: 600;
        }
        .bg-label-success {
            background-color: rgba(16, 185, 129, 0.12) !important;
            color: #059669 !important;
            font-weight: 600;
        }
        .bg-label-info {
            background-color: rgba(2, 132, 199, 0.1) !important;
            color: #0284c7 !important;
            font-weight: 600;
        }
        .border-primary { border-color: #2563eb !important; }

        /* Sidebar Navigation Active Colors */
        .layout-menu .menu-inner > .menu-item.active > .menu-link {
            color: #2563eb !important;
            background: linear-gradient(135deg, rgba(67, 56, 202, 0.08) 0%, rgba(37, 99, 235, 0.12) 100%) !important;
            font-weight: 600 !important;
            box-shadow: 0 2px 6px rgba(37, 99, 235, 0.08);
            border-radius: 8px;
        }
        .layout-menu .menu-inner > .menu-item.active:before {
            background: linear-gradient(180deg, #4338ca 0%, #2563eb 50%, #06b6d4 100%) !important;
            width: 4px !important;
            border-radius: 0 4px 4px 0 !important;
        }
        .layout-menu .menu-inner > .menu-item .menu-icon {
            color: #64748b;
        }
        .layout-menu .menu-inner > .menu-item.active .menu-icon {
            color: #2563eb !important;
        }

        /* Navbar Workspace & Plan Badges */
        .navbar-workspace-badge {
            background: linear-gradient(135deg, rgba(67, 56, 202, 0.08) 0%, rgba(37, 99, 235, 0.1) 100%);
            border: 1px solid rgba(37, 99, 235, 0.22);
            color: #2563eb;
            font-weight: 600;
            border-radius: 8px;
        }
        .navbar-plan-badge {
            background: linear-gradient(135deg, rgba(6, 182, 212, 0.08) 0%, rgba(16, 185, 129, 0.12) 100%);
            border: 1px solid rgba(6, 182, 212, 0.3);
            color: #0284c7;
            font-weight: 700;
            border-radius: 8px;
        }

        /* Input Controls */
        .form-check-input:checked {
            background-color: #2563eb !important;
            border-color: #2563eb !important;
        }
        .form-control:focus, .form-select:focus {
            border-color: #3b82f6 !important;
            box-shadow: 0 0 0 0.2rem rgba(37, 99, 235, 0.15) !important;
        }

        /* Cards, Tables & Badges */
        .badge-status { font-size: 0.82rem; padding: 0.4em 0.8em; text-transform: uppercase; font-weight: 600; }
        .table > :not(caption) > * > * { padding: 0.85rem 1.25rem; vertical-align: middle; }
        .card { box-shadow: 0 2px 10px 0 rgba(15, 23, 42, 0.06); border: none; }
        .btn-icon { width: 34px; height: 34px; display: inline-flex; align-items: center; justify-content: center; padding: 0; border-radius: 7px; }
        .brand-gradient-text {
            background: linear-gradient(135deg, #4338ca 0%, #2563eb 50%, #06b6d4 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        /* Floating Modern Toast Notifications */
        .toast-floating-container {
            position: fixed;
            top: 24px;
            right: 24px;
            z-index: 999999;
            display: flex;
            flex-direction: column;
            gap: 12px;
            pointer-events: none;
        }
        .custom-toaster {
            pointer-events: auto;
            min-width: 330px;
            max-width: 440px;
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.15), 0 1px 3px rgba(0, 0, 0, 0.08);
            display: flex;
            align-items: center;
            padding: 14px 18px;
            gap: 14px;
            transition: all 0.35s cubic-bezier(0.16, 1, 0.3, 1);
            animation: toastSlideIn 0.35s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .custom-toaster.toaster-success {
            border-left: 5px solid #10b981;
        }
        .custom-toaster.toaster-error {
            border-left: 5px solid #ef4444;
        }
        .custom-toaster.toaster-info {
            border-left: 5px solid #2563eb;
        }
        @keyframes toastSlideIn {
            from { transform: translateX(110%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
    </style>

    <!-- Helpers -->
    <script src="{{ asset('assets/vendor/js/helpers.js') }}"></script>
    <script src="{{ asset('assets/js/config.js') }}"></script>
</head>
<body>

<!-- Floating Green Toast Notification Container -->
<div class="toast-floating-container" id="toastFloatingContainer">
    @if(session('success'))
        <div class="custom-toaster toaster-success">
            <div class="avatar avatar-sm bg-label-success rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 38px; height: 38px;">
                <i class="bx bx-check fs-4 text-success"></i>
            </div>
            <div class="flex-grow-1">
                <strong class="d-block text-dark fw-bold mb-0">Success!</strong>
                <span class="text-muted small">{{ session('success') }}</span>
            </div>
            <button type="button" class="btn-close btn-sm" onclick="this.closest('.custom-toaster').remove()"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="custom-toaster toaster-error">
            <div class="avatar avatar-sm bg-label-danger rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 38px; height: 38px;">
                <i class="bx bx-x fs-4 text-danger"></i>
            </div>
            <div class="flex-grow-1">
                <strong class="d-block text-dark fw-bold mb-0">Error!</strong>
                <span class="text-muted small">{{ session('error') }}</span>
            </div>
            <button type="button" class="btn-close btn-sm" onclick="this.closest('.custom-toaster').remove()"></button>
        </div>
    @endif

    @if(session('info'))
        <div class="custom-toaster toaster-info">
            <div class="avatar avatar-sm bg-label-primary rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 38px; height: 38px;">
                <i class="bx bx-info-circle fs-4 text-primary"></i>
            </div>
            <div class="flex-grow-1">
                <strong class="d-block text-dark fw-bold mb-0">Notice</strong>
                <span class="text-muted small">{{ session('info') }}</span>
            </div>
            <button type="button" class="btn-close btn-sm" onclick="this.closest('.custom-toaster').remove()"></button>
        </div>
    @endif
</div>

@auth
    @php
        $user = auth()->user();
        $isSuperAdmin = $user->isSuperAdmin();
        $isCompanyOwner = $user->isCompanyOwner();
        $currentCompany = \App\Models\Company::find($user->current_company_id) ?? $user->currentCompany ?? $user->companies->first();

        $can = function($permissionKey) use ($user) {
            return $user->hasPermission($permissionKey);
        };

        $userRoleTitle = $isSuperAdmin ? 'Super Admin' : ($isCompanyOwner ? 'Company Owner' : ($user->role->name ?? 'Company Owner'));
    @endphp

        <!-- Layout wrapper -->
        <div class="layout-wrapper layout-content-navbar">
            <div class="layout-container">

                <!-- Left Sidebar Menu -->
                <aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
                    <div class="app-brand demo py-3">
                        <a href="{{ route('dashboard') }}" class="app-brand-link gap-2 align-items-center text-decoration-none">
                            <span class="app-brand-logo demo">
                                <img src="/assets/img/logo.svg" width="36" height="36" alt="Nexus Logo" />
                            </span>
                            <span class="app-brand-text demo menu-text fw-bold fs-4 ms-1" style="color: #0f172a; letter-spacing: -0.5px;">
                                NEXUS<span style="color: #2563eb;">ERP</span>
                            </span>
                        </a>

                        <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto d-block d-xl-none">
                            <i class="bx bx-chevron-left bx-sm align-middle"></i>
                        </a>
                    </div>

                    <div class="menu-inner-shadow"></div>

                    <ul class="menu-inner py-1">
                        <!-- Dashboard -->
                        <li class="menu-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                            <a href="{{ route('dashboard') }}" class="menu-link">
                                <i class="menu-icon tf-icons bx bx-home-circle"></i>
                                <div>Dashboard</div>
                            </a>
                        </li>

                        @if($isSuperAdmin)
                            <!-- Super Admin Dedicated Menu -->
                            <li class="menu-header small text-uppercase">
                                <span class="menu-header-text">Super Admin Control</span>
                            </li>
                            <li class="menu-item {{ request()->routeIs('admin.companies') ? 'active' : '' }}">
                                <a href="{{ route('admin.companies') }}" class="menu-link">
                                    <i class="menu-icon tf-icons bx bx-buildings"></i>
                                    <div>All Companies</div>
                                </a>
                            </li>
                            <li class="menu-item {{ request()->routeIs('admin.users*') ? 'active' : '' }}">
                                <a href="{{ route('admin.users') }}" class="menu-link">
                                    <i class="menu-icon tf-icons bx bx-user-check"></i>
                                    <div>System Users</div>
                                </a>
                            </li>
                            <li class="menu-item {{ request()->routeIs('activity.*') ? 'active' : '' }}">
                                <a href="{{ route('activity.index') }}" class="menu-link">
                                    <i class="menu-icon tf-icons bx bx-history"></i>
                                    <div>Activity & Audit Logs</div>
                                </a>
                            </li>
                        @endif

                        <!-- Company Tenant Menu (Scoped & Permission Checked) -->
                        @if(true)
                                @if($can('leads_view') || $can('customers_view'))
                                    <li class="menu-header small text-uppercase">
                                        <span class="menu-header-text">CRM (Sales & Clients)</span>
                                    </li>
                                    @if($can('leads_view'))
                                        <li class="menu-item {{ request()->routeIs('leads.*') ? 'active' : '' }}">
                                            <a href="{{ route('leads.index') }}" class="menu-link">
                                                <i class="menu-icon tf-icons bx bx-target-lock"></i>
                                                <div>Leads Pipeline</div>
                                            </a>
                                        </li>
                                    @endif
                                    @if($can('customers_view'))
                                        <li class="menu-item {{ request()->routeIs('customers.*') ? 'active' : '' }}">
                                            <a href="{{ route('customers.index') }}" class="menu-link">
                                                <i class="menu-icon tf-icons bx bx-user-pin"></i>
                                                <div>Customers Directory</div>
                                            </a>
                                        </li>
                                    @endif
                                @endif

                                <!-- Sales & Billing Section -->
                                @if($can('products_view') || $can('quotes_view') || $can('invoices_view'))
                                    <li class="menu-header small text-uppercase">
                                        <span class="menu-header-text">Sales & Invoicing</span>
                                    </li>
                                    @if($can('products_view'))
                                        <li class="menu-item {{ request()->routeIs('products.*') ? 'active' : '' }}">
                                            <a href="{{ route('products.index') }}" class="menu-link">
                                                <i class="menu-icon tf-icons bx bx-package"></i>
                                                <div>Products & Inventory</div>
                                            </a>
                                        </li>
                                    @endif
                                    @if($can('quotes_view'))
                                        <li class="menu-item {{ request()->routeIs('quotations.*') ? 'active' : '' }}">
                                            <a href="{{ route('quotations.index') }}" class="menu-link">
                                                <i class="menu-icon tf-icons bx bx-file-blank"></i>
                                                <div>Quotations</div>
                                            </a>
                                        </li>
                                    @endif
                                    @if($can('invoices_view'))
                                        <li class="menu-item {{ request()->routeIs('invoices.*') ? 'active' : '' }}">
                                            <a href="{{ route('invoices.index') }}" class="menu-link">
                                                <i class="menu-icon tf-icons bx bx-receipt"></i>
                                                <div>Sales Invoices</div>
                                            </a>
                                        </li>
                                    @endif
                                @endif

                                <!-- Procurement & Supplier Section -->
                                @if($can('vendors_view') || $can('expenses_view') || $isCompanyOwner)
                                    <li class="menu-header small text-uppercase">
                                        <span class="menu-header-text">Procurement & Stock</span>
                                    </li>
                                    <li class="menu-item {{ request()->routeIs('purchases.*') ? 'active' : '' }}">
                                        <a href="{{ route('purchases.index') }}" class="menu-link">
                                            <i class="menu-icon tf-icons bx bx-cart"></i>
                                            <div>Supplier Purchases</div>
                                        </a>
                                    </li>
                                    <li class="menu-item {{ request()->routeIs('supplier_ledger.*') ? 'active' : '' }}">
                                        <a href="{{ route('supplier_ledger.index') }}" class="menu-link">
                                            <i class="menu-icon tf-icons bx bx-file-find"></i>
                                            <div>Supplier Statement</div>
                                        </a>
                                    </li>
                                    @if($can('vendors_view'))
                                        <li class="menu-item {{ request()->routeIs('vendors.*') ? 'active' : '' }}">
                                            <a href="{{ route('vendors.index') }}" class="menu-link">
                                                <i class="menu-icon tf-icons bx bx-store-alt"></i>
                                                <div>Vendors List</div>
                                            </a>
                                        </li>
                                    @endif
                                @endif

                                <!-- Accounting Section -->
                                @if($can('accounts_view') || $can('expenses_view') || $can('reports_view'))
                                    <li class="menu-header small text-uppercase">
                                        <span class="menu-header-text">Accounting & Finance</span>
                                    </li>
                                    @if($can('accounts_view'))
                                        <li class="menu-item {{ request()->routeIs('accounts.*') ? 'active' : '' }}">
                                            <a href="{{ route('accounts.index') }}" class="menu-link">
                                                <i class="menu-icon tf-icons bx bx-wallet"></i>
                                                <div>Accounts</div>
                                            </a>
                                        </li>
                                    @endif
                                    @if($can('expenses_view'))
                                        <li class="menu-item {{ request()->routeIs('expenses.*') ? 'active' : '' }}">
                                            <a href="{{ route('expenses.index') }}" class="menu-link">
                                                <i class="menu-icon tf-icons bx bx-credit-card"></i>
                                                <div>Operating Expenses</div>
                                            </a>
                                        </li>
                                    @endif
                                    @if($can('reports_view'))
                                        <li class="menu-item {{ request()->routeIs('ledger.*') ? 'active' : '' }}">
                                            <a href="{{ route('ledger.index') }}" class="menu-link">
                                                <i class="menu-icon tf-icons bx bx-book-open"></i>
                                                <div>Customer Ledger</div>
                                            </a>
                                        </li>
                                        <li class="menu-item {{ request()->routeIs('reports.*') ? 'active' : '' }}">
                                            <a href="{{ route('reports.profit_loss') }}" class="menu-link">
                                                <i class="menu-icon tf-icons bx bx-line-chart"></i>
                                                <div>Profit & Loss Report</div>
                                            </a>
                                        </li>
                                    @endif
                                @endif

                                <!-- Management Section (Owner & Super Admin) -->
                                @if($isCompanyOwner || $isSuperAdmin)
                                    <li class="menu-header small text-uppercase">
                                        <span class="menu-header-text">Company Management</span>
                                    </li>
                                    <li class="menu-item {{ request()->routeIs('companies.*') ? 'active' : '' }}">
                                        <a href="{{ route('companies.index') }}" class="menu-link">
                                            <i class="menu-icon tf-icons bx bx-building"></i>
                                            <div>My Companies & Plans</div>
                                        </a>
                                    </li>
                                    <li class="menu-item {{ request()->routeIs('team.*') ? 'active' : '' }}">
                                        <a href="{{ route('team.index') }}" class="menu-link">
                                            <i class="menu-icon tf-icons bx bx-group"></i>
                                            <div>Team & Sub-Users</div>
                                        </a>
                                    </li>
                                    <li class="menu-item {{ request()->routeIs('activity.*') ? 'active' : '' }}">
                                        <a href="{{ route('activity.index') }}" class="menu-link">
                                            <i class="menu-icon tf-icons bx bx-shield-quarter"></i>
                                            <div>Activity & Audit Logs</div>
                                        </a>
                                    </li>
                                @endif
                        @endif
                    </ul>
                </aside>
                <!-- / Menu -->

                <!-- Layout Page Container -->
                <div class="layout-page">

                    <!-- Top Navbar -->
                    <nav class="layout-navbar container-xxl navbar navbar-expand-xl navbar-detached align-items-center bg-navbar-theme" id="layout-navbar">
                        <div class="layout-menu-toggle navbar-nav align-items-xl-center me-3 me-xl-0 d-xl-none">
                            <a class="nav-item nav-link px-0 me-xl-4" href="javascript:void(0)">
                                <i class="bx bx-menu bx-sm"></i>
                            </a>
                        </div>

                        <div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">
                            <!-- Workspace Indicator / Switcher -->
                            <div class="navbar-nav align-items-center">
                                @php
                                    $availableCompanies = $user->isSuperAdmin() ? \App\Models\Company::all() : $user->companies;
                                @endphp
                                @if($availableCompanies->count() > 0 || $user->isSuperAdmin())
                                    <div class="nav-item d-flex align-items-center gap-2">
                                        <form action="{{ route('company.switch') }}" method="POST" class="d-flex align-items-center m-0">
                                            @csrf
                                            <div class="d-flex align-items-center px-3 py-1 rounded-3" style="background: #f1f5f9; border: 1px solid #e2e8f0;">
                                                <i class="bx bx-buildings text-primary me-2 fs-5"></i>
                                                <select name="company_id" onchange="this.form.submit()" class="form-select form-select-sm border-0 p-0 fw-bold text-dark shadow-none" style="cursor: pointer; width: auto; background: transparent; font-size: 0.85rem;">
                                                    @if($user->isSuperAdmin())
                                                        <option value="all" {{ !$user->current_company_id ? 'selected' : '' }}>
                                                            🌐 Global Platform View (All Companies)
                                                        </option>
                                                    @endif
                                                    @foreach($availableCompanies as $comp)
                                                        <option value="{{ $comp->id }}" {{ ($user->current_company_id == $comp->id) ? 'selected' : '' }}>
                                                            🏢 {{ $comp->name }} ({{ $comp->currency }})
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </form>

                                        <!-- SaaS Plan Badge -->
                                        @php
                                            $activeComp = $currentCompany ?? $user->companies->first();
                                            $plan = $activeComp->plan ?? 'trial';
                                        @endphp
                                        @if($plan === 'premium')
                                            <span class="badge bg-label-primary px-3 py-2 fw-bold text-uppercase" style="letter-spacing: 0.5px; font-size: 0.72rem;"><i class="bx bx-diamond me-1"></i> Premium Suite</span>
                                        @elseif($plan === 'advance')
                                            <span class="badge bg-label-info px-3 py-2 fw-bold text-uppercase" style="letter-spacing: 0.5px; font-size: 0.72rem;"><i class="bx bx-crown me-1"></i> Advance Pro</span>
                                        @elseif($plan === 'basic')
                                            <span class="badge bg-label-secondary px-3 py-2 fw-bold text-uppercase" style="letter-spacing: 0.5px; font-size: 0.72rem;"><i class="bx bx-shield-alt me-1"></i> Basic Plan</span>
                                        @else
                                            <span class="badge bg-label-warning px-3 py-2 fw-bold text-uppercase" style="letter-spacing: 0.5px; font-size: 0.72rem;"><i class="bx bx-time me-1"></i> 14-Day Trial</span>
                                        @endif
                                    </div>
                                @elseif($user->companies->count() == 0)
                                    <a href="{{ route('companies.index') }}" class="btn btn-sm btn-outline-primary">
                                        <i class="bx bx-plus me-1"></i> Set Up Your Company Workspace
                                    </a>
                                @endif
                            </div>

                            <ul class="navbar-nav flex-row align-items-center ms-auto gap-3">
                                <!-- User Dropdown -->
                                <li class="nav-item navbar-dropdown dropdown-user dropdown">
                                    <a class="nav-link dropdown-toggle hide-arrow d-flex align-items-center" href="javascript:void(0);" data-bs-toggle="dropdown">
                                        <div class="avatar avatar-online me-2">
                                            @if($user->avatar)
                                                <img src="{{ asset($user->avatar) }}" alt="Avatar" class="avatar-img-fixed" />
                                            @else
                                                <span class="avatar-initial rounded-circle bg-primary text-white fw-bold">
                                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                                </span>
                                            @endif
                                        </div>
                                        <div class="d-none d-sm-block text-start">
                                            <span class="fw-semibold d-block lh-1">{{ $user->name }}</span>
                                            <small class="{{ $isSuperAdmin ? 'text-danger' : 'text-primary' }} fw-semibold" style="font-size: 0.72rem;"><i class="bx {{ $isSuperAdmin ? 'bxs-shield' : 'bxs-badge-check' }} me-1"></i>{{ $userRoleTitle }}</small>
                                        </div>
                                    </a>
                                    <ul class="dropdown-menu dropdown-menu-end shadow">
                                        <li>
                                            <a class="dropdown-item" href="{{ route('profile.show') }}">
                                                <div class="d-flex align-items-center">
                                                    <div class="flex-shrink-0 me-3">
                                                        <div class="avatar avatar-online">
                                                            @if($user->avatar)
                                                                <img src="{{ asset($user->avatar) }}" alt="Avatar" class="avatar-img-fixed" />
                                                            @else
                                                                <span class="avatar-initial rounded-circle bg-primary text-white fw-bold">
                                                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                                                </span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                    <div class="flex-grow-1">
                                                        <span class="fw-semibold d-block">{{ $user->name }}</span>
                                                        <small class="text-muted">{{ $user->email }} &bull; <strong class="{{ $isSuperAdmin ? 'text-danger' : 'text-primary' }}">{{ $userRoleTitle }}</strong></small>
                                                    </div>
                                                </div>
                                            </a>
                                        </li>
                                        <li><div class="dropdown-divider"></div></li>
                                        <li>
                                            <a class="dropdown-item" href="{{ route('profile.show') }}">
                                                <i class="bx bx-user me-2"></i>
                                                <span class="align-middle">My Account Profile</span>
                                            </a>
                                        </li>
                                        @if($isCompanyOwner)
                                            <li>
                                                <a class="dropdown-item" href="{{ route('companies.index') }}">
                                                    <i class="bx bx-buildings me-2"></i>
                                                    <span class="align-middle">My Companies & Plans</span>
                                                </a>
                                            </li>
                                        @endif
                                        <li><div class="dropdown-divider"></div></li>
                                        <li>
                                            <form action="{{ route('logout') }}" method="POST">
                                                @csrf
                                                <button type="submit" class="dropdown-item text-danger">
                                                    <i class="bx bx-power-off me-2"></i>
                                                    <span class="align-middle">Log Out</span>
                                                </button>
                                            </form>
                                        </li>
                                    </ul>
                                </li>
                            </ul>
                        </div>
                    </nav>
                    <!-- / Top Navbar -->

                    <!-- Content wrapper -->
                    <div class="content-wrapper">
                        <!-- Main Content Container -->
                        <div class="container-xxl flex-grow-1 container-p-y">
                            @yield('content')
                        </div>
                        <!-- / Container -->

                        <!-- Footer -->
                        <footer class="content-footer footer bg-footer-theme">
                            <div class="container-xxl d-flex flex-wrap justify-content-between py-2 flex-md-row flex-column">
                                <div class="mb-2 mb-md-0 text-muted small">
                                    © {{ date('Y') }} <strong>Nexus ERP & CRM</strong> — Enterprise Cloud Multi-Company Accounting
                                </div>
                                <div class="text-muted small">
                                    Active Workspace: <strong class="text-primary">{{ $currentCompany->name ?? 'Global' }}</strong> ({{ $currentCompany->currency ?? 'USD' }})
                                </div>
                            </div>
                        </footer>

                        <div class="content-backdrop fade"></div>
                    </div>
                    <!-- / Content wrapper -->
                </div>
                <!-- / Layout page -->
            </div>

            <!-- Overlay for mobile hamburger -->
            <div class="layout-overlay layout-menu-toggle"></div>
        </div>
        <!-- / Layout wrapper -->

        <!-- Multi-Company Popup Modal (Only for regular users with > 1 companies; Super Admin is excluded) -->
        @if(!$user->isSuperAdmin() && $user->companies->count() > 1 && session('show_company_select_modal') && !session()->has('company_modal_dismissed'))
            <div class="modal fade show d-block" id="companySelectModal" style="background: rgba(15, 23, 42, 0.75); backdrop-filter: blur(4px);" tabindex="-1" aria-modal="true" role="dialog">
                <div class="modal-dialog modal-dialog-centered" style="max-width: 480px;">
                    <div class="modal-content shadow-2xl border-0 rounded-4 overflow-hidden">
                        <div class="modal-header text-white px-4 py-3" style="background: linear-gradient(135deg, #1e1b4b 0%, #2563eb 100%);">
                            <div class="d-flex align-items-center gap-2">
                                <i class="bx bx-buildings fs-4 text-warning"></i>
                                <div>
                                    <h6 class="modal-title text-white fw-bold mb-0">Select Active Workspace</h6>
                                    <small class="text-white-50" style="font-size: 0.75rem;">Switch between enterprise entities</small>
                                </div>
                            </div>
                            <button type="button" class="btn-close btn-close-white" onclick="dismissCompanyModal()"></button>
                        </div>
                        <div class="modal-body p-4 bg-light">
                            <p class="text-muted small mb-3 text-center">Select the business profile to load dashboard &amp; transactions:</p>
                            <div class="d-grid gap-2">
                                @foreach($user->companies as $comp)
                                    @php $isActive = ($user->current_company_id == $comp->id); @endphp
                                    <form action="{{ route('company.switch') }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="company_id" value="{{ $comp->id }}">
                                        <button type="submit" class="btn w-100 text-start p-3 rounded-3 d-flex align-items-center justify-content-between transition-all" style="background: #ffffff; border: 1.5px solid {{ $isActive ? '#2563eb' : '#e2e8f0' }}; box-shadow: 0 2px 4px rgba(0,0,0,0.04);">
                                            <div class="d-flex align-items-center gap-3">
                                                <div class="rounded-3 p-2 d-flex align-items-center justify-content-center" style="background: {{ $isActive ? 'rgba(37,99,235,0.1)' : '#f1f5f9' }}; width: 44px; height: 44px;">
                                                    <i class="bx bxs-business fs-4 {{ $isActive ? 'text-primary' : 'text-muted' }}"></i>
                                                </div>
                                                <div>
                                                    <div class="fw-bold text-dark fs-6 lh-sm mb-1">{{ $comp->name }}</div>
                                                    <div class="d-flex align-items-center gap-2">
                                                        <span class="badge bg-label-primary px-2 py-0" style="font-size: 0.68rem;">{{ $comp->currency }}</span>
                                                        <span class="text-muted" style="font-size: 0.72rem;">{{ ucfirst($comp->plan ?? 'Enterprise') }} Plan</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div>
                                                @if($isActive)
                                                    <span class="badge bg-primary rounded-pill px-2 py-1" style="font-size: 0.65rem;">Active</span>
                                                @else
                                                    <i class="bx bx-chevron-right fs-4 text-muted"></i>
                                                @endif
                                            </div>
                                        </button>
                                    </form>
                                @endforeach
                            </div>
                        </div>
                        <div class="modal-footer bg-white border-top py-2 px-4 d-flex justify-content-between">
                            <small class="text-muted" style="font-size: 0.72rem;"><i class="bx bx-lock-alt me-1"></i> Multi-Tenant Isolated Scope</small>
                            <button type="button" class="btn btn-sm btn-outline-secondary px-3" onclick="dismissCompanyModal()">Dismiss</button>
                        </div>
                    </div>
                </div>
            </div>
            <script>
                function dismissCompanyModal() {
                    fetch('{{ route("company.dismiss_modal") }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Content-Type': 'application/json'
                        }
                    }).then(() => {
                        const modal = document.getElementById('companySelectModal');
                        if (modal) modal.remove();
                    });
                }
            </script>
        @endif

        <!-- Security OTP Verification Popup Modal for Unverified Registered Users Only (Super Admin Bypasses) -->
        @if(!$isSuperAdmin && !$user->is_otp_verified)
            <div class="modal fade show d-block" id="otpVerificationModal" data-bs-backdrop="static" data-bs-keyboard="false" style="background: rgba(15, 23, 42, 0.85); backdrop-filter: blur(6px); z-index: 1090;" tabindex="-1" aria-modal="true" role="dialog">
                <div class="modal-dialog modal-dialog-centered" style="max-width: 440px;">
                    <div class="modal-content shadow-2xl border-0 rounded-4 overflow-hidden">
                        <div class="modal-header text-white px-4 py-3" style="background: linear-gradient(135deg, #1e1b4b 0%, #2563eb 100%);">
                            <div class="d-flex align-items-center gap-2">
                                <i class="bx bx-shield-quarter fs-3 text-warning"></i>
                                <div>
                                    <h6 class="modal-title text-white fw-bold mb-0">Two-Step Verification</h6>
                                    <small class="text-white-50" style="font-size: 0.72rem;">Enter your 6-digit security code</small>
                                </div>
                            </div>
                        </div>
                        <div class="modal-body p-4 text-center bg-white">
                            <div class="avatar avatar-lg bg-label-primary rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 56px; height: 56px;">
                                <i class="bx bx-key text-primary fs-2"></i>
                            </div>

                            <h5 class="fw-bold mb-1 text-dark">Enter Security OTP</h5>
                            <p class="text-muted small mb-3">
                                We sent a verification code to<br/>
                                <strong class="text-dark">{{ $user->email }}</strong>
                            </p>

                            @php
                                $displayOtp = session('generated_otp') ?? ($user->otp ?? '123456');
                            @endphp
                            <div class="alert alert-primary py-2 px-3 small mb-3 text-center rounded-3" style="background: rgba(37,99,235,0.08); border: 1.5px dashed #2563eb;">
                                <div class="text-muted small mb-1"><i class="bx bx-lock-alt me-1 text-primary"></i> Your Security Verification OTP:</div>
                                <span class="badge bg-primary fs-3 px-3 py-1 font-monospace" style="letter-spacing: 4px;">{{ $displayOtp }}</span>
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

                            <form id="modalOtpForm" action="{{ route('otp.verify') }}" method="POST">
                                @csrf
                                <div class="mb-4">
                                    <label class="form-label fw-semibold text-muted small text-uppercase">Enter 6-Digit Code</label>
                                    <input
                                        type="text"
                                        class="form-control form-control-lg text-center fw-bold fs-2 text-primary shadow-none"
                                        id="modal_otp"
                                        name="otp"
                                        maxlength="6"
                                        value="{{ $displayOtp }}"
                                        placeholder="······"
                                        required
                                        autofocus
                                        style="letter-spacing: 8px; font-family: monospace; border: 2px solid #e2e8f0; border-radius: 10px;"
                                    />
                                </div>
                                <button class="btn btn-primary d-grid w-100 py-2 fw-semibold fs-6 mb-2" type="submit" style="border-radius: 8px;">
                                    <i class="bx bx-check-shield me-1"></i> Verify OTP &amp; Unlock Dashboard
                                </button>
                            </form>

                            <form action="{{ route('logout') }}" method="POST" class="mt-2">
                                @csrf
                                <button type="submit" class="btn btn-link text-muted small text-decoration-none py-0">
                                    <i class="bx bx-log-out me-1"></i> Sign out &amp; return to login
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @endif
@else
    <!-- Guest Layout (Login / Register) - Full Width Centered Container -->
    <div class="d-flex align-items-center justify-content-center min-vh-100 py-5 px-3" style="background: radial-gradient(circle at 10% 20%, rgba(37, 99, 235, 0.08) 0%, transparent 50%), radial-gradient(circle at 90% 80%, rgba(16, 185, 129, 0.08) 0%, transparent 50%), #f8fafc;">
        @yield('content')
    </div>
@endauth

<!-- Core JS -->
<script src="{{ asset('assets/vendor/libs/jquery/jquery.js') }}"></script>
<script src="{{ asset('assets/vendor/libs/popper/popper.js') }}"></script>
<script src="{{ asset('assets/vendor/js/bootstrap.js') }}"></script>
<script src="{{ asset('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js') }}"></script>
<script src="{{ asset('assets/vendor/js/menu.js') }}"></script>

<!-- Vendors JS -->
<script src="{{ asset('assets/vendor/libs/apex-charts/apexcharts.js') }}"></script>

<!-- Main JS -->
<script src="{{ asset('assets/js/main.js') }}"></script>

<!-- Toast Auto-Dismiss & Dynamic Helper Script -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(function() {
        document.querySelectorAll('.custom-toaster').forEach(function(el) {
            el.style.opacity = '0';
            el.style.transform = 'translateX(100%)';
            setTimeout(function() { el.remove(); }, 350);
        });
    }, 4500);
});

function showToast(type, message) {
    const container = document.getElementById('toastFloatingContainer');
    if (!container) return;
    const isSuccess = type === 'success';
    const borderClass = isSuccess ? 'toaster-success' : 'toaster-error';
    const bgBadge = isSuccess ? 'bg-label-success' : 'bg-label-danger';
    const iconClass = isSuccess ? 'bx-check text-success' : 'bx-x text-danger';
    const title = isSuccess ? 'Success!' : 'Notice';

    const toast = document.createElement('div');
    toast.className = `custom-toaster ${borderClass}`;
    toast.innerHTML = `
        <div class="avatar avatar-sm ${bgBadge} rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 38px; height: 38px;">
            <i class="bx ${iconClass} fs-4"></i>
        </div>
        <div class="flex-grow-1">
            <strong class="d-block text-dark fw-bold mb-0">${title}</strong>
            <span class="text-muted small">${message}</span>
        </div>
        <button type="button" class="btn-close btn-sm" onclick="this.closest('.custom-toaster').remove()"></button>
    `;
    container.appendChild(toast);
    setTimeout(function() {
        toast.style.opacity = '0';
        toast.style.transform = 'translateX(100%)';
        setTimeout(function() { toast.remove(); }, 350);
    }, 4500);
}
</script>

@stack('scripts')
</body>
</html>
