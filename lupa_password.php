<?php
session_start();
require 'config.php';

if (isset($_POST['kirim'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    
    // Cek apakah email ada di database
    $query = mysqli_query($conn, "SELECT * FROM users WHERE email = '$email'");
    
    if (mysqli_num_rows($query) > 0) {
        $user_data = mysqli_fetch_assoc($query);
        $username = $user_data['username'];
        $nama_lengkap = $user_data['nama_lengkap'];
        
        // Generate secure token
        $token = bin2hex(random_bytes(32));
        
        // Simpan token ke database dengan masa berlaku 1 jam
        $update = mysqli_query($conn, "
            UPDATE users 
            SET reset_token = '$token', 
                reset_expiry = DATE_ADD(NOW(), INTERVAL 1 HOUR) 
            WHERE email = '$email'
        ");
        
        if ($update) {
            // Buat link reset
            $reset_link = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/reset_password.php?token=" . $token;
            
            // Mencoba mengirim email asli (fallback)
            $to = $email;
            $subject = "Reset Kata Sandi SIM HIMATIF";
            $message = "Halo " . $nama_lengkap . ",\n\nKami menerima permintaan untuk mereset kata sandi Anda di SIM HIMATIF.\n\nKlik link berikut untuk mereset kata sandi Anda (Berlaku 1 jam):\n" . $reset_link . "\n\nJika Anda tidak meminta ini, silakan abaikan email ini.\n\nSalam,\nAdmin HIMATIF";
            $headers = "From: admin@himatif.org";
            
            // Coba kirim email asli
            @mail($to, $subject, $message, $headers);
            
            // Tampilkan info simulasi email dikirim di localhost
            $success_simulasi = true;
        } else {
            $error = "Terjadi kesalahan sistem, gagal memproses token.";
        }
    } else {
        $error = "Email tidak terdaftar di sistem SIM HIMATIF!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Password - SIM HIMATIF</title>
    <link rel="stylesheet" href="style.css?v=1.3">
</head>
<body class="login-page">
    <div class="login-card" style="max-width: 480px;">
        <h2>Lupa Kata Sandi?</h2>
        
        <?php if(isset($error)) echo "<p class='error-msg'>$error</p>"; ?>
        
        <?php if(isset($success_simulasi) && $success_simulasi): ?>
            <div style="background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.2); padding: 20px; border-radius: 12px; margin-bottom: 20px; text-align: left;">
                <h4 style="color: var(--success-color); margin-top: 0; margin-bottom: 8px;">📧 Simulasi Email Reset Dikirim!</h4>
                <p style="font-size: 13px; color: var(--text-secondary); margin-bottom: 12px; line-height: 1.4;">
                    Karena sistem dijalankan di <strong>localhost</strong>, email konfirmasi tidak dapat terkirim secara otomatis. Silakan gunakan tautan di bawah ini untuk mereset sandi Anda:
                </p>
                <div style="background: rgba(0, 0, 0, 0.2); padding: 12px; border-radius: 8px; font-family: monospace; font-size: 11px; word-break: break-all; border: 1px solid var(--card-border); margin-bottom: 14px;">
                    <a href="<?php echo $reset_link; ?>" style="color: #60a5fa; text-decoration: underline; font-weight: bold;" target="_blank"><?php echo $reset_link; ?></a>
                </div>
                <p style="font-size: 12px; color: var(--text-secondary); margin-bottom: 0;">
                    <em>Masa berlaku link ini adalah 1 jam sejak email disimulasikan.</em>
                </p>
            </div>
            <div class="register-link" style="margin-top: 10px;">
                <a href="login.php" style="color: var(--primary-color); font-weight: 600; text-decoration: none;">&larr; Kembali ke halaman Login</a>
            </div>
        <?php else: ?>
            <p style="font-size: 14px; color: var(--text-secondary); text-align: center; margin-bottom: 24px; line-height: 1.5;">
                Masukkan alamat email yang terdaftar pada akun pengurus Anda. Kami akan mengirimkan tautan reset kata sandi ke email tersebut.
            </p>
            
            <form method="POST" action="">
                <input type="email" name="email" placeholder="Alamat Email Terdaftar" required style="margin-bottom: 16px;">
                <button type="submit" name="kirim">Kirim Link Reset</button>
            </form>

            <div class="register-link">
                Ingat kata sandi? <a href="login.php">Login di sini</a>
            </div>
        <?php endif; ?>
    </div>
    <?php include 'footer.php'; ?>
</body>
</html>
