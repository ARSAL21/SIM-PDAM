ISSUE: [Feature & UI/UX] Implementasi Alur "Claim Akun" Pelanggan & Redesain Split-Screen Autentikasi
Issue Type: Enhancement, UI/UX, Backend Logic
Priority: Critical (Bloker untuk perilisan fitur registrasi warga)
Assignee: [Nama Developer]
Estimated Effort: 4 - 5 Hours

📖 1. Konteks & Latar Belakang Masalah
Saat ini terdapat masalah asinkronisasi antara data pelanggan (meteran fisik) dan data user (akun web). Jika warga mendaftar bebas tanpa nomor meteran, aplikasi berisiko dipenuhi akun bodong dan Admin harus bekerja dua kali untuk menautkan data.

Solusi Logika Bisnis (Flow B):
Kita akan mengimplementasikan sistem "Claim Akun". Pemasangan meteran (pembuatan nomor_pelanggan) dilakukan terlebih dahulu oleh Admin via Filament. Warga kemudian melakukan registrasi mandiri di web dengan memasukkan nomor_pelanggan tersebut. Sistem akan memvalidasi dan otomatis menautkan (user_id) ke data meteran tersebut.

Solusi UI/UX:
Bersamaan dengan perubahan backend, halaman autentikasi bawaan Laravel Breeze akan dirombak total menggunakan arsitektur Asymmetric Split-Screen ("Digital Ledger"). Ini akan memberikan konsistensi visual dengan Landing Page, menggunakan tekstur Dot Matrix dan Blueprint Grid, serta animasi interaktif.

✅ 2. Acceptance Criteria (Kriteria Selesai)
Developer wajib memastikan poin-poin berikut terpenuhi sebelum melakukan Merge Request:

[ ] Kolom user_id pada tabel pelanggans sudah bersifat nullable.

[ ] Admin tidak perlu/tidak bisa lagi memilih User saat menginput Pelanggan baru di Filament.

[ ] UI halam Login & Register terbagi dua (Split-Screen): Kiri berwarna Navy dengan pola Dot Matrix, Kanan berwarna Cream/White dengan pola Grid Samar.

[ ] Halaman Register memiliki input field wajib untuk Nomor Pelanggan dengan helper text yang jelas.

[ ] Sistem menolak registrasi jika nomor_pelanggan salah/tidak ditemukan di database.

[ ] Sistem menolak registrasi jika nomor_pelanggan sudah tertaut dengan akun user_id lain.

[ ] Setelah registrasi berhasil, tabel pelanggans otomatis ter- update dengan user_id dari pendaftar baru tersebut.

🛠️ 3. Instruksi Eksekusi (Step-by-Step Implementation)
TAHAP A: Penyesuaian Database & Model
Migration (*_create_pelanggans_table.php):
Pastikan relasi ke tabel users diset opsional (nullable).

PHP
$table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
Model (app/Models/Pelanggan.php):
Pastikan user_id termasuk dalam $fillable.

PHP
protected $fillable = ['nomor_pelanggan', 'nama_lengkap', 'alamat', 'user_id', /* atribut lain */];
TAHAP B: Penyesuaian Sisi Admin (Filament)
Resource (PelangganResource.php):
Hapus atau sembunyikan (hidden()) komponen Select::make('user_id') dari form builder. Admin hanya mengurus data fisik warga.

TAHAP C: Konfigurasi UI Global & Animasi
CSS (resources/css/app.css):
Injeksi tekstur grid dan utilitas animasi staggered.

CSS
@layer utilities {
    .bg-dot-pattern { background-image: radial-gradient(rgba(125, 211, 218, 0.15) 1px, transparent 1px); background-size: 24px 24px; }
    .bg-grid-faint { background-image: linear-gradient(rgba(13, 43, 69, 0.03) 1px, transparent 1px), linear-gradient(90deg, rgba(13, 43, 69, 0.03) 1px, transparent 1px); background-size: 40px 40px; }
    .mask-fade-bottom { mask-image: linear-gradient(to bottom, black 40%, transparent 100%); }

    @keyframes fadeUp {
        from { opacity: 0; transform: translateY(16px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .animate-fade-up { animation: fadeUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
    .delay-100 { animation-delay: 0.1s; }
    .delay-200 { animation-delay: 0.2s; }
    .delay-300 { animation-delay: 0.3s; }
}
TAHAP D: Redesain Layout Auth (Breeze)
Cangkang UI (resources/views/layouts/guest.blade.php):
Ganti desain default dengan struktur asimetris.

HTML
<body class="font-sans text-[#1C2E3A] antialiased">
    <div class="min-h-screen flex">
        <!-- SISI KIRI (Branding 40%) -->
        <div class="hidden lg:flex lg:w-5/12 bg-[#0D2B45] relative overflow-hidden flex-col justify-between p-14 text-white">
            <div class="absolute inset-0 bg-dot-pattern mask-fade-bottom"></div>
            <!-- Tambahkan Logo & Teks Sambutan di sini sesuai desain -->
        </div>

        <!-- SISI KANAN (Formulir 60%) -->
        <div class="w-full lg:w-7/12 relative flex items-center justify-center p-6 sm:p-12 bg-[#FDFAF6] overflow-hidden">
            <div class="absolute inset-0 bg-grid-faint"></div>
            <!-- Card Formulir Floating -->
            <div class="relative z-10 w-full max-w-[440px] bg-white p-8 sm:p-10 rounded-2xl shadow-[0_20px_60px_-15px_rgba(13,43,69,0.05)] border border-[#E8DFD0]/50 animate-fade-up">
                {{ $slot }}
            </div>
        </div>
    </div>
</body>
Form Register Tambahan (resources/views/auth/register.blade.php):
Terapkan class animasi (animate-fade-up) dan tambahkan input field khusus untuk nomor_pelanggan.

HTML
<!-- Input Nomor Pelanggan -->
<div class="animate-fade-up delay-200 opacity-0 mb-4">
    <label for="nomor_pelanggan" class="block text-sm font-bold text-[#0D2B45] mb-2">Nomor Pelanggan</label>
    <input id="nomor_pelanggan" type="text" name="nomor_pelanggan" :value="old('nomor_pelanggan')" required 
        class="w-full px-4 py-3.5 rounded-xl border-2 border-[#F4EFE6] bg-[#FDFAF6] text-[#1C2E3A] focus:bg-white focus:outline-none focus:ring-0 focus:border-[#1AACB4] transition-all shadow-sm" 
        placeholder="Contoh: PAM-2604-001">
    <p class="text-[0.7rem] text-[#5A7080] mt-1.5">*Cek pada struk pembayaran Anda atau tanyakan ke petugas desa.</p>
    <x-input-error :messages="$errors->get('nomor_pelanggan')" class="mt-2 text-red-500 text-xs" />
</div>
TAHAP E: Logika Registrasi & Claiming (Backend)
Modifikasi Controller (app/Http/Controllers/Auth/RegisteredUserController.php):
Pembaruan method store untuk melakukan validasi ganda terhadap nomor_pelanggan sebelum menyimpan data User.

PHP
public function store(Request $request): RedirectResponse
{
    // 1. Validasi Input
    $request->validate([
        'name' => ['required', 'string', 'max:255'],
        'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.\App\Models\User::class],
        'password' => ['required', 'confirmed', \Illuminate\Validation\Rules\Password::defaults()],
        'nomor_pelanggan' => ['required', 'string'],
    ]);

    // 2. Query Data Pelanggan berdasarkan Nomor Input
    $pelanggan = \App\Models\Pelanggan::where('nomor_pelanggan', $request->nomor_pelanggan)->first();

    // 3. Validasi Kondisi A: Nomor tidak ditemukan di sistem
    if (!$pelanggan) {
        throw \Illuminate\Validation\ValidationException::withMessages([
            'nomor_pelanggan' => 'Nomor Pelanggan tidak ditemukan. Pastikan ketikkan sesuai struk asli.',
        ]);
    }

    // 4. Validasi Kondisi B: Nomor sudah diklaim
    if ($pelanggan->user_id !== null) {
        throw \Illuminate\Validation\ValidationException::withMessages([
            'nomor_pelanggan' => 'Nomor Pelanggan ini sudah ditautkan ke akun lain.',
        ]);
    }

    // 5. Create User
    $user = \App\Models\User::create([
        'name' => $request->name,
        'email' => $request->email,
        'password' => \Illuminate\Support\Facades\Hash::make($request->password),
    ]);

    // 6. Tautkan (Claim) ID user baru ke tabel Pelanggan
    $pelanggan->update([
        'user_id' => $user->id
    ]);

    event(new \Illuminate\Auth\Events\Registered($user));
    \Illuminate\Support\Facades\Auth::login($user);

    return redirect(route('dashboard', absolute: false));
}
Catatan Tambahan: Pastikan seluruh rute dan namespace telah di-import dengan benar di dalam Controller. Lakukan pengujian pendaftaran dengan nomor pelanggan yang valid dan tidak valid untuk mengonfirmasi error message muncul sesuai skenario.