<?php
session_start();
include "koneksi.php";
if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];


    $stmt = $conn->prepare("SELECT * FROM login WHERE username=?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();
    if ($data) {

        if (password_verify($password, $data['password'])) {
            $_SESSION['username'] = $data['username'];
            $_SESSION['level'] = $data['level'];

            if ($data['level'] == "admin") {
                header("Location: tampil.php");
            } else if ($data['level'] == "user") {
                header("Location: tampil2.php");
            }
            exit();
        } else {
            echo "Password salah!";
        }
    } else {
        echo "Username tidak ditemukan";
    }
}
?>