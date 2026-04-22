# Issue: Perbaikan & Peningkatan Fitur Meter Air — Guard Delete,
# Soft Delete, Filter, dan Integrasi Tampilan di Tabel Pelanggan

## 📌 Latar Belakang

Ditemukan bug critical: admin saat ini bisa menghapus record `meter_air`
secara permanen tanpa batasan apapun. Karena FK di `pencatatan_meter`
menggunakan `cascadeOnDelete`, penghapusan satu meter akan ikut menghapus
seluruh riwayat bacaan dan berdampak pada integritas data tagihan historis.

Selain perbaikan bug, issue ini juga mencakup peningkatan UX pada resource
`Pelanggan` untuk menampilkan status meter aktif dan navigasi langsung ke
data meter.

---

## 🐛 Task 1: Implementasi Soft Delete pada Model & Migration `MeterAir`

Meter air tidak boleh dihapus secara fisik dari database dalam kondisi apapun.
Gunakan Soft Delete sebagai mekanisme "nonaktifkan" tanpa kehilangan data.

### 1a. Tambah kolom `deleted_at` via migration baru

```php
// php artisan make:migration add_soft_deletes_to_meter_air_table
Schema::table('meter_air', function (Blueprint $table) {
    $table->softDeletes();
});
```

### 1b. Tambah trait `SoftDeletes` di model `MeterAir`

```php
use Illuminate\Database\Eloquent\SoftDeletes;

class MeterAir extends Model
{
    use HasFactory, SoftDeletes;
    // ...
}
```

Setelah ini, `MeterAir::find($id)->delete()` hanya mengisi `deleted_at`,
tidak menghapus row dari tabel. Query normal otomatis mengabaikan record
yang sudah soft-deleted.

---

## 🐛 Task 2: Guard Delete di `MeterAirResource` Filament

Tambahkan tiga lapis proteksi di resource Filament.

### Lapis 1 — Override `canDelete` di Resource

```php
// MeterAirResource.php
public static function canDelete(Model $record): bool
{
    // Block delete jika meter masih punya riwayat pencatatan
    if ($record->pencatatanMeters()->exists()) {
        return false;
    }
    // Block delete jika meter masih berstatus Aktif
    if ($record->status === 'Aktif') {
        return false;
    }
    return true;
}
```

### Lapis 2 — Tampilkan pesan yang jelas di UI

Jika `canDelete` return false, Filament otomatis menyembunyikan tombol
delete. Tambahkan notifikasi informatif jika admin mencoba aksi ini via
URL langsung:

```php
// Di halaman EditMeterAir
protected function getHeaderActions(): array
{
    return [
        DeleteAction::make()
            ->disabled(fn ($record) => $record->pencatatanMeters()->exists()
                || $record->status === 'Aktif')
            ->tooltip('Meter yang memiliki riwayat pencatatan atau masih
                       aktif tidak dapat dihapus.'),
    ];
}
```

### Lapis 3 — Ubah FK constraint `pencatatan_meter` (opsional tapi direkomendasikan)

Pertimbangkan migration alter untuk mengubah FK dari `cascadeOnDelete`
ke `restrictOnDelete` sebagai perlindungan terakhir di level database:

```php
// php artisan make:migration update_pencatatan_meter_foreign_key
Schema::table('pencatatan_meter', function (Blueprint $table) {
    $table->dropForeign(['meter_air_id']);
    $table->foreign('meter_air_id')
          ->references('id')
          ->on('meter_air')
          ->restrictOnDelete();
});
```

> Dengan ini, bahkan jika lapisan aplikasi berhasil dibypass, database
> akan menolak operasi delete secara keras.

---

## 🔍 Task 3: Filter Pelanggan Tanpa Meter Air

Tambahkan filter di `PelangganResource` tabel list untuk memudahkan
admin mengidentifikasi pelanggan yang belum memiliki meter terpasang.

### 3a. Tambah filter di `PelangganResource::table()`

```php
->filters([
    Filter::make('belum_punya_meter')
        ->label('Belum Punya Meter')
        ->query(fn (Builder $query) =>
            $query->whereDoesntHave('meterAirs')
        ),

    Filter::make('tidak_ada_meter_aktif')
        ->label('Tidak Ada Meter Aktif')
        ->query(fn (Builder $query) =>
            $query->whereDoesntHave('meterAirs', fn ($q) =>
                $q->where('status', 'Aktif')
            )
        ),
])
```

### 3b. Tambah badge indikator di kolom tabel

```php
// Di kolom tabel PelangganResource
TextColumn::make('status_meter')
    ->label('Status Meter')
    ->getStateUsing(function ($record) {
        if ($record->meterAirs()->doesntExist()) {
            return 'Belum Ada Meter';
        }
        if (!$record->meterAktif) {
            return 'Tidak Ada Meter Aktif';
        }
        return 'Aktif';
    })
    ->badge()
    ->color(fn (string $state): string => match ($state) {
        'Aktif'                 => 'success',
        'Tidak Ada Meter Aktif' => 'warning',
        'Belum Ada Meter'       => 'danger',
    }),
```

---

## 🖥️ Task 4: Tampilkan Info Meter Aktif di Tabel & Detail Pelanggan

### 4a. Tambah kolom nomor meter di tabel list `PelangganResource`

```php
TextColumn::make('meterAktif.nomor_meter')
    ->label('Nomor Meter')
    ->default('—')
    ->placeholder('Belum terpasang'),
```

### 4b. Tambah tombol navigasi "Lihat Meter" di action tabel

```php
->actions([
    ActionGroup::make([
        EditAction::make(),

        Action::make('lihat_meter')
            ->label('Lihat Meter')
            ->icon('heroicon-o-signal')
            ->url(fn ($record) =>
                MeterAirResource::getUrl('index',
                    ['tableFilters[pelanggan][value]' => $record->id])
            )
            ->openUrlInNewTab(false),
    ]),
])
```

### 4c. Tambah section meter air di halaman ViewPelanggan

Jika resource `Pelanggan` memiliki halaman `View`, tambahkan section
yang menampilkan daftar semua meter milik pelanggan tersebut beserta
statusnya:

```php
// Di ViewPelanggan atau InfoList PelangganResource
Section::make('Riwayat Meter Air')
    ->schema([
        RepeatableEntry::make('meterAirs')
            ->schema([
                TextEntry::make('nomor_meter')->label('Nomor Meter'),
                TextEntry::make('merek')->label('Merek'),
                TextEntry::make('tanggal_pasang')->label('Dipasang')->date(),
                TextEntry::make('angka_awal')->label('Angka Awal'),
                TextEntry::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn ($state) => match($state) {
                        'Aktif'   => 'success',
                        'Rusak'   => 'warning',
                        'Diganti' => 'gray',
                    }),
            ])
            ->columns(5),
    ]),
```

---

## 💡 Catatan Desain: Urutan Buat Pelanggan vs Meter Air

Pelanggan **boleh dibuat tanpa meter air** — ini sesuai praktik PDAM
di lapangan:

1. Warga mengajukan permohonan sambungan
2. Admin mendaftarkan pelanggan (bisa dilakukan di kantor)
3. Petugas lapangan survei dan pasang meter fisik
4. Admin input data meter air setelah pemasangan selesai

Ada jeda waktu antara langkah 2 dan 3. Memaksa input meter saat
create pelanggan akan menjadi bottleneck yang tidak perlu.

Yang penting: admin bisa dengan mudah mengidentifikasi pelanggan
yang belum punya meter (via filter dan badge di Task 3) agar tidak
ada yang terlewat.

---

## ⚠️ Urutan Eksekusi

1. Jalankan migration soft deletes `meter_air`
2. Jalankan migration alter FK `pencatatan_meter` (jika Task 2 Lapis 3
   diputuskan untuk diimplementasi)
3. Update model `MeterAir`
4. Update `MeterAirResource` dengan guard delete
5. Update `PelangganResource` dengan filter, kolom, dan action baru