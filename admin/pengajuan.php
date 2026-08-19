<?php
session_start();

// Panggil header admin dan koneksi
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../config/koneksi.php';

// Filter Parameter
$filter_status = $_GET['status'] ?? '';
$search_keyword = $_GET['q'] ?? '';

// Build Query SQL berdasarkan Filter
$query_str = "SELECT * FROM pengajuan_surat WHERE 1=1";

if (!empty($filter_status)) {
    $filter_status_clean = mysqli_real_escape_string($koneksi, $filter_status);
    $query_str .= " AND status = '$filter_status_clean'";
}

if (!empty($search_keyword)) {
    $search_clean = mysqli_real_escape_string($koneksi, $search_keyword);
    $query_str .= " AND (nama LIKE '%$search_clean%' OR nik LIKE '%$search_clean%' OR jenis_surat LIKE '%$search_clean%')";
}

$query_str .= " ORDER BY id DESC";
$query = mysqli_query($koneksi, $query_str);

// Hitung Statistik
$count_all = mysqli_num_rows(mysqli_query($koneksi, "SELECT id FROM pengajuan_surat"));
$count_pending = mysqli_num_rows(mysqli_query($koneksi, "SELECT id FROM pengajuan_surat WHERE status='Pending'"));
$count_proses = mysqli_num_rows(mysqli_query($koneksi, "SELECT id FROM pengajuan_surat WHERE status='Diproses'"));
$count_selesai = mysqli_num_rows(mysqli_query($koneksi, "SELECT id FROM pengajuan_surat WHERE status='Selesai'"));
?>

<div class="container py-4 mt-4">
    <div class="row">
        <div class="col-lg-12">
            
            <h3 class="fw-bold text-dark mb-4"><i class="fa-solid fa-list-check me-2 text-success"></i>Monitoring Pengajuan Surat</h3>

            <!-- Card Cards Ringkasan Statistik -->
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <a href="pengajuan.php" class="text-decoration-none">
                        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
                            <small class="text-muted fw-bold text-uppercase">Total Pengajuan</small>
                            <h3 class="fw-bold text-dark mt-1 mb-0"><?php echo $count_all; ?></h3>
                        </div>
                    </a>
                </div>
                <div class="col-md-3">
                    <a href="pengajuan.php?status=Pending" class="text-decoration-none">
                        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white border-start border-4 border-warning">
                            <small class="text-warning fw-bold text-uppercase">Pending / Antrean</small>
                            <h3 class="fw-bold text-warning mt-1 mb-0"><?php echo $count_pending; ?></h3>
                        </div>
                    </a>
                </div>
                <div class="col-md-3">
                    <a href="pengajuan.php?status=Diproses" class="text-decoration-none">
                        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white border-start border-4 border-info">
                            <small class="text-info fw-bold text-uppercase">Sedang Diproses</small>
                            <h3 class="fw-bold text-info mt-1 mb-0"><?php echo $count_proses; ?></h3>
                        </div>
                    </a>
                </div>
                <div class="col-md-3">
                    <a href="pengajuan.php?status=Selesai" class="text-decoration-none">
                        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white border-start border-4 border-success">
                            <small class="text-success fw-bold text-uppercase">Selesai / Ready</small>
                            <h3 class="fw-bold text-success mt-1 mb-0"><?php echo $count_selesai; ?></h3>
                        </div>
                    </a>
                </div>
            </div>

            <!-- Filter & Search Bar -->
            <div class="card border-0 shadow-sm rounded-4 p-4 mb-4">
                <form action="" method="GET" class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-bold small">Filter Status Pengajuan</label>
                        <select name="status" class="form-select rounded-3" onchange="this.form.submit()">
                            <option value="">-- Semua Status --</option>
                            <option value="Pending" <?php echo ($filter_status == 'Pending') ? 'selected' : ''; ?>>Pending</option>
                            <option value="Diproses" <?php echo ($filter_status == 'Diproses') ? 'selected' : ''; ?>>Diproses</option>
                            <option value="Selesai" <?php echo ($filter_status == 'Selesai') ? 'selected' : ''; ?>>Selesai</option>
                            <option value="Ditolak" <?php echo ($filter_status == 'Ditolak') ? 'selected' : ''; ?>>Ditolak</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold small">Cari NIK / Nama / Surat</label>
                        <input type="text" name="q" class="form-control rounded-3" placeholder="Masukkan kata kunci pencarian..." value="<?php echo htmlspecialchars($search_keyword); ?>">
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-success fw-bold w-100 rounded-3">
                            <i class="fa-solid fa-magnifying-glass me-1"></i> Filter
                        </button>
                    </div>
                </form>
            </div>

            <!-- List Data Pengajuan -->
            <div class="card border-0 shadow-sm rounded-4 p-4">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>No</th>
                                <th>NIK & Nama</th>
                                <th>Jenis Surat</th>
                                <th>Tanggal Pengajuan</th>
                                <th>Status</th>
                                <th class="text-center">Aksi Langsung</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (mysqli_num_rows($query) > 0): ?>
                                <?php $no = 1; while ($r = mysqli_fetch_assoc($query)): ?>
                                    <?php 
                                        $st = $r['status'] ?? 'Pending';
                                        $st_class = 'bg-warning text-dark';
                                        if ($st == 'Diproses') $st_class = 'bg-info text-white';
                                        if ($st == 'Selesai') $st_class = 'bg-success text-white';
                                        if ($st == 'Ditolak') $st_class = 'bg-danger text-white';
                                    ?>
                                    <tr>
                                        <td><?php echo $no++; ?></td>
                                        <td>
                                            <span class="fw-bold text-dark d-block"><?php echo htmlspecialchars($r['nama']); ?></span>
                                            <small class="text-muted"><?php echo htmlspecialchars($r['nik']); ?></small>
                                        </td>
                                        <td><?php echo htmlspecialchars($r['jenis_surat']); ?></td>
                                        <td>
                                            <small class="text-muted"><i class="fa-regular fa-clock me-1"></i><?php echo date('d-m-Y H:i', strtotime($r['tanggal_pengajuan'] ?? $r['created_at'] ?? 'now')); ?></small>
                                        </td>
                                        <td>
                                            <span class="badge <?php echo $st_class; ?> rounded-pill px-3 py-2"><?php echo htmlspecialchars($st); ?></span>
                                        </td>
                                       <td class="text-center">
    <?php if (strtolower($st) == 'selesai'): ?>
        <!-- Tombol Cetak / PDF yang aktif jika status Selesai -->
        <a href="cetak_surat.php?id=<?php echo $r['id']; ?>" target="_blank" class="btn btn-sm btn-primary rounded-3 shadow-sm">
            <i class="fa-solid fa-print me-1"></i> Cetak / PDF
        </a>
    <?php else: ?>
        <!-- Jika belum selesai, tampilkan tombol kelola atau status diproses -->
        <a href="layanan.php" class="btn btn-sm btn-outline-secondary rounded-3">
            <i class="fa-solid fa-gear me-1"></i> Kelola
        </a>
    <?php endif; ?>
</td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">
                                        Data pengajuan tidak ditemukan berdasarkan kriteria pencarian/filter.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</div>

<?php 
include __DIR__ . '/../includes/footer.php'; 
?>