<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!-- SweetAlert2 CDN (Dark Theme & JS) -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@sweetalert2/theme-dark@4/dark.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. Notifikasi Sukses (Auto-close 2 detik)
    <?php if (isset($_SESSION['swal_success'])): ?>
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: '<?php echo htmlspecialchars($_SESSION['swal_success'], ENT_QUOTES, 'UTF-8'); ?>',
            timer: 2000,
            showConfirmButton: false,
            background: '#130d2b',
            color: '#f3f4f6',
            timerProgressBar: true
        });
        <?php unset($_SESSION['swal_success']); ?>
    <?php endif; ?>

    // 2. Notifikasi Error
    <?php if (isset($_SESSION['swal_error'])): ?>
        Swal.fire({
            icon: 'error',
            title: 'Gagal!',
            text: '<?php echo htmlspecialchars($_SESSION['swal_error'], ENT_QUOTES, 'UTF-8'); ?>',
            background: '#130d2b',
            color: '#f3f4f6',
            confirmButtonColor: '#8b5cf6'
        });
        <?php unset($_SESSION['swal_error']); ?>
    <?php endif; ?>

    // 3. Konfirmasi Hapus Data
    document.addEventListener('click', function(e) {
        const deleteBtn = e.target.closest('.btn-hapus');
        if (deleteBtn) {
            e.preventDefault();
            const href = deleteBtn.getAttribute('href');
            const message = deleteBtn.getAttribute('data-message') || 'Yakin ingin menghapus data ini?';
            
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: message,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444', // Warna bahaya/hapus
                cancelButtonColor: '#8b5cf6', // Warna utama/batal
                confirmButtonText: 'Ya, Hapus',
                cancelButtonText: 'Batal',
                background: '#130d2b',
                color: '#f3f4f6'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = href;
                }
            });
        }
    });
});
</script>
