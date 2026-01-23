<?php
session_start();
if (empty($_SESSION['admin_username'])) {
  header("Location: index.php");
  exit;
}
require_once __DIR__ . "/koneksi.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header("Location: data_aspirasi.php");
  exit;
}

$id_pelaporan = (int)($_POST['id_pelaporan'] ?? 0);
$status = $_POST['status'] ?? 'Menunggu';
$feedback_text = trim($_POST['feedback_text'] ?? '');

$allowed = ["Menunggu","Proses","Selesai"];
if (!in_array($status, $allowed, true)) $status = "Menunggu";

if ($id_pelaporan <= 0) {
  $_SESSION['flash'] = "ID pelaporan tidak valid.";
  header("Location: data_aspirasi.php");
  exit;
}

// cek apakah record aspirasi sudah ada
$stmt = mysqli_prepare($conn, "SELECT id_aspirasi FROM aspirasi WHERE id_pelaporan = ? LIMIT 1");
mysqli_stmt_bind_param($stmt, "i", $id_pelaporan);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($res) === 1) {
  // update
  $stmt2 = mysqli_prepare($conn, "UPDATE aspirasi SET status=?, feedback_text=? WHERE id_pelaporan=?");
  mysqli_stmt_bind_param($stmt2, "ssi", $status, $feedback_text, $id_pelaporan);
  mysqli_stmt_execute($stmt2);
} else {
  // insert
  // id_kategori wajib sesuai struktur kamu
  $stmtK = mysqli_prepare($conn, "SELECT id_kategori FROM input_aspirasi WHERE id_pelaporan = ? LIMIT 1");
  mysqli_stmt_bind_param($stmtK, "i", $id_pelaporan);
  mysqli_stmt_execute($stmtK);
  $resK = mysqli_stmt_get_result($stmtK);
  $rowK = mysqli_fetch_assoc($resK);

  if (!$rowK) {
    $_SESSION['flash'] = "Data laporan tidak ditemukan.";
    header("Location: data_aspirasi.php");
    exit;
  }

  $id_kategori = (int)$rowK['id_kategori'];

  $stmt3 = mysqli_prepare($conn, "INSERT INTO aspirasi (id_pelaporan, status, id_kategori, feedback_text) VALUES (?, ?, ?, ?)");
  mysqli_stmt_bind_param($stmt3, "isis", $id_pelaporan, $status, $id_kategori, $feedback_text);
  mysqli_stmt_execute($stmt3);
}

$_SESSION['flash'] = "Berhasil menyimpan status & feedback untuk laporan #$id_pelaporan.";
header("Location: data_aspirasi.php");
exit;
