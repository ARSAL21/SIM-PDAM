<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'SIM-PDAM') — Desa Lanto</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Lora:ital,wght@0,400;0,600;0,700;1,400&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }

        /* ─── LEFT PANEL ───────────────────────────────── */
        .auth-left {
            background: #0D2B45;
            position: relative;
            overflow: hidden;
        }

        /* Grid lines */
        .auth-left::before {
            content: '';
            position: absolute;
            inset: 0;
            background-image:
                linear-gradient(rgba(26,172,180,0.05) 1px, transparent 1px),
                linear-gradient(90deg, rgba(26,172,180,0.05) 1px, transparent 1px);
            background-size: 48px 48px;
            z-index: 0;
        }

        /* Ambient blobs */
        .blob {
            position: absolute;
            border-radius: 50%;
            filter: blur(60px);
            pointer-events: none;
        }
        .blob-1 {
            width: 380px; height: 380px;
            background: radial-gradient(circle, rgba(12,107,114,0.35), transparent 70%);
            top: -100px; right: -100px;
            animation: blobFloat 10s ease-in-out infinite;
        }
        .blob-2 {
            width: 260px; height: 260px;
            background: radial-gradient(circle, rgba(26,172,180,0.2), transparent 70%);
            bottom: -60px; left: -60px;
            animation: blobFloat 13s ease-in-out infinite 4s;
        }
        .blob-3 {
            width: 160px; height: 160px;
            background: radial-gradient(circle, rgba(200,169,110,0.15), transparent 70%);
            top: 50%; left: 20%;
            animation: blobFloat 8s ease-in-out infinite 2s;
        }
        @keyframes blobFloat {
            0%, 100% { transform: translateY(0) scale(1); }
            50%       { transform: translateY(-24px) scale(1.06); }
        }

        /* ─── WATER DROP ANIMATION ─────────────────────── */
        .water-scene {
            position: relative;
            width: 120px;
            height: 120px;
            margin: 0 auto 2rem;
        }

        /* Outer ring pulses */
        .water-ring {
            position: absolute;
            inset: 0;
            border-radius: 50%;
            border: 1.5px solid rgba(26,172,180,0.2);
            animation: ringPulse 3s ease-out infinite;
        }
        .water-ring:nth-child(2) { animation-delay: 1s; }
        .water-ring:nth-child(3) { animation-delay: 2s; }
        @keyframes ringPulse {
            0%   { transform: scale(1); opacity: 0.6; }
            100% { transform: scale(1.8); opacity: 0; }
        }

        /* Central drop icon */
        .water-drop-icon {
            position: absolute;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .water-drop-icon .drop-bg {
            width: 72px;
            height: 72px;
            border-radius: 50%;
            background: linear-gradient(135deg, rgba(26,172,180,0.2), rgba(12,107,114,0.1));
            border: 1px solid rgba(26,172,180,0.15);
            display: flex;
            align-items: center;
            justify-content: center;
            animation: dropBreathe 4s ease-in-out infinite;
        }
        @keyframes dropBreathe {
            0%, 100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(26,172,180,0.15); }
            50%      { transform: scale(1.05); box-shadow: 0 0 30px 8px rgba(26,172,180,0.1); }
        }

        /* Orbiting particles */
        .orbit-particle {
            position: absolute;
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: #1AACB4;
        }
        .orbit-particle:nth-child(1) {
            top: 10px; left: 50%;
            animation: orbitA 6s linear infinite;
            transform-origin: 0 50px;
        }
        .orbit-particle:nth-child(2) {
            bottom: 10px; right: 20%;
            animation: orbitB 8s linear infinite;
            transform-origin: -10px -40px;
            width: 4px; height: 4px;
            opacity: 0.6;
        }
        .orbit-particle:nth-child(3) {
            top: 40%; left: 5px;
            animation: orbitA 7s linear infinite reverse;
            transform-origin: 55px 15px;
            width: 5px; height: 5px;
            opacity: 0.4;
        }
        @keyframes orbitA {
            from { transform: rotate(0deg) translateX(54px) rotate(0deg); }
            to   { transform: rotate(360deg) translateX(54px) rotate(-360deg); }
        }
        @keyframes orbitB {
            from { transform: rotate(0deg) translateX(48px) rotate(0deg); }
            to   { transform: rotate(-360deg) translateX(48px) rotate(360deg); }
        }
        @keyframes livePulse {
            0%, 100% { box-shadow: 0 0 0 0 rgba(16,185,129,0.4); }
            50%       { box-shadow: 0 0 0 6px rgba(16,185,129,0); }
        }

        /* ─── RIGHT PANEL ──────────────────────────────── */
        .auth-right {
            background: #FDFAF6;
            position: relative;
            overflow: hidden;
        }
        .auth-right::before {
            content: '';
            position: absolute;
            inset: 0;
            background-image:
                linear-gradient(rgba(13,43,69,0.025) 1px, transparent 1px),
                linear-gradient(90deg, rgba(13,43,69,0.025) 1px, transparent 1px);
            background-size: 36px 36px;
            pointer-events: none;
        }

        /* ─── FORM ANIMATIONS ──────────────────────────── */
        .animate-fade-up {
            animation: fadeUpIn 0.5s ease forwards;
        }
        @keyframes fadeUpIn {
            from { opacity: 0; transform: translateY(14px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .delay-100 { animation-delay: 0.10s; }
        .delay-150 { animation-delay: 0.15s; }
        .delay-200 { animation-delay: 0.20s; }
        .delay-250 { animation-delay: 0.25s; }
        .delay-300 { animation-delay: 0.30s; }
        .delay-400 { animation-delay: 0.40s; }
        .delay-500 { animation-delay: 0.50s; }

        /* Shimmer */
        @keyframes shimmer {
            from { transform: translateX(-100%) skewX(-12deg); }
            to   { transform: translateX(200%) skewX(-12deg); }
        }
        .animate-shimmer { animation: shimmer 0.8s ease forwards; }

        /* ─── SCROLLBAR (register form) ─────────────────── */
        .form-scroll::-webkit-scrollbar { width: 4px; }
        .form-scroll::-webkit-scrollbar-track { background: transparent; }
        .form-scroll::-webkit-scrollbar-thumb { background: #E8DFD0; border-radius: 4px; }
    </style>
</head>
<body class="antialiased">
<div class="min-h-screen flex flex-col lg:flex-row">

    <!-- ─── LEFT: Branding panel (hidden on mobile) ─── -->
    <aside class="auth-left hidden lg:flex lg:w-1/2 flex-col items-center justify-center p-12 relative">

        <!-- Blobs -->
        <div class="blob blob-1"></div>
        <div class="blob blob-2"></div>
        <div class="blob blob-3"></div>

        <!-- Main content -->
        <div class="relative z-10 text-center text-white max-w-sm">

            <!-- Animated water scene -->
            <div class="water-scene">
                <div class="water-ring"></div>
                <div class="water-ring"></div>
                <div class="water-ring"></div>
                <div class="orbit-particle"></div>
                <div class="orbit-particle"></div>
                <div class="orbit-particle"></div>
                <div class="water-drop-icon">
                    <div class="drop-bg">
                        <svg viewBox="0 0 24 24" class="w-9 h-9 fill-white/90">
                            <path d="M12 2C12 2 4 10.5 4 15.5a8 8 0 0016 0C20 10.5 12 2 12 2zm0 18a6 6 0 01-6-6c0-3.5 4.5-9.8 6-11.8 1.5 2 6 8.3 6 11.8a6 6 0 01-6 6z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <h1 class="font-[Lora] text-4xl font-bold mb-3 leading-tight">
                Air bersih,<br>
                <em class="text-[#1AACB4] not-italic">tagihan jelas.</em>
            </h1>
            <p class="text-white/45 text-sm leading-relaxed mt-4 max-w-[280px] mx-auto">
                Portal resmi pengelolaan air bersih Desa Lanto — pantau pemakaian
                dan lunasi tagihan dari genggaman Anda.
            </p>

            <!-- Subtle divider -->
            <div class="mt-8 flex items-center gap-3 justify-center">
                <span class="block w-8 h-px bg-white/15"></span>
                <span class="text-[10px] text-white/30 uppercase tracking-[0.15em] font-medium">SIM-PDAM · Desa Lanto</span>
                <span class="block w-8 h-px bg-white/15"></span>
            </div>
        </div>
    </aside>

    <!-- ─── RIGHT: Form panel ─── -->
    <main class="auth-right w-full lg:w-1/2 flex items-center justify-center relative min-h-screen">

        <!-- Back button -->
        <a href="/"
           class="absolute top-5 right-5 z-20 inline-flex items-center gap-1.5 text-xs font-medium text-[#5A7080] hover:text-[#0D2B45] bg-white border border-[#E8DFD0] px-3 py-1.5 rounded-full shadow-sm hover:shadow transition">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M15 19l-7-7 7-7"/></svg>
            Beranda
        </a>

        <!-- Mobile logo (shown only on small screens) -->
        <div class="absolute top-5 left-5 z-20 flex items-center gap-2 lg:hidden">
            <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-[#1AACB4] to-[#0C6B72] flex items-center justify-center">
                <svg viewBox="0 0 24 24" class="w-4 h-4 fill-white">
                    <path d="M12 2C12 2 4 10.5 4 15.5a8 8 0 0016 0C20 10.5 12 2 12 2z"/>
                </svg>
            </div>
            <span class="font-[Lora] text-sm font-bold text-[#0D2B45]">SIM-PDAM</span>
        </div>

        <!-- Form content -->
        <div class="relative z-10 w-full max-w-[420px] px-6 py-16 lg:py-0">
            @yield('content')
        </div>
    </main>

</div>
</body>
</html>