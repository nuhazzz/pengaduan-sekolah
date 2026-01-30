<?php
session_start();
if (empty($_SESSION['siswa_nis'])) {
  header("Location: index.php");
  exit;
}
require_once __DIR__ . "/koneksi.php";

$nis = (int)$_SESSION['siswa_nis'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header("Location: siswa_buat_laporan.php");
  exit;
}

$id_kategori = (int)($_POST['id_kategori'] ?? 0);
$lokasi = trim($_POST['lokasi'] ?? '');
$ket = trim($_POST['ket'] ?? '');

if ($id_kategori <= 0 || $lokasi === '' || $ket === '') {
  $_SESSION['flash_err'] = "Semua field wajib diisi.";
  header("Location: siswa_buat_laporan.php");
  exit;
}

$stmt = mysqli_prepare($conn, "INSERT INTO input_aspirasi (nis, id_kategori, lokasi, ket) VALUES (?, ?, ?, ?)");
mysqli_stmt_bind_param($stmt, "iiss", $nis, $id_kategori, $lokasi, $ket);
mysqli_stmt_execute($stmt);

$_SESSION['flash_ok'] = "Laporan berhasil dikirim. Tunggu admin memproses.";
header("Location: siswa_dashboard.php");
exit;
