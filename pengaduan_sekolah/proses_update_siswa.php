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

$aksi = $_POST['aksi'] ?? '';
$nis  = (int)($_POST['nis'] ?? 0);

if ($nis <= 0) {
  $_SESSION['flash_err'] = "NIS tidak valid.";
  header("Location: data_siswa.php");
  exit;
}

if ($aksi === 'update_kelas') {
  $kelas = trim($_POST['kelas'] ?? '');
  if ($kelas === '') {
    $_SESSION['flash_err'] = "Kelas tidak boleh kosong.";
    header("Location: data_siswa.php");
    exit;
  }

  $stmt = mysqli_prepare($conn, "UPDATE siswa SET kelas=? WHERE nis=? LIMIT 1");
  mysqli_stmt_bind_param($stmt, "si", $kelas, $nis);
  mysqli_stmt_execute($stmt);

  $_SESSION['flash_ok'] = "Kelas siswa NIS $nis berhasil diupdate.";
  header("Location: data_siswa.php");
  exit;
}

if ($aksi === 'reset_password') {
  $new = $_POST['new_password'] ?? '';
  if (strlen($new) < 6) {
    $_SESSION['flash_err'] = "Password baru minimal 6 karakter.";
    header("Location: data_siswa.php");
    exit;
  }

  $hash = password_hash($new, PASSWORD_DEFAULT);

  $stmt = mysqli_prepare($conn, "UPDATE siswa SET password=? WHERE nis=? LIMIT 1");
  mysqli_stmt_bind_param($stmt, "si", $hash, $nis);
  mysqli_stmt_execute($stmt);

  $_SESSION['flash_ok'] = "Password siswa NIS $nis berhasil direset.";
  header("Location: data_siswa.php");
  exit;
}

$_SESSION['flash_err'] = "Aksi tidak dikenal.";
header("Location: data_siswa.php");
exit;
