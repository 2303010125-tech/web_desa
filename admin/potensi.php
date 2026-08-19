<?php
session_start();

// Koneksi ke database
if (file_exists('../config/koneksi.php')) {
    include '../config/koneksi.php';
} else {
    include 'config/koneksi.php';
}

/** @var mysqli $koneksi */

// Proteksi Sesi Admin
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

$pesan_swal = "";

// -------------------------------------------------------------
// 1. PROSES TAMBAH POTENSI / UMKM
// -------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah_potensi'])) {
    $nama      = mysqli_real_escape_string($koneksi, trim($_POST['nama']));
    $kategori  = mysqli_real_escape_string($koneksi, trim($_POST['kategori']));
    $deskripsi = mysqli_real_escape_string($koneksi, trim($_POST['deskripsi']));

    $nama_file   = $_FILES['foto']['name'];
    $ukuran_file = $_FILES['foto']['size'];
    $tmp_name    = $_FILES['foto']['tmp_name'];
    $error_img   = $_FILES['foto']['error'];

    if ($error_img === 0) {
        $ekstensi = strtolower(pathinfo($nama_file, PATHINFO_EXTENSION));
        $ekstensi_diizinkan = array('jpg', 'jpeg', 'png', 'webp');

        if (in_array($ekstensi, $ekstensi_diizinkan)) {
            if ($ukuran_file <= 5242880) { // Maksimal 5MB
                $nama_foto_baru = 'potensi_' . time() . '_' . uniqid() . '.' . $ekstensi;
                $target_dir     = '../uploads/';
                
                if (!is_dir($target_dir)) {
                    mkdir($target_dir, 0777, true);
                }

                $path_upload = $target_dir . $nama_foto_baru;
                $path_db     = 'uploads/' . $nama_foto_baru;

                if (move_uploaded_file($tmp_name, $path_upload)) {
                    $stmt = mysqli_prepare($koneksi, "INSERT INTO potensi_desa (nama, kategori, deskripsi, foto) VALUES (?, ?, ?, ?)");
                    mysqli_stmt_bind_param($stmt, "ssss", $nama, $kategori, $deskripsi, $path_db);
                    
                    if (mysqli_stmt_execute($stmt)) {
                        header("Location: potensi.php?msg=add_success");
                    } else {
                        header("Location: potensi.php?msg=error");
                    }
                    mysqli_stmt_close($stmt);
                    exit;
                } else {
                    header("Location: potensi.php?msg=upload_failed");
                    exit;
                }
            } else {
                header("Location: potensi.php?msg=size_limit");
                exit;
            }
        } else {
            header("Location: potensi.php?msg=invalid_format");
            exit;
        }
    } else {
        header("Location: potensi.php?msg=no_file");
        exit;
    }
}

// -------------------------------------------------------------
// 2. PROSES EDIT POTENSI / UMKM
// -------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_potensi'])) {
    $id_edit   = (int)$_POST['id_potensi'];
    $nama      = mysqli_real_escape_string($koneksi, trim($_POST['nama']));
    $kategori  = mysqli_real_escape_string($koneksi, trim($_POST['kategori']));
    $deskripsi = mysqli_real_escape_string($koneksi, trim($_POST['deskripsi']));
    $foto_lama = $_POST['foto_lama'];

    $foto_final = $foto_lama;

    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === 0) {
        $nama_file   = $_FILES['foto']['name'];
        $ukuran_file = $_FILES['foto']['size'];
        $tmp_name    = $_FILES['foto']['tmp_name'];
        $ekstensi    = strtolower(pathinfo($nama_file, PATHINFO_EXTENSION));
        $ekstensi_diizinkan = array('jpg', 'jpeg', 'png', 'webp');

        if (in_array($ekstensi, $ekstensi_diizinkan) && $ukuran_file <= 5242880) {
            $nama_foto_baru = 'potensi_' . time() . '_' . uniqid() . '.' . $ekstensi;
            $target_dir     = '../uploads/';

            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0777, true);
            }

            $path_upload = $target_dir . $nama_foto_baru;
            $path_db     = 'uploads/' . $nama_foto_baru;

            if (move_uploaded_file($tmp_name, $path_upload)) {
                // Hapus foto lama jika ada
                $clean_foto_lama = str_replace('../', '', $foto_lama);
                if (!empty($clean_foto_lama) && file_exists('../' . $clean_foto_lama)) {
                    unlink('../' . $clean_foto_lama);
                }
                $foto_final = $path_db;
            }
        }
    }

    $stmt = mysqli_prepare($koneksi, "UPDATE potensi_desa SET nama=?, kategori=?, deskripsi=?, foto=? WHERE id=?");
    mysqli_stmt_bind_param($stmt, "ssssi", $nama, $kategori, $deskripsi, $foto_final, $id_edit);

    if (mysqli_stmt_execute($stmt)) {
        header("Location: potensi.php?msg=edit_success");
    } else {
        header("Location: potensi.php?msg=error");
    }
    mysqli_stmt_close($stmt);
    exit;
}

// -------------------------------------------------------------
// 3. PROSES HAPUS POTENSI / UMKM
// -------------------------------------------------------------
if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    
    $stmt_foto = mysqli_prepare($koneksi, "SELECT foto FROM potensi_desa WHERE id = ?");
    mysqli_stmt_bind_param($stmt_foto, "i", $id);
    mysqli_stmt_execute($stmt_foto);
    $res_foto = mysqli_stmt_get_result($stmt_foto);

    if ($data_foto = mysqli_fetch_assoc($res_foto)) {
        $clean_foto = str_replace('../', '', $data_foto['foto']);
        if (!empty($clean_foto) && file_exists('../' . $clean_foto)) {
            unlink('../' . $clean_foto);
        }
    }
    mysqli_stmt_close($stmt_foto);

    $stmt_del = mysqli_prepare($koneksi, "DELETE FROM potensi_desa WHERE id = ?");
    mysqli_stmt_bind_param($stmt_del, "i", $id);
    mysqli_stmt_execute($stmt_del);
    mysqli_stmt_close($stmt_del);

    header("Location: potensi.php?msg=delete_success");
    exit;
}

// NOTIFIKASI SWEETALERT
if (isset($_GET['msg'])) {
    $msg = $_GET['msg'];
    if ($msg == 'add_success') {
        $pesan_swal = "Swal.fire({icon: 'success', title: 'Berhasil!', text: 'Data potensi/UMKM berhasil ditambahkan.', confirmButtonColor: '#059669'});";
    } elseif ($msg == 'edit_success') {
        $pesan_swal = "Swal.fire({icon: 'success', title: 'Berhasil!', text: 'Data potensi/UMKM berhasil diperbarui.', confirmButtonColor: '#059669'});";
    } elseif ($msg == 'delete_success') {
        $pesan_swal = "Swal.fire({icon: 'success', title: 'Terhapus!', text: 'Data potensi/UMKM berhasil dihapus.', confirmButtonColor: '#059669'});";
    } elseif ($msg == 'size_limit') {
        $pesan_swal = "Swal.fire({icon: 'warning', title: 'Gagal!', text: 'Ukuran foto maksimal 5MB.'});";
    } elseif ($msg == 'invalid_format') {
        $pesan_swal = "Swal.fire({icon: 'warning', title: 'Format Salah!', text: 'Format foto harus JPG, JPEG, PNG, atau WEBP.'});";
    } elseif ($msg == 'error') {
        $pesan_swal = "Swal.fire({icon: 'error', title: 'Gagal!', text: 'Terjadi kesalahan sistem.'});";
    }
}

// Hitung Statistik
$total_potensi = mysqli_num_rows(mysqli_query($koneksi, "SELECT id FROM potensi_desa"));
$total_umkm    = mysqli_num_rows(mysqli_query($koneksi, "SELECT id FROM potensi_desa WHERE kategori='UMKM'"));
$total_wisata  = mysqli_num_rows(mysqli_query($koneksi, "SELECT id FROM potensi_desa WHERE kategori='Wisata'"));

// Query Data Potensi
$query_potensi = mysqli_query($koneksi, "SELECT * FROM potensi_desa ORDER BY id DESC");

function tanggal_indo($tanggal) {
    $bulan = [1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    $split = explode('-', $tanggal);
    return $split[2] . ' ' . $bulan[(int)$split[1]] . ' ' . $split[0];
}
$hari_ini = tanggal_indo(date('Y-m-d'));
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Potensi Desa & UMKM - Admin Desa Jatijaya</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- CSS Components -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

    <style>
        :root {
            --primary-color: #059669;
            --primary-hover: #047857;
            --bg-light: #f3f4f6;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #f3f4f6;
            color: #1f2937;
            min-height: 100vh;
            overflow-x: hidden;
            margin: 0;
        }

        .admin-tag {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background-color: #e8f5e9;
            color: #059669;
            padding: 6px 16px;
            border-radius: 50px;
            font-size: 0.85rem;
            font-weight: 700;
        }

        .admin-tag .dot {
            width: 8px;
            height: 8px;
            background-color: #059669;
            border-radius: 50%;
        }

        .date-widget {
            background-color: #ffffff;
            border-radius: 12px;
            padding: 8px 18px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-weight: 600;
            color: #6b7280;
            font-size: 0.88rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }

        .hero-banner {
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
            border-radius: 20px;
            padding: 28px 32px;
            color: #ffffff;
            position: relative;
            overflow: hidden;
            box-shadow: 0 4px 14px rgba(5, 150, 105, 0.15);
        }

        .hero-banner .bg-icon {
            position: absolute;
            right: 20px;
            bottom: -20px;
            font-size: 8rem;
            opacity: 0.15;
            color: #ffffff;
            pointer-events: none;
        }

        .hero-banner .btn-banner-add {
            background-color: #ffffff;
            color: #047857;
            font-weight: 700;
            border-radius: 50px;
            padding: 12px 24px;
            font-size: 0.95rem;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.12);
            transition: all 0.2s ease-in-out;
            border: none;
        }

        .hero-banner .btn-banner-add:hover {
            background-color: #f0fdf4;
            color: #059669;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.18);
        }

        .stat-card {
            background: #ffffff;
            border-radius: 16px;
            padding: 18px 20px;
            border: none;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
        }

        .card-potensi {
            border: none;
            border-radius: 20px;
            background: #ffffff;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .card-potensi:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
        }

        .img-potensi-card {
            height: 190px;
            object-fit: cover;
            background-color: #f8f9fa;
        }

        /* STYLING MODAL ALA FORM BERITA BARU */
        .modal-icon-box {
            width: 48px;
            height: 48px;
            background-color: #ecfdf5;
            color: #059669;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.35rem;
            flex-shrink: 0;
        }

        .form-label-custom {
            font-size: 0.88rem;
            font-weight: 600;
            color: #4b5563;
            margin-bottom: 6px;
        }

        .form-control-custom, .form-select-custom {
            border-radius: 10px;
            border: 1px solid #e5e7eb;
            padding: 10px 14px;
            font-size: 0.9rem;
            color: #1f2937;
            transition: all 0.2s;
        }

        .form-control-custom:focus, .form-select-custom:focus {
            border-color: #10b981;
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.15);
        }
    </style>
</head>

<body class="d-flex">

    <!-- Sidebar Admin -->
    <?php 
        if (file_exists(__DIR__ . '/includes/sidebar.php')) {
            include __DIR__ . '/includes/sidebar.php';
        } elseif (file_exists(__DIR__ . '/sidebar.php')) {
            include __DIR__ . '/sidebar.php';
        }
    ?>

    <!-- Konten Utama -->
    <div class="flex-grow-1 p-3 p-md-4" style="min-width: 0;">

        <!-- Top Bar -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="admin-tag">
                <span class="dot"></span>
                <span>Manajemen Potensi & Komoditas</span>
            </div>
            <div class="date-widget shadow-sm">
                <i class="fa-regular fa-calendar me-1"></i>
                <span><?php echo $hari_ini; ?></span>
            </div>
        </div>

        <!-- Banner Hero -->
        <div class="hero-banner mb-4 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
            <div class="position-relative z-1">
                <h3 class="fw-bold mb-1 text-white">
                    <i class="fa-solid fa-store me-2"></i> Potensi Desa & UMKM
                </h3>
                <p class="mb-0 text-white opacity-90 small">
                    Kelola katalog produk lokal, komoditas unggulan, produk UMKM warga, dan destinasi wisata Desa Jatijaya.
                </p>
            </div>

            <div class="position-relative z-1 flex-shrink-0">
                <button type="button" class="btn btn-banner-add" data-bs-toggle="modal" data-bs-target="#modalTambahPotensi">
                    <i class="fa-solid fa-plus me-1"></i> Tambah Potensi Baru
                </button>
            </div>

            <i class="fa-solid fa-store bg-icon"></i>
        </div>

        <!-- Ringkasan Statistik -->
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="stat-card d-flex align-items-center gap-3">
                    <div class="p-3 bg-success bg-opacity-10 text-success rounded-3">
                        <i class="fa-solid fa-boxes-stacked fs-4"></i>
                    </div>
                    <div>
                        <small class="text-muted fw-semibold d-block">Total Potensi & UMKM</small>
                        <h4 class="fw-bold text-dark mb-0"><?php echo $total_potensi; ?></h4>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card d-flex align-items-center gap-3">
                    <div class="p-3 bg-info bg-opacity-10 text-info rounded-3">
                        <i class="fa-solid fa-store fs-4"></i>
                    </div>
                    <div>
                        <small class="text-muted fw-semibold d-block">Produk UMKM</small>
                        <h4 class="fw-bold text-dark mb-0"><?php echo $total_umkm; ?></h4>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card d-flex align-items-center gap-3">
                    <div class="p-3 bg-warning bg-opacity-10 text-warning rounded-3">
                        <i class="fa-solid fa-mountain-sun fs-4"></i>
                    </div>
                    <div>
                        <small class="text-muted fw-semibold d-block">Destinasi Wisata</small>
                        <h4 class="fw-bold text-dark mb-0"><?php echo $total_wisata; ?></h4>
                    </div>
                </div>
            </div>
        </div>

        <!-- Grid Card Potensi -->
        <div class="row g-4">
            <?php if ($query_potensi && mysqli_num_rows($query_potensi) > 0): ?>
                <?php while ($p = mysqli_fetch_assoc($query_potensi)): ?>
                    <?php 
                        $clean_path = str_replace('../', '', $p['foto']);
                        $src_foto = (!empty($clean_path) && file_exists('../' . $clean_path)) ? '../' . $clean_path : 'https://via.placeholder.com/400x200?text=Gambar+Tidak+Ada';
                    ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="card card-potensi overflow-hidden h-100 d-flex flex-column">
                            <img src="<?php echo htmlspecialchars($src_foto); ?>" alt="<?php echo htmlspecialchars($p['nama']); ?>" class="w-100 img-potensi-card">
                            
                            <div class="card-body p-4 d-flex flex-column flex-grow-1">
                                <div class="mb-2">
                                    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-20 rounded-2 px-2.5 py-1 small fw-bold">
                                        <?php echo htmlspecialchars($p['kategori']); ?>
                                    </span>
                                </div>
                                <h5 class="fw-bold text-dark mb-2"><?php echo htmlspecialchars($p['nama']); ?></h5>
                                <p class="text-secondary small mb-4 flex-grow-1" style="line-height: 1.6;">
                                    <?php echo htmlspecialchars($p['deskripsi']); ?>
                                </p>
                                
                                <div class="d-flex gap-2 pt-3 border-top">
                                    <!-- Tombol Edit -->
                                    <button class="btn btn-outline-warning w-50 fw-bold rounded-3 btn-sm py-2" data-bs-toggle="modal" data-bs-target="#modalEditPotensi<?php echo $p['id']; ?>">
                                        <i class="fa-solid fa-pen-to-square me-1"></i> Edit
                                    </button>
                                    <!-- Tombol Hapus -->
                                    <button class="btn btn-outline-danger w-50 fw-bold rounded-3 btn-sm py-2" onclick="konfirmasiHapus(<?php echo $p['id']; ?>)">
                                        <i class="fa-solid fa-trash-can me-1"></i> Hapus
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- MODAL EDIT POTENSI (TAMPILAN MODERN ALA FORM BERITA BARU) -->
                    <div class="modal fade" id="modalEditPotensi<?php echo $p['id']; ?>" tabindex="-1" aria-labelledby="modalEditPotensiLabel<?php echo $p['id']; ?>" aria-hidden="true">
                        <div class="modal-dialog modal-lg modal-dialog-centered">
                            <div class="modal-content rounded-4 border-0 shadow-lg overflow-hidden">
                                
                                <!-- HEADER MODAL -->
                                <div class="modal-header border-0 pb-2 pt-4 px-4 align-items-start">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="modal-icon-box">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </div>
                                        <div>
                                            <h5 class="modal-title fw-bold text-dark mb-0 fs-5" id="modalEditPotensiLabel<?php echo $p['id']; ?>">Edit Data Potensi Desa</h5>
                                            <p class="text-muted small mb-0">Perbarui profil produk, komoditas, atau wisata desa.</p>
                                        </div>
                                    </div>
                                    <button type="button" class="btn-close mt-1" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>

                                <!-- FORM EDIT DATA -->
                                <form action="potensi.php" method="POST" enctype="multipart/form-data">
                                    <input type="hidden" name="id_potensi" value="<?php echo $p['id']; ?>">
                                    <input type="hidden" name="foto_lama" value="<?php echo htmlspecialchars($p['foto']); ?>">

                                    <div class="modal-body p-4">
                                        
                                        <!-- BARIS 1: NAMA & KATEGORI -->
                                        <div class="row g-3 mb-3">
                                            <div class="col-md-7">
                                                <label class="form-label-custom">Nama Produk / Tempat</label>
                                                <input type="text" name="nama" class="form-control form-control-custom" value="<?php echo htmlspecialchars($p['nama']); ?>" required>
                                            </div>
                                            <div class="col-md-5">
                                                <label class="form-label-custom">Kategori</label>
                                                <select name="kategori" class="form-select form-select-custom" required>
                                                    <option value="UMKM" <?php echo ($p['kategori'] == 'UMKM') ? 'selected' : ''; ?>>UMKM</option>
                                                    <option value="Wisata" <?php echo ($p['kategori'] == 'Wisata') ? 'selected' : ''; ?>>Wisata</option>
                                                    <option value="Pertanian" <?php echo ($p['kategori'] == 'Pertanian') ? 'selected' : ''; ?>>Pertanian</option>
                                                    <option value="Peternakan" <?php echo ($p['kategori'] == 'Peternakan') ? 'selected' : ''; ?>>Peternakan</option>
                                                </select>
                                            </div>
                                        </div>

                                        <!-- BARIS 2: FILE FOTO & PREVIEW -->
                                        <div class="row g-3 mb-3">
                                            <div class="col-md-8">
                                                <label class="form-label-custom">Ganti Foto Sampul (Opsional)</label>
                                                <input type="file" name="foto" class="form-control form-control-custom" accept="image/*">
                                                <div class="form-text small">Format: JPG, PNG, WEBP (Maksimal 5MB). Kosongkan jika tidak diganti.</div>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label-custom d-block">Foto Saat Ini</label>
                                                <div class="d-flex align-items-center gap-2 border rounded-3 p-1.5 bg-light">
                                                    <img src="<?php echo htmlspecialchars($src_foto); ?>" class="rounded-2" style="width: 48px; height: 38px; object-fit: cover;">
                                                    <span class="text-muted small text-truncate fw-semibold">Sampul Saat Ini</span>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- BARIS 3: DESKRIPSI -->
                                        <div class="mb-2">
                                            <label class="form-label-custom">Deskripsi Potensi</label>
                                            <textarea name="deskripsi" class="form-control form-control-custom" rows="4" required><?php echo htmlspecialchars($p['deskripsi']); ?></textarea>
                                        </div>

                                    </div>

                                    <!-- FOOTER MODAL -->
                                    <div class="modal-footer bg-light border-0 py-3 px-4 d-flex justify-content-end gap-2">
                                        <button type="button" class="btn btn-light rounded-3 fw-bold px-4 py-2 border" data-bs-dismiss="modal">Batal</button>
                                        <button type="submit" name="edit_potensi" class="btn btn-success rounded-3 fw-bold px-4 py-2 d-inline-flex align-items-center gap-2" style="background-color: #059669; border: none;">
                                            <i class="fa-solid fa-floppy-disk"></i> Simpan Perubahan
                                        </button>
                                    </div>
                                </form>

                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="card border-0 shadow-sm rounded-4 bg-white text-center py-5">
                        <i class="fa-solid fa-store fs-1 d-block mb-2 text-secondary opacity-50"></i>
                        <p class="text-muted mb-0">Belum ada data potensi desa atau UMKM yang ditambahkan.</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- MODAL TAMBAH POTENSI (TAMPILAN MODERN ALA FORM BERITA BARU) -->
    <div class="modal fade" id="modalTambahPotensi" tabindex="-1" aria-labelledby="modalTambahPotensiLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow-lg overflow-hidden">
                
                <!-- HEADER MODAL -->
                <div class="modal-header border-0 pb-2 pt-4 px-4 align-items-start">
                    <div class="d-flex align-items-center gap-3">
                        <div class="modal-icon-box">
                            <i class="fa-solid fa-bag-shopping"></i>
                        </div>
                        <div>
                            <h5 class="modal-title fw-bold text-dark mb-0 fs-5" id="modalTambahPotensiLabel">Tambah Potensi Desa / UMKM Baru</h5>
                            <p class="text-muted small mb-0">Buat profil singkat produk lokal, usaha warga, atau destinasi wisata.</p>
                        </div>
                    </div>
                    <button type="button" class="btn-close mt-1" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <!-- FORM TAMBAH DATA -->
                <form action="potensi.php" method="POST" enctype="multipart/form-data">
                    <div class="modal-body p-4">
                        
                        <!-- BARIS 1: NAMA & KATEGORI -->
                        <div class="row g-3 mb-3">
                            <div class="col-md-7">
                                <label class="form-label-custom">Nama Produk / Tempat</label>
                                <input type="text" name="nama" class="form-control form-control-custom" placeholder="Contoh: Kerajinan Anyaman Bambu" required>
                            </div>
                            <div class="col-md-5">
                                <label class="form-label-custom">Kategori Potensi</label>
                                <select name="kategori" class="form-select form-select-custom" required>
                                    <option value="" disabled selected>-- Pilih Kategori --</option>
                                    <option value="UMKM">UMKM</option>
                                    <option value="Wisata">Wisata</option>
                                    <option value="Pertanian">Pertanian</option>
                                    <option value="Peternakan">Peternakan</option>
                                </select>
                            </div>
                        </div>

                        <!-- BARIS 2: FILE FOTO -->
                        <div class="mb-3">
                            <label class="form-label-custom">Upload Foto Sampul / Header</label>
                            <input type="file" name="foto" class="form-control form-control-custom" accept="image/*" required>
                            <div class="form-text small">Format gambar yang didukung: JPG, PNG, WEBP (Maksimal 5MB).</div>
                        </div>

                        <!-- BARIS 3: DESKRIPSI -->
                        <div class="mb-2">
                            <label class="form-label-custom">Deskripsi Singkat</label>
                            <textarea name="deskripsi" class="form-control form-control-custom" rows="4" placeholder="Jelaskan mengenai keunggulan, kualitas, lokasi, atau daya tarik..." required></textarea>
                        </div>

                    </div>

                    <!-- FOOTER MODAL -->
                    <div class="modal-footer bg-light border-0 py-3 px-4 d-flex justify-content-end gap-2">
                        <button type="button" class="btn btn-light rounded-3 fw-bold px-4 py-2 border" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" name="tambah_potensi" class="btn btn-success rounded-3 fw-bold px-4 py-2 d-inline-flex align-items-center gap-2" style="background-color: #059669; border: none;">
                            <i class="fa-solid fa-paper-plane"></i> Simpan Data
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
                text: "Data potensi/UMKM ini akan dihapus secara permanen!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e11d48',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal',
                customClass: {
                    popup: 'rounded-4 border-0 shadow-lg',
                    confirmButton: 'btn btn-danger px-4 py-2 rounded-3 fw-bold',
                    cancelButton: 'btn btn-secondary px-4 py-2 rounded-3 fw-bold me-2'
                },
                buttonsStyling: false
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'potensi.php?hapus=' + id;
                }
            });
        }
    </script>
</body>
</html>