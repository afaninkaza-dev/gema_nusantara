<?php
session_start();
include "koneksi.php";

if (isset($_POST['login'])) {
    $email    = trim($_POST['email']);
    $password = $_POST['password'];

    // Validasi field kosong
    if (empty($email) || empty($password)) {
        echo "<script>alert('Email dan kata sandi harus diisi!'); history.back();</script>";
        exit;
    }

    // Cari user berdasarkan email
    $stmt = $conn->prepare("SELECT * FROM user WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $data   = $result->fetch_assoc();

    if ($data) {
        // Verifikasi password
        if (password_verify($password, $data['password'])) {
            // Simpan session sesuai kolom di database
            $_SESSION['id']    = $data['id'];
            $_SESSION['nama']  = $data['nama'];
            $_SESSION['email'] = $data['email'];
            $_SESSION['role']  = $data['role'];
            $_SESSION['admin_id'] = $data['id'];

            // Redirect berdasarkan role
            if ($data['role'] == "admin") {
                header("Location: dashboardwebadmin.php");
            } else {
                header("Location: landingpage.php");
            }
            exit();
        } else {
            echo "<script>alert('Kata sandi salah!'); history.back();</script>";
        }
    } else {
        echo "<script>alert('Email tidak ditemukan!'); history.back();</script>";
    }

    $stmt->close();
    $conn->close();
}
?>