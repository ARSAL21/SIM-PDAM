Blueprint UI/UX Final: Tagihan & Pembayaran
1. 🏗️ Header & Overview Section
Desain: Sama dengan Beranda, tanpa background gelap.

Title: "Tagihan & Pembayaran" (text-slate-800 text-2xl font-bold).

Subtitle: "Kelola kewajiban bulanan dan riwayat pembayaran air Anda." (text-slate-500).

2. 🎛️ Utility Toolbar (Filter & Search)
Search Bar: Input field membulat (rounded-xl) dengan background putih (bg-white), border tipis (border-slate-200), dan ikon kaca pembesar berwarna slate-400.

Status Filter Tabs (Pill Navigation): Bisa di-scroll menyamping di HP.

Semua (Default)

Belum Bayar (Badge/Aksen: Abu-abu/Netral)

Menunggu Verifikasi (Badge/Aksen: Amber/Kuning)

Ditolak (Badge/Aksen: Merah)

Lunas (Badge/Aksen: Emerald/Hijau)

3. 🚨 Dynamic Alert Banner (Global)
Hanya muncul jika ada urgensi dari Admin (di atas deretan Card):

Banner Ditolak: bg-red-50 border-red-200 text-red-800. Menampilkan pesan: "Ada pembayaran Anda yang ditolak. Silakan periksa catatan admin pada tagihan terkait."

Banner Pending: bg-amber-50 border-amber-200 text-amber-800. Menampilkan pesan: "Bukti transfer Anda sedang dalam antrean verifikasi Admin."

4. 💳 Main Content: "Billing Cards" Layout
Ini adalah jantung halamannya. Menggunakan Grid untuk desktop, Stack untuk mobile. Desain Card adalah bg-white shadow-sm border border-slate-200 rounded-2xl.

Header Card:

Kiri: Ikon Kalender + "Mei 2026" (font-bold).

Kanan: Kapsul Status (Belum Bayar / Verifikasi / Ditolak / Lunas).

Body Card (Data Fokus):

Total Tagihan: Rp 45.000 (Dicetak sangat besar text-3xl text-slate-800).

Rincian Mini: Meter Awal (100) ➔ Meter Akhir (115) = 15 m³.

Footer Card (Tombol Aksi - Perubahan Krusial):

Jika Belum Bayar / Ditolak: Muncul tombol "Upload Bukti Transfer" (bg-[#1AACB4]). Jika ditekan, tombol ini akan memicu Modal menggunakan Alpine.js.

Jika Ditolak (Ekstra): Muncul kotak kecil di atas tombol berisi Catatan Admin yang dicetak miring.

Jika Verifikasi / Lunas: Tombol hilang, diganti teks muted seperti "Menunggu konfirmasi admin" atau "Lunas pada 15 Mei 2026".

5. 🪟 Interactive Modal (Upload Bukti via FilePond)
Mekanisme: Menggunakan Alpine.js (x-data="{ showModal: false }") untuk pop-up yang halus tanpa me-reload halaman.

Isi Modal:

Atas: Instruksi Transfer (Nama Bank, No. Rekening Desa, Atas Nama, dengan tombol Copy to Clipboard).

Bawah: Komponen FilePond dengan desain terang (light theme, border dashed slate-300, teks slate-500). Warga tinggal drag & drop atau klik untuk memilih foto struk.

Tombol Simpan: Untuk men-submit gambar ke Livewire/Server.

6. 👻 Empty State
Jika filter dipilih tapi kosong (misal filter "Ditolak", tapi ternyata tidak ada yang ditolak).

Tampilan: Kotak bg-slate-50 border-dashed border-slate-200 dengan ilustrasi SVG tipis dan teks "Tidak ada tagihan dalam kategori ini."

---

### 💡 Analisis & Improvement Tambahan (Antigravity):
Berdasarkan analisis arsitektur Livewire v4 dan standar UX modern, berikut adalah beberapa poin krusial yang ditambahkan untuk melengkapi blueprint ini:

7. ⏳ Loading States & Skeleton Loaders
- **Search & Filter Loading**: Saat user mengetik di Search Bar atau berpindah Tab Filter, gunakan `wire:loading` untuk menampilkan indikator loading spinner kecil atau transisi opacity pada list tagihan.
- **Upload Loading**: Saat tombol "Simpan" pada modal ditekan, tombol harus masuk ke *state* `disabled` dengan teks "Mengunggah..." untuk mencegah *double-submit*.

8. 🧾 Fitur Cetak Struk / Download Invoice (PDF)
- **Kondisi**: Hanya tersedia untuk tagihan dengan status **Lunas**.
- **UI**: Pada Footer Card tagihan yang sudah Lunas, tambahkan tombol sekunder (outline) berbunyi "Unduh Struk" atau "Cetak PDF" bersanding dengan teks keterangan tanggal lunas.

9. ⚡ Detail Integrasi Livewire (SFC)
- **Search Bar**: Gunakan `wire:model.live.debounce.300ms="search"` agar pencarian terasa instan namun tidak membebani server.
- **Modal Binding**: Saat tombol "Upload Bukti" diklik, gunakan aksi Alpine/Livewire `wire:click="bukaModalUpload({{ $tagihan->id }})"` untuk memastikan data tagihan yang akan dibayar terikat (*bind*) dengan benar ke state komponen sebelum modal terbuka.
- **Validasi FilePond**: Pastikan integrasi FilePond menggunakan ekosistem Livewire (`wire:model="buktiTransfer"`) agar file temporary langsung dikelola oleh Livewire.

10. 💬 Visibilitas Catatan Penolakan (Reject Notes)
- Jika status **Ditolak**, desain *Body Card* harus di-expand sedikit untuk menampilkan kotak peringatan merah muda (bg-rose-50) berisi pesan eksplisit dari Admin mengapa pembayaran sebelumnya ditolak (misal: "Foto buram" atau "Nominal kurang").