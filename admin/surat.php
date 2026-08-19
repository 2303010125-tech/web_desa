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

// 1. PROSES TAMBAH SURAT BARU
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah_surat'])) {
    $nik         = mysqli_real_escape_string($koneksi, $_POST['nik']);
    $nama        = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $jenis_surat = mysqli_real_escape_string($koneksi, $_POST['jenis_surat']);
    $no_hp       = mysqli_real_escape_string($koneksi, $_POST['no_hp']);
    $keterangan  = mysqli_real_escape_string($koneksi, $_POST['keterangan']);
    $status      = mysqli_real_escape_string($koneksi, $_POST['status']);

    $query_tambah = "INSERT INTO pengajuan_surat (nik, nama, jenis_surat, no_hp, keterangan, status) 
                     VALUES ('$nik', '$nama', '$jenis_surat', '$no_hp', '$keterangan', '$status')";

    if (mysqli_query($koneksi, $query_tambah)) {
        header("Location: surat.php?msg=add_success");
        exit;
    } else {
        header("Location: surat.php?msg=error");
        exit;
    }
}

// 2. PROSES EDIT SURAT
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_surat'])) {
    $id_surat   = mysqli_real_escape_string($koneksi, $_POST['id_surat']);
    $nik        = mysqli_real_escape_string($koneksi, $_POST['nik']);
    $nama       = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $jenis_surat= mysqli_real_escape_string($koneksi, $_POST['jenis_surat']);
    $no_hp      = mysqli_real_escape_string($koneksi, $_POST['no_hp']);
    $status     = mysqli_real_escape_string($koneksi, $_POST['status']);
    $keterangan = mysqli_real_escape_string($koneksi, $_POST['keterangan']);

    $query_update = "UPDATE pengajuan_surat SET 
                     nik='$nik', nama='$nama', jenis_surat='$jenis_surat', 
                     no_hp='$no_hp', status='$status', keterangan='$keterangan' 
                     WHERE id='$id_surat'";

    if (mysqli_query($koneksi, $query_update)) {
        header("Location: surat.php?msg=edit_success");
        exit;
    } else {
        header("Location: surat.php?msg=error");
        exit;
    }
}

// 3. QUICK ACTION: UBAH STATUS
if (isset($_GET['action_status']) && isset($_GET['id'])) {
    $id_surat   = mysqli_real_escape_string($koneksi, $_GET['id']);
    $action     = mysqli_real_escape_string($koneksi, $_GET['action_status']);

    $status_baru = 'Pending';
    if ($action === 'setujui') {
        $status_baru = 'Selesai';
    } elseif ($action === 'tolak') {
        $status_baru = 'Ditolak';
    }

    if (mysqli_query($koneksi, "UPDATE pengajuan_surat SET status='$status_baru' WHERE id='$id_surat'")) {
        header("Location: surat.php?msg=status_success");
        exit;
    } else {
        header("Location: surat.php?msg=error");
        exit;
    }
}

// 4. PROSES HAPUS SURAT
if (isset($_GET['hapus'])) {
    $id = mysqli_real_escape_string($koneksi, $_GET['hapus']);

    if (mysqli_query($koneksi, "DELETE FROM pengajuan_surat WHERE id='$id'")) {
        header("Location: surat.php?msg=delete_success");
        exit;
    } else {
        header("Location: surat.php?msg=error");
        exit;
    }
}

// SWAL NOTIFIKASI
if (isset($_GET['msg'])) {
    $msg = $_GET['msg'];
    if ($msg == 'add_success') {
        $pesan_swal = "Swal.fire({icon: 'success', title: 'Berhasil!', text: 'Data berhasil disimpan.', showConfirmButton: false, timer: 1800});";
    } elseif ($msg == 'edit_success' || $msg == 'status_success') {
        $pesan_swal = "Swal.fire({icon: 'success', title: 'Berhasil!', text: 'Status berhasil diperbarui.', showConfirmButton: false, timer: 1800});";
    } elseif ($msg == 'delete_success') {
        $pesan_swal = "Swal.fire({icon: 'success', title: 'Terhapus!', text: 'Data berhasil dihapus.', showConfirmButton: false, timer: 1800});";
    } elseif ($msg == 'error') {
        $pesan_swal = "Swal.fire({icon: 'error', title: 'Gagal!', text: 'Terjadi kesalahan sistem.'});";
    }
}

// Hitung Statistik
$total_surat   = mysqli_num_rows(mysqli_query($koneksi, "SELECT id FROM pengajuan_surat"));
$total_pending = mysqli_num_rows(mysqli_query($koneksi, "SELECT id FROM pengajuan_surat WHERE status='Pending'"));
$total_selesai = mysqli_num_rows(mysqli_query($koneksi, "SELECT id FROM pengajuan_surat WHERE status='Selesai'"));

// Data Pengajuan
$query_surat = mysqli_query($koneksi, "SELECT * FROM pengajuan_surat ORDER BY id DESC");

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
    <title>Kelola Surat Warga - Admin Desa Jatijaya</title>

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
            background-color: #f3f6f9;
            color: #2b2d42;
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* Top Admin Tag Badge */
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
            color: #146338;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
            font-size: 0.9rem;
        }

        .hero-banner {
            background: linear-gradient(135deg, #146338 0%, #1b5e20 100%);
            border-radius: 20px;
            padding: 28px 32px;
            color: #ffffff;
            position: relative;
            overflow: hidden;
            box-shadow: 0 10px 25px rgba(20, 99, 56, 0.15);
        }

        .hero-banner .bg-icon {
            position: absolute;
            right: 30px;
            bottom: -20px;
            font-size: 130px;
            color: rgba(255, 255, 255, 0.12);
            pointer-events: none;
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

        /* FIX AGAR TABEL FIT TANPA SCROLL HORIZONTAL */
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

        /* Badge & Elements */
        .badge-jenis-surat {
            background-color: #f1f5f9;
            color: #334155;
            font-size: 0.75rem;
            font-weight: 600;
            padding: 4px 8px;
            border-radius: 6px;
            display: inline-block;
            border: 1px solid #e2e8f0;
            max-width: 100%;
            white-space: normal;
        }

        .btn-wa {
            color: #16a34a;
            border: 1px solid #bbf7d0;
            background-color: #f0fdf4;
            padding: 3px 8px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .badge-status {
            padding: 0.35em 0.65em;
            font-size: 0.7rem;
            font-weight: 600;
            border-radius: 6px;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .badge-pending { background-color: #fff7ed; color: #c2410c; border: 1px solid #ffedd5; }
        .badge-success { background-color: #f0fdf4; color: #15803d; border: 1px solid #dcfce7; }
        .badge-danger { background-color: #fef2f2; color: #b91c1c; border: 1px solid #fee2e2; }

        /* Action Buttons Compact */
        .btn-action-sm {
            width: 28px;
            height: 28px;
            border-radius: 6px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 0.75rem;
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
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="admin-tag">
                <span class="dot"></span>
                <span>Layanan Surat Warga</span>
            </div>
            <div class="date-widget">
                <i class="fa-regular fa-calendar-days"></i>
                <span><?php echo $hari_ini; ?></span>
            </div>
        </div>

        <!-- Banner Hero -->
        <div class="hero-banner mb-4">
            <h3 class="fw-bold mb-2">Layanan Surat Warga 👋</h3>
            <p class="mb-0 text-white-50 small" style="max-width: 650px;">
                Pantau permohonan, verifikasi kelengkapan berkas warga, dan terbitkan surat resmi Desa Jatijaya secara cepat.
            </p>
            <i class="fa-solid fa-landmark bg-icon"></i>
        </div>

        <!-- Ringkasan Statistik -->
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="stat-card d-flex align-items-center gap-3">
                    <div class="p-3 bg-primary bg-opacity-10 text-primary rounded-3">
                        <i class="fa-solid fa-file-lines fs-5"></i>
                    </div>
                    <div>
                        <small class="text-muted fw-semibold d-block">Total Permohonan</small>
                        <h4 class="fw-bold text-dark mb-0"><?php echo $total_surat; ?></h4>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card d-flex align-items-center gap-3">
                    <div class="p-3 bg-warning bg-opacity-10 text-warning rounded-3">
                        <i class="fa-solid fa-clock fs-5"></i>
                    </div>
                    <div>
                        <small class="text-muted fw-semibold d-block">Menunggu Verifikasi</small>
                        <h4 class="fw-bold text-dark mb-0"><?php echo $total_pending; ?></h4>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card d-flex align-items-center gap-3">
                    <div class="p-3 bg-success bg-opacity-10 text-success rounded-3">
                        <i class="fa-solid fa-circle-check fs-5"></i>
                    </div>
                    <div>
                        <small class="text-muted fw-semibold d-block">Surat Selesai</small>
                        <h4 class="fw-bold text-dark mb-0"><?php echo $total_selesai; ?></h4>
                    </div>
                </div>
            </div>
        </div>

        <!-- Header Section Bar: Judul & Group Tombol Aksi Rapi -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-3">
            <div>
                <h5 class="fw-bold text-dark mb-0">Daftar Pengajuan Surat</h5>
                <p class="text-muted small mb-0">Kelola dan update permohonan surat masuk secara real-time.</p>
            </div>

            <!-- Grouping Tombol Sejajar Rapi -->
            <div class="d-flex align-items-center gap-2">
                <a href="export_pdf_semua.php" target="_blank" class="btn btn-sm btn-white text-danger fw-semibold px-3 py-2 rounded-3 border shadow-sm">
                    <i class="fa-solid fa-file-pdf me-1"></i> Export PDF
                </a>

                <a href="export_surat.php" class="btn btn-sm btn-white text-success fw-semibold px-3 py-2 rounded-3 border shadow-sm">
                    <i class="fa-solid fa-file-excel me-1"></i> Export Excel
                </a>

                <button type="button" class="btn btn-sm text-white fw-semibold px-3 py-2 rounded-3 shadow-sm" style="background-color: var(--primary-color);" data-bs-toggle="modal" data-bs-target="#modalTambahSurat">
                    <i class="fa-solid fa-plus me-1"></i> Tambah Pengajuan
                </button>
            </div>
        </div>

        <!-- Tabel Pas Layanan tanpa Scrollbar Horizontal -->
        <div class="card card-custom overflow-hidden">
            <div class="table-responsive">
                <table class="table table-custom table-fixed align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="ps-3" style="width: 5%;">NO</th>
                            <th style="width: 22%;">NIK & PEMOHON</th>
                            <th style="width: 20%;">JENIS SURAT</th>
                            <th style="width: 15%;">NO. WHATSAPP</th>
                            <th style="width: 16%;">KETERANGAN</th>
                            <th style="width: 10%;">STATUS</th>
                            <th class="text-center pe-3" style="width: 12%;">AKSI</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($query_surat && mysqli_num_rows($query_surat) > 0): ?>
                            <?php $no = 1; while ($s = mysqli_fetch_assoc($query_surat)): ?>
                                <tr>
                                    <!-- 1. NO -->
                                    <td class="ps-3 fw-bold text-secondary"><?php echo $no++; ?></td>

                                    <!-- 2. NIK & PEMOHON -->
                                    <td>
                                        <div class="fw-bold text-dark text-truncate" title="<?php echo htmlspecialchars($s['nama']); ?>"><?php echo htmlspecialchars($s['nama']); ?></div>
                                        <div class="text-muted small"><i class="fa-regular fa-id-card me-1"></i><?php echo htmlspecialchars($s['nik']); ?></div>
                                    </td>

                                    <!-- 3. JENIS SURAT -->
                                    <td>
                                        <span class="badge-jenis-surat">
                                            <?php echo htmlspecialchars($s['jenis_surat']); ?>
                                        </span>
                                    </td>

                                    <!-- 4. NO WA -->
                                    <td>
                                        <?php $no_wa = preg_replace('/[^0-9]/', '', $s['no_hp']); ?>
                                        <a href="https://wa.me/<?php echo $no_wa; ?>" target="_blank" class="btn-wa">
                                            <i class="fa-brands fa-whatsapp"></i>
                                            <span><?php echo htmlspecialchars($s['no_hp']); ?></span>
                                        </a>
                                    </td>

                                    <!-- 5. KETERANGAN -->
                                    <td>
                                        <span class="text-secondary small d-block text-truncate" title="<?php echo htmlspecialchars($s['keterangan']); ?>">
                                            <?php echo !empty($s['keterangan']) ? htmlspecialchars($s['keterangan']) : '-'; ?>
                                        </span>
                                    </td>

                                    <!-- 6. STATUS -->
                                    <td>
                                        <?php if ($s['status'] == 'Selesai'): ?>
                                            <span class="badge-status badge-success"><i class="fa-solid fa-circle-check"></i> Selesai</span>
                                        <?php elseif ($s['status'] == 'Ditolak'): ?>
                                            <span class="badge-status badge-danger"><i class="fa-solid fa-circle-xmark"></i> Ditolak</span>
                                        <?php else: ?>
                                            <span class="badge-status badge-pending"><i class="fa-solid fa-clock"></i> Pending</span>
                                        <?php endif; ?>
                                    </td>

                                    <!-- 7. AKSI (Ringkas & Menu Dropdown) -->
                                    <td class="text-center pe-3">
                                        <div class="d-flex justify-content-center align-items-center gap-1">
                                            <!-- Action Status Cepat -->
                                            <a href="surat.php?action_status=setujui&id=<?php echo $s['id']; ?>" class="btn btn-outline-success btn-action-sm" data-bs-toggle="tooltip" title="Setujui">
                                                <i class="fa-solid fa-check"></i>
                                            </a>
                                            <a href="surat.php?action_status=tolak&id=<?php echo $s['id']; ?>" class="btn btn-outline-danger btn-action-sm" data-bs-toggle="tooltip" title="Tolak">
                                                <i class="fa-solid fa-xmark"></i>
                                            </a>

                                            <!-- Cetak PDF (Jika Selesai) -->
                                            <?php if ($s['status'] == 'Selesai'): ?>
                                                <a href="cetak_surat.php?id=<?php echo $s['id']; ?>" target="_blank" class="btn btn-outline-primary btn-action-sm" data-bs-toggle="tooltip" title="Cetak PDF">
                                                    <i class="fa-solid fa-print"></i>
                                                </a>
                                            <?php endif; ?>

                                            <!-- Dropdown Aksi Lanjutan -->
                                            <div class="dropdown">
                                                <button class="btn btn-light btn-action-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                    <i class="fa-solid fa-ellipsis-vertical"></i>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 rounded-3 text-start small">
                                                    <li>
                                                        <a class="dropdown-item" href="surat.php?action_status=pending&id=<?php echo $s['id']; ?>">
                                                            <i class="fa-solid fa-clock text-warning me-2"></i> Set Pending
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <button type="button" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#modalEditSurat<?php echo $s['id']; ?>">
                                                            <i class="fa-solid fa-pen-to-square text-secondary me-2"></i> Edit Data
                                                        </button>
                                                    </li>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li>
                                                        <button type="button" class="dropdown-item text-danger" onclick="konfirmasiHapus(<?php echo $s['id']; ?>)">
                                                            <i class="fa-solid fa-trash-can me-2"></i> Hapus Data
                                                        </button>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>

                                        <!-- MODAL EDIT SURAT -->
                                        <div class="modal fade modal-modern text-start" id="modalEditSurat<?php echo $s['id']; ?>" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered">
                                                <div class="modal-content">
                                                    <div class="modal-header border-0 bg-white pt-4 px-4 pb-2">
                                                        <div class="d-flex align-items-center">
                                                            <div class="bg-warning bg-opacity-10 text-warning p-3 rounded-3 me-3">
                                                                <i class="fa-solid fa-pen-to-square fs-4"></i>
                                                            </div>
                                                            <div>
                                                                <h5 class="modal-title fw-bold text-dark mb-0">Edit Pengajuan Surat</h5>
                                                                <p class="text-muted small mb-0">Perbarui informasi berkas warga</p>
                                                            </div>
                                                        </div>
                                                        <button type="button" class="btn-close align-self-start" data-bs-dismiss="modal"></button>
                                                    </div>

                                                    <form action="surat.php" method="POST">
                                                        <input type="hidden" name="id_surat" value="<?php echo $s['id']; ?>">

                                                        <div class="modal-body p-4">
                                                            <div class="row g-3">
                                                                <div class="col-12">
                                                                    <label class="form-label fw-semibold small text-secondary">NIK Pemohon</label>
                                                                    <input type="number" name="nik" class="form-control" value="<?php echo htmlspecialchars($s['nik']); ?>" required>
                                                                </div>
                                                                <div class="col-12">
                                                                    <label class="form-label fw-semibold small text-secondary">Nama Lengkap</label>
                                                                    <input type="text" name="nama" class="form-control" value="<?php echo htmlspecialchars($s['nama']); ?>" required>
                                                                </div>
                                                                <div class="col-12">
                                                                    <label class="form-label fw-semibold small text-secondary">Jenis Surat</label>
                                                                    <input type="text" name="jenis_surat" class="form-control" value="<?php echo htmlspecialchars($s['jenis_surat']); ?>" required>
                                                                </div>
                                                                <div class="col-12">
                                                                    <label class="form-label fw-semibold small text-secondary">No. WhatsApp</label>
                                                                    <input type="text" name="no_hp" class="form-control" value="<?php echo htmlspecialchars($s['no_hp']); ?>" required>
                                                                </div>
                                                                <div class="col-12">
                                                                    <label class="form-label fw-semibold small text-secondary">Status Pengajuan</label>
                                                                    <select name="status" class="form-select" required>
                                                                        <option value="Pending" <?php echo ($s['status'] == 'Pending') ? 'selected' : ''; ?>>Pending / Diproses</option>
                                                                        <option value="Selesai" <?php echo ($s['status'] == 'Selesai') ? 'selected' : ''; ?>>Selesai / Diterbitkan</option>
                                                                        <option value="Ditolak" <?php echo ($s['status'] == 'Ditolak') ? 'selected' : ''; ?>>Ditolak</option>
                                                                    </select>
                                                                </div>
                                                                <div class="col-12">
                                                                    <label class="form-label fw-semibold small text-secondary">Catatan / Keterangan</label>
                                                                    <textarea name="keterangan" class="form-control" rows="2"><?php echo htmlspecialchars($s['keterangan']); ?></textarea>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="modal-footer border-0 bg-light px-4 py-3">
                                                            <button type="button" class="btn btn-white text-secondary fw-semibold rounded-3 px-4 border" data-bs-dismiss="modal">Batal</button>
                                                            <button type="submit" name="edit_surat" class="btn btn-warning text-dark fw-bold rounded-3 px-4 py-2 shadow-sm">
                                                                <i class="fa-solid fa-floppy-disk me-1"></i> Simpan Perubahan
                                                            </button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- END MODAL EDIT -->

                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center text-muted py-5">
                                    <i class="fa-regular fa-folder-open fs-2 d-block mb-2 opacity-50"></i>
                                    <span>Belum ada data pengajuan surat masuk.</span>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- MODAL TAMBAH SURAT -->
    <div class="modal fade modal-modern" id="modalTambahSurat" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0 bg-white pt-4 px-4 pb-2">
                    <div class="d-flex align-items-center">
                        <div class="bg-success bg-opacity-10 text-success p-3 rounded-3 me-3">
                            <i class="fa-solid fa-file-circle-plus fs-4"></i>
                        </div>
                        <div>
                            <h5 class="modal-title fw-bold text-dark mb-0">Tambah Pengajuan Surat</h5>
                            <p class="text-muted small mb-0">Input data pengajuan surat warga secara manual.</p>
                        </div>
                    </div>
                    <button type="button" class="btn-close align-self-start" data-bs-dismiss="modal"></button>
                </div>

                <form action="surat.php" method="POST">
                    <div class="modal-body p-4">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-semibold small text-secondary">NIK Pemohon</label>
                                <input type="number" name="nik" class="form-control" placeholder="Contoh: 3206xxxxxxxxxxxx" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold small text-secondary">Nama Lengkap</label>
                                <input type="text" name="nama" class="form-control" placeholder="Nama pemohon" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold small text-secondary">Jenis Surat</label>
                                <input type="text" name="jenis_surat" class="form-control" placeholder="Contoh: Surat Keterangan Domisili" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold small text-secondary">Nomor WhatsApp/HP</label>
                                <input type="text" name="no_hp" class="form-control" placeholder="Contoh: 08123456789" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold small text-secondary">Status Awal</label>
                                <select name="status" class="form-select">
                                    <option value="Pending">Pending / Diproses</option>
                                    <option value="Selesai">Selesai / Diterbitkan</option>
                                    <option value="Ditolak">Ditolak</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold small text-secondary">Keterangan / Keperluan</label>
                                <textarea name="keterangan" class="form-control" rows="2" placeholder="Keperluan pembuatan surat..."></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer border-0 bg-light px-4 py-3">
                        <button type="button" class="btn btn-white text-secondary fw-semibold rounded-3 px-4 border" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" name="tambah_surat" class="btn text-white fw-bold rounded-3 px-4 py-2 shadow-sm" style="background-color: var(--primary-color);">
                            <i class="fa-solid fa-check me-1"></i> Simpan Data
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- END MODAL TAMBAH -->

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });

        <?php echo $pesan_swal; ?>

        function konfirmasiHapus(id) {
            Swal.fire({
                title: 'Apakah Anda Yakin?',
                text: "Data pengajuan surat ini akan dihapus permanen!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal',
                customClass: { popup: 'rounded-4' }
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'surat.php?hapus=' + id;
                }
            });
        }
    </script>
</body>
</html>