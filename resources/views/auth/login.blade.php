<x-guest-layout>
    <!-- Header Form dengan Animasi Masuk -->
    <div class="mb-8 animate-fade-up opacity-0">
        <h1 class="font-serif text-3xl font-bold text-[#0D2B45] mb-2">Masuk ke Portal</h1>
        <p class="text-[#5A7080] text-sm leading-relaxed">
            Selamat datang kembali! Silakan masukkan email dan kata sandi Anda untuk memantau tagihan air bulan ini.
        </p>
    </div>

    <!-- Status Session (Misal: Password Reset Success) -->
    <x-auth-session-status class="mb-4 animate-fade-up delay-100 opacity-0" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-6">
        @csrf

        <!-- Email Address -->
        <div class="animate-fade-up delay-100 opacity-0">
            <label for="email" class="block text-sm font-semibold text-[#0D2B45] mb-1.5">Alamat Email</label>
            <div class="relative group">
                <div class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none text-[#5A7080] group-focus-within:text-[#1AACB4] transition-colors">
                    <!-- Ikon Surat -->
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                </div>
                <input id="email" type="email" name="email" :value="old('email')" required autofocus autocomplete="username"
                    class="w-full pl-11 pr-4 py-3.5 rounded-xl border border-[#E8DFD0] bg-[#FDFAF6] text-[#1C2E3A] focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#1AACB4]/30 focus:border-[#1AACB4] transition-all duration-300 shadow-sm hover:border-[#1AACB4]/50" 
                    placeholder="contoh@email.com">
            </div>
            <x-input-error :messages="$errors->get('email')" class="mt-2 text-red-500 text-xs" />
        </div>

        <!-- Password dengan Fitur Intip (Alpine.js) -->
        <div class="animate-fade-up delay-200 opacity-0" x-data="{ show: false }">
            <label for="password" class="block text-sm font-semibold text-[#0D2B45] mb-1.5">Kata Sandi</label>
            <div class="relative group">
                <div class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none text-[#5A7080] group-focus-within:text-[#1AACB4] transition-colors">
                    <!-- Ikon Kunci -->
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                </div>
                
                <!-- Input Password Dinamis -->
                <input id="password" :type="show ? 'text' : 'password'" name="password" required autocomplete="current-password"
                    class="w-full pl-11 pr-12 py-3.5 rounded-xl border border-[#E8DFD0] bg-[#FDFAF6] text-[#1C2E3A] focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#1AACB4]/30 focus:border-[#1AACB4] transition-all duration-300 shadow-sm hover:border-[#1AACB4]/50" 
                    placeholder="Masukkan sandi Anda">
                
                <!-- Tombol Intip Password -->
                <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 flex items-center pr-4 text-[#5A7080] hover:text-[#1AACB4] focus:outline-none transition-colors">
                    <svg x-show="!show" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                    <svg x-show="show" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path></svg>
                </button>
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-2 text-red-500 text-xs" />
        </div>

        <!-- Remember Me & Forgot Password (Di-highlight) -->
        <div class="flex items-center justify-between animate-fade-up delay-300 opacity-0">
            <label for="remember_me" class="inline-flex items-center cursor-pointer group">
                <div class="relative flex items-center justify-center w-5 h-5 border border-[#E8DFD0] rounded bg-white group-hover:border-[#1AACB4] transition-colors">
                    <input id="remember_me" type="checkbox" name="remember" class="peer absolute w-full h-full opacity-0 cursor-pointer">
                    <!-- Custom Checkmark (Muncul saat peer di-check) -->
                    <svg class="w-3.5 h-3.5 text-white opacity-0 peer-checked:opacity-100 z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                    <!-- Background Check (Muncul saat peer di-check) -->
                    <div class="absolute inset-0 bg-[#1AACB4] rounded opacity-0 peer-checked:opacity-100 transition-opacity"></div>
                </div>
                <span class="ml-2.5 text-sm text-[#5A7080] group-hover:text-[#1C2E3A] transition-colors">Ingat saya</span>
            </label>

            @if (Route::has('password.request'))
                <!-- Tombol Lupa Password Dibuat Sangat Jelas -->
                <a href="{{ route('password.request') }}" class="inline-flex items-center gap-1 text-sm font-semibold text-[#0C6B72] hover:text-[#1AACB4] transition-colors group">
                    <svg class="w-4 h-4 group-hover:-translate-y-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    Lupa Sandi?
                </a>
            @endif
        </div>

        <!-- Tombol Aksi Animasi Dinamis -->
        <div class="pt-4 animate-fade-up delay-400 opacity-0">
            <button type="submit" class="group relative w-full flex justify-center py-3.5 px-4 border border-transparent text-sm font-bold rounded-xl text-[#0D2B45] bg-[#1AACB4] hover:bg-[#7DD3DA] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#1AACB4] shadow-[0_4px_14px_0_rgba(26,172,180,0.39)] hover:shadow-[0_6px_20px_rgba(26,172,180,0.23)] hover:-translate-y-1 transition-all duration-300 overflow-hidden">
                <!-- Efek Kilap saat hover -->
                <div class="absolute inset-0 w-full h-full -translate-x-full bg-gradient-to-r from-transparent via-white/40 to-transparent group-hover:animate-[shimmer_1.5s_infinite]"></div>
                <span class="relative flex items-center gap-2">
                    Masuk Sekarang
                    <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                </span>
            </button>
        </div>

        <!-- Tautan Pendaftaran -->
        <div class="text-center mt-6 animate-fade-up delay-400 opacity-0">
            <p class="text-sm text-[#5A7080]">
                Belum memiliki akun portal? 
                <a href="{{ route('register') }}" class="font-semibold text-[#0C6B72] hover:text-[#1AACB4] relative after:content-[''] after:absolute after:-bottom-0.5 after:left-0 after:w-0 after:h-[1.5px] after:bg-[#1AACB4] hover:after:w-full after:transition-all after:duration-300">
                    Daftar di sini
                </a>
            </p>
        </div>
    </form>
</x-guest-layout>