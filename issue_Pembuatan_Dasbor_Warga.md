ISSUE: [Feature & UI] Pembuatan Dasbor Warga (Livewire SFC, Decoupled Logic & Reactive Chart)
Issue Type: Feature, Frontend, Architecture
Priority: High (Blocker untuk modul pembayaran)
Tech Stack: Laravel v13, Livewire v4 (SFC), Alpine.js, Tailwind CSS, ApexCharts

📖 1. Latar Belakang & Tujuan
Pembuatan Halaman Beranda Dasbor Warga dari nol menggunakan pendekatan Page-First Architecture (Livewire SFC).

Fokus implementasi ini adalah stabilitas lifecycle data:

Menghindari penggunaan #[Computed] yang salah kaprah untuk initial load. Data di- resolve langsung di method mount().

Menerapkan reaktivitas grafik yang benar di Alpine.js agar grafik bisa diperbarui (re-render) jika ada perubahan data tanpa perlu me- reload halaman.

Memastikan Service Layer terdekopling dari struktur UI.

✅ 2. Kriteria Penerimaan (Acceptance Criteria)
[ ] File komponen di-generate di direktori resources/views/pages/⚡dashboard.blade.php.

[ ] Routing menggunakan Route::view('/dashboard', 'pages.dashboard') (Sederhana dan elegan).

[ ] Data kalkulasi di- resolve ke dalam public property standar saat proses mount().

[ ] Logic ApexCharts di Alpine.js menyimpan instance grafik (this.chart) dan menggunakan $watch (opsional/advanced) agar grafik siap menerima pembaruan data secara dinamis.

🛠️ 3. Instruksi Eksekusi Teknis
LANGKAH 1: Arsitektur Data (Decoupled Service)
Buat app/Services/StatistikAirService.php yang murni mengembalikan data domain/primitif.

PHP
public function getTrenPemakaian(int $pelangganId): array
{
    // Return data primitif, biarkan komponen yang melakukan formatting
    return [
        ['bulan' => 'Mar', 'kubikasi' => 12],
        ['bulan' => 'Apr', 'kubikasi' => 18],
        ['bulan' => 'Mei', 'kubikasi' => 15],
    ];
}
LANGKAH 2: Pendaftaran Rute (Standard View)
Buka routes/web.php dan gunakan standar routing view:

PHP
Route::view('/dashboard', 'pages.dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');
LANGKAH 3: Penulisan SFC (⚡dashboard.blade.php) - Bagian Backend
Gunakan properti standar dan resolve data di dalam fungsi lifecycle mount(). Lakukan mapping data domain ke format spesifik ApexCharts di sini (sebagai Adapter/Presenter).

HTML
<?php
use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Services\StatistikAirService;

new #[Layout('layouts.app-user')] class extends Component {
    public $pelanggan = null;
    public $ringkasan = 0;
    public $chartData = null;

    public function mount()
    {
        $this->pelanggan = auth()->user()->pelanggan;

        if ($this->pelanggan) {
            $service = app(StatistikAirService::class);
            $this->ringkasan = $service->getRingkasanTagihan($this->pelanggan->id);
            $this->chartData = $this->mapChartData($service->getTrenPemakaian($this->pelanggan->id));
        }
    }

    private function mapChartData($rawData)
    {
        if (empty($rawData)) return null;
        
        return [
            'categories' => collect($rawData)->pluck('bulan')->toArray(),
            'series'     => collect($rawData)->pluck('kubikasi')->toArray(),
        ];
    }
};
?>
LANGKAH 4: Penulisan SFC - Bagian UI
Markup HTML murni, menghindari wire:loading yang tidak akan aktif pada inisiasi awal properti mount.

HTML
<div>
    @if(!$pelanggan)
        <div class="bg-amber-50 p-4 rounded-xl text-amber-700">
            Akun digital Anda belum ditautkan ke data meteran fisik.
        </div>
    @else
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <!-- Widget Ringkasan Tagihan -->
            <div class="lg:col-span-2 bg-white rounded-2xl p-6 shadow-sm border border-slate-200">
                <p class="text-sm text-slate-500">Total Tagihan Tertunggak</p>
                <h2 class="text-4xl font-bold text-slate-800">Rp {{ number_format($ringkasan, 0, ',', '.') }}</h2>
                <!-- Tombol CTA dll -->
            </div>

            <!-- Widget Grafik -->
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200">
                <h2 class="text-base font-bold text-slate-800 mb-4">Grafik Pemakaian</h2>
                
                <!-- Area Render Alpine & ApexCharts -->
                <div wire:ignore x-data="waterChart(@js($chartData))">
                    <div x-ref="chartCanvas"></div>
                </div>
            </div>

        </div>
    @endif
</div>
LANGKAH 5: Isolasi & Reaktivitas JavaScript (Alpine.js)
Tulis skrip ini secara terpisah. Pastikan untuk menyimpan instance grafik (this.chart) agar reaktivitas data terjaga jika sewaktu-waktu Livewire mengirim ulang payload chartData yang baru.

JavaScript
document.addEventListener('alpine:init', () => {
    Alpine.data('waterChart', (initialData) => ({
        chart: null,
        chartData: initialData,

        init() {
            if (!this.chartData) return;

            let options = {
                series: [{ name: 'Pemakaian (m³)', data: this.chartData.series }],
                chart: { type: 'bar', height: 250, toolbar: { show: false }, fontFamily: 'inherit' },
                colors: ['#1AACB4'],
                xaxis: { categories: this.chartData.categories, labels: { style: { colors: '#64748b' } } },
                grid: { borderColor: '#f1f5f9' }
            };

            // Simpan instance ke property `this.chart`
            this.chart = new ApexCharts(this.$refs.chartCanvas, options);
            this.chart.render();

            // (Advanced/Optional) Watcher jika data dari backend berubah sewaktu-waktu
            this.$watch('chartData', (newData) => {
                if (newData && this.chart) {
                    this.chart.updateOptions({
                        xaxis: { categories: newData.categories }
                    });
                    this.chart.updateSeries([{ data: newData.series }]);
                }
            });
        }
    }));
});