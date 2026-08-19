<?php
session_start();
require_once '../config/koneksi.php';

/** @var mysqli $koneksi */

// Proteksi Sesi Admin
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit;
}

$pesan_swal = "";

// 1. PROSES TAMBAH BERITA
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah_berita'])) {
    $judul      = mysqli_real_escape_string($koneksi, $_POST['judul']);
    $penulis    = mysqli_real_escape_string($koneksi, $_POST['penulis']);
    $tanggal    = mysqli_real_escape_string($koneksi, $_POST['tanggal']);
    $isi_berita = mysqli_real_escape_string($koneksi, $_POST['isi_berita']);
    
    $ringkasan  = mysqli_real_escape_string($koneksi, substr(strip_tags($_POST['isi_berita']), 0, 150));

    $gambar = 'default.jpg';
    if (isset($_FILES['gambar']['name']) && $_FILES['gambar']['name'] != "") {
        $ext = pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION);
        $gambar = 'berita_' . time() . '.' . $ext;
        if (!is_dir('../uploads')) {
            mkdir('../uploads', 0777, true);
        }
        move_uploaded_file($_FILES['gambar']['tmp_name'], '../uploads/' . $gambar);
    }

    $query = "INSERT INTO berita (judul, ringkasan, isi_berita, penulis, tanggal, gambar) 
              VALUES ('$judul', '$ringkasan', '$isi_berita', '$penulis', '$tanggal', '$gambar')";
              
    if (mysqli_query($koneksi, $query)) {
        header("Location: berita.php?msg=add_success");
        exit;
    } else {
        header("Location: berita.php?msg=error");
        exit;
    }
}

// 2. PROSES EDIT BERITA
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_berita'])) {
    $id         = mysqli_real_escape_string($koneksi, $_POST['id_berita']);
    $judul      = mysqli_real_escape_string($koneksi, $_POST['judul']);
    $penulis    = mysqli_real_escape_string($koneksi, $_POST['penulis']);
    $tanggal    = mysqli_real_escape_string($koneksi, $_POST['tanggal']);
    $isi_berita = mysqli_real_escape_string($koneksi, $_POST['isi_berita']);
    
    $ringkasan  = mysqli_real_escape_string($koneksi, substr(strip_tags($_POST['isi_berita']), 0, 150));

    if (isset($_FILES['gambar']['name']) && $_FILES['gambar']['name'] != "") {
        $ext = pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION);
        $gambar = 'berita_' . time() . '.' . $ext;
        if (!is_dir('../uploads')) {
            mkdir('../uploads', 0777, true);
        }
        move_uploaded_file($_FILES['gambar']['tmp_name'], '../uploads/' . $gambar);
        
        $query = "UPDATE berita SET 
                  judul='$judul', ringkasan='$ringkasan', isi_berita='$isi_berita', 
                  penulis='$penulis', tanggal='$tanggal', gambar='$gambar' 
                  WHERE id='$id'";
    } else {
        $query = "UPDATE berita SET 
                  judul='$judul', ringkasan='$ringkasan', isi_berita='$isi_berita', 
                  penulis='$penulis', tanggal='$tanggal' 
                  WHERE id='$id'";
    }

    if (mysqli_query($koneksi, $query)) {
        header("Location: berita.php?msg=edit_success");
        exit;
    } else {
        header("Location: berita.php?msg=error");
        exit;
    }
}

// 3. PROSES HAPUS BERITA
if (isset($_GET['hapus'])) {
    $id = mysqli_real_escape_string($koneksi, $_GET['hapus']);
    if (mysqli_query($koneksi, "DELETE FROM berita WHERE id='$id'")) {
        header("Location: berita.php?msg=delete_success");
        exit;
    } else {
        header("Location: berita.php?msg=error");
        exit;
    }
}

// NOTIFIKASI SWEETALERT
if (isset($_GET['msg'])) {
    $msg = $_GET['msg'];
    if ($msg == 'add_success') {
        $pesan_swal = "Swal.fire({icon: 'success', title: 'Berhasil!', text: 'Berita berhasil dipublikasikan.', showConfirmButton: false, timer: 1800});";
    } elseif ($msg == 'edit_success') {
        $pesan_swal = "Swal.fire({icon: 'success', title: 'Berhasil!', text: 'Berita berhasil diperbarui.', showConfirmButton: false, timer: 1800});";
    } elseif ($msg == 'delete_success') {
        $pesan_swal = "Swal.fire({icon: 'success', title: 'Terhapus!', text: 'Berita berhasil dihapus.', showConfirmButton: false, timer: 1800});";
    } elseif ($msg == 'error') {
        $pesan_swal = "Swal.fire({icon: 'error', title: 'Gagal!', text: 'Terjadi kesalahan sistem.'});";
    }
}

// Hitung Statistik Berita
$total_berita     = mysqli_num_rows(mysqli_query($koneksi, "SELECT id FROM berita"));
$berita_bulan_ini = mysqli_num_rows(mysqli_query($koneksi, "SELECT id FROM berita WHERE MONTH(tanggal) = MONTH(CURRENT_DATE()) AND YEAR(tanggal) = YEAR(CURRENT_DATE())"));

// Query Data Berita
$query_berita = mysqli_query($koneksi, "SELECT * FROM berita ORDER BY tanggal DESC");

function tanggal_indo($tanggal) {
    if (empty($tanggal)) return '-';
    $bulan = [1 => 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agt', 'Sep', 'Okt', 'Nov', 'Des'];
    $split = explode('-', $tanggal);
    return isset($split[2], $split[1], $split[0]) ? $split[2] . ' ' . $bulan[(int)$split[1]] . ' ' . $split[0] : $tanggal;
}

$hari_ini = tanggal_indo(date('Y-m-d'));
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Berita - Admin Desa Jatijaya</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Bootstrap CSS & FontAwesome -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

    <style>
        :root {
            --primary-color: #196635;
            --primary-hover: #124d27;
            --bg-light: #f8fafc;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #f3f6f9;
            color: #2b2d42;
            min-height: 100vh;
            overflow-x: hidden;
        }

        .admin-tag {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background-color: #e8f5e9;
            border: 1px solid #c8e6c9;
            color: #1b5e20;
            padding: 6px 16px;
            border-radius: 50px;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .admin-tag .dot {
            width: 10px;
            height: 10px;
            background-color: #2e7d32;
            border-radius: 50%;
        }

        .date-widget {
            background-color: #ffffff;
            border: 1px solid #e0e0e0;
            border-radius: 12px;
            padding: 8px 18px;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            font-weight: 600;
            color: #196635;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
            font-size: 0.9rem;
        }

        /* BANNER HERO DESAIN BARU (SESUAI GAMBAR) */
        .hero-banner {
            background-color: #196635;
            border-radius: 28px;
            padding: 40px 45px;
            color: #ffffff;
            position: relative;
            overflow: hidden;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 10px 30px rgba(25, 102, 53, 0.15);
        }

        .hero-content {
            z-index: 2;
            max-width: 65%;
        }

        .hero-banner .bg-icon {
            position: absolute;
            right: -20px;
            bottom: -50px;
            font-size: 260px;
            color: rgba(255, 255, 255, 0.08);
            pointer-events: none;
            transform: rotate(-10deg);
        }

        .btn-hero {
            background-color: #ffffff;
            color: #196635;
            font-weight: 700;
            padding: 14px 32px;
            border-radius: 50px;
            text-decoration: none;
            border: none;
            white-space: nowrap;
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.12);
            transition: all 0.3s ease;
            z-index: 2;
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }

        .btn-hero:hover {
            background-color: #f8f9fa;
            color: #124d27;
            transform: translateY(-3px);
            box-shadow: 0 8px 22px rgba(0, 0, 0, 0.18);
        }

        .stat-card {
            background: #ffffff;
            border-radius: 16px;
            padding: 18px 20px;
            border: 1px solid #edf2f7;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.02);
        }

        .card-custom {
            border: none;
            border-radius: 18px;
            background: #ffffff;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.03);
        }

        .table-fixed {
            table-layout: fixed;
            width: 100%;
        }

        .table-custom thead th {
            background-color: #f8fafc;
            color: #64748b;
            font-size: 0.72rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 700;
            padding: 0.85rem 0.6rem;
            border-bottom: 1px solid #e2e8f0;
        }

        .table-custom tbody td {
            padding: 0.75rem 0.6rem;
            vertical-align: middle;
            border-bottom: 1px solid #f1f5f9;
            font-size: 0.825rem;
            word-wrap: break-word;
        }

        .img-thumb-container {
            width: 55px;
            height: 42px;
            border-radius: 8px;
            overflow: hidden;
            background-color: #f1f5f9;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid #e2e8f0;
        }

        .img-thumb-berita {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .penulis-badge {
            background-color: #f0fdf4;
            color: #166534;
            border: 1px solid #bbf7d0;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .btn-action-sm {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8rem;
            padding: 0;
        }

        .modal-modern .modal-content {
            border: none;
            border-radius: 20px;
        }

        .form-control, .form-select {
            border-radius: 10px;
            padding: 0.6rem 1rem;
            border: 1px solid #e2e8f0;
        }
    </style>
</head>

<body class="d-flex">

    <!-- Sidebar Admin -->
    <?php include 'sidebar.php'; ?>

    <!-- Konten Utama -->
    <div class="flex-grow-1 p-3 p-md-4" style="min-width: 0;">

        <!-- Top Bar -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="admin-tag">
                <span class="dot"></span>
                <span>Manajemen Berita & Informasi</span>
            </div>
            <div class="date-widget">
                <i class="fa-regular fa-calendar-days"></i>
                <span><?php echo $hari_ini; ?></span>
            </div>
        </div>

        <!-- Hero Banner Berita Desa (Disesuaikan dengan Referensi Gambar) -->
        <div class="hero-banner mb-4">
            <div class="hero-content">
                <h2 class="fw-bold mb-2 text-white fs-2 d-flex align-items-center gap-2">
                    Manajemen Berita Desa 📰
                </h2>
                <p class="mb-0 text-white-50 fs-6 leading-relaxed">
                    Kelola artikel, berita kegiatan, dan publikasi resmi informasi Desa Jatijaya secara mudah dan cepat.
                </p>
            </div>
            
            <button type="button" class="btn-hero" data-bs-toggle="modal" data-bs-target="#modalTambahBerita">
                <i class="fa-solid fa-plus"></i>
                <span>Tambah Berita Baru</span>
            </button>

            <i class="fa-solid fa-newspaper bg-icon"></i>
        </div>

        <!-- Ringkasan Statistik -->
        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <div class="stat-card d-flex align-items-center gap-3">
                    <div class="p-3 bg-success bg-opacity-10 text-success rounded-3">
                        <i class="fa-solid fa-newspaper fs-5"></i>
                    </div>
                    <div>
                        <small class="text-muted fw-semibold d-block">Total Publikasi Berita</small>
                        <h4 class="fw-bold text-dark mb-0"><?php echo $total_berita; ?></h4>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="stat-card d-flex align-items-center gap-3">
                    <div class="p-3 bg-success bg-opacity-10 text-success rounded-3">
                        <i class="fa-solid fa-calendar-check fs-5"></i>
                    </div>
                    <div>
                        <small class="text-muted fw-semibold d-block">Berita Bulan Ini</small>
                        <h4 class="fw-bold text-dark mb-0"><?php echo $berita_bulan_ini; ?></h4>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabel Berita -->
        <div class="card card-custom overflow-hidden">
            <div class="table-responsive">
                <table class="table table-custom table-fixed align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="ps-3" style="width: 6%;">NO</th>
                            <th style="width: 14%;">TANGGAL</th>
                            <th style="width: 12%;">GAMBAR</th>
                            <th style="width: 38%;">JUDUL BERITA</th>
                            <th style="width: 18%;">PENULIS</th>
                            <th class="text-center pe-3" style="width: 12%;">AKSI</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($query_berita && mysqli_num_rows($query_berita) > 0): ?>
                            <?php $no = 1; while ($b = mysqli_fetch_assoc($query_berita)): ?>
                                <?php 
                                    $sub_teks = !empty($b['ringkasan']) ? $b['ringkasan'] : $b['isi_berita'];
                                    $has_image = !empty($b['gambar']) && file_exists('../uploads/' . $b['gambar']);
                                ?>
                                <tr>
                                    <td class="ps-3 fw-bold text-secondary"><?php echo $no++; ?></td>
                                    <td>
                                        <div class="text-dark small fw-medium">
                                            <i class="fa-regular fa-calendar me-1 text-muted"></i>
                                            <?php echo tanggal_indo($b['tanggal']); ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="img-thumb-container shadow-sm">
                                            <?php if ($has_image): ?>
                                                <img src="../uploads/<?php echo htmlspecialchars($b['gambar']); ?>" alt="Gambar Berita" class="img-thumb-berita">
                                            <?php else: ?>
                                                <i class="fa-solid fa-image text-muted opacity-50"></i>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark text-truncate" title="<?php echo htmlspecialchars($b['judul']); ?>">
                                            <?php echo htmlspecialchars($b['judul']); ?>
                                        </div>
                                        <small class="text-muted text-truncate d-block" style="max-width: 320px;">
                                            <?php echo strip_tags($sub_teks); ?>
                                        </small>
                                    </td>
                                    <td>
                                        <span class="penulis-badge">
                                            <i class="fa-solid fa-user-pen"></i>
                                            <?php echo htmlspecialchars($b['penulis']); ?>
                                        </span>
                                    </td>
                                    <td class="text-center pe-3">
                                        <div class="d-flex justify-content-center align-items-center gap-1">
                                            <button type="button" class="btn btn-outline-warning btn-action-sm" data-bs-toggle="modal" data-bs-target="#modalEditBerita<?php echo $b['id']; ?>" title="Edit Berita">
                                                <i class="fa-solid fa-pen-to-square"></i>
                                            </button>
                                            <button type="button" class="btn btn-outline-danger btn-action-sm" onclick="konfirmasiHapus(<?php echo $b['id']; ?>)" title="Hapus Berita">
                                                <i class="fa-solid fa-trash-can"></i>
                                            </button>
                                        </div>

                                        <!-- MODAL EDIT BERITA -->
                                        <div class="modal fade modal-modern text-start" id="modalEditBerita<?php echo $b['id']; ?>" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog modal-lg modal-dialog-centered">
                                                <div class="modal-content">
                                                    <div class="modal-header border-0 bg-white pt-4 px-4 pb-2">
                                                        <div class="d-flex align-items-center">
                                                            <div class="bg-warning bg-opacity-10 text-warning p-3 rounded-3 me-3">
                                                                <i class="fa-solid fa-pen-to-square fs-4"></i>
                                                            </div>
                                                            <div>
                                                                <h5 class="modal-title fw-bold text-dark mb-0">Edit Berita</h5>
                                                                <p class="text-muted small mb-0">Perbarui konten artikel berita desa</p>
                                                            </div>
                                                        </div>
                                                        <button type="button" class="btn-close align-self-start" data-bs-dismiss="modal"></button>
                                                    </div>

                                                    <form action="berita.php" method="POST" enctype="multipart/form-data">
                                                        <input type="hidden" name="id_berita" value="<?php echo $b['id']; ?>">

                                                        <div class="modal-body p-4">
                                                            <div class="row g-3">
                                                                <div class="col-md-8">
                                                                    <label class="form-label fw-semibold small text-secondary">Judul Berita</label>
                                                                    <input type="text" name="judul" class="form-control" value="<?php echo htmlspecialchars($b['judul']); ?>" required>
                                                                </div>
                                                                <div class="col-md-4">
                                                                    <label class="form-label fw-semibold small text-secondary">Tanggal Publish</label>
                                                                    <input type="date" name="tanggal" class="form-control" value="<?php echo $b['tanggal']; ?>" required>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <label class="form-label fw-semibold small text-secondary">Penulis / Admin</label>
                                                                    <input type="text" name="penulis" class="form-control" value="<?php echo htmlspecialchars($b['penulis']); ?>" required>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <label class="form-label fw-semibold small text-secondary">Ganti Gambar (Opsional)</label>
                                                                    <input type="file" name="gambar" class="form-control" accept="image/*">
                                                                </div>
                                                                <div class="col-12">
                                                                    <label class="form-label fw-semibold small text-secondary">Isi Berita</label>
                                                                    <textarea name="isi_berita" class="form-control" rows="6" required><?php echo htmlspecialchars($b['isi_berita']); ?></textarea>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="modal-footer border-0 bg-light px-4 py-3">
                                                            <button type="button" class="btn btn-white text-secondary fw-semibold rounded-3 px-4 border" data-bs-dismiss="modal">Batal</button>
                                                            <button type="submit" name="edit_berita" class="btn btn-warning text-dark fw-bold rounded-3 px-4 py-2 shadow-sm">
                                                                <i class="fa-solid fa-floppy-disk me-1"></i> Simpan Perubahan
                                                            </button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>

                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted py-5">
                                    <i class="fa-regular fa-newspaper fs-2 d-block mb-2 opacity-50"></i>
                                    <span>Belum ada data berita yang dipublikasikan.</span>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- MODAL TAMBAH BERITA -->
    <div class="modal fade modal-modern" id="modalTambahBerita" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0 bg-white pt-4 px-4 pb-2">
                    <div class="d-flex align-items-center">
                        <div class="bg-success bg-opacity-10 text-success p-3 rounded-3 me-3">
                            <i class="fa-solid fa-newspaper fs-4"></i>
                        </div>
                        <div>
                            <h5 class="modal-title fw-bold text-dark mb-0">Tambah Berita Baru</h5>
                            <p class="text-muted small mb-0">Buat postingan pengumuman atau artikel informasi desa.</p>
                        </div>
                    </div>
                    <button type="button" class="btn-close align-self-start" data-bs-dismiss="modal"></button>
                </div>

                <form action="berita.php" method="POST" enctype="multipart/form-data">
                    <div class="modal-body p-4">
                        <div class="row g-3">
                            <div class="col-md-8">
                                <label class="form-label fw-semibold small text-secondary">Judul Berita</label>
                                <input type="text" name="judul" class="form-control" placeholder="Masukkan judul artikel berita..." required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold small text-secondary">Tanggal Publish</label>
                                <input type="date" name="tanggal" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small text-secondary">Penulis / Admin</label>
                                <input type="text" name="penulis" class="form-control" value="Admin Jatijaya" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small text-secondary">Upload Gambar Sampul</label>
                                <input type="file" name="gambar" class="form-control" accept="image/*">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold small text-secondary">Isi Berita</label>
                                <textarea name="isi_berita" class="form-control" rows="6" placeholder="Tuliskan berita lengkap di sini..." required></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer border-0 bg-light px-4 py-3">
                        <button type="button" class="btn btn-white text-secondary fw-semibold rounded-3 px-4 border" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" name="tambah_berita" class="btn text-white fw-bold rounded-3 px-4 py-2 shadow-sm" style="background-color: var(--primary-color);">
                            <i class="fa-solid fa-paper-plane me-1"></i> Publikasikan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        <?php echo $pesan_swal; ?>

        function konfirmasiHapus(id) {
            Swal.fire({
                title: 'Apakah Anda Yakin?',
                text: "Berita ini akan dihapus secara permanen!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal',
                customClass: { popup: 'rounded-4' }
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'berita.php?hapus=' + id;
                }
            });
        }
    </script>
</body>
</html>