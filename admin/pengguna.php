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

// 1. PROSES TAMBAH PENGGUNA
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah_pengguna'])) {
    $username = mysqli_real_escape_string($koneksi, trim($_POST['username']));
    $nama     = mysqli_real_escape_string($koneksi, trim($_POST['nama']));
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Cek apakah username sudah ada
    $cek_user = mysqli_query($koneksi, "SELECT * FROM admin WHERE username = '$username'");
    if (mysqli_num_rows($cek_user) > 0) {
        $pesan_status = '
        <div class="alert alert-warning alert-dismissible fade show rounded-4 p-3 shadow-sm mb-4" role="alert">
            <i class="fa-solid fa-triangle-exclamation me-1"></i> Username sudah digunakan! Silakan gunakan username lain.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>';
    } else {
        // Cek ketersediaan kolom 'nama' di tabel admin
        $check_col = mysqli_query($koneksi, "SHOW COLUMNS FROM admin LIKE 'nama'");
        if(mysqli_num_rows($check_col) > 0) {
            $query_insert = "INSERT INTO admin (username, nama, password) VALUES ('$username', '$nama', '$password')";
        } else {
            $query_insert = "INSERT INTO admin (username, password) VALUES ('$username', '$password')";
        }

        if (mysqli_query($koneksi, $query_insert)) {
            $pesan_status = '
            <div class="alert alert-success alert-dismissible fade show rounded-4 p-3 shadow-sm mb-4" role="alert">
                <i class="fa-solid fa-circle-check me-1"></i> Admin baru berhasil ditambahkan!
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>';
        } else {
            $pesan_status = '
            <div class="alert alert-danger alert-dismissible fade show rounded-4 p-3 shadow-sm mb-4" role="alert">
                <i class="fa-solid fa-circle-xmark me-1"></i> Gagal menambahkan admin: '.mysqli_error($koneksi).'
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>';
        }
    }
}

// 2. PROSES EDIT PENGGUNA
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_pengguna'])) {
    $id_edit  = intval($_POST['id_admin']);
    $username = mysqli_real_escape_string($koneksi, trim($_POST['username']));
    $nama     = mysqli_real_escape_string($koneksi, trim($_POST['nama']));
    $password = $_POST['password'];

    // Cek ketersediaan username unik (kecuali milik diri sendiri)
    $cek_user = mysqli_query($koneksi, "SELECT * FROM admin WHERE username = '$username' AND id != '$id_edit'");
    if (mysqli_num_rows($cek_user) > 0) {
        $pesan_status = '
        <div class="alert alert-warning alert-dismissible fade show rounded-4 p-3 shadow-sm mb-4" role="alert">
            <i class="fa-solid fa-triangle-exclamation me-1"></i> Username sudah dipakai oleh admin lain!
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>';
    } else {
        $check_col = mysqli_query($koneksi, "SHOW COLUMNS FROM admin LIKE 'nama'");
        $has_nama_col = (mysqli_num_rows($check_col) > 0);

        // Update password jika diisi, jika kosong gunakan password lama
        if (!empty($password)) {
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            if ($has_nama_col) {
                $query_update = "UPDATE admin SET username = '$username', nama = '$nama', password = '$password_hash' WHERE id = '$id_edit'";
            } else {
                $query_update = "UPDATE admin SET username = '$username', password = '$password_hash' WHERE id = '$id_edit'";
            }
        } else {
            if ($has_nama_col) {
                $query_update = "UPDATE admin SET username = '$username', nama = '$nama' WHERE id = '$id_edit'";
            } else {
                $query_update = "UPDATE admin SET username = '$username' WHERE id = '$id_edit'";
            }
        }

        if (mysqli_query($koneksi, $query_update)) {
            // Update session jika admin mengubah akunnya sendiri
            $cek_self = mysqli_query($koneksi, "SELECT username FROM admin WHERE id = '$id_edit'");
            if (isset($_SESSION['admin_username']) && $_SESSION['admin_username'] === $username) {
                $_SESSION['admin_username'] = $username;
            }

            $pesan_status = '
            <div class="alert alert-success alert-dismissible fade show rounded-4 p-3 shadow-sm mb-4" role="alert">
                <i class="fa-solid fa-circle-check me-1"></i> Data pengguna berhasil diperbarui!
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>';
        } else {
            $pesan_status = '
            <div class="alert alert-danger alert-dismissible fade show rounded-4 p-3 shadow-sm mb-4" role="alert">
                <i class="fa-solid fa-circle-xmark me-1"></i> Gagal memperbarui admin: '.mysqli_error($koneksi).'
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>';
        }
    }
}

// 3. PROSES HAPUS PENGGUNA
if (isset($_GET['action']) && $_GET['action'] === 'hapus' && isset($_GET['id'])) {
    $id_hapus = intval($_GET['id']);
    
    $cek_self = mysqli_query($koneksi, "SELECT username FROM admin WHERE id = '$id_hapus'");
    $data_self = mysqli_fetch_assoc($cek_self);
    
    if (isset($_SESSION['admin_username']) && $_SESSION['admin_username'] === $data_self['username']) {
        $pesan_status = '
        <div class="alert alert-danger alert-dismissible fade show rounded-4 p-3 shadow-sm mb-4" role="alert">
            <i class="fa-solid fa-triangle-exclamation me-1"></i> Anda tidak bisa menghapus akun yang sedang digunakan saat ini!
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>';
    } else {
        if (mysqli_query($koneksi, "DELETE FROM admin WHERE id = '$id_hapus'")) {
            $pesan_status = '
            <div class="alert alert-success alert-dismissible fade show rounded-4 p-3 shadow-sm mb-4" role="alert">
                <i class="fa-solid fa-circle-check me-1"></i> Pengguna berhasil dihapus!
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>';
        }
    }
}

// AMBIL SEMUA DATA ADMIN
$result_admin = mysqli_query($koneksi, "SELECT * FROM admin ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Pengguna - Admin Desa Jatijaya</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- FontAwesome 6 Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

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

        .card-custom {
            border: none;
            border-radius: 20px;
            background-color: #ffffff;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
            padding: 24px;
        }

        .page-header-banner {
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
            border-radius: 20px;
            padding: 28px;
            color: #ffffff;
            margin-bottom: 25px;
        }

        .table > :not(caption) > * > * {
            padding: 14px 16px;
            vertical-align: middle;
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
        }
    ?>

    <!-- KONTEN UTAMA -->
    <main class="admin-main-content">
        
        <!-- BANNER HEADER -->
        <div class="page-header-banner d-flex justify-content-between align-items-center">
            <div>
                <h3 class="fw-bold mb-1"><i class="fa-solid fa-users-gear me-2"></i> Manajemen Pengguna Admin</h3>
                <p class="mb-0 opacity-90 small">Kelola daftar akun administrator yang dapat mengakses dashboard sistem desa.</p>
            </div>
            <button type="button" class="btn btn-light fw-bold text-success rounded-pill px-4 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalTambahUser">
                <i class="fa-solid fa-user-plus me-1"></i> Tambah Admin
            </button>
        </div>

        <?php echo $pesan_status; ?>

        <!-- TABEL PENGGUNA -->
        <div class="card card-custom">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr class="text-secondary small fw-bold text-uppercase">
                            <th width="5%">No</th>
                            <th>Nama Lengkap</th>
                            <th>Username</th>
                            <th>Role / Akses</th>
                            <th width="18%" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result_admin && mysqli_num_rows($result_admin) > 0): ?>
                            <?php $no = 1; while ($row = mysqli_fetch_assoc($result_admin)): ?>
                                <?php 
                                    $nama_tampil = !empty($row['nama']) ? $row['nama'] : $row['username'];
                                ?>
                                <tr>
                                    <td class="fw-bold text-muted"><?php echo $no++; ?></td>
                                    <td>
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="bg-success bg-opacity-10 text-success rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 40px; height: 40px;">
                                                <?php echo strtoupper(substr($nama_tampil, 0, 1)); ?>
                                            </div>
                                            <span class="fw-bold text-dark"><?php echo htmlspecialchars($nama_tampil); ?></span>
                                        </div>
                                    </td>
                                    <td class="fw-semibold text-secondary">
                                        @<?php echo htmlspecialchars($row['username']); ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-success bg-opacity-10 text-success fw-bold px-3 py-2 rounded-pill">
                                            Administrator
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <div class="d-inline-flex gap-1">
                                            <!-- Tombol Edit -->
                                            <button type="button" class="btn btn-sm btn-outline-warning rounded-3 fw-semibold" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#modalEditUser<?php echo $row['id']; ?>" 
                                                    title="Edit Pengguna">
                                                <i class="fa-solid fa-pen-to-square me-1"></i>
                                            </button>

                                            <!-- Tombol Hapus -->
                                            <a href="pengguna.php?action=hapus&id=<?php echo $row['id']; ?>" class="btn btn-sm btn-outline-danger rounded-3 fw-semibold" onclick="return confirm('Yakin ingin menghapus pengguna ini?')" title="Hapus">
                                                <i class="fa-solid fa-trash me-1"></i> Hapus
                                            </a>
                                        </div>

                                        <!-- MODAL EDIT ADMIN PER BARIS -->
                                        <div class="modal fade text-start" id="modalEditUser<?php echo $row['id']; ?>" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered">
                                                <div class="modal-content border-0 rounded-4 shadow-lg">
                                                    <div class="modal-header bg-warning text-dark p-4 rounded-top-4">
                                                        <h5 class="modal-title fw-bold">
                                                            <i class="fa-solid fa-user-pen me-2"></i> Edit Administrator
                                                        </h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    
                                                    <form action="pengguna.php" method="POST">
                                                        <input type="hidden" name="id_admin" value="<?php echo $row['id']; ?>">
                                                        <div class="modal-body p-4">
                                                            <div class="mb-3">
                                                                <label class="form-label fw-semibold text-secondary small">Nama Lengkap Admin <span class="text-danger">*</span></label>
                                                                <input type="text" name="nama" class="form-control rounded-3 py-2" value="<?php echo htmlspecialchars($nama_tampil); ?>" required>
                                                            </div>

                                                            <div class="mb-3">
                                                                <label class="form-label fw-semibold text-secondary small">Username Login <span class="text-danger">*</span></label>
                                                                <input type="text" name="username" class="form-control rounded-3 py-2" value="<?php echo htmlspecialchars($row['username']); ?>" required>
                                                            </div>

                                                            <div class="mb-3">
                                                                <label class="form-label fw-semibold text-secondary small">Password Baru <span class="text-muted fs-7">(Kosongkan jika tidak ingin diubah)</span></label>
                                                                <input type="password" name="password" class="form-control rounded-3 py-2" placeholder="Masukkan password baru">
                                                            </div>
                                                        </div>
                                                        
                                                        <div class="modal-footer bg-light p-3 rounded-bottom-4">
                                                            <button type="button" class="btn btn-secondary rounded-3 px-4 fw-bold" data-bs-dismiss="modal">Batal</button>
                                                            <button type="submit" name="edit_pengguna" class="btn btn-warning rounded-3 px-4 fw-bold">
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
                                <td colspan="5" class="text-center py-5 text-muted">
                                    Belum ada data admin.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </main>
</div>

<!-- MODAL TAMBAH ADMIN -->
<div class="modal fade" id="modalTambahUser" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow-lg">
            <div class="modal-header bg-success text-white p-4 rounded-top-4">
                <h5 class="modal-title fw-bold">
                    <i class="fa-solid fa-user-plus me-2"></i> Tambah Administrator Baru
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <form action="pengguna.php" method="POST">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-secondary small">Nama Lengkap Admin <span class="text-danger">*</span></label>
                        <input type="text" name="nama" class="form-control rounded-3 py-2" placeholder="Contoh: Ahmad Subagja" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold text-secondary small">Username Login <span class="text-danger">*</span></label>
                        <input type="text" name="username" class="form-control rounded-3 py-2" placeholder="Contoh: admin_desa" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold text-secondary small">Password <span class="text-danger">*</span></label>
                        <input type="password" name="password" class="form-control rounded-3 py-2" placeholder="Minimal 6 karakter" required>
                    </div>
                </div>
                
                <div class="modal-footer bg-light p-3 rounded-bottom-4">
                    <button type="button" class="btn btn-secondary rounded-3 px-4 fw-bold" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="tambah_pengguna" class="btn btn-success rounded-3 px-4 fw-bold">
                        <i class="fa-solid fa-floppy-disk me-1"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>