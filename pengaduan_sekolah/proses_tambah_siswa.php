<?php
session_start();
if (empty($_SESSION['admin_username'])) {
  header("Location: index.php");
  exit;
}
require_once __DIR__ . "/koneksi.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header("Location: tambah_siswa.php");
  exit;
}

$nisRaw = trim($_POST['nis'] ?? '');
$kelas  = trim($_POST['kelas'] ?? '');
$pass1  = $_POST['password'] ?? '';
$pass2  = $_POST['password2'] ?? '';

if ($nisRaw === '' || $kelas === '' || $pass1 === '' || $pass2 === '') {
  $_SESSION['flash_err'] = "Semua field wajib diisi.";
  header("Location: tambah_siswa.php");
  exit;
}

if (!ctype_digit($nisRaw)) {
  $_SESSION['flash_err'] = "NIS harus berupa angka.";
  header("Location: tambah_siswa.php");
  exit;
}

$nis = (int)$nisRaw;
if ($nis <= 0) {
  $_SESSION['flash_err'] = "NIS tidak valid.";
  header("Location: tambah_siswa.php");
  exit;
}

if (strlen($pass1) < 6) {
  $_SESSION['flash_err'] = "Password minimal 6 karakter.";
  header("Location: tambah_siswa.php");
  exit;
}

if ($pass1 !== $pass2) {
  $_SESSION['flash_err'] = "Password dan ulangi password tidak sama.";
  header("Location: tambah_siswa.php");
  exit;
}

// cek NIS sudah ada belum
$stmt = mysqli_prepare($conn, "SELECT nis FROM siswa WHERE nis = ? LIMIT 1");
mysqli_stmt_bind_param($stmt, "i", $nis);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($res) === 1) {
  $_SESSION['flash_err'] = "NIS sudah terdaftar.";
  header("Location: tambah_siswa.php");
  exit;
}

$hash = password_hash($pass1, PASSWORD_DEFAULT);

// insert siswa baru
$stmt2 = mysqli_prepare($conn, "INSERT INTO siswa (nis, kelas, password) VALUES (?, ?, ?)");
mysqli_stmt_bind_param($stmt2, "iss", $nis, $kelas, $hash);

if (mysqli_stmt_execute($stmt2)) {
  $_SESSION['flash_ok'] = "Berhasil menambahkan siswa. NIS: $nis";
  header("Location: tambah_siswa.php");
  exit;
}

$_SESSION['flash_err'] = "Gagal menyimpan data siswa.";
header("Location: tambah_siswa.php");
exit;
