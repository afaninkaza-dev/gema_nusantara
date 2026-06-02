<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
echo "PHP OK";
session_start();
echo " | Session OK";
include 'koneksi.php';
echo " | Koneksi OK";
echo " | User ID: " . ($_SESSION['id'] ?? 'tidak ada');