nah sekarang tinggal bagaimana caranya agar tampilannya ui ini responsif ke ukurang layar hp yang standar kisaran 6,1 - 6,7 inch.

Perlu dua breakpoint — satu untuk tablet (sudah ada di 900px) dan satu khusus mobile kecil. Yang perlu saya lihat dulu adalah apakah di CSS yang ada sudah ada media query di bawah 600px atau belum.
Dari CSS yang kamu kirim sebelumnya, breakpoint yang ada hanya max-width: 900px. Tidak ada yang spesifik untuk mobile kecil. Jadi tambahkan blok ini di bagian paling bawah landing_page.css:
css/* ─── MOBILE (6.1 – 6.7 inch ≈ 390px – 430px) ─── */
@media (max-width: 480px) {

    /* NAVBAR */
    nav {
        padding: 0.9rem 1.25rem;
    }

    /* HERO */
    #hero {
        padding: 5.5rem 1.25rem 3rem;
        min-height: auto;
    }

    #hero .hero-main-layout {
        flex-direction: column;
        gap: 2.5rem;
        text-align: center;
    }

    #hero .hero-content {
        align-items: center;
        display: flex;
        flex-direction: column;
        max-width: 100%;
    }

    .hero-announcement {
        font-size: 0.75rem;
        white-space: normal;
        text-align: left;
    }

    .announcement-text {
        white-space: normal;
        font-size: 0.78rem;
    }

    h1 {
        font-size: 1.9rem;
        text-align: center;
    }

    .hero-sub {
        font-size: 0.88rem;
        text-align: center;
        max-width: 100%;
    }

    .hero-actions {
        flex-direction: column;
        width: 100%;
        gap: 0.75rem;
    }

    .btn-primary {
        width: 100%;
        justify-content: center;
        padding: 0.9rem;
    }

    .btn-ghost {
        justify-content: center;
    }

    /* Phone mockup di hero — sembunyikan di mobile kecil,
       karena layarnya tidak cukup lebar untuk dua kolom */
    #hero .hero-right-side {
        display: none;
    }

    /* SECTION FITUR */
    #fitur {
        padding: 4rem 1.25rem;
    }

    .section-title {
        font-size: 1.6rem;
    }

    .section-desc {
        font-size: 0.88rem;
    }

    /* Stats bar: 2x2 grid */
    .stats-bar {
        display: grid;
        grid-template-columns: 1fr 1fr;
        border-radius: 14px;
        margin-bottom: 2.5rem;
    }

    .stats-bar-item {
        padding: 1.1rem 1rem;
        border-bottom: 1px solid var(--sand);
    }

    .stats-bar-item:nth-child(1),
    .stats-bar-item:nth-child(3) {
        border-right: 1px solid var(--sand);
    }

    .stats-bar-item:nth-child(3),
    .stats-bar-item:nth-child(4) {
        border-bottom: none;
    }

    .stats-bar-divider {
        display: none;
    }

    .stats-bar-num {
        font-size: 1.4rem;
    }

    /* Fitur layout: single column */
    .fitur-layout {
        grid-template-columns: 1fr;
        gap: 2rem;
    }

    /* Simulasi card */
    .simulasi-card {
        padding: 1.5rem;
    }

    .simulasi-input {
        font-size: 1.6rem;
    }

    .breakdown-row {
        grid-template-columns: 1fr auto;
        gap: 0.25rem;
    }

    .breakdown-detail {
        display: none; /* Sembunyikan kolom tengah di mobile */
    }

    /* Feature cards */
    .feature-card {
        padding: 1.25rem;
    }

    /* CARA BAYAR */
    #cara-bayar {
        padding: 4rem 1.25rem;
    }

    .steps-grid {
        grid-template-columns: 1fr;
        gap: 1.25rem;
    }

    .steps-grid::before {
        display: none;
    }

    .step-card {
        padding: 1.5rem 1.25rem;
        text-align: left;
        display: flex;
        align-items: flex-start;
        gap: 1rem;
    }

    /* Step card horizontal di mobile */
    .step-card .step-num {
        margin: 0;
        flex-shrink: 0;
        width: 40px;
        height: 40px;
        font-size: 1rem;
    }

    .step-card .step-icon {
        display: none; /* Sembunyikan emoji di mobile, nomor sudah cukup */
    }

    .step-card .step-text {
        flex: 1;
    }

    /* LOKET */
    #loket {
        padding: 4rem 1.25rem;
    }

    .loket-layout {
        grid-template-columns: 1fr;
        gap: 2rem;
        margin-top: 2rem;
    }

    .loket-visual {
        padding: 1.5rem;
    }

    /* FOOTER */
    footer {
        padding: 3rem 1.25rem 2rem;
    }

    .footer-top {
        grid-template-columns: 1fr;
        gap: 2rem;
        margin-bottom: 2rem;
    }

    .footer-bottom {
        flex-direction: column;
        gap: 0.5rem;
        text-align: center;
    }

    .footer-brand p {
        max-width: 100%;
    }
}
Dua hal yang perlu diperhatikan setelah ini diterapkan:
Pertama, di step card mobile, kamu perlu bungkus teks h3 dan p di dalam <div class="step-text"> di HTML agar CSS selector .step-card .step-text bekerja. Atau hapus saja selector itu dari CSS karena tidak krusial.
Kedua, phone mockup di hero sengaja disembunyikan di mobile karena layar 390px tidak cukup lebar untuk dua kolom. Warga sudah bisa melihat "preview UI" lewat phone mockup di desktop/tablet, sedangkan di mobile cukup teks dan tombol yang tampil — lebih fokus dan tidak sempit.