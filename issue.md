# Issue: Eksekusi Perbaikan Bug Sistem & Kesenjangan PRD (Tahap 1)

## 📌 Deskripsi Masalah

Menindaklanjuti hasil Testing sistem dari fungsionalitas yang baru dibangun (Sesuai dokumentasi PRD `Siam pdam feature documentation.md`), terdapat beberapa **celah keamanan UI (UX)** dan **celah logika fatal** yang memungkinkan data menjadi korup atau crash di kemudian hari. 

Issue ini ditujukan untuk menambal ke-$7$ Titik Celah tersebut secara simultan sebelum kita melangkah masuk ke dalam fitur Pembayaran & Pencatatan bulanan.

---

## 🛠️ Checklist Eksekusi Bug

### [Bug 1] Crash pada Aksi Hapus Golongan Tarif (GolonganTarifResource)
- **Problem:** Tombol Hapus pada antarmuka *Filament* menampilkan layar _Fatal Error 500 (SQLSTATE ON DELETE RESTRICT)_ setiap kali Admin secara tidak sengaja/sengaja mencoba menghapus blok Golongan Tarif yang masih memiliki pelanggan aktif.
- **Instruksi Perbaikan:** Pada menu `recordActions(DeleteAction::make())` dan `DeleteBulkAction`, tangkap kondisi validasi. Jika `$record->pelanggans()->exists()`, jangan izinkan _database query_ berjalan, namun munculkan UX Notifikasi *Error/Danger* Pop-up berwarna merah di antarmuka Admin (misal: `"Gagal. Golongan ini masih digunakan pelanggan!"`).

### [Bug 2] Kelahiran User Tanpa Peran / "Role Hantu" (PelangganResource)
- **Problem:** Pada menu Buat Pelanggan Baru, ketika Admin membypass form User baru dengan menekan (➕) *CreateOptionForm*, akun langsung terdaftar namun **terlahir ke sistem tanpa Role** apapun. Akibatnya, fungsionalitas _Spattie Shield_ tidak mencakup mereka.
- **Instruksi Perbaikan:** Buka file `PelangganForm.php`, lalu sisipkan parameter _Select dropdown_ `Roles` pada _array_ `createOptionForm([...])`, lengkap dengan relasi standar `->multiple()->preload()` persis seperti form standar `UserResource`. (Lebih bagus lagi jika secara default ia diberikan Role 'Pelanggan').

### [Bug 3] Celah Manipulasi Pelanggan Non-Aktif (MeterAirResource)
- **Problem:** Filter bawaan _Dropdown_ pelanggan saat pembuatan Meter Air hanya menolak nama orang yang punya meter aktif, *tetapi ironisnya membiarkan pelanggan yang sudah dicabut izinnya / terblokir* (`status_aktif = false`) untuk dipasangkan alat meter baru.
- **Instruksi Perbaikan:** Di file `MeterAirForm.php`, tambahkan rantai query builder `->where('status_aktif', true)` sebelum mengeksekusi logika lanjutan di field `pelanggan_id`.

### [Bug 4] Celah Menggandakan Meter Air lewat Fitur Edit (MeterAirResource)
- **Problem:** Poin perlindungan Anti Double-Meter hanya kuat di fase _Create_. Jika Admin masuk ke mode _EDIT_ untuk mengutak-atik meteran lawas yang notabene berstatus `Rusak`, admin bisa sembarangan memindahkannya kembali menjadi status `Aktif` meskipun pelanggan yang bersangkutan **SUDAH** punya alat pengganti yang aktif.
- **Instruksi Perbaikan:** Wajib menaruh **Custom Rule Validation (Closure)** di Form level untuk memasikan: *Tidak boleh ada penyimpanan* yang mana status form adalah 'Aktif', sementara `$record->pelanggan` memiliki relasi `meterAktif` lain.

### [Bug 5] Kerentanan Perubahan Angka Offset Meteran (MeterAirResource)
- **Problem:** Angka awal / `angka_awal` masih terbuka rentan dan bisa diutak-atik sewaktu Admin berada di dalam *mode Edit Form*. Hal tersebut bisa memanipulasi rentang kalkulasi pada riwayat penagihan yang telah lama berjalan. 
- **Instruksi Perbaikan:** Harus diunci! Tambahkan rantai `->disabledOn('edit')` di field input tersebut.

### [Bug 6] Kekurangan Tabel Parameter Visual (Berbagai Resource)
Selesaikan hal-hal tertinggal berikut agar antarmuka sejalan sesuai PRD:
- **`GolonganTarifsTable.php`:** Ketiadaan kolom _Jumlah Pelanggan_. Buat kolom pembaca agregat/kalkulasi via relasi (`pelanggans_count`).
- **`MeterAirsTable.php`:** Ketiadaan indikator merek. Sisipkan `TextColumn` untuk memanjangkan display teks dari Field `merek`.
- **`PelanggansTable.php`:** Tidak ada tombol cepat Filter pelanggan aktif. Buat klasifikasi filter via `TernaryFilter` untuk param `status_aktif`.

### [Bug 7] Hak Dasar Pembaruan Password "Super_Admin" Lumpuh (Security Enhancement)
- **Problem:** Demi perlindungan, akun sang Super Admin `admin-PDAM` sudah disembunyikan dari tabel `UsersTable`, namun sayangnya itu membuat Sang Admin ini tidak akan pernah bisa mengakses halamannya sendiri untuk mengganti *Password* atau merubah alamat *Email* pribadinya sendiri di Panel Admin jika diretas/handover.
- **Instruksi Perbaikan:** Harus menghidupkan (_Uncomment/Add_) fitur `->profile()` milik Filament 3 pada *provider / service config filament admin panel* utama, sehingga Super-Admin bisa mengatur dan mengganti sandinya dengan aman di "Bilah Pojok Kanan Atas" akun tanpa bersentuhan dengan Resource Warga!
