<?php
session_start();
require 'config.php';

// Logika Register
if (isset($_POST['register'])) {
    $nama = mysqli_real_escape_string($conn, $_POST['nama_lengkap']);
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = md5($_POST['password']); 

    // Cek apakah username atau email sudah ada di database
    $cek_username = mysqli_query($conn, "SELECT * FROM users WHERE username='$username' OR email='$email'");
    
    if (mysqli_num_rows($cek_username) > 0) {
        $error = "Username atau Alamat Email sudah digunakan, silakan gunakan yang lain!";
    } else {
        // Jika tersedia, simpan ke database
        $query = "INSERT INTO users (nama_lengkap, username, email, password) VALUES ('$nama', '$username', '$email', '$password')";
        if (mysqli_query($conn, $query)) {
            $_SESSION['swal_success'] = 'Pendaftaran Berhasil! Silakan Login';
            header("Location: login.php");
            exit;
        } else {
            $error = "Pendaftaran Gagal: " . mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - SIM HIMATIF</title>
    <link rel="stylesheet" href="style.css?v=1.3">
</head>
<body class="login-page">
    <div class="login-card">
        <h2>Daftar Pengurus Baru</h2>
        
        <?php if(isset($error)) echo "<p class='error-msg'>$error</p>"; ?>
        
        <form method="POST" action="">
            <input type="text" name="nama_lengkap" placeholder="Nama Lengkap sesuai KTM" required>
            <input type="text" name="username" placeholder="Buat Username" required>
            <input type="email" name="email" placeholder="Alamat Email Aktif" required>
            <input type="password" name="password" placeholder="Buat Password" required>
            <button type="submit" name="register">Daftar Sekarang</button>
        </form>

        <div class="register-link">
            Sudah punya akun? <a href="login.php">Login di sini</a>
        </div>
    </div>
    <?php include 'footer.php'; ?>
</body>
</html>