<?php
$page_title = "Potensi & UMKM";
include 'includes/header.php';
include 'config/koneksi.php';

$query_potensi = mysqli_query($koneksi, "SELECT * FROM potensi_desa ORDER BY id DESC");
?>

<div class="bg-success text-white py-5 mt-5">
    <div class="container text-center pt-4">
        <h2 class="fw-bold">Potensi Ekonomi & Wisata</h2>
    </div>
</div>

<div class="container py-5">
    <div class="row g-4">
        <?php while ($p = mysqli_fetch_assoc($query_potensi)): ?>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100">
                <img src="<?php echo htmlspecialchars($p['foto']); ?>" class="card-img-top" style="height: 200px; object-fit: cover;" alt="Foto">
                <div class="card-body p-4">
                    <span class="badge bg-success mb-2"><?php echo htmlspecialchars($p['kategori']); ?></span>
                    <h5 class="fw-bold text-dark"><?php echo htmlspecialchars($p['nama']); ?></h5>
                    <p class="text-secondary small mb-0"><?php echo htmlspecialchars($p['deskripsi']); ?></p>
                </div>
            </div>
        </div>
        <?php endwhile; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>