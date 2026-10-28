<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'POS') - ระบบ POS</title>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Sarabun', sans-serif; background: #f4f6f9; }
        .sidebar { width: 230px; min-height: 100vh; background: #1f2a44; }
        .sidebar .nav-link { color: #c5cde0; border-radius: .5rem; margin-bottom: .15rem; }
        .sidebar .nav-link:hover, .sidebar .nav-link.active { background: #33415f; color: #fff; }
        .sidebar .section { color: #7f8aa8; font-size: .75rem; text-transform: uppercase; margin: 1rem 0 .3rem .75rem; }
        .card { border: 0; box-shadow: 0 1px 3px rgba(0,0,0,.08); }
        .stat-icon { font-size: 2rem; opacity: .85; }
        @media print { .sidebar, .no-print { display: none !important; } }
    </style>
    @stack('styles')
</head>
<body>
<div class="d-flex">
    <nav class="sidebar p-3 flex-shrink-0 no-print">
        <a href="{{ route('dashboard') }}" class="d-block text-white fs-4 fw-bold text-decoration-none mb-3"><i class="bi bi-shop"></i> ระบบ POS</a>
        <ul class="nav flex-column">
            <li><a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}"><i class="bi bi-speedometer2"></i> แดชบอร์ด</a></li>
            <div class="section">งานขาย</div>
            <li><a class="nav-link {{ request()->routeIs('pos') ? 'active' : '' }}" href="{{ route('pos') }}"><i class="bi bi-cart3"></i> ขายสินค้า (POS)</a></li>
            <li><a class="nav-link {{ request()->routeIs('sales.*') ? 'active' : '' }}" href="{{ route('sales.index') }}"><i class="bi bi-receipt"></i> ประวัติการขาย</a></li>
            <li><a class="nav-link {{ request()->routeIs('members.*') ? 'active' : '' }}" href="{{ route('members.index') }}"><i class="bi bi-people"></i> สมาชิก</a></li>
            <div class="section">คลังสินค้า</div>
            <li><a class="nav-link {{ request()->routeIs('products.*') ? 'active' : '' }}" href="{{ route('products.index') }}"><i class="bi bi-box-seam"></i> สินค้า</a></li>
            <li><a class="nav-link {{ request()->routeIs('stock.movements') ? 'active' : '' }}" href="{{ route('stock.movements') }}"><i class="bi bi-arrow-left-right"></i> ความเคลื่อนไหวสต็อก</a></li>
            @if (auth()->user()->isAdmin())
                <li><a class="nav-link {{ request()->routeIs('categories.*') ? 'active' : '' }}" href="{{ route('categories.index') }}"><i class="bi bi-tags"></i> หมวดหมู่</a></li>
                <div class="section">ผู้ดูแลระบบ</div>
                <li><a class="nav-link {{ request()->routeIs('employees.*') ? 'active' : '' }}" href="{{ route('employees.index') }}"><i class="bi bi-person-badge"></i> พนักงาน</a></li>
                <li><a class="nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}" href="{{ route('reports.index') }}"><i class="bi bi-bar-chart-line"></i> รายงานการขาย</a></li>
            @endif
        </ul>
    </nav>

    <div class="flex-grow-1" style="min-width:0">
        <header class="bg-white border-bottom px-4 py-2 d-flex justify-content-between align-items-center no-print">
            <h5 class="mb-0">@yield('title')</h5>
            <div class="dropdown">
                <a href="#" class="text-decoration-none text-dark dropdown-toggle" data-bs-toggle="dropdown">
                    <i class="bi bi-person-circle"></i> {{ auth()->user()->name }}
                    <span class="badge {{ auth()->user()->isAdmin() ? 'bg-danger' : 'bg-secondary' }}">{{ auth()->user()->isAdmin() ? 'Admin' : 'Cashier' }}</span>
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="{{ route('profile') }}"><i class="bi bi-gear"></i> โปรไฟล์</a></li>
                    <li>
                        <form method="POST" action="{{ route('logout') }}">@csrf
                            <button class="dropdown-item"><i class="bi bi-box-arrow-right"></i> ออกจากระบบ</button>
                        </form>
                    </li>
                </ul>
            </div>
        </header>

        <main class="p-4">
            @if (session('success'))<div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button class="btn-close" data-bs-dismiss="alert"></button></div>@endif
            @if (session('error'))<div class="alert alert-danger alert-dismissible fade show">{{ session('error') }}<button class="btn-close" data-bs-dismiss="alert"></button></div>@endif
            @if ($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif
            @yield('content')
        </main>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>
