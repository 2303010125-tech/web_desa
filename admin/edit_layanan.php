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

$pesan_status = '';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Otomatis buat kolom 'alamat' di database jika belum ada
$cek_kolom = mysqli_query($koneksi, "SHOW COLUMNS FROM pengajuan_surat LIKE 'alamat'");
if (mysqli_num_rows($cek_kolom) == 0) {
    mysqli_query($koneksi, "ALTER TABLE pengajuan_surat ADD COLUMN alamat TEXT NULL AFTER keterangan");
}

// Ambil data pengajuan berdasarkan ID
$query_data = mysqli_query($koneksi, "SELECT * FROM pengajuan_surat WHERE id = $id");
if (!$query_data || mysqli_num_rows($query_data) == 0) {
    header("Location: layanan.php");
    exit();
}
$data = mysqli_fetch_assoc($query_data);

// PROSES UPDATE DATA
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_surat'])) {
    $nik         = mysqli_real_escape_string($koneksi, trim($_POST['nik']));
    $nama        = mysqli_real_escape_string($koneksi, trim($_POST['nama']));
    $jenis_surat = mysqli_real_escape_string($koneksi, trim($_POST['jenis_surat']));
    $no_hp       = mysqli_real_escape_string($koneksi, trim($_POST['no_hp']));
    $keterangan  = mysqli_real_escape_string($koneksi, trim($_POST['keterangan']));
    $status      = mysqli_real_escape_string($koneksi, trim($_POST['status']));
    $alamat      = mysqli_real_escape_string($koneksi, trim($_POST['alamat']));

    $query_update = "UPDATE pengajuan_surat SET 
                        nik = '$nik', 
                        nama = '$nama', 
                        jenis_surat = '$jenis_surat', 
                        no_hp = '$no_hp', 
                        keterangan = '$keterangan', 
                        status = '$status', 
                        alamat = '$alamat' 
                     WHERE id = $id";

    if (mysqli_query($koneksi, $query_update)) {
        $pesan_status = '<div class="alert alert-success alert-dismissible fade show rounded-3 mb-4" role="alert">
                            <i class="fa-solid fa-circle-check me-2"></i> Data pengajuan surat berhasil diperbarui!
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                         </div>';
        
        // Ambil data terbaru setelah di-update
        $query_data = mysqli_query($koneksi, "SELECT * FROM pengajuan_surat WHERE id = $id");
        $data = mysqli_fetch_assoc($query_data);
    } else {
        $pesan_status = '<div class="alert alert-danger alert-dismissible fade show rounded-3 mb-4" role="alert">
                            <i class="fa-solid fa-triangle-exclamation me-2"></i> Gagal memperbarui data: '.mysqli_error($koneksi).'
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                         </div>';
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Pengajuan Surat - Admin Desa Jatijaya</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #f3f4f6;
            margin: 0;
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
            padding: 28px;
        }
        .form-label {
            color: #374151;
            font-weight: 600;
            font-size: 0.88rem;
        }
        .form-control, .form-select {
            border-radius: 12px;
            border: 1px solid #e5e7eb;
            padding: 10px 16px;
            font-size: 0.93rem;
        }
        .form-control:focus, .form-select:focus {
            border-color: #059669;
            box-shadow: 0 0 0 0.25rem rgba(5, 150, 105, 0.15);
        }
    </style>
</head>
<body>

<div class="admin-wrapper">
    <?php 
        if (file_exists(__DIR__ . '/includes/sidebar.php')) {
            include __DIR__ . '/includes/sidebar.php';
        } elseif (file_exists(__DIR__ . '/sidebar.php')) {
            include __DIR__ . '/sidebar.php';
        } elseif (file_exists(__DIR__ . '/../includes/sidebar.php')) {
            include __DIR__ . '/../includes/sidebar.php';
        }
    ?>

    <main class="admin-main-content">
        <div class="admin-topbar">
            <div>
                <span class="page-badge">
                    <i class="fa-solid fa-pen-to-square"></i> Edit Status & Data Surat
                </span>
            </div>
            <a href="layanan.php" class="btn btn-light border rounded-pill px-3 fw-bold text-secondary small">
                <i class="fa-solid fa-arrow-left me-1"></i> Kembali
            </a>
        </div>

        <?php echo $pesan_status; ?>

        <div class="card card-custom">
            <h5 class="fw-bold text-dark mb-4 pb-2 border-bottom">
                <i class="fa-solid fa-file-signature text-success me-2"></i> Formulir Perubahan Permohonan Surat
            </h5>

            <form action="" method="POST">
                <div class="row g-3">
                    <div class="col-md-12 mb-2">
                        <div class="p-3 bg-light rounded-4 border">
                            <label class="form-label text-success fw-bold">Status Progres Permohonan <span class="text-danger">*</span></label>
                            <select name="status" class="form-select fw-bold py-2 border-success" required>
                                <option value="Pending" <?php echo (strtolower($data['status'] ?? '') == 'pending') ? 'selected' : ''; ?>>🟡 Pending (Menunggu Ditinjau)</option>
                                <option value="Diproses" <?php echo (strtolower($data['status'] ?? '') == 'diproses') ? 'selected' : ''; ?>>🔵 Diproses (Dalam Pengerjaan)</option>
                                <option value="Selesai" <?php echo (strtolower($data['status'] ?? '') == 'selesai') ? 'selected' : ''; ?>>🟢 Selesai (Surat Siap/Diambil)</option>
                                <option value="Ditolak" <?php echo (strtolower($data['status'] ?? '') == 'ditolak') ? 'selected' : ''; ?>>🔴 Ditolak (Persyaratan Kurang/Salah)</option>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">NIK Pemohon</label>
                        <input type="text" name="nik" class="form-control" value="<?php echo htmlspecialchars($data['nik'] ?? ''); ?>" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Nama Lengkap Pemohon</label>
                        <input type="text" name="nama" class="form-control" value="<?php echo htmlspecialchars($data['nama'] ?? ''); ?>" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Jenis Surat</label>
                        <input type="text" name="jenis_surat" class="form-control" value="<?php echo htmlspecialchars($data['jenis_surat'] ?? ''); ?>" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Nomor WhatsApp / HP</label>
                        <input type="text" name="no_hp" class="form-control" value="<?php echo htmlspecialchars($data['no_hp'] ?? ''); ?>" required>
                    </div>

                    <div class="col-md-12">
                        <label class="form-label">Keperluan / Alasan Pengajuan</label>
                        <input type="text" name="keterangan" class="form-control" value="<?php echo htmlspecialchars($data['keterangan'] ?? ''); ?>" required>
                    </div>

                    <div class="col-md-12">
                        <label class="form-label">Alamat Lengkap</label>
                        <textarea name="alamat" class="form-control" rows="3" placeholder="Masukkan alamat lengkap pemohon..."><?php echo htmlspecialchars($data['alamat'] ?? ''); ?></textarea>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                    <a href="layanan.php" class="btn btn-light border px-4 rounded-3 fw-bold text-secondary">Batal</a>
                    <button type="submit" name="update_surat" class="btn btn-success px-4 rounded-3 fw-bold" style="background-color: #059669;">
                        <i class="fa-solid fa-floppy-disk me-1"></i> Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>