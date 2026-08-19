<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/** @var mysqli $koneksi */

// Jika sudah login, langsung arahkan ke dashboard admin
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: dashboard.php");
    exit();
}

// Panggil koneksi database
include '../config/koneksi.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = mysqli_real_escape_string($koneksi, trim($_POST['username']));
    $password = trim($_POST['password']);

    if (!empty($username) && !empty($password)) {
        // Sesuaikan nama tabel dan kolom sesuai database kamu (misal: admin / users)
        $query = "SELECT * FROM admin WHERE username = '$username' LIMIT 1";
        $result = mysqli_query($koneksi, $query);

        if ($result && mysqli_num_rows($result) === 1) {
            $user = mysqli_fetch_assoc($result);

            // Verifikasi Password (bisa md5 atau password_verify)
            if (password_verify($password, $user['password']) || md5($password) === $user['password'] || $password === $user['password']) {
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_id'] = $user['id'];
                $_SESSION['admin_username'] = $user['username'];
                $_SESSION['admin_nama'] = isset($user['nama']) ? $user['nama'] : 'Administrator';

                header("Location: dashboard.php");
                exit();
            } else {
                $error = "Password yang Anda masukkan salah!";
            }
        } else {
            $error = "Username tidak ditemukan!";
        }
    } else {
        $error = "Silakan isi username dan password!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - Desa Jatijaya</title>
    
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
            background: linear-gradient(135deg, #0e4828 0%, #146338 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            padding: 20px;
        }

        .login-card {
            background: #ffffff;
            border-radius: 24px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 420px;
            padding: 40px 30px;
        }

        .brand-icon {
            width: 70px;
            height: 70px;
            background: #e8f5e9;
            color: #146338;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px auto;
            font-size: 2rem;
        }

        .btn-login {
            background-color: #146338;
            color: #ffffff;
            font-weight: 700;
            border-radius: 12px;
            padding: 12px;
            transition: all 0.3s ease;
        }

        .btn-login:hover {
            background-color: #0e4828;
            color: #ffffff;
        }

        .form-control {
            border-radius: 12px;
            padding: 12px 15px;
        }

        .form-control:focus {
            border-color: #146338;
            box-shadow: 0 0 0 0.25rem rgba(20, 99, 56, 0.25);
        }
    </style>
</head>
<body>

<div class="login-card text-center">
    <div class="brand-icon">
        <i class="fa-solid fa-shield-halved"></i>
    </div>
    <h4 class="fw-bold text-dark mb-1">Panel Admin</h4>
    <p class="text-muted small mb-4">Masuk untuk mengelola sistem Desa Jatijaya</p>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show text-start small p-3 rounded-3 mb-4" role="alert">
            <i class="fa-solid fa-circle-exclamation me-1"></i> <?php echo $error; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <form action="login.php" method="POST">
        <div class="text-start mb-3">
            <label class="form-label fw-semibold small text-secondary">Username</label>
            <div class="input-group">
                <span class="input-group-text bg-light border-end-0 rounded-start-3 text-muted"><i class="fa-solid fa-user"></i></span>
                <input type="text" name="username" class="form-control border-start-0" placeholder="Masukkan username" required autofocus>
            </div>
        </div>

        <div class="text-start mb-4">
            <label class="form-label fw-semibold small text-secondary">Password</label>
            <div class="input-group">
                <span class="input-group-text bg-light border-end-0 rounded-start-3 text-muted"><i class="fa-solid fa-key"></i></span>
                <input type="password" name="password" class="form-control border-start-0" placeholder="Masukkan password" required>
            </div>
        </div>

        <button type="submit" class="btn btn-login w-100 shadow-sm mb-3">
            <i class="fa-solid fa-right-to-bracket me-1"></i> Masuk Sekarang
        </button>

        <a href="../index.php" class="text-decoration-none small text-muted">
            <i class="fa-solid fa-arrow-left me-1"></i> Kembali ke Website Utama
        </a>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>