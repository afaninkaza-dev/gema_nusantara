<?php
session_start();
include 'koneksi.php';

header('Content-Type: application/json');

if (!isset($_SESSION['id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Belum login']);
    exit;
}

$user_id = (int) $_SESSION['id'];
$cerita_id = isset($_POST['cerita_id']) ? (int) $_POST['cerita_id'] : 0;

if ($cerita_id <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'ID tidak valid']);
    exit;
}

// 1. Cek dulu apakah user sudah simpan cerita ini
$cek = $conn->prepare("SELECT id FROM simpan WHERE user_id = ? AND cerita_id = ?");
$cek->bind_param("ii", $user_id, $cerita_id);
$cek->execute();
$ada = $cek->get_result()->num_rows > 0;

if ($ada) {
    // Hapus
    $del = $conn->prepare("DELETE FROM simpan WHERE user_id = ? AND cerita_id = ?");
    $del->bind_param("ii", $user_id, $cerita_id);
    $del->execute();
    echo json_encode(['status' => 'dihapus']);
} else {
    // Tambah
    $ins = $conn->prepare("INSERT INTO simpan (user_id, cerita_id, waktu) VALUES (?, ?, NOW())");
    $ins->bind_param("ii", $user_id, $cerita_id);
    $ins->execute();
    echo json_encode(['status' => 'ditambah']);
}

$conn->close();
exit;