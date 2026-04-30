@extends('layouts.guest')
@section('title', 'Lupa Sandi')

@section('content')
<div x-data="{ isLoading: false }" class="space-y-6">
    <!-- Header -->
    <div class="animate-fade-up opacity-0">
        <h2 class="font-[Lora] text-2xl font-bold text-[#0D2B45]">Lupa Kata Sandi?</h2>
        <p class="text-[#5A7080] text-sm mt-1">
            Jangan khawatir! Masukkan alamat email Anda dan kami akan mengirimkan tautan untuk mengatur ulang kata sandi.
        </p>
    </div>

    <!-- Session Status (Success Message) -->
    @if (session('status'))
        <div class="animate-fade-up opacity-0 delay-100 bg-[#1AACB4]/10 text-[#0C6B72] text-sm rounded-lg p-3 font-medium border border-[#1AACB4]/20">
            {{ session('status') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="animate-fade-up opacity-0 delay-100 bg-red-50 text-red-600 text-xs rounded-lg p-3 space-y-0.5">
            @foreach ($errors->all() as $error)
                <p>• {{ $error }}</p>
            @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('password.email') }}" class="space-y-5" @submit="isLoading = true">
        @csrf

        <!-- Email Address -->
        <div class="animate-fade-up opacity-0 delay-100">
            <label class="block text-xs font-semibold text-[#0D2B45] mb-1">Alamat Email</label>
            <div class="relative group">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="w-4 h-4 text-[#5A7080] group-focus-within:text-[#1AACB4] transition" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                        <polyline points="22,6 12,13 2,6"/>
                    </svg>
                </span>
                <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus
                       class="w-full pl-10 pr-4 py-2.5 text-sm bg-white border border-[#E8DFD0] rounded-lg focus:ring-2 focus:ring-[#1AACB4]/30 focus:border-[#1AACB4] transition outline-none"
                       placeholder="nama@email.com">
            </div>
        </div>

        <div class="animate-fade-up opacity-0 delay-200 pt-2 flex flex-col gap-3">
            <button type="submit"
                    :disabled="isLoading"
                    class="relative w-full py-3 bg-[#1AACB4] text-[#0D2B45] font-bold text-sm rounded-lg overflow-hidden group hover:shadow-lg hover:shadow-[#1AACB4]/30 transition disabled:opacity-70 disabled:cursor-not-allowed">
                
                <span x-show="!isLoading" class="relative z-10">Kirim Tautan Reset</span>
                
                <span x-show="isLoading" class="relative z-10 flex items-center justify-center gap-2">
                    <svg class="animate-spin h-4 w-4 text-[#0D2B45]" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                    Mengirim...
                </span>

                <span x-show="!isLoading" class="absolute inset-0 bg-gradient-to-r from-transparent via-white/30 to-transparent -skew-x-12 -translate-x-full group-hover:animate-shimmer"></span>
            </button>
            
            <a href="{{ route('login') }}" class="text-center text-xs font-semibold text-[#5A7080] hover:text-[#0D2B45] transition">
                Kembali ke halaman Masuk
            </a>
        </div>
    </form>
</div>
@endsection
