<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Cek autentikasi admin
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

// Koneksi ke database
if (file_exists('../config/koneksi.php')) {
    include '../config/koneksi.php';
} else {
    include 'config/koneksi.php';
}

// -------------------------------------------------------------
// PROSES SIMPAN DATA (TAMBAH SURAT BARU)
// -------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah_surat'])) {
    $nik         = mysqli_real_escape_string($koneksi, trim($_POST['nik']));
    $nama        = mysqli_real_escape_string($koneksi, trim($_POST['nama']));
    $jenis_surat = mysqli_real_escape_string($koneksi, trim($_POST['jenis_surat']));
    $no_hp       = mysqli_real_escape_string($koneksi, trim($_POST['no_hp']));
    $keterangan  = mysqli_real_escape_string($koneksi, trim($_POST['keterangan']));
    $status      = mysqli_real_escape_string($koneksi, trim($_POST['status']));
    $tanggal     = date('Y-m-d H:i:s');

    $query_insert = "INSERT INTO pengajuan_surat (nik, nama, jenis_surat, no_hp, keterangan, status, tanggal) 
                     VALUES ('$nik', '$nama', '$jenis_surat', '$no_hp', '$keterangan', '$status', '$tanggal')";

    if (mysqli_query($koneksi, $query_insert)) {
        header("Location: layanan.php?pesan=sukses_tambah");
        exit();
    } else {
        $error_msg = "Gagal menambahkan data: " . mysqli_error($koneksi);
    }
}

// Ambil parameter filter & pencarian
$search        = isset($_GET['search']) ? trim($_POST['search'] ?? $_GET['search']) : '';
$filter_status = isset($_GET['status']) ? trim($_GET['status']) : '';
$filter_jenis  = isset($_GET['jenis_surat']) ? trim($_GET['jenis_surat']) : '';

// Konstruksi Query Dinamis
$where_clauses = [];

if (!empty($search)) {
    $search_clean    = mysqli_real_escape_string($koneksi, $search);
    $where_clauses[] = "(nama LIKE '%$search_clean%' OR nik LIKE '%$search_clean%')";
}

if (!empty($filter_status)) {
    $status_clean    = mysqli_real_escape_string($koneksi, $filter_status);
    $where_clauses[] = "status = '$status_clean'";
}

if (!empty($filter_jenis)) {
    $jenis_clean     = mysqli_real_escape_string($koneksi, $filter_jenis);
    $where_clauses[] = "jenis_surat = '$jenis_clean'";
}

$query = "SELECT * FROM pengajuan_surat";
if (count($where_clauses) > 0) {
    $query .= " WHERE " . implode(' AND ', $where_clauses);
}
$query .= " ORDER BY tanggal DESC";

$result = mysqli_query($koneksi, $query);

// Array Master Jenis Surat (Daftar Pilihan Utama)
$master_jenis_surat = [
    "Surat Keterangan Usaha",
    "Surat Keterangan Tidak Mampu",
    "Surat Keterangan Domisili",
    "Surat Keterangan Pengantar SKCK",
    "Surat Keterangan Belum Menikah",
    "Surat Keterangan Kematian"
];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Layanan Surat - Admin Desa Jatijaya</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- FontAwesome 6 Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- SweetAlert2 CSS & JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #f3f4f6;
            margin: 0;
            overflow-x: hidden;
        }

        .admin-wrapper {
            display: flex;
            width: 100%;
            min-height: 100vh;
        }

        .admin-main-content {
            flex-grow: 1;
            padding: 30px;
            overflow-y: auto;
        }

        .admin-topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 25px;
        }

        .page-badge {
            background-color: #e8f5e9;
            color: #059669;
            font-weight: 700;
            padding: 6px 16px;
            border-radius: 50px;
            font-size: 0.85rem;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .card-custom {
            border: none;
            border-radius: 20px;
            background-color: #ffffff;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
            padding: 24px;
        }

        /* BANNER HEADER DENGAN TOMBOL BUAT SURAT DI KANAN */
        .page-header-banner {
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
            border-radius: 20px;
            padding: 28px 32px;
            color: #ffffff;
            margin-bottom: 25px;
            position: relative;
            overflow: hidden;
        }

        .page-header-banner::after {
            content: "\f0e0";
            font-family: "Font Awesome 6 Free";
            font-weight: 900;
            position: absolute;
            right: 20px;
            bottom: -20px;
            font-size: 8rem;
            opacity: 0.15;
            color: #ffffff;
            pointer-events: none;
        }

        .btn-banner-add {
            background-color: #ffffff;
            color: #047857;
            font-weight: 700;
            border-radius: 50px;
            padding: 12px 24px;
            font-size: 0.95rem;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.12);
            transition: all 0.2s ease-in-out;
            position: relative;
            z-index: 2;
            border: none;
        }

        .btn-banner-add:hover {
            background-color: #f0fdf4;
            color: #059669;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.18);
        }

        .table > :not(caption) > * > * {
            padding: 14px 16px;
            vertical-align: middle;
        }

        .badge-status {
            padding: 6px 12px;
            border-radius: 50px;
            font-weight: 700;
            font-size: 0.78rem;
        }

        /* BAR FILTER */
        .filter-card {
            border: 1px solid #e5e7eb;
            border-radius: 50px;
            background-color: #ffffff;
            padding: 10px 16px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.02);
            margin-bottom: 20px;
        }

        .filter-input-group {
            position: relative;
            min-width: 260px;
        }

        .filter-input-group .search-icon {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
            font-size: 0.95rem;
        }

        .filter-input-group input {
            padding-left: 42px;
            border-radius: 50px;
            border: 1px solid #e5e7eb;
            height: 44px;
            font-size: 0.9rem;
        }

        .filter-select {
            border-radius: 50px;
            border: 1px solid #e5e7eb;
            height: 44px;
            font-size: 0.9rem;
            padding-left: 16px;
            padding-right: 36px;
            background-color: #ffffff;
            color: #374151;
            font-weight: 500;
        }

        .btn-filter-pill {
            border-radius: 50px;
            height: 44px;
            padding: 0 20px;
            font-weight: 600;
            font-size: 0.9rem;
            display: inline-flex;
            align-items: center;
            gap: 8px;
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
<body>

<div class="admin-wrapper">
    <!-- INKLUSI SIDEBAR -->
    <?php 
        if (file_exists(__DIR__ . '/includes/sidebar.php')) {
            include __DIR__ . '/includes/sidebar.php';
        } elseif (file_exists(__DIR__ . '/sidebar.php')) {
            include __DIR__ . '/sidebar.php';
        } elseif (file_exists(__DIR__ . '/../includes/sidebar.php')) {
            include __DIR__ . '/../includes/sidebar.php';
        }
    ?>

    <!-- KONTEN UTAMA ADMIN -->
    <main class="admin-main-content">
        
        <!-- TOPBAR -->
        <div class="admin-topbar">
            <div>
                <span class="page-badge">
                    <i class="fa-solid fa-circle"></i> Manajemen Layanan & Surat
                </span>
            </div>
            <div class="bg-white px-3 py-2 rounded-3 shadow-sm text-muted fw-semibold small">
                <i class="fa-regular fa-calendar me-1"></i> <?php echo date('d F Y'); ?>
            </div>
        </div>

        <!-- BANNER HEADER DENGAN TOMBOL TAMBAH DI SAMPING KANAN -->
        <div class="page-header-banner d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h3 class="fw-bold mb-1"><i class="fa-solid fa-envelope-open-text me-2"></i> Kelola Layanan Surat Warga</h3>
                <p class="mb-0 opacity-90 small">Daftar permohonan surat keterangan mandiri yang diajukan oleh masyarakat Desa Jatijaya.</p>
            </div>
            <div>
                <button type="button" class="btn btn-banner-add" data-bs-toggle="modal" data-bs-target="#modalTambahSurat">
                    <i class="fa-solid fa-plus me-1"></i> Buat Pengajuan Surat
                </button>
            </div>
        </div>

        <!-- BAR FILTER & EXPORT EXCEL -->
        <form method="GET" action="" class="filter-card d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div class="d-flex flex-wrap align-items-center gap-2 flex-grow-1">
                <!-- Input Cari NIK / Nama -->
                <div class="filter-input-group flex-grow-1" style="max-width: 320px;">
                    <i class="fa-solid fa-magnifying-glass search-icon"></i>
                    <input type="text" name="search" class="form-control" placeholder="Cari NIK atau Nama Pemohon..." value="<?php echo htmlspecialchars($search); ?>" onchange="this.form.submit()">
                </div>

                <!-- Dropdown Status -->
                <div>
                    <select name="status" class="form-select filter-select" onchange="this.form.submit()">
                        <option value="">Semua Status</option>
                        <option value="pending" <?php echo ($filter_status === 'pending') ? 'selected' : ''; ?>>Pending</option>
                        <option value="diproses" <?php echo ($filter_status === 'diproses') ? 'selected' : ''; ?>>Diproses</option>
                        <option value="selesai" <?php echo ($filter_status === 'selesai') ? 'selected' : ''; ?>>Selesai</option>
                        <option value="ditolak" <?php echo ($filter_status === 'ditolak') ? 'selected' : ''; ?>>Ditolak</option>
                    </select>
                </div>

                <!-- Dropdown Jenis Surat -->
                <div>
                    <select name="jenis_surat" class="form-select filter-select" onchange="this.form.submit()">
                        <option value="">Semua Jenis Surat</option>
                        <?php foreach ($master_jenis_surat as $jenis_opt): ?>
                            <option value="<?php echo htmlspecialchars($jenis_opt); ?>" <?php echo ($filter_jenis === $jenis_opt) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($jenis_opt); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <!-- Tombol Export Excel -->
            <div>
                <a href="export_excel.php" class="btn btn-emerald text-white btn-filter-pill" style="background-color: #10b981;">
                    <i class="fa-solid fa-file-excel"></i> Export Excel
                </a>
            </div>
        </form>

        <!-- TABEL DATA -->
        <div class="card card-custom">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr class="text-secondary small fw-bold text-uppercase">
                            <th width="5%" class="text-center">No</th>
                            <th>NIK & Nama Pemohon</th>
                            <th class="text-center">Jenis Surat</th>
                            <th class="text-center">Kontak</th>
                            <th class="text-center">Keperluan</th>
                            <th class="text-center">Status Progres</th>
                            <th width="12%" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result && mysqli_num_rows($result) > 0): ?>
                            <?php $no = 1; while ($row = mysqli_fetch_assoc($result)): ?>
                                <?php $id_row = $row['id'] ?? $row['id_pengajuan']; ?>
                                <tr>
                                    <td class="fw-bold text-muted text-center"><?php echo $no++; ?></td>
                                    <td>
                                        <div class="d-flex align-items-center gap-3">
                                            <!-- Avatar Inisial Bulat -->
                                            <div class="rounded-circle bg-emerald-subtle text-emerald d-flex align-items-center justify-content-center fw-bold shadow-sm flex-shrink-0" 
                                                 style="width: 42px; height: 42px; background-color: #ecfdf5; color: #059669; font-size: 0.95rem; border: 1px solid #a7f3d0;">
                                                <?php echo strtoupper(substr(trim($row['nama']), 0, 1)); ?>
                                            </div>
                                            
                                            <!-- Nama & NIK -->
                                            <div>
                                                <div class="fw-bold text-dark text-capitalize lh-sm mb-1" style="font-size: 0.95rem; letter-spacing: -0.2px;">
                                                    <?php echo htmlspecialchars(ucwords(strtolower($row['nama']))); ?>
                                                </div>
                                                <div class="d-inline-flex align-items-center gap-1 px-2 py-1 rounded bg-light border text-muted small" style="font-size: 0.78rem;">
                                                    <i class="fa-regular fa-id-card text-emerald" style="color: #059669;"></i>
                                                    <span class="font-monospace fw-semibold" style="letter-spacing: 0.5px;"><?php echo htmlspecialchars($row['nik']); ?></span>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-light text-dark border px-3 py-2 rounded-3">
                                            <?php echo htmlspecialchars($row['jenis_surat']); ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <?php if (!empty($row['no_hp'])): ?>
                                            <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $row['no_hp']); ?>" target="_blank" class="btn btn-sm btn-outline-success rounded-pill px-3 py-1 small fw-bold">
                                                <i class="fa-brands fa-whatsapp fs-6 me-1"></i> <?php echo htmlspecialchars($row['no_hp']); ?>
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted small">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center small text-secondary">
                                        <?php echo htmlspecialchars($row['keterangan']); ?>
                                    </td>
                                    <td class="text-center">
                                        <?php 
                                            $st = strtolower($row['status']);
                                            if ($st == 'selesai') {
                                                echo '<span class="badge bg-success badge-status"><i class="fa-solid fa-check me-1"></i>Selesai</span>';
                                            } elseif ($st == 'diproses') {
                                                echo '<span class="badge bg-info text-white badge-status"><i class="fa-solid fa-spinner me-1"></i>Diproses</span>';
                                            } elseif ($st == 'ditolak') {
                                                echo '<span class="badge bg-danger badge-status"><i class="fa-solid fa-xmark me-1"></i>Ditolak</span>';
                                            } else {
                                                echo '<span class="badge bg-warning text-dark badge-status"><i class="fa-solid fa-clock me-1"></i>Pending</span>';
                                            }
                                        ?>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm">
                                            <a href="edit_surat.php?id=<?php echo $id_row; ?>" class="btn btn-outline-warning" title="Edit Status"><i class="fa-solid fa-pen"></i></a>
                                            <a href="cetak_surat.php?id=<?php echo $id_row; ?>" target="_blank" class="btn btn-outline-primary" title="Cetak Surat"><i class="fa-solid fa-print"></i></a>
                                            <button type="button" class="btn btn-outline-danger btn-hapus" data-id="<?php echo $id_row; ?>" title="Hapus"><i class="fa-solid fa-trash"></i></button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="fa-solid fa-inbox fs-2 mb-2 d-block"></i>
                                    Tidak ada data pengajuan surat yang sesuai dengan filter/pencarian.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </main>
</div>

<!-- MODAL TAMBAH SURAT BARU (TAMPILAN ALA BERITA BARU) -->
<div class="modal fade" id="modalTambahSurat" tabindex="-1" aria-labelledby="modalTambahSuratLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg overflow-hidden">
            
            <!-- HEADER MODAL -->
            <div class="modal-header border-0 pb-2 pt-4 px-4 align-items-start">
                <div class="d-flex align-items-center gap-3">
                    <div class="modal-icon-box">
                        <i class="fa-solid fa-file-circle-plus"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold text-dark mb-0 fs-5" id="modalTambahSuratLabel">Tambah Pengajuan Surat Baru</h5>
                        <p class="text-muted small mb-0">Buat permohonan surat keterangan mandiri untuk warga desa.</p>
                    </div>
                </div>
                <button type="button" class="btn-close mt-1" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <!-- FORM TAMBAH DATA -->
            <form action="" method="POST">
                <div class="modal-body p-4">
                    
                    <!-- BARIS 1: NIK & NAMA -->
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label-custom">NIK Pemohon</label>
                            <input type="text" name="nik" class="form-control form-control-custom" placeholder="Masukkan 16 Digit NIK" maxlength="16" pattern="[0-9]{16}" title="NIK harus berisi 16 digit angka" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label-custom">Nama Lengkap</label>
                            <input type="text" name="nama" class="form-control form-control-custom" placeholder="Masukkan Nama Lengkap Pemohon" required>
                        </div>
                    </div>

                    <!-- BARIS 2: JENIS SURAT & NO WHATSAPP -->
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label-custom">Jenis Surat</label>
                            <select name="jenis_surat" class="form-select form-select-custom" required>
                                <option value="" disabled selected>-- Pilih Jenis Surat --</option>
                                <?php foreach ($master_jenis_surat as $j_item): ?>
                                    <option value="<?php echo htmlspecialchars($j_item); ?>"><?php echo htmlspecialchars($j_item); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label-custom">Nomor WhatsApp / HP</label>
                            <input type="text" name="no_hp" class="form-control form-control-custom" placeholder="Contoh: 081234567890" required>
                        </div>
                    </div>

                    <!-- BARIS 3: STATUS PROGRES -->
                    <div class="mb-3">
                        <label class="form-label-custom">Status Permohonan</label>
                        <select name="status" class="form-select form-select-custom">
                            <option value="pending" selected>Pending</option>
                            <option value="diproses">Diproses</option>
                            <option value="selesai">Selesai</option>
                            <option value="ditolak">Ditolak</option>
                        </select>
                    </div>

                    <!-- BARIS 4: KEPERLUAN / KETERANGAN -->
                    <div class="mb-2">
                        <label class="form-label-custom">Keperluan / Keterangan</label>
                        <textarea name="keterangan" class="form-control form-control-custom" rows="4" placeholder="Penjelasan/Tujuan pembuatan surat..." required></textarea>
                    </div>

                </div>

                <!-- FOOTER MODAL -->
                <div class="modal-footer bg-light border-0 py-3 px-4 d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-light rounded-3 fw-bold px-4 py-2 border" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="tambah_surat" class="btn btn-success rounded-3 fw-bold px-4 py-2 d-inline-flex align-items-center gap-2" style="background-color: #059669; border: none;">
                        <i class="fa-solid fa-paper-plane"></i> Simpan Data
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- SCRIPT SWEETALERT2 POPUP CONFIRMATION & NOTIFICATION -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const urlParams = new URLSearchParams(window.location.search);

        // Notifikasi Sukses Tambah Data
        if (urlParams.get('pesan') === 'sukses_tambah') {
            Swal.fire({
                title: 'Berhasil!',
                text: 'Data pengajuan surat baru berhasil ditambahkan.',
                icon: 'success',
                confirmButtonColor: '#059669'
            });
        }

        // Notifikasi Sukses Hapus
        if (urlParams.get('pesan') === 'sukses_hapus') {
            Swal.fire({
                title: 'Berhasil!',
                text: 'Data pengajuan surat telah berhasil dihapus.',
                icon: 'success',
                confirmButtonColor: '#059669'
            });
        }

        // Listener Event Klik Tombol Hapus
        const deleteButtons = document.querySelectorAll('.btn-hapus');
        deleteButtons.forEach(button => {
            button.addEventListener('click', function () {
                const id = this.getAttribute('data-id');

                Swal.fire({
                    title: 'Apakah Anda Yakin?',
                    text: 'Data pengajuan surat ini akan dihapus secara permanen!',
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
                        window.location.href = `hapus_surat.php?id=${id}`;
                    }
                });
            });
        });
    });
</script>

</body>
</html>