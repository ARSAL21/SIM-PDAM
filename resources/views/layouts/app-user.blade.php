<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'SIM-PDAM') }} — @yield('title', 'Dashboard')</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Lora:ital,wght@0,400;0,600;0,700;1,400&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --navy:  #0D2B45;
            --teal:  #0C6B72;
            --aqua:  #1AACB4;
            --sky:   #7DD3DA;
            --cream: #F4EFE6;
            --sand:  #E8DFD0;
            --white: #FDFAF6;
            --text:  #1C2E3A;
            --muted: #5A7080;
            --sidebar-w: 256px;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: var(--cream);
            color: var(--text);
        }

        /* ─── SIDEBAR ─────────────────────────────── */
        .sidebar {
            width: var(--sidebar-w);
            background: var(--navy);
            position: fixed;
            top: 0; left: 0; bottom: 0;
            z-index: 50;
            display: flex;
            flex-direction: column;
            transition: transform 0.3s cubic-bezier(0.4,0,0.2,1);
            overflow: hidden;
        }

        /* subtle grid texture */
        .sidebar::before {
            content: '';
            position: absolute;
            inset: 0;
            background-image:
                linear-gradient(rgba(26,172,180,0.04) 1px, transparent 1px),
                linear-gradient(90deg, rgba(26,172,180,0.04) 1px, transparent 1px);
            background-size: 32px 32px;
            pointer-events: none;
        }

        /* ambient glow */
        .sidebar::after {
            content: '';
            position: absolute;
            width: 300px; height: 300px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(12,107,114,0.2), transparent 70%);
            top: -80px; right: -100px;
            pointer-events: none;
        }

        .sidebar-inner {
            position: relative;
            z-index: 1;
            display: flex;
            flex-direction: column;
            height: 100%;
            padding: 0;
        }

        /* Brand */
        .sidebar-brand {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 1.5rem 1.25rem 1.25rem;
            border-bottom: 1px solid rgba(255,255,255,0.07);
            text-decoration: none;
        }

        .sidebar-logo {
            width: 36px; height: 36px;
            border-radius: 10px;
            background: linear-gradient(135deg, var(--aqua), var(--teal));
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
            box-shadow: 0 4px 12px rgba(26,172,180,0.3);
        }

        .sidebar-logo svg { width: 20px; height: 20px; fill: white; }

        .sidebar-brand-text { display: flex; flex-direction: column; }
        .sidebar-app-name {
            font-family: 'Lora', serif;
            font-size: 0.9rem;
            font-weight: 700;
            color: white;
            line-height: 1.1;
        }
        .sidebar-app-sub {
            font-size: 0.6rem;
            color: var(--sky);
            text-transform: uppercase;
            letter-spacing: 0.1em;
            font-weight: 500;
        }

        /* Nav sections */
        .sidebar-nav {
            flex: 1;
            overflow-y: auto;
            padding: 1rem 0.75rem;
            scrollbar-width: none;
        }
        .sidebar-nav::-webkit-scrollbar { display: none; }

        .nav-group { margin-bottom: 1.5rem; }

        .nav-group-label {
            font-size: 0.62rem;
            font-weight: 700;
            color: rgba(255,255,255,0.3);
            text-transform: uppercase;
            letter-spacing: 0.12em;
            padding: 0 0.5rem;
            margin-bottom: 0.4rem;
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.6rem 0.75rem;
            border-radius: 10px;
            font-size: 0.83rem;
            font-weight: 500;
            color: rgba(255,255,255,0.6);
            text-decoration: none;
            transition: background 0.15s, color 0.15s;
            position: relative;
            margin-bottom: 2px;
        }

        .nav-item:hover {
            background: rgba(255,255,255,0.06);
            color: rgba(255,255,255,0.9);
        }

        .nav-item.active {
            background: rgba(26,172,180,0.15);
            color: var(--aqua);
        }

        .nav-item.active::before {
            content: '';
            position: absolute;
            left: 0; top: 25%; bottom: 25%;
            width: 3px;
            background: var(--aqua);
            border-radius: 0 3px 3px 0;
        }

        .nav-icon {
            width: 18px; height: 18px;
            flex-shrink: 0;
            opacity: 0.7;
        }
        .nav-item.active .nav-icon { opacity: 1; }

        .nav-badge {
            margin-left: auto;
            background: rgba(239,68,68,0.2);
            color: #f87171;
            font-size: 0.65rem;
            font-weight: 700;
            padding: 0.1rem 0.45rem;
            border-radius: 20px;
            min-width: 18px;
            text-align: center;
        }

        /* User card at bottom */
        .sidebar-user {
            padding: 0.75rem;
            border-top: 1px solid rgba(255,255,255,0.07);
        }

        .user-card {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem;
            border-radius: 12px;
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.07);
            text-decoration: none;
            transition: background 0.15s;
            cursor: pointer;
        }
        .user-card:hover { background: rgba(255,255,255,0.08); }

        .user-avatar {
            width: 34px; height: 34px;
            border-radius: 50%;
            background: linear-gradient(135deg, rgba(26,172,180,0.3), rgba(12,107,114,0.4));
            border: 1.5px solid rgba(26,172,180,0.4);
            display: flex; align-items: center; justify-content: center;
            font-family: 'Lora', serif;
            font-size: 0.85rem;
            font-weight: 700;
            color: var(--aqua);
            flex-shrink: 0;
        }

        .user-info { flex: 1; min-width: 0; }
        .user-name {
            font-size: 0.8rem;
            font-weight: 600;
            color: white;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .user-id {
            font-size: 0.65rem;
            color: rgba(255,255,255,0.4);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .user-chevron {
            width: 14px; height: 14px;
            color: rgba(255,255,255,0.3);
            flex-shrink: 0;
        }

        /* ─── MAIN CONTENT ────────────────────────── */
        .main-wrapper {
            margin-left: var(--sidebar-w);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            transition: margin-left 0.3s cubic-bezier(0.4,0,0.2,1);
        }

        /* Top bar (mobile only shows, desktop hides hamburger) */
        .topbar {
            background: var(--white);
            border-bottom: 1px solid var(--sand);
            padding: 0.875rem 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 30;
        }

        .topbar-left { display: flex; align-items: center; gap: 1rem; }

        .hamburger {
            display: none;
            width: 36px; height: 36px;
            border-radius: 8px;
            border: 1px solid var(--sand);
            background: white;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            color: var(--muted);
            transition: background 0.15s, color 0.15s;
        }
        .hamburger:hover { background: var(--cream); color: var(--navy); }

        .topbar-title {
            font-family: 'Lora', serif;
            font-size: 1rem;
            font-weight: 700;
            color: var(--navy);
        }

        .topbar-right { display: flex; align-items: center; gap: 0.75rem; }

        .topbar-badge {
            position: relative;
            width: 36px; height: 36px;
            border-radius: 8px;
            border: 1px solid var(--sand);
            background: white;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            color: var(--muted);
            transition: background 0.15s;
        }
        .topbar-badge:hover { background: var(--cream); }

        .notif-dot {
            position: absolute;
            top: 6px; right: 6px;
            width: 8px; height: 8px;
            background: #ef4444;
            border-radius: 50%;
            border: 2px solid white;
        }

        .topbar-avatar {
            width: 36px; height: 36px;
            border-radius: 50%;
            background: linear-gradient(135deg, rgba(26,172,180,0.2), rgba(12,107,114,0.3));
            border: 1.5px solid var(--aqua);
            display: flex; align-items: center; justify-content: center;
            font-family: 'Lora', serif;
            font-size: 0.8rem;
            font-weight: 700;
            color: var(--teal);
            cursor: pointer;
        }

        /* Page content */
        .page-content {
            flex: 1;
            padding: 2rem 2rem 3rem;
        }

        /* ─── MOBILE OVERLAY ──────────────────────── */
        .sidebar-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(13,43,69,0.6);
            backdrop-filter: blur(4px);
            z-index: 40;
        }

        /* ─── RESPONSIVE ──────────────────────────── */
        @media (max-width: 1024px) {
            .sidebar {
                transform: translateX(-100%);
            }
            .sidebar.open {
                transform: translateX(0);
            }
            .sidebar-overlay.open {
                display: block;
            }
            .main-wrapper {
                margin-left: 0;
            }
            .hamburger {
                display: flex;
            }
            .page-content {
                padding: 1.25rem 1rem 3rem;
            }
        }
    </style>

    @stack('styles')
</head>
<body x-data="{ sidebarOpen: false }">

<!-- ─── MOBILE OVERLAY ─────────────────────────────── -->
<div class="sidebar-overlay"
     :class="{ 'open': sidebarOpen }"
     @click="sidebarOpen = false">
</div>

<!-- ─── SIDEBAR ────────────────────────────────────── -->
<aside class="sidebar" :class="{ 'open': sidebarOpen }">
    <div class="sidebar-inner">

        <!-- Brand -->
        <a href="{{ route('dashboard') }}" class="sidebar-brand">
            <div class="sidebar-logo">
                <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path d="M12 2C12 2 4 10.5 4 15.5a8 8 0 0016 0C20 10.5 12 2 12 2zm0 18a6 6 0 01-6-6c0-3.5 4.5-9.8 6-11.8 1.5 2 6 8.3 6 11.8a6 6 0 01-6 6z"/>
                </svg>
            </div>
            <div class="sidebar-brand-text">
                <span class="sidebar-app-name">SIM-PDAM</span>
                <span class="sidebar-app-sub">Desa Lanto</span>
            </div>
        </a>

        <!-- Nav -->
        <nav class="sidebar-nav">

            <!-- UTAMA -->
            <div class="nav-group">
                <div class="nav-group-label">Utama</div>

                <a href="{{ route('dashboard') }}"
                   class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <svg class="nav-icon" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>
                        <polyline points="9 22 9 12 15 12 15 22"/>
                    </svg>
                    Beranda
                </a>

                <a href="{{ route('tagihan.index') }}"
                   class="nav-item {{ request()->routeIs('tagihan.*') ? 'active' : '' }}">
                    <svg class="nav-icon" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <rect x="2" y="5" width="20" height="14" rx="2"/>
                        <path d="M2 10h20"/>
                    </svg>
                    Tagihan & Pembayaran
                    @php
                        $unpaid = auth()->user()?->pelanggan?->tagihans()
                            ->whereIn('status_bayar', ['Belum Bayar'])
                            ->count();
                    @endphp
                    @if($unpaid > 0)
                        <span class="nav-badge">{{ $unpaid }}</span>
                    @endif
                </a>

                <a href="{{ route('statistik.index') }}"
                   class="nav-item {{ request()->routeIs('statistik.*') ? 'active' : '' }}">
                    <svg class="nav-icon" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>
                    </svg>
                    Statistik Pemakaian
                </a>
            </div>

            <!-- LAYANAN -->
            <div class="nav-group">
                <div class="nav-group-label">Layanan</div>

                <a href="{{ route('meteran.index') }}"
                   class="nav-item {{ request()->routeIs('meteran.*') ? 'active' : '' }}">
                    <svg class="nav-icon" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="3"/>
                        <path d="M19.07 4.93a10 10 0 010 14.14M4.93 4.93a10 10 0 000 14.14"/>
                    </svg>
                    Detail Meteran
                </a>

                <a href="{{ route('pengaduan.index') }}"
                   class="nav-item {{ request()->routeIs('pengaduan.*') ? 'active' : '' }}">
                    <svg class="nav-icon" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/>
                    </svg>
                    Laporan Pengaduan
                </a>
            </div>

            <!-- AKUN -->
            <div class="nav-group">
                <div class="nav-group-label">Akun</div>

                <a href="{{ route('profile.edit') }}"
                   class="nav-item {{ request()->routeIs('profile.*') ? 'active' : '' }}">
                    <svg class="nav-icon" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/>
                        <circle cx="12" cy="7" r="4"/>
                    </svg>
                    Profil & Pengaturan
                </a>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="nav-item w-full text-left"
                            style="background: none; border: none; cursor: pointer;">
                        <svg class="nav-icon" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/>
                            <polyline points="16 17 21 12 16 7"/>
                            <line x1="21" y1="12" x2="9" y2="12"/>
                        </svg>
                        Keluar
                    </button>
                </form>
            </div>

        </nav>

        <!-- User card -->
        <div class="sidebar-user">
            <a href="{{ route('profile.edit') }}" class="user-card">
                <div class="user-avatar">
                    {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
                </div>
                <div class="user-info">
                    <div class="user-name">{{ auth()->user()->name ?? 'Pengguna' }}</div>
                    <div class="user-id">
                        {{ auth()->user()->pelanggan?->no_pelanggan ?? 'Belum terdaftar' }}
                    </div>
                </div>
                <svg class="user-chevron" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M9 18l6-6-6-6"/>
                </svg>
            </a>
        </div>

    </div>
</aside>

<!-- ─── MAIN CONTENT ────────────────────────────────── -->
<div class="main-wrapper">

    <!-- Topbar -->
    <header class="topbar">
        <div class="topbar-left">
            <button class="hamburger" @click="sidebarOpen = !sidebarOpen">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <line x1="3" y1="6" x2="21" y2="6"/>
                    <line x1="3" y1="12" x2="21" y2="12"/>
                    <line x1="3" y1="18" x2="21" y2="18"/>
                </svg>
            </button>
            <span class="topbar-title">@yield('page-title', 'Dashboard')</span>
        </div>

        <div class="topbar-right">
            <!-- Notifikasi -->
            <div class="topbar-badge">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                    <path d="M13.73 21a2 2 0 01-3.46 0"/>
                </svg>
                @php
                    $hasNotif = auth()->user()?->pelanggan?->tagihans()
                        ->whereIn('status_bayar', ['Belum Bayar'])->exists();
                @endphp
                @if($hasNotif)
                    <span class="notif-dot"></span>
                @endif
            </div>

            <!-- Avatar -->
            <div class="topbar-avatar">
                {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
            </div>
        </div>
    </header>

    <!-- Page slot -->
    <main class="page-content">
        {{ $slot }}
    </main>

</div>

@stack('scripts')
</body>
</html>