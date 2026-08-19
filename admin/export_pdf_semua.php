<?php
session_start();
require_once '../config/koneksi.php';

/** @var mysqli $koneksi */

// Proteksi Sesi Admin
if (!isset($_SESSION['admin_logged_in'])) {
    exit("Akses ditolak! Anda harus login terlebih dahulu.");
}

// Ambil Data Pengajuan Surat
$query = mysqli_query($koneksi, "SELECT * FROM pengajuan_surat ORDER BY id DESC");

function tgl_indo($tanggal) {
    $bulan = array (
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    );
    $pecahkan = explode('-', $tanggal);
    return $pecahkan[2] . ' ' . $bulan[(int)$pecahkan[1]] . ' ' . $pecahkan[0];
}

$tanggal_sekarang = tgl_indo(date('Y-m-d'));
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan_Pengajuan_Surat_<?php echo date('Ymd'); ?></title>
    <style>
        body { 
            font-family: Arial, Helvetica, sans-serif; 
            font-size: 12px; 
            color: #333;
            margin: 20px;
        }
        .header { 
            text-align: center; 
            margin-bottom: 20px; 
            border-bottom: 2px solid #146338;
            padding-bottom: 10px;
        }
        .header h2 { 
            margin: 0 0 5px 0; 
            color: #146338;
            text-transform: uppercase;
            font-size: 18px;
        }
        .header p { 
            margin: 0; 
            font-size: 12px;
            color: #666;
        }
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 10px; 
        }
        th, td { 
            border: 1px solid #cccccc; 
            padding: 8px 10px; 
            text-align: left; 
            vertical-align: middle;
        }
        th { 
            background-color: #146338 !important; 
            color: #ffffff !important;
            font-weight: bold;
            text-align: center;
            text-transform: uppercase;
            font-size: 11px;
            --webkit-print-color-adjust: exact;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .text-center { text-align: center; }
        .badge {
            padding: 4px 8px;
            font-weight: bold;
            font-size: 10px;
            border-radius: 4px;
            display: inline-block;
            text-align: center;
        }
        .status-selesai { background-color: #d4edda; color: #155724; }
        .status-proses { background-color: #fff3cd; color: #856404; }
        .status-ditolak { background-color: #f8d7da; color: #721c24; }
        .status-pending { background-color: #e2e3e5; color: #383d41; }

        /* Sembunyikan Tombol saat di-print */
        @media print {
            .no-print { display: none !important; }
            @page { size: landscape; margin: 10mm; }
            body { --webkit-print-color-adjust: exact; }
        }
    </style>
</head>
<body>

    <!-- Tombol Aksi Cetak / Simpan PDF -->
    <div class="no-print" style="margin-bottom: 20px; text-align: right;">
        <button onclick="window.print()" style="padding: 10px 20px; background-color: #146338; color: white; border: none; border-radius: 5px; cursor: pointer; font-weight: bold;">
            🖨️ Cetak / Save as PDF
        </button>
        <button onclick="window.close()" style="padding: 10px 20px; background-color: #6c757d; color: white; border: none; border-radius: 5px; cursor: pointer; font-weight: bold; margin-left: 5px;">
            Tutup
        </button>
    </div>

    <div class="header">
        <h2>Laporan Rekapitulasi Pengajuan Surat Warga</h2>
        <p>Pemerintah Desa Jatijaya | Tanggal Cetak: <?php echo $tanggal_sekarang; ?></p>
    </div>

    <table>
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="15%">NIK</th>
                <th width="20%">Nama Pemohon</th>
                <th width="30%">Jenis Surat</th>
                <th width="15%">No. HP / WA</th>
                <th width="15%">Status</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $no = 1;
            if ($query && mysqli_num_rows($query) > 0):
                while ($row = mysqli_fetch_assoc($query)):
                    $status_raw = strtolower($row['status'] ?? '');
                    $status_class = 'status-pending';
                    
                    if (strpos($status_raw, 'selesai') !== false || strpos($status_raw, 'disetujui') !== false) {
                        $status_class = 'status-selesai';
                    } elseif (strpos($status_raw, 'proses') !== false) {
                        $status_class = 'status-proses';
                    } elseif (strpos($status_raw, 'tolak') !== false) {
                        $status_class = 'status-ditolak';
                    }
            ?>
                    <tr>
                        <td class="text-center"><?php echo $no++; ?></td>
                        <td><?php echo htmlspecialchars($row['nik'] ?? '-'); ?></td>
                        <td><?php echo htmlspecialchars($row['nama'] ?? '-'); ?></td>
                        <td><?php echo htmlspecialchars($row['jenis_surat'] ?? '-'); ?></td>
                        <td class="text-center"><?php echo htmlspecialchars($row['no_hp'] ?? '-'); ?></td>
                        <td class="text-center">
                            <span class="badge <?php echo $status_class; ?>"><?php echo strtoupper(htmlspecialchars($row['status'] ?? 'PENDING')); ?></span>
                        </td>
                    </tr>
            <?php 
                endwhile;
            else:
            ?>
                <tr>
                    <td colspan="6" class="text-center" style="padding: 20px;">Belum ada data pengajuan surat.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <script>
        // Otomatis buka dialog cetak/save PDF saat halaman dibuka
        window.onload = function() {
            window.print();
        };
    </script>
</body>
</html>