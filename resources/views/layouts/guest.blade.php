<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'SIM-PDAM') - Desa Lanto</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .hero-bg {
            position: absolute;
            inset: 0;
            background:
                radial-gradient(ellipse 60% 80% at 70% 50%, rgba(12, 107, 114, 0.25) 0%, transparent 60%),
                radial-gradient(ellipse 40% 60% at 20% 80%, rgba(26, 172, 180, 0.12) 0%, transparent 50%);
        }
        .hero-grid {
            position: absolute;
            inset: 0;
            background-image:
                linear-gradient(rgba(26, 172, 180, 0.04) 1px, transparent 1px),
                linear-gradient(90deg, rgba(26, 172, 180, 0.04) 1px, transparent 1px);
            background-size: 60px 60px;
        }
        .hero-orb {
            position: absolute;
            border-radius: 50%;
            animation: float 8s ease-in-out infinite;
        }
        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-20px); }
        }
        .orb-1 { width: 400px; height: 400px; right: -80px; top: 50%; transform: translateY(-50%); background: radial-gradient(circle, rgba(26, 172, 180, 0.18) 0%, transparent 70%); animation-delay: 0s; }
        .orb-2 { width: 200px; height: 200px; right: 200px; top: 15%; background: radial-gradient(circle, rgba(12, 107, 114, 0.2) 0%, transparent 70%); animation-delay: 3s; }
        .orb-3 { width: 100px; height: 100px; left: 10%; bottom: 10%; background: radial-gradient(circle, rgba(26, 172, 180, 0.15) 0%, transparent 70%); animation-delay: 5s; }
        
        .blueprint-grid {
            position: absolute;
            inset: 0;
            background-image:
                linear-gradient(rgba(13, 43, 69, 0.03) 1px, transparent 1px),
                linear-gradient(90deg, rgba(13, 43, 69, 0.03) 1px, transparent 1px);
            background-size: 40px 40px;
            z-index: 0;
            pointer-events: none;
        }
        .content-wrapper {
            position: relative;
            z-index: 10;
        }
    </style>
</head>
<body class="font-sans antialiased">
<div class="min-h-screen flex flex-col lg:flex-row">
    <!-- Panel Kiri: Branding (tersembunyi di mobile) -->
    <aside class="hidden lg:flex lg:w-2/5 bg-[#0D2B45] relative overflow-hidden items-center justify-center p-12">
        <!-- Ornamen latar dari Hero Landing Page -->
        <div class="hero-bg"></div>
        <div class="hero-grid"></div>
        <div class="hero-orb orb-1"></div>
        <div class="hero-orb orb-2"></div>
        <div class="hero-orb orb-3"></div>

        <div class="relative text-center text-white max-w-xs">
            <!-- Logo -->
            <div class="w-20 h-20 mx-auto mb-6 rounded-2xl bg-gradient-to-br from-[#1AACB4] to-[#0C6B72] flex items-center justify-center shadow-lg shadow-[#1AACB4]/20">
                <svg viewBox="0 0 24 24" class="w-10 h-10 fill-white">
                    <path d="M12 2C12 2 4 10.5 4 15.5a8 8 0 0016 0C20 10.5 12 2 12 2zm0 18a6 6 0 01-6-6c0-3.5 4.5-9.8 6-11.8 1.5 2 6 8.3 6 11.8a6 6 0 01-6 6z"/>
                </svg>
            </div>
            <h1 class="font-[Lora] text-3xl font-bold mb-2">SIM-PDAM</h1>
            <p class="text-sm text-[#7DD3DA] uppercase tracking-widest mb-4">Desa Lanto</p>
            <p class="text-white/60 text-sm leading-relaxed">
                Pantau tagihan air, kubikasi, dan lakukan pembayaran dengan mudah — kapan pun, di mana pun.
            </p>
        </div>
    </aside>

    <!-- Panel Kanan: Formulir -->
    <main class="w-full lg:w-3/5 bg-[#FDFAF6] flex items-center justify-center relative px-5 py-10 lg:py-0 overflow-hidden">
        <!-- Blueprint Grid Samar -->
        <div class="blueprint-grid"></div>

        <!-- Tombol Kembali -->
        <a href="/" class="absolute top-6 right-6 inline-flex items-center gap-1 text-sm text-[#5A7080] hover:text-[#0D2B45] transition z-20">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M15 19l-7-7 7-7"/></svg>
            Kembali ke Beranda
        </a>

        <div class="w-full max-w-md content-wrapper">
            @yield('content')
        </div>
    </main>
</div>
</body>
</html> 