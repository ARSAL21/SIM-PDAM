# Issue: Finalisasi Resource `PencatatanMeter` — Validasi Kronologis,
# Halaman View, dan Fitur Generate Tagihan

## 📌 Latar Belakang & Keputusan Desain

Berdasarkan diskusi dan blueprint yang sudah disepakati, fitur pencatatan meter
memiliki tiga aturan utama yang menjadi fondasi implementasi:

1. **Aturan Periode (Strict Chronological Insertion):** Admin bebas input periode
   lampau, dengan syarat belum ada pencatatan di periode yang lebih baru untuk
   meter yang sama. Jika sudah ada, sistem menolak keras.

2. **Tagihan Flat:** Tidak ada denda. Formula:
   `jumlah_tagihan = (pemakaian_m3 × tarif_per_kubik) + biaya_admin`

3. **Generate Tagihan Manual:** Dipicu oleh tombol eksplisit di halaman View
   dan baris tabel. Tombol otomatis hilang jika tagihan sudah ada.

---

## 🔄 Flow Lengkap

### Flow Create

```
Admin input angka_akhir
  │
  ├── Validasi 1: angka_akhir >= angka_awal
  ├── Validasi 2: composite unique (meter_air_id + bulan + tahun)
  ├── Validasi 3: Strict Chronological — tidak ada pencatatan
  │              di periode setelahnya untuk meter ini
  │
  └── Tersimpan
        ├── pemakaian_m3 dihitung server
        ├── dicatat_oleh = auth()->id()
        └── Status tagihan di tabel: "Belum Digenerate"
```

### Flow Edit (Koreksi)

```
Admin edit angka_akhir
  │
  ├── Guard: tagihan sudah Lunas? → blok edit
  ├── Validasi: catatan_koreksi wajib diisi
  ├── Validasi: angka_akhir >= angka_awal (dari DB, bukan form)
  ├── Validasi: Strict Chronological (exclude ID record sendiri)
  │
  └── Tersimpan
        ├── pemakaian_m3 dihitung ulang server
        └── Warning: jika sudah ada tagihan aktif, tagihan
            tidak otomatis terupdate — harus generate ulang
```

### Flow Generate Tagihan

```
Admin klik "Generate Tagihan"
  │
  ├── Guard: tagihan sudah ada? → tombol tidak tampil
  │
  ├── Ambil data kalkulasi:
  │     ├── pemakaian_m3 dari pencatatan ini
  │     ├── tarif_per_kubik dari pelanggan → golonganTarif
  │     └── biaya_admin dari pelanggan → golonganTarif
  │
  ├── Hitung: jumlah_tagihan = (pemakaian_m3 × tarif_per_kubik) + biaya_admin
  │
  ├── Generate no_tagihan otomatis (format: INV-YYYY-XXXXX)
  │
  ├── Buat record Tagihan:
  │     ├── pencatatan_meter_id = record ini
  │     ├── pelanggan_id = meter_air → pelanggan_id (BUKAN dari meter lama)
  │     ├── jumlah_tagihan = hasil kalkulasi
  │     └── status_bayar = 'Belum Bayar'
  │
  └── Kirim email notifikasi ke pelanggan
```

> **Catatan kritis:** `pelanggan_id` di tagihan WAJIB diambil dari
> `pencatatan->meterAir->pelanggan_id` — bukan dari cache atau relasi
> meter lama. Ini menjamin tagihan selalu terikat ke pelanggan yang
> benar meskipun meter pernah dioper kontrak ke pelanggan berbeda golongan.

---

## 🗄️ Task 1: Tambah Halaman `ViewPencatatanMeter`

Halaman View belum ada di resource. Perlu dibuat dan didaftarkan.

### 1a. Buat file `ViewPencatatanMeter.php`

```php
<?php

namespace App\Filament\Resources\PencatatanMeters\Pages;

use App\Filament\Resources\PencatatanMeters\PencatatanMeterResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewPencatatanMeter extends ViewRecord
{
    protected static string $resource = PencatatanMeterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
```

### 1b. Daftarkan di `PencatatanMeterResource::getPages()`

```php
public static function getPages(): array
{
    return [
        'index'  => ListPencatatanMeters::route('/'),
        'create' => CreatePencatatanMeter::route('/create'),
        'view'   => ViewPencatatanMeter::route('/{record}'),  // tambah ini
        'edit'   => EditPencatatanMeter::route('/{record}/edit'),
    ];
}
```

### 1c. Buat Infolist `PencatatanMeterInfolist`

```php
<?php

namespace App\Filament\Resources\PencatatanMeters\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PencatatanMeterInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Identitas Meter')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('meterAir.nomor_meter')
                            ->label('Nomor Meter'),

                        TextEntry::make('meterAir.pelanggan.user.name')
                            ->label('Nama Pelanggan'),

                        TextEntry::make('meterAir.pelanggan.no_pelanggan')
                            ->label('No. Pelanggan'),

                        TextEntry::make('meterAir.pelanggan.golonganTarif.nama_golongan')
                            ->label('Golongan Tarif'),
                    ]),

                Section::make('Data Pencatatan')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('periode')
                            ->label('Periode')
                            ->state(fn ($record) =>
                                \Carbon\Carbon::create(
                                    $record->periode_tahun,
                                    $record->periode_bulan
                                )->translatedFormat('F Y')
                            ),

                        TextEntry::make('petugas.name')
                            ->label('Dicatat Oleh'),

                        TextEntry::make('angka_awal')
                            ->label('Angka Awal (m³)')
                            ->numeric(),

                        TextEntry::make('angka_akhir')
                            ->label('Angka Akhir (m³)')
                            ->numeric(),

                        TextEntry::make('pemakaian_m3')
                            ->label('Pemakaian (m³)')
                            ->numeric()
                            ->weight(\Filament\Support\Enums\FontWeight::Bold),

                        TextEntry::make('created_at')
                            ->label('Waktu Input')
                            ->dateTime('d M Y, H:i'),
                    ]),

                Section::make('Status Tagihan')
                    ->schema([
                        TextEntry::make('tagihan.status_bayar')
                            ->label('Status')
                            ->badge()
                            ->default('Belum Digenerate')
                            ->color(fn (string $state): string => match ($state) {
                                'Belum Bayar'         => 'warning',
                                'Menunggu Verifikasi' => 'info',
                                'Lunas'               => 'success',
                                default               => 'gray',
                            }),

                        TextEntry::make('tagihan.no_tagihan')
                            ->label('No. Tagihan')
                            ->placeholder('—')
                            ->visible(fn ($record) => $record->tagihan),

                        TextEntry::make('tagihan.jumlah_tagihan')
                            ->label('Jumlah Tagihan')
                            ->money('IDR')
                            ->visible(fn ($record) => $record->tagihan),
                    ]),

                Section::make('Catatan Koreksi')
                    ->visible(fn ($record) => filled($record->catatan_koreksi))
                    ->schema([
                        TextEntry::make('catatan_koreksi')
                            ->label('')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
```

### 1d. Daftarkan di Resource

```php
// PencatatanMeterResource.php
public static function infolist(Schema $schema): Schema
{
    return PencatatanMeterInfolist::configure($schema);
}
```

---

## ✅ Task 2: Validasi Strict Chronological Insertion

Validasi ini diterapkan di dua tempat: form (client-side) dan server
(mutate methods).

### 2a. Tambah validasi di field `angka_akhir` form

Validasi kronologis diletakkan bersama validasi angka:

```php
TextInput::make('angka_akhir')
    ->rules([
        // Validasi 1: angka tidak boleh kurang dari angka_awal
        fn (Get $get): Closure => function (
            string $attribute, mixed $value, Closure $fail
        ) use ($get) {
            if ((int) $value < (int) $get('angka_awal')) {
                $fail(
                    'Angka akhir tidak boleh lebih kecil dari angka awal (' .
                    number_format((int) $get('angka_awal')) . ' m³).'
                );
            }
        },

        // Validasi 2: Strict Chronological Insertion
        fn (Get $get, ?Model $record): Closure => function (
            string $attribute, mixed $value, Closure $fail
        ) use ($get, $record) {
            $meterId = $get('meter_air_id') ?? $record?->meter_air_id;
            $bulan   = (int) $get('periode_bulan');
            $tahun   = (int) $get('periode_tahun');

            if (!$meterId || !$bulan || !$tahun) return;

            $adaPeriodeLebihBaru = \App\Models\PencatatanMeter::where('meter_air_id', $meterId)
                ->when($record?->id, fn ($q) => $q->where('id', '!=', $record->id))
                ->where(fn ($q) => $q
                    ->where('periode_tahun', '>', $tahun)
                    ->orWhere(fn ($q) => $q
                        ->where('periode_tahun', $tahun)
                        ->where('periode_bulan', '>', $bulan)
                    )
                )
                ->exists();

            if ($adaPeriodeLebihBaru) {
                $fail(
                    'Tidak dapat menyimpan pencatatan untuk periode ini. ' .
                    'Sudah ada pencatatan di periode yang lebih baru untuk meter ini. ' .
                    'Hapus pencatatan setelahnya terlebih dahulu jika ingin menyisipkan data.'
                );
            }
        },
    ]),
```

### 2b. Safeguard di `mutateFormDataBeforeCreate`

```php
protected function mutateFormDataBeforeCreate(array $data): array
{
    // Strict Chronological — server side
    $adaPeriodeLebihBaru = \App\Models\PencatatanMeter::where('meter_air_id', $data['meter_air_id'])
        ->where(fn ($q) => $q
            ->where('periode_tahun', '>', (int) $data['periode_tahun'])
            ->orWhere(fn ($q) => $q
                ->where('periode_tahun', (int) $data['periode_tahun'])
                ->where('periode_bulan', '>', (int) $data['periode_bulan'])
            )
        )
        ->exists();

    if ($adaPeriodeLebihBaru) {
        $this->halt();
        \Filament\Notifications\Notification::make()
            ->danger()
            ->title('Periode Tidak Valid')
            ->body(
                'Sudah ada pencatatan di periode yang lebih baru untuk meter ini. ' .
                'Hapus pencatatan setelahnya terlebih dahulu.'
            )
            ->send();
    }

    $data['pemakaian_m3']    = (int) $data['angka_akhir'] - (int) $data['angka_awal'];
    $data['dicatat_oleh']    = auth()->id();
    $data['catatan_koreksi'] = null;

    return $data;
}
```

### 2c. Safeguard di `mutateFormDataBeforeSave`

```php
protected function mutateFormDataBeforeSave(array $data): array
{
    $record    = $this->getRecord();
    $angkaAwal = $record->angka_awal;

    // Strict Chronological — server side (exclude ID sendiri)
    $adaPeriodeLebihBaru = \App\Models\PencatatanMeter::where('meter_air_id', $record->meter_air_id)
        ->where('id', '!=', $record->id)
        ->where(fn ($q) => $q
            ->where('periode_tahun', '>', (int) ($data['periode_tahun'] ?? $record->periode_tahun))
            ->orWhere(fn ($q) => $q
                ->where('periode_tahun', (int) ($data['periode_tahun'] ?? $record->periode_tahun))
                ->where('periode_bulan', '>', (int) ($data['periode_bulan'] ?? $record->periode_bulan))
            )
        )
        ->exists();

    if ($adaPeriodeLebihBaru) {
        $this->halt();
        \Filament\Notifications\Notification::make()
            ->danger()
            ->title('Periode Tidak Valid')
            ->body(
                'Sudah ada pencatatan di periode yang lebih baru. ' .
                'Tidak dapat menyimpan perubahan ini.'
            )
            ->send();
    }

    // catatan_koreksi wajib saat edit
    if (blank($data['catatan_koreksi'] ?? null)) {
        $this->halt();
        \Filament\Notifications\Notification::make()
            ->danger()
            ->title('Alasan koreksi wajib diisi.')
            ->send();
    }

    $data['pemakaian_m3'] = (int) $data['angka_akhir'] - (int) $angkaAwal;

    return $data;
}
```

---

## 🧾 Task 3: Fitur Generate Tagihan

### 3a. Service Class `GenerateTagihanService`

Logic generate tagihan dipisahkan ke service class agar bisa dipanggil
dari dua tempat (View page dan tabel) tanpa duplikasi kode.

```php
<?php

namespace App\Services;

use App\Models\PencatatanMeter;
use App\Models\Tagihan;
use Illuminate\Support\Str;

class GenerateTagihanService
{
    public static function execute(PencatatanMeter $pencatatan): Tagihan
    {
        // Ambil pelanggan dari meter saat ini — BUKAN dari relasi lama
        $pelanggan    = $pencatatan->meterAir->pelanggan;
        $golongan     = $pelanggan->golonganTarif;

        // Kalkulasi flat — tanpa denda
        $biayaPemakaian = $pencatatan->pemakaian_m3 * $golongan->tarif_per_kubik;
        $jumlahTagihan  = $biayaPemakaian + $golongan->biaya_admin;

        // Generate no_tagihan unik
        $noTagihan = 'INV-' . now()->format('Y') . '-' . strtoupper(Str::random(5));

        return Tagihan::create([
            'pencatatan_meter_id' => $pencatatan->id,
            'pelanggan_id'        => $pelanggan->id,
            'no_tagihan'          => $noTagihan,
            'jumlah_tagihan'      => $jumlahTagihan,
            'status_bayar'        => 'Belum Bayar',
        ]);
    }
}
```

### 3b. Tombol di Halaman `ViewPencatatanMeter`

```php
// ViewPencatatanMeter.php
protected function getHeaderActions(): array
{
    return [
        EditAction::make(),

        \Filament\Actions\Action::make('generate_tagihan')
            ->label('Generate Tagihan')
            ->icon('heroicon-o-document-plus')
            ->color('success')
            ->hidden(fn () => $this->getRecord()->tagihan()->exists())
            ->requiresConfirmation()
            ->modalHeading('Generate Tagihan')
            ->modalDescription(fn () =>
                'Tagihan akan digenerate untuk periode ' .
                \Carbon\Carbon::create(
                    $this->getRecord()->periode_tahun,
                    $this->getRecord()->periode_bulan
                )->translatedFormat('F Y') .
                '. Lanjutkan?'
            )
            ->action(function () {
                $tagihan = \App\Services\GenerateTagihanService::execute(
                    $this->getRecord()
                );

                // TODO: Kirim email notifikasi ke pelanggan
                // Mail::to($tagihan->pelanggan->user->email)
                //     ->queue(new TagihanBaruMail($tagihan));

                \Filament\Notifications\Notification::make()
                    ->success()
                    ->title('Tagihan berhasil digenerate.')
                    ->body("No. Tagihan: {$tagihan->no_tagihan}")
                    ->send();

                $this->refreshFormData([]);
            }),
    ];
}
```

### 3c. Tombol di Tabel `PencatatanMetersTable`

```php
// Di ->recordActions() PencatatanMetersTable
\Filament\Actions\Action::make('generate_tagihan')
    ->label('Generate Tagihan')
    ->icon('heroicon-o-document-plus')
    ->color('success')
    ->hidden(fn ($record) => $record->tagihan()->exists())
    ->requiresConfirmation()
    ->action(function ($record) {
        $tagihan = \App\Services\GenerateTagihanService::execute($record);

        \Filament\Notifications\Notification::make()
            ->success()
            ->title('Tagihan berhasil digenerate.')
            ->body("No. Tagihan: {$tagihan->no_tagihan}")
            ->send();
    }),
```

---

## ✅ Skenario Uji

### Validasi Strict Chronological

| # | Kondisi | Hasil yang Diharapkan |
|---|---|---|
| ✅ 1 | Input Maret, belum ada pencatatan sama sekali | Diizinkan |
| ✅ 2 | Input Maret, sudah ada Januari & Februari | Diizinkan |
| ❌ 3 | Input Maret, sudah ada pencatatan April | Ditolak: "Sudah ada pencatatan di periode yang lebih baru" |
| ❌ 4 | Input Desember 2024, sudah ada Januari 2025 | Ditolak — tahun lebih baru terdeteksi |
| ✅ 5 | Edit angka Maret, April sudah ada | Validasi exclude ID sendiri — tidak bentrok dengan dirinya |

### Generate Tagihan

| # | Kondisi | Hasil yang Diharapkan |
|---|---|---|
| ✅ 6 | Klik generate, tagihan belum ada | Tagihan ter-create, no_tagihan unik, status `Belum Bayar` |
| ✅ 7 | Pelanggan B oper kontrak dari A (beda golongan) | Tagihan menggunakan tarif golongan B, bukan A |
| ❌ 8 | Klik generate, tagihan sudah ada | Tombol tidak muncul sama sekali |
| ✅ 9 | Generate dari tabel list | Tagihan ter-create, notifikasi muncul di tempat |
| ✅ 10 | Generate dari halaman View | Tagihan ter-create, status di infolist terupdate |

---

## ⚠️ Catatan Teknis Penting

**`pelanggan_id` di tagihan wajib dari `meterAir->pelanggan_id`**
bukan dari cache atau parameter lain. Ini critical untuk kasus oper
kontrak lintas golongan tarif.

**`no_tagihan` harus unik** — gunakan kombinasi tahun + random string.
Jika terjadi collision (sangat jarang), Eloquent akan throw exception
karena ada unique constraint di kolom ini. Bisa ditambahkan retry loop
jika diperlukan.

**Tombol generate menggunakan `->hidden()` bukan `->disabled()`** —
tombol yang disabled masih terlihat dan membingungkan admin. Hidden
lebih bersih secara UX.

**Email notifikasi** di service class sengaja di-comment sebagai TODO —
implementasi Mailable belum ada dan akan dikerjakan di issue terpisah.

---

## 📋 Urutan Eksekusi

```
1. Buat ViewPencatatanMeter.php
2. Buat PencatatanMeterInfolist.php
3. Daftarkan view page dan infolist di PencatatanMeterResource
4. Update form — tambah validasi Strict Chronological di angka_akhir
5. Update CreatePencatatanMeter — tambah safeguard server
6. Update EditPencatatanMeter — tambah safeguard chronological
7. Buat GenerateTagihanService
8. Tambah tombol generate di ViewPencatatanMeter
9. Tambah tombol generate di PencatatanMetersTable
```
