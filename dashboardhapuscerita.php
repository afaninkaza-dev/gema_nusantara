<?php
session_start();
include "koneksi.php";

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id <= 0) {
    header("Location: dashboardbuku.php");
    exit;
}

// Pastikan cerita ada
$cek = $conn->prepare("SELECT id, judul, sampul FROM cerita_rakyat WHERE id = ?");
$cek->bind_param("i", $id);
$cek->execute();
$cerita = $cek->get_result()->fetch_assoc();

if (!$cerita) {
    header("Location: dashboardbuku.php?msg=notfound");
    exit;
}

// Hapus semua data terkait dulu (urutan penting!)
// 1. Riwayat membaca
$conn->prepare("DELETE FROM riwayat_membaca WHERE cerita_id = ?")->execute() 
    ?: true;
$stmt = $conn->prepare("DELETE FROM riwayat_membaca WHERE cerita_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();

// 2. Suka
$stmt = $conn->prepare("DELETE FROM suka WHERE cerita_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();

// 3. Simpan
$stmt = $conn->prepare("DELETE FROM simpan WHERE cerita_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();

// 4. Ulasan
$stmt = $conn->prepare("DELETE FROM ulasan WHERE cerita_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();

// 5. Bab (hapus semua bab cerita ini)
$stmt = $conn->prepare("DELETE FROM bab WHERE cerita_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();

// 6. Hapus file sampul dari server (kalau ada)
$sampul_path = "buku/" . $cerita['sampul'];
if (!empty($cerita['sampul']) && file_exists($sampul_path)) {
    unlink($sampul_path);
}

// 7. Hapus cerita utama
$stmt = $conn->prepare("DELETE FROM cerita_rakyat WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    header("Location: dashboardbuku.php?msg=deleted&judul=" . urlencode($cerita['judul']));
} else {
    header("Location: dashboardbuku.php?msg=error");
}

$conn->close();
exit;
?>