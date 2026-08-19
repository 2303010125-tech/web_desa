<?php
$page_title = "Profil Desa Jatijaya";

// Koneksi ke Database (menggunakan @ agar tidak tampil error jika MySQL mati)
$koneksi = @mysqli_connect("localhost", "root", "", "db_desa_jatijaya");

include 'includes/header.php';
?>

<!-- Leaflet Maps CSS & JS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<style>
    /* HERO BANNER MATCHING EXACT DESIGN */
    .hero-banner {
        position: relative;
        background: linear-gradient(180deg, rgba(16, 94, 60, 0.92) 0%, rgba(13, 71, 45, 0.95) 100%),
                    url('https://images.unsplash.com/photo-1500382017468-9049fed747ef?auto=format&fit=crop&w=1920&q=80') center/cover no-repeat;
        border-radius: 0 0 28px 28px;
        padding: 60px 20px 70px 20px;
        color: #ffffff;
        text-align: center;
        margin-top: 0;
        margin-bottom: 40px;
    }

    .hero-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background-color: #ffffff;
        color: #105e3c;
        font-size: 0.82rem;
        font-weight: 700;
        padding: 6px 18px;
        border-radius: 50px;
        margin-bottom: 20px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

    .hero-banner h1 {
        font-size: 2.8rem;
        font-weight: 800;
        letter-spacing: -0.5px;
        margin-bottom: 12px;
        color: #ffffff;
    }

    .hero-banner p {
        font-size: 0.98rem;
        color: rgba(255, 255, 255, 0.88);
        max-width: 680px;
        margin: 0 auto;
        line-height: 1.6;
        font-weight: 400;
    }

    /* Card Styling & Hover */
    .card-custom {
        border: none;
        border-radius: 20px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.04);
        background: #ffffff;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .card-hover:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.08) !important;
    }

    .card-clickable {
        cursor: pointer;
        position: relative;
    }

    .avatar-kades {
        width: 80px;
        height: 80px;
        object-fit: cover;
        border: 3px solid #105e3c;
    }

    .btn-green {
        background-color: #108a55;
        color: white;
        border-radius: 30px;
        padding: 10px 24px;
        font-weight: 600;
        border: none;
        transition: all 0.3s;
    }
    .btn-green:hover {
        background-color: #0d7347;
        color: white;
    }

    /* Hierarchy Level Styling */
    .section-divider-title {
        position: relative;
        text-align: center;
        margin: 30px 0 20px 0;
    }
    .section-divider-title::before {
        content: "";
        position: absolute;
        top: 50%;
        left: 0;
        right: 0;
        height: 1px;
        background: #e2e8f0;
        z-index: 1;
    }
    .section-divider-title span {
        position: relative;
        z-index: 2;
        background: #f8fafc;
        padding: 4px 16px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 700;
        color: #105e3c;
        border: 1px solid #cbd5e1;
    }

    /* Modal Custom Styling */
    .modal-dusun-content {
        border: none;
        border-radius: 24px;
        overflow: hidden;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
    }
    .modal-dusun-header {
        background: linear-gradient(135deg, #105e3c 0%, #0d7347 100%);
        color: #ffffff;
        padding: 24px 28px;
        border: none;
    }
    .dusun-card-item {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        padding: 16px;
        transition: all 0.25s ease;
    }
    .dusun-card-item:hover {
        background: #ffffff;
        border-color: #105e3c;
        box-shadow: 0 6px 18px rgba(16, 94, 60, 0.1);
        transform: translateY(-2px);
    }
    .dusun-number {
        width: 38px;
        height: 38px;
        background: #105e3c;
        color: white;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 0.95rem;
    }

    @media (min-width: 768px) {
        .border-end-md {
            border-right: 1px solid #dee2e6 !important;
        }
    }
</style>

<div class="container-fluid p-0">
    <!-- 1. HERO BANNER PERSIS DENGAN BERITA/INFORMASI -->
    <div class="hero-banner">
        <div class="hero-badge">
            <i class="fa-solid fa-id-card"></i> Profil Resmi
        </div>
        <h1>Profil Desa Jatijaya</h1>
        <p>Kecamatan Gunung Tanjung, Kabupaten Tasikmalaya. Mengenal lebih dekat sejarah, visi misi, potensi, dan tata kelola Pemerintah Desa Jatijaya.</p>
    </div>
</div>

<div class="container pb-5">

    <!-- 2. STATISTIK RINGKAS DESA -->
    <div class="row g-3 mb-5 justify-content-center">
        <div class="col-md-4 col-sm-6">
            <div class="card-custom p-4 text-center card-hover">
                <i class="fa-solid fa-users fa-2x mb-3" style="color: #105e3c;"></i>
                <h3 class="fw-bold text-dark mb-1">3.938</h3>
                <p class="text-muted small mb-0">Total Penduduk</p>
            </div>
        </div>
        <div class="col-md-4 col-sm-6">
            <div class="card-custom p-4 text-center card-hover">
                <i class="fa-solid fa-house-chimney fa-2x mb-3" style="color: #105e3c;"></i>
                <h3 class="fw-bold text-dark mb-1">1.376</h3>
                <p class="text-muted small mb-0">Kepala Keluarga</p>
            </div>
        </div>
        <!-- Kartu 6 Dusun Interaktif -->
        <div class="col-md-4 col-sm-6">
            <div class="card-custom p-4 text-center card-hover card-clickable" data-bs-toggle="modal" data-bs-target="#modalDusun">
                <div class="position-absolute top-0 end-0 m-3">
                    <span class="badge bg-success-subtle text-success rounded-circle p-2"><i class="fa-solid fa-arrow-up-right-from-square"></i></span>
                </div>
                <i class="fa-solid fa-map-location fa-2x mb-3" style="color: #105e3c;"></i>
                <h3 class="fw-bold text-dark mb-1">6 Dusun</h3>
                <p class="text-muted small mb-0">Kedusunan / RW <span class="text-success fw-bold ms-1"></span></p>
            </div>
        </div>
    </div>

    <!-- 3. VISI & MISI PEMERINTAH KABUPATEN TASIKMALAYA -->
    <div class="card card-custom p-4 p-md-5 mb-5">
        <div class="text-center mb-4">
            <p class="text-uppercase fw-bold text-muted small mb-1">Pemerintah Kabupaten Tasikmalaya</p>
            <h3 class="fw-bold" style="color: #105e3c;"><i class="fa-solid fa-bullseye me-2"></i>Visi & Misi</h3>
            <p class="text-muted small">Pedoman arah pembangunan Pemerintah Kabupaten Tasikmalaya</p>
        </div>
        <div class="row g-4 align-items-center">
            <div class="col-md-5 border-end-md">
                <div class="p-4 bg-light rounded-4 h-100 d-flex flex-column justify-content-center">
                    <h5 class="fw-bold mb-3" style="color: #105e3c;"><i class="fa-solid fa-eye me-2"></i>VISI</h5>
                    <p class="fst-italic text-dark mb-0 fs-6 fw-semibold">
                        "Tasikmalaya yang Religius/Islami, sebagai Kabupaten yang maju dan sejahtera, serta kompetitif dalam bidang Agribisnis di Jawa Barat tahun 2010"
                    </p>
                </div>
            </div>
            <div class="col-md-7">
                <div class="p-2">
                    <h5 class="fw-bold mb-3" style="color: #105e3c;"><i class="fa-solid fa-list-check me-2"></i>MISI</h5>
                    <ol class="text-secondary small mb-0 lh-lg ps-3">
                        <li class="mb-2">Mewujudkan Sumber Daya Manusia yang beriman dan bertaqwa, serta berakhlaqul karimah.</li>
                        <li class="mb-2">Mewujudkan Sumber Daya Manusia yang berkualitas, dan mandiri.</li>
                        <li class="mb-2">Mewujudkan kepemerintahan yang baik dan pemerintah yang bersih.</li>
                        <li class="mb-2">Mewujudkan Pembangunan Daerah melalui pemberdayaan masyarakat.</li>
                        <li class="mb-2">Mewujudkan pertumbuhan Ekonomi Daerah melalui pengembangan agribisnis dengan didukung oleh sektor lain.</li>
                        <li class="mb-0">Mewujudkan tata ruang dan pengelolaan pertanahan yang berkesinambungan dan berwawasan lingkungan.</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- 4. PERANGKAT & STRUKTUR ORGANISASI DESA JATIJAYA -->
    <div class="mb-5">
        <div class="text-center mb-4">
            <h3 class="fw-bold" style="color: #105e3c;"><i class="fa-solid fa-sitemap me-2"></i>Struktur Organisasi Desa Jatijaya</h3>
            <p class="text-muted small">Aparatur Pemerintah Desa Jatijaya berdasarkan bagan tata kerja resmi</p>
        </div>

        <div class="p-3 p-md-4 rounded-4" style="background-color: #f8fafc; border: 1px dashed #cbd5e1;">
            
            <!-- LEVEL 1: KEPALA DESA & SEKRETARIS DESA -->
            <div class="row g-3 justify-content-center text-center mb-2">
                <div class="col-md-4 col-sm-6">
                    <div class="card-custom p-3 card-hover h-100 border-start border-4 border-success">
                        <img src="https://ui-avatars.com/api/?name=Ruswandi&background=105e3c&color=fff" class="rounded-circle avatar-kades mb-2" alt="Kepala Desa">
                        <h6 class="fw-bold text-dark mb-1">RUSWANDI</h6>
                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1 small">Kepala Desa Jatijaya</span>
                    </div>
                </div>
                <div class="col-md-4 col-sm-6">
                    <div class="card-custom p-3 card-hover h-100 border-start border-4 border-success">
                        <img src="https://ui-avatars.com/api/?name=Iing+Supriadi&background=105e3c&color=fff" class="rounded-circle avatar-kades mb-2" alt="Sekretaris Desa">
                        <h6 class="fw-bold text-dark mb-1">IING SUPRIADI</h6>
                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1 small">Sekretaris Desa</span>
                    </div>
                </div>
            </div>

            <!-- LEVEL 2: KEPALA URUSAN (KAUR) -->
            <div class="section-divider-title">
                <span>SEKRETARIAT DESA (URUSAN)</span>
            </div>
            <div class="row g-3 justify-content-center text-center">
                <div class="col-md-4 col-sm-6">
                    <div class="card-custom p-3 card-hover h-100">
                        <img src="https://ui-avatars.com/api/?name=Dadang&background=e6f4ed&color=105e3c" class="rounded-circle avatar-kades mb-2" alt="Kaur Perencanaan">
                        <h6 class="fw-bold text-dark mb-1">DADANG</h6>
                        <p class="small text-muted fw-semibold mb-0">Kaur Perencanaan</p>
                    </div>
                </div>
                <div class="col-md-4 col-sm-6">
                    <div class="card-custom p-3 card-hover h-100">
                        <img src="https://ui-avatars.com/api/?name=Ali&background=e6f4ed&color=105e3c" class="rounded-circle avatar-kades mb-2" alt="Kaur Keuangan">
                        <h6 class="fw-bold text-dark mb-1">ALI</h6>
                        <p class="small text-muted fw-semibold mb-0">Kaur Keuangan</p>
                    </div>
                </div>
                <div class="col-md-4 col-sm-6">
                    <div class="card-custom p-3 card-hover h-100">
                        <img src="https://ui-avatars.com/api/?name=Asep+Syaiful+Uyun&background=e6f4ed&color=105e3c" class="rounded-circle avatar-kades mb-2" alt="Kaur Umum">
                        <h6 class="fw-bold text-dark mb-1">ASEP SYAIFUL UYUN</h6>
                        <p class="small text-muted fw-semibold mb-0">Kaur Umum</p>
                    </div>
                </div>
            </div>

            <!-- LEVEL 3: PELAKSANA TEKNIS (KASI) -->
            <div class="section-divider-title">
                <span>PELAKSANA TEKNIS (SEKSI)</span>
            </div>
            <div class="row g-3 justify-content-center text-center">
                <div class="col-md-4 col-sm-6">
                    <div class="card-custom p-3 card-hover h-100">
                        <img src="https://ui-avatars.com/api/?name=Husen&background=e6f4ed&color=105e3c" class="rounded-circle avatar-kades mb-2" alt="Kasi Pemerintahan">
                        <h6 class="fw-bold text-dark mb-1">HUSEN</h6>
                        <p class="small text-muted fw-semibold mb-0">Kasi Pemerintahan</p>
                    </div>
                </div>
                <div class="col-md-4 col-sm-6">
                    <div class="card-custom p-3 card-hover h-100">
                        <img src="https://ui-avatars.com/api/?name=Hj+Nurhaedah&background=e6f4ed&color=105e3c" class="rounded-circle avatar-kades mb-2" alt="Kasi Pelayanan">
                        <h6 class="fw-bold text-dark mb-1">Hj. NURHAEDAH</h6>
                        <p class="small text-muted fw-semibold mb-0">Kasi Pelayanan</p>
                    </div>
                </div>
                <div class="col-md-4 col-sm-6">
                    <div class="card-custom p-3 card-hover h-100">
                        <img src="https://ui-avatars.com/api/?name=Rosada&background=e6f4ed&color=105e3c" class="rounded-circle avatar-kades mb-2" alt="Kasi Kesejahteraan">
                        <h6 class="fw-bold text-dark mb-1">ROSADA</h6>
                        <p class="small text-muted fw-semibold mb-0">Kasi Kesejahteraan</p>
                    </div>
                </div>
            </div>

            <!-- LEVEL 4: KEPALA WILAYAH (KAWIL / KEPALA DUSUN) -->
            <div class="section-divider-title">
                <span>PELAKSANA KEWILAYAHAN (KAWIL)</span>
            </div>
            <div class="row g-3 justify-content-center text-center">
                <div class="col-md-2 col-sm-4 col-6">
                    <div class="card-custom p-3 card-hover h-100">
                        <img src="https://ui-avatars.com/api/?name=Cecep+Herdiawan&background=f1f5f9&color=334155" class="rounded-circle avatar-kades mb-2" style="width: 65px; height: 65px;" alt="Kawil Panuusan">
                        <h6 class="fw-bold text-dark mb-1 small">CECEP HERDIAWAN</h6>
                        <p class="text-muted mb-0" style="font-size: 0.75rem;">Kawil Panuusan</p>
                    </div>
                </div>
                <div class="col-md-2 col-sm-4 col-6">
                    <div class="card-custom p-3 card-hover h-100">
                        <img src="https://ui-avatars.com/api/?name=Saeful+Rahman&background=f1f5f9&color=334155" class="rounded-circle avatar-kades mb-2" style="width: 65px; height: 65px;" alt="Kawil Neglasari">
                        <h6 class="fw-bold text-dark mb-1 small">SAEFUL RAHMAN</h6>
                        <p class="text-muted mb-0" style="font-size: 0.75rem;">Kawil Neglasari</p>
                    </div>
                </div>
                <div class="col-md-2 col-sm-4 col-6">
                    <div class="card-custom p-3 card-hover h-100">
                        <img src="https://ui-avatars.com/api/?name=Hoer+Afandi&background=f1f5f9&color=334155" class="rounded-circle avatar-kades mb-2" style="width: 65px; height: 65px;" alt="Kawil Sukahurip">
                        <h6 class="fw-bold text-dark mb-1 small">HOER AFANDI</h6>
                        <p class="text-muted mb-0" style="font-size: 0.75rem;">Kawil Sukahurip</p>
                    </div>
                </div>
                <div class="col-md-2 col-sm-4 col-6">
                    <div class="card-custom p-3 card-hover h-100">
                        <img src="https://ui-avatars.com/api/?name=Sutisman&background=f1f5f9&color=334155" class="rounded-circle avatar-kades mb-2" style="width: 65px; height: 65px;" alt="Kawil Rancaherang">
                        <h6 class="fw-bold text-dark mb-1 small">SUTISMAN</h6>
                        <p class="text-muted mb-0" style="font-size: 0.75rem;">Kawil Rancaherang</p>
                    </div>
                </div>
                <div class="col-md-2 col-sm-4 col-6">
                    <div class="card-custom p-3 card-hover h-100">
                        <img src="https://ui-avatars.com/api/?name=Toto+Heryanto&background=f1f5f9&color=334155" class="rounded-circle avatar-kades mb-2" style="width: 65px; height: 65px;" alt="Kawil Pasirmuncang">
                        <h6 class="fw-bold text-dark mb-1 small">TOTO HERYANTO</h6>
                        <p class="text-muted mb-0" style="font-size: 0.75rem;">Kawil Pasirmuncang</p>
                    </div>
                </div>
                <div class="col-md-2 col-sm-4 col-6">
                    <div class="card-custom p-3 card-hover h-100">
                        <img src="https://ui-avatars.com/api/?name=Eneng+Eli&background=f1f5f9&color=334155" class="rounded-circle avatar-kades mb-2" style="width: 65px; height: 65px;" alt="Kawil Ciroyom">
                        <h6 class="fw-bold text-dark mb-1 small">ENENG ELI</h6>
                        <p class="text-muted mb-0" style="font-size: 0.75rem;">Kawil Ciroyom</p>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- 5. LOKASI & PETA -->
    <div class="card card-custom p-4 p-md-5 mb-4">
        <div class="row align-items-center g-4">
            <div class="col-lg-5">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="p-3 rounded-4" style="background-color: #e6f4ed; color: #105e3c;">
                        <i class="fa-solid fa-map-location-dot fa-2x"></i>
                    </div>
                    <h3 class="fw-bold mb-0 text-dark">Lokasi & Peta</h3>
                </div>
                
                <p class="text-secondary small mb-4">
                    Kantor Desa Jatijaya berlokasi strategis di pusat Kecamatan Gunungtanjung, dapat dijangkau dengan mudah oleh masyarakat untuk keperluan administrasi publik.
                </p>

                <div class="d-flex align-items-start gap-2 text-secondary small mb-3">
                    <i class="fa-solid fa-location-dot mt-1" style="color: #105e3c;"></i>
                    <span>Jl. Kp. Pannusan Gng. Cikodol, Jatijaya, Kec. Gunungtanjung, Kabupaten Tasikmalaya, Jawa Barat 46418</span>
                </div>

                <div class="d-flex align-items-center gap-2 text-secondary small mb-3">
                    <i class="fa-solid fa-clock" style="color: #105e3c;"></i>
                    <span>Jam Operasional: Senin - Jumat (08:00 - 15:30 WIB)</span>
                </div>

                <div class="d-flex align-items-center gap-2 text-secondary small mb-4">
                    <i class="fa-solid fa-phone" style="color: #105e3c;"></i>
                    <span>(0265) 1234567 / 0812-3456-7890</span>
                </div>

                <a href="https://www.google.com/maps/dir/?api=1&destination=-7.4093717,108.3114768" target="_blank" class="btn btn-green text-decoration-none">
    <i class="fa-solid fa-diamond-turn-right me-1"></i> Petunjuk Arah
</a>

                    <i class="fa-solid fa-diamond-turn-right me-1"></i> Petunjuk Arah
                </a>
            </div>

            <div class="col-lg-7">
                <div id="map-jatijaya" class="rounded-4 overflow-hidden border shadow-sm" style="height: 350px; width: 100%; z-index: 1;"></div>
            </div>
        </div>
    </div>

</div>

<!-- MODAL RINCIAN 6 DUSUN DESA JATIJAYA -->
<div class="modal fade" id="modalDusun" tabindex="-1" aria-labelledby="modalDusunLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content modal-dusun-content">
            <div class="modal-header modal-dusun-header">
                <div>
                    <h5 class="modal-title fw-bold" id="modalDusunLabel">
                        <i class="fa-solid fa-layer-group me-2"></i>Daftar Kedusunan Desa Jatijaya
                    </h5>
                    <p class="small mb-0 text-white-50">Wilayah Administratif & Kepala Wilayah (Kawil)</p>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row g-3">
                    <!-- Dusun 1 -->
                    <div class="col-md-6">
                        <div class="dusun-card-item d-flex align-items-center gap-3">
                            <div class="dusun-number">1</div>
                            <div class="flex-grow-1">
                                <h6 class="fw-bold text-dark mb-1">PANUUSAN</h6>
                                <p class="small text-muted mb-0">
                                    <i class="fa-solid fa-user-tie me-1 text-success"></i> Kawil: <strong>Cecep Herdiawan</strong>
                                </p>
                            </div>
                        </div>
                    </div>
                    <!-- Dusun 2 -->
                    <div class="col-md-6">
                        <div class="dusun-card-item d-flex align-items-center gap-3">
                            <div class="dusun-number">2</div>
                            <div class="flex-grow-1">
                                <h6 class="fw-bold text-dark mb-1">NEGLASARI</h6>
                                <p class="small text-muted mb-0">
                                    <i class="fa-solid fa-user-tie me-1 text-success"></i> Kawil: <strong>Saeful Rahman</strong>
                                </p>
                            </div>
                        </div>
                    </div>
                    <!-- Dusun 3 -->
                    <div class="col-md-6">
                        <div class="dusun-card-item d-flex align-items-center gap-3">
                            <div class="dusun-number">3</div>
                            <div class="flex-grow-1">
                                <h6 class="fw-bold text-dark mb-1">SUKAHURIP</h6>
                                <p class="small text-muted mb-0">
                                    <i class="fa-solid fa-user-tie me-1 text-success"></i> Kawil: <strong>Hoer Afandi</strong>
                                </p>
                            </div>
                        </div>
                    </div>
                    <!-- Dusun 4 -->
                    <div class="col-md-6">
                        <div class="dusun-card-item d-flex align-items-center gap-3">
                            <div class="dusun-number">4</div>
                            <div class="flex-grow-1">
                                <h6 class="fw-bold text-dark mb-1">RANCAHERANG</h6>
                                <p class="small text-muted mb-0">
                                    <i class="fa-solid fa-user-tie me-1 text-success"></i> Kawil: <strong>Sutisman</strong>
                                </p>
                            </div>
                        </div>
                    </div>
                    <!-- Dusun 5 -->
                    <div class="col-md-6">
                        <div class="dusun-card-item d-flex align-items-center gap-3">
                            <div class="dusun-number">5</div>
                            <div class="flex-grow-1">
                                <h6 class="fw-bold text-dark mb-1">PASIRMUNCANG</h6>
                                <p class="small text-muted mb-0">
                                    <i class="fa-solid fa-user-tie me-1 text-success"></i> Kawil: <strong>Toto Heryanto</strong>
                                </p>
                            </div>
                        </div>
                    </div>
                    <!-- Dusun 6 -->
                    <div class="col-md-6">
                        <div class="dusun-card-item d-flex align-items-center gap-3">
                            <div class="dusun-number">6</div>
                            <div class="flex-grow-1">
                                <h6 class="fw-bold text-dark mb-1">CIROYOM</h6>
                                <p class="small text-muted mb-0">
                                    <i class="fa-solid fa-user-tie me-1 text-success"></i> Kawil: <strong>Eneng Eli</strong>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light border-0 justify-content-between">
                <span class="small text-muted"><i class="fa-solid fa-circle-info me-1"></i>Pemerintah Desa Jatijaya</span>
                <button type="button" class="btn btn-secondary rounded-pill px-4 btn-sm" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- Script Peta Leaflet -->
<script>
    document.addEventListener("DOMContentLoaded", function() {
        var lat = -7.4116;
        var lng = 108.2882;

       var map = L.map('map-jatijaya').setView([-7.4093717, 108.3114768], 18);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '© OpenStreetMap'
        }).addTo(map);

        L.marker([lat, lng]).addTo(map)
            .bindPopup('<b>Kantor Desa Jatijaya</b><br>Kec. Gunungtanjung, Kab. Tasikmalaya.')
            .openPopup();
    });
</script>

<?php 
include 'includes/footer.php'; 
?>