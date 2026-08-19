<?php
session_start();
include '../config/koneksi.php';

/** @var mysqli $koneksi */

// Proses Tambah Data Berita Baru
if (isset($_POST['submit_tambah'])) {
    $judul     = mysqli_real_escape_string($koneksi, $_POST['judul']);
    $ringkasan = mysqli_real_escape_string($koneksi, $_POST['ringkasan']);
    $isi       = mysqli_real_escape_string($koneksi, $_POST['isi_berita']);
    $penulis   = mysqli_real_escape_string($koneksi, $_POST['penulis']);
    $tanggal   = date('Y-m-d');
    $gambar    = mysqli_real_escape_string($koneksi, $_POST['gambar']); // URL Gambar / path

    $query_insert = "INSERT INTO berita (judul, ringkasan, isi_berita, gambar, penulis, tanggal) 
                     VALUES ('$judul', '$ringkasan', '$isi', '$gambar', '$penulis', '$tanggal')";
    
    if (mysqli_query($koneksi, $query_insert)) {
        header("Location: kelola_berita.php?status=success");
        exit();
    }
}

// Query Ambil Semua Berita
$query_admin = mysqli_query($koneksi, "SELECT * FROM berita ORDER BY id DESC");
$total_berita = mysqli_num_rows($query_admin);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Berita & Pengumuman - Admin Desa Jatijaya</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    
    <style>
        body { 
            background-color: #f4f6f9; 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
        }

        /* SIDEBAR ADMIN STYLING */
        .sidebar {
            background-color: #111827;
            min-height: 100vh;
            color: #ffffff;
        }

        .sidebar .nav-link {
            color: #9ca3af;
            font-weight: 500;
            border-radius: 10px;
            margin-bottom: 6px;
            padding: 12px 16px;
            transition: all 0.2s ease;
        }

        .sidebar .nav-link:hover, 
        .sidebar .nav-link.active {
            background-color: #1f2937;
            color: #34d399;
        }

        /* HEADER BANNER UTAMA (SESUAI GAMBAR TEMPLATE) */
        .admin-page-header {
            background-color: #126841; /* Warna Forest Green */
            color: #ffffff;
            border-radius: 20px;
            padding: 28px 32px;
            position: relative;
            overflow: hidden;
            box-shadow: 0 8px 20px rgba(18, 104, 65, 0.15);
            margin-bottom: 28px;
        }

        .admin-page-header .header-content {
            position: relative;
            z-index: 2;
        }

        .admin-page-header h2 {
            font-size: 1.85rem;
            font-weight: 800;
            margin-bottom: 6px;
            letter-spacing: -0.3px;
            display: flex;
            align-items: center;
        }

        .admin-page-header p {
            font-size: 0.95rem;
            color: rgba(255, 255, 255, 0.85);
            margin-bottom: 0;
        }

        /* Watermark Ikon Transparan di Sisi Kanan Header */
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

        /* CARD & TABLE CUSTOM */
        .card-custom {
            border: none;
            border-radius: 18px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.03);
        }

        .table > :not(caption) > * > * {
            padding: 14px 16px;
        }
    </style>
</head>
<body>

<div class="container-fluid p-0">
    <div class="row g-0">
        
        <!-- SIDEBAR KIRI -->
        <div class="col-md-2 sidebar p-3 d-none d-md-block">
            <div class="d-flex align-items-center mb-4 px-2 pt-2">
                <i class="fa-solid fa-shield-halved text-success fs-3 me-2"></i>
                <h5 class="fw-bold mb-0 text-white">Admin Desa</h5>
            </div>
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link" href="dashboard.php">
                        <i class="fa-solid fa-chart-line me-2"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="kelola_berita.php">
                        <i class="fa-solid fa-newspaper me-2"></i> Berita & Informasi
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="pengajuan_surat.php">
                        <i class="fa-solid fa-file-signature me-2"></i> Permohonan Surat
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="pengaturan.php">
                        <i class="fa-solid fa-sliders me-2"></i> Pengaturan
                    </a>
                </li>
                <li class="nav-item mt-4">
                    <a class="nav-link text-danger" href="logout.php">
                        <i class="fa-solid fa-right-from-bracket me-2"></i> Logout
                    </a>
                </li>
            </ul>
        </div>

        <!-- AREA KONTEN UTAMA (KANAN) -->
        <div class="col-md-10 p-4">

            <!-- BANNER HEADER SESUAI GAMBAR REFERENSI -->
            <div class="admin-page-header">
                <div class="header-content">
                    <h2>
                        <i class="fa-solid fa-newspaper me-3"></i>
                        Manajemen Berita & Pengumuman
                    </h2>
                    <p>Kelola, publikasikan, dan perbarui berita resmi serta pengumuman penting warga desa.</p>
                </div>
                <div class="header-watermark">
                    <i class="fa-solid fa-bullhorn"></i>
                </div>
            </div>

            <!-- RINGKASAN STATISTIK -->
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <div class="card card-custom p-3 bg-white border-start border-4 border-success">
                        <small class="text-muted fw-bold">TOTAL POSTINGAN BERITA</small>
                        <h3 class="fw-bold text-dark mt-1 mb-0"><?php echo $total_berita; ?></h3>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card card-custom p-3 bg-white border-start border-4 border-primary">
                        <small class="text-muted fw-bold">STATUS PUBLIKASI</small>
                        <h3 class="fw-bold text-dark mt-1 mb-0">Aktif Publik</h3>
                    </div>
                </div>
            </div>

            <!-- CARD TABEL KELOLA BERITA -->
            <div class="card card-custom bg-white p-4">
                <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
                    <h5 class="fw-bold text-dark mb-0">
                        <i class="fa-solid fa-list me-2 text-success"></i>Daftar Postingan
                    </h5>
                    <button class="btn btn-success fw-bold rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#modalTambah">
                        <i class="fa-solid fa-plus me-1"></i> Buat Informasi Baru
                    </button>
                </div>

                <!-- NOTIFIKASI SUKSES -->
                <?php if (isset($_GET['status']) && $_GET['status'] == 'success'): ?>
                    <div class="alert alert-success alert-dismissible fade show rounded-3 mb-4" role="alert">
                        <i class="fa-solid fa-circle-check me-1"></i> Data berita berhasil ditambahkan!
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <!-- TABEL DATA -->
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>No</th>
                                <th>Gambar</th>
                                <th>Judul Berita</th>
                                <th>Tanggal</th>
                                <th>Penulis</th>
                                <th class="text-center">Aksi Management</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($total_berita > 0): ?>
                                <?php $no = 1; while ($row = mysqli_fetch_assoc($query_admin)): ?>
                                <tr>
                                    <td><?php echo $no++; ?></td>
                                    <td>
                                        <img src="<?php echo htmlspecialchars($row['gambar']); ?>" class="rounded-3" style="width: 60px; height: 40px; object-fit: cover;" alt="Gbr">
                                    </td>
                                    <td class="fw-bold text-dark"><?php echo htmlspecialchars($row['judul']); ?></td>
                                    <td><small class="text-muted"><?php echo date('d M Y', strtotime($row['tanggal'])); ?></small></td>
                                    <td><span class="badge bg-secondary"><?php echo htmlspecialchars($row['penulis'] ?? 'Admin'); ?></span></td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-outline-warning me-1" title="Edit Data"><i class="fa-solid fa-pen-to-square"></i></button>
                                        <button class="btn btn-sm btn-outline-danger" title="Hapus Data"><i class="fa-solid fa-trash"></i></button>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">Belum ada data berita atau pengumuman.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- MODAL TAMBAH BERITA BARU -->
<div class="modal fade" id="modalTambah" tabindex="-1" aria-labelledby="modalTambahLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content rounded-4 border-0">
            <div class="modal-header bg-success text-white rounded-top-4">
                <h5 class="modal-header-title fw-bold mb-0" id="modalTambahLabel">
                    <i class="fa-solid fa-newspaper me-2"></i>Tambah Berita / Pengumuman Baru
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="" method="POST">
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label fw-bold small">Judul Berita / Informasi</label>
                            <input type="text" name="judul" class="form-control rounded-3" placeholder="Masukkan judul..." required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold small">Penulis / Admin</label>
                            <input type="text" name="penulis" class="form-control rounded-3" value="Admin Desa" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold small">URL Gambar Utama / Foto Header</label>
                            <input type="text" name="gambar" class="form-control rounded-3" placeholder="https://..." required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold small">Ringkasan Singkat</label>
                            <input type="text" name="ringkasan" class="form-control rounded-3" placeholder="Ringkasan 1-2 kalimat untuk preview..." required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold small">Isi Lengkap Berita</label>
                            <textarea name="isi_berita" class="form-control rounded-3" rows="5" placeholder="Tuliskan isi berita selengkapnya..." required></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top-0 px-4 pb-4">
                    <button type="button" class="btn btn-light rounded-3 fw-bold" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="submit_tambah" class="btn btn-success rounded-3 fw-bold px-4">Simpan & Publikasikan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Bootstrap 5 JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>