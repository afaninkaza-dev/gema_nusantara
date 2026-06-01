<?php
session_start();
include "koneksi.php";

if (isset($_POST['submit'])) {

    // Cek session dulu
    if (!isset($_SESSION['id'])) {
        echo "<script>alert('Session habis, silakan login ulang!'); window.location='masuk.php';</script>";
        exit;
    }

    $admin_id = 'Admin';

    $judul      = mysqli_real_escape_string($conn, $_POST['judul']);
    $asal       = mysqli_real_escape_string($conn, $_POST['asal_daerah']);
    $sinopsis   = mysqli_real_escape_string($conn, $_POST['sinopsis']);
    $isi_cerita = mysqli_real_escape_string($conn, $_POST['isi_cerita']);
    $nama_bab   = mysqli_real_escape_string($conn, $_POST['nama_bab']);

    $nama_file  = $_FILES['sampul']['name'];
    $tmp_file   = $_FILES['sampul']['tmp_name'];
    $ekstensi_boleh = array('png', 'jpg', 'jpeg', 'svg');
    $x = explode('.', $nama_file);
    $ekstensi = strtolower(end($x));

    $nama_sampul_baru = time() . '_' . $nama_file;

    if (in_array($ekstensi, $ekstensi_boleh) === true) {
        move_uploaded_file($tmp_file, 'buku/' . $nama_sampul_baru);

        $query1 = "INSERT INTO cerita_rakyat (judul, asal_daerah, sinopsis, sampul, dibuat_oleh) 
                   VALUES ('$judul', '$asal', '$sinopsis', '$nama_sampul_baru', '$admin_id')";
        
        if (mysqli_query($conn, $query1)) {
            $id_terakhir = mysqli_insert_id($conn);

            $query2 = "INSERT INTO bab (cerita_id, nomor_bab, judul_bab, isi) 
                       VALUES ('$id_terakhir', 1, '$nama_bab', '$isi_cerita')";
            
            if (mysqli_query($conn, $query2)) {
                echo "<script>alert('Berhasil menambah cerita!'); window.location='dashboardbuku.php';</script>";
            }
        }
    } else {
        echo "<script>alert('Format gambar harus PNG/JPG/SVG!'); window.history.back();</script>";
    }
}
?>