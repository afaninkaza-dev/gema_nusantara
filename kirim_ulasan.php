<?php
session_start();
include 'koneksi.php';

// Hanya user yang login
if (!isset($_SESSION['id'])) {
    header("Location: masuk.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: jelajahi.php");
    exit;
}

$user_id   = (int) $_SESSION['id'];
$cerita_id = isset($_POST['cerita_id']) ? (int) $_POST['cerita_id'] : 0;
$rating    = isset($_POST['rating'])    ? (int) $_POST['rating']    : 0;
$isi       = isset($_POST['isi_ulasan']) ? trim($_POST['isi_ulasan']) : '';

// Validasi
if ($cerita_id <= 0 || $rating < 1 || $rating > 5 || $isi === '') {
    header("Location: detailbuku.php?id=$cerita_id&error=invalid");
    exit;
}

// Cek apakah user sudah pernah beri ulasan di cerita ini
$cek = $conn->prepare("SELECT id FROM ulasan WHERE user_id = ? AND cerita_id = ?");
if (!$cek) die("Prepare cek gagal: " . $conn->error);

$cek->bind_param("ii", $user_id, $cerita_id);
$cek->execute();
$cek->store_result();

if ($cek->num_rows > 0) {
    $upd = $conn->prepare("UPDATE ulasan SET isi_ulasan = ?, rating = ?, waktu = NOW() WHERE user_id = ? AND cerita_id = ?");
    if (!$upd) die("Prepare update gagal: " . $conn->error);
    $upd->bind_param("siii", $isi, $rating, $user_id, $cerita_id);
    if (!$upd->execute()) die("Execute update gagal: " . $upd->error);
} else {
    $ins = $conn->prepare("INSERT INTO ulasan (user_id, cerita_id, isi_ulasan, rating, waktu) VALUES (?, ?, ?, ?, NOW())");
    if (!$ins) die("Prepare insert gagal: " . $conn->error);
    $ins->bind_param("iisi", $user_id, $cerita_id, $isi, $rating);
    if (!$ins->execute()) die("Execute insert gagal: " . $ins->error);
}
$conn->close();
header("Location: detailbuku.php?id=$cerita_id&sukses=1");
exit;