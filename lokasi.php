<?php
$page_title = "Lokasi & Peta Desa";
include 'includes/header.php';
?>

<style>
    /* ------------------------------------
       HERO BANNER LOKASI
    ------------------------------------ */
    .hero-location-section {
        position: relative;
        background: linear-gradient(
            135deg, 
            rgba(15, 81, 50, 0.90) 0%, 
            rgba(25, 135, 84, 0.82) 100%
        ), 
        url('https://images.unsplash.com/photo-1500382017468-9049fed747ef?auto=format&fit=crop&w=1920&q=80') center/cover no-repeat;
        padding: 90px 0 70px 0;
        border-bottom-left-radius: 40px;
        border-bottom-right-radius: 40px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
    }

    .hero-badge {
        background-color: rgba(255, 255, 255, 0.95);
        color: #146338;
        font-weight: 700;
        padding: 8px 20px;
        border-radius: 50px;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-size: 0.9rem;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        backdrop-filter: blur(5px);
    }

    .hero-title {
        font-size: 2.8rem;
        font-weight: 800;
        line-height: 1.25;
        letter-spacing: -0.5px;
        text-shadow: 0 2px 10px rgba(0,0,0,0.2);
    }

    .hero-subtitle {
        font-size: 1.1rem;
        opacity: 0.95;
        max-width: 750px;
        margin: 0 auto;
        line-height: 1.6;
        font-weight: 400;
        text-shadow: 0 1px 5px rgba(0,0,0,0.2);
    }

    @media (max-width: 768px) {
        .hero-location-section {
            padding: 70px 0 50px 0;
            border-bottom-left-radius: 24px;
            border-bottom-right-radius: 24px;
        }
        .hero-title {
            font-size: 2rem;
        }
    }
</style>

<!-- 1. HERO SECTION -->
<section class="hero-location-section text-white mb-5">
    <div class="container text-center">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="mb-3">
                    <span class="hero-badge">
                        <i class="fa-solid fa-map-location-dot text-success"></i> Wilayah Administrasi
                    </span>
                </div>
                <h1 class="hero-title mb-3">
                    Lokasi & Peta Desa Jatijaya
                </h1>
                <p class="hero-subtitle">
                    Kecamatan Gunung Tanjung, Kabupaten Tasikmalaya, Jawa Barat. Petunjuk lokasi resmi Kantor Balai Desa Jatijaya.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- 2. SECTION LOKASI & PETA GOOGLE MAPS -->
<div class="container py-3 mb-5">
    <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white">
        <div class="row align-items-center g-4">
            
            <!-- Sisi Kiri: Detail Informasi Alamat -->
            <div class="col-lg-5">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="bg-success bg-opacity-10 text-success p-3 rounded-4">
                        <i class="fa-solid fa-building-flag fs-3"></i>
                    </div>
                    <div>
                        <h3 class="fw-bold text-dark mb-0">Kantor Desa</h3>
                        <small class="text-muted">Desa Jatijaya</small>
                    </div>
                </div>
                
                <p class="text-secondary small mb-4">
                    Kantor Desa Jatijaya berlokasi strategis di pusat Kecamatan Gunung Tanjung, dapat dijangkau dengan mudah oleh masyarakat untuk keperluan pelayanan publik dan administrasi kependudukan.
                </p>

                <ul class="list-unstyled d-flex flex-column gap-3 mb-0">
                    <li class="d-flex align-items-start gap-3 text-secondary small">
                        <i class="fa-solid fa-location-dot text-success fs-5 mt-1"></i>
                        <span><strong>Alamat Lengkap:</strong><br>Jl. kp pannusan gng cikodol, Jatijaya, Kec. Gunungtanjung, Kabupaten Tasikmalaya, Jawa Barat 46418</span>
                    </li>
                    <li class="d-flex align-items-center gap-3 text-secondary small">
                        <i class="fa-solid fa-clock text-success fs-5"></i>
                        <span><strong>Jam Operasional:</strong><br>Senin - Jumat (08:00 - 15:30 WIB)</span>
                    </li>
                    <li class="d-flex align-items-center gap-3 text-secondary small">
                        <i class="fa-solid fa-phone text-success fs-5"></i>
                        <span><strong>Telepon / WhatsApp:</strong><br>(0265) 1234567 / 0812-3456-7890</span>
                    </li>
                </ul>

                <!-- Tombol Buka di Aplikasi Google Maps -->
                <div class="mt-4">
                    <a href="https://maps.google.com/?q=Kantor+Desa+Jatijaya+Gunungtanjung+Tasikmalaya" target="_blank" class="btn btn-success btn-sm rounded-pill px-4 fw-bold shadow-sm">
                        <i class="fa-solid fa-diamond-turn-right me-2"></i> Petunjuk Arah (Google Maps)
                    </a>
                </div>
            </div>

<!-- Gantikan <iframe ...> </iframe> dengan kode div & script ini -->
<div id="map-desa" class="rounded-4 overflow-hidden shadow-sm border" style="height: 280px; width: 100%;"></div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Koordinat Desa Jatijaya, Gunungtanjung, Tasikmalaya
        var lat = -7.4116;
        var lng = 108.2882;

        var map = L.map('map-desa').setView([lat, lng], 15);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '© OpenStreetMap'
        }).addTo(map);

        L.marker([lat, lng]).addTo(map)
            .bindPopup('<b>Kantor Desa Jatijaya</b><br>Kec. Gunungtanjung, Tasikmalaya.')
            .openPopup();
    });
</script>
                </div>
            </div>

        </div>
    </div>
</div>

<?php 
include 'includes/footer.php'; 
?>