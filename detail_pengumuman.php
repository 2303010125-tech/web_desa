<?php
$page_title = "Detail Pengumuman";
include 'includes/header.php';
include 'config/koneksi.php';

// 1. Ambil ID dari parameter URL secara aman
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$pengumuman = null;

if ($id > 0) {
    // Ambil data pengumuman dari database
    $query = mysqli_query($koneksi, "SELECT * FROM pengumuman WHERE id = '$id'");
    if ($query && mysqli_num_rows($query) > 0) {
        $pengumuman = mysqli_fetch_assoc($query);
    }
}

// 2. Fallback Sampel Posyandu jika ID tidak ditemukan di database
if (!$pengumuman) {
    $pengumuman = [
        'id' => 1,
        'judul' => 'Jadwal Pelayanan Posyandu Balita & Lansia Bulan Ini',
        'kategori' => 'Kesehatan',
        'tanggal' => date('Y-m-d'),
        'penulis' => 'Kader Posyandu Desa Jatijaya',
        'gambar' => 'https://images.unsplash.com/photo-1576091160399-112ba8d25d1d?auto=format&fit=crop&w=1200&q=80',
        'file_lampiran' => '',
        'isi' => '
            <p>Diberitahukan kepada seluruh warga Desa Jatijaya, khususnya ibu hamil, ibu yang memiliki anak balita, serta para lansia, bahwa kegiatan pelayanan kesehatan Posyandu rutin bulan ini akan dilaksanakan sesuai jadwal berikut:</p>
            
            <h5 class="fw-bold mt-4 mb-3 text-success"><i class="fa-solid fa-calendar-check me-2"></i>Jadwal Pelaksanaan Per Dusun:</h5>
            <div class="table-responsive">
                <table class="table table-bordered border-light-subtle shadow-sm">
                    <thead class="table-success">
                        <tr>
                            <th>Posyandu / Dusun</th>
                            <th>Tanggal & Hari</th>
                            <th>Waktu</th>
                            <th>Lokasi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>Posyandu Melati 1</strong> (Dusun I)</td>
                            <td>Senin, 10 Bulan Ini</td>
                            <td>08.00 - 11.30 WIB</td>
                            <td>Poskesdes Dusun I</td>
                        </tr>
                        <tr>
                            <td><strong>Posyandu Mawar 2</strong> (Dusun II)</td>
                            <td>Rabu, 12 Bulan Ini</td>
                            <td>08.00 - 11.30 WIB</td>
                            <td>Balai Dusun II</td>
                        </tr>
                        <tr>
                            <td><strong>Posyandu Anggrek 3</strong> (Dusun III)</td>
                            <td>Jumat, 14 Bulan Ini</td>
                            <td>08.30 - 11.30 WIB</td>
                            <td>Rumah Ibu RT 03</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <h5 class="fw-bold mt-4 mb-2 text-success"><i class="fa-solid fa-heart-pulse me-2"></i>Jenis Pelayanan yang Diberikan:</h5>
            <ul class="list-group list-group-flush mb-4">
                <li class="list-group-item"><i class="fa-solid fa-check-circle text-success me-2"></i><strong>Balita:</strong> Penimbangan berat badan, pengukuran tinggi badan, imunisasi rutin, dan pemberian makanan tambahan (PMT).</li>
                <li class="list-group-item"><i class="fa-solid fa-check-circle text-success me-2"></i><strong>Lansia:</strong> Pemeriksaan tekanan darah, cek gula darah & asam urat gratis, serta senam sehat bersama.</li>
            </ul>

            <div class="alert alert-warning border-0 shadow-sm d-flex align-items-center gap-3">
                <i class="fa-solid fa-circle-info fs-3 text-warning"></i>
                <div>
                    <strong>Catatan Penting:</strong> Harap membawa Buku KIA (Kesehatan Ibu dan Anak) bagi peserta Balita dan kartu identitas (KTP/KK) bagi peserta Lansia.
                </div>
            </div>
        '
    ];
}

// 3. Pengecekan Aman Variabel & Gambar
$judul_pengumuman = $pengumuman['judul'] ?? 'Pengumuman Desa';
$tanggal_pengumuman = $pengumuman['tanggal'] ?? $pengumuman['created_at'] ?? date('Y-m-d');

$nama_gambar = $pengumuman['gambar'] ?? $pengumuman['foto'] ?? '';
if (!empty($nama_gambar) && strpos($nama_gambar, 'http') === 0) {
    $src_gambar = $nama_gambar;
} elseif (!empty($nama_gambar) && file_exists("uploads/pengumuman/" . $nama_gambar)) {
    $src_gambar = "uploads/pengumuman/" . $nama_gambar;
} elseif (!empty($nama_gambar) && file_exists("uploads/" . $nama_gambar)) {
    $src_gambar = "uploads/" . $nama_gambar;
} else {
    $src_gambar = "https://images.unsplash.com/photo-1576091160399-112ba8d25d1d?auto=format&fit=crop&w=1200&q=80";
}

// Kategori Badge Color
$kategori = $pengumuman['kategori'] ?? 'Informasi';
$badge_class = 'bg-success';
if (strtolower($kategori) == 'penting' || strtolower($kategori) == 'mendesak') {
    $badge_class = 'bg-danger';
} elseif (strtolower($kategori) == 'himbauan' || strtolower($kategori) == 'kesehatan') {
    $badge_class = 'bg-warning text-dark';
}
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-9">
            <!-- Navigasi Kembali -->
            <a href="pengumuman.php" class="btn btn-light rounded-pill px-4 mb-4 shadow-sm text-secondary fw-bold">
                <i class="fa-solid fa-arrow-left me-2"></i> Kembali ke Pengumuman
            </a>

            <!-- Card Utama Detail Pengumuman -->
            <article class="card border-0 shadow-lg rounded-4 overflow-hidden">
                <img src="<?php echo $src_gambar; ?>" class="card-img-top" style="max-height: 420px; object-fit: cover;" alt="<?php echo htmlspecialchars($judul_pengumuman); ?>">
                
                <div class="card-body p-4 p-md-5">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <span class="badge <?php echo $badge_class; ?> px-3 py-2 rounded-pill fw-bold">
                            <?php echo htmlspecialchars($kategori); ?>
                        </span>
                        <span class="text-muted small">
                            <i class="fa-regular fa-calendar me-1 text-success"></i> <?php echo date('d M Y', strtotime($tanggal_pengumuman)); ?>
                        </span>
                    </div>

                    <h1 class="fw-bold text-dark mb-4"><?php echo htmlspecialchars($judul_pengumuman); ?></h1>

                    <hr class="my-4 text-muted opacity-25">

                    <!-- Konten Pengumuman (Sistem Pengecekan Kunci 'isi' Bebas Error Warning) -->
                    <div class="content-body text-secondary lh-lg fs-6">
                        <?php 
                        if (!empty($pengumuman['isi'])) {
                            echo $pengumuman['isi'];
                        } else {
                            // Tampilan basa-basi jika kolom 'isi' kosong/tidak ada di database
                            echo '
                            <p class="mb-3">Halo warga Desa Jatijaya! Terima kasih telah berkunjung dan membaca informasi resmi dari pemerintah desa.</p>
                            <p class="mb-3">Mengenai pengumuman <strong>"' . htmlspecialchars($judul_pengumuman) . '"</strong>, saat ini rincian pesan lengkapnya sedang disiapkan atau dalam proses penyesuaian oleh pengurus desa setempat.</p>
                            <p class="mb-3">Apabila Anda membutuhkan informasi lebih lanjut mengenai kegiatan ini atau memiliki pertanyaan, silakan langsung menghubungi kantor Desa Jatijaya atau menanyakan langsung kepada ketua RT/RW setempat.</p>
                            <p class="mb-0 text-success fw-bold">Terima kasih atas perhatian dan kerja samanya!</p>
                            ';
                        }
                        ?>
                    </div>

                    <!-- Lampiran Jika Ada -->
                    <?php if (!empty($pengumuman['file_lampiran'])): ?>
                        <div class="mt-5 p-4 bg-light rounded-4 d-flex align-items-center justify-content-between border">
                            <div class="d-flex align-items-center gap-3">
                                <i class="fa-solid fa-file-pdf fs-2 text-danger"></i>
                                <div>
                                    <h6 class="fw-bold mb-0 text-dark">Lampiran Dokumen Resmi</h6>
                                    <small class="text-muted"><?php echo htmlspecialchars($pengumuman['file_lampiran']); ?></small>
                                </div>
                            </div>
                            <a href="uploads/lampiran/<?php echo htmlspecialchars($pengumuman['file_lampiran']); ?>" target="_blank" class="btn btn-outline-success rounded-pill fw-bold px-4">
                                Unduh <i class="fa-solid fa-download ms-1"></i>
                            </a>
                        </div>
                    <?php endif; ?>

                    <!-- Fitur Bagikan -->
                    <div class="mt-5 pt-4 border-top d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <span class="text-muted small fw-bold"><i class="fa-solid fa-share-nodes me-1"></i> Bagikan Pengumuman:</span>
                        <div class="d-flex gap-2">
                            <a href="https://api.whatsapp.com/send?text=<?php echo urlencode($judul_pengumuman . ' - ' . (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"); ?>" target="_blank" class="btn btn-success btn-sm rounded-pill px-3">
                                <i class="fa-brands fa-whatsapp me-1"></i> WhatsApp
                            </a>
                        </div>
                    </div>

                </div>
            </article>
        </div>
    </div>
</div>

<?php 
include 'includes/footer.php'; 
?>