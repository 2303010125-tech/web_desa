<?php
include __DIR__ . '/../config/koneksi.php';

// Header khusus untuk memicu download format Excel (.xls)
header("Content-type: application/vnd-ms-excel");
header("Content-Disposition: attachment; filename=Laporan_Pengajuan_Surat_Desa_Jatijaya.xls");

$query = mysqli_query($koneksi, "SELECT * FROM pengajuan_surat ORDER BY id DESC");
?>

<h2>Laporan Pengajuan Surat Warga - Desa Jatijaya</h2>
<table border="1" cellpadding="5" cellspacing="0">
    <thead>
        <tr style="background-color: #198754; color: white;">
            <th>No</th>
            <th>NIK</th>
            <th>Nama Warga</th>
            <th>Jenis Surat</th>
            <th>No. HP</th>
            <th>Keterangan / Keperluan</th>
            <th>Status</th>
            <th>Tanggal Pengajuan</th>
        </tr>
    </thead>
    <tbody>
        <?php 
        $no = 1;
        while ($row = mysqli_fetch_assoc($query)): 
        ?>
        <tr>
            <td><?php echo $no++; ?></td>
            <td style="mso-number-format:'\@';"><?php echo htmlspecialchars($row['nik']); ?></td>
            <td><?php echo htmlspecialchars($row['nama']); ?></td>
            <td><?php echo htmlspecialchars($row['jenis_surat']); ?></td>
            <td style="mso-number-format:'\@';"><?php echo htmlspecialchars($row['no_hp']); ?></td>
            <td><?php echo htmlspecialchars($row['keterangan']); ?></td>
            <td><?php echo htmlspecialchars($row['status'] ?? 'Pending'); ?></td>
            <td><?php echo date('d-m-Y H:i', strtotime($row['created_at'] ?? 'now')); ?></td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>