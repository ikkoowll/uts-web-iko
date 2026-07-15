<?php
session_start();
require 'config.php';

// Cek status logout dari query parameter
if (isset($_GET['logout']) && $_GET['logout'] == 'success') {
    $_SESSION['swal_success'] = 'Anda telah berhasil logout!';
}

// Logika Login
if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = md5($_POST['password']); 

    $query = mysqli_query($conn, "SELECT * FROM users WHERE username='$username' AND password='$password'");
    
    if (mysqli_num_rows($query) > 0) {
        $_SESSION['username'] = $username;
        header("Location: dashboard.php");
        exit;
    } else {
        $error = "Username atau Password salah!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SIM HIMATIF</title>
    <link rel="stylesheet" href="style.css?v=1.3">
</head>
<body class="login-page">
    <div class="login-card">
        <h2>Login Pengurus HIMATIF</h2>
        
        <?php if(isset($error)) echo "<p class='error-msg'>$error</p>"; ?>
        
        <form method="POST" action="">
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <div style="text-align: right; margin-top: -6px; margin-bottom: 16px;">
                <a href="lupa_password.php" style="color: var(--primary-color); font-size: 13px; text-decoration: none; font-weight: 600; transition: color 0.3s ease;">Lupa Password?</a>
            </div>
            <button type="submit" name="login">Masuk ke Dashboard</button>
        </form>

        <div class="register-link">
            Belum punya akun? <a href="register.php">Daftar di sini</a>
        </div>
    </div>
    <?php include 'footer.php'; ?>
    <?php include 'alerts.php'; ?>
</body>
</html>