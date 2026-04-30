    <!DOCTYPE html>
    <html lang="id">

    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>SIM-PDAM — Desa Lanto</title>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link
            href="https://fonts.googleapis.com/css2?family=Lora:ital,wght@0,400;0,600;0,700;1,400&family=Plus+Jakarta+Sans:wght@300;400;500;600&display=swap"
            rel="stylesheet">
        <link rel="stylesheet" href="{{ asset('landing_page_css/landing_page.css') }}">
    </head>

    <body>

        <!-- ─── NAVBAR ─────────────────────────────────────── -->
        <nav>
            <a href="#" class="nav-brand">
                <div class="nav-logo">
                    <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M12 2C12 2 4 10.5 4 15.5a8 8 0 0016 0C20 10.5 12 2 12 2zm0 18a6 6 0 01-6-6c0-3.5 4.5-9.8 6-11.8 1.5 2 6 8.3 6 11.8a6 6 0 01-6 6z" />
                        <path d="M9 17a3 3 0 006 0" fill="none" stroke="white" stroke-width="1.5"
                            stroke-linecap="round" />
                    </svg>
                </div>
                <div class="nav-brand-text">
                    <span class="nav-brand-name">SIM-PDAM</span>
                    <span class="nav-brand-sub">Desa Lanto</span>
                </div>
            </a>
            <ul class="nav-links">
                <li><a href="#fitur">Layanan</a></li>
                <li><a href="#cara-bayar">Cara Bayar</a></li>
                <li><a href="#kontak">Kontak</a></li>
                <li><a href="/login" class="nav-cta">Masuk ke Portal</a></li>
            </ul>
            <button class="hamburger" aria-label="Buka menu navigasi" id="hamburgerBtn">
                <span class="hamburger-line"></span>
                <span class="hamburger-line"></span>
                <span class="hamburger-line"></span>
            </button>
        </nav>

        <!-- ─── HERO ────────────────────────────────────────── -->
        <section id="hero">
            <div class="hero-bg"></div>
            <div class="hero-grid"></div>
            <div class="hero-orb orb-1"></div>
            <div class="hero-orb orb-2"></div>
            <div class="hero-orb orb-3"></div>

            <div class="hero-main-layout">

                <!-- KIRI: Teks -->
                <div class="hero-content">
                    <div class="hero-announcement">
                        <span class="announcement-badge">Info</span>
                        <span class="announcement-text">
                            <strong>Info Desa:</strong> Tagihan air periode April 2026 telah terbit. Batas bayar tgl 20.
                        </span>
                        <span class="announcement-arrow">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2.5">
                                <path d="M5 12h14M13 6l6 6-6 6" />
                            </svg>
                        </span>
                    </div>

                    <h1>
                        Tagihan air Anda,<br>
                        <em>selesai dari rumah.</em>
                    </h1>
                    <p class="hero-sub">
                        Pantau pemakaian kubikasi bulanan, cek besaran tagihan, dan kirim
                        bukti bayar kapan saja — tanpa harus antre di loket.
                    </p>
                    <div class="hero-actions">
                        <a href="/login" class="btn-primary">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2.5">
                                <circle cx="12" cy="8" r="4" />
                                <path d="M4 20c0-4 3.6-7 8-7s8 3 8 7" />
                            </svg>
                            Masuk ke Portal
                        </a>
                        <a href="#cara-bayar" class="btn-ghost">
                            Cara penggunaan
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2.5">
                                <path d="M5 12h14M13 6l6 6-6 6" />
                            </svg>
                        </a>
                    </div>
                </div>

                <!-- KANAN: Phone mockup saja -->
                <div class="hero-right-side">
                    <div class="phone-mockup">
                        <div class="phone-accent"></div>
                        <div class="phone-accent-2"></div>
                        <div class="phone-frame">
                            <div class="phone-notch"></div>
                            <div class="phone-screen">
                                <div class="phone-status-bar">
                                    <span>9:41</span>
                                    <span>⬛⬛⬛</span>
                                </div>
                                <div class="phone-header">Tagihan Saya</div>
                                <div class="phone-subheader">Budi Santoso · PAM-2501-0042</div>
                                <div class="tagihan-card">
                                    <div class="tagihan-month">Tagihan Bulan Ini · Apr 2026</div>
                                    <div class="tagihan-amount">Rp 80.000</div>
                                    <div class="tagihan-meta">
                                        <span class="tagihan-usage">30 m³ pemakaian</span>
                                        <span class="tagihan-badge">Belum Bayar</span>
                                    </div>
                                </div>
                                <button class="btn-bayar">💳 Bayar Sekarang</button>
                                <div class="riwayat-label">Riwayat Pembayaran</div>
                                <div class="riwayat-item">
                                    <div>
                                        <div class="riwayat-bulan">Mar 2026</div>
                                        <div class="riwayat-kubik">28 m³ · Rp 76.000</div>
                                    </div>
                                    <span class="riwayat-status status-lunas">Lunas</span>
                                </div>
                                <div class="riwayat-item">
                                    <div>
                                        <div class="riwayat-bulan">Feb 2026</div>
                                        <div class="riwayat-kubik">32 m³ · Rp 84.000</div>
                                    </div>
                                    <span class="riwayat-status status-lunas">Lunas</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </section>

        <!-- wave -->
        <div class="wave-divider">
            <svg viewBox="0 0 1440 60" preserveAspectRatio="none" height="60" xmlns="http://www.w3.org/2000/svg">
                <path d="M0,40 C360,60 720,10 1080,40 C1260,55 1380,50 1440,48 L1440,60 L0,60 Z" fill="#F4EFE6" />
            </svg>
        </div>

        <!-- ─── FITUR ─────────────────────────────────────────── -->
        <section id="fitur">

            <!-- Header -->
            <div class="reveal" style="text-align: center; margin-bottom: 3.5rem;">
                <p class="section-label">Layanan Kami</p>
                <h2 class="section-title">Semua yang Anda butuhkan, dalam satu portal.</h2>
                <p class="section-desc" style="margin: 0 auto;">
                    Dirancang agar warga dari semua kalangan bisa mengakses informasi
                    dan melakukan pembayaran dengan mudah — kapan pun, di mana pun.
                </p>
            </div>

            <!-- Stats Bar -->
            <div class="stats-bar reveal">
                <div class="stats-bar-item">
                    <span class="stats-bar-num">24/7</span>
                    <span class="stats-bar-label">Akses Portal</span>
                </div>
                <div class="stats-bar-divider"></div>
                <div class="stats-bar-item">
                    <span class="stats-bar-num">2 Cara</span>
                    <span class="stats-bar-label">Metode Pembayaran</span>
                </div>
                <div class="stats-bar-divider"></div>
                <div class="stats-bar-item">
                    <span class="stats-bar-num">Real-time</span>
                    <span class="stats-bar-label">Status Tagihan</span>
                </div>
                <div class="stats-bar-divider"></div>
                <div class="stats-bar-item">
                    <span class="stats-bar-num">Aman</span>
                    <span class="stats-bar-label">Diverifikasi Petugas</span>
                </div>
            </div>

            <!-- Simulasi + Feature Cards -->
            <div class="fitur-layout">

                <!-- KIRI: Simulasi Tagihan -->
                <div class="simulasi-card reveal">
                    <div class="simulasi-header">
                        <div class="simulasi-icon">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                stroke="var(--teal)" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round">
                                <rect width="16" height="20" x="4" y="2" rx="2"></rect>
                                <line x1="8" x2="16" y1="6" y2="6"></line>
                                <line x1="16" x2="16.01" y1="14" y2="14"></line>
                                <line x1="16" x2="16.01" y1="10" y2="10"></line>
                                <line x1="12" x2="12.01" y1="14" y2="14"></line>
                                <line x1="12" x2="12.01" y1="10" y2="10"></line>
                                <line x1="8" x2="8.01" y1="14" y2="14"></line>
                                <line x1="8" x2="8.01" y1="10" y2="10"></line>
                            </svg>
                        </div>
                        <div>
                            <h3>Simulasi Tagihan Air</h3>
                            <p>Masukkan perkiraan pemakaian untuk melihat estimasi tagihan Anda bulan ini.</p>
                        </div>
                    </div>

                    <div class="simulasi-input-group">
                        <label class="simulasi-label">Perkiraan Pemakaian Air</label>
                        <div class="simulasi-input-wrap">
                            <input type="number" id="kubikInput" class="simulasi-input" value="20"
                                min="0" max="999" placeholder="0" />
                            <span class="simulasi-unit">m³</span>
                        </div>
                        <input type="range" id="kubikSlider" class="simulasi-slider" min="0"
                            max="100" value="20" />
                        <div class="simulasi-slider-labels">
                            <span>0 m³</span>
                            <span>50 m³</span>
                            <span>100 m³</span>
                        </div>
                    </div>

                    <div class="simulasi-breakdown">
                        <div class="breakdown-row">
                            <span class="breakdown-label">Biaya Pemakaian</span>
                            <span class="breakdown-detail" id="detailPemakaian">20 m³ × Rp 2.000</span>
                            <span class="breakdown-value" id="nilaiPemakaian">Rp 40.000</span>
                        </div>
                        <div class="breakdown-row">
                            <span class="breakdown-label">Biaya Beban</span>
                            <span class="breakdown-detail">Tetap per bulan</span>
                            <span class="breakdown-value">Rp 20.000</span>
                        </div>
                        <div class="breakdown-divider"></div>
                        <div class="breakdown-row breakdown-total">
                            <span class="breakdown-label">Estimasi Total</span>
                            <span class="breakdown-detail"></span>
                            <span class="breakdown-value" id="nilaiTotal">Rp 60.000</span>
                        </div>
                    </div>

                    <div class="simulasi-note">
                        ℹSimulasi ini menggunakan tarif resmi Rp 2.000/m³ + biaya beban Rp 20.000.
                        Tagihan aktual akan tertera di portal setelah petugas mencatat meter Anda.
                    </div>

                    <a href="/login" class="simulasi-cta">
                        Cek Tagihan Asli Saya
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2.5">
                            <path d="M5 12h14M13 6l6 6-6 6" />
                        </svg>
                    </a>
                </div>

                <!-- KANAN: Feature Cards -->
                <div class="feature-cards">
                    <div class="feature-card reveal">
                        <div class="feature-icon icon-blue">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                stroke="var(--aqua)" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round">
                                <path
                                    d="M12 22a7 7 0 0 0 7-7c0-2-1-3.9-3-5.5s-3.5-4-4-6.5c-.5 2.5-2 4.9-4 6.5C6 11.1 5 13 5 15a7 7 0 0 0 7 7z">
                                </path>
                            </svg>
                        </div>
                        <div>
                            <h3>Pantau Pemakaian Bulanan</h3>
                            <p>Lihat riwayat kubikasi setiap bulan secara transparan. Ketahui tren konsumsi air rumah
                                tangga
                                Anda dari waktu ke waktu.</p>
                        </div>
                    </div>
                    <div class="feature-card reveal">
                        <div class="feature-icon icon-green">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                stroke="var(--teal)" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round">
                                <rect width="14" height="20" x="5" y="2" rx="2" ry="2">
                                </rect>
                                <path d="M12 18h.01"></path>
                            </svg>
                        </div>
                        <div>
                            <h3>Bayar via Transfer Bank</h3>
                            <p>Transfer ke rekening desa, unggah foto struk langsung di portal, dan tunggu konfirmasi
                                petugas — semua dari HP Anda.</p>
                        </div>
                    </div>
                    <div class="feature-card reveal">
                        <div class="feature-icon icon-warm">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                stroke="var(--warm)" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round">
                                <rect width="20" height="12" x="2" y="6" rx="2"></rect>
                                <circle cx="12" cy="12" r="2"></circle>
                                <path d="M6 12h.01M18 12h.01"></path>
                            </svg>
                        </div>
                        <div>
                            <h3>Tetap Bisa Bayar Tunai di Loket</h3>
                            <p>Bagi warga yang belum terbiasa transfer, pembayaran langsung di Balai Desa tetap dilayani
                                sepenuhnya oleh petugas.</p>
                        </div>
                    </div>
                </div>

            </div>
        </section>

        <!-- ─── CARA BAYAR ────────────────────────────────────── -->
        <section id="cara-bayar">
            <div class="wave-bg"></div>
            <div class="reveal">
                <p class="section-label">Panduan Singkat</p>
                <h2 class="section-title">Tiga langkah, tagihan beres.</h2>
                <p class="section-desc">
                    Proses pembayaran dirancang sesederhana mungkin agar tidak membingungkan.
                </p>
            </div>

            <div class="steps-grid">
                <div class="step-card reveal">
                    <div class="step-num">1</div>
                    <div class="step-icon">
                        <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="var(--sky)"
                            stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"
                            style="margin: 0 auto;">
                            <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"></path>
                            <polyline points="10 17 15 12 10 7"></polyline>
                            <line x1="15" y1="12" x2="3" y2="12"></line>
                        </svg>
                    </div>
                    <div class="step-text">
                        <h3>Masuk ke Portal</h3>
                        <p>Login menggunakan email yang terdaftar di kantor PDAM Desa Lanto. Belum punya akun? Hubungi
                            petugas untuk pendaftaran.</p>
                    </div>
                </div>
                <div class="step-card reveal">
                    <div class="step-num">2</div>
                    <div class="step-icon">
                        <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="var(--sky)"
                            stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"
                            style="margin: 0 auto;">
                            <path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"></path>
                            <polyline points="14 2 14 8 20 8"></polyline>
                            <line x1="16" y1="13" x2="8" y2="13"></line>
                            <line x1="16" y1="17" x2="8" y2="17"></line>
                            <line x1="10" y1="9" x2="8" y2="9"></line>
                        </svg>
                    </div>
                    <div class="step-text">
                        <h3>Lihat & Konfirmasi Tagihan</h3>
                        <p>Cek rincian biaya pemakaian dan biaya beban bulan ini. Lalu transfer ke rekening resmi PDAM
                            Desa
                            Lanto.</p>
                    </div>
                </div>
                <div class="step-card reveal">
                    <div class="step-num">3</div>
                    <div class="step-icon">
                        <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="var(--sky)"
                            stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"
                            style="margin: 0 auto;">
                            <path d="M4 14.899A7 7 0 1 1 15.71 8h1.79a4.5 4.5 0 0 1 2.5 8.242"></path>
                            <path d="M12 12v9"></path>
                            <path d="m16 16-4-4-4 4"></path>
                        </svg>
                    </div>
                    <div class="step-text">
                        <h3>Upload Bukti Transfer</h3>
                        <p>Foto atau screenshot struk transfer diunggah langsung di portal. Petugas akan memverifikasi
                            dalam
                            jam kerja.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- wave reverse -->
        <div style="background: var(--navy); margin-bottom: -2px;">
            <svg viewBox="0 0 1440 60" preserveAspectRatio="none" height="60" xmlns="http://www.w3.org/2000/svg">
                <path d="M0,20 C360,0 720,50 1080,20 C1260,5 1380,12 1440,15 L1440,60 L0,60 Z" fill="#F4EFE6" />
            </svg>
        </div>

        <!-- ─── LOKET ──────────────────────────────────────────── -->
        <section id="loket">
            <div class="loket-layout">
                <div class="reveal">
                    <p class="section-label">Untuk Semua Warga</p>
                    <h2 class="section-title">Tidak bisa transfer? Loket kami tetap terbuka.</h2>
                    <p class="section-desc" style="margin-bottom: 1.5rem;">
                        Kami paham tidak semua warga terbiasa bertransaksi digital.
                        Pembayaran tunai di Balai Desa tetap menjadi pilihan yang selalu tersedia.
                    </p>
                    <p class="section-desc">
                        Cukup datang, sebutkan nama atau nomor pelanggan, dan petugas kami
                        akan menangani proses pencatatan pembayaran langsung di sistem.
                    </p>
                </div>

                <div class="loket-visual reveal">
                    <div class="loket-icon-big">
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="var(--teal)"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="3" x2="21" y1="22" y2="22"></line>
                            <line x1="6" x2="6" y1="18" y2="11"></line>
                            <line x1="10" x2="10" y1="18" y2="11"></line>
                            <line x1="14" x2="14" y1="18" y2="11"></line>
                            <line x1="18" x2="18" y1="18" y2="11"></line>
                            <polygon points="12 2 20 7 4 7"></polygon>
                        </svg>
                    </div>
                    <h3>Loket Pembayaran Balai Desa</h3>
                    <p>Melayani pembayaran air dan pengaduan warga secara langsung. Tanpa perlu aplikasi.</p>
                    <ul class="loket-info-list">
                        <li>Senin – Jumat, pukul 08.00 – 15.00 WITA</li>
                        <li>Tidak perlu membawa dokumen khusus</li>
                        <li>Petugas langsung cetak struk pembayaran</li>
                        <li>Riwayat tercatat otomatis di sistem</li>
                    </ul>
                </div>
            </div>
        </section>

        <!-- ─── FOOTER ────────────────────────────────────────── -->
        <footer id="kontak">
            <div class="footer-top">
                <div class="footer-brand">
                    <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.25rem;">
                        <div class="nav-logo" style="width:32px; height:32px;">
                            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"
                                style="width:18px; height:18px;">
                                <path fill="white" d="M12 2C12 2 4 10.5 4 15.5a8 8 0 0016 0C20 10.5 12 2 12 2z" />
                            </svg>
                        </div>
                        <span class="nav-brand-name" style="color: white; font-family: 'Lora', serif;">SIM-PDAM Desa
                            Lanto</span>
                    </div>
                    <p>Sistem Informasi Manajemen Air Bersih untuk pengelolaan tagihan dan pemakaian air warga Desa
                        Lanto
                        secara transparan dan efisien.</p>
                </div>

                <div class="footer-col">
                    <h4>Tautan Cepat</h4>
                    <ul>
                        <li><a href="#fitur">Layanan</a></li>
                        <li><a href="#cara-bayar">Cara Bayar</a></li>
                        <li><a href="/login">Masuk Portal</a></li>
                    </ul>
                </div>

                <div class="footer-col">
                    <h4>Pusat Bantuan</h4>
                    <ul>
                        <li>Kantor PDAM Desa Lanto, Kec. Wakahohondo</li>
                        <li>Sen–Jum · 08.00 – 15.00 WITA</li>
                        <li>
                            <a href="https://wa.me/6282255087336" class="footer-wa-btn" target="_blank">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="#25D366">
                                    <path
                                        d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z" />
                                </svg>
                                Chat WhatsApp
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <hr class="footer-divider" />

            <div class="footer-bottom">
                <span class="footer-copy">© 2026 SIM-PDAM Desa Lanto · Semua hak dilindungi</span>
                <span class="footer-tag">Dibangun dengan ❤ untuk warga Desa Lanto</span>
            </div>
        </footer>

        <script src="{{ asset('landing_page_js/landing_page.js') }}"></script>
        <script>

        </script>
    </body>

    </html>
