    @extends('layouts.guest')
    @section('title', 'Masuk')

    @section('content')
    <div x-data="{ showPassword: false, isLoading: false }" class="space-y-6">
        <!-- Header -->
        <div class="text-center lg:text-left animate-fade-up opacity-0">
            <h2 class="font-[Lora] text-3xl font-bold text-[#0D2B45]">Selamat Datang</h2>
            <p class="text-[#5A7080] mt-1">Masuk untuk melihat tagihan air Anda</p>
        </div>

        @if ($errors->any())
            <div class="animate-fade-up opacity-0 delay-100 bg-red-50 text-red-600 text-sm rounded-lg p-3">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}" class="space-y-5" @submit="isLoading = true">
            @csrf

            <!-- Email -->
            <div class="animate-fade-up opacity-0 delay-100">
                <label class="block text-sm font-medium text-[#0D2B45] mb-1">Alamat Email</label>
                <div class="relative group">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="w-5 h-5 text-[#5A7080] group-focus-within:text-[#1AACB4] transition" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                            <polyline points="22,6 12,13 2,6"/>
                        </svg>
                    </span>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus
                        class="w-full pl-10 pr-4 py-3 bg-white border border-[#E8DFD0] rounded-lg focus:ring-2 focus:ring-[#1AACB4]/30 focus:border-[#1AACB4] transition outline-none"
                        placeholder="nama@email.com">
                </div>
            </div>

            <!-- Password -->
            <div class="animate-fade-up opacity-0 delay-200">
                <label class="block text-sm font-medium text-[#0D2B45] mb-1">Kata Sandi</label>
                <div class="relative group">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="w-5 h-5 text-[#5A7080] group-focus-within:text-[#1AACB4] transition" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                            <path d="M7 11V7a5 5 0 0110 0v4"/>
                        </svg>
                    </span>
                    <input id="password" name="password" :type="showPassword ? 'text' : 'password'" required
                        class="w-full pl-10 pr-12 py-3 bg-white border border-[#E8DFD0] rounded-lg focus:ring-2 focus:ring-[#1AACB4]/30 focus:border-[#1AACB4] transition outline-none"
                        placeholder="········">
                    <button type="button" @click="showPassword = !showPassword" class="absolute inset-y-0 right-0 pr-3 flex items-center text-[#5A7080] hover:text-[#1AACB4] transition">
                        <svg x-show="!showPassword" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                            <circle cx="12" cy="12" r="3"/>
                        </svg>
                        <svg x-show="showPassword" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19m-6.72-1.07a3 3 0 11-4.24-4.24"/>
                            <line x1="1" y1="1" x2="23" y2="23"/>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Remember + Lupa Sandi -->
            <div class="animate-fade-up opacity-0 delay-300 flex items-center justify-between">
                <label class="flex items-center gap-2 cursor-pointer select-none">
                    <input type="checkbox" name="remember" class="hidden peer">
                    <span class="w-5 h-5 border-2 border-[#E8DFD0] peer-checked:bg-[#1AACB4] peer-checked:border-[#1AACB4] rounded transition flex items-center justify-center">
                        <svg class="w-3 h-3 text-white hidden peer-checked:block" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path d="M5 13l4 4L19 7"/></svg>
                    </span>
                    <span class="text-sm text-[#5A7080]">Ingat saya</span>
                </label>
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="text-sm font-medium text-[#0C6B72] hover:-translate-y-0.5 transition inline-block">
                        Lupa Sandi?
                    </a>
                @endif
            </div>

            <!-- Tombol Submit -->
            <div class="animate-fade-up opacity-0 delay-400 pt-2">
                <button type="submit"
                        :disabled="isLoading"
                        class="relative w-full py-3.5 bg-[#1AACB4] text-[#0D2B45] font-bold rounded-lg overflow-hidden group hover:shadow-lg hover:shadow-[#1AACB4]/30 transition disabled:opacity-70 disabled:cursor-not-allowed">
                    
                    <!-- State Normal -->
                    <span x-show="!isLoading" class="relative z-10">Masuk ke Portal</span>
                    
                    <!-- State Loading -->
                    <span x-show="isLoading" class="relative z-10 flex items-center justify-center gap-2">
                        <svg class="animate-spin h-5 w-5 text-[#0D2B45]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Memproses...
                    </span>

                    <!-- Shimmer hanya saat hover -->
                    <span x-show="!isLoading" class="absolute inset-0 bg-gradient-to-r from-transparent via-white/30 to-transparent -skew-x-12 -translate-x-full group-hover:animate-shimmer"></span>
                </button>
            </div>

            <p class="text-center text-sm text-[#5A7080] animate-fade-up opacity-0 delay-500">
                Belum punya akun? <a href="{{ route('register') }}" class="text-[#0C6B72] font-medium hover:underline">Daftar di sini</a>
            </p>
        </form>
    </div>
    @endsection