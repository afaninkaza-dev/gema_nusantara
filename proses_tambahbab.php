<?php
session_start();
include "koneksi.php";

// Cek session & role admin
if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'admin') {
    header("Location: masuk.php");
    exit;
}

if (!isset($_POST['submit'])) {
    header("Location: dashboardbuku.php");
    exit;
}

$cerita_id = (int) $_POST['cerita_id'];
$nomor_bab = (int) $_POST['nomor_bab'];
$judul_bab = trim($_POST['judul_bab']);
$isi = trim($_POST['isi']);

// Validasi semua field wajib
if ($cerita_id <= 0 || empty($judul_bab) || empty($isi)) {
    header("Location: dashboardtambahbab.php?cerita_id=$cerita_id&status=gagal");
    exit;
}

// Pastikan cerita_id memang ada di database
$cek = $conn->prepare("SELECT id FROM cerita_rakyat WHERE id = ?");
$cek->bind_param("i", $cerita_id);
$cek->execute();
$cek->store_result();

if ($cek->num_rows === 0) {
    header("Location: dashboardbuku.php");
    exit;
}
$cek->close();

// Insert bab baru
$sql = "INSERT INTO bab (cerita_id, nomor_bab, judul_bab, isi) VALUES (?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iiss", $cerita_id, $nomor_bab, $judul_bab, $isi);

if ($stmt->execute()) {
    header("Location: dashboardeditcerita.php?id=$cerita_id&status=sukses");
} else {
    header("Location: dashboardtambahbab.php?cerita_id=$cerita_id&status=gagal");
}
exit;
?>