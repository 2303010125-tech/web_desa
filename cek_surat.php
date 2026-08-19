<?php
$page_title = "Cek Permohonan Surat";

include_once 'includes/header.php';
include_once 'config/koneksi.php';

$nik_search = '';
$result = null;

if (isset($_GET['nik'])) {
    $nik_search = mysqli_real_escape_string($koneksi, trim($_GET['nik']));
    if (!empty($nik_search)) {
        // Mengurutkan berdasarkan tanggal dan ID terbaru
        $query = "SELECT * FROM pengajuan_surat WHERE nik = '$nik_search' ORDER BY tanggal DESC, id DESC LIMIT 1";
        $result = mysqli_query($koneksi, $query);
    }
}
?>

<style>
    /* UTILS & WRAPPER */
    .page-wrapper {
        min-height: calc(100vh - 350px);
        display: flex;
        flex-direction: column;
    }

    /* HERO BANNER MATCHING DESIGN */
    .hero-tracking-section {
        background: linear-gradient(180deg, rgba(16, 94, 60, 0.92) 0%, rgba(13, 71, 45, 0.95) 100%),
                    url('https://images.unsplash.com/photo-1500382017468-9049fed747ef?auto=format&fit=crop&w=1920&q=80') center/cover no-repeat;
        border-radius: 0 0 28px 28px;
        padding: 60px 20px 90px 20px;
        color: #ffffff;
        text-align: center;
        position: relative;
    }

    .badge-tracking {
        background-color: rgba(255, 255, 255, 0.95);
        color: #105e3c;
        font-weight: 700;
        font-size: 0.82rem;
        padding: 6px 18px;
        border-radius: 50px;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        margin-bottom: 20px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

    .hero-tracking-title {
        font-size: 2.8rem;
        font-weight: 800;
        letter-spacing: -0.5px;
        margin-bottom: 12px;
    }

    .hero-tracking-desc {
        font-size: 0.98rem;
        color: rgba(255, 255, 255, 0.88);
        max-width: 620px;
        margin: 0 auto;
        line-height: 1.6;
    }

    /* FLOATING SEARCH BOX */
    .search-box-floating {
        max-width: 800px;
        margin: -42px auto 40px auto;
        background: #ffffff;
        border-radius: 24px;
        padding: 8px 10px 8px 24px;
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.08);
        position: relative;
        z-index: 10;
    }

    .search-input-group {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .search-input-icon {
        color: #64748b;
        font-size: 1.25rem;
        display: flex;
        align-items: center;
    }

    .search-input-field {
        border: none !important;
        outline: none !important;
        box-shadow: none !important;
        font-size: 0.98rem;
        font-weight: 500;
        color: #1e293b;
        width: 100%;
        padding: 10px 0;
        background: transparent;
    }

    .search-input-field::placeholder {
        color: #64748b;
        font-weight: 500;
    }

    .btn-search-green {
        background-color: #108a55;
        color: #ffffff;
        font-weight: 600;
        font-size: 0.95rem;
        padding: 12px 28px;
        border-radius: 16px;
        border: none;
        white-space: nowrap;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .btn-search-green:hover {
        background-color: #0d7347;
        color: #ffffff;
        transform: translateY(-1px);
    }

    /* TABEL HASIL */
    .table-custom {
        background: #ffffff;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
    }

    .table-custom thead {
        background-color: #f8fafc;
    }

    .table-custom th {
        color: #475569;
        font-weight: 700;
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding: 16px 20px;
        border-bottom: 2px solid #e2e8f0;
    }

    .table-custom td {
        padding: 16px 20px;
        vertical-align: middle;
        font-size: 0.92rem;
    }

    .badge-status {
        padding: 6px 14px;
        border-radius: 50px;
        font-weight: 700;
        font-size: 0.78rem;
    }

    /* STATE AWAL */
    .initial-state-box {
        background: #ffffff;
        border: 1px dashed #cbd5e1;
        border-radius: 20px;
        padding: 40px 20px;
        text-align: center;
    }
</style>

<div class="page-wrapper">
    <!-- HERO HEADER BANNER -->
    <section class="hero-tracking-section">
        <div class="container">
            <span class="badge-tracking">
                <i class="fa-solid fa-magnifying-glass"></i> Tracking Layanan
            </span>
            <h1 class="hero-tracking-title">Cek Status Surat</h1>
            <p class="hero-tracking-desc">
                Pantau tahapan dan proses verifikasi pengajuan surat administrasi Anda secara real-time cukup dengan memasukkan NIK.
            </p>
        </div>
    </section>

    <!-- FORM PENCARIAN FLOATING -->
    <div class="container">
        <div class="search-box-floating">
            <form action="cek_surat.php" method="GET">
                <div class="search-input-group">
                    <div class="search-input-icon">
                        <i class="fa-regular fa-address-card"></i>
                    </div>
                    <input type="number" name="nik" class="search-input-field" placeholder="Masukkan NIK 16 Digit..." value="<?php echo htmlspecialchars($nik_search); ?>" required>
                    <button type="submit" class="btn btn-search-green">
                        <i class="fa-solid fa-magnifying-glass"></i> Cek Status
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- KONTEN UTAMA -->
    <div class="container mb-5 flex-grow-1">
        <?php if (!empty($nik_search)): ?>
            <!-- HEADER TABEL HASIL -->
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div>
                    <h4 class="fw-bold text-dark mb-0"><i class="fa-solid fa-list-check text-success me-2"></i>Hasil Riwayat Permohonan</h4>
                    <p class="text-muted small mb-0">Menampilkan status pengajuan terbaru untuk NIK: <strong><?php echo htmlspecialchars($nik_search); ?></strong></p>
                </div>
            </div>

            <!-- TABEL KONTEN HASIL -->
            <div class="table-custom">
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr>
                                <th width="5%">NO</th>
                                <th>JENIS SURAT</th>
                                <th>NAMA PEMOHON</th>
                                <th>KEPERLUAN</th>
                                <th>TANGGAL PENGAJUAN</th>
                                <th>STATUS</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result && mysqli_num_rows($result) > 0): ?>
                                <?php $no = 1; while ($row = mysqli_fetch_assoc($result)): ?>
                                    <?php $st = strtolower(trim($row['status'])); ?>
                                    <tr>
                                        <td class="fw-bold text-muted"><?php echo $no++; ?></td>
                                        <td>
                                            <span class="fw-bold text-dark d-block"><?php echo htmlspecialchars($row['jenis_surat']); ?></span>
                                            <small class="text-muted"><i class="fa-solid fa-clock me-1"></i>Estimasi: 1x24 Jam Kerja</small>
                                        </td>
                                        <td>
                                            <span class="fw-semibold text-dark"><?php echo htmlspecialchars($row['nama']); ?></span>
                                            <small class="d-block text-muted">NIK: <?php echo htmlspecialchars($row['nik']); ?></small>
                                        </td>
                                        <td class="text-secondary"><?php echo htmlspecialchars($row['keterangan']); ?></td>
                                        <td class="small text-muted">
                                            <i class="fa-regular fa-calendar me-1"></i>
                                            <?php 
                                                $tgl = isset($row['tanggal']) ? $row['tanggal'] : ($row['created_at'] ?? 'now');
                                                echo date('d M Y, H:i', strtotime($tgl)); 
                                            ?> WIB
                                        </td>
                                        <td>
                                            <?php
                                            if (in_array($st, ['selesai', 'acc', 'disetujui'])) {
                                                echo '<span class="badge bg-success badge-status"><i class="fa-solid fa-circle-check me-1"></i>Selesai</span>';
                                            } elseif (in_array($st, ['diproses', 'proses'])) {
                                                echo '<span class="badge bg-info text-white badge-status"><i class="fa-solid fa-spinner me-1"></i>Diproses</span>';
                                            } elseif (in_array($st, ['ditolak', 'reject'])) {
                                                echo '<span class="badge bg-danger badge-status"><i class="fa-solid fa-circle-xmark me-1"></i>Ditolak</span>';
                                            } else {
                                                echo '<span class="badge bg-warning text-dark badge-status"><i class="fa-solid fa-clock me-1"></i>Pending</span>';
                                            }
                                            ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">
                                        <i class="fa-solid fa-file-excel fs-1 mb-2 text-secondary d-block"></i>
                                        Tidak ditemukan riwayat permohonan surat untuk NIK <strong><?php echo htmlspecialchars($nik_search); ?></strong>.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php else: ?>
            <!-- PANDUAN AWAL SEBELUM PENCARIAN -->
            <div class="initial-state-box my-2">
                <div class="mb-3">
                    <i class="fa-solid fa-id-card-clip fs-1 text-success"></i>
                </div>
                <h5 class="fw-bold text-dark mb-2">Belum Ada NIK Ditampilkan</h5>
                <p class="text-muted small mx-auto mb-0" style="max-width: 480px;">
                    Silakan masukkan 16 digit Nomor Induk Kependudukan (NIK) Anda pada kolom pencarian di atas untuk melihat status pengajuan surat.
                </p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include_once 'includes/footer.php'; ?>