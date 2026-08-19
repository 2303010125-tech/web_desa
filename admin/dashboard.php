<?php
session_start();
require_once '../config/koneksi.php';

/** @var mysqli $koneksi */

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit;
}

// Set Lokalisasi Tanggal ke Bahasa Indonesia
setlocale(LC_TIME, 'id_ID.utf8', 'id_ID', 'id');

// Query Statistik
$t_surat = 0;
$q_surat = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM pengajuan_surat");
if ($q_surat && $row_surat = mysqli_fetch_assoc($q_surat)) {
    $t_surat = $row_surat['total'];
}

$t_berita = 0;
$q_berita = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM berita");
if ($q_berita && $row_berita = mysqli_fetch_assoc($q_berita)) {
    $t_berita = $row_berita['total'];
}

$t_pending = 0;
$q_pending = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM pengajuan_surat WHERE status='pending'");
if ($q_pending && $row_pending = mysqli_fetch_assoc($q_pending)) {
    $t_pending = $row_pending['total'];
}

// Array Bulan Indonesia Manual (Backup jika setlocale server mati)
$bulan_indo = [
    1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
    'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
];
$tgl_sekarang = date('d') . ' ' . $bulan_indo[(int)date('m')] . ' ' . date('Y');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Desa Jatijaya</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    
    <style>
        body { 
            background-color: #f4f6f9; 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
        }

        /* HEADER BANNER HIJAU */
        .admin-page-header {
            background-color: #126841;
            color: #ffffff;
            border-radius: 20px;
            padding: 24px 32px;
            position: relative;
            overflow: hidden;
            box-shadow: 0 8px 20px rgba(18, 104, 65, 0.15);
            margin-bottom: 24px;
        }

        .admin-page-header .header-content {
            position: relative;
            z-index: 2;
        }

        .admin-page-header h2 {
            font-size: 1.8rem;
            font-weight: 800;
            margin-bottom: 4px;
        }

        .admin-page-header p {
            font-size: 0.95rem;
            color: rgba(255, 255, 255, 0.85);
            margin-bottom: 0;
        }

        .admin-page-header .header-watermark {
            position: absolute;
            right: 32px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 4.5rem;
            color: rgba(255, 255, 255, 0.15);
            z-index: 1;
            pointer-events: none;
        }

        /* CARD STATISTIK */
        .card-stat, .card-modern {
            border: none;
            border-radius: 16px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.03);
            background-color: #ffffff;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .card-stat:hover, .btn-quick-access:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.06);
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
        }

        /* Animasi Melambai Emoji */
        .wave-emoji {
            display: inline-block;
            animation: wave-animation 2.5s infinite;
            transform-origin: 70% 70%;
        }

        @keyframes wave-animation {
            0% { transform: rotate(0.0deg) }
            10% { transform: rotate(14.0deg) }
            20% { transform: rotate(-8.0deg) }
            30% { transform: rotate(14.0deg) }
            40% { transform: rotate(-4.0deg) }
            50% { transform: rotate(10.0deg) }
            60% { transform: rotate(0.0deg) }
            100% { transform: rotate(0.0deg) }
        }
    </style>
</head>
<body class="d-flex">

    <?php include 'sidebar.php'; ?>

    <div class="flex-grow-1 p-4 overflow-auto" style="min-height: 100vh;">
        
        <!-- BARIS ATAS: WAKTU & TANGGAL -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 rounded-pill fw-semibold">
                    <i class="fa-solid fa-circle text-success fs-6 me-1"></i> Dashboard Admin
                </span>
            </div>
            <!-- Tanggal Bahasa Indonesia -->
            <div class="bg-white p-2 px-3 rounded-3 border d-flex align-items-center gap-2 shadow-sm">
                <i class="fa-regular fa-calendar text-success"></i>
                <span class="small fw-semibold text-dark"><?php echo $tgl_sekarang; ?></span>
            </div>
        </div>

        <!-- BANNER HIJAU UTAMA (GABUNGAN WELCOME + BANNER) -->
        <div class="admin-page-header">
            <div class="header-content">
                <h2>Selamat Datang, Admin <span class="wave-emoji">👋</span></h2>
                <p>Kelola identitas resmi, pantau permohonan surat warga, dan atur pembaruan berita portal Desa Jatijaya.</p>
            </div>
            <div class="header-watermark">
                <i class="fa-solid fa-landmark"></i>
            </div>
        </div>

        <!-- KARTU STATISTIK RINGKAS -->
        <div class="row g-3 mb-4">
            <div class="col-12 col-sm-6 col-md-4">
                <div class="card card-stat p-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="stat-icon bg-warning-subtle text-warning">
                            <i class="fa-solid fa-clock-rotate-left"></i>
                        </div>
                        <div>
                            <span class="text-secondary small fw-semibold d-block">Surat Menunggu</span>
                            <h4 class="fw-bold mb-0 text-dark"><?php echo $t_pending; ?> <span class="fs-6 text-muted fw-normal">Berkas</span></h4>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-md-4">
                <div class="card card-stat p-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="stat-icon bg-primary-subtle text-primary">
                            <i class="fa-solid fa-envelope-open-text"></i>
                        </div>
                        <div>
                            <span class="text-secondary small fw-semibold d-block">Total Pengajuan Surat</span>
                            <h4 class="fw-bold mb-0 text-dark"><?php echo $t_surat; ?> <span class="fs-6 text-muted fw-normal">Pengajuan</span></h4>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-md-4">
                <div class="card card-stat p-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="stat-icon bg-success-subtle text-success">
                            <i class="fa-solid fa-newspaper"></i>
                        </div>
                        <div>
                            <span class="text-secondary small fw-semibold d-block">Artikel & Berita</span>
                            <h4 class="fw-bold mb-0 text-dark"><?php echo $t_berita; ?> <span class="fs-6 text-muted fw-normal">Diterbitkan</span></h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- AKSES PINTAR -->
        <div class="card card-modern p-4 mb-4">
            <h5 class="fw-bold text-dark mb-3"><i class="fa-solid fa-bolt text-warning me-2"></i>Akses Pintar</h5>
            <div class="row g-3">
                <div class="col-6 col-md-3">
                    <a href="layanan.php" class="btn btn-light btn-quick-access w-100 p-3 text-start border rounded-3 text-decoration-none">
                        <i class="fa-solid fa-file-signature text-primary fs-4 d-block mb-2"></i>
                        <span class="fw-bold text-dark d-block">Proses Surat</span>
                        <small class="text-muted">Cek & acc pengajuan</small>
                    </a>
                </div>
                <div class="col-6 col-md-3">
                    <a href="berita.php" class="btn btn-light btn-quick-access w-100 p-3 text-start border rounded-3 text-decoration-none">
                        <i class="fa-solid fa-pen-to-square text-success fs-4 d-block mb-2"></i>
                        <span class="fw-bold text-dark d-block">Tulis Berita</span>
                        <small class="text-muted">Publikasi kabar terbaru</small>
                    </a>
                </div>
                <div class="col-6 col-md-3">
                    <a href="pengumuman.php" class="btn btn-light btn-quick-access w-100 p-3 text-start border rounded-3 text-decoration-none">
                        <i class="fa-solid fa-bullhorn text-warning fs-4 d-block mb-2"></i>
                        <span class="fw-bold text-dark d-block">Pengumuman</span>
                        <small class="text-muted">Informasi penting warga</small>
                    </a>
                </div>
                <div class="col-6 col-md-3">
                    <a href="pengaturan.php" class="btn btn-light btn-quick-access w-100 p-3 text-start border rounded-3 text-decoration-none">
                        <i class="fa-solid fa-sliders text-danger fs-4 d-block mb-2"></i>
                        <span class="fw-bold text-dark d-block">Pengaturan</span>
                        <small class="text-muted">Sistem & profil desa</small>
                    </a>
                </div>
            </div>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>