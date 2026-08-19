<?php
$page_title = "Berita & Pengumuman Desa";
include 'includes/header.php';
include 'config/koneksi.php';

// Fitur Pencarian
$keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';

if (!empty($keyword)) {
    $keyword_escaped = mysqli_real_escape_string($koneksi, $keyword);
    
    // Pengecekan kolom tabel berita secara dinamis
    $check_columns = mysqli_query($koneksi, "SHOW COLUMNS FROM berita");
    $columns = [];
    if ($check_columns) {
        while ($col = mysqli_fetch_assoc($check_columns)) {
            $columns[] = $col['Field'];
        }
    }

    // Membangun kondisi pencarian berdasarkan kolom yang tersedia
    $conditions = [];
    if (in_array('judul', $columns)) {
        $conditions[] = "LOWER(judul) LIKE LOWER('%$keyword_escaped%')";
    }
    if (in_array('isi', $columns)) {
        $conditions[] = "LOWER(isi) LIKE LOWER('%$keyword_escaped%')";
    }
    if (in_array('konten', $columns)) {
        $conditions[] = "LOWER(konten) LIKE LOWER('%$keyword_escaped%')";
    }
    if (in_array('ringkasan', $columns)) {
        $conditions[] = "LOWER(ringkasan) LIKE LOWER('%$keyword_escaped%')";
    }
    if (in_array('kategori', $columns)) {
        $conditions[] = "LOWER(kategori) LIKE LOWER('%$keyword_escaped%')";
    }

    $where_clause = !empty($conditions) ? "WHERE " . implode(" OR ", $conditions) : "";
    $order_by = in_array('tanggal', $columns) ? "tanggal" : "id";
    $query_berita = mysqli_query($koneksi, "SELECT * FROM berita $where_clause ORDER BY $order_by DESC");
} else {
    $query_berita = mysqli_query($koneksi, "SELECT * FROM berita ORDER BY id DESC");
}
?>

<style>
    /* ------------------------------------
        HERO BANNER BERITA
    ------------------------------------ */
    .hero-news-section {
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
    .card-news-hover {
        border: none;
        border-radius: 20px;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .card-news-hover:hover {
        transform: translateY(-8px);
        box-shadow: 0 15px 30px rgba(0,0,0,0.1) !important;
    }

    @media (max-width: 768px) {
        .hero-news-section {
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

<!-- 1. HERO SECTION BANNER BERITA -->
<section class="hero-news-section text-white mb-5">
    <div class="container text-center">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                
                <!-- Badge Atas -->
                <div class="mb-3">
                    <span class="hero-badge">
                        <i class="fa-solid fa-newspaper text-success"></i> Informasi Terkini
                    </span>
                </div>

                <!-- Judul Utama -->
                <h1 class="hero-title mb-3">
                    Berita & Pengumuman Desa
                </h1>

                <!-- Subtitle / Deskripsi -->
                <p class="hero-subtitle">
                    Kecamatan Gunung Tanjung, Kabupaten Tasikmalaya. Pusat kabar berita kegiatan dan pengumuman resmi bagi warga.
                </p>

            </div>
        </div>
    </div>
</section>

<!-- 2. SEARCH BAR & CONTENT SECTION -->
<div class="container py-3">
    
    <!-- Form Pencarian -->
    <div class="row justify-content-center mb-5">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 p-2 bg-white">
                <form action="berita.php" method="GET" class="d-flex align-items-center gap-2">
                    <div class="input-group border-0">
                        <span class="input-group-text bg-transparent border-0 text-muted ps-3">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </span>
                        <input type="text" name="keyword" class="form-control border-0 shadow-none" placeholder="Cari berita atau informasi desa..." value="<?php echo htmlspecialchars($keyword); ?>">
                    </div>
                    <button type="submit" class="btn btn-success fw-bold px-4 py-2 rounded-3">Cari</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Grid Daftar Berita -->
    <div class="row g-4 mb-5">
        <?php if ($query_berita && mysqli_num_rows($query_berita) > 0): ?>
            <?php while ($b = mysqli_fetch_assoc($query_berita)): ?>
            <?php 
                // Logika Auto-Fallback Gambar
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
                <div class="card card-news-hover shadow-sm h-100 rounded-4 overflow-hidden bg-white border-0">
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
                                $teks_isi = $b['ringkasan'] ?? $b['isi'] ?? $b['konten'] ?? '';
                                $deskripsi = !empty($teks_isi) ? substr(strip_tags($teks_isi), 0, 110) . '...' : '';
                                echo htmlspecialchars($deskripsi);
                            ?>
                        </p>
                        <a href="detail_berita.php?id=<?php echo $b['id']; ?>" class="btn btn-outline-success btn-sm rounded-pill fw-bold px-3 mt-3 align-self-start">
                            Baca Berita &rarr;
                        </a>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        <?php else: ?>
            <!-- Jika pencarian tidak ditemukan -->
            <div class="col-12 text-center py-5">
                <div class="bg-light d-inline-block p-4 rounded-circle mb-3">
                    <i class="fa-solid fa-newspaper fs-1 text-muted"></i>
                </div>
                <h5 class="fw-bold text-dark">Belum Ada Berita</h5>
                <p class="text-muted small">Berita yang Anda cari tidak ditemukan atau belum ada postingan terbaru.</p>
                <?php if(!empty($keyword)): ?>
                    <a href="berita.php" class="btn btn-outline-success btn-sm rounded-pill mt-2">Tampilkan Semua Berita</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

</div>

<?php 
include 'includes/footer.php'; 
?>