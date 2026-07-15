<?php
session_start();
require 'config.php';

$valid_token = false;
$token = '';

if (isset($_GET['token'])) {
    $token = mysqli_real_escape_string($conn, $_GET['token']);
    
    // Verifikasi token dan masa berlakunya
    $query = mysqli_query($conn, "
        SELECT * FROM users 
        WHERE reset_token = '$token' 
          AND reset_expiry > NOW()
    ");
    
    if (mysqli_num_rows($query) > 0) {
        $valid_token = true;
        $user_data = mysqli_fetch_assoc($query);
        $username = $user_data['username'];
    }
}

if (isset($_POST['reset'])) {
    $password_baru = $_POST['password'];
    $konfirmasi_password = $_POST['konfirmasi_password'];
    
    if ($password_baru !== $konfirmasi_password) {
        $error = "Konfirmasi kata sandi tidak cocok!";
        $valid_token = true; // Tetap biarkan form tampil
    } else {
        // Enkripsi kata sandi baru menggunakan MD5 sesuai sistem login/register yang ada
        $password_md5 = md5($password_baru);
        
        // Update password dan kosongkan kembali token
        $update = mysqli_query($conn, "
            UPDATE users 
            SET password = '$password_md5', 
                reset_token = NULL, 
                reset_expiry = NULL 
            WHERE reset_token = '$token'
        ");
        
        if ($update) {
            $_SESSION['swal_success'] = 'Kata sandi berhasil diatur ulang! Silakan Login';
            header("Location: login.php");
            exit;
        } else {
            $error = "Gagal memperbarui kata sandi, terjadi kesalahan sistem.";
            $valid_token = true;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - SIM HIMATIF</title>
    <link rel="stylesheet" href="style.css?v=1.3">
</head>
<body class="login-page">
    <div class="login-card">
        <h2>Reset Kata Sandi</h2>
        
        <?php if(isset($error)) echo "<p class='error-msg'>$error</p>"; ?>
        
        <?php if($valid_token): ?>
            <p style="font-size: 14px; color: var(--text-secondary); text-align: center; margin-bottom: 24px;">
                Buat kata sandi baru untuk akun pengurus <strong>@<?php echo htmlspecialchars($username); ?></strong>.
            </p>
            
            <form method="POST" action="">
                <input type="password" name="password" placeholder="Kata Sandi Baru" required style="margin-bottom: 12px;">
                <input type="password" name="konfirmasi_password" placeholder="Ulangi Kata Sandi Baru" required style="margin-bottom: 20px;">
                <button type="submit" name="reset">Ubah Kata Sandi</button>
            </form>
        <?php else: ?>
            <div style="text-align: center; padding: 20px 0;">
                <p class="error-msg" style="margin-bottom: 20px; font-weight: 500;">
                    Tautan reset kata sandi tidak valid atau telah kedaluwarsa (masa aktif 1 jam).
                </p>
                <div class="register-link">
                    <a href="lupa_password.php" style="color: var(--primary-color); font-weight: 600; text-decoration: none;">Minta tautan baru lagi</a>
                </div>
            </div>
        <?php endif; ?>
    </div>
    <?php include 'footer.php'; ?>
</body>
</html>
