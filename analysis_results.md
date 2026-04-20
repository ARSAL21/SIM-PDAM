# Laporan Analisis Kesenjangan & Bug (GolonganTarif, Pelanggan, MeterAir, User)

Melanjutkan penemuan bug dari pengujian Anda, saya telah menelusuri secara menyeluruh keempat *Resource* yang ada dan menyandingkannya dengan PRD (`Siam pdam feature documentation.md`). 

Berikut adalah rangkuman potensi **Bug & Ketidaksesuaian Sistem** yang harus kita selesaikan sebelum melangkah lebih jauh:

---

## 🏗️ 1. GolonganTarifResource (Bab 5.1)

1. 🔴 **[Bug] UI Error Saat Hapus Golongan Terpakai (Temuan Anda):**
   - **Kondisi:** Menekan Action `Delete` pada golongan bertarif yang sudah memiliki antrean `Pelanggan` menyebabkan browser melempar Fatal Error HTTP 500 karena menabrak proteksi Database (`ON DELETE RESTRICT`).
   - **Ekspektasi PRD:** "Filament menampilkan pesan gagal karena restrictOnDelete."
   - **Rencana Solusi:** Kita harus mencegat antrean dengan fitur Notification dan `beforeDelete` closure atau membajak fungsi validasi `checkIfRecordIsSelectableUsing()` pada bulk-action agar UI mengeluarkan notifikasi Pop-Up berwarna merah yang rapi.

2. 🟡 **[Bug Visual] Hitungan Jumlah Pelanggan Hilang:**
   - **Kondisi:** Di daftar tabel, tidak ada indikator jumlah rumah/pelanggan yang bernaung di bawah kategori tarif tertentu.
   - **Ekspektasi PRD:** "Kolom tabel list: nama_golongan, tarif_per_kubik, biaya_admin, **jumlah pelanggan (count relasi)**."
   - **Rencana Solusi:** Tambahkan `TextColumn::make('pelanggans_count')->counts('pelanggans')` di kelas tabel GolonganTarif.

---

## 👥 2. PelangganResource (Bab 5.2)

3. 🔴 **[Bug] Shortcut Buat Pelanggan Gagal Menerapkan Role (Temuan Anda):**
   - **Kondisi:** Fungsi *popup* tombol `+` di `user_id` tidak memiliki parameter input '*Roles*'. Hal ini menyebabkan User terbuat namun berstatus 'Hantu' alias tidak memiliki hak _Permission_.
   - **Ekspektasi PRD:** User baru harus terset-up lengkap (atau kita jadikan 'Pelanggan' role sebagai default).
   - **Rencana Solusi:** Kita dapat memasukkan `Select::make('roles')` di dalam `createOptionForm`, ATAU kita dapat memanfaatkan hook `mutateFormDataBeforeCreate` pada Pelanggan untuk otomatis memberikan panji Relasi `role = Pelanggan`.

4. 🟡 **[Bug Visual] Filter Tabel Tidak Sesuai Spesifikasi:**
   - **Kondisi:** Anda hanya bisa menyortir berdasarkan Golongan Tarif.
   - **Ekspektasi PRD:** "Filter: `status_aktif`, `golongan_tarif_id`".
   - **Rencana Solusi:** Tambahkan Filter tipe Ternary / Toggles untuk mendeteksi Pelanggan mana yang sedang terblokir (status Aktif = false).

---

## 🔧 3. MeterAirResource (Bab 5.3) *[Tambahan dari Review Kemarin]*

5. 🔴 **[Bug Integritas] Bypass Anti Double-Meter di Halaman Edit:**
   - **Kondisi:** Meskipun Dropdown sudah kita kunci agar orang yang sudah memiliki meteran aktif tidak muncul, _"TAPI jika Admin membuka halaman EDIT Meteran Lama yang sudah berstatus RUSAK, lalu merubahnya menjadi AKTIF, maka itu akan tembus"_ (Penyebap: tidak ada *rules* validasi simpan tingkat Lanjut saat action submit).
   - **Ekspektasi PRD:** "Dua meter aktif untuk satu pelanggan -> Secara schema tidak diblok, tapi... **perlu validasi di form level: cek apakah pelanggan sudah punya meter Aktif sebelum simpan**".
   - **Rencana Solusi:** Selipkan Custom Error Validation (`rule()`) pada properti `status`.

6. 🔴 **[Bug] Filter Pelanggan Mati/NonAktif Tembus:**
   - **Kondisi:** Dropdown Pelanggan di `MeterAirResource` masih bisa diisi oleh pelanggan yang berstatus dicabut langganannya (`status_aktif = false`).
   - **Ekspektasi PRD:** "Filter: hanya tampilkan pelanggan dengan `status_aktif = true`".

7. 🔴 **[Bug Integritas] Penggantian Angka Awal Meter Lama Tembus:**
   - **Kondisi:** Angka Awal meteran belum diblokir / `disabledOn('edit')`. Admin dapat sembarangan mengganti offset catatan lawas.

8. 🟡 **[Bug Visual] Kolom Merek & Riwayat Aksi Hilang:**
   - Sebagaimana yang dilaporkan sebelumnya (Kehilangan kolom `Merek` & Ketiadaan *Action Link* ke history pencatatannya).

---

## 🛡️ 4. UserResource (Arsitektur Keamanan)

9. 🟡 **[Bug Logika] Super Admin Tidak Bisa Melihat Akun Miliknya Sendiri:**
   - **Kondisi:** Pada kodingan sebelumnya (sewaktu proteksi keamanan UI), Fungsi Eloquent tabel `Users` di `UserResource` sepenuhnya mem-filter-keluar baris yang memegang *Role* `admin-PDAM`. Artinya akun admin sendiri lenyap dari tabel.
   - **Efek Samping:** Jika Sang Direktur/Super Admin lupa mengatur ulang Password atau mengubah Email miliknya sendiri, ia tidak bisa melakukannya dari Dashboard karena ia menyembunyikan identitasnya sendiri.

<br>

***

*(Laporan Analisis selesai)*. Semua daftar _Checklist_ PRD ini dapat kita jadikan target eliminasi (Refactoring) bersama secara bertahap.
