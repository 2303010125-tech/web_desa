<?php
/**
 * Function untuk membuat Header Banner Admin
 * 
 * @param string $title - Judul halaman
 * @param string $subtitle - Deskripsi/subjudul halaman
 * @param string $icon - Class icon FontAwesome (contoh: 'fa-envelope-open-text')
 * @param string $btn_label - Label tombol di kanan (Opsional)
 * @param string $btn_url - URL tujuan tombol (Opsional)
 * @param string $btn_icon - Class icon untuk tombol (Opsional)
 */
function render_admin_header($title, $subtitle, $icon = 'fa-folder-open', $btn_label = '', $btn_url = '#', $btn_icon = 'fa-file-excel') {
?>
    <style>
        .admin-page-header {
            background: linear-gradient(135deg, #00875a 0%, #006644 100%);
            border-radius: 20px;
            padding: 30px 35px;
            color: #ffffff;
            position: relative;
            overflow: hidden;
            box-shadow: 0 10px 25px rgba(0, 135, 90, 0.2);
        }

        /* Watermark Icon Transparan di Background Kanan */
        .admin-page-header::after {
            content: '\f0e0'; /* Kode Icon Envelope */
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            position: absolute;
            right: -10px;
            bottom: -35px;
            font-size: 11rem;
            opacity: 0.12;
            color: #ffffff;
            pointer-events: none;
        }

        .admin-header-title {
            font-weight: 800;
            font-size: 1.85rem;
            letter-spacing: -0.5px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .admin-header-subtitle {
            font-size: 0.95rem;
            opacity: 0.9;
            margin-bottom: 0;
            font-weight: 400;
        }

        .btn-admin-action {
            background-color: #ffffff;
            color: #00875a;
            font-weight: 700;
            border-radius: 50px;
            padding: 10px 24px;
            border: none;
            transition: all 0.2s ease-in-out;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
        }

        .btn-admin-action:hover {
            background-color: #f0fdf4;
            color: #006644;
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.15);
        }
    </style>

    <div class="admin-page-header mb-4">
        <div class="row align-items-center position-relative" style="z-index: 2;">
            <div class="col-md-8">
                <h2 class="admin-header-title mb-1">
                    <i class="fa-solid <?= $icon; ?>"></i> <?= $title; ?>
                </h2>
                <p class="admin-header-subtitle">
                    <?= $subtitle; ?>
                </p>
            </div>
            <?php if (!empty($btn_label)) : ?>
            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                <a href="<?= $btn_url; ?>" class="btn btn-admin-action">
                    <i class="fa-solid <?= $btn_icon; ?> text-success"></i> <?= $btn_label; ?>
                </a>
            </div>
            <?php endif; ?>
        </div>
    </div>
<?php
}
?>