<?php
session_start();
require_once __DIR__ . "/koneksi.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header("Location: index.php");
  exit;
}

$role = $_POST['role'] ?? 'SISWA';
$user = trim($_POST['username'] ?? '');
$pass = $_POST['password'] ?? '';

if ($user === '' || $pass === '') {
  $_SESSION['login_error'] = "Username/NIS dan password wajib diisi.";
  header("Location: index.php");
  exit;
}

if ($role === 'ADMIN') {
  // ===== LOGIN ADMIN =====
  $stmt = mysqli_prepare($conn, "SELECT username, password FROM admin WHERE username = ? LIMIT 1");
  mysqli_stmt_bind_param($stmt, "s", $user);
  mysqli_stmt_execute($stmt);
  $res = mysqli_stmt_get_result($stmt);

  if (mysqli_num_rows($res) !== 1) {
    $_SESSION['login_error'] = "Akun admin tidak ditemukan / password salah.";
    header("Location: index.php");
    exit;
  }

  $row = mysqli_fetch_assoc($res);
  if (!password_verify($pass, $row['password'])) {
    $_SESSION['login_error'] = "Akun admin tidak ditemukan / password salah.";
    header("Location: index.php");
    exit;
  }

  session_regenerate_id(true);
  $_SESSION['admin_username'] = $row['username'];
  unset($_SESSION['siswa_nis']);

  header("Location: dashboard.php");
  exit;
}

// ===== LOGIN SISWA =====
// input "username" berisi NIS
$nis = (int)$user;
if ($nis <= 0) {
  $_SESSION['login_error'] = "NIS tidak valid.";
  header("Location: index.php");
  exit;
}

$stmt = mysqli_prepare($conn, "SELECT nis, kelas, password FROM siswa WHERE nis = ? LIMIT 1");
mysqli_stmt_bind_param($stmt, "i", $nis);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($res) !== 1) {
  $_SESSION['login_error'] = "Akun siswa tidak ditemukan / password salah.";
  header("Location: index.php");
  exit;
}

$row = mysqli_fetch_assoc($res);
if (!password_verify($pass, $row['password'])) {
  $_SESSION['login_error'] = "Akun siswa tidak ditemukan / password salah.";
  header("Location: index.php");
  exit;
}

session_regenerate_id(true);
$_SESSION['siswa_nis'] = (int)$row['nis'];
$_SESSION['siswa_kelas'] = $row['kelas'];
unset($_SESSION['admin_username']);

header("Location: siswa_dashboard.php");
exit;
