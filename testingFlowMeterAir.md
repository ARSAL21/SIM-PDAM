# Flow Pengujian Lengkap — Fitur Meter Air

## Persiapan Sebelum Pengujian

Pastikan data berikut sudah ada di database sebelum mulai:

```
User A  → Pelanggan A (Aktif, Golongan GL1)
User B  → Pelanggan B (Aktif, Golongan GL2)
User C  → Pelanggan C (Aktif, Golongan GL1)
```

---

## SKENARIO 1 — Create Meter Air Normal

**Tujuan:** Memastikan meter baru bisa dibuat untuk pelanggan aktif.

### Langkah
1. Buka halaman **Meter Air → Create**
2. Pilih **Pelanggan A** di dropdown pelanggan
3. Isi `nomor_meter` = `MT-001`
4. Isi `merek` = `Itron`
5. Isi `tanggal_pasang` = hari ini
6. Biarkan `angka_awal` = `0`
7. Biarkan `status` = `Aktif`
8. Kosongkan field `melanjutkan_dari_id`
9. Klik **Save**

### Hasil yang Diharapkan
- ✅ Record tersimpan
- ✅ `melanjutkan_dari_id` = null
- ✅ `oper_dari_nomor_meter` = null
- ✅ `oper_dari_nama_pelanggan` = null
- ✅ `oper_angka_serah_terima` = null
- ✅ `tanggal_nonaktif` = null
- ✅ Section **Riwayat Oper Kontrak** tidak muncul di halaman View

---

## SKENARIO 2 — Validasi Duplikat Meter Aktif per Pelanggan

**Tujuan:** Memastikan satu pelanggan tidak bisa punya dua meter aktif.

### Langkah
1. Buka halaman **Meter Air → Create**
2. Pilih **Pelanggan A** (yang sudah punya MT-001 Aktif dari Skenario 1)
3. Isi `nomor_meter` = `MT-002`
4. Set `status` = `Aktif`
5. Klik **Save**

### Hasil yang Diharapkan
- ❌ Validasi gagal
- ❌ Pesan: *"Pelanggan ini sudah memiliki alat meter berstatus Aktif lainnya"*
- ❌ Record tidak tersimpan

---

## SKENARIO 3 — Validasi Nomor Meter Duplikat Aktif

**Tujuan:** Memastikan tidak ada dua meter aktif dengan nomor yang sama.

### Langkah
1. Buka halaman **Meter Air → Create**
2. Pilih **Pelanggan B**
3. Isi `nomor_meter` = `MT-001` (sama dengan milik Pelanggan A yang masih Aktif)
4. Set `status` = `Aktif`
5. Klik **Save**

### Hasil yang Diharapkan
- ❌ Validasi gagal
- ❌ Pesan: *"Nomor meter MT-001 sudah digunakan oleh meter lain yang masih berstatus Aktif"*
- ❌ Record tidak tersimpan

---

## SKENARIO 4 — Nonaktifkan Pelanggan → Meter Ikut Nonaktif Otomatis

**Tujuan:** Memastikan observer berjalan dan mengisi `tanggal_nonaktif`.

### Langkah
1. Buka halaman **Pelanggan → Edit Pelanggan A**
2. Toggle `status_aktif` → **false**
3. Klik **Save**

### Hasil yang Diharapkan
- ✅ `pelanggan.status_aktif` = false
- ✅ MT-001 milik Pelanggan A otomatis berubah `status` = `Nonaktif`
- ✅ MT-001 `tanggal_nonaktif` = hari ini (bukan null, bukan `updated_at`)
- ✅ Buka halaman **View MT-001** → field *"Pelanggan Sebelumnya Berhenti Pada"* menampilkan tanggal hari ini dengan format `dd M YYYY`

---

## SKENARIO 5 — Blok Reaktivasi Meter Milik Pelanggan Nonaktif

**Tujuan:** Memastikan meter tidak bisa diaktifkan kembali selama pelanggannya nonaktif.

### Langkah
1. Buka halaman **Meter Air → Edit MT-001** (Pelanggan A sudah nonaktif)
2. Ubah `status` dari `Nonaktif` → `Aktif`
3. Klik **Save**

### Hasil yang Diharapkan
- ❌ Validasi gagal
- ❌ Pesan: *"Meter tidak dapat diaktifkan karena pelanggan [nama Pelanggan A] sedang nonaktif"*
- ❌ `status` MT-001 tetap `Nonaktif`

---

## SKENARIO 6 — Oper Kontrak (Lokasi Sama, Pelanggan Baru)

**Tujuan:** Memastikan flow oper kontrak lengkap berjalan dengan benar,
snapshot tersimpan, dan keterlacakan tampil di infolist.

### Prasyarat
- MT-001 milik Pelanggan A berstatus `Nonaktif` (hasil Skenario 4)
- Pelanggan A berstatus nonaktif
- Pelanggan B berstatus aktif, belum punya meter

### Langkah
1. Buka halaman **Meter Air → Create**
2. Pilih **Pelanggan B** di dropdown pelanggan
3. Di field `melanjutkan_dari_id`, ketik `MT-001` atau nama Pelanggan A
4. Verifikasi: MT-001 muncul di dropdown hasil pencarian
5. Pilih MT-001
6. Verifikasi field berikut ter-populate otomatis:
   - `nomor_meter` = `MT-001`
   - `merek` = `Itron`
   - `angka_awal` = angka terakhir pencatatan MT-001 (atau `meter_air.angka_awal` jika belum ada pencatatan)
   - `tanggal_oper_kontrak` = hari ini
7. Klik **Save**

### Hasil yang Diharapkan
- ✅ Record meter baru tersimpan dengan `pelanggan_id` = Pelanggan B
- ✅ `melanjutkan_dari_id` = ID MT-001 lama
- ✅ `tanggal_oper_kontrak` = hari ini
- ✅ `oper_dari_nomor_meter` = `MT-001` (snapshot)
- ✅ `oper_dari_nama_pelanggan` = nama Pelanggan A (snapshot)
- ✅ `oper_angka_serah_terima` = angka yang benar (snapshot)
- ✅ Buka **View meter baru** → Section *"Riwayat Oper Kontrak"* muncul dengan data:
  - *Melanjutkan dari Meter*: `MT-001 — milik [nama Pelanggan A]`
  - *Pelanggan Sebelumnya Berhenti Pada*: tanggal nonaktif Pelanggan A
  - *Tanggal Oper Kontrak*: hari ini
- ✅ Buka **View MT-001 lama** → Section *"Riwayat Oper Kontrak"* muncul dengan data:
  - *Diteruskan ke Pelanggan*: nama Pelanggan B — mulai: hari ini

---

## SKENARIO 7 — Oper Kontrak Diblok: Meter Aktif Milik Pelanggan Nonaktif

**Tujuan:** Memastikan meter aktif milik pelanggan nonaktif tidak bisa
dijadikan sumber oper kontrak (state invalid tidak boleh bisa di-oper).

### Setup
Buat kondisi buatan langsung di DB (atau via Tinker):
```php
// Paksa meter jadi Aktif meski pelanggannya nonaktif — simulasi state invalid
MeterAir::where('nomor_meter', 'MT-001')->update(['status' => 'Aktif']);
```

### Langkah
1. Buka halaman **Meter Air → Create**
2. Pilih **Pelanggan C**
3. Di field `melanjutkan_dari_id`, ketik `MT-001`

### Hasil yang Diharapkan
- ❌ MT-001 tidak muncul di dropdown oper kontrak
- ❌ Alasan: filter hanya menampilkan meter `Nonaktif` dengan pelanggan `status_aktif = false`

### Cleanup
```php
// Kembalikan ke state Nonaktif setelah test
MeterAir::where('nomor_meter', 'MT-001')->update(['status' => 'Nonaktif']);
```

---

## SKENARIO 8 — Ketahanan Snapshot Saat Meter Lama Terhapus

**Tujuan:** Memastikan data oper kontrak di meter baru tetap tampil
meskipun `melanjutkan_dari_id` menjadi null akibat meter lama terhapus.

### Prasyarat
- Skenario 6 sudah dijalankan
- MT-001 lama belum punya pencatatan (agar bisa dihapus)

### Langkah
1. Buka halaman **Meter Air → Edit MT-001 lama**
2. Klik **Delete**
3. Konfirmasi delete
4. Buka halaman **View meter baru** milik Pelanggan B

### Hasil yang Diharapkan
- ✅ MT-001 lama terhapus (karena belum punya pencatatan)
- ✅ `melanjutkan_dari_id` di meter baru = null (nullOnDelete bekerja)
- ✅ Section *"Riwayat Oper Kontrak"* di meter baru **tetap menampilkan** data dari kolom snapshot:
  - `oper_dari_nomor_meter` = `MT-001`
  - `oper_dari_nama_pelanggan` = nama Pelanggan A
  - `oper_angka_serah_terima` = angka yang benar
- ✅ Hanya link navigasi ke meter lama yang hilang — data historisnya tetap ada

---

## SKENARIO 9 — Delete Meter yang Punya Riwayat Pencatatan

**Tujuan:** Memastikan guard delete berjalan di semua lapisan.

### Prasyarat
MT-002 milik Pelanggan B (aktif) sudah punya minimal 1 record pencatatan meter.

### Langkah A — Via Tabel List
1. Buka halaman **Meter Air → List**
2. Klik Delete pada MT-002
3. Konfirmasi

### Langkah B — Via Halaman Edit
1. Buka halaman **Meter Air → Edit MT-002**
2. Klik Delete di header

### Hasil yang Diharapkan (kedua langkah)
- ❌ Delete diblok
- ❌ Notifikasi: *"Meter air ini tidak dapat dihapus karena sudah memiliki riwayat pencatatan"*
- ❌ Record tidak terhapus

---

## SKENARIO 10 — Delete Meter Aktif Tanpa Pencatatan

**Tujuan:** Memastikan meter aktif tidak bisa dihapus meskipun belum punya pencatatan.

### Langkah
1. Buat meter baru untuk Pelanggan C, status Aktif, tanpa pencatatan apapun
2. Coba delete via tabel list atau halaman edit

### Hasil yang Diharapkan
- ❌ Delete diblok
- ❌ Notifikasi: *"Meter air berstatus Aktif tidak dapat dihapus. Ubah statusnya terlebih dahulu"*

---

## SKENARIO 11 — Pelanggan Diaktifkan Kembali, Meter Tetap Nonaktif

**Tujuan:** Memastikan mengaktifkan kembali pelanggan tidak otomatis
mengaktifkan meternya — admin harus lakukan secara eksplisit.

### Langkah
1. Buka **Pelanggan A → Edit** (saat ini nonaktif)
2. Toggle `status_aktif` → **true**
3. Klik **Save**
4. Cek status MT-001 lama

### Hasil yang Diharapkan
- ✅ `pelanggan.status_aktif` = true
- ✅ MT-001 tetap `Nonaktif` — observer hanya berjalan satu arah (nonaktifkan, tidak mengaktifkan)
- ✅ Admin harus secara eksplisit edit MT-001 untuk mengaktifkan kembali

---

## Checklist Ringkasan

| # | Skenario | Yang Diuji |
|---|---|---|
| 1 | Create normal | Form create dasar, snapshot null |
| 2 | Duplikat meter aktif | Validasi satu pelanggan satu meter aktif |
| 3 | Duplikat nomor aktif | Validasi nomor meter unik per status Aktif |
| 4 | Nonaktifkan pelanggan | Observer + `tanggal_nonaktif` terisi |
| 5 | Blok reaktivasi | Validasi status field saat edit |
| 6 | Oper kontrak normal | Full flow + snapshot + infolist |
| 7 | Oper kontrak diblok | Filter dropdown hanya meter Nonaktif + pelanggan nonaktif |
| 8 | Ketahanan snapshot | Data tetap ada meski FK jadi null |
| 9 | Delete + pencatatan | Guard delete berlapis |
| 10 | Delete meter aktif | Guard delete berlapis |
| 11 | Reaktivasi pelanggan | Observer hanya satu arah |