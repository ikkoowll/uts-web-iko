<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "pengelolaan_data_internal_ukm";

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}
?>