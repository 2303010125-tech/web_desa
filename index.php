<?php
$page_title = "Beranda";
include 'includes/header.php';
include 'config/koneksi.php';

// Query Ambil 3 Berita Terbaru
$query_berita = mysqli_query($koneksi, "SELECT * FROM berita ORDER BY id DESC LIMIT 3");
?>

<style>
    /* ------------------------------------
        HERO SECTION BACKGROUND & CENTER
    ------------------------------------ */
    .hero-hero-section {
        position: relative;
        /* Background Gambar dengan Gradient Hijau Transparan */
        background: linear-gradient(
            135deg, 
            rgba(15, 81, 50, 0.90) 0%, 
            rgba(25, 135, 84, 0.82) 100%
        ), 
        url('https://images.unsplash.com/photo-1500382017468-9049fed747ef?auto=format&fit=crop&w=1920&q=80') center/cover no-repeat;
        padding: 120px 0 100px 0;
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
        font-size: 3.2rem;
        font-weight: 800;
        line-height: 1.25;
        letter-spacing: -0.5px;
        text-shadow: 0 2px 10px rgba(0,0,0,0.2);
    }

    .hero-subtitle {
        font-size: 1.2rem;
        opacity: 0.95;
        max-width: 750px;
        margin: 0 auto;
        line-height: 1.6;
        font-weight: 400;
        text-shadow: 0 1px 5px rgba(0,0,0,0.2);
    }

    .btn-hero-primary {
        background-color: #ffffff;
        color: #146338 !important;
        font-weight: 700;
        padding: 14px 32px;
        border-radius: 12px;
        border: none;
        box-shadow: 0 8px 20px rgba(0,0,0,0.15);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .btn-hero-primary:hover {
        transform: translateY(-3px);
        box-shadow: 0 12px 25px rgba(0,0,0,0.25);
        background-color: #f8f9fa;
    }

    .btn-hero-outline {
        background-color: rgba(255, 255, 255, 0.12);
        color: #ffffff !important;
        font-weight: 700;
        padding: 14px 32px;
        border-radius: 12px;
        border: 1.5px solid rgba(255, 255, 255, 0.4);
        backdrop-filter: blur(5px);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .btn-hero-outline:hover {
        background-color: rgba(255, 255, 255, 0.25);
        border-color: #ffffff;
        transform: translateY(-3px);
    }

    /* CARD SERVICE HOVER EFFECT */
    .card-feature-hover {
        border: none;
        border-radius: 20px;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .card-feature-hover:hover {
        transform: translateY(-8px);
        box-shadow: 0 15px 30px rgba(0,0,0,0.1) !important;
    }

    @media (max-width: 768px) {
        .hero-hero-section {
            padding: 80px 0 70px 0;
            border-bottom-left-radius: 24px;
            border-bottom-right-radius: 24px;
        }
        .hero-title {
            font-size: 2.2rem;
        }
        .hero-subtitle {
            font-size: 1rem;
        }
    }
</style>

<!-- 1. HERO SECTION BANNER -->
<section class="hero-hero-section text-white mb-5">
    <div class="container text-center">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                
                <!-- Badge Atas -->
                <div class="mb-4">
                    <span class="hero-badge">
                        <i class="fa-solid fa-house-chimney text-success"></i> Portal Resmi Desa Jatijaya
                    </span>
                </div>

                <!-- Judul Utama -->
                <h1 class="hero-title mb-3">
                    Mewujudkan Desa Jatijaya Mandiri & Berbasis Digital
                </h1>

                <!-- Subtitle / Deskripsi -->
                <p class="hero-subtitle mb-4">
                    Kecamatan Gunung Tanjung, Kabupaten Tasikmalaya. Pusat pelayanan publik online dan transparansi informasi desa.
                </p>

                <!-- Tombol Aksi di Tengah -->
                <div class="d-flex flex-wrap justify-content-center gap-3 mt-4">
                    <a href="layanan.php" class="btn btn-hero-primary d-inline-flex align-items-center gap-2">
                        <i class="fa-solid fa-file-pen"></i>
                        <span>Buat Surat Online</span>
                    </a>
                    <a href="profil.php" class="btn btn-hero-outline d-inline-flex align-items-center gap-2">
                        <i class="fa-solid fa-landmark"></i>
                        <span>Profil Desa</span>
                    </a>
                </div>

            </div>
        </div>
    </div>
</section>

<!-- 2. SECTION LAYANAN CEPAT -->
<section class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-3 py-2 rounded-pill fw-bold mb-2">
                <i class="fa-solid fa-bolt me-1"></i> Layanan Publik
            </span>
            <h2 class="fw-bold text-dark">Layanan Utama Mandiri</h2>
            <p class="text-secondary small col-md-6 mx-auto">Kemudahan akses informasi dan pembuatan dokumen administrasi kependudukan untuk warga Desa Jatijaya.</p>
        </div>
        
        <div class="row g-4">
            <div class="col-md-4">
                <div class="card card-feature-hover shadow-sm p-4 text-center bg-white h-100">
                    <div class="bg-success bg-opacity-10 text-success rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 65px; height: 65px;">
                        <i class="fa-solid fa-file-lines fs-3"></i>
                    </div>
                    <h5 class="fw-bold text-dark">Pengurusan Surat</h5>
                    <p class="text-secondary small mb-4">Pengajuan permohonan surat keterangan mandiri secara gratis dan praktis dari rumah.</p>
                    <a href="layanan.php" class="btn btn-outline-success btn-sm rounded-pill fw-bold mt-auto align-self-center px-4">
                        Ajukan Surat &rarr;
                    </a>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card card-feature-hover shadow-sm p-4 text-center bg-white h-100">
                    <div class="bg-primary bg-opacity-10 text-primary rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 65px; height: 65px;">
                        <i class="fa-solid fa-magnifying-glass-chart fs-3"></i>
                    </div>
                    <h5 class="fw-bold text-dark">Cek Status Pengajuan</h5>
                    <p class="text-secondary small mb-4">Lacak progres dan status permohonan surat yang sedang diproses oleh petugas desa.</p>
                    <a href="cek_status.php" class="btn btn-outline-primary btn-sm rounded-pill fw-bold mt-auto align-self-center px-4">
                        Cek Status &rarr;
                    </a>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card card-feature-hover shadow-sm p-4 text-center bg-white h-100">
                    <div class="bg-warning bg-opacity-10 text-warning rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 65px; height: 65px;">
                        <i class="fa-solid fa-scale-balanced fs-3"></i>
                    </div>
                    <h5 class="fw-bold text-dark">Peraturan Desa</h5>
                    <p class="text-secondary small mb-4">Keterbukaan informasi hukum, SK, dan Peraturan Desa Jatijaya terbaru.</p>
                    <a href="peraturan.php" class="btn btn-outline-warning text-dark btn-sm rounded-pill fw-bold mt-auto align-self-center px-4">
                        Lihat Peraturan &rarr;
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- 3. SECTION BERITA TERKINI -->
<section class="py-5 bg-white border-top">
    <div class="container">
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
            <div>
                <h3 class="fw-bold text-dark mb-1">Berita & Pengumuman Terkini</h3>
                <p class="text-muted small mb-0">Kabar terbaru seputar program kegiatan dan pengumuman resmi Desa Jatijaya.</p>
            </div>
            <a href="berita.php" class="btn btn-outline-success btn-sm fw-bold rounded-pill px-4">
                Lihat Semua Berita &rarr;
            </a>
        </div>

        <div class="row g-4">
            <?php if ($query_berita && mysqli_num_rows($query_berita) > 0): ?>
                <?php while ($b = mysqli_fetch_assoc($query_berita)): ?>
                <?php 
                    // LOGIKA PEMANGGILAN GAMBAR BERITA (MULTI FOLDER)
                    $nama_gambar = $b['gambar'] ?? $b['foto'] ?? '';
                    
                    if (!empty($nama_gambar) && strpos($nama_gambar, 'http') === 0) {
                        $src_gambar = $nama_gambar;
                    } elseif (!empty($nama_gambar) && file_exists("uploads/berita/" . $nama_gambar)) {
                        $src_gambar = "uploads/berita/" . $nama_gambar;
                    } elseif (!empty($nama_gambar) && file_exists("uploads/" . $nama_gambar)) {
                        $src_gambar = "uploads/" . $nama_gambar;
                    } elseif (!empty($nama_gambar) && file_exists($nama_gambar)) {
                        $src_gambar = $nama_gambar;
                    } else {
                        $src_gambar = "https://images.unsplash.com/photo-1577495508048-b635879837f1?auto=format&fit=crop&w=600&q=80";
                    }

                    // Ambil Tanggal Berita
                    $tgl_berita = $b['tanggal'] ?? $b['created_at'] ?? date('Y-m-d');
                ?>
                <div class="col-md-4">
                    <div class="card card-feature-hover shadow-sm h-100 rounded-4 overflow-hidden bg-white border-0">
                        <div class="position-relative">
                            <img src="<?php echo $src_gambar; ?>" class="card-img-top" style="height: 200px; object-fit: cover;" alt="<?php echo htmlspecialchars($b['judul'] ?? 'Berita'); ?>">
                            <span class="badge bg-success position-absolute top-0 end-0 m-3 px-3 py-2 rounded-pill shadow-sm">Berita</span>
                        </div>
                        <div class="card-body p-4 d-flex flex-column">
                            <small class="text-muted mb-2">
                                <i class="fa-regular fa-calendar me-1"></i> <?php echo date('d M Y', strtotime($tgl_berita)); ?>
                            </small>
                            <h5 class="fw-bold text-dark mb-2"><?php echo htmlspecialchars($b['judul'] ?? 'Judul Berita'); ?></h5>
                            <p class="text-secondary small flex-grow-1">
                                <?php 
                                    $deskripsi = isset($b['ringkasan']) ? $b['ringkasan'] : (isset($b['isi']) ? substr(strip_tags($b['isi']), 0, 100) . '...' : '');
                                    echo htmlspecialchars($deskripsi);
                                ?>
                            </p>
                            <!-- Tombol Berhasil Diperbaiki (Mengarah ke detail_berita.php dengan variabel $b['id']) -->
                            <a href="detail_berita.php?id=<?php echo $b['id']; ?>" class="btn btn-outline-success btn-sm rounded-pill fw-bold px-3 mt-3 align-self-start">
                                Baca Berita &rarr;
                            </a>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            <?php else: ?>
                <!-- Card Contoh jika database berita kosong -->
                <div class="col-md-4">
                    <div class="card card-feature-hover shadow-sm h-100 rounded-4 overflow-hidden bg-white border-0">
                        <img src="https://images.unsplash.com/photo-1577495508048-b635879837f1?auto=format&fit=crop&w=600&q=80" class="card-img-top" style="height: 200px; object-fit: cover;" alt="Berita">
                        <div class="card-body p-4 d-flex flex-column">
                            <small class="text-muted mb-2"><i class="fa-regular fa-calendar me-1"></i> 02 Jul 2026</small>
                            <h5 class="fw-bold text-dark mb-2">Gotong Royong Warga Jatijaya Menyambut Musim Hujan</h5>
                            <p class="text-secondary small flex-grow-1">Seluruh warga Desa Jatijaya bersama-sama membersihkan lingkungan demi menjaga kebersihan desa...</p>
                            <a href="berita.php" class="text-success fw-bold text-decoration-none small mt-3">Baca Selengkapnya &rarr;</a>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php 
include 'includes/footer.php'; 
?>