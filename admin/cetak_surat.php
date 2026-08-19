<?php
session_start();
require_once '../config/koneksi.php';

/** @var mysqli $koneksi */

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit;
}

// ==========================================
// 1. AMBIL DATA PROFIL / PENGATURAN DESA
// ==========================================
$query_desa = mysqli_query($koneksi, "SELECT * FROM pengaturan LIMIT 1");
if (!$query_desa || mysqli_num_rows($query_desa) == 0) {
    $query_desa = mysqli_query($koneksi, "SELECT * FROM profil_desa LIMIT 1");
}
$desa = ($query_desa && mysqli_num_rows($query_desa) > 0) ? mysqli_fetch_assoc($query_desa) : [];

// Pembersih Teks Ganda (Mencegah "DESA DESA JATIJAYA" / "KECAMATAN KECAMATAN")
$raw_nama_desa = isset($desa['nama_desa']) && is_string($desa['nama_desa']) ? $desa['nama_desa'] : 'Jatijaya';
$nama_desa     = trim(str_ireplace('desa', '', $raw_nama_desa));
if (empty($nama_desa)) { $nama_desa = 'Jatijaya'; }

$raw_kecamatan = isset($desa['kecamatan']) && is_string($desa['kecamatan']) ? $desa['kecamatan'] : 'Gunung Tanjung';
$kecamatan     = trim(str_ireplace('kecamatan', '', $raw_kecamatan));
if (empty($kecamatan)) { $kecamatan = 'Gunung Tanjung'; }

$raw_kabupaten = isset($desa['kabupaten']) && is_string($desa['kabupaten']) ? $desa['kabupaten'] : 'Tasikmalaya';
$kabupaten     = trim(str_ireplace(array('kabupaten', 'kota'), '', $raw_kabupaten));
if (empty($kabupaten)) { $kabupaten = 'Tasikmalaya'; }

// ==========================================
// 2. LOGIKA PENANGANAN LOGO DESA (MULTI-PATH)
// ==========================================
$logo_filename = !empty($desa['logo']) ? $desa['logo'] : '';
$logo_src = '';

if (!empty($logo_filename)) {
    // Memeriksa berbagai jalur lokasi folder upload gambar
    $possible_paths = array(
        '../uploads/' . $logo_filename,
        '../assets/img/' . $logo_filename,
        '../assets/uploads/' . $logo_filename,
        'uploads/' . $logo_filename,
        'assets/img/' . $logo_filename
    );

    foreach ($possible_paths as $path) {
        if (file_exists($path)) {
            $logo_src = $path;
            break;
        }
    }

    // Jika file fisik tidak terdeteksi via file_exists, tetap gunakan path standar
    if (empty($logo_src)) {
        $logo_src = '../uploads/' . $logo_filename;
    }
}

// ==========================================
// 3. AMBIL DATA PENGAJUAN SURAT
// ==========================================
$id = isset($_GET['id']) ? mysqli_real_escape_string($koneksi, $_GET['id']) : '';
$query = mysqli_query($koneksi, "SELECT * FROM pengajuan_surat WHERE id='$id'");
$data = ($query && mysqli_num_rows($query) > 0) ? mysqli_fetch_assoc($query) : [];

if (!$data) {
    echo "Data surat tidak ditemukan!";
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Surat Keterangan - <?php echo htmlspecialchars($data['nama'] ?? ''); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { 
            font-family: 'Times New Roman', Times, serif; 
            padding: 40px; 
            color: #000;
            background-color: #fff;
        }

        /* Garis Ganda Formal Kop Surat Official */
        .kop-surat { 
            border-bottom: 4px double #000; 
            padding-bottom: 10px; 
            margin-bottom: 25px;
        }

        /* Ukuran Logo Diperbesar dan Presisi */
        .logo-kop {
            width: 110px !important;
            height: auto !important;
            max-height: 130px;
            object-fit: contain;
            display: block;
            margin: 0 auto;
        }

        @media print {
            body { padding: 0; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>

    <!-- Tombol Cetak / Save PDF -->
    <div class="no-print mb-4 text-end">
        <button onclick="window.print()" class="btn btn-primary shadow-sm">
            <i class="fa-solid fa-print me-1"></i> Cetak / Simpan sebagai PDF
        </button>
    </div>

    <!-- KOP SURAT 3 KOLOM SIMETRIS -->
    <div class="kop-surat">
        <table class="w-100" border="0" cellspacing="0" cellpadding="0">
            <tr>
                <td width="15%" class="text-center align-middle">
                    <?php if (!empty($logo_src)) : ?>
                        <img src="<?php echo htmlspecialchars($logo_src); ?>" alt="Logo Desa" class="logo-kop">
                    <?php else : ?>
                        <div class="text-center p-2 border border-secondary rounded" style="width:90px; margin:0 auto; font-size:10pt;">
                            [ Logo ]
                        </div>
                    <?php endif; ?>
                </td>
                <td width="70%" class="text-center align-middle">
                    <h5 class="m-0 fw-bold text-uppercase" style="font-size: 14pt; letter-spacing: 0.5px;">
                        PEMERINTAH KABUPATEN <?php echo htmlspecialchars(strtoupper($kabupaten)); ?>
                    </h5>
                    <h4 class="m-0 fw-bold text-uppercase" style="font-size: 15pt; letter-spacing: 0.5px;">
                        KECAMATAN <?php echo htmlspecialchars(strtoupper($kecamatan)); ?>
                    </h4>
                    <h3 class="m-0 fw-bold text-uppercase" style="font-size: 18pt; letter-spacing: 1px;">
                        DESA <?php echo htmlspecialchars(strtoupper($nama_desa)); ?>
                    </h3>
                    <p class="m-0 mt-1" style="font-size: 9.5pt; font-style: italic; line-height: 1.3;">
                        <?php echo !empty($desa['alamat']) ? htmlspecialchars($desa['alamat']) : 'JL. kp pannusan gng cikodol, Jatijaya, Kec. Gunungtanjung, Kabupaten Tasikmalaya, Jawa Barat 46418'; ?>
                    </p>
                </td>
                <td width="15%"></td>
            </tr>
        </table>
    </div>

    <!-- JUDUL SURAT -->
    <div class="text-center mb-4">
        <h5 class="text-decoration-underline fw-bold mb-0" style="letter-spacing: 1px; font-size: 14pt;">SURAT KETERANGAN</h5>
        <div style="font-size: 11pt;">Nomor: 140 / <?php echo htmlspecialchars($data['id'] ?? ''); ?> / Pem-Des / <?php echo date('Y'); ?></div>
    </div>

    <!-- PEMBUKA SURAT -->
    <p style="text-align: justify; line-height: 1.6; font-size: 12pt;">
Yang bertanda tangan di bawah ini Kepala Desa <?php echo htmlspecialchars($nama_desa ?? ''); ?>, Kecamatan <?php echo htmlspecialchars($kecamatan ?? ''); ?>, Kabupaten <?php echo htmlspecialchars($kabupaten ?? ''); ?>, menerangkan dengan sebenarnya bahwa:
    </p>

    <!-- ISI DATA PEMOHON -->
    <table class="table table-borderless w-75 mx-auto my-3" style="font-size: 11.5pt;">
        <tr>
            <td width="32%">Nama Lengkap</td>
            <td width="3%">:</td>
            <th width="65%"><?php echo htmlspecialchars($data['nama'] ?? ''); ?></th>
        </tr>
        <tr>
            <td>NIK</td>
            <td>:</td>
            <td><?php echo htmlspecialchars($data['nik'] ?? ''); ?></td>
        </tr>
        <tr>
            <td>No. HP/WA</td>
            <td>:</td>
            <td><?php echo htmlspecialchars($data['no_hp'] ?? ''); ?></td>
        </tr>
        <tr>
            <td>Jenis Permohonan</td>
            <td>:</td>
            <td><?php echo htmlspecialchars($data['jenis_surat'] ?? ''); ?></td>
        </tr>
        <tr>
            <td>Keterangan / Keperluan</td>
            <td>:</td>
            <td><?php echo htmlspecialchars($data['keterangan'] ?? ''); ?></td>
        </tr>
    </table>

    <!-- PENUTUP SURAT -->
    <p style="text-align: justify; line-height: 1.6; font-size: 12pt;">
        Demikian surat keterangan ini dibuat dengan sebenarnya untuk dipergunakan sebagaimana mestinya.
    </p>

    <!-- TANDA TANGAN -->
    <div class="row mt-5" style="font-size: 12pt;">
        <div class="col-6"></div>
        <div class="col-6 text-center">
            <p class="mb-1"><?php echo htmlspecialchars($nama_desa ?? ''); ?>, <?php echo date('d F Y'); ?></p>
            <p class="mb-5">Kepala Desa <?php echo htmlspecialchars($nama_desa ?? ''); ?></p>
            <br><br>
            <p class="fw-bold text-decoration-underline mb-0">( _____________________ )</p>
        </div>
    </div>

    <script>
        window.onload = function() {
            window.print();
        }
    </script>
</body>
</html>