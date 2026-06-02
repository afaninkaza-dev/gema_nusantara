<?php
session_start();
include 'koneksi.php';
header('Content-Type: application/json');

if (!isset($_SESSION['id'])) {
    echo json_encode(['status' => 'gagal', 'pesan' => 'Belum login']);
    exit;
}

$user_id   = (int) $_SESSION['id'];
$cerita_id = isset($_POST['cerita_id']) ? (int) $_POST['cerita_id'] : 0;

if ($cerita_id <= 0) {
    echo json_encode(['status' => 'gagal', 'pesan' => 'ID tidak valid']);
    exit;
}

// Cek apakah sudah ada
$cek = $conn->prepare("SELECT id FROM suka WHERE user_id = ? AND cerita_id = ?");
$cek->bind_param("ii", $user_id, $cerita_id);
$cek->execute();
$ada = $cek->get_result()->num_rows > 0;

if ($ada) {
    // Hapus (unlike)
    $del = $conn->prepare("DELETE FROM suka WHERE user_id = ? AND cerita_id = ?");
    $del->bind_param("ii", $user_id, $cerita_id);
    $del->execute();
    echo json_encode(['status' => 'dihapus']);
} else {
    // Tambah (like)
    $ins = $conn->prepare("INSERT INTO suka (user_id, cerita_id, waktu) VALUES (?, ?, NOW())");
    $ins->bind_param("ii", $user_id, $cerita_id);
    $ins->execute();
    echo json_encode(['status' => 'ditambah']);
}

$conn->close();