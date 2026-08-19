<?php
$page_title = "Pengumuman Desa";
include 'includes/header.php';
include 'config/koneksi.php';

// Fitur Pencarian
$keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';

if (!empty($keyword)) {
    $keyword_escaped = mysqli_real_escape_string($koneksi, $keyword);
    
    // Cek kolom yang tersedia di tabel pengumuman
    $check_columns = mysqli_query($koneksi, "SHOW COLUMNS FROM pengumuman");
    $columns = [];
    if ($check_columns) {
        while ($col = mysqli_fetch_assoc($check_columns)) {
            $columns[] = $col['Field'];
        }
    }

    // Susun filter dinamis
    $conditions = [];
    if (in_array('judul', $columns)) {
        $conditions[] = "LOWER(judul) LIKE LOWER('%$keyword_escaped%')";
    }
    if (in_array('isi', $columns)) {
        $conditions[] = "LOWER(isi) LIKE LOWER('%$keyword_escaped%')";
    }
    if (in_array('kategori', $columns)) {
        $conditions[] = "LOWER(kategori) LIKE LOWER('%$keyword_escaped%')";
    }

    $where_clause = !empty($conditions) ? "WHERE " . implode(" OR ", $conditions) : "";
    $order_by = in_array('tanggal', $columns) ? "tanggal" : "id";
    $query_pengumuman = mysqli_query($koneksi, "SELECT * FROM pengumuman $where_clause ORDER BY $order_by DESC");
} else {
    $query_pengumuman = mysqli_query($koneksi, "SELECT * FROM pengumuman ORDER BY id DESC");
}
?>

<style>
    /* HERO BANNER PENGUMUMAN */
    .hero-announcement-section {
        position: relative;
        background: linear-gradient(
            135deg, 
            rgba(15, 81, 50, 0.90) 0%, 
            rgba(25, 135, 84, 0.82) 100%
        ), 
        url('https://images.unsplash.com/photo-1500382017468-9049fed747ef?auto=format&fit=crop&w=1920&q=80') center/cover no-repeat;
        padding: 100px 0 80px 0;
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
        font-size: 3rem;
        font-weight: 800;
        line-height: 1.25;
        letter-spacing: -0.5px;
        text-shadow: 0 2px 10px rgba(0,0,0,0.2);
    }

    .hero-subtitle {
        font-size: 1.15rem;
        opacity: 0.95;
        max-width: 750px;
        margin: 0 auto;
        line-height: 1.6;
        font-weight: 400;
        text-shadow: 0 1px 5px rgba(0,0,0,0.2);
    }

    /* CARD HOVER EFFECT */
    .card-announcement {
        border: none;
        border-radius: 20px;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        background: #ffffff;
    }

    .card-announcement:hover {
        transform: translateY(-8px);
        box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1) !important;
    }

    .search-box-container {
        margin-top: -30px;
        position: relative;
        z-index: 10;
    }

    @media (max-width: 768px) {
        .hero-announcement-section {
            padding: 70px 0 60px 0;
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

<!-- 1. HERO SECTION -->
<section class="hero-announcement-section text-white mb-4">
    <div class="container text-center">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="mb-3">
                    <span class="hero-badge">
                        <i class="fa-solid fa-bullhorn text-success"></i> Informasi Resmi
                    </span>
                </div>
                <h1 class="hero-title mb-3">Pengumuman Desa Jatijaya</h1>
                <p class="hero-subtitle">
                    Kecamatan Gunung Tanjung, Kabupaten Tasikmalaya. Pusat pemberitahuan resmi, jadwal kegiatan, dan edaran penting pemerintah desa.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- 2. SEARCH BAR & MAIN CONTENT SECTION -->
<div class="container py-3">
    
    <!-- Form Pencarian Floating -->
    <div class="row justify-content-center mb-5 search-box-container">
        <div class="col-lg-8">
            <div class="card border-0 shadow-lg rounded-4 p-2 bg-white">
                <form action="pengumuman.php" method="GET" class="d-flex align-items-center gap-2">
                    <div class="input-group border-0">
                        <span class="input-group-text bg-transparent border-0 text-muted ps-3">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </span>
                        <input type="text" name="keyword" class="form-control border-0 shadow-none" placeholder="Cari pengumuman atau kata kunci..." value="<?php echo htmlspecialchars($keyword); ?>">
                    </div>
                    <button type="submit" class="btn btn-success fw-bold px-4 py-2 rounded-3">Cari</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Grid Daftar Pengumuman -->
    <div class="row g-4 mb-5">
        <?php if ($query_pengumuman && mysqli_num_rows($query_pengumuman) > 0): ?>
            <?php while ($p = mysqli_fetch_assoc($query_pengumuman)): ?>
            <?php 
                // Gambar Fallback
                $nama_gambar = $p['gambar'] ?? $p['foto'] ?? '';
                
                if (!empty($nama_gambar) && strpos($nama_gambar, 'http') === 0) {
                    $src_gambar = $nama_gambar;
                } elseif (!empty($nama_gambar) && file_exists("uploads/pengumuman/" . $nama_gambar)) {
                    $src_gambar = "uploads/pengumuman/" . $nama_gambar;
                } elseif (!empty($nama_gambar) && file_exists("uploads/" . $nama_gambar)) {
                    $src_gambar = "uploads/" . $nama_gambar;
                } else {
                    $src_gambar = "https://images.unsplash.com/photo-1577495508048-b635879837f1?auto=format&fit=crop&w=600&q=80";
                }

                // Kategori Badge
                $kategori = $p['kategori'] ?? 'Informasi';
                $badge_class = 'bg-success';
                if (strtolower($kategori) == 'penting' || strtolower($kategori) == 'mendesak') {
                    $badge_class = 'bg-danger';
                } elseif (strtolower($kategori) == 'himbauan' || strtolower($kategori) == 'kesehatan') {
                    $badge_class = 'bg-warning text-dark';
                }

                // Tanggal
                $tgl = $p['tanggal'] ?? $p['created_at'] ?? date('Y-m-d');
            ?>
            <div class="col-md-4">
                <div class="card card-announcement shadow-sm h-100 rounded-4 overflow-hidden d-flex flex-column">
                    <div class="position-relative">
                        <img src="<?php echo $src_gambar; ?>" class="card-img-top" style="height: 190px; object-fit: cover;" alt="<?php echo htmlspecialchars($p['judul'] ?? ''); ?>">
                        <span class="badge <?php echo $badge_class; ?> position-absolute top-0 end-0 m-3 px-3 py-2 rounded-pill shadow-sm fw-bold">
                            <i class="fa-solid fa-circle-exclamation me-1"></i> <?php echo htmlspecialchars($kategori); ?>
                        </span>
                    </div>
                    <div class="card-body p-4 d-flex flex-column flex-grow-1">
                        <div class="d-flex align-items-center text-muted small mb-2 gap-2">
                            <span><i class="fa-regular fa-calendar me-1 text-success"></i> <?php echo date('d M Y', strtotime($tgl)); ?></span>
                        </div>
                        <h5 class="fw-bold text-dark mb-3"><?php echo htmlspecialchars($p['judul'] ?? ''); ?></h5>
                        <p class="text-secondary small mb-4 flex-grow-1">
                            <?php 
                                $isi = isset($p['isi']) ? strip_tags($p['isi']) : '';
                                echo htmlspecialchars(substr($isi, 0, 110)) . '...';
                            ?>
                        </p>
                        <div class="pt-3 border-top mt-auto d-flex align-items-center justify-content-between">
                            <a href="detail_pengumuman.php?id=<?php echo urlencode($p['id']); ?>" class="btn btn-outline-success btn-sm rounded-pill fw-bold px-3">
                                Baca Pengumuman &rarr;
                            </a>
                            <?php if(!empty($p['file_lampiran'])): ?>
                                <a href="uploads/lampiran/<?php echo htmlspecialchars($p['file_lampiran']); ?>" target="_blank" class="btn btn-sm btn-light text-secondary rounded-circle p-2" title="Unduh Lampiran">
                                    <i class="fa-solid fa-paperclip"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        <?php else: ?>
            <!-- Pesan Jika Pengumuman Tidak Ditemukan / Kosong -->
            <div class="col-12 text-center py-5">
                <div class="bg-light d-inline-block p-4 rounded-circle mb-3">
                    <i class="fa-solid fa-bullhorn fs-1 text-muted"></i>
                </div>
                <h5 class="fw-bold text-dark">Pengumuman Tidak Ditemukan</h5>
                <p class="text-muted small">Kata kunci <strong>"<?php echo htmlspecialchars($keyword); ?>"</strong> tidak cocok dengan pengumuman mana pun.</p>
                <a href="pengumuman.php" class="btn btn-outline-success btn-sm rounded-pill mt-2">Tampilkan Semua Pengumuman</a>
            </div>
        <?php endif; ?>
    </div>

</div>

<?php 
include 'includes/footer.php'; 
?>