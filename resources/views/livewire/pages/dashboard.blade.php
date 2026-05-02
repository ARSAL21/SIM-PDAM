<?php

use App\Services\StatistikAirService;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts.app-user')] class extends Component {
    public $pelanggan = null;
    public array $dashboard = [];
    public array $chartData = [
        'categories' => [],
        'series' => [],
    ];
    public bool $hasChartData = false;

    public function mount()
    {
        if (auth()->user()->hasRole(['super_admin', 'admin', 'admin-PDAM'])) {
            $this->redirect(config('filament.path', 'admin'));
            return;
        }

        $this->pelanggan = auth()->user()->pelanggan()->with(['golonganTarif'])->first();

        if (! $this->pelanggan) {
            return;
        }

        $service = app(StatistikAirService::class);

        $this->dashboard = $service->getDashboardSummary($this->pelanggan);

        $rawData = $service->getTrenPemakaian($this->pelanggan->id);
        $this->hasChartData = ! empty($rawData);
        $this->chartData = $this->mapChartData($rawData);
    }

    private function mapChartData(array $rawData): array
    {
        return [
            'categories' => collect($rawData)->pluck('bulan')->toArray(),
            'series' => collect($rawData)->pluck('kubikasi')->toArray(),
        ];
    }
};
?>

@section('title', 'Beranda Dasbor')
@section('page-title', 'Beranda')

@php
    $meter = $dashboard['meter'] ?? null;
    $tagihan = $dashboard['tagihan'] ?? null;

    $toneMap = [
        'emerald' => [
            'badge' => 'bg-emerald-50 text-emerald-700 border border-emerald-200',
            'dot' => 'bg-emerald-500',
            'panel' => 'bg-emerald-50/70 border-emerald-100',
        ],
        'rose' => [
            'badge' => 'bg-rose-50 text-rose-700 border border-rose-200',
            'dot' => 'bg-rose-500',
            'panel' => 'bg-rose-50/70 border-rose-100',
        ],
        'amber' => [
            'badge' => 'bg-amber-50 text-amber-700 border border-amber-200',
            'dot' => 'bg-amber-500',
            'panel' => 'bg-amber-50/70 border-amber-100',
        ],
        'sky' => [
            'badge' => 'bg-sky-50 text-sky-700 border border-sky-200',
            'dot' => 'bg-sky-500',
            'panel' => 'bg-sky-50/70 border-sky-100',
        ],
        'slate' => [
            'badge' => 'bg-slate-100 text-slate-700 border border-slate-200',
            'dot' => 'bg-slate-500',
            'panel' => 'bg-slate-50 border-slate-200',
        ],
    ];

    $meterTone = $toneMap[$meter['tone'] ?? 'slate'] ?? $toneMap['slate'];
    $tagihanTone = $toneMap[$tagihan['tone'] ?? 'slate'] ?? $toneMap['slate'];
@endphp

<div class="space-y-8 animate-fade-in">
    @if(!$pelanggan)
        <div class="bg-amber-50 border border-amber-200 rounded-2xl p-8 flex flex-col items-center text-center shadow-sm">
            <div class="w-20 h-20 bg-amber-100 text-amber-600 rounded-full flex items-center justify-center mb-6">
                <svg class="w-10 h-10" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
            <h3 class="text-xl font-bold text-amber-900 mb-2">Akun Belum Ditautkan</h3>
            <p class="text-amber-700 max-w-md leading-relaxed">
                Akun digital Anda berhasil dibuat, namun belum terhubung dengan data pelanggan PDAM.
                Silakan hubungi admin untuk proses verifikasi.
            </p>
        </div>
    @else
        <section class="grid grid-cols-1 xl:grid-cols-12 gap-6">
            <div class="xl:col-span-7 bg-white rounded-3xl p-8 border border-slate-100 shadow-[0_12px_40px_rgba(15,23,42,0.06)]">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div class="space-y-3">
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-[0.2em]">Status Tagihan</p>
                        <div class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-bold uppercase tracking-[0.16em] {{ $tagihanTone['badge'] }}">
                            <span class="w-2 h-2 rounded-full {{ $tagihanTone['dot'] }}"></span>
                            {{ $tagihan['label'] ?? 'Belum Ada Data' }}
                        </div>
                        <div>
                            <h2 class="text-3xl sm:text-5xl font-extrabold text-slate-900 leading-tight">
                                Rp {{ number_format($tagihan['amount'] ?? 0, 0, ',', '.') }}
                            </h2>
                            <p class="mt-3 text-lg font-bold text-slate-800">
                                {{ $tagihan['headline'] ?? 'Belum ada informasi tagihan.' }}
                            </p>
                            <p class="mt-2 text-sm leading-6 text-slate-500 max-w-2xl">
                                {{ $tagihan['description'] ?? 'Data tagihan belum tersedia.' }}
                            </p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3 min-w-0 sm:min-w-[240px]">
                        <div class="rounded-2xl border border-slate-100 bg-slate-50 p-4">
                            <div class="text-[11px] font-bold uppercase tracking-[0.16em] text-slate-400">Belum Bayar</div>
                            <div class="mt-2 text-2xl font-extrabold text-slate-900">{{ $tagihan['jumlah_belum_bayar'] ?? 0 }}</div>
                        </div>
                        <div class="rounded-2xl border border-slate-100 bg-slate-50 p-4">
                            <div class="text-[11px] font-bold uppercase tracking-[0.16em] text-slate-400">Verifikasi</div>
                            <div class="mt-2 text-2xl font-extrabold text-slate-900">{{ $tagihan['jumlah_menunggu_verifikasi'] ?? 0 }}</div>
                        </div>
                    </div>
                </div>

                <div class="mt-8 flex flex-wrap gap-3">
                    <a href="{{ route('tagihan.index') }}"
                       class="inline-flex items-center px-6 py-3 bg-[#1AACB4] hover:bg-[#158e95] text-white font-bold rounded-xl transition-all shadow-lg shadow-teal-100">
                        Lihat Tagihan
                    </a>
                    <a href="{{ route('statistik.index') }}"
                       class="inline-flex items-center px-6 py-3 bg-white border border-slate-200 text-slate-700 font-bold rounded-xl hover:bg-slate-50 transition-all">
                        Lihat Riwayat
                    </a>
                </div>
            </div>

            <div class="xl:col-span-5 grid grid-cols-1 gap-6">
                <div class="bg-white rounded-3xl p-6 border border-slate-100 shadow-sm">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-[0.18em]">Informasi Pelanggan</p>
                            <h3 class="mt-2 text-2xl font-extrabold text-slate-900">{{ $pelanggan->nama_lengkap }}</h3>
                            <p class="mt-1 text-sm text-slate-500">{{ $pelanggan->alamat }}</p>
                        </div>
                        <div class="rounded-2xl bg-[#F4EFE6] border border-[#E8DFD0] px-3 py-2 text-right">
                            <div class="text-[11px] font-bold uppercase tracking-[0.16em] text-slate-400">ID Pelanggan</div>
                            <div class="mt-1 text-sm font-extrabold text-slate-800 font-mono">{{ $pelanggan->no_pelanggan }}</div>
                        </div>
                    </div>

                    <div class="mt-6 grid grid-cols-2 gap-4">
                        <div class="rounded-2xl border border-slate-100 bg-slate-50 p-4">
                            <div class="text-[11px] font-bold uppercase tracking-[0.16em] text-slate-400">Golongan</div>
                            <div class="mt-2 text-sm font-bold text-slate-800">{{ $pelanggan->golonganTarif->nama_golongan ?? 'Belum diatur' }}</div>
                        </div>
                        <div class="rounded-2xl border border-slate-100 bg-slate-50 p-4">
                            <div class="text-[11px] font-bold uppercase tracking-[0.16em] text-slate-400">No. HP</div>
                            <div class="mt-2 text-sm font-bold text-slate-800">{{ $pelanggan->no_hp ?: 'Belum diisi' }}</div>
                        </div>
                    </div>
                </div>

                <div class="rounded-3xl border p-6 {{ $meterTone['panel'] }}">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-[0.18em]">Status Meter</p>
                            <div class="mt-2 inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-bold uppercase tracking-[0.16em] {{ $meterTone['badge'] }}">
                                <span class="w-2 h-2 rounded-full {{ $meterTone['dot'] }}"></span>
                                {{ $meter['label'] ?? 'Belum Ada Data' }}
                            </div>
                        </div>
                        <div class="text-left sm:text-right">
                            <div class="text-[11px] font-bold uppercase tracking-[0.16em] text-slate-400">Nomor Meter</div>
                            <div class="mt-1 text-base font-extrabold text-slate-900">{{ $meter['nomor_meter'] ?? 'Belum tersedia' }}</div>
                        </div>
                    </div>

                    <p class="mt-4 text-sm leading-6 text-slate-600">
                        {{ $meter['description'] ?? 'Informasi meter belum tersedia.' }}
                    </p>

                    <div class="mt-5 grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="rounded-2xl bg-white/80 border border-white p-4">
                            <div class="text-[11px] font-bold uppercase tracking-[0.16em] text-slate-400">Merek</div>
                            <div class="mt-2 text-sm font-bold text-slate-800">{{ $meter['merek'] ?? 'Belum tersedia' }}</div>
                        </div>
                        <div class="rounded-2xl bg-white/80 border border-white p-4">
                            <div class="text-[11px] font-bold uppercase tracking-[0.16em] text-slate-400">Tanggal Pasang</div>
                            <div class="mt-2 text-sm font-bold text-slate-800">
                                {{ $meter['tanggal_pasang']?->translatedFormat('d M Y') ?? 'Belum dijadwalkan' }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-100 shadow-sm">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-[0.18em]">Tren Pemakaian</p>
                    <h3 class="mt-2 text-2xl font-extrabold text-slate-900">Pemakaian Air 6 Bulan Terakhir</h3>
                    <p class="mt-2 text-sm text-slate-500">
                        Grafik ini menampilkan riwayat pembacaan meter yang sudah tercatat di sistem.
                    </p>
                </div>
                <a href="{{ route('statistik.index') }}"
                   class="inline-flex items-center px-4 py-2 bg-slate-50 border border-slate-200 text-slate-700 font-bold rounded-xl hover:bg-slate-100 transition-all">
                    Buka Statistik
                </a>
            </div>

            @if($hasChartData)
                <div wire:ignore x-data="waterChart(@js($chartData))" class="mt-8">
                    <div x-ref="chartCanvas" class="min-h-[320px]"></div>
                </div>
            @else
                <div class="mt-8 rounded-3xl border border-dashed border-slate-200 bg-slate-50 px-6 py-14 text-center">
                    <div class="mx-auto w-14 h-14 rounded-2xl bg-white border border-slate-200 flex items-center justify-center text-slate-400">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path d="M3 3v18h18"/>
                            <path d="M19 9l-5 5-4-4-3 3"/>
                        </svg>
                    </div>
                    <h4 class="mt-4 text-lg font-extrabold text-slate-800">Belum Ada Data Pemakaian</h4>
                    <p class="mt-2 text-sm leading-6 text-slate-500 max-w-xl mx-auto">
                        Grafik akan muncul setelah petugas mencatat pembacaan meter pertama untuk pelanggan ini.
                    </p>
                </div>
            @endif
        </section>
    @endif
</div>
