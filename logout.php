<?php
// Mulai sesi
session_start();

// Hapus semua variabel sesi
session_unset();

// Hancurkan sesi
session_destroy();

// Arahkan kembali pengguna ke halaman login
echo "<script>
        alert('Anda telah berhasil logout!');
        window.location='login.php';
      </script>";
exit;
?>