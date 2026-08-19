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

// 1. PROSES TAMBAH PENGGUNA BARU
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah_user'])) {
    $username     = mysqli_real_escape_string($koneksi, $_POST['username']);
    $nama_lengkap = mysqli_real_escape_string($koneksi, $_POST['nama_lengkap']);
    $password_raw = $_POST['password'];

    // Enkripsi password menggunakan bcrypt
    $password_hash = password_hash($password_raw, PASSWORD_BCRYPT);

    // Cek apakah username sudah dipakai
    $cek_username = mysqli_query($koneksi, "SELECT id FROM admin WHERE username='$username'");
    if (mysqli_num_rows($cek_username) > 0) {
        header("Location: users.php?msg=username_exist");
        exit;
    } else {
        $insert = mysqli_query($koneksi, "INSERT INTO admin (username, password, nama_lengkap) VALUES ('$username', '$password_hash', '$nama_lengkap')");
        if ($insert) {
            header("Location: users.php?msg=add_success");
            exit;
        } else {
            header("Location: users.php?msg=error");
            exit;
        }
    }
}

// 2. PROSES EDIT PENGGUNA
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_user'])) {
    $id_user      = mysqli_real_escape_string($koneksi, $_POST['id_user']);
    $username     = mysqli_real_escape_string($koneksi, $_POST['username']);
    $nama_lengkap = mysqli_real_escape_string($koneksi, $_POST['nama_lengkap']);
    $password_raw = $_POST['password'];

    if (!empty($password_raw)) {
        // Jika password diisi, perbarui password
        $password_hash = password_hash($password_raw, PASSWORD_BCRYPT);
        $update = mysqli_query($koneksi, "UPDATE admin SET username='$username', nama_lengkap='$nama_lengkap', password='$password_hash' WHERE id='$id_user'");
    } else {
        // Jika password kosong, jangan ubah password lama
        $update = mysqli_query($koneksi, "UPDATE admin SET username='$username', nama_lengkap='$nama_lengkap' WHERE id='$id_user'");
    }

    if ($update) {
        header("Location: users.php?msg=edit_success");
        exit;
    } else {
        header("Location: users.php?msg=error");
        exit;
    }
}

// 3. PROSES HAPUS PENGGUNA
if (isset($_GET['hapus'])) {
    $id_hapus = mysqli_real_escape_string($koneksi, $_GET['hapus']);
    
    // Hitung sisa akun admin agar tidak terhapus semua
    $cek_total = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM admin");
    $total_admin = mysqli_fetch_assoc($cek_total)['total'] ?? 0;

    if ($total_admin <= 1) {
        header("Location: users.php?msg=min_one");
        exit;
    }

    $hapus = mysqli_query($koneksi, "DELETE FROM admin WHERE id='$id_hapus'");
    if ($hapus) {
        header("Location: users.php?msg=delete_success");
        exit;
    }
}

// NOTIFIKASI SWEETALERT
if (isset($_GET['msg'])) {
    $msg = $_GET['msg'];
    if ($msg == 'add_success') {
        $pesan_swal = "Swal.fire({icon: 'success', title: 'Berhasil!', text: 'Pengguna baru berhasil ditambahkan.', showConfirmButton: false, timer: 1800});";
    } elseif ($msg == 'edit_success') {
        $pesan_swal = "Swal.fire({icon: 'success', title: 'Berhasil!', text: 'Data pengguna berhasil diperbarui.', showConfirmButton: false, timer: 1800});";
    } elseif ($msg == 'delete_success') {
        $pesan_swal = "Swal.fire({icon: 'success', title: 'Terhapus!', text: 'Pengguna berhasil dihapus.', showConfirmButton: false, timer: 1800});";
    } elseif ($msg == 'username_exist') {
        $pesan_swal = "Swal.fire({icon: 'error', title: 'Gagal!', text: 'Username sudah digunakan oleh akun lain.'});";
    } elseif ($msg == 'min_one') {
        $pesan_swal = "Swal.fire({icon: 'warning', title: 'Aksi Ditolak!', text: 'Sistem harus memiliki minimal 1 akun admin aktif.'});";
    } elseif ($msg == 'error') {
        $pesan_swal = "Swal.fire({icon: 'error', title: 'Gagal!', text: 'Terjadi kesalahan sistem.'});";
    }
}

// Ambil seluruh data pengguna
$query_users = mysqli_query($koneksi, "SELECT * FROM admin ORDER BY id DESC");
$total_users = ($query_users) ? mysqli_num_rows($query_users) : 0;

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
    <title>Kelola Pengguna - Admin Desa Jatijaya</title>

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

        .card-custom {
            border: none;
            border-radius: 18px;
            background: #ffffff;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.03);
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
                <span>Manajemen Akses Sistem</span>
            </div>
            <div class="date-widget">
                <i class="fa-regular fa-calendar-days"></i>
                <span><?php echo $hari_ini; ?></span>
            </div>
        </div>

        <!-- Banner Hero -->
        <div class="hero-banner mb-4">
            <h3 class="fw-bold mb-2">Kelola Pengguna & Petugas 👤</h3>
            <p class="mb-0 text-white-50 small" style="max-width: 650px;">
                Kelola akun administrator dan hak akses pengguna pengelola sistem informasi Desa Jatijaya.
            </p>
            <i class="fa-solid fa-users-gear bg-icon"></i>
        </div>

        <!-- Header Action Bar -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
            <div>
                <h5 class="fw-bold text-dark mb-0">Daftar Akun Pengguna</h5>
                <p class="text-muted small mb-0">Total terdaftar <strong><?php echo $total_users; ?></strong> akun pengelola.</p>
            </div>

            <div>
                <button type="button" class="btn btn-sm text-white fw-semibold px-3 py-2 rounded-3 shadow-sm" style="background-color: var(--primary-color);" data-bs-toggle="modal" data-bs-target="#modalTambah">
                    <i class="fa-solid fa-user-plus me-1"></i> Tambah Pengguna Baru
                </button>
            </div>
        </div>

        <!-- Tabel Pengguna -->
        <div class="card card-custom p-4">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th width="5%" class="ps-3">No</th>
                            <th width="35%">Nama Lengkap</th>
                            <th width="25%">Username</th>
                            <th width="20%">Role / Jabatan</th>
                            <th width="15%" class="text-center me-3">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($total_users > 0): ?>
                            <?php $no = 1; while ($u = mysqli_fetch_assoc($query_users)): ?>
                                <tr>
                                    <td class="ps-3 fw-bold text-muted"><?php echo $no++; ?></td>
                                    <td class="fw-bold text-dark">
                                        <i class="fa-solid fa-circle-user text-success me-2 fs-5"></i>
                                        <?php echo htmlspecialchars($u['nama_lengkap']); ?>
                                    </td>
                                    <td><code>@<?php echo htmlspecialchars($u['username']); ?></code></td>
                                    <td>
                                        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-3 py-2">
                                            <i class="fa-solid fa-user-shield me-1"></i> Administrator
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center gap-1">
                                            <!-- Edit -->
                                            <button type="button" class="btn btn-sm btn-outline-warning rounded-3" data-bs-toggle="modal" data-bs-target="#modalEdit<?php echo $u['id']; ?>" title="Edit Pengguna">
                                                <i class="fa-solid fa-pen"></i>
                                            </button>
                                            <!-- Hapus -->
                                            <button type="button" onclick="konfirmasiHapus(<?php echo $u['id']; ?>)" class="btn btn-sm btn-outline-danger rounded-3" title="Hapus Pengguna">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>

                                <!-- MODAL EDIT PENGGUNA -->
                                <div class="modal fade modal-modern" id="modalEdit<?php echo $u['id']; ?>" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content shadow-lg">
                                            <div class="modal-header border-0 bg-white pt-4 px-4 pb-2">
                                                <div class="d-flex align-items-center">
                                                    <div class="bg-warning bg-opacity-10 text-warning p-3 rounded-3 me-3">
                                                        <i class="fa-solid fa-user-pen fs-4"></i>
                                                    </div>
                                                    <div>
                                                        <h5 class="modal-title fw-bold text-dark mb-0">Edit Akun Pengguna</h5>
                                                        <p class="text-muted small mb-0">Perbarui profil dan password pengguna.</p>
                                                    </div>
                                                </div>
                                                <button type="button" class="btn-close align-self-start" data-bs-dismiss="modal"></button>
                                            </div>

                                            <form action="users.php" method="POST">
                                                <div class="modal-body p-4">
                                                    <input type="hidden" name="id_user" value="<?php echo $u['id']; ?>">
                                                    <div class="mb-3">
                                                        <label class="form-label fw-bold small text-secondary">Nama Lengkap</label>
                                                        <input type="text" name="nama_lengkap" class="form-control" value="<?php echo htmlspecialchars($u['nama_lengkap']); ?>" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label fw-bold small text-secondary">Username</label>
                                                        <input type="text" name="username" class="form-control" value="<?php echo htmlspecialchars($u['username']); ?>" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label fw-bold small text-secondary">Password Baru <span class="fw-normal text-muted">(Kosongkan jika tidak diganti)</span></label>
                                                        <input type="password" name="password" class="form-control" placeholder="Masukkan password baru">
                                                    </div>
                                                </div>

                                                <div class="modal-footer border-0 bg-light px-4 py-3">
                                                    <button type="button" class="btn btn-white text-secondary fw-semibold rounded-3 px-4 border" data-bs-dismiss="modal">Batal</button>
                                                    <button type="submit" name="edit_user" class="btn btn-warning text-dark fw-bold rounded-3 px-4 py-2 shadow-sm">
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
                                <td colspan="5" class="text-center py-5 text-muted">
                                    <i class="fa-solid fa-users-slash fa-3x mb-3 opacity-50"></i>
                                    <p class="mb-0">Belum ada data pengguna / tabel database belum terpasang.</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- MODAL TAMBAH PENGGUNA -->
    <div class="modal fade modal-modern" id="modalTambah" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content shadow-lg">
                <div class="modal-header border-0 bg-white pt-4 px-4 pb-2">
                    <div class="d-flex align-items-center">
                        <div class="bg-success bg-opacity-10 text-success p-3 rounded-3 me-3">
                            <i class="fa-solid fa-user-plus fs-4"></i>
                        </div>
                        <div>
                            <h5 class="modal-title fw-bold text-dark mb-0">Tambah Pengguna Baru</h5>
                            <p class="text-muted small mb-0">Lengkapi data akun untuk pengelola baru.</p>
                        </div>
                    </div>
                    <button type="button" class="btn-close align-self-start" data-bs-dismiss="modal"></button>
                </div>

                <form action="users.php" method="POST">
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-secondary">Nama Lengkap</label>
                            <input type="text" name="nama_lengkap" class="form-control" placeholder="Contoh: Budi Santoso" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-secondary">Username</label>
                            <input type="text" name="username" class="form-control" placeholder="Contoh: budi_admin" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-secondary">Password</label>
                            <input type="password" name="password" class="form-control" placeholder="Masukkan password" required>
                        </div>
                    </div>

                    <div class="modal-footer border-0 bg-light px-4 py-3">
                        <button type="button" class="btn btn-white text-secondary fw-semibold rounded-3 px-4 border" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" name="tambah_user" class="btn text-white fw-bold rounded-3 px-4 py-2 shadow-sm" style="background-color: var(--primary-color);">
                            <i class="fa-solid fa-check me-1"></i> Tambah Pengguna
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
                text: "Akun pengguna ini akan dihapus secara permanen!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal',
                customClass: { popup: 'rounded-4' }
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'users.php?hapus=' + id;
                }
            });
        }
    </script>
</body>
</html>