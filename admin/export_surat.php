<?php
session_start();
include __DIR__ . '/../config/koneksi.php';

// Header khusus untuk memberi tahu browser bahwa ini file Excel
header("Content-type: application/vnd-ms-excel");
header("Content-Disposition: attachment; filename=Rekap_Pengajuan_Surat_Desa_Jatijaya.xls");

$query = mysqli_query($koneksi, "SELECT * FROM pengajuan_surat ORDER BY id DESC");
?>

<h3 style="text-align: center;">REKAPITULASI PENGAJUAN SURAT DESA JATIJAYA</h3>

<table border="1" cellpadding="5" cellspacing="0">
    <thead>
        <tr style="background-color: #28a745; color: #ffffff;">
            <th>No</th>
            <th>NIK</th>
            <th>Nama Lengkap</th>
            <th>Jenis Surat</th>
            <th>No. HP / WA</th>
            <th>Keperluan / Keterangan</th>
            <th>Status</th>
            <th>Tanggal Pengajuan</th>
        </tr>
    </thead>
    <tbody>
        <?php if (mysqli_num_rows($query) > 0): ?>
            <?php $no = 1; while ($d = mysqli_fetch_assoc($query)): ?>
                <tr>
                    <td><?php echo $no++; ?></td>
                    <td style="mso-number-format:'\@';"><?php echo htmlspecialchars($d['nik']); ?></td>
                    <td><?php echo htmlspecialchars($d['nama']); ?></td>
                    <td><?php echo htmlspecialchars($d['jenis_surat']); ?></td>
                    <td style="mso-number-format:'\@';"><?php echo htmlspecialchars($d['no_hp']); ?></td>
                    <td><?php echo htmlspecialchars($d['keterangan']); ?></td>
                    <td><?php echo htmlspecialchars($d['status']); ?></td>
                    <td><?php echo isset($d['tanggal_pengajuan']) ? $d['tanggal_pengajuan'] : '-'; ?></td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="8" style="text-align: center;">Belum ada data pengajuan surat.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>