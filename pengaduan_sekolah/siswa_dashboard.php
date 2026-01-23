<?php
session_start();
if (empty($_SESSION['siswa_nis'])) {
  header("Location: index.php");
  exit;
}
$nis = $_SESSION['siswa_nis'];
$kelas = $_SESSION['siswa_kelas'] ?? '';
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Dashboard Siswa</title>
  <style>
    body{font-family:system-ui;background:#fff;margin:0;padding:24px;color:#0f172a}
    .card{max-width:780px;margin:0 auto;border:1px solid #e2e8f0;border-radius:16px;padding:18px;box-shadow:0 18px 40px rgba(15,23,42,.06)}
    .muted{color:#64748b}
    a{color:#2563eb;font-weight:800;text-decoration:none}
    a:hover{text-decoration:underline}
  </style>
</head>
<body>
  <div class="card">
    <h2>Dashboard Siswa</h2>
    <p class="muted">NIS: <b><?= htmlspecialchars((string)$nis) ?></b> • Kelas: <b><?= htmlspecialchars($kelas) ?></b></p>
    <p>Halaman ini nanti bisa diisi: buat laporan, lihat status, lihat feedback, histori.</p>
    <p><a href="logout.php">Logout</a></p>
  </div>
</body>
</html>
