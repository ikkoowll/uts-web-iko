<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "pengelolaan_data_internal_ukm";

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

// ==========================================
// KONFIGURASI SMTP UNTUK PENGIRIMAN EMAIL
// ==========================================
// Silakan sesuaikan nilai di bawah ini dengan provider SMTP Anda (misal Gmail, Mailtrap, Brevo, dll)
define('SMTP_HOST', 'smtp.gmail.com');       // Host SMTP (misal: smtp.gmail.com atau sandbox.smtp.mailtrap.io)
define('SMTP_PORT', 587);                    // Port SMTP (587 untuk TLS/STARTTLS, 465 untuk SSL, atau 2525 untuk Mailtrap)
define('SMTP_USER', 'your-email@gmail.com'); // Username/Email SMTP Anda
define('SMTP_PASS', 'your-app-password');    // Password SMTP atau App Password (jika menggunakan Gmail)
define('SMTP_FROM', 'your-email@gmail.com'); // Email pengirim yang akan muncul di inbox penerima
define('SMTP_FROM_NAME', 'SIM HIMATIF');     // Nama pengirim yang muncul
define('SMTP_SECURE', 'tls');                // Pengaman (tls atau ssl)
?>