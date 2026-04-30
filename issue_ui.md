MASTER ISSUE: [UI/UX Refactor] Rebalancing Hero Section, Penempatan Stats, & Bento Grid Layanan
Issue Type: Enhancement & UI/UX
Priority: Critical (Menyangkut First Impression dan Trust Pengguna)
Assignee: [Nama Developer]
Estimated Effort: 3 Hours

📖 1. Konteks & Latar Belakang
Tampilan Landing Page saat ini memiliki dua masalah arsitektur visual utama:

Unbalanced Visual Weight di Hero: Sisi kanan Hero Section kosong, sementara seksi Layanan terlalu padat. Hero Stats (24/7, 2 Cara, Real-time) juga sempat terhapus, padahal ini adalah social proof krusial.

Struktur Layanan yang Usang: Layout grid lama pada seksi Layanan kurang merepresentasikan kewibawaan instansi pengelola air desa.

Solusi Arsitektural: Phone Mockup wajib dipindahkan ke Hero Section (sisi kanan) sebagai elemen dinamis utama, dengan Hero Stats diletakkan tepat di bawahnya menggunakan efek glassmorphism. Seksi Layanan akan dirombak total menggunakan arsitektur Bento Grid berskala Enterprise untuk menampilkan transparansi data, kualitas air, dan testimoni.

🛠️ 2. Instruksi Pengerjaan (Step-by-Step Implementation)
TAHAP A: Restrukturisasi Hero Section (Mockup & Stats)
Modifikasi Struktur HTML (welcome.blade.php):

Buat container utama <div class="hero-main-layout"> di dalam #hero.

Bagian teks dan tombol (kiri) tetap berada di dalam <div class="hero-content">.

Buat container baru untuk kolom kanan: <div class="hero-right-side">.

Pindahkan (Cut & Paste) <div class="phone-mockup"> dari seksi Fitur ke dalam .hero-right-side.

Pindahkan kembali elemen hero-stats ke bawah mockup di dalam .hero-right-side.

Struktur HTML Target:

HTML
<div class="hero-main-layout">
    <div class="hero-content">
        </div>

    <div class="hero-right-side">
        <div class="phone-mockup hero-dynamic-phone reveal">
            <div class="phone-accent"></div>
            <div class="phone-frame">
                </div>
        </div>

        <div class="hero-stats-new reveal">
            <div class="stat-item-new">
                <span class="stat-num-new">24/7</span>
                <span class="stat-label-new">Akses Portal</span>
            </div>
            <div class="stat-divider-new"></div>
            <div class="stat-item-new">
                <span class="stat-num-new">2 Cara</span>
                <span class="stat-label-new">Metode Bayar</span>
            </div>
            <div class="stat-divider-new"></div>
            <div class="stat-item-new">
                <span class="stat-num-new">Real-time</span>
                <span class="stat-label-new">Status Tagihan</span>
            </div>
        </div>
    </div>
</div>
Eksekusi CSS Hero (Layout, Glow, & Glassmorphism):

Terapkan Flexbox untuk membagi 2 kolom dan mengatur jarak vertikal di kolom kanan.

Terapkan rotasi -5deg dan Glow Aqua khusus pada .phone-frame di dalam #hero.

CSS
/* Layout Utama Hero */
.hero-main-layout {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 4rem;
}

/* Kolom Kanan (Mockup & Stats) */
.hero-right-side {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 3.5rem;
}

/* Efek Rotasi & Glow pada HP */
#hero .phone-frame {
    transform: rotate(-5deg);
    box-shadow: 
        0 40px 80px rgba(13, 43, 69, 0.3),
        0 0 0 1px rgba(255, 255, 255, 0.08),
        inset 0 0 0 1px rgba(255, 255, 255, 0.05),
        0 0 100px rgba(26, 172, 180, 0.4);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}
#hero .phone-frame:hover {
    transform: rotate(-3deg) scale(1.02);
    box-shadow: 0 0 120px rgba(26, 172, 180, 0.55);
}

/* Styling Stats Glassmorphism */
.hero-stats-new {
    display: flex;
    align-items: center;
    gap: 2.5rem;
    background: rgba(255, 255, 255, 0.03);
    padding: 1.5rem 2.5rem;
    border-radius: 16px;
    border: 1px solid rgba(255, 255, 255, 0.05);
    backdrop-filter: blur(10px);
}
.stat-num-new {
    font-family: 'Lora', serif;
    font-size: 1.75rem;
    font-weight: 700;
    color: var(--aqua);
    display: block;
}
.stat-label-new {
    font-size: 0.65rem;
    font-weight: 600;
    color: rgba(255, 255, 255, 0.5);
    text-transform: uppercase;
}
.stat-divider-new {
    width: 1px; height: 30px; background: rgba(255, 255, 255, 0.1);
}

/* Responsivitas Mobile */
@media (max-width: 900px) {
    .hero-main-layout { flex-direction: column; text-align: center; }
    #hero .phone-frame { transform: rotate(0deg) scale(1); }
    .hero-stats-new { flex-direction: column; gap: 1.5rem; width: 100%; }
    .stat-divider-new { width: 100px; height: 1px; }
}
TAHAP B: Implementasi Bento Grid pada Seksi Layanan
Karena mockup sudah dipindahkan, bersihkan sisa-sisa layout grid lama di <section id="fitur">.

Struktur Grid: Buat container <div class="bento-layout reveal">.

Kartu 1 (Kiri, Tinggi): Berisi Data Transparansi Distribusi dengan mockup grafik batang CSS murni. Gunakan class .bento-tall.

Kartu 2 (Kanan Atas, Lebar): Berisi Info Kualitas Air. Gunakan background gradien Navy (.bento-navy) dan indikator pulse hijau aktif.

Kartu 3 (Kanan Bawah, Lebar): Berisi Testimoni / Suara Warga (.bento-wide).

(Gunakan referensi kode HTML dan CSS Bento Grid yang telah disepakati sebelumnya untuk mengimplementasikan ketiga kartu ini).

✅ 3. Acceptance Criteria (Kriteria Selesai mutlak)
Developer WAJIB mencentang seluruh daftar ini sebelum melakukan Commit/Push:

[ ] Layout Hero terbagi menjadi dua kolom yang seimbang di layar Desktop.

[ ] Phone mockup berada di kanan, miring -5deg, dan memancarkan efek bayangan cahaya (Glow) warna Aqua.

[ ] Hero Stats (24/7, 2 Cara, Real-time) berada tepat di bawah Phone mockup dengan latar belakang blur (glassmorphism) dan angka berwarna Aqua.

[ ] Pada layar Mobile (< 900px), Phone mockup tidak miring (berdiri tegak 0deg) dan semua elemen menumpuk rata tengah secara vertikal.

[ ] Seksi Layanan (Fitur) sudah tidak menggunakan desain lama, melainkan menggunakan struktur Bento Grid (3 kotak asimetris: 1 tinggi di kiri, 2 tumpuk di kanan).