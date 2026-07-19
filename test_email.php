<?php
require 'config.php';
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$message_result = "";
$status = "";

if (isset($_POST['test_send'])) {
    $to_email = $_POST['test_email'];
    
    // Cek apakah SMTP sudah dikonfigurasi
    if (SMTP_USER === 'your-email@gmail.com' || SMTP_PASS === 'your-app-password') {
        $status = "error";
        $message_result = "Anda belum merubah kredensial SMTP di <code>config.php</code>! Silakan ubah <code>SMTP_USER</code> dan <code>SMTP_PASS</code> terlebih dahulu.";
    } else {
        $mail = new PHPMailer(true);
        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host       = SMTP_HOST;
            $mail->SMTPAuth   = true;
            $mail->Username   = SMTP_USER;
            $mail->Password   = SMTP_PASS;
            $mail->SMTPSecure = SMTP_SECURE;
            $mail->Port       = SMTP_PORT;
            
            // Debugging
            $mail->SMTPDebug = 2; // Output detailed debug log
            ob_start(); // Capture debug output
            
            // Recipients
            $mail->setFrom(SMTP_FROM, SMTP_FROM_NAME);
            $mail->addAddress($to_email);
            
            // Content
            $mail->isHTML(true);
            $mail->Subject = 'Uji Coba SMTP - SIM HIMATIF';
            $mail->Body    = 'Halo! Ini adalah email uji coba dari sistem lokal <strong>SIM HIMATIF</strong>. Jika Anda menerima email ini, konfigurasi SMTP Anda sudah berhasil dan berjalan dengan baik!';
            $mail->AltBody = 'Halo! Ini adalah email uji coba dari sistem lokal SIM HIMATIF. Jika Anda menerima email ini, konfigurasi SMTP Anda sudah berhasil!';
            
            $mail->send();
            $debug_log = ob_get_clean();
            $status = "success";
            $message_result = "Email uji coba berhasil dikirim ke <strong>" . htmlspecialchars($to_email) . "</strong>!<br><br><strong>Detail Log SMTP:</strong><pre style='background:#f4f4f5; padding:10px; border-radius:4px; font-size:11px; overflow-x:auto;'>" . htmlspecialchars($debug_log) . "</pre>";
        } catch (Exception $e) {
            $debug_log = ob_get_clean();
            $status = "error";
            $message_result = "Pengiriman gagal. PHPMailer Error: {$mail->ErrorInfo}<br><br><strong>Detail Log SMTP Error:</strong><pre style='background:#fee2e2; color:#991b1b; padding:10px; border-radius:4px; font-size:11px; overflow-x:auto;'>" . htmlspecialchars($debug_log) . "</pre>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Uji Coba SMTP - SIM HIMATIF</title>
    <link rel="stylesheet" href="style.css?v=1.3">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #0f172a;
            color: #f8fafc;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
        }
        .container {
            background-color: #1e293b;
            border: 1px solid #334155;
            padding: 30px;
            border-radius: 12px;
            max-width: 600px;
            width: 100%;
            box-shadow: 0 10px 15px -3px rgba(0,0,0,0.3);
        }
        h2 {
            margin-top: 0;
            color: #38bdf8;
            border-bottom: 1px solid #334155;
            padding-bottom: 15px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            font-size: 14px;
            color: #94a3b8;
        }
        input[type="email"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #475569;
            background-color: #0f172a;
            color: #f8fafc;
            border-radius: 6px;
            box-sizing: border-box;
            font-size: 14px;
        }
        button {
            background-color: #38bdf8;
            color: #0f172a;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.2s;
        }
        button:hover {
            background-color: #0ea5e9;
        }
        .alert {
            padding: 15px;
            border-radius: 6px;
            margin-top: 20px;
            font-size: 14px;
            line-height: 1.5;
        }
        .alert-success {
            background-color: rgba(16, 185, 129, 0.1);
            border: 1px solid rgba(16, 185, 129, 0.3);
            color: #34d399;
        }
        .alert-error {
            background-color: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #f87171;
        }
        .meta-info {
            background: #0f172a;
            padding: 12px;
            border-radius: 6px;
            font-size: 12px;
            color: #94a3b8;
            margin-bottom: 20px;
        }
        .meta-info table {
            width: 100%;
            border-collapse: collapse;
        }
        .meta-info td {
            padding: 4px 0;
        }
        .meta-info td:first-child {
            font-weight: bold;
            width: 120px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>🛠️ SMTP Connection Tester</h2>
        <p style="font-size:14px; color:#94a3b8; margin-bottom: 20px;">
            Gunakan halaman ini untuk memverifikasi apakah kredensial SMTP Anda di <code>config.php</code> sudah benar dan dapat mengirim email dari localhost.
        </p>

        <div class="meta-info">
            <strong>Konfigurasi Aktif Saat Ini:</strong>
            <table style="margin-top:8px;">
                <tr>
                    <td>SMTP Host</td>
                    <td>: <?php echo SMTP_HOST; ?></td>
                </tr>
                <tr>
                    <td>SMTP Port</td>
                    <td>: <?php echo SMTP_PORT; ?></td>
                </tr>
                <tr>
                    <td>SMTP User</td>
                    <td>: <?php echo SMTP_USER; ?></td>
                </tr>
                <tr>
                    <td>Secure Protocol</td>
                    <td>: <?php echo SMTP_SECURE; ?></td>
                </tr>
            </table>
        </div>

        <form method="POST">
            <div class="form-group">
                <label for="test_email">Alamat Email Penerima Uji Coba:</label>
                <input type="email" name="test_email" id="test_email" placeholder="contoh: email_anda@gmail.com" required value="<?php echo isset($_POST['test_email']) ? htmlspecialchars($_POST['test_email']) : ''; ?>">
            </div>
            <button type="submit" name="test_send">Kirim Email Uji Coba</button>
            <a href="lupa_password.php" style="color: #94a3b8; font-size: 14px; margin-left: 15px; text-decoration: none;">&larr; Kembali ke Lupa Password</a>
        </form>

        <?php if ($message_result !== ""): ?>
            <div class="alert alert-<?php echo $status; ?>">
                <?php echo $message_result; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
