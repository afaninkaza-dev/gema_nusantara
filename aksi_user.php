<?php
session_start();
include 'koneksi.php';
header('Content-Type: application/json');

if (!isset($_SESSION['id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Belum login']);
    exit;
}

$user_id   = (int) $_SESSION['id'];
$cerita_id = isset($_POST['cerita_id']) ? (int) $_POST['cerita_id'] : 0;
$aksi      = $_POST['aksi'] ?? '';

if ($cerita_id <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'ID tidak valid']);
    exit;
}

if ($aksi === 'toggle_simpan') {
    $cek = $conn->prepare("SELECT id FROM koleksi WHERE user_id = ? AND cerita_id = ?");
    $cek->bind_param("ii", $user_id, $cerita_id);
    $cek->execute();
    $ada = $cek->get_result()->num_rows > 0;

    if ($ada) {
        $del = $conn->prepare("DELETE FROM koleksi WHERE user_id = ? AND cerita_id = ?");
        $del->bind_param("ii", $user_id, $cerita_id);
        $del->execute();
        echo json_encode(['status' => 'ok', 'saved' => false]);
    } else {
        $ins = $conn->prepare("INSERT INTO koleksi (user_id, cerita_id, waktu) VALUES (?, ?, NOW())");
        $ins->bind_param("ii", $user_id, $cerita_id);
        $ins->execute();
        echo json_encode(['status' => 'ok', 'saved' => true]);
    }

} elseif ($aksi === 'toggle_like') {
    $cek = $conn->prepare("SELECT id FROM suka WHERE user_id = ? AND cerita_id = ?");
    $cek->bind_param("ii", $user_id, $cerita_id);
    $cek->execute();
    $ada = $cek->get_result()->num_rows > 0;

    if ($ada) {
        $del = $conn->prepare("DELETE FROM suka WHERE user_id = ? AND cerita_id = ?");
        $del->bind_param("ii", $user_id, $cerita_id);
        $del->execute();
        echo json_encode(['status' => 'ok', 'liked' => false]);
    } else {
        $ins = $conn->prepare("INSERT INTO suka (user_id, cerita_id, waktu) VALUES (?, ?, NOW())");
        $ins->bind_param("ii", $user_id, $cerita_id);
        $ins->execute();
        echo json_encode(['status' => 'ok', 'liked' => true]);
    }

} else {
    echo json_encode(['status' => 'error', 'message' => 'Aksi tidak dikenal']);
}

$conn->close();