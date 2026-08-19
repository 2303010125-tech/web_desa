<footer class="bg-dark text-white pt-4 pb-3 mt-5">
    <div class="container text-center">
        <p class="mb-0 small">&copy; <?= date('Y'); ?> Pemerintah Desa Jatijaya. All Rights Reserved.</p>
    </div>
</footer>

<!-- MODAL NOTIFIKASI KONFIRMASI LOGOUT -->
<div class="modal fade" id="logoutModal" tabindex="-1" aria-labelledby="logoutModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-body text-center p-4">
                <div class="mb-3 text-warning">
                    <i class="fa-solid fa-triangle-exclamation fa-3x"></i>
                </div>
                <h5 class="fw-bold text-dark mb-2">Konfirmasi Keluar</h5>
                <p class="text-muted small mb-4">Apakah Anda yakin ingin keluar dari sistem pelayanan Desa Jatijaya?</p>
                <div class="d-flex justify-content-center gap-2">
                    <button type="button" class="btn btn-light px-4 rounded-pill fw-semibold" data-bs-dismiss="modal">Batal</button>
                    <a href="logout.php" class="btn btn-danger px-4 rounded-pill fw-semibold">Ya, Logout</a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap 5 JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>