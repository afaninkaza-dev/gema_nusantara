<?php
session_start();
include "koneksi.php";

if (!isset($_POST['submit'])) {
    header("Location: dashboardbuku.php");
    exit;
}

// Cek session & role admin
if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'admin') {
    echo "<script>alert('Session habis atau bukan admin, silakan login ulang!'); window.location='masuk.php';</script>";
    exit;
}

$admin_id = $_SESSION['id'];
$judul = mysqli_real_escape_string($conn, trim($_POST['judul']));
$asal = mysqli_real_escape_string($conn, trim($_POST['asal_daerah']));
$sinopsis = mysqli_real_escape_string($conn, trim($_POST['sinopsis']));
$isi_cerita = mysqli_real_escape_string($conn, $_POST['isi_cerita']);
$nama_bab = mysqli_real_escape_string($conn, trim($_POST['nama_bab']));

// Validasi field wajib
if (empty($judul) || empty($asal) || empty($isi_cerita) || empty($nama_bab)) {
    echo "<script>alert('Judul, asal daerah, nama bab, dan isi cerita wajib diisi!'); window.history.back();</script>";
    exit;
}

// --- Handle sampul (opsional) ---
$nama_sampul_baru = null;

if (!empty($_FILES['sampul']['name']) && $_FILES['sampul']['error'] === 0) {
    $nama_file = $_FILES['sampul']['name'];
    $tmp_file = $_FILES['sampul']['tmp_name'];
    $ekstensi_boleh = ['png', 'jpg', 'jpeg', 'svg'];
    $ekstensi = strtolower(pathinfo($nama_file, PATHINFO_EXTENSION));

    if (!in_array($ekstensi, $ekstensi_boleh)) {
        echo "<script>alert('Format gambar harus PNG, JPG, JPEG, atau SVG!'); window.history.back();</script>";
        exit;
    }

    $nama_sampul_baru = time() . '_' . basename($nama_file);

    if (!file_exists('buku/')) {
        mkdir('buku/', 0777, true);
    }

    if (!move_uploaded_file($tmp_file, 'buku/' . $nama_sampul_baru)) {
        echo "<script>alert('Gagal menyimpan gambar ke folder!'); window.history.back();</script>";
        exit;
    }
} elseif (isset($_FILES['sampul']) && $_FILES['sampul']['error'] !== UPLOAD_ERR_NO_FILE) {
    // Ada file tapi error bukan karena tidak diisi
    echo "<script>alert('Terjadi kesalahan saat upload file. Coba lagi.'); window.history.back();</script>";
    exit;
}

// --- Insert cerita_rakyat ---
$sampul_value = $nama_sampul_baru ? "'$nama_sampul_baru'" : "NULL";
$query1 = "INSERT INTO cerita_rakyat (judul, asal_daerah, sinopsis, sampul, dibuat_oleh) 
           VALUES ('$judul', '$asal', '$sinopsis', $sampul_value, '$admin_id')";

if (!mysqli_query($conn, $query1)) {
    echo "<script>alert('Gagal menyimpan cerita: " . mysqli_error($conn) . "'); window.history.back();</script>";
    exit;
}

$id_terakhir = mysqli_insert_id($conn);

// --- Insert bab pertama ---
$query2 = "INSERT INTO bab (cerita_id, nomor_bab, judul_bab, isi) 
           VALUES ('$id_terakhir', 1, '$nama_bab', '$isi_cerita')";

if (!mysqli_query($conn, $query2)) {
    // Rollback: hapus cerita yang baru dibuat supaya tidak orphan
    mysqli_query($conn, "DELETE FROM cerita_rakyat WHERE id = $id_terakhir");
    echo "<script>alert('Gagal menyimpan bab: " . mysqli_error($conn) . "'); window.history.back();</script>";
    exit;
}

echo "<script>alert('Cerita berhasil ditambahkan!'); window.location='dashboardbuku.php';</script>";
?>