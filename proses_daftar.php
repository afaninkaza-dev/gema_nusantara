<?php
include "koneksi.php";

if (isset($_POST['submit'])) {
    $nama = trim($_POST['nama']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $konfirm = $_POST['konfirm'];
    $role = "user";

    if (empty($nama) || empty($email) || empty($password) || empty($konfirm)) {
        echo "<script>alert('Semua field harus diisi!'); history.back();</script>";
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "<script>alert('Format email tidak valid!'); history.back();</script>";
        exit;
    }

    if ($password !== $konfirm) {
        echo "<script>alert('Kata sandi tidak cocok!'); history.back();</script>";
        exit;
    }

    $cek = $conn->prepare("SELECT * FROM user WHERE email = ?");
    $cek->bind_param("s", $email);
    $cek->execute();
    $result = $cek->get_result();

    if ($result->num_rows > 0) {
        echo "<script>alert('Email sudah digunakan, silakan gunakan email lain!'); history.back();</script>";
        exit;
    }

    $hash = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO user (nama, email, password, role) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $nama, $email, $hash, $role);

    if ($stmt->execute()) {
        echo "<script>alert('Akun berhasil dibuat! Silakan masuk.'); window.location.href='masuk.php';</script>";
    } else {
        echo "<script>alert('Pendaftaran gagal, coba lagi!'); history.back();</script>";
    }

    $stmt->close();
    $cek->close();
    $conn->close();
}
?>