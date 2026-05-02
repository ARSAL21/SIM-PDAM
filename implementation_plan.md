# Rencana Implementasi: UI/UX Tagihan & Pembayaran

Melanjutkan blueprint yang telah kita bahas dan tingkatkan, berikut adalah rencana teknis komprehensif untuk mengimplementasikan fitur "Tagihan & Pembayaran" warga menggunakan arsitektur Livewire v4 (SFC) dan Alpine.js.

## User Review Required

> [!IMPORTANT]
> Mohon tinjau rencana di bawah ini. Fitur upload struk akan terintegrasi langsung dengan Spatie Media Library pada tabel `Pembayaran`. Apakah Anda setuju dengan pendekatan ini, atau Anda ingin agar sistem pembayaran ini di-*mock up* UI-nya saja terlebih dahulu tanpa *action* upload asli?

## Proposed Changes

---

### Backend & Service Layer

Untuk menjaga komponen Livewire tetap bersih, kita akan memperluas `StatistikAirService` atau membuat `TagihanService` khusus untuk menangani *query* data.

#### [NEW] `app/Services/TagihanService.php`
Service ini bertanggung jawab mengambil daftar tagihan berdasarkan ID pelanggan, dengan kemampuan *filtering* (Semua, Belum Bayar, Menunggu Verifikasi, dll.) dan pencarian (*search*). Service ini juga akan memuat (eager load) relasi ke `PencatatanMeter` dan `Pembayaran` (termasuk *reject notes*).

#### [MODIFY] `app/Models/Tagihan.php` & `app/Models/Pembayaran.php`
- Memastikan `HasMedia` trait dari Spatie Media Library terpasang di model `Pembayaran` (saat ini sudah ada koleksi `bukti_pembayaran`, namun pastikan trait `InteractsWithMedia` di-include).
- Menambahkan metode `isDitolak()` atau accessor status pembayaran.

---

### Frontend Components (SFC Livewire)

#### [NEW] `resources/views/pages/tagihan.blade.php`
Komponen Single-File (Volt API) yang mencakup:
- **State Variables**: `$search`, `$activeTab` (default: 'Semua'), `$tagihanList`, `$selectedTagihanId`, `$buktiTransfer` (tipe `TemporaryUploadedFile`).
- **Traits**: `WithFileUploads` (wajib untuk unggah file di Livewire).
- **Methods**:
  - `mount()`: Cek otorisasi dan identitas pelanggan.
  - `updatedActiveTab()` & `updatedSearch()`: Me-*refresh* daftar tagihan melalui service.
  - `bukaModalUpload($tagihanId)`: Menyiapkan tagihan yang akan dibayar.
  - `submitPembayaran()`: Menyimpan entri `Pembayaran` baru, menyematkan media struk transfer dari FilePond, dan merubah status tagihan menjadi 'Menunggu Verifikasi'.

#### [MODIFY] `routes/web.php`
- Menghubungkan rute *placeholder* `/tagihan` agar menggunakan `Route::livewire('/tagihan', 'pages.tagihan')`.

---

### UI & Alpine.js Integration

#### Komponen UI pada `pages/tagihan.blade.php`
- **Utility Toolbar**: Render Tab "Semua", "Belum Bayar", "Verifikasi", "Ditolak", "Lunas" menggunakan perulangan Blade yang bereaksi terhadap properti `$activeTab`.
- **Search Bar**: Input dengan atribut `wire:model.live.debounce.300ms="search"`.
- **Grid Tagihan (Billing Cards)**: Implementasi desain sesuai Blueprint. Jika status "Ditolak", akan merender kotak alert `bg-rose-50` yang menampilkan relasi `$tagihan->pembayarans->last()->catatan_admin`.
- **Upload Modal**: Dikontrol oleh state Alpine.js `x-data="{ showModal: @entangle('showModal') }"`. Menampilkan detail transfer dan komponen integrasi FilePond.
- **Empty State & Loading**: Memanfaatkan `wire:loading` pada blok list tagihan untuk memunculkan efek transisi saat data dimuat.

---

## Verification Plan

//ini saya akan coba sendiri KAU TIDAK PERLU LAKUKAN TESTING UNTUK HAL INI!!
### Manual Verification
1. Login sebagai user Warga.
2. Navigasi ke halaman **Tagihan & Pembayaran**.
3. Coba lakukan pencarian dan klik setiap tab filter.
4. Klik tombol **Upload Bukti Transfer** pada tagihan yang "Belum Bayar", pastikan modal terbuka mulus.
5. Upload dummy gambar melalui FilePond dan tekan Simpan.
6. Pastikan status tagihan langsung berubah menjadi "Menunggu Verifikasi" tanpa full *reload*.
7. Login sebagai Admin (di Filament) -> Tolak pembayaran tersebut dengan catatan -> Kembali login sebagai Warga -> Pastikan tagihan kembali menjadi "Ditolak" dan pesan dari admin muncul di dalam card.
