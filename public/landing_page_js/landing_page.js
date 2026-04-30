// Reveal on scroll
const revealEls = document.querySelectorAll(".reveal");
const observer = new IntersectionObserver(
    (entries) => {
        entries.forEach((entry, i) => {
            if (entry.isIntersecting) {
                setTimeout(() => entry.target.classList.add("visible"), i * 80);
                observer.unobserve(entry.target);
            }
        });
    },
    { threshold: 0.1 },
);
revealEls.forEach((el) => observer.observe(el));

// ─── SIMULASI TAGIHAN ───────────────────────────────
const TARIF = 2000;
const BEBAN = 20000;

const kubikInput = document.getElementById("kubikInput");
const kubikSlider = document.getElementById("kubikSlider");
const detailPemakaian = document.getElementById("detailPemakaian");
const nilaiPemakaian = document.getElementById("nilaiPemakaian");
const nilaiTotal = document.getElementById("nilaiTotal");

function formatRupiah(n) {
    return "Rp " + n.toLocaleString("id-ID");
}

function hitungSimulasi(kubik) {
    const k = Math.max(0, parseInt(kubik) || 0);
    const biayaPemakaian = k * TARIF;
    const total = biayaPemakaian + BEBAN;
    detailPemakaian.textContent = k + " m³ × Rp 2.000";
    nilaiPemakaian.textContent = formatRupiah(biayaPemakaian);
    nilaiTotal.textContent = formatRupiah(total);
}

kubikInput.addEventListener("input", function () {
    kubikSlider.value = Math.min(this.value, 100);
    hitungSimulasi(this.value);
});

kubikSlider.addEventListener("input", function () {
    kubikInput.value = this.value;
    hitungSimulasi(this.value);
});

hitungSimulasi(20);
// ─── NAVBAR HAMBURGER MENU ─────────────────────────
(function () {
    const hamburger = document.getElementById("hamburgerBtn");
    const navLinks = document.querySelector(".nav-links");
    const nav = document.querySelector("nav");
    const body = document.body;

    // Buat overlay
    const overlay = document.createElement("div");
    overlay.classList.add("nav-overlay");
    document.body.appendChild(overlay);

    // Toggle menu
    function openMenu() {
        hamburger.classList.add("active");
        navLinks.classList.add("active");
        overlay.classList.add("active");
        body.style.overflow = "hidden"; // cegah scroll
    }

    function closeMenu() {
        hamburger.classList.remove("active");
        navLinks.classList.remove("active");
        overlay.classList.remove("active");
        body.style.overflow = "";
    }

    hamburger.addEventListener("click", function () {
        if (navLinks.classList.contains("active")) {
            closeMenu();
        } else {
            openMenu();
        }
    });

    // Tutup menu saat overlay diklik
    overlay.addEventListener("click", closeMenu);

    // Tutup menu saat link diklik
    const allNavLinks = navLinks.querySelectorAll("a");
    allNavLinks.forEach((link) => {
        link.addEventListener("click", function () {
            closeMenu();
        });
    });

    // Tutup menu dengan tombol Escape
    document.addEventListener("keydown", function (e) {
        if (e.key === "Escape" && navLinks.classList.contains("active")) {
            closeMenu();
        }
    });

    // ─── SCROLL EVENT: tambah kelas .scrolled ──────
    window.addEventListener("scroll", function () {
        if (window.scrollY > 50) {
            nav.classList.add("scrolled");
        } else {
            nav.classList.remove("scrolled");
        }
    });
})();

// Hapus animasi setelah selesai (biarkan transform bebas)
document.addEventListener('DOMContentLoaded', function() {
    const phoneMockup = document.querySelector('.phone-mockup');
    if (phoneMockup) {
        phoneMockup.addEventListener('animationend', function(e) {
            // Pastikan semua animasi selesai (slide + shake total 1.8s)
            if (e.target === phoneMockup) {
                phoneMockup.classList.add('animation-done');
            }
        }, { once: true });
    }
});
