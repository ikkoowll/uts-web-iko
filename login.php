<form method="POST" action="">
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit" name="login">Masuk ke Dasbor</button>
        </form>

        <div class="register-link">
            Belum punya akun? <a href="register.php">Daftar di sini</a>
        </div>
    </div> ```

### 3. Buat File `register.php` (Baru)
Karena kamu menambahkan link ke `register.php`, kamu perlu membuat filenya agar tidak *error* saat diklik. [cite_start]File ini berfungsi untuk menyimpan data pengurus baru ke tabel `users`[cite: 27].

```php
<?php
require 'koneksi.php';

if (isset($_POST['register'])) {
    $nama = $_POST['nama_lengkap'];
    $username = $_POST['username'];
    // Enkripsi password dengan MD5 sesuai standar tugas
    $password = md5($_POST['password']); 

    $query = "INSERT INTO users (nama_lengkap, username, password) VALUES ('$nama', '$username', '$password')";
    
    if (mysqli_query($conn, $query)) {
        echo "<script>alert('Pendaftaran Berhasil! Silakan Login'); window.location='login.php';</script>";
    } else {
        $error = "Pendaftaran Gagal: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Register - SIM UKM</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="login-page">
    <div class="login-card">
        <h2>Daftar Pengurus</h2>
        <?php if(isset($error)) echo "<p class='error-msg'>$error</p>"; ?>
        
        <form method="POST" action="">
            <input type="text" name="nama_lengkap" placeholder="Nama Lengkap" required>
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit" name="register">Daftar Sekarang</button>
        </form>

        <div class="register-link">
            Sudah punya akun? <a href="login.php">Login di sini</a>
        </div>
    </div>
</body>
</html>