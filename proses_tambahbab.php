<?php
include "koneksi.php";

if (!isset($_POST['submit'])) {
    header("Location: dashboardbuku.php");
    exit;
}

$cerita_id  = (int) $_POST['cerita_id'];
$nomor_bab  = (int) $_POST['nomor_bab'];
$judul_bab  = trim($_POST['judul_bab']);
$isi        = $_POST['isi'];

if (empty($judul_bab) || $cerita_id <= 0) {
    header("Location: dashboardtambahbab.php?cerita_id=$cerita_id&status=gagal");
    exit;
}

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