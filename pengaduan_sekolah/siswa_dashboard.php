<?php
session_start();
if (empty($_SESSION['siswa_nis'])) {
  header("Location: index.php");
  exit;
}
require_once __DIR__ . "/koneksi.php";

$nis = (int)$_SESSION['siswa_nis'];
$kelas = $_SESSION['siswa_kelas'] ?? '';

/* Statistik status */
$stmt = mysqli_prepare($conn, "
  SELECT
    COUNT(*) AS total,
    SUM(CASE WHEN COALESCE(a.status,'Menunggu')='Menunggu' THEN 1 ELSE 0 END) AS menunggu,
    SUM(CASE WHEN COALESCE(a.status,'Menunggu')='Proses' THEN 1 ELSE 0 END) AS proses,
    SUM(CASE WHEN COALESCE(a.status,'Menunggu')='Selesai' THEN 1 ELSE 0 END) AS selesai
  FROM input_aspirasi ia
  LEFT JOIN aspirasi a ON a.id_pelaporan = ia.id_pelaporan
  WHERE ia.nis = ?
");
mysqli_stmt_bind_param($stmt, "i", $nis);
mysqli_stmt_execute($stmt);
$st = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt)) ?: ["total"=>0,"menunggu"=>0,"proses"=>0,"selesai"=>0];

/* Histori laporan siswa */
$stmt2 = mysqli_prepare($conn, "
  SELECT
    ia.id_pelaporan,
    ia.lokasi,
    ia.ket,
    ia.created_at,
    k.ket_kategori,
    COALESCE(a.status,'Menunggu') AS status
  FROM input_aspirasi ia
  JOIN kategori k ON k.id_kategori = ia.id_kategori
  LEFT JOIN aspirasi a ON a.id_pelaporan = ia.id_pelaporan
  WHERE ia.nis = ?
  ORDER BY ia.created_at DESC, ia.id_pelaporan DESC
  LIMIT 50
");
mysqli_stmt_bind_param($stmt2, "i", $nis);
mysqli_stmt_execute($stmt2);
$res2 = mysqli_stmt_get_result($stmt2);

$rows = [];
while ($r = mysqli_fetch_assoc($res2)) $rows[] = $r;

function badgeClass($s){
  return match($s){
    "Menunggu" => "wait",
    "Proses"   => "proc",
    "Selesai"  => "done",
    default    => "wait"
  };
}
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Dashboard Siswa</title>
  <style>
    :root{--bg:#fff;--text:#0f172a;--muted:#64748b;--border:#e2e8f0;--shadow:0 18px 40px rgba(15,23,42,.06);--primary:#2563eb;--ring:rgba(37,99,235,.16);--radius:18px}
    *{box-sizing:border-box}
    body{margin:0;font-family:system-ui,-apple-system,Segoe UI,Roboto,Arial;background:var(--bg);color:var(--text);padding:18px}
    .wrap{max-width:1100px;margin:0 auto}
    .top{display:flex;justify-content:space-between;gap:12px;align-items:flex-start;margin-bottom:12px}
    .muted{color:var(--muted)}
    .card{border:1px solid var(--border);border-radius:var(--radius);background:#fff;box-shadow:var(--shadow)}
    .pad{padding:16px}
    h1{margin:0;font-size:20px}
    .sub{margin-top:6px;font-size:13px;color:var(--muted)}
    .btn{border:1px solid var(--border);background:#fff;padding:10px 12px;border-radius:14px;font-weight:900;cursor:pointer;text-decoration:none;display:inline-block}
    .btn.primary{border:0;color:#fff;background:linear-gradient(135deg,var(--primary),rgba(99,102,241,.95));box-shadow:0 16px 28px rgba(37,99,235,.18)}
    .btn:hover{box-shadow:0 0 0 6px rgba(15,23,42,.04)}
    .grid{display:grid;grid-template-columns:repeat(12,1fr);gap:12px}
    .stat{grid-column:span 3}
    .kpi span{font-size:13px;color:var(--muted)}
    .kpi b{display:block;font-size:22px;margin-top:4px}
    .table{grid-column:span 12}
    table{width:100%;border-collapse:collapse;font-size:13px}
    th,td{padding:12px 14px;border-bottom:1px solid var(--border);text-align:left;vertical-align:top}
    th{font-size:12px;color:#334155}
    .badge{font-size:12px;font-weight:900;padding:6px 10px;border-radius:999px;border:1px solid var(--border);display:inline-block;background:#fff}
    .badge.wait{border-color:rgba(245,158,11,.25);background:rgba(245,158,11,.08);color:#92400e}
    .badge.proc{border-color:rgba(37,99,235,.25);background:rgba(37,99,235,.08);color:#1d4ed8}
    .badge.done{border-color:rgba(22,163,74,.25);background:rgba(22,163,74,.06);color:#166534}
    .right{display:flex;gap:10px;flex-wrap:wrap;justify-content:flex-end}
    @media(max-width:900px){.stat{grid-column:span 6}}
    @media(max-width:520px){.stat{grid-column:span 12}}
  </style>
</head>
<body>
<div class="wrap">
  <div class="top">
    <div>
      <h1>Dashboard Siswa</h1>
      <div class="sub">NIS: <b><?= htmlspecialchars((string)$nis) ?></b> • Kelas: <b><?= htmlspecialchars($kelas) ?></b></div>
    </div>
    <div class="right">
      <a class="btn primary" href="siswa_buat_laporan.php">+ Buat Laporan</a>
      <a class="btn" href="logout.php">Logout</a>
    </div>
  </div>

  <div class="grid">
    <div class="card pad stat"><div class="kpi"><span>Total Laporan</span><b><?= (int)$st["total"] ?></b></div></div>
    <div class="card pad stat"><div class="kpi"><span>Menunggu</span><b><?= (int)$st["menunggu"] ?></b></div></div>
    <div class="card pad stat"><div class="kpi"><span>Proses</span><b><?= (int)$st["proses"] ?></b></div></div>
    <div class="card pad stat"><div class="kpi"><span>Selesai</span><b><?= (int)$st["selesai"] ?></b></div></div>

    <div class="card table">
      <div class="pad" style="border-bottom:1px solid var(--border);display:flex;justify-content:space-between;gap:10px;align-items:center">
        <b>Histori Laporan (maks 50)</b>
        <span class="muted" style="font-size:12px">Klik “Detail” untuk lihat feedback admin</span>
      </div>
      <div style="overflow:auto">
        <table>
          <thead>
            <tr>
              <th>No</th>
              <th>Kategori</th>
              <th>Lokasi</th>
              <th>Keterangan</th>
              <th>Status</th>
              <th>Tanggal</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
          <?php if (count($rows) === 0): ?>
            <tr><td colspan="7" class="muted">Belum ada laporan. Klik “Buat Laporan”.</td></tr>
          <?php else: ?>
            <?php $no = 1; ?>
            <?php foreach ($rows as $r): ?>
              <tr>
                <td><?= $no++ ?></td>
                <td><?= htmlspecialchars($r["ket_kategori"]) ?></td>
                <td><?= htmlspecialchars($r["lokasi"]) ?></td>
                <td><?= htmlspecialchars($r["ket"]) ?></td>
                <td><span class="badge <?= badgeClass($r["status"]) ?>"><?= htmlspecialchars($r["status"]) ?></span></td>
                <td><?= htmlspecialchars($r["created_at"]) ?></td>
                <td><a class="btn" href="siswa_detail.php?id=<?= (int)$r["id_pelaporan"] ?>">Detail</a></td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
</body>
</html>
