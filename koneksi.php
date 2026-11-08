<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "spk_kos"; // Menyesuaikan dengan nama database Anda di phpMyAdmin

$koneksi = mysqli_connect($host, $user, $pass, $db);

if (!$koneksi) {
    die("Koneksi ke database gagal: " . mysqli_connect_error());
}
?>