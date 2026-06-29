<?php
// Mulai sesi
session_start();

// Hapus semua variabel sesi
session_unset();

// Hancurkan sesi
session_destroy();

// Arahkan kembali pengguna ke halaman login dengan parameter status
header("Location: login.php?logout=success");
exit;
?>