# Issue: Implementasi Resource `PencatatanMeter` — Form, Validasi, Koreksi, dan Skenario Lengkap

## 📌 Latar Belakang

`PencatatanMeter` adalah inti dari seluruh siklus billing SIAM-PDAM.
Setiap record yang lahir dari resource ini adalah sumber kebenaran tunggal
untuk kalkulasi tagihan. Karena itu ada dua hal yang harus dijamin:

1. **Tidak boleh ada angka yang salah masuk tanpa bisa dikoreksi** — admin
   harus punya jalan keluar jika terjadi kesalahan input di lapangan.
2. **Koreksi tidak boleh merusak data periode lain** — desain snapshot
   `angka_awal` yang sudah ada menjamin ini secara arsitektur.

Fitur ini hanya bisa diakses oleh **admin (petugas PDAM) lewat Filament**.
User/pelanggan tidak memiliki akses ke resource ini dalam bentuk apapun.

---

## 🔄 Flow Lengkap

### Flow Create (Input Bulanan)

```
Admin buka form PencatatanMeter baru
  │
  ├── Pilih meter aktif
  │     └── Sistem auto-populate angka_awal:
  │           ├── Ada pencatatan sebelumnya? → ambil angka_akhir terakhir
  │           └── Tidak ada?                → ambil meter_air.angka_awal
  │
  ├── Pilih periode (bulan + tahun)
  │     └── Validasi: kombinasi (meter_air_id + bulan + tahun) belum ada?
  │           └── Sudah ada → tolak, tampilkan pesan duplikat
  │
  ├── Input angka_akhir (satu-satunya input manual petugas)
  │     └── Live preview: pemakaian_m3 = angka_akhir - angka_awal
  │
  └── Submit
        ├── Server hitung ulang pemakaian_m3 (safeguard, tidak percaya client)
        ├── dicatat_oleh diisi otomatis: auth()->id()
        └── Record tersimpan → siap untuk di-generate tagihannya (langkah terpisah)
```

### Flow Edit (Koreksi)

```
Admin klik Edit pada record pencatatan
  │
  ├── Guard: apakah tagihan sudah berstatus Lunas?
  │     └── Ya  → blok edit, tampilkan pesan penjelasan
  │     └── Tidak → lanjut ke form edit
  │
  ├── Jika tagihan sudah ada (belum Lunas) → tampilkan WARNING eksplisit
  │     "Perubahan angka ini tidak otomatis mengupdate tagihan.
  │      Batalkan tagihan lama dan generate ulang setelah koreksi."
  │
  ├── Admin ubah angka_akhir
  │     └── Live preview pemakaian_m3 terupdate
  │
  ├── Admin wajib isi catatan_koreksi (required saat edit)
  │
  └── Submit
        ├── Server hitung ulang pemakaian_m3
        └── Record terupdate — tagihan lama TIDAK otomatis berubah
```

---

## 🗄️ Task 1: Migration — Tambah Kolom `catatan_koreksi`

Kolom ini nullable karena pada saat **create** tidak ada koreksi.
Hanya diisi saat **edit** dan wajib diisi.

```php
// php artisan make:migration add_catatan_koreksi_to_pencatatan_meter_table

Schema::table('pencatatan_meter', function (Blueprint $table) {
    $table->text('catatan_koreksi')
          ->nullable()
          ->after('pemakaian_m3')
          ->comment('Wajib diisi saat edit. Kosong jika ini input pertama kali.');
});
```

Setelah migration, tambahkan ke `$fillable` di model `PencatatanMeter`:

```php
#[Fillable([
    'meter_air_id', 'periode_bulan', 'periode_tahun',
    'angka_awal', 'angka_akhir', 'pemakaian_m3',
    'catatan_koreksi', // tambahkan ini
    'dicatat_oleh',
])]
```

---

## 🖥️ Task 2: Form Create `PencatatanMeterResource`

### Field `meter_air_id`

```php
Select::make('meter_air_id')
    ->label('Meter Air')
    ->required()
    ->searchable()
    ->getSearchResultsUsing(fn (string $search) =>
        MeterAir::where('status', 'Aktif')
            ->whereHas('pelanggan', fn ($q) =>
                $q->where('status_aktif', true)
            )
            ->where(fn ($q) => $q
                ->where('nomor_meter', 'like', "%{$search}%")
                ->orWhereHas('pelanggan.user', fn ($q) =>
                    $q->where('name', 'like', "%{$search}%")
                )
            )
            ->with('pelanggan.user')
            ->limit(20)
            ->get()
            ->mapWithKeys(fn ($meter) => [
                $meter->id => "[{$meter->nomor_meter}] " .
                              $meter->pelanggan->user->name
            ])
    )
    ->live()
    ->afterStateUpdated(function ($state, Set $set) {
        if (!$state) {
            $set('angka_awal', null);
            $set('pemakaian_m3', null);
            return;
        }

        $meter = MeterAir::with('pencatatanTerakhir')->find($state);
        if (!$meter) return;

        $angkaAwal = $meter->pencatatanTerakhir?->angka_akhir
                     ?? $meter->angka_awal;

        $set('angka_awal', $angkaAwal);
    }),
```

> **Filter wajib:** Hanya meter `status = 'Aktif'` milik pelanggan
> `status_aktif = true` yang muncul. Meter rusak, nonaktif, dan diganti
> tidak boleh bisa dipilih.

---

### Field Periode

```php
Grid::make(2)->schema([
    Select::make('periode_bulan')
        ->label('Bulan')
        ->required()
        ->options([
            1 => 'Januari',  2 => 'Februari', 3 => 'Maret',
            4 => 'April',    5 => 'Mei',       6 => 'Juni',
            7 => 'Juli',     8 => 'Agustus',   9 => 'September',
            10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ])
        ->default(now()->month),

    TextInput::make('periode_tahun')
        ->label('Tahun')
        ->required()
        ->numeric()
        ->minValue(2000)
        ->maxValue(now()->year + 1)
        ->default(now()->year),
]),
```

---

### Field `angka_awal`

```php
TextInput::make('angka_awal')
    ->label('Angka Awal (m³)')
    ->required()
    ->numeric()
    ->readOnly()
    ->dehydrated(true)  // WAJIB — field readOnly tidak ter-submit tanpa ini
    ->helperText(
        'Terisi otomatis dari angka akhir bulan lalu. ' .
        'Jika ini pencatatan pertama, diambil dari angka awal meter.'
    ),
```

---

### Field `angka_akhir`

```php
TextInput::make('angka_akhir')
    ->label('Angka Akhir (m³)')
    ->required()
    ->numeric()
    ->minValue(0)
    ->live(onBlur: true)
    ->afterStateUpdated(function ($state, Get $get, Set $set) {
        $angkaAwal  = (int) $get('angka_awal');
        $angkaAkhir = (int) $state;

        $set('pemakaian_m3', $angkaAkhir >= $angkaAwal
            ? $angkaAkhir - $angkaAwal
            : null
        );
    })
    ->rules([
        fn (Get $get): Closure => function (
            string $attribute,
            mixed $value,
            Closure $fail
        ) use ($get) {
            if ((int) $value < (int) $get('angka_awal')) {
                $fail(
                    'Angka akhir tidak boleh lebih kecil dari angka awal (' .
                    number_format((int) $get('angka_awal')) . ' m³).'
                );
            }
        },
    ]),
```

---

### Field `pemakaian_m3`

```php
TextInput::make('pemakaian_m3')
    ->label('Pemakaian (m³)')
    ->numeric()
    ->readOnly()
    ->dehydrated(true)  // WAJIB
    ->helperText('Dihitung otomatis: angka akhir dikurangi angka awal.'),
```

---

### Safeguard Server di `mutateFormDataBeforeCreate`

```php
protected function mutateFormDataBeforeCreate(array $data): array
{
    // Hitung ulang di server — tidak percaya kalkulasi dari client
    $data['pemakaian_m3'] = (int) $data['angka_akhir']
                          - (int) $data['angka_awal'];

    // Isi petugas yang mencatat secara otomatis
    $data['dicatat_oleh'] = auth()->id();

    // Pastikan catatan_koreksi kosong saat create
    $data['catatan_koreksi'] = null;

    return $data;
}
```

---

## 🖥️ Task 3: Form Edit `PencatatanMeterResource` (Koreksi)

Form edit **berbeda** dari form create dalam dua hal: field `meter_air_id`
dan `angka_awal` menjadi `disabled()` (tidak boleh diubah), dan field
`catatan_koreksi` menjadi wajib.

### Perbedaan di Form Edit

```php
// meter_air_id tidak boleh diubah saat edit
Select::make('meter_air_id')
    ->disabled()
    ->dehydrated(false), // tidak perlu dikirim ulang

// angka_awal tidak boleh diubah saat edit
TextInput::make('angka_awal')
    ->disabled()
    ->dehydrated(false),

// catatan_koreksi WAJIB saat edit
Textarea::make('catatan_koreksi')
    ->label('Alasan Koreksi')
    ->required()
    ->rows(3)
    ->helperText('Jelaskan alasan perubahan angka meter ini. Wajib diisi.')
    ->visibleOn('edit'),
```

### Warning Jika Tagihan Sudah Ada

```php
Placeholder::make('peringatan_koreksi')
    ->label('')
    ->content(new HtmlString(
        '<div style="padding: 0.75rem; background: #FAEEDA;
                     border-radius: 8px; color: #633806;">
            <strong>Perhatian:</strong> Pencatatan ini sudah memiliki tagihan
            aktif. Perubahan angka di sini <strong>tidak otomatis mengupdate
            jumlah tagihan</strong>. Batalkan tagihan lama dan generate ulang
            setelah koreksi ini disimpan.
        </div>'
    ))
    ->visibleOn('edit')
    ->visible(fn ($record) =>
        $record?->tagihan?->status_bayar !== null &&
        $record?->tagihan?->status_bayar !== 'Lunas'
    ),
```

### Safeguard Server di `mutateFormDataBeforeSave`

```php
protected function mutateFormDataBeforeSave(array $data): array
{
    // Ambil angka_awal dari database — bukan dari form (sudah disabled)
    $angkaAwal = $this->record->angka_awal;

    // Hitung ulang pemakaian berdasarkan angka_akhir yang baru
    $data['pemakaian_m3'] = (int) $data['angka_akhir'] - (int) $angkaAwal;

    return $data;
}
```

---

## 🔒 Task 4: Guard Edit & Delete

```php
// PencatatanMeterResource.php

public static function canEdit(Model $record): bool
{
    // Blok edit jika tagihan sudah Lunas — pembayaran final tidak bisa dikoreksi
    if ($record->tagihan?->status_bayar === 'Lunas') {
        return false;
    }
    return true;
}

public static function canDelete(Model $record): bool
{
    // Blok delete jika sudah ada tagihan dalam kondisi apapun
    return ! $record->tagihan()->exists();
}
```

Tambahkan tooltip yang informatif pada action:

```php
->actions([
    EditAction::make()
        ->tooltip(fn ($record) =>
            $record->tagihan?->status_bayar === 'Lunas'
                ? 'Tidak dapat diedit — tagihan sudah lunas.'
                : 'Edit pencatatan'
        ),

    DeleteAction::make()
        ->tooltip(fn ($record) =>
            $record->tagihan()->exists()
                ? 'Tidak dapat dihapus — sudah memiliki tagihan.'
                : 'Hapus pencatatan'
        ),
])
```

---

## 🖥️ Task 5: Tabel List `PencatatanMeterResource`

```php
->columns([
    TextColumn::make('meterAir.nomor_meter')
        ->label('Nomor Meter')
        ->searchable(),

    TextColumn::make('meterAir.pelanggan.user.name')
        ->label('Nama Pelanggan')
        ->searchable(),

    TextColumn::make('periode')
        ->label('Periode')
        ->getStateUsing(fn ($record) =>
            \Carbon\Carbon::create($record->periode_tahun, $record->periode_bulan)
                ->translatedFormat('F Y')
        )
        ->sortable(query: fn ($query, $direction) =>
            $query->orderBy('periode_tahun', $direction)
                  ->orderBy('periode_bulan', $direction)
        ),

    TextColumn::make('angka_awal')
        ->label('Angka Awal')
        ->numeric()
        ->alignRight(),

    TextColumn::make('angka_akhir')
        ->label('Angka Akhir')
        ->numeric()
        ->alignRight(),

    TextColumn::make('pemakaian_m3')
        ->label('Pemakaian (m³)')
        ->numeric()
        ->alignRight()
        ->weight(FontWeight::Medium),

    TextColumn::make('tagihan.status_bayar')
        ->label('Status Tagihan')
        ->badge()
        ->default('Belum Digenerate')
        ->color(fn (string $state): string => match ($state) {
            'Belum Bayar'         => 'warning',
            'Menunggu Verifikasi' => 'info',
            'Lunas'               => 'success',
            default               => 'gray',
        }),

    IconColumn::make('catatan_koreksi')
        ->label('Dikoreksi')
        ->boolean()
        ->trueIcon('heroicon-o-pencil-square')
        ->falseIcon('')
        ->trueColor('warning')
        ->tooltip(fn ($record) => $record->catatan_koreksi ?? '')
        ->getStateUsing(fn ($record) => filled($record->catatan_koreksi)),

    TextColumn::make('petugas.name')
        ->label('Dicatat Oleh')
        ->toggleable(isToggledHiddenByDefault: true),
])
->filters([
    SelectFilter::make('periode_tahun')
        ->label('Tahun')
        ->options(
            PencatatanMeter::selectRaw('DISTINCT periode_tahun')
                ->orderByDesc('periode_tahun')
                ->pluck('periode_tahun', 'periode_tahun')
        ),

    SelectFilter::make('periode_bulan')
        ->label('Bulan')
        ->options([
            1 => 'Januari',  2 => 'Februari', 3 => 'Maret',
            4 => 'April',    5 => 'Mei',       6 => 'Juni',
            7 => 'Juli',     8 => 'Agustus',   9 => 'September',
            10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ]),

    Filter::make('belum_digenerate')
        ->label('Belum Ada Tagihan')
        ->query(fn ($query) => $query->doesntHave('tagihan')),

    Filter::make('sudah_dikoreksi')
        ->label('Pernah Dikoreksi')
        ->query(fn ($query) => $query->whereNotNull('catatan_koreksi')),
])
```

---

## ✅ Skenario Uji Lengkap

### Create — Happy Path

| # | Kondisi | Langkah | Hasil yang Diharapkan |
|---|---|---|---|
| ✅ 1 | Pencatatan pertama meter baru | Pilih meter baru, `angka_awal` = 0 (dari `meter_air.angka_awal`), input `angka_akhir` = 80 | `pemakaian_m3` = 80, record tersimpan, `catatan_koreksi` = null |
| ✅ 2 | Pencatatan bulan berikutnya | Pilih meter yang sudah punya pencatatan, `angka_awal` = 80 (auto), input `angka_akhir` = 155 | `pemakaian_m3` = 75, record tersimpan |
| ✅ 3 | Live preview pemakaian | Input `angka_akhir` sambil mengetik | Field `pemakaian_m3` berubah real-time |
| ✅ 4 | `dicatat_oleh` otomatis | Submit form | Kolom terisi ID admin yang login, tidak ada field manual |

### Create — Edge Case & Error

| # | Kondisi | Langkah | Hasil yang Diharapkan |
|---|---|---|---|
| ❌ 5 | Angka akhir lebih kecil | `angka_awal` = 500, input `angka_akhir` = 300 | Validasi gagal: "Angka akhir tidak boleh lebih kecil dari angka awal (500 m³)" |
| ❌ 6 | Duplikat periode | Input pencatatan kedua untuk meter dan periode yang sama | Error ditangkap dari unique constraint, tampilkan pesan ramah — bukan raw SQL error |
| ❌ 7 | Pilih meter tidak aktif | — | Meter rusak, nonaktif, diganti tidak muncul di dropdown |
| ❌ 8 | Pilih meter milik pelanggan nonaktif | — | Tidak muncul di dropdown karena filter `status_aktif = true` |

### Edit (Koreksi) — Happy Path

| # | Kondisi | Langkah | Hasil yang Diharapkan |
|---|---|---|---|
| ✅ 9 | Koreksi sebelum tagihan digenerate | Edit `angka_akhir`, isi `catatan_koreksi` | Record terupdate, `pemakaian_m3` dihitung ulang server |
| ✅ 10 | Koreksi saat tagihan belum Lunas | Edit `angka_akhir`, isi `catatan_koreksi` | Record terupdate, warning tagihan perlu diregenerate muncul |
| ✅ 11 | Indikator koreksi di tabel | Setelah koreksi tersimpan | Kolom "Dikoreksi" menampilkan ikon pensil, hover menampilkan isi catatan |

### Edit (Koreksi) — Edge Case & Error

| # | Kondisi | Langkah | Hasil yang Diharapkan |
|---|---|---|---|
| ❌ 12 | Edit tanpa isi catatan koreksi | Kosongkan field `catatan_koreksi`, submit | Validasi gagal: "Alasan koreksi wajib diisi" |
| ❌ 13 | Edit pencatatan yang tagihannya sudah Lunas | Klik edit | Tombol edit tidak aktif, tooltip: "Tidak dapat diedit — tagihan sudah lunas" |
| ❌ 14 | Koreksi tidak mempengaruhi bulan lain | Edit `angka_akhir` Februari | Record Maret tetap menggunakan `angka_awal` lama (snapshot) — tidak ikut berubah |

### Delete

| # | Kondisi | Langkah | Hasil yang Diharapkan |
|---|---|---|---|
| ✅ 15 | Hapus pencatatan tanpa tagihan | Klik delete | Record terhapus |
| ❌ 16 | Hapus pencatatan yang sudah punya tagihan | Klik delete | Diblok: tooltip "Tidak dapat dihapus — sudah memiliki tagihan" |

---

## ⚠️ Catatan Teknis Penting

**`dehydrated(true)` wajib** pada `angka_awal` dan `pemakaian_m3`. Field
`readOnly()` secara default tidak ter-submit ke server oleh Filament.
Tanpa `dehydrated(true)`, kedua kolom tersimpan sebagai `null` atau `0`
di database tanpa ada error apapun — bug yang sulit di-debug.

**Double kalkulasi by design.** `pemakaian_m3` dihitung dua kali:
sekali di client via `live()` untuk preview UX, sekali di server via
`mutateFormDataBeforeCreate` dan `mutateFormDataBeforeSave` untuk
keamanan. Ini bukan redundansi — kalkulasi client bisa dimanipulasi
dari browser.

**Relasi `petugas()` non-standard.** FK `dicatat_oleh` bukan `user_id`.
Selalu akses via `$pencatatan->petugas`, bukan `$pencatatan->user`
(akan return `null`).

**Koreksi tidak cascade ke bulan berikutnya.** Ini by design — setiap
record `pencatatan_meter` menyimpan `angka_awal` sebagai snapshot statis.
Mengedit bulan Februari tidak akan mengubah `angka_awal` bulan Maret.
Konsekuensinya adalah gap kecil antar periode yang wajar secara bisnis
dan harus dijelaskan ke admin lewat UI.

**Urutan eksekusi:**
1. Jalankan migration tambah kolom `catatan_koreksi`
2. Update `$fillable` di model `PencatatanMeter`
3. Implementasi form create, form edit, guard, dan tabel list