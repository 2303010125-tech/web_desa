<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Koneksi ke database
if (file_exists('../config/koneksi.php')) {
    include '../config/koneksi.php';
} else {
    include 'config/koneksi.php';
}

if (isset($_GET['id']) && !empty($_GET['id'])) {
    $id = (int)$_GET['id'];

    // Cek otomatis apakah nama kolomnya 'id' atau 'id_pengajuan'
    $cek_kolom = mysqli_query($koneksi, "SHOW COLUMNS FROM pengajuan_surat LIKE 'id'");
    $kolom_pk = (mysqli_num_rows($cek_kolom) > 0) ? 'id' : 'id_pengajuan';

    // Eksekusi Hapus Data
    $query = "DELETE FROM pengajuan_surat WHERE $kolom_pk = $id";
    $hapus = mysqli_query($koneksi, $query);

    if ($hapus) {
        if (mysqli_affected_rows($koneksi) > 0) {
            header("Location: layanan.php?pesan=sukses_hapus");
            exit();
        } else {
            die("Query berhasil dijalankan, tetapi tidak ada data yang terhapus. Cek apakah ID ($id) ada di database.");
        }
    } else {
        // Tampilkan error MySQL jika gagal
        die("Gagal menghapus data! Pesan Error Database: " . mysqli_error($koneksi));
    }
} else {
    die("ID pengajuan tidak ditemukan pada URL.");
}
?>