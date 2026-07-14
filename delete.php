<?php
session_start();
include "koneksi.php";

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id <= 0) {
    header("Location: dashboardwebadmin.php");
    exit;
}

// Pastikan user ada
$cek = $conn->prepare("SELECT id, nama, foto FROM user WHERE id = ?");
$cek->bind_param("i", $id);
$cek->execute();
$user = $cek->get_result()->fetch_assoc();

if (!$user) {
    header("Location: dashboardwebadmin.php?msg=notfound");
    exit;
}

// Jangan bisa hapus diri sendiri (kalau ada session admin)
if (isset($_SESSION['id']) && $_SESSION['id'] == $id) {
    header("Location: dashboardwebadmin.php?msg=self");
    exit;
}

// Hapus semua data terkait user (urutan penting!)

// 1. Riwayat membaca
$stmt = $conn->prepare("DELETE FROM riwayat_membaca WHERE user_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();

// 2. Suka
$stmt = $conn->prepare("DELETE FROM suka WHERE user_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();

// 3. Simpan
$stmt = $conn->prepare("DELETE FROM simpan WHERE user_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();

// 4. Ulasan
$stmt = $conn->prepare("DELETE FROM ulasan WHERE user_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();

// 5. Saran
$stmt = $conn->prepare("DELETE FROM saran WHERE user_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();

// 6. Hapus foto profil dari server (kalau bukan default)
if (!empty($user['foto']) && file_exists($user['foto'])
    && $user['foto'] !== 'img/profile.jpg'
    && $user['foto'] !== 'img/profile.svg') {
    unlink($user['foto']);
}

// 7. Hapus user
$stmt = $conn->prepare("DELETE FROM user WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    header("Location: dashboardwebadmin.php?msg=deleted&nama=" . urlencode($user['nama']));
} else {
    header("Location: dashboardwebadmin.php?msg=error");
}

$conn->close();
exit;
?>