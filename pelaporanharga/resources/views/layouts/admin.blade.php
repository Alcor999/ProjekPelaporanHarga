<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Panel') - Pelaporan Harga Komoditas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { min-height: 100vh; background: #f8f9fa; }
        .navbar { box-shadow: 0 2px 4px rgba(0,0,0,.1); }
        .card { border: none; transition: transform .2s, box-shadow .2s; }
        .card:hover { box-shadow: 0 4px 12px rgba(0,0,0,.08); }
        .stat-card { transition: transform .2s, box-shadow .2s; }
        .stat-card:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(0,0,0,.12) !important; }
        .sidebar-collapse { max-height: 0; overflow: hidden; }
        @media (max-width: 991px) { .navbar-collapse { max-height: 70vh; overflow-y: auto; } }
        .modal.fade .modal-dialog { transform: scale(0.9); transition: transform 0.2s ease-out; }
        .modal.show .modal-dialog { transform: scale(1); }
        .modal-content { border: none; box-shadow: 0 10px 40px rgba(0,0,0,.2); border-radius: 12px; overflow: hidden; }
        .modal-header { padding: 1rem 1.5rem; }
        .modal-body { padding: 1.5rem; }
        .modal-footer { padding: 1rem 1.5rem; }
        /* Override kontras WCAG AA (>= 4.5:1) untuk status Bootstrap */
        .badge.bg-warning,
        .badge.bg-warning.text-dark,
        .card.bg-warning {
            background-color: #FFF3CD !important;
            color: #5D4037 !important;
        }
        .card.bg-warning .card-title,
        .card.bg-warning small,
        .card.bg-warning i,
        .card.bg-warning h2,
        .card.bg-warning h4 {
            color: #5D4037 !important;
        }
        .badge.bg-info,
        .card.bg-info {
            background-color: #087990 !important;
            color: #FFFFFF !important;
        }
        .text-warning {
            color: #B45309 !important;
        }
    </style>
    @stack('styles')
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="{{ route('admin.dashboard') }}">
                <i class="fas fa-chart-line"></i> Admin - Pelaporan Harga
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('admin.laporan.*') ? 'active' : '' }}" href="{{ route('admin.laporan.index') }}">
                            <i class="fas fa-file-alt"></i> Laporan
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}" href="{{ route('admin.users.petani.index') }}">
                            <i class="fas fa-users"></i> Petani
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle {{ request()->routeIs('admin.wilayah.*') || request()->routeIs('admin.komoditas.*') || request()->routeIs('admin.supplier.*') ? 'active' : '' }}" href="#" data-bs-toggle="dropdown">
                            <i class="fas fa-database"></i> Master Data
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('admin.wilayah.index') }}"><i class="fas fa-map-marker-alt"></i> Wilayah</a></li>
                            <li><a class="dropdown-item" href="{{ route('admin.komoditas.index') }}"><i class="fas fa-seedling"></i> Komoditas</a></li>
                            <li><a class="dropdown-item" href="{{ route('admin.supplier.index') }}"><i class="fas fa-truck"></i> Supplier</a></li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('admin.penyetoran.*') ? 'active' : '' }}" href="{{ route('admin.penyetoran.index') }}">
                            <i class="fas fa-hand-holding-usd"></i> Penyetoran
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('admin.broadcast.*') ? 'active' : '' }}" href="{{ route('admin.broadcast.index') }}">
                            <i class="fas fa-bullhorn"></i> Broadcast
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('admin.statistik.*') ? 'active' : '' }}" href="{{ route('admin.statistik.index') }}">
                            <i class="fas fa-chart-bar"></i> Statistik
                        </a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('public.index') }}" target="_blank">
                            <i class="fas fa-external-link-alt"></i> Lihat Situs
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                            <i class="fas fa-user"></i> {{ auth()->user()->name }}
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><form method="POST" action="{{ route('logout') }}">@csrf<button type="submit" class="dropdown-item"><i class="fas fa-sign-out-alt"></i> Logout</button></form></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid py-4">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @yield('content')
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    @stack('scripts')
</body>
</html>
