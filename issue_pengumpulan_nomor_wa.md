ISSUE: [Feature & UX] Pengumpulan Nomor WhatsApp untuk Notifikasi Pembayaran Tagihan
Issue Type: Enhancement, UX/UI, Database Schema
Priority: High
Assignee: [Nama Developer]

📖 1. Konteks & Latar Belakang Masalah
Dalam ekosistem aplikasi SIM-PDAM, salah satu fitur krusial paska-pembayaran adalah mengirimkan struk atau bukti bayar digital secara otomatis ke warga melalui WhatsApp (menggunakan gateway seperti Fonnte/Wablas).

Masalah Utama:
Saat ini, sistem autentikasi (Registrasi) belum mengakomodasi pengumpulan data Nomor WhatsApp. Kita dihadapkan pada dilema UI/UX mengenai kapan waktu yang paling tepat untuk menagih data ini dari warga:

Jika ditagih saat akan membayar (Checkout): Akan menciptakan gesekan (friction) yang sangat tinggi. Meminta pengguna mengisi form tepat di saat mereka bersedia mengeluarkan uang berisiko membuat mereka frustrasi dan membatalkan transaksi digital.

Jika diletakkan di menu Profil: Berpotensi besar diabaikan. Hukum UX menyatakan pengguna jarang sekali mengisi data tambahan secara sukarela jika tidak dipaksa. Fitur notifikasi WA akan gagal berfungsi karena ketiadaan data.

💡 2. Resolusi & Cara Penyelesaian (Opsi 3: Upfront Registration)
Pendekatan yang dipilih untuk menyelesaikan masalah ini adalah Upfront Registration (Pengumpulan Data di Awal). Nomor WhatsApp akan diwajibkan sebagai salah satu kolom isian pada saat warga pertama kali membuat akun di halaman Register.

Alasan Pemilihan Arsitektur Ini:
Frictionless Checkout (Pembayaran Tanpa Hambatan): Dengan menyimpan nomor WA sejak awal, pengalaman warga saat membayar tagihan di bulan-bulan berikutnya akan menjadi 1-Click Experience. Mereka hanya perlu klik "Bayar" tanpa diganggu form pengisian data apa pun.

Familiaritas Warga: Ekosistem digital di Indonesia (e-commerce, ride-hailing) selalu meminta nomor ponsel sejak fase registrasi awal. Warga sudah sangat terbiasa dengan pola ini.

Transparansi Data: Dengan menambahkan helper text yang menjelaskan fungsi nomor tersebut (untuk kirim bukti bayar), rasa percaya (trust) warga terhadap sistem desa akan meningkat.

🛠️ 3. Instruksi Eksekusi (Step-by-Step Implementation)
TAHAP A: Penyesuaian Skema Database (Pemisahan Entitas)
Sangat penting untuk memahami perbedaan entitas. Nomor WhatsApp ini wajib disimpan di tabel users, bukan di tabel pelanggans. Alasannya: Nama di tabel pelanggan mungkin adalah nama Ayah (Kepala Keluarga), tetapi yang membuat akun digital dan membayar menggunakan smartphone mungkin adalah Anaknya.

Migration (*_create_users_table.php atau buat migrasi baru):
Tambahkan kolom no_whatsapp pada tabel users.

PHP
$table->string('no_whatsapp', 20)->nullable();
Model (app/Models/User.php):
Pastikan no_whatsapp dimasukkan ke dalam variabel $fillable.

PHP
protected $fillable = [
    'name',
    'email',
    'password',
    'no_whatsapp',
];
TAHAP B: Modifikasi UI Form Register (Breeze Blade)
Suntikkan input field Nomor WhatsApp ke dalam file form registrasi yang sudah menggunakan desain Asymmetric Split-Screen.

File: resources/views/auth/register.blade.php
Tambahkan kode berikut (ideal diletakkan setelah input Email dan sebelum Password):

HTML
<!-- Input No WhatsApp -->
<div class="animate-fade-up delay-150 opacity-0 mb-4">
    <label for="no_whatsapp" class="block text-sm font-bold text-[#0D2B45] mb-2 tracking-wide">Nomor WhatsApp</label>
    <div class="relative group">
        <input id="no_whatsapp" type="tel" name="no_whatsapp" :value="old('no_whatsapp')" required 
            class="w-full px-4 py-3.5 rounded-xl border-2 border-[#F4EFE6] bg-[#FDFAF6] text-[#1C2E3A] font-medium placeholder:text-[#A0B0BA] focus:bg-white focus:outline-none focus:ring-0 focus:border-[#1AACB4] transition-all duration-300 shadow-sm" 
            placeholder="Contoh: 081234567890">
    </div>
    <!-- Penjelasan fungsi data untuk membangun Trust -->
    <p class="text-[0.7rem] text-[#5A7080] mt-1.5 leading-relaxed">
        *Nomor WhatsApp digunakan oleh sistem untuk mengirimkan bukti pembayaran tagihan Anda secara otomatis.
    </p>
    <x-input-error :messages="$errors->get('no_whatsapp')" class="mt-2 text-red-500 text-xs" />
</div>
TAHAP C: Modifikasi Logika Controller
Pastikan data yang diinput oleh warga divalidasi dan disimpan saat registrasi berhasil.

File: app/Http/Controllers/Auth/RegisteredUserController.php

PHP
// 1. Tambahkan validasi pada method store()
$request->validate([
    // ... validasi lainnya ...
    'no_whatsapp' => ['required', 'string', 'max:20', 'regex:/^([0-9\s\-\+\(\)]*)$/'],
]);

// 2. Simpan ke dalam tabel Users
$user = User::create([
    'name' => $request->name,
    'email' => $request->email,
    'password' => Hash::make($request->password),
    'no_whatsapp' => $request->no_whatsapp, // Simpan data WA
]);