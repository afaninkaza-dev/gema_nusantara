<?php
$host = "localhost";
$user = "root";
$password = "";
$dbname = "gema_nusantara";
$conn = new mysqli($host, $user, $password, $dbname);
if ($conn->connect_error) {
    die("koneksi gagal: " . $conn->connect_error);
}
?>