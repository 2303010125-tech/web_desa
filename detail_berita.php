<?php
include 'config/koneksi.php';

// 1. Cek Koneksi Database
if (!$koneksi) {
    die("Koneksi Database Gagal: " . mysqli_connect_error());
}

// 2. Tangkap ID dari URL (Contoh: detail_berita.php?id=1)
$id = isset($_GET['id']) ? mysqli_real_escape_string($koneksi, $_GET['id']) : '';

if (empty($id)) {
    die("<div style='padding: 20px; color: red;'><b>Error:</b> ID Berita tidak ditemukan pada URL.</div>");
}

// 3. Query Ambil Data Berita Berdasarkan ID
$query = mysqli_query($koneksi, "SELECT * FROM berita WHERE id = '$id'");

if (!$query) {
    die("<div style='padding: 20px; color: red;'><b>Query Error:</b> " . mysqli_error($koneksi) . "<br><i>Pastikan nama tabel di database adalah 'berita'.</i></div>");
}

$data = mysqli_fetch_assoc($query);

if (!$data) {
    die("<div style='padding: 20px; color: red;'><b>Data Tidak Ditemukan:</b> Berita dengan ID <b>" . htmlspecialchars($id) . "</b> tidak ada di database.</div>");
}

// 4. Deteksi Otomatis Kolom Isi / Teks Berita
$isi_berita = $data['isi']
    ?? $data['isi_berita']
    ?? $data['konten']
    ?? $data['deskripsi']
    ?? $data['detail']
    ?? 'Belum ada isi berita.';

// Deteksi Otomatis Kolom Judul
$judul_berita = $data['judul']
    ?? $data['judul_berita']
    ?? $data['nama_berita']
    ?? 'Berita Desa';

// Update Views / Dilihat (Opsional)
if (isset($data['views'])) {
    mysqli_query($koneksi, "UPDATE berita SET views = views + 1 WHERE id = '$id'");
}

// Set Path Gambar Berita (Pengecekan Multi-Folder agar Gambar Tidak Pecah)
$nama_gambar = $data['gambar'] ?? $data['foto'] ?? $data['sampul'] ?? '';
$folder_pilihan = [
    'uploads/berita/' . $nama_gambar,
    'uploads/' . $nama_gambar,
    'assets/img/berita/' . $nama_gambar,
    'assets/img/' . $nama_gambar
];

$gambar = 'https://via.placeholder.com/800x400?text=Tidak+Ada+Gambar';
if (!empty($nama_gambar)) {
    foreach ($folder_pilihan as $path_cek) {
        if (file_exists($path_cek) && !is_dir($path_cek)) {
            $gambar = $path_cek;
            break;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($judul_berita); ?> - Desa Jatijaya</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome Icon -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- html2pdf.js untuk Export PDF -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Poppins', sans-serif;
        }

        .card-detail {
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        }

        .img-header {
            width: 100%;
            max-height: 450px;
            object-fit: cover;
            border-radius: 12px;
        }

        .meta-info {
            font-size: 0.9rem;
            color: #6c757d;
        }

        .isi-berita {
            font-size: 1.05rem;
            line-height: 1.8;
            color: #333;
            text-align: justify;
        }

        @media print {
            .no-print {
                display: none !important;
            }

            .card-detail {
                box-shadow: none !important;
            }

            body {
                background-color: #fff;
            }
        }
    </style>
</head>

<body>

    <div class="container py-5">

        <!-- Navigasi & Aksi (Tidak Ikut Tercetak saat Export PDF) -->
        <div class="d-flex justify-content-between align-items-center mb-4 no-print">
            <a href="javascript:history.back()" class="btn btn-outline-secondary rounded-pill px-4">
                <i class="fa-solid fa-arrow-left me-2"></i>Kembali
            </a>
        </div>

        <!-- Area Konten Detail Berita -->
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card card-detail bg-white p-4 p-md-5" id="area-detail">

                    <!-- Badge Kategori -->
                    <div class="mb-2">
                        <span class="badge bg-success px-3 py-2 rounded-pill">
                            <?php echo htmlspecialchars($data['kategori'] ?? 'Berita Desa'); ?>
                        </span>
                    </div>

                    <!-- Judul Berita -->
                    <h1 class="fw-bold text-dark mb-3">
                        <?php echo htmlspecialchars($judul_berita); ?>
                    </h1>

                    <!-- Meta Informasi (Penulis, Tanggal, Views) -->
                    <div class="meta-info d-flex flex-wrap align-items-center gap-3 border-bottom pb-3 mb-4">
                        <div>
                            <i class="fa-regular fa-user text-success me-1"></i>
                            <span><?php echo htmlspecialchars($data['penulis'] ?? $data['author'] ?? 'Admin Desa'); ?></span>
                        </div>
                        <div>
                            <i class="fa-regular fa-calendar-alt text-success me-1"></i>
                            <span><?php echo isset($data['tanggal']) ? date('d F Y', strtotime($data['tanggal'])) : (isset($data['created_at']) ? date('d F Y', strtotime($data['created_at'])) : date('d F Y')); ?></span>
                        </div>
                        <?php if (isset($data['views'])): ?>
                            <div>
                                <i class="fa-regular fa-eye text-success me-1"></i>
                                <span><?php echo $data['views']; ?>x Dilihat</span>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Gambar Berita -->
                    <div class="mb-4">
                        <img src="<?php echo $gambar; ?>" class="img-header shadow-sm" alt="<?php echo htmlspecialchars($judul_berita); ?>">
                    </div>

                    <!-- Isi Berita -->
                    <div class="isi-berita mt-3">
                        <?php
                        // Mengubah baris baru (\n) menjadi tag <br> secara otomatis
                        echo nl2br(htmlspecialchars_decode($isi_berita));
                        ?>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <!-- Script Download PDF -->
    <script>
        function downloadPDF() {
            const element = document.getElementById('area-detail');
            const options = {
                margin: [10, 10, 10, 10],
                filename: 'Berita_<?php echo sprintf("%03d", $data['id']); ?>.pdf',
                image: {
                    type: 'jpeg',
                    quality: 0.98
                },
                html2canvas: {
                    scale: 2,
                    useCORS: true
                },
                jsPDF: {
                    unit: 'mm',
                    format: 'a4',
                    orientation: 'portrait'
                }
            };

            html2pdf().set(options).from(element).save();
        }
    </script>

</body>
</html>