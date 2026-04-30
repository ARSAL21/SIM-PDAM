@extends('layouts.guest')
@section('title', 'Daftar')

@section('content')
<div x-data="{ showPassword: false, showPasswordConfirmation: false, isLoading: false }" class="space-y-5">

    <!-- Header -->
    <div class="animate-fade-up opacity-0">
        <h2 class="font-[Lora] text-2xl font-bold text-[#0D2B45]">Buat Akun</h2>
        <p class="text-[#5A7080] text-sm mt-1">Daftarkan diri untuk memantau tagihan air Anda</p>
    </div>

    @if ($errors->any())
        <div class="animate-fade-up opacity-0 delay-100 bg-red-50 text-red-600 text-xs rounded-lg p-3 space-y-0.5">
            @foreach ($errors->all() as $error)
                <p>• {{ $error }}</p>
            @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('register') }}" class="space-y-4" @submit="isLoading = true">
        @csrf

        <!-- Row 1: Nama + Nomor Pelanggan (side by side) -->
        <div class="grid grid-cols-2 gap-3 animate-fade-up opacity-0 delay-100">

            <!-- Nama -->
            <div>
                <label class="block text-xs font-semibold text-[#0D2B45] mb-1">Nama Lengkap</label>
                <div class="relative group">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="w-4 h-4 text-[#5A7080] group-focus-within:text-[#1AACB4] transition" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/>
                        </svg>
                    </span>
                    <input name="name" type="text" value="{{ old('name') }}" required
                           class="w-full pl-9 pr-3 py-2.5 text-sm bg-white border border-[#E8DFD0] rounded-lg focus:ring-2 focus:ring-[#1AACB4]/30 focus:border-[#1AACB4] transition outline-none"
                           placeholder="Nama Anda">
                </div>
            </div>

            <!-- Nomor Pelanggan -->
            <div>
                <label class="block text-xs font-semibold text-[#0D2B45] mb-1">No. Pelanggan</label>
                <div class="relative group">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="w-4 h-4 text-[#5A7080] group-focus-within:text-[#1AACB4] transition" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path d="M10 13a5 5 0 007.54.54l3-3a5 5 0 00-7.07-7.07l-1.72 1.71"/>
                            <path d="M14 11a5 5 0 00-7.54-.54l-3 3a5 5 0 007.07 7.07l1.71-1.71"/>
                        </svg>
                    </span>
                    <input name="nomor_pelanggan" type="text" value="{{ old('nomor_pelanggan') }}" required
                           class="w-full pl-9 pr-3 py-2.5 text-sm bg-white border border-[#E8DFD0] rounded-lg focus:ring-2 focus:ring-[#1AACB4]/30 focus:border-[#1AACB4] transition outline-none"
                           placeholder="PAM-2501-0042">
                </div>
            </div>
        </div>

        <!-- Hint nomor pelanggan -->
        <p class="text-[10px] text-[#5A7080] -mt-2 animate-fade-up opacity-0 delay-100">
            Cek pada struk pembayaran bulan lalu atau tanyakan ke petugas balai desa.
        </p>

        <!-- Email -->
        <div class="animate-fade-up opacity-0 delay-150">
            <label class="block text-xs font-semibold text-[#0D2B45] mb-1">Alamat Email</label>
            <div class="relative group">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="w-4 h-4 text-[#5A7080] group-focus-within:text-[#1AACB4] transition" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                        <polyline points="22,6 12,13 2,6"/>
                    </svg>
                </span>
                <input name="email" type="email" value="{{ old('email') }}" required
                       class="w-full pl-10 pr-4 py-2.5 text-sm bg-white border border-[#E8DFD0] rounded-lg focus:ring-2 focus:ring-[#1AACB4]/30 focus:border-[#1AACB4] transition outline-none"
                       placeholder="nama@email.com">
            </div>
        </div>

        <!-- Row 2: Password + Konfirmasi (side by side) -->
        <div class="grid grid-cols-2 gap-3 animate-fade-up opacity-0 delay-200">

            <!-- Password -->
            <div>
                <label class="block text-xs font-semibold text-[#0D2B45] mb-1">Kata Sandi</label>
                <div class="relative group">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="w-4 h-4 text-[#5A7080] group-focus-within:text-[#1AACB4] transition" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/>
                        </svg>
                    </span>
                    <input name="password" :type="showPassword ? 'text' : 'password'" required
                           class="w-full pl-9 pr-9 py-2.5 text-sm bg-white border border-[#E8DFD0] rounded-lg focus:ring-2 focus:ring-[#1AACB4]/30 focus:border-[#1AACB4] transition outline-none"
                           placeholder="Min. 8 karakter">
                    <button type="button" @click="showPassword = !showPassword"
                            class="absolute inset-y-0 right-0 pr-2.5 flex items-center text-[#5A7080] hover:text-[#1AACB4] transition">
                        <svg x-show="!showPassword" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        <svg x-show="showPassword" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19m-6.72-1.07a3 3 0 11-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                    </button>
                </div>
            </div>

            <!-- Konfirmasi Password -->
            <div>
                <label class="block text-xs font-semibold text-[#0D2B45] mb-1">Konfirmasi</label>
                <div class="relative group">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="w-4 h-4 text-[#5A7080] group-focus-within:text-[#1AACB4] transition" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/>
                        </svg>
                    </span>
                    <input name="password_confirmation" :type="showPasswordConfirmation ? 'text' : 'password'" required
                           class="w-full pl-9 pr-9 py-2.5 text-sm bg-white border border-[#E8DFD0] rounded-lg focus:ring-2 focus:ring-[#1AACB4]/30 focus:border-[#1AACB4] transition outline-none"
                           placeholder="Ulangi sandi">
                    <button type="button" @click="showPasswordConfirmation = !showPasswordConfirmation"
                            class="absolute inset-y-0 right-0 pr-2.5 flex items-center text-[#5A7080] hover:text-[#1AACB4] transition">
                        <svg x-show="!showPasswordConfirmation" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        <svg x-show="showPasswordConfirmation" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19m-6.72-1.07a3 3 0 11-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Submit -->
        <div class="animate-fade-up opacity-0 delay-300 pt-1">
            <button type="submit"
                    :disabled="isLoading"
                    class="relative w-full py-3 bg-[#1AACB4] text-[#0D2B45] font-bold text-sm rounded-lg overflow-hidden group hover:shadow-lg hover:shadow-[#1AACB4]/30 transition disabled:opacity-70 disabled:cursor-not-allowed">
                <span x-show="!isLoading" class="relative z-10">Daftar Sekarang</span>
                <span x-show="isLoading" class="relative z-10 flex items-center justify-center gap-2">
                    <svg class="animate-spin h-4 w-4 text-[#0D2B45]" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                    </svg>
                    Mendaftarkan...
                </span>
                <span x-show="!isLoading" class="absolute inset-0 bg-gradient-to-r from-transparent via-white/30 to-transparent -skew-x-12 -translate-x-full group-hover:animate-shimmer"></span>
            </button>
        </div>

        <p class="text-center text-xs text-[#5A7080] animate-fade-up opacity-0 delay-400">
            Sudah punya akun?
            <a href="{{ route('login') }}" class="text-[#0C6B72] font-semibold hover:underline">Masuk di sini</a>
        </p>
    </form>
</div>
@endsection