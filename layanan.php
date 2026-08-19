<?php
$page_title = "Layanan Surat";
include 'includes/header.php';
include 'config/koneksi.php';

$pesan_status = '';

// PROSES SIMPAN PENGAJUAN SURAT
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_pengajuan'])) {
    $nik         = mysqli_real_escape_string($koneksi, trim($_POST['nik']));
    $nama        = mysqli_real_escape_string($koneksi, trim($_POST['nama']));
    $jenis_surat = mysqli_real_escape_string($koneksi, trim($_POST['jenis_surat']));
    $no_hp       = mysqli_real_escape_string($koneksi, trim($_POST['no_hp']));
    $alamat      = mysqli_real_escape_string($koneksi, trim($_POST['alamat']));
    $keterangan  = mysqli_real_escape_string($koneksi, trim($_POST['keterangan']));
    $tanggal     = date('Y-m-d H:i:s');
    $status      = 'Pending';

    if (!empty($nik) && !empty($nama) && !empty($jenis_surat) && !empty($alamat)) {
        // Query insert data memasukkan kolom alamat
        $query = "INSERT INTO pengajuan_surat (nik, nama, jenis_surat, no_hp, alamat, keterangan, status, tanggal) 
                  VALUES ('$nik', '$nama', '$jenis_surat', '$no_hp', '$alamat', '$keterangan', '$status', '$tanggal')";

        if (mysqli_query($koneksi, $query)) {
            $pesan_status = '
            <div class="alert alert-success alert-dismissible fade show rounded-4 p-3 shadow-sm mb-4" role="alert">
                <div class="d-flex align-items-center gap-3">
                    <i class="fa-solid fa-circle-check fs-2 text-success"></i>
                    <div>
                        <h6 class="fw-bold mb-1">Pengajuan Berhasil Dikirim!</h6>
                        <p class="mb-0 small text-secondary">Silakan catat NIK Anda (<strong>'.$nik.'</strong>) dan cek progres berkas pada menu <strong>Cek Surat</strong>.</p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>';
        } else {
            // Fallback jika kolom alamat di database belum dibuat
            $query_fallback = "INSERT INTO pengajuan_surat (nik, nama, jenis_surat, no_hp, keterangan, status, tanggal) 
                               VALUES ('$nik', '$nama', '$jenis_surat', '$no_hp', '$keterangan', '$status', '$tanggal')";

            if (mysqli_query($koneksi, $query_fallback)) {
                $pesan_status = '
                <div class="alert alert-success alert-dismissible fade show rounded-4 p-3 shadow-sm mb-4" role="alert">
                    <div class="d-flex align-items-center gap-3">
                        <i class="fa-solid fa-circle-check fs-2 text-success"></i>
                        <div>
                            <h6 class="fw-bold mb-1">Pengajuan Berhasil Dikirim!</h6>
                            <p class="mb-0 small text-secondary">Silakan catat NIK Anda (<strong>'.$nik.'</strong>) dan cek progres berkas pada menu <strong>Cek Surat</strong>.</p>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>';
            } else {
                $error_mysql = mysqli_error($koneksi);
                $pesan_status = '
                <div class="alert alert-danger alert-dismissible fade show rounded-4 p-3 shadow-sm mb-4" role="alert">
                    <div class="d-flex align-items-center gap-3">
                        <i class="fa-solid fa-circle-xmark fs-2 text-danger"></i>
                        <div>
                            <h6 class="fw-bold mb-1">Gagal Mengirim Pengajuan!</h6>
                            <p class="mb-0 small text-secondary">Detail Kendala: '.$error_mysql.'</p>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>';
            }
        }
    } else {
        $pesan_status = '
        <div class="alert alert-warning alert-dismissible fade show rounded-4 p-3 shadow-sm mb-4" role="alert">
            <i class="fa-solid fa-triangle-exclamation me-1"></i> Mohon lengkapi seluruh data formulir dengan benar.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>';
    }
}
?>

<style>
    /* HERO SECTION */
    .hero-layanan-section {
        background: linear-gradient(135deg, rgba(15, 81, 50, 0.92) 0%, rgba(25, 135, 84, 0.85) 100%), 
                    url('https://images.unsplash.com/photo-1450133064473-71024230f91b?auto=format&fit=crop&w=1920&q=80') center/cover no-repeat;
        padding: 90px 0 70px 0;
        border-bottom-left-radius: 40px;
        border-bottom-right-radius: 40px;
        color: #ffffff;
    }

    .card-layanan-item {
        border: none;
        border-radius: 20px;
        background: #ffffff;
        box-shadow: 0 8px 25px rgba(0,0,0,0.04);
        transition: all 0.3s ease;
    }

    .card-layanan-item:hover {
        transform: translateY(-5px);
        box-shadow: 0 12px 30px rgba(0,0,0,0.08);
    }

    .icon-layanan-box {
        width: 60px;
        height: 60px;
        background-color: #e8f5e9;
        color: #146338;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
    }
</style>

<!-- HERO HEADER BANNER -->
<section class="hero-layanan-section text-center mb-4">
    <div class="container">
        <span class="badge bg-white text-success px-3 py-2 rounded-pill fw-bold mb-3 shadow-sm">
            <i class="fa-solid fa-file-signature me-1"></i> Pelayanan Publik Digital
        </span>
        <h1 class="fw-extrabold display-5 mb-2">Layanan Surat Keterangan Online</h1>
        <p class="lead opacity-90 mx-auto" style="max-width: 700px;">
            Ajukan pembuatan surat administrasi desa secara cepat, mudah, dan transparan dari mana saja.
        </p>
    </div>
</section>

<!-- KONTEN UTAMA -->
<div class="container py-4 mb-5">
    
    <!-- NOTIFIKASI STATUS -->
    <?php echo $pesan_status; ?>

    <div class="text-center mb-5">
        <span class="badge bg-success bg-opacity-10 text-success fw-bold px-3 py-2 rounded-pill mb-2">
            <i class="fa-solid fa-list me-1"></i> Pilih Surat
        </span>
        <h2 class="fw-bold text-dark">Daftar Layanan Surat Keterangan</h2>
        <p class="text-muted small">Klik tombol <strong>'Buat Surat'</strong> pada jenis permohonan yang Anda butuhkan untuk mengisi formulir secara online.</p>
    </div>

    <!-- GRID PILIHAN SURAT -->
    <div class="row g-4">
        <!-- SURAT 1 -->
        <div class="col-md-6 col-lg-4">
            <div class="card card-layanan-item p-4 h-100 d-flex flex-column justify-content-between">
                <div>
                    <div class="icon-layanan-box mb-3">
                        <i class="fa-solid fa-house-chimney"></i>
                    </div>
                    <h5 class="fw-bold text-dark mb-2">Surat Keterangan Domisili</h5>
                    <p class="text-muted small mb-4">Surat bukti tempat tinggal atau domisili resmi warga di wilayah Desa Jatijaya.</p>
                </div>
                <button type="button" class="btn btn-success fw-bold rounded-3 w-100 py-2" onclick="openModalSurat('Surat Keterangan Domisili')">
                    <i class="fa-solid fa-pen-to-square me-1"></i> Buat Surat
                </button>
            </div>
        </div>

        <!-- SURAT 2 -->
        <div class="col-md-6 col-lg-4">
            <div class="card card-layanan-item p-4 h-100 d-flex flex-column justify-content-between">
                <div>
                    <div class="icon-layanan-box mb-3">
                        <i class="fa-solid fa-hand-holding-heart"></i>
                    </div>
                    <h5 class="fw-bold text-dark mb-2">Surat Keterangan Tidak Mampu (SKTM)</h5>
                    <p class="text-muted small mb-4">Persyaratan pengajuan beasiswa pendidikan, pengobatan gratis, atau bantuan sosial.</p>
                </div>
                <button type="button" class="btn btn-success fw-bold rounded-3 w-100 py-2" onclick="openModalSurat('Surat Keterangan Tidak Mampu (SKTM)')">
                    <i class="fa-solid fa-pen-to-square me-1"></i> Buat Surat
                </button>
            </div>
        </div>

        <!-- SURAT 3 -->
        <div class="col-md-6 col-lg-4">
            <div class="card card-layanan-item p-4 h-100 d-flex flex-column justify-content-between">
                <div>
                    <div class="icon-layanan-box mb-3">
                        <i class="fa-solid fa-store"></i>
                    </div>
                    <h5 class="fw-bold text-dark mb-2">Surat Keterangan Usaha (SKU)</h5>
                    <p class="text-muted small mb-4">Surat keterangan legalitas usaha lokal warga untuk pengajuan bantuan atau pinjaman bank.</p>
                </div>
                <button type="button" class="btn btn-success fw-bold rounded-3 w-100 py-2" onclick="openModalSurat('Surat Keterangan Usaha (SKU)')">
                    <i class="fa-solid fa-pen-to-square me-1"></i> Buat Surat
                </button>
            </div>
        </div>

        <!-- SURAT 4 -->
        <div class="col-md-6 col-lg-4">
            <div class="card card-layanan-item p-4 h-100 d-flex flex-column justify-content-between">
                <div>
                    <div class="icon-layanan-box mb-3">
                        <i class="fa-solid fa-user-shield"></i>
                    </div>
                    <h5 class="fw-bold text-dark mb-2">Surat Pengantar SKCK</h5>
                    <p class="text-muted small mb-4">Surat pengantar rekomendasi pembuatan SKCK di kantor kepolisian / Polsek.</p>
                </div>
                <button type="button" class="btn btn-success fw-bold rounded-3 w-100 py-2" onclick="openModalSurat('Surat Pengantar SKCK')">
                    <i class="fa-solid fa-pen-to-square me-1"></i> Buat Surat
                </button>
            </div>
        </div>

        <!-- SURAT 5 -->
        <div class="col-md-6 col-lg-4">
            <div class="card card-layanan-item p-4 h-100 d-flex flex-column justify-content-between">
                <div>
                    <div class="icon-layanan-box mb-3">
                        <i class="fa-solid fa-id-card"></i>
                    </div>
                    <h5 class="fw-bold text-dark mb-2">Surat Keterangan Beda Nama</h5>
                    <p class="text-muted small mb-4">Keterangan resmi kekeliruan penulisan identitas pada dokumen KTP/KK/Ijazah.</p>
                </div>
                <button type="button" class="btn btn-success fw-bold rounded-3 w-100 py-2" onclick="openModalSurat('Surat Keterangan Beda Nama')">
                    <i class="fa-solid fa-pen-to-square me-1"></i> Buat Surat
                </button>
            </div>
        </div>

        <!-- SURAT 6 -->
        <div class="col-md-6 col-lg-4">
            <div class="card card-layanan-item p-4 h-100 d-flex flex-column justify-content-between">
                <div>
                    <div class="icon-layanan-box mb-3">
                        <i class="fa-solid fa-file-lines"></i>
                    </div>
                    <h5 class="fw-bold text-dark mb-2">Surat Keterangan Umum</h5>
                    <p class="text-muted small mb-4">Permohonan dokumen administrasi lainnya yang membutuhkan keterangan Kepala Desa.</p>
                </div>
                <button type="button" class="btn btn-success fw-bold rounded-3 w-100 py-2" onclick="openModalSurat('Surat Keterangan Umum')">
                    <i class="fa-solid fa-pen-to-square me-1"></i> Buat Surat
                </button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL FORMULIR PENGAJUAN -->
<div class="modal fade" id="modalPengajuan" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 rounded-4 shadow-lg">
            <div class="modal-header bg-success text-white p-4 rounded-top-4">
                <h5 class="modal-title fw-bold" id="modalTitleText">
                    <i class="fa-solid fa-file-pen me-2"></i> Formulir Pengajuan Surat
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <form action="layanan.php" method="POST">
                <div class="modal-body p-4">
                    <input type="hidden" name="jenis_surat" id="inputJenisSurat">

                    <div class="mb-3">
                        <label class="form-label fw-bold text-secondary small">Jenis Surat Yang Diajukan</label>
                        <input type="text" id="displayJenisSurat" class="form-control bg-light fw-bold text-success border-0 py-2" readonly>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-secondary small">NIK Pemohon (16 Digit) <span class="text-danger">*</span></label>
                            <input type="number" name="nik" class="form-control py-2" placeholder="Contoh: 3201234567890001" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-secondary small">Nama Lengkap Pemohon <span class="text-danger">*</span></label>
                            <input type="text" name="nama" class="form-control py-2" placeholder="Sesuai KTP" required>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-secondary small">Nomor WhatsApp / HP <span class="text-danger">*</span></label>
                            <input type="text" name="no_hp" class="form-control py-2" placeholder="Contoh: 081234567890" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-secondary small">Keperluan / Alasan Pengajuan <span class="text-danger">*</span></label>
                            <input type="text" name="keterangan" class="form-control py-2" placeholder="Misal: Persyaratan Melamar Kerja" required>
                        </div>
                    </div>

                    <!-- INPUT ALAMAT LENGKAP -->
                    <div class="mb-3">
                        <label class="form-label fw-bold text-secondary small">Alamat Lengkap Pemohon <span class="text-danger">*</span></label>
                        <textarea name="alamat" class="form-control py-2" rows="3" placeholder="Contoh: Dusun pasirmuncang RT 016/RW 05, Desa Jatijaya" required></textarea>
                    </div>
                </div>
                
                <div class="modal-footer bg-light p-3 rounded-bottom-4">
                    <button type="button" class="btn btn-secondary rounded-3 px-4 fw-bold" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="submit_pengajuan" class="btn btn-success rounded-3 px-4 fw-bold">
                        <i class="fa-solid fa-paper-plane me-1"></i> Kirim Permohonan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function openModalSurat(jenisSurat) {
        document.getElementById('inputJenisSurat').value = jenisSurat;
        document.getElementById('displayJenisSurat').value = jenisSurat;
        
        var modalElement = new bootstrap.Modal(document.getElementById('modalPengajuan'));
        modalElement.show();
    }
</script>

<?php include 'includes/footer.php'; ?>