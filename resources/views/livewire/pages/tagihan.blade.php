<?php

namespace App\Livewire\Pages;

use App\Models\Tagihan;
use App\Models\Pembayaran;
use App\Services\TagihanService;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

new #[Layout('layouts.app-user')] class extends Component {
    use WithFileUploads;

    public $pelanggan = null;
    public string $search = '';
    public string $activeTab = 'Semua';
    public $buktiTransfer;
    public ?int $selectedTagihanId = null;
    public bool $showModal = false;

    // Tabs definition
    public array $tabs = [
        'Semua' => 'Semua',
        'Belum Bayar' => 'Belum Bayar',
        'Menunggu Verifikasi' => 'Verifikasi',
        'Ditolak' => 'Ditolak',
        'Lunas' => 'Lunas',
    ];

    public function mount()
    {
        if (auth()->user()->hasRole(['super_admin', 'admin', 'admin-PDAM'])) {
            $this->redirect(config('filament.path', 'admin'));
            return;
        }

        $this->pelanggan = auth()->user()->pelanggan;
    }

    // Reset pagination / selection when filter changes
    public function updatedActiveTab()
    {
        $this->reset('search');
    }

    public function bukaModalUpload($tagihanId)
    {
        $this->selectedTagihanId = $tagihanId;
        $this->resetValidation();
        $this->reset('buktiTransfer');
        $this->showModal = true;
    }

    public function tutupModal()
    {
        $this->showModal = false;
        $this->selectedTagihanId = null;
        $this->reset('buktiTransfer');
    }

    public function submitPembayaran()
    {
        $this->validate([
            'buktiTransfer' => 'required|image|max:5120', // Max 5MB
            'selectedTagihanId' => 'required|exists:tagihan,id',
        ], [
            'buktiTransfer.required' => 'Foto bukti transfer wajib diunggah.',
            'buktiTransfer.image' => 'File harus berupa gambar.',
            'buktiTransfer.max' => 'Ukuran gambar maksimal 5MB.',
        ]);

        $tagihan = Tagihan::findOrFail($this->selectedTagihanId);

        // Buat record pembayaran baru
        $pembayaran = new Pembayaran([
            'tagihan_id' => $tagihan->id,
            'tanggal_bayar' => now(),
            'jumlah_bayar' => $tagihan->jumlah_tagihan, // bayar sesuai tagihan
            'status_pembayaran' => 'Menunggu Verifikasi',
            'metode_bayar' => 'Transfer Bank',
        ]);
        $pembayaran->save();

        // Attach image via Spatie Media Library
        $pembayaran->addMedia($this->buktiTransfer->getRealPath())
                   ->usingFileName($this->buktiTransfer->getClientOriginalName())
                   ->toMediaCollection('bukti_pembayaran');

        // Update status tagihan
        $tagihan->update([
            'status_bayar' => 'Menunggu Verifikasi'
        ]);

        $this->tutupModal();
        session()->flash('message', 'Bukti pembayaran berhasil diunggah dan sedang menunggu verifikasi.');
    }

    public function getTagihanProperty()
    {
        if (!$this->pelanggan) {
            return collect([]);
        }

        return app(TagihanService::class)->getDaftarTagihan(
            $this->pelanggan,
            $this->activeTab,
            $this->search
        );
    }
};
?>

@section('title', 'Tagihan & Pembayaran')
@section('page-title', 'Tagihan & Pembayaran')

<div class="space-y-6 lg:space-y-8 animate-fade-in pb-10">

    <!-- Header Section -->
    <section class="bg-white rounded-3xl p-6 lg:p-8 border border-slate-100 shadow-[0_4px_20px_rgb(0,0,0,0.03)] flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Tagihan & Pembayaran</h1>
            <p class="text-slate-500 text-sm mt-1">Kelola kewajiban bulanan dan riwayat pembayaran air Anda.</p>
        </div>
        <div class="bg-slate-50 border border-slate-100 rounded-2xl p-4 flex items-center gap-4">
            <div class="w-10 h-10 rounded-full bg-teal-100 text-teal-600 flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zM12 17v-2m0-4V7"/>
                </svg>
            </div>
            <div>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Total Belum Dibayar</p>
                <p class="text-lg font-extrabold text-slate-800">
                    Rp {{ number_format($this->tagihan->where('status_bayar', 'Belum Bayar')->sum('jumlah_tagihan'), 0, ',', '.') }}
                </p>
            </div>
        </div>
    </section>

    <!-- Global Alerts -->
    @if (session()->has('message'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl p-4 flex items-start gap-3 shadow-sm animate-fade-in">
            <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M5 13l4 4L19 7"/>
            </svg>
            <div>
                <h4 class="font-bold text-sm">Berhasil!</h4>
                <p class="text-sm mt-0.5 opacity-90">{{ session('message') }}</p>
            </div>
        </div>
    @endif

    @if($this->tagihan->where('status_bayar', 'Ditolak')->count() > 0)
        <div class="bg-rose-50 border border-rose-200 text-rose-800 rounded-2xl p-4 flex items-start gap-3 shadow-sm animate-fade-in">
            <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/>
            </svg>
            <div>
                <h4 class="font-bold text-sm">Pembayaran Ditolak</h4>
                <p class="text-sm mt-0.5 opacity-90">Ada pembayaran Anda yang ditolak. Silakan periksa catatan admin pada tagihan terkait di bawah ini.</p>
            </div>
        </div>
    @endif

    @if($this->tagihan->where('status_bayar', 'Menunggu Verifikasi')->count() > 0)
        <div class="bg-amber-50 border border-amber-200 text-amber-800 rounded-2xl p-4 flex items-start gap-3 shadow-sm animate-fade-in">
            <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/>
            </svg>
            <div>
                <h4 class="font-bold text-sm">Menunggu Verifikasi</h4>
                <p class="text-sm mt-0.5 opacity-90">Bukti transfer Anda sedang dalam antrean verifikasi Admin.</p>
            </div>
        </div>
    @endif

    <!-- Utility Toolbar -->
    <section class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <!-- Tabs -->
        <div class="flex items-center overflow-x-auto pb-2 md:pb-0 scrollbar-hide gap-2 w-full md:w-auto">
            @foreach($tabs as $key => $label)
                <button 
                    wire:click="$set('activeTab', '{{ $key }}')"
                    class="whitespace-nowrap px-4 py-2.5 rounded-full text-sm font-bold transition-all border
                    {{ $activeTab === $key 
                        ? 'bg-slate-800 text-white border-slate-800 shadow-md' 
                        : 'bg-white text-slate-500 border-slate-200 hover:bg-slate-50' }}">
                    {{ $label }}
                    
                    @php
                        $count = 0;
                        if($key !== 'Semua') {
                            $count = $this->tagihan->where('status_bayar', $key)->count();
                        }
                    @endphp
                    @if($count > 0 && $key !== 'Semua')
                        <span class="ml-1.5 px-1.5 py-0.5 rounded-full text-[10px] 
                            {{ $activeTab === $key ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-600' }}">
                            {{ $count }}
                        </span>
                    @endif
                </button>
            @endforeach
        </div>

        <!-- Search -->
        <div class="relative w-full md:w-64 flex-shrink-0">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>
            <input 
                wire:model.live.debounce.300ms="search" 
                type="text" 
                placeholder="Cari tagihan..." 
                class="w-full pl-10 pr-4 py-2.5 bg-white border border-slate-200 text-sm text-slate-800 rounded-xl focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-transparent transition-all placeholder-slate-400">
            
            <div wire:loading wire:target="search" class="absolute inset-y-0 right-0 pr-3 flex items-center">
                <svg class="animate-spin h-4 w-4 text-slate-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </div>
        </div>
    </section>

    <!-- Main Content: Billing Cards -->
    <div class="relative min-h-[300px]">
        <!-- Loading Overlay -->
        <div wire:loading.class="opacity-100 visible" wire:loading.class.remove="opacity-0 invisible" class="absolute inset-0 bg-white/60 backdrop-blur-[2px] z-10 opacity-0 invisible transition-all duration-200 flex items-center justify-center rounded-3xl">
            <div class="flex items-center gap-2 px-4 py-2 bg-white shadow-sm border border-slate-100 rounded-full text-slate-600 font-medium text-sm">
                <svg class="animate-spin h-4 w-4 text-[#1AACB4]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                Memuat data...
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
            @forelse($this->tagihan as $item)
                @php
                    $isDitolak = $item->status_bayar === 'Ditolak';
                    $isBelumBayar = $item->status_bayar === 'Belum Bayar';
                    $isVerifikasi = $item->status_bayar === 'Menunggu Verifikasi';
                    $isLunas = $item->status_bayar === 'Lunas';

                    $badgeColor = match($item->status_bayar) {
                        'Belum Bayar' => 'bg-slate-100 text-slate-600 border-slate-200',
                        'Menunggu Verifikasi' => 'bg-amber-50 text-amber-600 border-amber-200',
                        'Ditolak' => 'bg-rose-50 text-rose-600 border-rose-200',
                        'Lunas' => 'bg-emerald-50 text-emerald-600 border-emerald-200',
                        default => 'bg-slate-100 text-slate-600 border-slate-200'
                    };
                    
                    $dotColor = match($item->status_bayar) {
                        'Belum Bayar' => 'bg-slate-400',
                        'Menunggu Verifikasi' => 'bg-amber-500',
                        'Ditolak' => 'bg-rose-500',
                        'Lunas' => 'bg-emerald-500',
                        default => 'bg-slate-400'
                    };
                @endphp

                <div class="bg-white border border-slate-200 shadow-sm rounded-2xl overflow-hidden flex flex-col hover:shadow-md transition-shadow">
                    
                    <!-- Header Card -->
                    <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-lg bg-white border border-slate-200 flex items-center justify-center text-slate-400 shadow-sm">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-slate-800">
                                    {{ $item->pencatatanMeter ? \Carbon\Carbon::createFromDate($item->pencatatanMeter->periode_tahun, $item->pencatatanMeter->periode_bulan, 1)->translatedFormat('F Y') : 'Periode Tidak Diketahui' }}
                                </h3>
                                <p class="text-[10px] font-medium text-slate-500 uppercase tracking-widest">Inv: #{{ $item->no_tagihan }}</p>
                            </div>
                        </div>
                        <div class="px-2.5 py-1 rounded-full border {{ $badgeColor }} text-[10px] font-bold uppercase tracking-widest flex items-center gap-1.5">
                            <span class="w-1.5 h-1.5 rounded-full {{ $dotColor }}"></span>
                            {{ $item->status_bayar }}
                        </div>
                    </div>

                    <!-- Body Card -->
                    <div class="p-6 flex-1 flex flex-col justify-center">
                        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Total Tagihan</div>
                        <div class="text-3xl font-extrabold text-slate-800 mb-4">
                            Rp {{ number_format($item->jumlah_tagihan, 0, ',', '.') }}
                        </div>
                        
                        <div class="bg-slate-50 border border-slate-100 rounded-xl p-3 flex items-center justify-between text-xs">
                            <div class="text-slate-500">
                                Meter Awal: <span class="font-bold text-slate-700">{{ $item->pencatatanMeter->angka_awal ?? 0 }}</span>
                            </div>
                            <div class="text-slate-300">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                            </div>
                            <div class="text-slate-500">
                                Meter Akhir: <span class="font-bold text-slate-700">{{ $item->pencatatanMeter->angka_akhir ?? 0 }}</span>
                            </div>
                        </div>
                        <div class="text-center mt-2 text-xs font-bold text-slate-600 bg-white border border-slate-200 py-1.5 rounded-lg shadow-sm w-max px-4 mx-auto -translate-y-5">
                            Total: {{ $item->pencatatanMeter->pemakaian_m3 ?? 0 }} m³
                        </div>

                        <!-- Reject Notes -->
                        @if($isDitolak)
                            @php
                                $lastPayment = $item->pembayarans->last();
                            @endphp
                            @if($lastPayment && $lastPayment->catatan_admin)
                                <div class="mt-4 bg-rose-50 border border-rose-200 rounded-xl p-3 text-sm">
                                    <div class="font-bold text-rose-800 mb-1 text-xs flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                        Catatan Penolakan
                                    </div>
                                    <p class="text-rose-700 italic">"{{ $lastPayment->catatan_admin }}"</p>
                                </div>
                            @endif
                        @endif
                    </div>

                    <!-- Footer Card -->
                    <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/30">
                        @if($isBelumBayar || $isDitolak)
                            <button 
                                wire:click="bukaModalUpload({{ $item->id }})"
                                class="w-full inline-flex justify-center items-center px-4 py-2.5 bg-[#1AACB4] hover:bg-[#158e95] text-white font-bold rounded-xl transition-all shadow-md shadow-teal-100 group">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                                Upload Bukti Transfer
                            </button>
                        @elseif($isVerifikasi)
                            <div class="text-center py-2 text-sm font-medium text-amber-600 flex items-center justify-center gap-2">
                                <svg class="animate-spin w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                Menunggu konfirmasi admin
                            </div>
                        @elseif($isLunas)
                            <div class="flex items-center justify-between">
                                <div class="text-xs font-medium text-emerald-600 flex items-center gap-1.5">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M5 13l4 4L19 7"/></svg>
                                    Lunas pada {{ $item->pembayarans->where('status_pembayaran', 'Diterima')->first()?->diverifikasi_pada?->translatedFormat('d M Y') ?? 'N/A' }}
                                </div>
                                <button class="text-xs font-bold text-slate-500 hover:text-slate-800 border border-slate-200 bg-white hover:bg-slate-50 rounded-lg px-3 py-1.5 transition-colors flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                    PDF
                                </button>
                            </div>
                        @endif
                    </div>
                </div>
            @empty
                <div class="col-span-full py-16 px-6 border-2 border-dashed border-slate-200 rounded-3xl bg-slate-50 text-center flex flex-col items-center">
                    <div class="w-16 h-16 rounded-full bg-white flex items-center justify-center text-slate-300 shadow-sm mb-4 border border-slate-100">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-slate-800">Tidak ada tagihan</h3>
                    <p class="text-slate-500 mt-1 max-w-sm mx-auto">
                        @if($search)
                            Pencarian "{{ $search }}" tidak menemukan hasil apapun pada kategori ini.
                        @else
                            Tidak ada tagihan dalam kategori "{{ $activeTab }}".
                        @endif
                    </p>
                    @if($search || $activeTab !== 'Semua')
                        <button wire:click="$set('activeTab', 'Semua'); $set('search', '')" class="mt-4 text-teal-600 font-bold hover:underline text-sm">
                            Lihat semua tagihan
                        </button>
                    @endif
                </div>
            @endforelse
        </div>
    </div>

    <!-- Modal Upload Bukti Transfer -->
    <div 
        x-data="{ show: @entangle('showModal') }"
        x-show="show"
        x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto overflow-x-hidden p-4 sm:p-6"
    >
        <!-- Backdrop -->
        <div 
            x-show="show" 
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm"
            @click="show = false; $wire.tutupModal()"
        ></div>

        <!-- Modal Panel -->
        <div 
            x-show="show"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-8 sm:translate-y-0 sm:scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave-end="opacity-0 translate-y-8 sm:translate-y-0 sm:scale-95"
            class="relative w-full max-w-lg rounded-3xl bg-white shadow-2xl overflow-hidden flex flex-col max-h-[90vh]"
        >
            <!-- Modal Header -->
            <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between bg-white sticky top-0 z-10">
                <h3 class="text-lg font-bold text-slate-800">Upload Bukti Pembayaran</h3>
                <button @click="show = false; $wire.tutupModal()" class="text-slate-400 hover:text-slate-600 hover:bg-slate-100 p-2 rounded-full transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="p-6 overflow-y-auto">
                <!-- Instruksi Transfer -->
                <div class="bg-sky-50 border border-sky-100 rounded-2xl p-4 mb-6">
                    <div class="flex items-start gap-3">
                        <div class="mt-0.5 w-8 h-8 rounded-full bg-sky-200 text-sky-700 flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                        </div>
                        <div class="flex-1">
                            <h4 class="text-sm font-bold text-sky-900 mb-2">Instruksi Transfer</h4>
                            <div class="space-y-1.5 text-sm text-sky-800">
                                <div class="flex justify-between items-center bg-white/60 px-3 py-2 rounded-lg">
                                    <span class="text-sky-600/80 text-xs uppercase tracking-widest font-bold">Bank</span>
                                    <span class="font-bold text-slate-800">Bank BRI</span>
                                </div>
                                <div class="flex justify-between items-center bg-white/60 px-3 py-2 rounded-lg">
                                    <span class="text-sky-600/80 text-xs uppercase tracking-widest font-bold">No. Rekening</span>
                                    <div class="flex items-center gap-2">
                                        <span class="font-bold text-slate-800 font-mono tracking-wider">1234-5678-9012</span>
                                        <button class="text-sky-600 hover:text-sky-800" title="Copy"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg></button>
                                    </div>
                                </div>
                                <div class="flex justify-between items-center bg-white/60 px-3 py-2 rounded-lg">
                                    <span class="text-sky-600/80 text-xs uppercase tracking-widest font-bold">Atas Nama</span>
                                    <span class="font-bold text-slate-800">BUMDes Air Lanto</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Custom File Upload (FilePond styled) -->
                <div class="mb-2">
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">Pilih Foto Struk</label>
                    <div 
                        x-data="{ isUploading: false, progress: 0 }"
                        x-on:livewire-upload-start="isUploading = true"
                        x-on:livewire-upload-finish="isUploading = false"
                        x-on:livewire-upload-error="isUploading = false"
                        x-on:livewire-upload-progress="progress = $event.detail.progress"
                        class="relative"
                    >
                        <!-- Dropzone area -->
                        <div class="w-full border-2 border-dashed rounded-2xl p-6 transition-colors duration-200 ease-in-out text-center cursor-pointer 
                            {{ $buktiTransfer ? 'border-teal-300 bg-teal-50' : 'border-slate-300 bg-slate-50 hover:bg-slate-100 hover:border-slate-400' }}
                            relative overflow-hidden group"
                        >
                            <input 
                                type="file" 
                                wire:model="buktiTransfer" 
                                accept="image/*"
                                class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-20"
                            >
                            
                            @if ($buktiTransfer)
                                <!-- Preview Image -->
                                <div class="flex flex-col items-center">
                                    <div class="w-16 h-16 rounded-xl bg-white border border-teal-200 p-1 mb-3 shadow-sm">
                                        <img src="{{ $buktiTransfer->temporaryUrl() }}" class="w-full h-full object-cover rounded-lg">
                                    </div>
                                    <p class="text-sm font-bold text-teal-800">File dipilih</p>
                                    <p class="text-xs text-teal-600 mt-1 truncate max-w-xs">{{ $buktiTransfer->getClientOriginalName() }}</p>
                                </div>
                            @else
                                <div x-show="!isUploading" class="flex flex-col items-center">
                                    <div class="w-12 h-12 bg-white rounded-full border border-slate-200 flex items-center justify-center text-slate-400 mb-3 group-hover:scale-110 transition-transform shadow-sm">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                                    </div>
                                    <p class="text-sm font-bold text-slate-700">Tarik & lepas file di sini</p>
                                    <p class="text-xs text-slate-500 mt-1">atau klik untuk menelusuri (Max 5MB)</p>
                                </div>
                            @endif

                            <!-- Uploading State -->
                            <div x-show="isUploading" class="absolute inset-0 bg-white/90 backdrop-blur-sm z-30 flex flex-col items-center justify-center">
                                <div class="w-10 h-10 border-4 border-slate-200 border-t-teal-500 rounded-full animate-spin mb-3"></div>
                                <p class="text-xs font-bold text-slate-600 mb-2">Mengunggah <span x-text="progress"></span>%</p>
                                <div class="w-3/4 bg-slate-100 rounded-full h-1.5 overflow-hidden">
                                    <div class="bg-teal-500 h-full transition-all duration-300" :style="`width: ${progress}%`"></div>
                                </div>
                            </div>
                        </div>

                        @error('buktiTransfer')
                            <p class="mt-2 text-sm text-rose-500 flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 8v4m0 4h.01"/></svg>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="px-6 py-4 border-t border-slate-100 bg-slate-50 flex items-center justify-end gap-3 sticky bottom-0 z-10">
                <button 
                    @click="show = false; $wire.tutupModal()" 
                    class="px-5 py-2.5 rounded-xl font-bold text-slate-600 hover:bg-slate-200 transition-colors text-sm">
                    Batal
                </button>
                <button 
                    wire:click="submitPembayaran"
                    wire:loading.attr="disabled"
                    wire:target="submitPembayaran"
                    class="px-5 py-2.5 rounded-xl font-bold text-white bg-[#1AACB4] hover:bg-[#158e95] shadow-md shadow-teal-100 transition-all text-sm flex items-center gap-2 disabled:opacity-70 disabled:cursor-not-allowed">
                    <span wire:loading.remove wire:target="submitPembayaran">Simpan Bukti</span>
                    <span wire:loading wire:target="submitPembayaran" class="flex items-center gap-2">
                        <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        Memproses...
                    </span>
                </button>
            </div>
        </div>
    </div>

</div>
