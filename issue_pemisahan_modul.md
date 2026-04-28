[Refactoring & Feature] Pemisahan Modul Tagihan (TagihanResource) & Implementasi Proforma Invoice
Issue Type: Refactoring / Enhancement
Priority: High
Assignee: [Nama Developer]
Estimated Effort: 2-3 Hours

1. Latar Belakang (Context & Problem Statement)
Saat ini, PencatatanMeterResource memiliki dua tanggung jawab yang melanggar prinsip SRP (Single Responsibility Principle): mengelola input data teknis lapangan (kubikasi air) sekaligus mengeksekusi logika finansial (Generate Tagihan).

Masalah UX (Blind Generation):
Admin memicu pembuatan tagihan melalui modal konfirmasi ("Apakah Anda yakin ingin generate?"). Admin tidak dapat melihat estimasi rupiah, status smart waiver, atau total tagihan sebelum data resmi tersimpan di database. Jika terjadi kesalahan kalkulasi, tagihan harus dihapus/direvisi yang menyebabkan data kotor.

Solusi Arsitektur (Proforma Invoice Pattern):

Mencabut fungsi Generate langsung dari Pencatatan Meter.

Membuat TagihanResource khusus di bawah grup "Manajemen Transaksi/Keuangan".

Mengubah aksi "Generate Tagihan" menjadi tombol redirect (Bawa parameter ID) menuju halaman Create Tagihan.

Menampilkan Preview/Draft tagihan (Proforma Invoice) di halaman Create agar Admin bisa memvalidasi angka sebelum menekan tombol "Simpan".

2. Out of Scope (Di Luar Cakupan)
Agar fokus pengerjaan tetap terjaga, hal-hal berikut DILARANG dikerjakan dalam tiket ini:

Pembuatan modul Pembayaran (Verifikasi).

Fitur notifikasi WhatsApp/Email.

Fitur desain dan cetak Struk/Invoice PDF.

3. Instruksi Pengerjaan (Step-by-Step Implementation)
TAHAP A: Inisialisasi TagihanResource
[ ] Jalankan perintah: php artisan make:filament-resource Tagihan

[ ] Konfigurasi properti di TagihanResource.php:

PHP
protected static ?string $model = Tagihan::class;
protected static string|\UnitEnum|null $navigationGroup = 'Manajemen Keuangan';
protected static ?string $navigationLabel = 'Data Tagihan';
protected static ?int $navigationSort = 1;
TAHAP B: Modifikasi Tombol di PencatatanMeterResource
Ubah Action "Generate Tagihan" yang mengeksekusi Service menjadi tombol navigasi biasa.

[ ] Di PencatatanMetersTable.php (Tabel) dan ViewPencatatanMeter.php (Header Actions), ganti Action lamanya menjadi:

PHP
use Filament\Actions\Action; // atau Tables\Actions\Action

Action::make('buat_tagihan')
    ->label('Buat Tagihan')
    ->icon('heroicon-o-document-plus')
    ->color('success')
    ->hidden(fn ($record) => $record->tagihan()->exists())
    // REDIRECT KE HALAMAN CREATE TAGIHAN DENGAN MEMBAWA ID PENCATATAN
    ->url(fn ($record) => \App\Filament\Resources\Tagihans\TagihanResource::getUrl('create', [
        'pencatatan_id' => $record->id,
    ]));
TAHAP C: Desain Form Preview (Proforma Invoice) di TagihanForm
Kita akan menangkap parameter URL dan menampilkannya sebagai form Read-Only / Placeholder yang informatif.

[ ] Buka TagihanResource.php (Method form).

[ ] Susun skema form agar menangkap pencatatan_id dari URL:

PHP
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Schemas\Components\Section;
use Illuminate\Support\HtmlString;
use App\Models\PencatatanMeter;
use App\Services\GenerateTagihanService;

// Tangkap parameter dari URL
$pencatatanId = request()->query('pencatatan_id');
$pencatatan = $pencatatanId ? PencatatanMeter::with('meterAir.pelanggan.golonganTarif')->find($pencatatanId) : null;

// Hitung simulasi (jika data valid)
$simulasiTotal = $pencatatan ? GenerateTagihanService::calculateAmount($pencatatan) : 0;

return $schema->components([
    Hidden::make('pencatatan_meter_id')
        ->default($pencatatanId)
        ->required(),

    // Tambahkan Hidden input untuk pelanggan_id
    Hidden::make('pelanggan_id')
        ->default($pencatatan?->meterAir?->pelanggan_id)
        ->required(),

    Section::make('Preview Tagihan (Proforma)')
        ->description('Validasi rincian biaya sebelum tagihan diterbitkan secara resmi.')
        ->visible(fn () => $pencatatan !== null)
        ->schema([
            Placeholder::make('info_pelanggan')
                ->label('Pelanggan')
                ->content($pencatatan?->meterAir?->pelanggan?->user?->name . ' (' . $pencatatan?->meterAir?->nomor_meter . ')'),

            Placeholder::make('pemakaian')
                ->label('Total Pemakaian')
                ->content($pencatatan?->pemakaian_m3 . ' m³'),

            Placeholder::make('estimasi_total')
                ->label('Total Tagihan')
                ->content(new HtmlString('<span style="font-size: 1.5em; font-weight: bold; color: #10B981;">Rp ' . number_format($simulasiTotal, 0, ',', '.') . '</span>')),
        ])
]);
TAHAP D: Keamanan Server-Side di CreateTagihan.php
Jangan pernah mempercayai UI. Kalkulasi asli dan pembuatan nomor invoice harus tetap dilakukan di backend sesaat sebelum di- insert.

[ ] Buka App\Filament\Resources\Tagihans\Pages\CreateTagihan.php.

[ ] Timpa proses data dengan mutateFormDataBeforeCreate:

PHP
use App\Models\PencatatanMeter;
use App\Services\GenerateTagihanService;
use Illuminate\Support\Str;

protected function mutateFormDataBeforeCreate(array $data): array
{
    $pencatatan = PencatatanMeter::findOrFail($data['pencatatan_meter_id']);

    // Kalkulasi ulang di server untuk keamanan mutlak
    $data['jumlah_tagihan'] = GenerateTagihanService::calculateAmount($pencatatan);

    // Generate Nomor Tagihan
    $data['no_tagihan'] = 'INV-' . now()->format('Y') . '-' . strtoupper(Str::random(5));

    // Status Default
    $data['status_bayar'] = 'Belum Bayar';

    // (Opsional: Salin logika otomatisasi menonaktifkan alat rusak dari Service lama ke sini, 
    // atau letakkan di observer Tagihan "created")

    return $data;
}

4. Acceptance Criteria (Kriteria Selesai)
Developer harus memastikan checklist ini terpenuhi sebelum tiket dipindah ke status "Done":

[ ] Tombol "Generate Tagihan" di halaman Pencatatan Meter telah hilang, diganti dengan "Buat Tagihan" yang mengarahkan user ke halaman baru.

[ ] Halaman URL mencantumkan parameter (contoh: /tagihans/create?pencatatan_id=5).

[ ] Layar memunculkan angka Rupiah yang benar (hasil calculateAmount) sebelum admin menekan tombol Simpan.

[ ] Setelah Save, data masuk dengan sukses ke tabel tagihan lengkap dengan no_tagihan yang ter- generate otomatis, dan status Belum Bayar.

[ ] Alat yang berstatus "Rusak" harus tetap otomatis menjadi "Nonaktif" setelah tagihan ini berhasil disimpan (Pastikan fitur dari GenerateTagihanService::execute lama tidak terputus/hilang).