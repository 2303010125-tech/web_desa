<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Ambil nama file saat ini untuk menentukan menu aktif
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . " - Desa Jatijaya" : "Desa Jatijaya"; ?></title>
    
    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- FontAwesome 6 Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        :root {
            --primary-green: #146338;
            --primary-hover: #0e4828;
            --active-bg-green: #e8f5e9;
            --dark-footer-bg: #181f25;
            --footer-text-muted: #8e9a9f;
            --footer-icon-bg: #212930;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #f8f9fa;
            color: #212529;
        }

        /* NAVBAR STYLING */
        .navbar-modern {
            background-color: #ffffff !important;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.03);
            padding-top: 14px;
            padding-bottom: 14px;
        }

        .brand-text {
            color: var(--primary-green);
            font-weight: 800;
            font-size: 1.4rem;
            letter-spacing: -0.3px;
        }

        .nav-link-custom {
            color: #4a5568 !important;
            font-weight: 600;
            font-size: 0.95rem;
            padding: 10px 18px !important;
            border-radius: 10px;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .nav-link-custom:hover {
            color: var(--primary-green) !important;
            background-color: rgba(20, 99, 56, 0.05);
            transform: translateY(-1px);
        }

        /* Active Menu State */
        .nav-link-custom.active {
            color: var(--primary-green) !important;
            background-color: var(--active-bg-green) !important;
            font-weight: 700;
        }

        .btn-admin-login {
            background-color: var(--primary-green);
            color: #ffffff !important;
            border-radius: 50px;
            padding: 10px 24px;
            font-weight: 700;
            font-size: 0.95rem;
            border: none;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(20, 99, 56, 0.2);
        }

        .btn-admin-login:hover {
            background-color: var(--primary-hover);
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(20, 99, 56, 0.3);
        }
    </style>
</head>
<body class="d-flex flex-column min-vh-100">

    <!-- HEADER NAVBAR -->
    <nav class="navbar navbar-expand-xl navbar-light navbar-modern sticky-top">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center gap-2" href="index.php">
                <i class="fa-solid fa-tree fs-3 text-success"></i>
                <span class="brand-text">Desa Jatijaya</span>
            </a>

            <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarContent">
                <ul class="navbar-nav ms-auto align-items-xl-center gap-1">
                    <li class="nav-item">
                        <a class="nav-link nav-link-custom <?php echo ($current_page == 'index.php') ? 'active' : ''; ?>" href="index.php">Beranda</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link nav-link-custom <?php echo ($current_page == 'profil.php') ? 'active' : ''; ?>" href="profil.php">Profil</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link nav-link-custom <?php echo ($current_page == 'berita.php') ? 'active' : ''; ?>" href="berita.php">Berita</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link nav-link-custom <?php echo ($current_page == 'pengumuman.php') ? 'active' : ''; ?>" href="pengumuman.php">Pengumuman</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link nav-link-custom <?php echo ($current_page == 'layanan.php') ? 'active' : ''; ?>" href="layanan.php">Layanan Surat</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link nav-link-custom me-xl-2 <?php echo ($current_page == 'cek_surat.php' || $current_page == 'cek_status.php') ? 'active' : ''; ?>" href="cek_surat.php">Cek Surat</a>
                    </li>
                   
                    <li class="nav-item mt-2 mt-xl-0">
                        <a href="admin/login.php" class="btn btn-admin-login d-inline-flex align-items-center gap-2">
                            <i class="fa-solid fa-shield-halved"></i>
                            <span>Login Admin</span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- KONTEN UTAMA HALAMAN MEMULAI DARI SINI -->
    <main class="flex-grow-1">