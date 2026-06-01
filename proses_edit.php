<?php
include "koneksi.php";

if (!isset($_POST['submit'])) {
    header("Location: dashboardbuku.php");
    exit;
}

$id          = (int) $_POST['id'];
$judul       = trim($_POST['judul']);
$asal_daerah = trim($_POST['asal_daerah']);
$sinopsis    = trim($_POST['sinopsis']);
$bab_id      = (int) $_POST['bab_id'];       // ← was 'nomor_bab', harus 'bab_id'
$isi_cerita  = $_POST['isi_cerita'];          // ← was 'isi', harus 'isi_cerita'

// Ambil nama sampul lama
$stmt_old = $conn->prepare("SELECT sampul FROM cerita_rakyat WHERE id = ?");
$stmt_old->bind_param("i", $id);
$stmt_old->execute();
$old    = $stmt_old->get_result()->fetch_assoc();
$sampul = $old['sampul'];

// Upload sampul baru jika ada
if (!empty($_FILES['sampul']['name'])) {
    $allowed = ['png', 'jpg', 'jpeg', 'svg'];
    $ext     = strtolower(pathinfo($_FILES['sampul']['name'], PATHINFO_EXTENSION));

    if (in_array($ext, $allowed)) {
        $filename = 'sampul_' . $id . '_' . time() . '.' . $ext;
        $target   = 'buku/' . $filename;

        if (move_uploaded_file($_FILES['sampul']['tmp_name'], $target)) {
            if (!empty($sampul) && file_exists('buku/' . $sampul)) {
                unlink('buku/' . $sampul);
            }
            $sampul = $filename;
        }
    }
}

// Update tabel cerita_rakyat
$stmt = $conn->prepare("UPDATE cerita_rakyat SET judul=?, asal_daerah=?, sinopsis=?, sampul=? WHERE id=?");
$stmt->bind_param("ssssi", $judul, $asal_daerah, $sinopsis, $sampul, $id);
$stmt->execute();

// Update isi bab
if ($bab_id > 0) {
    $stmt_bab = $conn->prepare("UPDATE bab SET isi = ? WHERE id = ? AND cerita_id = ?");
    $stmt_bab->bind_param("sii", $isi_cerita, $bab_id, $id);
    $stmt_bab->execute();
}

header("Location: dashboardbuku.php");
exit;
?>