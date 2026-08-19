<?php
session_start();

// 1. Panggil koneksi database
require_once '../config/koneksi.php';

/** @var mysqli $koneksi */

// 2. Cek apakah admin sudah login
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit;
}

$pesan = "";
$tipe_pesan = "";

// 3. Proses Simpan / Update Pengaturan
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['simpan_pengaturan'])) {
    $nama_desa     = mysqli_real_escape_string($koneksi, $_POST['nama_desa']);
    $kecamatan     = mysqli_real_escape_string($koneksi, $_POST['kecamatan']);
    $kabupaten     = mysqli_real_escape_string($koneksi, $_POST['kabupaten']);
    $alamat_kantor = mysqli_real_escape_string($koneksi, $_POST['alamat_kantor']);
    $nama_kades    = mysqli_real_escape_string($koneksi, $_POST['nama_kades']);

    // Ambil data logo lama
    $cek_existing = mysqli_query($koneksi, "SELECT logo FROM pengaturan WHERE id=1");
    $data_existing = mysqli_fetch_assoc($cek_existing);
    $logo_nama_db = $data_existing['logo'] ?? '';

    // Upload Logo Baru
    if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
        $file_tmp   = $_FILES['logo']['tmp_name'];
        $file_name  = $_FILES['logo']['name'];
        $file_ext   = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $ext_izink  = ['jpg', 'jpeg', 'png', 'svg', 'webp'];

        if (in_array($file_ext, $ext_izink)) {
            // Nama file baru agar unik
            $nama_logo_baru = "logo_desa_" . time() . "." . $file_ext;
            $target_dir     = "../uploads/";

            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0777, true);
            }

            // Hapus logo lama jika ada
            if (!empty($logo_nama_db) && file_exists($target_dir . $logo_nama_db)) {
                unlink($target_dir . $logo_nama_db);
            }

            if (move_uploaded_file($file_tmp, $target_dir . $nama_logo_baru)) {
                $logo_nama_db = $nama_logo_baru;
            } else {
                $pesan = "Gagal mengunggah file logo!";
                $tipe_pesan = "danger";
            }
        } else {
            $pesan = "Format logo harus berupa JPG, JPEG, PNG, WEBP, atau SVG!";
            $tipe_pesan = "warning";
        }
    }

    if (empty($pesan)) {
        // Cek apakah data ID 1 sudah ada di database
        $cek_data = mysqli_query($koneksi, "SELECT id FROM pengaturan WHERE id=1");

        if (mysqli_num_rows($cek_data) > 0) {
            // Update Data
            $query = "UPDATE pengaturan SET 
                        nama_desa='$nama_desa', 
                        kecamatan='$kecamatan', 
                        kabupaten='$kabupaten', 
                        alamat_kantor='$alamat_kantor', 
                        nama_kades='$nama_kades',
                        logo='$logo_nama_db' 
                      WHERE id=1";
        } else {
            // Insert Data Baru
            $query = "INSERT INTO pengaturan (id, nama_desa, kecamatan, kabupaten, alamat_kantor, nama_kades, logo) 
                      VALUES (1, '$nama_desa', '$kecamatan', '$kabupaten', '$alamat_kantor', '$nama_kades', '$logo_nama_db')";
        }

        if (mysqli_query($koneksi, $query)) {
            $pesan = "Pengaturan profil desa dan logo berhasil diperbarui!";
            $tipe_pesan = "success";
        } else {
            $pesan = "Gagal memperbarui pengaturan: " . mysqli_error($koneksi);
            $tipe_pesan = "danger";
        }
    }
}

// 4. Ambil Data Pengaturan
$query_p = mysqli_query($koneksi, "SELECT * FROM pengaturan WHERE id=1");

if ($query_p && mysqli_num_rows($query_p) > 0) {
    $p = mysqli_fetch_assoc($query_p);
} else {
    $p = [
        'nama_desa'     => 'Desa Jatijaya',
        'kecamatan'     => 'Gunung Tanjung',
        'kabupaten'     => 'Tasikmalaya',
        'alamat_kantor' => 'Jl. Raya Gunung Tanjung No. 123, Desa Jatijaya',
        'nama_kades'    => 'H. AHMAD SUBAGJA',
        'logo'          => ''
    ];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaturan Desa - Admin Jatijaya</title>
    <!-- Google Fonts (Inter) -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8f9fa;
        }

        .card-custom {
            border: none;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.04);
            transition: all 0.3s ease;
        }

        .header-gradient {
            background: linear-gradient(135deg, #198754 0%, #0f5132 100%);
            border-radius: 20px;
            color: #fff;
        }

        .form-control:focus, .form-select:focus {
            border-color: #198754;
            box-shadow: 0 0 0 0.25rem rgba(25, 135, 84, 0.15);
        }

        .logo-preview-box {
            width: 140px;
            height: 140px;
            border: 2px dashed #dee2e6;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #f8f9fa;
            overflow: hidden;
            position: relative;
        }

        .logo-preview-box img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }

        .btn-success-custom {
            background-color: #198754;
            border: none;
            padding: 12px 28px;
            border-radius: 12px;
            font-weight: 600;
            box-shadow: 0 4px 12px rgba(25, 135, 84, 0.25);
            transition: all 0.2s ease;
        }

        .btn-success-custom:hover {
            background-color: #157347;
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(25, 135, 84, 0.35);
        }
    </style>
</head>
<body class="d-flex">

    <!-- Sidebar Admin -->
    <?php include 'sidebar.php'; ?>

    <!-- Area Konten Utama -->
    <div class="flex-grow-1 p-4" style="min-width: 0;">
        
        <!-- Banner Header Modern -->
        <div class="header-gradient p-4 mb-4 d-flex justify-content-between align-items-center shadow-sm">
            <div>
                <h3 class="fw-bold mb-1"><i class="fa-solid fa-sliders me-2"></i> Pengaturan Profil & Kop Surat</h3>
                <p class="mb-0 opacity-75 small">Kelola identitas resmi, alamat kantor, penandatangan, serta logo instansi desa.</p>
            </div>
            <div class="d-none d-md-block opacity-25 me-3">
                <i class="fa-solid fa-building-columns fs-1"></i>
            </div>
        </div>

        <!-- Notifikasi Pesan -->
        <?php if ($pesan): ?>
            <div class="alert alert-<?php echo $tipe_pesan; ?> alert-dismissible fade show border-0 shadow-sm rounded-4 mb-4" role="alert">
                <div class="d-flex align-items-center">
                    <i class="fa-solid <?php echo ($tipe_pesan == 'success') ? 'fa-circle-check text-success' : 'fa-triangle-exclamation text-danger'; ?> fs-4 me-3"></i>
                    <div><?php echo $pesan; ?></div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- Form Card Modern -->
        <div class="card card-custom bg-white p-4 p-md-5" style="max-width: 900px;">
            <form action="pengaturan.php" method="POST" enctype="multipart/form-data">
                
                <!-- BAGIAN 1: UPLOAD LOGO DESA -->
                <div class="mb-4 pb-4 border-bottom">
                    <label class="form-label fw-bold text-dark mb-1">
                        <i class="fa-solid fa-image me-2 text-success"></i> Logo Resmikan Desa / Instansi
                    </label>
                    <p class="text-muted small mb-3">Logo ini akan tampil pada Kop Surat resmi dan Halaman Utama Website.</p>

                    <div class="d-flex flex-column flex-sm-row align-items-sm-center gap-4">
                        <!-- Preview Box -->
                        <div class="logo-preview-box shadow-sm" id="previewContainer">
                            <?php if (!empty($p['logo']) && file_exists("../uploads/" . $p['logo'])): ?>
                                <img src="../uploads/<?php echo htmlspecialchars($p['logo']); ?>" id="imgPreview" alt="Logo Desa">
                            <?php else: ?>
                                <div class="text-center text-muted" id="placeholderText">
                                    <i class="fa-solid fa-cloud-arrow-up fs-2 d-block mb-1 opacity-50"></i>
                                    <span style="font-size: 11px;">Belum ada logo</span>
                                </div>
                                <img src="" id="imgPreview" alt="Logo Desa" class="d-none">
                            <?php endif; ?>
                        </div>

                        <!-- File Input Control -->
                        <div class="flex-grow-1">
                            <input type="file" name="logo" id="logoInput" class="form-control rounded-3 py-2" accept="image/*">
                            <div class="form-text mt-2 small text-secondary">
                                <i class="fa-solid fa-circle-info me-1"></i> Format yang didukung: <strong>PNG, JPG, WEBP, SVG</strong> (Maksimal 2 MB).
                            </div>
                        </div>
                    </div>
                </div>

                <!-- BAGIAN 2: INFORMASI WILAYAH -->
                <div class="row g-3 mb-4">
                    <div class="col-md-12">
                        <label class="form-label fw-semibold small text-secondary">NAMA DESA</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0 rounded-start-3"><i class="fa-solid fa-house-flag text-secondary"></i></span>
                            <input type="text" name="nama_desa" class="form-control border-start-0 rounded-end-3 py-2" value="<?php echo htmlspecialchars($p['nama_desa'] ?? ''); ?>" placeholder="Contoh: Desa Jatijaya" required>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold small text-secondary">KECAMATAN</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0 rounded-start-3"><i class="fa-solid fa-map-location-dot text-secondary"></i></span>
                            <input type="text" name="kecamatan" class="form-control border-start-0 rounded-end-3 py-2" value="<?php echo htmlspecialchars($p['kecamatan'] ?? ''); ?>" placeholder="Contoh: Gunung Tanjung" required>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold small text-secondary">KABUPATEN / KOTA</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0 rounded-start-3"><i class="fa-solid fa-city text-secondary"></i></span>
                            <input type="text" name="kabupaten" class="form-control border-start-0 rounded-end-3 py-2" value="<?php echo htmlspecialchars($p['kabupaten'] ?? ''); ?>" placeholder="Contoh: Tasikmalaya" required>
                        </div>
                    </div>
                </div>

                <!-- BAGIAN 3: ALAMAT & KEPALA DESA -->
                <div class="mb-4">
                    <label class="form-label fw-semibold small text-secondary">ALAMAT KANTOR DESA & KODE POS</label>
                    <textarea name="alamat_kantor" class="form-control rounded-3 p-3" rows="3" placeholder="Masukkan alamat lengkap kantor desa..." required><?php echo htmlspecialchars($p['alamat_kantor'] ?? ''); ?></textarea>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-semibold small text-secondary">NAMA KEPALA DESA (PENANDATANGAN SURAT)</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 rounded-start-3"><i class="fa-solid fa-user-tie text-secondary"></i></span>
                        <input type="text" name="nama_kades" class="form-control border-start-0 rounded-end-3 py-2" value="<?php echo htmlspecialchars($p['nama_kades'] ?? ''); ?>" placeholder="Contoh: H. AHMAD SUBAGJA" required>
                    </div>
                </div>

                <!-- TOMBOL SIMPAN -->
                <div class="pt-3 border-top d-flex justify-content-end">
                    <button type="submit" name="simpan_pengaturan" class="btn btn-success-custom text-white">
                        <i class="fa-solid fa-floppy-disk me-2"></i> Simpan Perubahan Profil
                    </button>
                </div>

            </form>
        </div>
    </div>

    <!-- Script Live Preview Gambar -->
    <script>
        document.getElementById('logoInput').addEventListener('change', function(event) {
            const file = event.target.files[0];
            const imgPreview = document.getElementById('imgPreview');
            const placeholderText = document.getElementById('placeholderText');

            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    imgPreview.src = e.target.result;
                    imgPreview.classList.remove('d-none');
                    if (placeholderText) {
                        placeholderText.classList.add('d-none');
                    }
                }
                reader.readAsDataURL(file);
            }
        });
    </script>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>