<?php
session_start();
require 'config.php';
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

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
            
            // Cek apakah SMTP sudah dikonfigurasi (bukan nilai default placeholder)
            $is_smtp_configured = (SMTP_USER !== 'your-email@gmail.com' && SMTP_PASS !== 'your-app-password');
            
            $success_email = false;
            $smtp_error = "";
            
            if ($is_smtp_configured) {
                $mail = new PHPMailer(true);
                try {
                    // Pengaturan Server SMTP
                    $mail->isSMTP();
                    $mail->Host       = SMTP_HOST;
                    $mail->SMTPAuth   = true;
                    $mail->Username   = SMTP_USER;
                    $mail->Password   = SMTP_PASS;
                    $mail->SMTPSecure = SMTP_SECURE;
                    $mail->Port       = SMTP_PORT;
                    $mail->CharSet    = 'UTF-8';
                    
                    // Penerima & Pengirim
                    $mail->setFrom(SMTP_FROM, SMTP_FROM_NAME);
                    $mail->addAddress($email, $nama_lengkap);
                    
                    // Konten Email
                    $mail->isHTML(true);
                    $mail->Subject = 'Reset Kata Sandi SIM HIMATIF';
                    
                    $mail->Body = "
                    <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #e5e7eb; border-radius: 8px; background-color: #ffffff; color: #333333;'>
                        <div style='text-align: center; margin-bottom: 20px;'>
                            <h2 style='color: #4f46e5; margin: 0;'>SIM HIMATIF</h2>
                        </div>
                        <hr style='border: 0; border-top: 1px solid #e5e7eb; margin-bottom: 20px;'>
                        <p>Halo <strong>" . htmlspecialchars($nama_lengkap) . "</strong>,</p>
                        <p>Kami menerima permintaan untuk mereset kata sandi Anda di <strong>SIM HIMATIF</strong>.</p>
                        <p>Silakan klik tombol di bawah ini untuk mereset kata sandi Anda. Tautan ini hanya berlaku selama <strong>1 jam</strong>:</p>
                        <div style='text-align: center; margin: 30px 0;'>
                            <a href='" . $reset_link . "' style='background-color: #4f46e5; color: #ffffff; padding: 12px 24px; text-decoration: none; border-radius: 6px; font-weight: bold; display: inline-block;'>Reset Kata Sandi</a>
                        </div>
                        <p style='color: #ef4444; font-size: 13px;'>* Jika Anda tidak meminta pengaturan ulang kata sandi ini, silakan abaikan email ini.</p>
                        <hr style='border: 0; border-top: 1px solid #e5e7eb; margin: 20px 0;'>
                        <p style='color: #9ca3af; font-size: 11px; text-align: center; margin: 0;'>Email ini dikirim secara otomatis oleh SIM HIMATIF. Jangan membalas email ini.</p>
                    </div>";
                    
                    $mail->AltBody = "Halo " . $nama_lengkap . ",\n\nKami menerima permintaan untuk mereset kata sandi Anda di SIM HIMATIF.\n\nSilakan klik tautan berikut untuk mereset kata sandi Anda (Berlaku 1 jam):\n" . $reset_link . "\n\nJika Anda tidak meminta ini, silakan abaikan email ini.\n\nSalam,\nAdmin HIMATIF";
                    
                    $mail->send();
                    $success_email = true;
                } catch (Exception $e) {
                    $smtp_error = "PHPMailer Error: " . $mail->ErrorInfo;
                }
            } else {
                $smtp_error = "Konfigurasi SMTP di config.php masih default (belum diubah dari 'your-email@gmail.com').";
            }
            
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
            <?php if(isset($success_email) && $success_email): ?>
                <div style="background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.2); padding: 20px; border-radius: 12px; margin-bottom: 20px; text-align: left;">
                    <h4 style="color: var(--success-color); margin-top: 0; margin-bottom: 8px;">📧 Email Reset Dikirim!</h4>
                    <p style="font-size: 13px; color: var(--text-secondary); margin-bottom: 12px; line-height: 1.4;">
                        Tautan reset kata sandi telah berhasil dikirim ke email <strong><?php echo htmlspecialchars($email); ?></strong>. Silakan cek kotak masuk (inbox) atau folder spam email Anda.
                    </p>
                    <p style="font-size: 12px; color: var(--text-secondary); margin-bottom: 0;">
                        <em>Masa berlaku link ini adalah 1 jam sejak email terkirim.</em>
                    </p>
                </div>
            <?php else: ?>
                <div style="background: rgba(245, 158, 11, 0.1); border: 1px solid rgba(245, 158, 11, 0.2); padding: 20px; border-radius: 12px; margin-bottom: 20px; text-align: left;">
                    <h4 style="color: #f59e0b; margin-top: 0; margin-bottom: 8px;">⚠️ Simulasi Link Reset (Email Gagal Terkirim)</h4>
                    <p style="font-size: 13px; color: var(--text-secondary); margin-bottom: 12px; line-height: 1.4;">
                        Sistem gagal mengirimkan email asli secara otomatis.<br>
                        <strong>Penyebab/Pesan:</strong> <code style="color: #f87171;"><?php echo htmlspecialchars($smtp_error); ?></code><br><br>
                        <em>Sebagai alternatif untuk pengujian lokal, gunakan tautan simulasi berikut untuk mereset kata sandi Anda:</em>
                    </p>
                    <div style="background: rgba(0, 0, 0, 0.2); padding: 12px; border-radius: 8px; font-family: monospace; font-size: 11px; word-break: break-all; border: 1px solid var(--card-border); margin-bottom: 14px;">
                        <a href="<?php echo $reset_link; ?>" style="color: #60a5fa; text-decoration: underline; font-weight: bold;" target="_blank"><?php echo $reset_link; ?></a>
                    </div>
                    <p style="font-size: 12px; color: var(--text-secondary); margin-bottom: 0;">
                        <em>Silakan konfigurasikan SMTP dengan benar di <code>config.php</code> agar dapat mengirim email asli.</em>
                    </p>
                </div>
            <?php endif; ?>
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
