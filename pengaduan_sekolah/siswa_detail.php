<?php
session_start();
if (empty($_SESSION['siswa_nis'])) {
  header("Location: index.php");
  exit;
}
require_once __DIR__ . "/koneksi.php";

$nis = (int)$_SESSION['siswa_nis'];
$id  = (int)($_GET['id'] ?? 0);

if ($id <= 0) {
  header("Location: siswa_dashboard.php");
  exit;
}

$stmt = mysqli_prepare($conn, "
  SELECT
    ia.id_pelaporan, ia.lokasi, ia.ket, ia.created_at,
    k.ket_kategori,
    COALESCE(a.status,'Menunggu') AS status,
    COALESCE(a.feedback_text,'') AS feedback_text,
    COALESCE(a.updated_at,'') AS updated_at
  FROM input_aspirasi ia
  JOIN kategori k ON k.id_kategori = ia.id_kategori
  LEFT JOIN aspirasi a ON a.id_pelaporan = ia.id_pelaporan
  WHERE ia.id_pelaporan = ? AND ia.nis = ?
  LIMIT 1
");
mysqli_stmt_bind_param($stmt, "ii", $id, $nis);
mysqli_stmt_execute($stmt);
$row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$row) {
  header("Location: siswa_dashboard.php");
  exit;
}
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Detail Laporan</title>
  <style>
    :root{--bg:#fff;--text:#0f172a;--muted:#64748b;--border:#e2e8f0;--shadow:0 18px 40px rgba(15,23,42,.06);--primary:#2563eb;--radius:18px}
    *{box-sizing:border-box}
    body{margin:0;font-family:system-ui,-apple-system,Segoe UI,Roboto,Arial;background:var(--bg);color:var(--text);padding:18px}
    .wrap{max-width:860px;margin:0 auto}
    .top{display:flex;justify-content:space-between;gap:12px;align-items:flex-start;margin-bottom:12px}
    a{color:var(--primary);font-weight:900;text-decoration:none}
    a:hover{text-decoration:underline}
    .card{border:1px solid var(--border);border-radius:var(--radius);background:#fff;box-shadow:var(--shadow)}
    .pad{padding:16px}
    .muted{color:var(--muted)}
    .badge{font-size:12px;font-weight:900;padding:6px 10px;border-radius:999px;border:1px solid var(--border);display:inline-block;background:#fff}
    .badge.wait{border-color:rgba(245,158,11,.25);background:rgba(245,158,11,.08);color:#92400e}
    .badge.proc{border-color:rgba(37,99,235,.25);background:rgba(37,99,235,.08);color:#1d4ed8}
    .badge.done{border-color:rgba(22,163,74,.25);background:rgba(22,163,74,.06);color:#166534}
  </style>
</head>
<body>
<div class="wrap">
  <div class="top">
    <div>
      <h2 style="margin:0">Detail Laporan #<?= (int)$row["id_pelaporan"] ?></h2>
      <div class="muted" style="margin-top:6px;font-size:13px">Dibuat: <?= htmlspecialchars($row["created_at"]) ?></div>
    </div>
    <a href="siswa_dashboard.php">← Kembali</a>
  </div>

  <div class="card pad" style="margin-bottom:12px">
    <div class="muted" style="font-size:12px">Kategori</div>
    <div style="font-weight:900"><?= htmlspecialchars($row["ket_kategori"]) ?></div>

    <div class="muted" style="font-size:12px;margin-top:10px">Lokasi</div>
    <div style="font-weight:900"><?= htmlspecialchars($row["lokasi"]) ?></div>

    <div class="muted" style="font-size:12px;margin-top:10px">Keterangan</div>
    <div><?= nl2br(htmlspecialchars($row["ket"])) ?></div>

    <?php
      $status = $row["status"];
      $cls = $status==="Proses" ? "proc" : ($status==="Selesai" ? "done" : "wait");
    ?>
    <div class="muted" style="font-size:12px;margin-top:10px">Status</div>
    <div><span class="badge <?= $cls ?>"><?= htmlspecialchars($status) ?></span></div>
  </div>

  <div class="card pad">
    <div style="display:flex;justify-content:space-between;gap:10px;align-items:center">
      <b>Feedback Admin</b>
      <span class="muted" style="font-size:12px">Update: <?= htmlspecialchars($row["updated_at"] ?: "-") ?></span>
    </div>
    <div style="margin-top:10px">
      <?php if (trim($row["feedback_text"]) === ""): ?>
        <span class="muted">Belum ada feedback dari admin.</span>
      <?php else: ?>
        <?= nl2br(htmlspecialchars($row["feedback_text"])) ?>
      <?php endif; ?>
    </div>
  </div>
</div>
</body>
</html>
