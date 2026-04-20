# SIAM-PDAM — Dokumentasi Fitur & Skenario

> **Sistem Informasi dan Manajemen PDAM**
> Dokumen ini ditujukan untuk memberikan pemahaman menyeluruh tentang web app
> SIAM-PDAM kepada siapapun (developer atau AI) yang akan melanjutkan
> pengerjaan. Dokumen mencakup konteks sistem, stack teknologi, skema database,
> relasi model, semua fitur wajib, dan skenario uji per fitur.

---

## 1. Konteks Sistem

SIAM-PDAM adalah sistem manajemen penagihan air berbasis web untuk PDAM skala
kecil. Sistem ini mengelola siklus bulanan dari pencatatan angka meter di
lapangan hingga verifikasi pembayaran oleh petugas.

**Alur utama satu siklus:**
```
Catat bacaan meter → Hitung tagihan → Kirim notifikasi email →
User lihat tagihan → User bayar & upload bukti →
Admin verifikasi → Tagihan lunas → Cetak laporan PDF
```

**Dua aktor utama:**
- **Admin** — petugas PDAM. Menggunakan panel Filament.
- **User** — warga/pelanggan. Menggunakan halaman Livewire.

---

## 2. Tech Stack

| Layer | Teknologi |
|---|---|
| Framework | Laravel 13 |
| Admin panel | Filament 5 |
| Frontend interaktif | Livewire 4 |
| Role & permission | Filament Shield (Spatie Permission) |
| Upload file/media | Spatie Laravel Media Library + Filament plugin |
| Generate PDF | barryvdh/laravel-dompdf |
| Notifikasi email | Laravel Mail (Mailable + Queue) |

---

## 3. Skema Database

### Urutan eksekusi migrasi (wajib dipatuhi)
```
golongan_tarif → pelanggan → meter_air → pencatatan_meter → tagihan → pembayaran
```

---

### 3.1 Tabel `golongan_tarif`

| Kolom | Tipe | Keterangan |
|---|---|---|
| `id` | bigIncrements | PK |
| `nama_golongan` | string | Contoh: "Rumah Tangga A", "Niaga" |
| `tarif_per_kubik` | integer | Harga per m³, default 7000 |
| `biaya_admin` | integer | Biaya tetap per bulan, default 0 |
| `timestamps` | | |

**Formula tagihan:**
```
jumlah_tagihan = (pemakaian_m3 × tarif_per_kubik) + biaya_admin
```

---

### 3.2 Tabel `pelanggan`

| Kolom | Tipe | Keterangan |
|---|---|---|
| `id` | bigIncrements | PK |
| `user_id` | foreignId, unique | FK → `users`, cascadeOnDelete |
| `golongan_tarif_id` | foreignId | FK → `golongan_tarif`, restrictOnDelete |
| `no_pelanggan` | string, unique | Format auto-generate: `PLG-0001` |
| `alamat` | text | |
| `no_hp` | string, nullable | |
| `status_aktif` | boolean, default true | |
| `timestamps` | | |

> **Catatan penting:** Tabel `pelanggan` tidak memiliki kolom `nama`.
> Nama pelanggan diambil dari relasi `user.name`.
> Satu `user` hanya bisa menjadi satu `pelanggan` (relasi unique).

---

### 3.3 Tabel `meter_air`

| Kolom | Tipe | Keterangan |
|---|---|---|
| `id` | bigIncrements | PK |
| `pelanggan_id` | foreignId | FK → `pelanggan`, cascadeOnDelete |
| `nomor_meter` | string, unique, nullable | Nomor seri fisik dari pabrik |
| `merek` | string, nullable | Merek/pabrikan alat |
| `tanggal_pasang` | date | Tanggal unit dipasang |
| `angka_awal` | integer, default 0 | Angka display saat pertama dipasang |
| `status` | enum | `['Aktif', 'Rusak', 'Diganti']`, default `Aktif` |
| `timestamps` | | |

> **Alasan arsitektur (fatal flaw prevention):**
> `meter_air` dipisah dari `pelanggan` karena satu pelanggan bisa berganti
> meter berkali-kali. Jika bacaan langsung ke pelanggan, penggantian meter
> akan menyebabkan kalkulasi minus: meter lama berhenti di 1500, meter baru
> mulai dari 0 dan dicatat 20 → sistem hitung `20 - 1500 = -1480 m³`.
> Dengan entitas terpisah, meter lama dinonaktifkan dan meter baru memulai
> riwayat sendiri.
>
> **Catatan `angka_awal`:** Bukan untuk kalkulasi bulanan. Hanya sebagai
> fallback sumber `angka_awal` pada `pencatatan_meter` pertama meter tersebut,
> untuk kasus meter baru yang tidak dimulai dari 0000.

---

### 3.4 Tabel `pencatatan_meter`

| Kolom | Tipe | Keterangan |
|---|---|---|
| `id` | bigIncrements | PK |
| `meter_air_id` | foreignId | FK → `meter_air`, cascadeOnDelete |
| `periode_bulan` | tinyInteger | 1–12 |
| `periode_tahun` | smallInteger | Contoh: 2025 |
| `angka_awal` | integer | Di-populate otomatis, tidak boleh diedit |
| `angka_akhir` | integer | Input manual petugas |
| `pemakaian_m3` | integer | `angka_akhir - angka_awal`, dihitung server |
| `dicatat_oleh` | foreignId | FK → `users`, restrictOnDelete |
| `timestamps` | | |
| **UNIQUE** | | `(meter_air_id, periode_bulan, periode_tahun)` |

> **Logika `angka_awal`:**
> Saat record baru dibuat, sistem mencari `angka_akhir` dari record terakhir
> meter tersebut (`pencatatanTerakhir()`). Jika tidak ada record sebelumnya
> (bacaan pertama), ambil dari `meter_air.angka_awal`.
>
> **Mengapa `angka_awal` disimpan, bukan selalu di-derive:**
> Agar setiap record adalah snapshot immutable. Jika di-derive, mengedit
> record lama akan mengubah kalkulasi semua record setelahnya secara berantai.

---

### 3.5 Tabel `tagihan`

| Kolom | Tipe | Keterangan |
|---|---|---|
| `id` | bigIncrements | PK |
| `pencatatan_meter_id` | foreignId, unique | FK → `pencatatan_meter`, cascadeOnDelete |
| `pelanggan_id` | foreignId | FK → `pelanggan`, cascadeOnDelete |
| `no_tagihan` | string, unique | Format auto-generate |
| `jumlah_tagihan` | integer | Hasil kalkulasi: `(pemakaian × tarif) + biaya_admin` |
| `status_bayar` | enum | `['Belum Bayar', 'Menunggu Verifikasi', 'Lunas']`, default `Belum Bayar` |
| `timestamps` | | |

---

### 3.6 Tabel `pembayaran`

| Kolom | Tipe | Keterangan |
|---|---|---|
| `id` | bigIncrements | PK |
| `tagihan_id` | foreignId | FK → `tagihan`, cascadeOnDelete |
| `tanggal_bayar` | dateTime | |
| `jumlah_bayar` | integer | |
| `status_verifikasi` | enum | `['Pending', 'Disetujui', 'Ditolak']`, default `Pending` |
| `timestamps` | | |

> **Gap yang belum ada di schema saat ini (perlu ditambahkan):**
> - `catatan_admin` (text, nullable) — alasan penolakan dari admin
> - `verified_by` (foreignId → users) — audit trail siapa yang approve/tolak
> - `verified_at` (timestamp, nullable)
> - Bukti struk pembayaran ditangani oleh **Spatie Media Library**
>   sebagai polymorphic relation di tabel `media`, bukan kolom di tabel ini.

---

## 4. Relasi Eloquent (Ringkasan)

```
GolonganTarif  ──hasMany──►  Pelanggan
User           ──hasOne───►  Pelanggan
Pelanggan      ──hasMany──►  MeterAir
Pelanggan      ──hasMany──►  Tagihan
Pelanggan      ──hasManyThrough(MeterAir)──►  PencatatanMeter
MeterAir       ──hasMany──►  PencatatanMeter
MeterAir       ──hasOne───►  PencatatanMeter (pencatatanTerakhir / latestOfMany)
PencatatanMeter──hasOne───►  Tagihan
Tagihan        ──hasMany──►  Pembayaran
PencatatanMeter──belongsTo─►  User (via dicatat_oleh, non-standard FK)
```

### Relasi non-standard yang perlu diperhatikan

```php
// PencatatanMeter.php
// FK-nya bukan user_id, melainkan dicatat_oleh — parameter kedua wajib
public function petugas(): BelongsTo
{
    return $this->belongsTo(User::class, 'dicatat_oleh');
}
```

---

## 5. Fitur Admin (Filament 5 Panel)

### 5.1 Kelola Golongan Tarif

**Deskripsi:** CRUD master data tarif. Menentukan harga per m³ dan biaya admin
tetap per golongan pelanggan.

**Field form:**
- `nama_golongan` — TextInput, required
- `tarif_per_kubik` — TextInput numeric, required, min 0
- `biaya_admin` — TextInput numeric, required, min 0

**Kolom tabel list:** `nama_golongan`, `tarif_per_kubik` (format Rupiah),
`biaya_admin` (format Rupiah), jumlah pelanggan (count relasi).

**Batasan:** Golongan tarif yang sudah memiliki pelanggan tidak boleh dihapus
(`restrictOnDelete` di FK `pelanggan.golongan_tarif_id`).

| Skenario | Input | Hasil yang diharapkan |
|---|---|---|
| ✅ Tambah tarif baru | nama: "Rumah Tangga A", tarif: 3500, biaya_admin: 5000 | Record tersimpan, muncul di list |
| ✅ Edit tarif | Ubah tarif_per_kubik dari 3500 → 4000 | Record terupdate, tagihan lama tidak berubah (sudah snapshot) |
| ❌ Hapus tarif yang masih dipakai | Klik delete pada golongan yang punya pelanggan aktif | Database error / Filament menampilkan pesan gagal karena restrictOnDelete |
| ❌ Input tarif negatif | tarif_per_kubik: -1000 | Validasi gagal, form tidak tersubmit |

---

### 5.2 Kelola Data Pelanggan

**Deskripsi:** CRUD data pelanggan. Saat membuat pelanggan baru, admin bisa
sekaligus membuat akun user baru dari modal pop-up tanpa berpindah halaman.

**Field form:**
- `user_id` — Select dengan `relationship('user', 'name')`, `searchable()`,
  `preload()`, dan `createOptionForm([name, email, password])` untuk
  registrasi user baru langsung dari modal
- `golongan_tarif_id` — Select dengan `relationship('golonganTarif', 'nama_golongan')`
- `no_pelanggan` — TextInput, auto-generate format `PLG-0001`, `disabled()`
  (tidak boleh diisi manual)
- `alamat` — Textarea, required
- `no_hp` — TextInput, nullable
- `status_aktif` — Toggle, default true

**Kolom tabel list:** `no_pelanggan`, nama (via `user.name`), `golongan_tarif`
(via relasi), `alamat`, badge `status_aktif`.

**Filter:** `status_aktif`, `golongan_tarif_id`.

| Skenario | Input | Hasil yang diharapkan |
|---|---|---|
| ✅ Buat pelanggan + user baru sekaligus | Klik "Create new" di select user, isi name/email/password | User baru terbuat, langsung terpilih di form pelanggan |
| ✅ Buat pelanggan dari user yang sudah ada | Pilih user yang belum jadi pelanggan dari dropdown | Record pelanggan tersimpan dengan `no_pelanggan` auto-generate |
| ❌ Pilih user yang sudah jadi pelanggan | Pilih user yang sudah punya record pelanggan | Validasi gagal karena `user_id` unique di tabel `pelanggan` |
| ❌ Hapus pelanggan yang punya meter aktif | Klik delete pada pelanggan yang memiliki `meter_air` | Karena `cascadeOnDelete`, semua meter dan pencatatan ikut terhapus — perlu konfirmasi modal peringatan |
| ✅ Nonaktifkan pelanggan | Toggle `status_aktif` → false | Status berubah; pelanggan tidak muncul di dropdown pilihan meter baru |

---

### 5.3 Kelola Data Meter Air

**Deskripsi:** CRUD inventaris fisik alat meter. Satu pelanggan bisa memiliki
lebih dari satu meter dalam riwayat (aktif, rusak, diganti).

**Field form:**
- `pelanggan_id` — Select, `relationship('pelanggan', ...)`, `searchable()`.
  Label yang ditampilkan: `no_pelanggan` + `user.name` (bukan `pelanggan.nama`
  karena kolom nama tidak ada di tabel pelanggan).
  Filter: hanya tampilkan pelanggan dengan `status_aktif = true`.
- `nomor_meter` — TextInput, `unique(ignoreRecord: true)`, nullable
- `merek` — TextInput, nullable
- `tanggal_pasang` — DatePicker, default hari ini, required
- `angka_awal` — TextInput numeric, default 0. Hint: "Isi jika angka display
  saat dipasang bukan 0000". Pada Edit: `disabled()`.
- `status` — Select/ToggleButtons, opsi `['Aktif', 'Rusak', 'Diganti']`,
  default `Aktif`

**Kolom tabel list:** nama pelanggan (via relasi), `nomor_meter`, `merek`,
`tanggal_pasang`, badge `status` (warna berbeda per nilai).

**Filter:** `status`, `pelanggan_id`.

**Action per baris:** Edit, lihat riwayat pencatatan (link ke
`PencatatanMeterResource` dengan filter `meter_air_id`).

| Skenario | Input | Hasil yang diharapkan |
|---|---|---|
| ✅ Pasang meter baru (angka mulai 0) | angka_awal: 0, status: Aktif | Record tersimpan; pencatatan pertama nanti akan gunakan `angka_awal = 0` |
| ✅ Pasang meter baru (angka tidak dari 0) | angka_awal: 350, status: Aktif | Record tersimpan; pencatatan pertama akan gunakan `angka_awal = 350` sebagai fallback |
| ✅ Ganti meter rusak | Ubah status meter lama → Diganti, buat record meter baru → Aktif | Meter lama tidak bisa menerima pencatatan baru; riwayatnya tetap intact |
| ❌ Edit angka_awal setelah meter punya pencatatan | — | Field `angka_awal` sudah `disabled()` di form Edit; nilai tidak bisa diubah |
| ❌ Pilih pelanggan nonaktif | Filter di Select hanya tampilkan `status_aktif = true` | Pelanggan nonaktif tidak muncul sebagai opsi |
| ❌ Dua meter aktif untuk satu pelanggan | Buat meter kedua dengan status Aktif untuk pelanggan yang sudah punya meter Aktif | Secara schema tidak diblok di DB, tapi relasi `meterAktif()` menggunakan `latestOfMany()` — perlu validasi di form level: cek apakah pelanggan sudah punya meter Aktif sebelum simpan |

---

### 5.4 Pencatatan Meter (Input Bacaan Bulanan)

**Deskripsi:** Fitur ini digunakan petugas untuk menginput hasil pembacaan
meter di lapangan setiap bulan. Satu pencatatan menghasilkan satu tagihan.

**Field form:**
- `meter_air_id` — Select, `searchable()`. Label: `nomor_meter` + nama
  pelanggan. **Filter wajib:** hanya meter dengan `status = 'Aktif'`.
- `periode_bulan` — Select 1–12, default bulan berjalan
- `periode_tahun` — TextInput numeric, default tahun berjalan
- `angka_awal` — TextInput, `disabled()`, `dehydrated(true)`. Di-populate
  otomatis: ambil `angka_akhir` dari `pencatatanTerakhir()`. Jika bacaan
  pertama, ambil dari `meter_air.angka_awal`.
- `angka_akhir` — TextInput numeric, required. Validasi: >= `angka_awal`.
- `pemakaian_m3` — TextInput, `disabled()`, `dehydrated(true)`. Live preview
  via `reactive()` pada `angka_akhir`. Nilai final dihitung ulang di server
  (`mutateFormDataBeforeCreate`) — tidak percaya kalkulasi dari client.
- `dicatat_oleh` — Diisi otomatis dengan `auth()->id()` di
  `mutateFormDataBeforeCreate()`. Tidak tampil di form.

**Penting:** Setelah record `pencatatan_meter` tersimpan, sistem otomatis
generate record `tagihan` baru dengan kalkulasi:
```
jumlah_tagihan = (pemakaian_m3 × golongan_tarif.tarif_per_kubik) + golongan_tarif.biaya_admin
```
`no_tagihan` di-generate otomatis dengan format yang konsisten (contoh:
`INV-2025-001`).

| Skenario | Input | Hasil yang diharapkan |
|---|---|---|
| ✅ Pencatatan normal | meter aktif, angka_akhir: 1580, angka_awal auto: 1500 | pemakaian: 80 m³, tagihan ter-generate otomatis |
| ✅ Bacaan pertama meter baru | Tidak ada record sebelumnya, angka_awal di meter: 0 | `angka_awal` form terisi 0 dari `meter_air.angka_awal` |
| ✅ Bacaan pertama meter baru (tidak dari 0) | `meter_air.angka_awal` = 350 | `angka_awal` form terisi 350 |
| ❌ Input angka_akhir lebih kecil dari angka_awal | angka_akhir: 1400, angka_awal: 1500 | Validasi gagal: "Angka akhir tidak boleh lebih kecil dari angka awal" |
| ❌ Pencatatan ganda satu periode | Coba input pencatatan kedua untuk meter yang sama di bulan/tahun yang sama | Validasi gagal karena composite unique constraint `(meter_air_id, periode_bulan, periode_tahun)` |
| ❌ Pilih meter rusak/diganti | Filter Select hanya tampilkan `status = 'Aktif'` | Meter tidak aktif tidak muncul sebagai opsi |
| ❌ `dehydrated(false)` pada angka_awal | — | Nilai tidak tersimpan ke DB karena Filament skip disabled field. Wajib gunakan `dehydrated(true)` |

---

### 5.5 Verifikasi Pembayaran

**Deskripsi:** Setelah user mengupload bukti struk pembayaran, status tagihan
berubah ke `Menunggu Verifikasi`. Admin meninjau foto bukti dan memutuskan
Approve atau Tolak.

**Halaman list pembayaran:** Tampilkan semua `pembayaran` dengan
`status_verifikasi = 'Pending'` sebagai prioritas utama. Kolom: nama pelanggan,
`no_tagihan`, `jumlah_tagihan`, `jumlah_bayar`, `tanggal_bayar`, badge status.

**Halaman detail/review:**
- Tampilkan foto bukti pembayaran (via Spatie Media Library)
- Tampilkan detail tagihan: nama pelanggan, periode, pemakaian m³, jumlah tagihan
- Action **Approve:** ubah `pembayaran.status_verifikasi` → `Disetujui`,
  ubah `tagihan.status_bayar` → `Lunas`, isi `verified_by` dan `verified_at`,
  kirim email konfirmasi ke user
- Action **Tolak:** ubah `pembayaran.status_verifikasi` → `Ditolak`,
  ubah `tagihan.status_bayar` → `Belum Bayar`, isi `catatan_admin` dengan
  alasan penolakan, kirim email pemberitahuan penolakan ke user

| Skenario | Kondisi | Hasil yang diharapkan |
|---|---|---|
| ✅ Approve pembayaran valid | Foto bukti jelas, jumlah sesuai | Status tagihan → Lunas, email konfirmasi terkirim |
| ✅ Tolak dengan alasan | Foto buram atau jumlah tidak sesuai | Status tagihan → Belum Bayar, email penolakan + alasan terkirim |
| ❌ Approve tanpa lihat bukti | Admin klik Approve tanpa buka detail | Secara sistem bisa, tapi alur UI seharusnya memaksa admin melewati halaman detail terlebih dahulu |
| ❌ User upload ulang setelah ditolak | Status tagihan kembali ke Belum Bayar | User bisa upload bukti baru; record `pembayaran` baru ter-create, status kembali ke Menunggu Verifikasi |
| ❌ Tagihan yang sudah Lunas disubmit lagi | — | Tombol upload di sisi user tidak boleh muncul jika `status_bayar = 'Lunas'` |

---

### 5.6 Kirim Notifikasi Email

**Deskripsi:** Email dikirim otomatis di tiga titik dalam sistem. Tidak ada
halaman UI terpisah untuk kirim email manual kecuali tombol reminder.

**Tiga titik pengiriman otomatis:**
1. Setelah tagihan baru ter-generate dari pencatatan meter → email tagihan baru
   + nominal + tanggal jatuh tempo
2. Setelah pembayaran di-Approve → email konfirmasi pembayaran berhasil
3. Setelah pembayaran di-Tolak → email penolakan + alasan dari admin

**Trigger manual (opsional):** Tombol di Filament untuk kirim ulang notifikasi
tagihan ke pelanggan tertentu (untuk kasus email pertama tidak terkirim).

**Implementasi:** Laravel `Mailable` class + Queue. Log pengiriman disimpan
(tabel `notifikasi_log` jika ada, atau minimal log Laravel).

| Skenario | Kondisi | Hasil yang diharapkan |
|---|---|---|
| ✅ Email tagihan baru | Setelah pencatatan meter berhasil | Email terkirim ke alamat `user.email` pelanggan tersebut |
| ✅ Email konfirmasi lunas | Setelah admin approve | User menerima email dengan detail pembayaran dan status Lunas |
| ✅ Email penolakan | Setelah admin tolak | User menerima email berisi alasan penolakan |
| ❌ Email gagal kirim | SMTP down atau email tidak valid | Queue retry otomatis; jangan biarkan error menghentikan proses utama (verifikasi tetap tersimpan) |
| ❌ Kirim ke user yang tidak punya email | — | Validasi di registrasi: email wajib ada. Tapi tambahkan guard di Mailable untuk skip gracefully jika null |

---

### 5.7 Cetak / Ekspor Laporan PDF

**Deskripsi:** Admin bisa mencetak laporan pembayaran dalam format PDF.
Trigger: dari halaman detail pembayaran (cetak satu) atau dari halaman list
(cetak per periode/bulk).

**Konten laporan PDF per record pembayaran:**
- Nomor tagihan (`no_tagihan`)
- Nama pelanggan (`user.name` via relasi)
- Nomor pelanggan (`no_pelanggan`)
- Alamat pelanggan
- Golongan tarif
- Periode tagihan (bulan + tahun)
- Nomor meter (`nomor_meter`)
- Angka awal meter
- Angka akhir meter
- Pemakaian (m³)
- Tarif per kubik
- Biaya admin
- Total tagihan
- Tanggal bayar
- Status pembayaran
- Nama petugas yang memverifikasi (`verified_by` → `user.name`)

**Implementasi:** Blade template → `dompdf` render → response download atau
print dialog.

| Skenario | Kondisi | Hasil yang diharapkan |
|---|---|---|
| ✅ Cetak satu tagihan | Klik cetak di halaman detail pembayaran | PDF ter-download / print dialog muncul dengan data lengkap |
| ✅ Cetak bulk per periode | Filter bulan/tahun, klik cetak semua | PDF berisi semua pembayaran lunas pada periode tersebut |
| ❌ Cetak tagihan yang belum lunas | — | PDF tetap bisa digenerate tapi status akan menampilkan "Belum Bayar" / "Menunggu Verifikasi" sesuai kondisi aktual |
| ❌ Data pelanggan tidak lengkap | Pelanggan tanpa nomor HP | PDF tetap ter-generate; field kosong dibiarkan kosong, bukan error |

---

## 6. Fitur User (Livewire 4 Pages)

### 6.1 Registrasi & Login

**Deskripsi:** User mendaftarkan akun. Akun user yang baru register belum
otomatis menjadi pelanggan — admin yang mengaitkan akun user ke data pelanggan.

| Skenario | Input | Hasil yang diharapkan |
|---|---|---|
| ✅ Registrasi normal | name, email valid, password kuat | Akun terbuat, diarahkan ke dashboard |
| ✅ Login berhasil | email + password benar | Redirect ke dashboard user |
| ❌ Email sudah terdaftar | Email yang sama didaftarkan ulang | Validasi gagal: "Email sudah digunakan" |
| ❌ Login dengan kredensial salah | Password salah | Pesan error, tidak masuk |
| ❌ User login tapi belum jadi pelanggan | Akun ada tapi belum diaitkan ke `pelanggan` | Dashboard menampilkan pesan informatif: "Akun Anda belum terdaftar sebagai pelanggan. Hubungi kantor PDAM." |

---

### 6.2 Dashboard User

**Deskripsi:** Halaman utama setelah login. Menampilkan ringkasan status
tagihan aktif dan navigasi ke fitur lain.

**Konten dashboard:**
- Ringkasan tagihan aktif (belum bayar / menunggu verifikasi)
- Nominal tagihan terbaru
- Status tagihan terbaru dengan badge warna
- Tombol akses cepat ke riwayat pemakaian

**Guard:** Jika `auth()->user()->pelanggan` null, tampilkan pesan informatif,
bukan halaman kosong atau error 500.

---

### 6.3 Lihat Tagihan & Upload Bukti Pembayaran

**Deskripsi:** User melihat daftar tagihan dan bisa mengupload bukti pembayaran
untuk tagihan yang belum lunas.

**Daftar tagihan:** List semua tagihan milik pelanggan tersebut, diurutkan
terbaru. Filter status: Belum Bayar, Menunggu Verifikasi, Lunas.

**Detail tagihan:** Klik tagihan → detail lengkap (periode, pemakaian, rincian
biaya) + tombol upload bukti (hanya muncul jika `status_bayar = 'Belum Bayar'`).

**Alur upload:**
1. User klik "Bayar Sekarang"
2. Form upload: pilih file foto/scan bukti, input `jumlah_bayar`, `tanggal_bayar`
3. Submit → file disimpan via Spatie Media Library pada model `Pembayaran`
4. Record `pembayaran` ter-create dengan `status_verifikasi = 'Pending'`
5. `tagihan.status_bayar` berubah ke `'Menunggu Verifikasi'`

| Skenario | Kondisi | Hasil yang diharapkan |
|---|---|---|
| ✅ Upload bukti valid | File JPG/PNG < 2MB, jumlah sesuai | Pembayaran tersimpan, status tagihan → Menunggu Verifikasi |
| ✅ Lihat tagihan lunas | Tagihan sudah diverifikasi | Tombol upload tidak muncul, status badge hijau "Lunas" |
| ❌ Upload file bukan gambar | Upload file .exe atau .pdf | Validasi gagal: hanya terima jpg, jpeg, png |
| ❌ Upload di tagihan yang sedang Menunggu Verifikasi | — | Tombol upload tidak muncul; tagihan dalam status ini sedang diproses admin |
| ❌ Upload file terlalu besar | File > 5MB | Validasi gagal dengan pesan ukuran maksimum |
| ❌ User mencoba akses tagihan milik pelanggan lain | Manipulasi URL dengan ID tagihan lain | Guard: validasi `tagihan.pelanggan_id === auth()->user()->pelanggan->id`, return 403 jika tidak cocok |

---

### 6.4 Riwayat Pemakaian Air

**Deskripsi:** User melihat history konsumsi air bulanan dari semua meter yang
pernah dimiliki.

**Konten:** List `pencatatan_meter` via relasi `hasManyThrough` dari
`Pelanggan → MeterAir → PencatatanMeter`. Tampilkan: periode, nomor meter,
pemakaian m³, status tagihan terkait.

| Skenario | Kondisi | Hasil yang diharapkan |
|---|---|---|
| ✅ User dengan riwayat panjang | Pelanggan sudah 2+ tahun | Semua record muncul, diurutkan terbaru |
| ✅ User pernah ganti meter | Riwayat dari 2 meter berbeda | Semua pencatatan muncul dengan label nomor meter yang berbeda |
| ❌ User baru tanpa pencatatan | Belum ada record di `pencatatan_meter` | Tampilkan empty state: "Belum ada riwayat pemakaian" |

---

## 7. Role & Permission (Filament Shield)

| Resource / Halaman | Admin (Petugas) | User (Warga) |
|---|---|---|
| Filament admin panel | ✅ Full access | ❌ Tidak bisa akses |
| GolonganTarifResource | ✅ CRUD | ❌ |
| PelangganResource | ✅ CRUD | ❌ |
| MeterAirResource | ✅ CRUD | ❌ |
| PencatatanMeterResource | ✅ CRUD | ❌ |
| TagihanResource (admin) | ✅ View, generate, manage | ❌ |
| PembayaranResource (admin) | ✅ Verifikasi | ❌ |
| Dashboard user (Livewire) | ❌ | ✅ |
| Lihat tagihan sendiri | ❌ | ✅ |
| Upload bukti pembayaran | ❌ | ✅ |
| Lihat riwayat pemakaian | ❌ | ✅ |

---

## 8. Hal-Hal Kritis yang Harus Diperhatikan

1. **Nama kolom `nama` tidak ada di tabel `pelanggan`** — nama selalu diambil
   dari `user.name` via relasi. Jangan gunakan `$pelanggan->nama`.

2. **`dehydrated(true)` wajib** pada field `disabled()` di Filament yang tetap
   perlu disimpan ke DB (`angka_awal` dan `pemakaian_m3` di form pencatatan).
   Tanpa ini, nilai tidak ter-submit.

3. **Kalkulasi `pemakaian_m3` harus dihitung di server** (`mutateFormDataBeforeCreate`)
   meskipun sudah ada live preview di client via `reactive()`. Jangan percaya
   kalkulasi dari browser.

4. **Composite unique constraint** di `pencatatan_meter` mencegah double entry,
   tapi error dari DB perlu di-catch dan ditampilkan sebagai pesan yang
   ramah pengguna di Filament, bukan raw SQL error.

5. **`meter_air.angka_awal` tidak boleh diedit** setelah meter punya pencatatan.
   Enforce dengan `disabled()` di form Edit Filament.

6. **Penggantian meter** harus melalui alur: ubah status meter lama → `Diganti`,
   buat record `meter_air` baru dengan `status = 'Aktif'`. Jangan hapus meter
   lama karena riwayat `pencatatan_meter` masih terikat ke sana.

7. **`status_bayar` tagihan diubah dari dua tempat** — saat user upload bukti
   (→ Menunggu Verifikasi) dan saat admin approve/tolak (→ Lunas / Belum Bayar).
   Pastikan tidak ada race condition jika ada dua admin aktif bersamaan.

8. **Bukti pembayaran** disimpan via Spatie Media Library pada model `Pembayaran`.
   Bukan sebagai path string di kolom tabel. Gunakan `->addMediaFromRequest()`
   dan relasi `$pembayaran->getFirstMediaUrl('bukti')`.