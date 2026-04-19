# Issue: Setup CRUD Filament - Pelanggan Resource (Task 2 & 3)

## 📌 Deskripsi
Issue ini berfokus pada perancangan antarmuka form (Create/Edit) dan daftar tabel (List) untuk resource **Pelanggan** pada Filament Admin. Target utamanya adalah menjamin pengalaman *entry data* oleh admin lebih terarah agar tidak terjadi kesalahan pada penentuan relasi database, serta mempermudah pencarian/manajemen massal lewat tabel interaktif.

---

## 📋 Task 2: Perancangan Form (`PelangganForm.php`)
Fokus pada pengalaman admin saat menginput data agar tidak terjadi kesalahan relasi.

- [ ] **Grup Autentikasi (Relasi User)**
  - Gunakan instansiasi `Select` untuk validasi `user_id`.
  - **Fitur Wajib:** Implementasikan parameter `relationship('user', 'name')`, `searchable()`, dan `preload()`.
  - **Pro Tip:** Tambahkan modifier method `createOptionForm([...])` ke dalam `Select` User tersebut. Hal ini akan memungkinkan admin untuk meregistrasikan akun *user* baru sekaligus (meminta field `name`, `email`, dan `password`) dari pop-up modal tanpa harus berpindah modul halaman.

- [ ] **Identitas Pelanggan**
  - `no_pelanggan`: Gunakan `TextInput`. Berikan standard relasi validasi `unique(ignoreRecord: true)` dan atur sebagai `required()`.
  - *Saran Logic:* Berikan sifat `disabled()` atau `readOnly()` pada *field* formulir ini jika nomornya nanti direncanakan untuk terisi otomatis menggunakan logic format (misalnya: `PLG-0001`).

- [ ] **Kategori & Kontak**
  - `golongan_tarif_id`: Gunakan `Select` diikat dengan `relationship('golonganTarif', 'nama_golongan')` dan sifatnya wajb diisi `required()`.
  - `no_hp`: Gunakan `TextInput` lengkap dengan validasi tambahan `tel()` atau `numeric()`.
  - `alamat`: Gunakan komponen UI `Textarea` agar input alamat lengkap yang sifat teksnya lebih dari stau baris bisa dimasukkan dengan leluasa.

- [ ] **Status Kontrol**
  - `status_aktif`: Gunakan komponen `Toggle` (Switch Button). Jangan lupa mengganti label visualnya dengan parameter `label("Status Langganan Aktif")`.

---

## 📊 Task 3: Perancangan Tabel (`PelanggansTable.php`)
Fokus pada kemudahan pencarian data dan manajemen massal.

- [ ] **Kolom Informasi Utama (`TextColumn`)**
  - Menampilkan relasi `user.name` (Menampilkan nama asli akun, jangan menujukkan angka ID-nya). Atur kolom ini menjadi `searchable()` dan `sortable()`.
  - Menampilkan baris `no_pelanggan` sebagai nomor unik yang dapat di-`searchable()`.
  - Menampilkan relasi bertingkat `golonganTarif.nama_golongan` (cth: Rumah Tangga A).

- [ ] **Kolom Interaktif**
  - `status_aktif`: **Wajib menggunakan `ToggleColumn`** di tabel. Fitur ini sangat krusial agar petugas lapangan/admin bisa menonaktifkan status langganan pelanggan (memberi segel/mematikan air) secara *real-time* langsung dari list tabel tanpa perlu masuk ke mode perantara form *Edit*.

- [ ] **Filter & Default Sorting**
  - Integrasikan filter bawaan dengan menambahkan kelas `SelectFilter::make('golongan_tarif_id')->relationship('golonganTarif', 'nama_golongan')`. Tujuannya agar admin dapat memilah tampilan secara khusus (hanya melihat pelanggan kategori tertentu).
  - Urutkan urutan rendering default klasemen tabel berbasis *created_at* terbaru (paling baru mendaftar di urutan awal). Implementasikan dengan properti `defaultSort('created_at', 'desc')` langsung di rantai *build table*.
