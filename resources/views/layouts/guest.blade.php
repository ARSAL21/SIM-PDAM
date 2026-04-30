<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'SIM-PDAM') }} - Autentikasi</title>

    <!-- Menggunakan Font dari Landing Page -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lora:ital,wght@0,400;0,600;0,700;1,400&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .font-serif { font-family: 'Lora', serif; }
    </style>
</head>
<body class="font-sans text-[#1C2E3A] antialiased bg-[#FDFAF6]">
    <div class="min-h-screen flex">
        
        <!-- SISI KIRI: Branding (40%) - Disembunyikan di layar HP -->
        <div class="hidden lg:flex lg:w-2/5 bg-[#0D2B45] relative overflow-hidden flex-col justify-between p-12 text-white">
            <!-- Ornamen Dekoratif Landing Page -->
            <div class="absolute top-[-10%] right-[-10%] w-96 h-96 rounded-full bg-[radial-gradient(circle,rgba(26,172,180,0.15)_0%,transparent_70%)]"></div>
            <div class="absolute bottom-10 left-[-5%] w-64 h-64 rounded-full bg-[radial-gradient(circle,rgba(12,107,114,0.2)_0%,transparent_70%)]"></div>
            
            <!-- Logo & Nama Sistem -->
            <div class="relative z-10 flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-[#1AACB4] to-[#0C6B72] flex items-center justify-center">
                    <svg viewBox="0 0 24 24" class="w-6 h-6 fill-white" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 2C12 2 4 10.5 4 15.5a8 8 0 0016 0C20 10.5 12 2 12 2zm0 18a6 6 0 01-6-6c0-3.5 4.5-9.8 6-11.8 1.5 2 6 8.3 6 11.8a6 6 0 01-6 6z"/>
                    </svg>
                </div>
                <div class="flex flex-col">
                    <span class="font-serif font-bold text-lg leading-tight">SIM-PDAM</span>
                    <span class="text-[0.65rem] text-[#7DD3DA] tracking-widest uppercase">Desa Lanto</span>
                </div>
            </div>

            <!-- Teks Sambutan Kiri -->
            <div class="relative z-10 mb-12">
                <h2 class="font-serif text-3xl font-bold mb-4 leading-tight">
                    Transparansi air,<br>kini dalam genggaman.
                </h2>
                <p class="text-[#7DD3DA] text-sm leading-relaxed max-w-sm">
                    Akses portal resmi untuk memantau riwayat pemakaian kubikasi dan melunasi tagihan bulanan Anda dengan aman.
                </p>
            </div>
        </div>

        <!-- SISI KANAN: Formulir Interaktif (60%) -->
        <div class="w-full lg:w-3/5 flex items-center justify-center p-6 sm:p-12 lg:p-24 relative">
            <!-- Tombol Kembali ke Beranda (Pojok Kanan Atas) -->
            <a href="/" class="absolute top-6 right-6 lg:top-12 lg:right-12 text-[#5A7080] hover:text-[#1AACB4] text-sm font-medium transition-colors flex items-center gap-2">
                &larr; Kembali ke Beranda
            </a>

            <!-- Area Konten Form dari View Login/Register -->
            <div class="w-full max-w-md">
                {{ $slot }}
            </div>
        </div>

    </div>
</body>
</html>