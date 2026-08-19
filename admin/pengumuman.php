<?php
session_start();
require_once '../config/koneksi.php';

/** @var mysqli $koneksi */

// Proteksi Sesi Admin (Hanya admin login yang bisa akses)
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit;
}

$pesan_swal = "";

// 1. PROSES TAMBAH PENGUMUMAN
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah_pengumuman'])) {
    $judul    = trim($_POST['judul']);
    $isi      = trim($_POST['isi_pengumuman']);
    $lampiran = "";

    // Upload Lampiran/Dokumen jika ada
    if (!empty($_FILES['lampiran']['name'])) {
        $target_dir = "../uploads/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $file_name   = time() . '_' . basename($_FILES["lampiran"]["name"]);
        $target_file = $target_dir . $file_name;
        
        if (move_uploaded_file($_FILES["lampiran"]["tmp_name"], $target_file)) {
            $lampiran = "uploads/" . $file_name;
        }
    }

    $stmt = mysqli_prepare($koneksi, "INSERT INTO pengumuman (judul, isi_pengumuman, lampiran) VALUES (?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "sss", $judul, $isi, $lampiran);

    if (mysqli_stmt_execute($stmt)) {
        header("Location: pengumuman.php?msg=add_success");
    } else {
        header("Location: pengumuman.php?msg=error");
    }
    mysqli_stmt_close($stmt);
    exit;
}

// 2. PROSES EDIT PENGUMUMAN (DENGAN LAMPIRAN)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_pengumuman'])) {
    $id_edit = (int)$_POST['id_pengumuman'];
    $judul   = trim($_POST['judul']);
    $isi     = trim($_POST['isi_pengumuman']);
    $stmt = null;

    // Cek apakah ada file lampiran baru yang diunggah
    if (!empty($_FILES['lampiran']['name'])) {
        $target_dir = "../uploads/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $file_name   = time() . '_' . basename($_FILES["lampiran"]["name"]);
        $target_file = $target_dir . $file_name;

        if (move_uploaded_file($_FILES["lampiran"]["tmp_name"], $target_file)) {
            $lampiran_baru = "uploads/" . $file_name;

            // Hapus berkas lama jika ada
            $stmt_old = mysqli_prepare($koneksi, "SELECT lampiran FROM pengumuman WHERE id = ?");
            mysqli_stmt_bind_param($stmt_old, "i", $id_edit);
            mysqli_stmt_execute($stmt_old);
            $res_old = mysqli_stmt_get_result($stmt_old);
            if ($old_data = mysqli_fetch_assoc($res_old)) {
                $clean_old = str_replace('../', '', $old_data['lampiran']);
                if (!empty($clean_old) && file_exists('../' . $clean_old)) {
                    unlink('../' . $clean_old);
                }
            }
            mysqli_stmt_close($stmt_old);

            // Update judul, isi, dan lampiran baru
            $stmt = mysqli_prepare($koneksi, "UPDATE pengumuman SET judul = ?, isi_pengumuman = ?, lampiran = ? WHERE id = ?");
            mysqli_stmt_bind_param($stmt, "sssi", $judul, $isi, $lampiran_baru, $id_edit);
        }
    }

    if ($stmt === null) {
        // Update judul dan isi saja (tanpa mengubah lampiran)
        $stmt = mysqli_prepare($koneksi, "UPDATE pengumuman SET judul = ?, isi_pengumuman = ? WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "ssi", $judul, $isi, $id_edit);
    }

    if (mysqli_stmt_execute($stmt)) {
        header("Location: pengumuman.php?msg=edit_success");
    } else {
        header("Location: pengumuman.php?msg=error");
    }
    mysqli_stmt_close($stmt);
    exit;
}

// 3. PROSES HAPUS PENGUMUMAN
if (isset($_GET['aksi']) && $_GET['aksi'] == 'hapus') {
    $id_hapus = (int)$_GET['id'];
    
    // Hapus berkas lampiran jika ada
    $stmt_berkas = mysqli_prepare($koneksi, "SELECT lampiran FROM pengumuman WHERE id = ?");
    mysqli_stmt_bind_param($stmt_berkas, "i", $id_hapus);
    mysqli_stmt_execute($stmt_berkas);
    $res_berkas = mysqli_stmt_get_result($stmt_berkas);

    if ($data_b = mysqli_fetch_assoc($res_berkas)) {
        $clean_file = str_replace('../', '', $data_b['lampiran']);
        if (!empty($clean_file) && file_exists('../' . $clean_file)) {
            unlink('../' . $clean_file);
        }
    }
    mysqli_stmt_close($stmt_berkas);

    $stmt_del = mysqli_prepare($koneksi, "DELETE FROM pengumuman WHERE id = ?");
    mysqli_stmt_bind_param($stmt_del, "i", $id_hapus);
    mysqli_stmt_execute($stmt_del);
    mysqli_stmt_close($stmt_del);

    header("Location: pengumuman.php?msg=delete_success");
    exit;
}

// NOTIFIKASI SWEETALERT
if (isset($_GET['msg'])) {
    $msg = $_GET['msg'];
    if ($msg == 'add_success') {
        $pesan_swal = "Swal.fire({icon: 'success', title: 'Berhasil!', text: 'Pengumuman berhasil ditambahkan.', showConfirmButton: false, timer: 1800});";
    } elseif ($msg == 'edit_success') {
        $pesan_swal = "Swal.fire({icon: 'success', title: 'Berhasil!', text: 'Pengumuman berhasil diperbarui.', showConfirmButton: false, timer: 1800});";
    } elseif ($msg == 'delete_success') {
        $pesan_swal = "Swal.fire({icon: 'success', title: 'Terhapus!', text: 'Pengumuman berhasil dihapus.', showConfirmButton: false, timer: 1800});";
    } elseif ($msg == 'error') {
        $pesan_swal = "Swal.fire({icon: 'error', title: 'Gagal!', text: 'Terjadi kesalahan sistem.'});";
    }
}

// Ambil seluruh data pengumuman
$query = mysqli_query($koneksi, "SELECT * FROM pengumuman ORDER BY id DESC");
$total_pengumuman = mysqli_num_rows($query);

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
    <title>Kelola Pengumuman - Admin Desa Jatijaya</title>

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
            --primary-color: #146338;
            --primary-hover: #0d4828;
            --bg-light: #f8fafc;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #f4f6f9;
            color: #1e293b;
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
            width: 8px;
            height: 8px;
            background-color: #2e7d32;
            border-radius: 50%;
        }

        .date-widget {
            background-color: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 8px 18px;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            font-weight: 600;
            color: #146338;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.02);
            font-size: 0.9rem;
        }

        /* Banner Hero Modern */
        .hero-banner {
            background: linear-gradient(135deg, #0b6e3d 0%, #146338 100%);
            border-radius: 20px;
            padding: 32px 36px;
            color: #ffffff;
            position: relative;
            overflow: hidden;
            box-shadow: 0 10px 25px rgba(20, 99, 56, 0.15);
        }

        .hero-banner .bg-icon {
            position: absolute;
            right: 20px;
            bottom: -25px;
            font-size: 150px;
            color: rgba(255, 255, 255, 0.08);
            pointer-events: none;
            transform: rotate(-10deg);
        }

        .hero-banner .btn-light {
            color: #146338 !important;
            border: none;
            font-size: 0.95rem;
            transition: all 0.2s ease;
        }

        .hero-banner .btn-light:hover {
            background-color: #ffffff;
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.15) !important;
        }

        /* Stat Card */
        .stat-card {
            background-color: #ffffff;
            border-radius: 20px;
            padding: 18px 24px;
            display: inline-flex;
            align-items: center;
            gap: 16px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.03);
            border: 1px solid #f1f5f9;
            min-width: 280px;
        }

        .stat-icon-wrapper {
            width: 52px;
            height: 52px;
            background-color: #fff8e1;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .stat-icon-wrapper i {
            font-size: 1.5rem;
            color: #f59e0b;
        }

        .stat-title {
            font-size: 0.88rem;
            color: #64748b;
            font-weight: 500;
            margin-bottom: 2px;
        }

        .stat-value {
            font-size: 1.4rem;
            font-weight: 800;
            color: #0f172a;
            line-height: 1.2;
        }

        .stat-unit {
            font-size: 0.95rem;
            font-weight: 600;
            color: #64748b;
            margin-left: 4px;
        }

        /* Card & Table Modern UI */
        .card-custom {
            border: 1px solid #e2e8f0;
            border-radius: 20px;
            background: #ffffff;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
            overflow: hidden;
            padding: 0;
        }

        .custom-table {
            margin-bottom: 0;
            width: 100%;
        }

        .custom-table thead th {
            background-color: #f8fafc;
            color: #475569;
            font-size: 0.8rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 16px 24px;
            border-bottom: 1px solid #e2e8f0;
            border-top: none;
        }

        .custom-table tbody td {
            padding: 18px 24px;
            vertical-align: middle;
            color: #334155;
            border-bottom: 1px solid #f1f5f9;
            font-size: 0.92rem;
        }

        .custom-table tbody tr:last-child td {
            border-bottom: none;
        }

        .custom-table tbody tr:hover {
            background-color: #f8fafc;
        }

        /* Tombol Aksi Soft Style */
        .btn-action {
            width: 36px;
            height: 36px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
            border: none;
            transition: all 0.2s ease;
            text-decoration: none;
        }

        .btn-action-edit {
            background-color: #fef3c7;
            color: #d97706;
        }

        .btn-action-edit:hover {
            background-color: #fde68a;
            color: #b45309;
        }

        .btn-action-delete {
            background-color: #fee2e2;
            color: #dc2626;
        }

        .btn-action-delete:hover {
            background-color: #fca5a5;
            color: #991b1b;
        }

        .modal-modern .modal-content {
            border: none;
            border-radius: 20px;
        }

        .form-control, .form-select {
            border-radius: 12px;
            padding: 0.65rem 1rem;
            border: 1px solid #cbd5e1;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(20, 99, 56, 0.15);
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
                <span>Manajemen Informasi Publik</span>
            </div>
            <div class="date-widget">
                <i class="fa-regular fa-calendar-days"></i>
                <span><?php echo $hari_ini; ?></span>
            </div>
        </div>

        <!-- Hero Banner Modern -->
        <div class="hero-banner mb-4 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
            <div class="position-relative z-1">
                <h3 class="fw-bold mb-1 d-flex align-items-center gap-2 text-white">
                    <i class="fa-solid fa-bullhorn fs-3"></i>
                    <span>Kelola Pengumuman Desa</span>
                </h3>
                <p class="mb-0 text-white-50 small opacity-75">
                    Buat dan atur pemberitahuan resmi secara instan untuk seluruh masyarakat Desa Jatijaya.
                </p>
            </div>

            <div class="position-relative z-1 flex-shrink-0">
                <button type="button" class="btn btn-light fw-bold px-4 py-2.5 rounded-pill shadow-sm d-inline-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#modalTambah">
                    <i class="fa-solid fa-plus fs-6"></i>
                    <span>Buat Pengumuman</span>
                </button>
            </div>

            <i class="fa-solid fa-bullhorn bg-icon"></i>
        </div>

        <!-- STAT CARD -->
        <div class="mb-4">
            <div class="stat-card">
                <div class="stat-icon-wrapper">
                    <i class="fa-solid fa-rotate-left"></i>
                </div>
                <div>
                    <div class="stat-title">Pengumuman Dipublikasikan</div>
                    <div class="stat-value">
                        <?php echo $total_pengumuman; ?>
                        <span class="stat-unit">Pengumuman</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabel Pengumuman Clean Card -->
        <div class="card card-custom">
            <div class="table-responsive">
                <table class="table custom-table align-middle">
                    <thead>
                        <tr>
                            <th width="6%">No</th>
                            <th width="28%">Judul Pengumuman</th>
                            <th width="34%">Isi Ringkas</th>
                            <th width="16%">Tanggal Publikasi</th>
                            <th width="8%">Lampiran</th>
                            <th width="8%" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($total_pengumuman > 0): ?>
                            <?php $no = 1; while ($p = mysqli_fetch_assoc($query)): ?>
                                <tr>
                                    <td class="fw-bold text-muted"><?php echo $no++; ?></td>
                                    <td class="fw-bold text-dark"><?php echo htmlspecialchars($p['judul']); ?></td>
                                    <td class="text-secondary small">
                                        <?php echo htmlspecialchars(mb_strimwidth($p['isi_pengumuman'], 0, 95, '...')); ?>
                                    </td>
                                    <td>
                                        <small class="text-muted d-inline-flex align-items-center gap-1">
                                            <i class="fa-regular fa-calendar"></i>
                                            <?php echo date('d-m-Y H:i', strtotime($p['tanggal'])); ?>
                                        </small>
                                    </td>
                                    <td>
                                        <?php if (!empty($p['lampiran'])): ?>
                                            <a href="../<?php echo htmlspecialchars($p['lampiran']); ?>" target="_blank" class="btn btn-sm btn-outline-success rounded-pill px-3 py-1 small fw-semibold">
                                                <i class="fa-solid fa-paperclip me-1"></i> Berkas
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted small">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center gap-2">
                                            <!-- Edit -->
                                            <button type="button" class="btn-action btn-action-edit" data-bs-toggle="modal" data-bs-target="#modalEdit<?php echo $p['id']; ?>" title="Edit">
                                                <i class="fa-solid fa-pen fs-6"></i>
                                            </button>
                                            <!-- Hapus -->
                                            <button type="button" onclick="konfirmasiHapus(<?php echo $p['id']; ?>)" class="btn-action btn-action-delete" title="Hapus">
                                                <i class="fa-solid fa-trash fs-6"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>

                                <!-- MODAL EDIT PENGUMUMAN -->
                                <div class="modal fade modal-modern" id="modalEdit<?php echo $p['id']; ?>" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-lg modal-dialog-centered">
                                        <div class="modal-content shadow-lg">
                                            <div class="modal-header border-0 bg-white pt-4 px-4 pb-2">
                                                <div class="d-flex align-items-center">
                                                    <div class="bg-warning bg-opacity-10 text-warning p-3 rounded-3 me-3">
                                                        <i class="fa-solid fa-pen-to-square fs-4"></i>
                                                    </div>
                                                    <div>
                                                        <h5 class="modal-title fw-bold text-dark mb-0">Edit Pengumuman</h5>
                                                        <p class="text-muted small mb-0">Perbarui judul, rincian teks, atau berkas lampiran.</p>
                                                    </div>
                                                </div>
                                                <button type="button" class="btn-close align-self-start" data-bs-dismiss="modal"></button>
                                            </div>

                                            <form action="pengumuman.php" method="POST" enctype="multipart/form-data">
                                                <div class="modal-body p-4">
                                                    <input type="hidden" name="id_pengumuman" value="<?php echo $p['id']; ?>">
                                                    
                                                    <div class="mb-3">
                                                        <label class="form-label fw-bold small text-secondary">Judul Pengumuman</label>
                                                        <input type="text" name="judul" class="form-control" value="<?php echo htmlspecialchars($p['judul']); ?>" required>
                                                    </div>

                                                    <div class="mb-3">
                                                        <label class="form-label fw-bold small text-secondary">Isi Pengumuman</label>
                                                        <textarea name="isi_pengumuman" class="form-control" rows="6" required><?php echo htmlspecialchars($p['isi_pengumuman']); ?></textarea>
                                                    </div>

                                                    <div class="mb-3">
                                                        <label class="form-label fw-bold small text-secondary">Lampiran Dokumen (Opsional)</label>
                                                        <?php if (!empty($p['lampiran'])): ?>
                                                            <div class="mb-2">
                                                                <span class="badge bg-light text-dark border p-2 rounded-3">
                                                                    <i class="fa-solid fa-paperclip text-success me-1"></i> Berkas Saat Ini: 
                                                                    <a href="../<?php echo htmlspecialchars($p['lampiran']); ?>" target="_blank" class="text-success text-decoration-underline ms-1">Lihat Berkas</a>
                                                                </span>
                                                            </div>
                                                        <?php endif; ?>
                                                        <input type="file" name="lampiran" class="form-control">
                                                        <small class="text-muted">Pilih berkas baru jika ingin mengganti lampiran saat ini.</small>
                                                    </div>
                                                </div>

                                                <div class="modal-footer border-0 bg-light px-4 py-3">
                                                    <button type="button" class="btn btn-white text-secondary fw-semibold rounded-3 px-4 border" data-bs-dismiss="modal">Batal</button>
                                                    <button type="submit" name="edit_pengumuman" class="btn btn-warning text-dark fw-bold rounded-3 px-4 py-2 shadow-sm">
                                                        <i class="fa-solid fa-floppy-disk me-1"></i> Simpan Perubahan
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="fa-solid fa-bullhorn fa-3x mb-3 opacity-25"></i>
                                    <p class="mb-0 fw-medium">Belum ada pengumuman yang dibuat.</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- MODAL TAMBAH PENGUMUMAN -->
    <div class="modal fade modal-modern" id="modalTambah" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content shadow-lg">
                <div class="modal-header border-0 bg-white pt-4 px-4 pb-2">
                    <div class="d-flex align-items-center">
                        <div class="bg-success bg-opacity-10 text-success p-3 rounded-3 me-3">
                            <i class="fa-solid fa-bullhorn fs-4"></i>
                        </div>
                        <div>
                            <h5 class="modal-title fw-bold text-dark mb-0">Buat Pengumuman Baru</h5>
                            <p class="text-muted small mb-0">Isi judul dan pengumuman yang akan dipublikasikan.</p>
                        </div>
                    </div>
                    <button type="button" class="btn-close align-self-start" data-bs-dismiss="modal"></button>
                </div>

                <form action="pengumuman.php" method="POST" enctype="multipart/form-data">
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-secondary">Judul Pengumuman</label>
                            <input type="text" name="judul" class="form-control" placeholder="Contoh: Kerja Bakti Masal Warga" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-secondary">Isi Pengumuman</label>
                            <textarea name="isi_pengumuman" class="form-control" rows="6" placeholder="Tuliskan detail informasi pengumuman..." required></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-secondary">Lampiran Dokumen (Opsional - PDF/Gambar)</label>
                            <input type="file" name="lampiran" class="form-control">
                        </div>
                    </div>

                    <div class="modal-footer border-0 bg-light px-4 py-3">
                        <button type="button" class="btn btn-white text-secondary fw-semibold rounded-3 px-4 border" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" name="tambah_pengumuman" class="btn text-white fw-bold rounded-3 px-4 py-2 shadow-sm" style="background-color: var(--primary-color);">
                            <i class="fa-solid fa-paper-plane me-1"></i> Publikasikan Pengumuman
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
                text: "Pengumuman ini akan dihapus secara permanen!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal',
                customClass: { popup: 'rounded-4' }
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'pengumuman.php?aksi=hapus&id=' + id;
                }
            });
        }
    </script>
</body>
</html>