<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Mendapatkan nama file saat ini untuk penentuan status aktif menu
$current_page = basename($_SERVER['PHP_SELF']);
?>

<style>
    /* ------------------------------------
        SIDEBAR FIXED FULL HEIGHT DESIGN
    ------------------------------------ */
    :root {
        --sb-bg: #111827;               /* Warna background dark sidebar */
        --sb-active-green: #059669;      /* Warna hijau menu aktif */
        --sb-active-glow: rgba(5, 150, 105, 0.4);
        --sb-text-muted: #9ca3af;        /* Warna teks biasa & header kategori */
        --sb-text-light: #e5e7eb;        /* Warna teks item */
        --sb-badge-bg: #064e3b;          /* Hijau gelap badge brand */
        --sb-badge-text: #34d399;        /* Hijau terang badge brand */
    }

    .sidebar-container {
        width: 260px;
        height: 100vh;                  /* Kunci tinggi seukuran layar */
        position: sticky;               /* Bikin sidebar terkunci diam */
        top: 0;
        left: 0;
        background-color: var(--sb-bg);
        color: #ffffff;
        display: flex;
        flex-direction: column;
        padding: 24px 16px;
        flex-shrink: 0;
        z-index: 1000;
        font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
        box-sizing: border-box;
    }

    /* BRAND LOGO HEADER */
    .sidebar-brand-box {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 24px;
        padding-left: 4px;
        flex-shrink: 0;
    }

    .brand-icon-wrapper {
        width: 44px;
        height: 44px;
        background-color: var(--sb-badge-bg);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--sb-badge-text);
        font-size: 1.4rem;
        flex-shrink: 0;
    }

    .brand-details {
        display: flex;
        flex-direction: column;
    }

    .brand-name {
        font-size: 1.1rem;
        font-weight: 800;
        color: #ffffff;
        line-height: 1.2;
        letter-spacing: -0.2px;
    }

    .brand-tag {
        font-size: 0.68rem;
        font-weight: 700;
        color: var(--sb-badge-text);
        letter-spacing: 0.5px;
        margin-top: 2px;
    }

    /* WADAH SCROLL UNTUK MENU */
    .sidebar-nav-scroll {
        flex: 1;
        overflow-y: auto;
        padding-right: 4px;
    }

    /* Kustom Scrollbar halus untuk sidebar */
    .sidebar-nav-scroll::-webkit-scrollbar {
        width: 4px;
    }
    .sidebar-nav-scroll::-webkit-scrollbar-thumb {
        background-color: rgba(255, 255, 255, 0.1);
        border-radius: 10px;
    }

    /* CATEGORY HEADERS */
    .sidebar-category {
        font-size: 0.68rem;
        font-weight: 800;
        text-transform: uppercase;
        color: var(--sb-text-muted);
        letter-spacing: 0.8px;
        margin-top: 12px;
        margin-bottom: 8px;
        padding-left: 12px;
    }

    /* MENU ITEMS */
    .sidebar-menu {
        list-style: none;
        padding: 0;
        margin: 0;
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .sidebar-menu-link {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 12px 16px;
        color: var(--sb-text-muted);
        font-weight: 600;
        font-size: 0.9rem;
        border-radius: 14px;
        text-decoration: none;
        transition: all 0.2s ease-in-out;
    }

    .sidebar-menu-link i {
        font-size: 1.15rem;
        width: 20px;
        text-align: center;
        color: var(--sb-text-muted);
        transition: color 0.2s ease;
    }

    /* HOVER STATE */
    .sidebar-menu-link:hover {
        color: #ffffff;
        background-color: rgba(255, 255, 255, 0.05);
    }

    .sidebar-menu-link:hover i {
        color: #ffffff;
    }

    /* ACTIVE STATE */
    .sidebar-menu-link.active {
        background-color: var(--sb-active-green);
        color: #ffffff;
        font-weight: 700;
        box-shadow: 0 4px 15px var(--sb-active-glow);
    }

    .sidebar-menu-link.active i {
        color: #ffffff;
    }

    /* FOOTER SIDEBAR (PROFIL ADMIN & LOGOUT TERKUNCI DI PADA DASAR SIDEBAR) */
    .sidebar-footer {
        margin-top: auto; 
        padding-top: 16px;
        border-top: 1px solid rgba(255, 255, 255, 0.08);
        display: flex;
        flex-direction: column;
        gap: 12px;
        flex-shrink: 0;
    }

    .admin-profile-box {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 4px 6px;
    }

    .admin-avatar {
        width: 40px;
        height: 40px;
        background-color: var(--sb-active-green);
        color: #ffffff;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        font-size: 1rem;
        flex-shrink: 0;
        box-shadow: 0 4px 10px rgba(5, 150, 105, 0.3);
    }

    .admin-info {
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }

    .admin-name {
        font-size: 0.88rem;
        font-weight: 700;
        color: #ffffff;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .admin-status {
        font-size: 0.72rem;
        color: #34d399;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .admin-status-dot {
        width: 6px;
        height: 6px;
        background-color: #34d399;
        border-radius: 50%;
        display: inline-block;
    }

    .btn-sidebar-logout {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        width: 100%;
        padding: 10px 16px;
        background-color: rgba(239, 68, 68, 0.1);
        color: #f87171;
        border: 1px solid rgba(239, 68, 68, 0.2);
        border-radius: 12px;
        font-weight: 700;
        font-size: 0.85rem;
        text-decoration: none;
        transition: all 0.2s ease-in-out;
    }

    .btn-sidebar-logout:hover {
        background-color: #ef4444;
        color: #ffffff;
        border-color: #ef4444;
        box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
    }
</style>

<aside class="sidebar-container">
    <!-- BRAND HEADER -->
    <div class="sidebar-brand-box">
        <div class="brand-icon-wrapper">
            <i class="fa-solid fa-tree"></i>
        </div>
        <div class="brand-details">
            <span class="brand-name">Admin Jatijaya</span>
            <span class="brand-tag">PORTAL PANEL</span>
        </div>
    </div>

    <!-- WADAH NAVIGASI UTAMA & PENGATURAN -->
    <div class="sidebar-nav-scroll">
        <div class="sidebar-category">NAVIGASI UTAMA</div>
        <ul class="sidebar-menu">
            <li>
                <a href="dashboard.php" class="sidebar-menu-link <?php echo ($current_page == 'dashboard.php') ? 'active' : ''; ?>">
                    <i class="fa-solid fa-chart-pie"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li>
                <a href="layanan.php" class="sidebar-menu-link <?php echo ($current_page == 'layanan.php' || $current_page == 'surat.php') ? 'active' : ''; ?>">
                    <i class="fa-solid fa-envelope"></i>
                    <span>Layanan Surat</span>
                </a>
            </li>
            <li>
                <a href="berita.php" class="sidebar-menu-link <?php echo ($current_page == 'berita.php') ? 'active' : ''; ?>">
                    <i class="fa-solid fa-newspaper"></i>
                    <span>Kelola Berita</span>
                </a>
            </li>
            <li>
                <a href="potensi.php" class="sidebar-menu-link <?php echo ($current_page == 'potensi.php' || $current_page == 'umkm.php') ? 'active' : ''; ?>">
                    <i class="fa-solid fa-store"></i>
                    <span>Potensi & UMKM</span>
                </a>
            </li>
            <li>
                <a href="pengumuman.php" class="sidebar-menu-link <?php echo ($current_page == 'pengumuman.php') ? 'active' : ''; ?>">
                    <i class="fa-solid fa-bullhorn"></i>
                    <span>Pengumuman</span>
                </a>
            </li>
        </ul>

        <div class="sidebar-category" style="margin-top: 20px;">PENGATURAN</div>
        <ul class="sidebar-menu">
            <li>
                <a href="pengguna.php" class="sidebar-menu-link <?php echo ($current_page == 'pengguna.php') ? 'active' : ''; ?>">
                    <i class="fa-solid fa-users-gear"></i>
                    <span>Kelola Pengguna</span>
                </a>
            </li>
            <li>
                <a href="pengaturan.php" class="sidebar-menu-link <?php echo ($current_page == 'pengaturan.php') ? 'active' : ''; ?>">
                    <i class="fa-solid fa-sliders"></i>
                    <span>Pengaturan Desa</span>
                </a>
            </li>
            <!-- MENU KEMBALI KE WEBSITE UTAMA -->
            <li>
                <a href="../index.php" target="_blank" class="sidebar-menu-link">
                    <i class="fa-solid fa-globe"></i>
                    <span>Ke Website Utama</span>
                    <i class="fa-solid fa-arrow-up-right-from-square" style="margin-left: auto; font-size: 0.75rem; opacity: 0.6;"></i>
                </a>
            </li>
        </ul>
    </div>

    <!-- PROFIL ADMIN & LOGOUT (TETAP DIAM DI PALING BAWAH) -->
    <div class="sidebar-footer">
        <div class="admin-profile-box">
            <div class="admin-avatar">
                <?php 
                    $nama_admin = $_SESSION['admin_nama'] ?? $_SESSION['admin_username'] ?? 'Administrator';
                    echo strtoupper(substr($nama_admin, 0, 1)); 
                ?>
            </div>
            <div class="admin-info">
                <span class="admin-name"><?php echo htmlspecialchars($nama_admin); ?></span>
                <span class="admin-status"><span class="admin-status-dot"></span> Online</span>
            </div>
        </div>

        <a href="logout.php" class="btn-sidebar-logout" onclick="return confirm('Apakah Anda yakin ingin keluar dari sistem?');">
            <i class="fa-solid fa-right-from-bracket"></i>
            <span>Logout</span>
        </a>
    </div>
</aside>