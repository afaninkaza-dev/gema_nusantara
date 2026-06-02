<?php
session_start();
include 'koneksi.php';

header('Content-Type: application/json');

if (!isset($_SESSION['id'])) {
    echo json_encode(['success' => false, 'message' => 'Belum login']);
    exit;
}

$user_id   = (int) $_SESSION['id'];
$cerita_id = isset($_POST['cerita_id']) ? (int) $_POST['cerita_id'] : 0;
$rating    = isset($_POST['rating'])    ? (int) $_POST['rating']    : 0;
$isi       = isset($_POST['isi_ulasan']) ? trim($_POST['isi_ulasan']) : '';

if ($cerita_id <= 0 || $rating < 1 || $rating > 5 || $isi === '') {
    echo json_encode(['success' => false, 'message' => 'Data tidak lengkap.']);
    exit;
}

// Cek sudah pernah ulasan?
$cek = $conn->prepare("SELECT id FROM ulasan WHERE user_id = ? AND cerita_id = ?");
$cek->bind_param("ii", $user_id, $cerita_id);
$cek->execute();
$cek->store_result();

if ($cek->num_rows > 0) {
    $upd = $conn->prepare("UPDATE ulasan SET isi_ulasan = ?, rating = ?, waktu = NOW() WHERE user_id = ? AND cerita_id = ?");
    $upd->bind_param("siii", $isi, $rating, $user_id, $cerita_id);
    $upd->execute();
} else {
    $ins = $conn->prepare("INSERT INTO ulasan (user_id, cerita_id, isi_ulasan, rating, waktu) VALUES (?, ?, ?, ?, NOW())");
    $ins->bind_param("iisi", $user_id, $cerita_id, $isi, $rating);
    $ins->execute();
}

$conn->close();
echo json_encode(['success' => true]);
exit;