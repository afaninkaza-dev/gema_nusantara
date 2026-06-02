<?php
include "koneksi.php";
session_start();
$user_id = $_SESSION['user_id']; // sesuaikan dengan session kamu

if ($_SERVER['METHOD'] == 'POST') {
    $foto_name = null;

    if (!empty($_FILES['foto']['name'])) {
        $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
        $foto_name = 'foto_' . $user_id . '.' . $ext;
        move_uploaded_file($_FILES['foto']['tmp_name'], 'buku/' . $foto_name);
    }

    if ($foto_name) {
        $stmt = $conn->prepare("UPDATE user SET foto = ? WHERE id = ?");
        $stmt->bind_param("si", $foto_name, $user_id);
        $stmt->execute();
    }
}
?>

<form method="POST" enctype="multipart/form-data">
    <input type="file" name="foto" accept="image/*">
    <button type="submit">Simpan</button>
</form>