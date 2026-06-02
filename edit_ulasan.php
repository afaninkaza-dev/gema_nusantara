<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['id'])) {
    header("Location: masuk.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: dashboardulasan_user.php");
    exit;
}

$user_id   = (int) $_SESSION['id'];
$ulasan_id = isset($_POST['ulasan_id']) ? (int) $_POST['ulasan_id'] : 0;
$isi       = isset($_POST['isi_ulasan']) ? trim($_POST['isi_ulasan']) : '';

if ($ulasan_id <= 0 || $isi === '') {
    header("Location: dashboardulasan_user.php?error=invalid");
    exit;
}

// Pastikan ulasan milik user yang sedang login (keamanan)
$upd = $conn->prepare("UPDATE ulasan SET isi_ulasan = ?, waktu = NOW() WHERE id = ? AND user_id = ?");
$upd->bind_param("sii", $isi, $ulasan_id, $user_id);
$upd->execute();

$conn->close();
header("Location: dashboardulasan_user.php?sukses=edit");
exit;