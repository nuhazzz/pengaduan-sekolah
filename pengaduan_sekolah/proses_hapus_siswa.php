<?php
session_start();
if (empty($_SESSION['admin_username'])) {
  header("Location: index.php");
  exit;
}
require_once __DIR__ . "/koneksi.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header("Location: data_siswa.php");
  exit;
}

$nis = (int)($_POST['nis'] ?? 0);
if ($nis <= 0) {
  $_SESSION['flash_err'] = "NIS tidak valid.";
  header("Location: data_siswa.php");
  exit;
}

// NOTE: kalau ada tabel lain yang FK ke siswa.nis, ini bisa gagal.
// Kalau gagal, berarti siswa masih punya relasi laporan, dll.
$stmt = mysqli_prepare($conn, "DELETE FROM siswa WHERE nis=? LIMIT 1");
mysqli_stmt_bind_param($stmt, "i", $nis);

try {
  mysqli_stmt_execute($stmt);
  if (mysqli_affected_rows($conn) === 1) {
    $_SESSION['flash_ok'] = "Siswa NIS $nis berhasil dihapus.";
  } else {
    $_SESSION['flash_err'] = "Siswa tidak ditemukan.";
  }
} catch (Throwable $e) {
  $_SESSION['flash_err'] = "Gagal hapus. Mungkin siswa masih punya data laporan (FK).";
}

header("Location: data_siswa.php");
exit;
