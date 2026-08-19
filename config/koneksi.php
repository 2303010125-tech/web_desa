<?php
$host     = "localhost:3307"; // TAMBAHKAN :3307 di sebelah localhost
$user     = "root";
$password = "";               // Password default XAMPP biasanya kosong
$database = "db_profil_desa_jatijaya";

$koneksi = mysqli_connect($host, $user, $password, $database);

if (!$koneksi) {
    die("Koneksi ke database MySQL gagal: " . mysqli_connect_error());
}
?>