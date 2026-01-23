<?php
session_start();
if (empty($_SESSION['admin_username'])) {
  header("Location: index.php");
  exit;
}

require_once __DIR__ . "/koneksi.php";
$admin = $_SESSION['admin_username'];

// ==== STATS ====
$stats = [
  "total" => 0,
  "menunggu" => 0,
  "proses" => 0,
  "selesai" => 0,
];

$qTotal = mysqli_query($conn, "SELECT COUNT(*) AS c FROM input_aspirasi");
$stats["total"] = (int) (mysqli_fetch_assoc($qTotal)["c"] ?? 0);

// hitung status dari tabel aspirasi (yang belum ada status dianggap Menunggu)
$qStatus = mysqli_query($conn, "
  SELECT 
    SUM(CASE WHEN COALESCE(a.status,'Menunggu')='Menunggu' THEN 1 ELSE 0 END) AS menunggu,
    SUM(CASE WHEN COALESCE(a.status,'Menunggu')='Proses' THEN 1 ELSE 0 END) AS proses,
    SUM(CASE WHEN COALESCE(a.status,'Menunggu')='Selesai' THEN 1 ELSE 0 END) AS selesai
  FROM input_aspirasi ia
  LEFT JOIN aspirasi a ON a.id_pelaporan = ia.id_pelaporan
");
$st = mysqli_fetch_assoc($qStatus);
$stats["menunggu"] = (int)($st["menunggu"] ?? 0);
$stats["proses"]   = (int)($st["proses"] ?? 0);
$stats["selesai"]  = (int)($st["selesai"] ?? 0);

// ==== LATEST TABLE ====
$qLatest = mysqli_query($conn, "
  SELECT 
    ia.id_pelaporan,
    ia.nis,
    k.ket_kategori,
    ia.lokasi,
    ia.ket,
    COALESCE(a.status,'Menunggu') AS status,
    ia.created_at
  FROM input_aspirasi ia
  JOIN kategori k ON k.id_kategori = ia.id_kategori
  LEFT JOIN aspirasi a ON a.id_pelaporan = ia.id_pelaporan
  ORDER BY ia.created_at DESC, ia.id_pelaporan DESC
  LIMIT 8
");

$latest = [];
while ($row = mysqli_fetch_assoc($qLatest)) {
  $latest[] = $row;
}

function badgeClass($status){
  return match($status){
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
  <title>Dashboard Admin</title>
  <style>
    :root{--bg:#fff;--card:#fff;--text:#0f172a;--muted:#64748b;--border:#e2e8f0;--shadow:0 18px 40px rgba(15,23,42,.06);--shadow2:0 10px 24px rgba(15,23,42,.05);--primary:#2563eb;--ring:rgba(37,99,235,.16);--radius:18px;}
    *{box-sizing:border-box} html,body{height:100%}
    body{margin:0;font-family:system-ui,-apple-system,Segoe UI,Roboto,Arial;background:var(--bg);color:var(--text)}
    a{color:inherit;text-decoration:none} a:hover{opacity:.9}
    .layout{min-height:100vh;display:grid;grid-template-columns:280px 1fr}
    .sidebar{border-right:1px solid var(--border);padding:18px 16px;position:sticky;top:0;height:100vh;background:#fff}
    .brand{display:flex;gap:12px;align-items:center;padding:10px;border:1px solid var(--border);border-radius:16px;box-shadow:var(--shadow2)}
    .logo{width:42px;height:42px;border-radius:14px;background:linear-gradient(135deg,var(--primary),rgba(99,102,241,.95));display:grid;place-items:center;color:#fff;font-weight:900;box-shadow:0 16px 26px rgba(37,99,235,.18)}
    .brand-title{display:flex;flex-direction:column;line-height:1.2} .brand-title b{font-size:14px} .brand-title span{font-size:12px;color:var(--muted)}
    .nav{margin-top:14px;display:grid;gap:8px}
    .nav a{display:flex;align-items:center;justify-content:space-between;gap:10px;padding:12px;border-radius:14px;border:1px solid transparent}
    .nav a:hover{background:rgba(15,23,42,.03);border-color:rgba(226,232,240,.9)}
    .nav a.active{background:rgba(37,99,235,.06);border-color:rgba(37,99,235,.18)}
    .nav small{color:var(--muted);font-weight:600}
    .sidebar-footer{position:absolute;left:16px;right:16px;bottom:16px;border:1px solid var(--border);border-radius:16px;padding:12px;box-shadow:var(--shadow2)}
    .user{display:flex;align-items:center;justify-content:space-between;gap:10px}
    .user-info{display:flex;flex-direction:column;line-height:1.2} .user-info b{font-size:13px} .user-info span{font-size:12px;color:var(--muted)}
    .btn{border:1px solid var(--border);background:#fff;padding:8px 10px;border-radius:12px;cursor:pointer;font-weight:700}
    .btn:hover{box-shadow:0 0 0 6px rgba(15,23,42,.04)}
    .main{padding:18px 18px 26px}
    .topbar{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:10px 6px 16px}
    .topbar h1{margin:0;font-size:20px}
    .topbar .sub{margin-top:4px;color:var(--muted);font-size:13px}
    .actions{display:flex;gap:10px;align-items:center}
    .search{display:flex;align-items:center;gap:10px;border:1px solid var(--border);border-radius:14px;padding:10px 12px;min-width:280px}
    .search input{border:0;outline:0;width:100%;font-size:13px}
    .primary{background:linear-gradient(135deg,var(--primary),rgba(99,102,241,.95));color:#fff;border:0;box-shadow:0 16px 28px rgba(37,99,235,.18)}
    .grid{display:grid;grid-template-columns:repeat(12,1fr);gap:14px}
    .card{background:var(--card);border:1px solid var(--border);border-radius:var(--radius);box-shadow:var(--shadow)}
    .card.pad{padding:16px}
    .stat{grid-column:span 3;display:flex;align-items:flex-start;justify-content:space-between;gap:12px}
    .kpi b{display:block;font-size:22px;margin-top:4px}
    .kpi span{color:var(--muted);font-size:13px}
    .pill{font-size:12px;font-weight:800;padding:6px 10px;border-radius:999px;border:1px solid var(--border);background:#fff}
    .pill.good{border-color:rgba(22,163,74,.25);background:rgba(22,163,74,.06);color:#166534}
    .pill.warn{border-color:rgba(245,158,11,.25);background:rgba(245,158,11,.08);color:#92400e}
    .table-card{grid-column:span 8} .side-card{grid-column:span 4}
    .card-title{display:flex;align-items:center;justify-content:space-between;gap:10px;padding:14px 16px;border-bottom:1px solid var(--border)}
    .card-title h2{margin:0;font-size:14px}
    .card-title a{color:var(--primary);font-weight:800;font-size:13px}
    .table-wrap{overflow:auto}
    table{width:100%;border-collapse:collapse;font-size:13px}
    th,td{padding:12px 16px;border-bottom:1px solid var(--border);text-align:left;white-space:nowrap}
    th{color:#334155;font-size:12px;letter-spacing:.2px}
    .badge{font-size:12px;font-weight:800;padding:6px 10px;border-radius:999px;display:inline-block;border:1px solid var(--border);background:#fff}
    .badge.wait{border-color:rgba(245,158,11,.25);background:rgba(245,158,11,.08);color:#92400e}
    .badge.proc{border-color:rgba(37,99,235,.25);background:rgba(37,99,235,.08);color:#1d4ed8}
    .badge.done{border-color:rgba(22,163,74,.25);background:rgba(22,163,74,.06);color:#166534}
    .quick{display:grid;gap:10px;padding:16px}
    .quick a{display:flex;align-items:center;justify-content:space-between;gap:10px;padding:12px;border:1px solid var(--border);border-radius:16px;background:#fff}
    .quick a:hover{box-shadow:0 0 0 6px rgba(15,23,42,.04)}
    .muted{color:var(--muted)}
    @media (max-width:980px){
      .layout{grid-template-columns:1fr}
      .sidebar{position:relative;height:auto}
      .sidebar-footer{position:relative;left:auto;right:auto;bottom:auto;margin-top:12px}
      .search{min-width:0;width:100%}
      .actions{flex-direction:column;align-items:stretch}
      .stat{grid-column:span 6}
      .table-card,.side-card{grid-column:span 12}
    }
    @media (max-width:520px){.stat{grid-column:span 12}}
  </style>
</head>
<body>
<div class="layout">
  <aside class="sidebar">
    <div class="brand">
      <div class="logo">AS</div>
      <div class="brand-title">
        <b>Aspirasi Sarana</b>
        <span>Panel Admin</span>
      </div>
    </div>

    <nav class="nav">
      <a class="active" href="dashboard.php"><span>Dashboard</span><small>Home</small></a>
      <a href="data_aspirasi.php"><span>Data Aspirasi</span><small>List</small></a>
      <a href="#" onclick="return false;"><span>Kategori</span><small>Master</small></a>
      <a href="#" onclick="return false;"><span>Laporan</span><small>Filter</small></a>
    </nav>

    <div class="sidebar-footer">
      <div class="user">
        <div class="user-info">
          <b><?= htmlspecialchars($admin) ?></b>
          <span>Administrator</span>
        </div>
        <a class="btn" href="logout.php">Logout</a>
      </div>
    </div>
  </aside>

  <main class="main">
    <header class="topbar">
      <div>
        <h1>Dashboard</h1>
        <div class="sub">Ringkasan aspirasi & aktivitas terbaru.</div>
      </div>
      <div class="actions">
        <div class="search">
          <span class="muted">🔎</span>
          <input type="text" placeholder="Pencarian (nanti bisa dibuat beneran)..." disabled />
        </div>
        <a class="btn primary" href="data_aspirasi.php">Kelola Aspirasi</a>
      </div>
    </header>

    <section class="grid">
      <div class="card pad stat"><div class="kpi"><span>Total Laporan</span><b><?= $stats["total"] ?></b></div><span class="pill">All</span></div>
      <div class="card pad stat"><div class="kpi"><span>Menunggu</span><b><?= $stats["menunggu"] ?></b></div><span class="pill warn">Pending</span></div>
      <div class="card pad stat"><div class="kpi"><span>Proses</span><b><?= $stats["proses"] ?></b></div><span class="pill">In Progress</span></div>
      <div class="card pad stat"><div class="kpi"><span>Selesai</span><b><?= $stats["selesai"] ?></b></div><span class="pill good">Done</span></div>

      <div class="card table-card">
        <div class="card-title">
          <h2>Aspirasi Terbaru</h2>
          <a href="data_aspirasi.php">Lihat semua</a>
        </div>
        <div class="table-wrap">
          <table>
            <thead>
              <tr>
                <th>ID</th>
                <th>NIS</th>
                <th>Kategori</th>
                <th>Lokasi</th>
                <th>Keterangan</th>
                <th>Status</th>
                <th>Tanggal</th>
              </tr>
            </thead>
            <tbody>
              <?php if (count($latest) === 0): ?>
                <tr><td colspan="7" class="muted">Belum ada laporan.</td></tr>
              <?php else: ?>
                <?php foreach ($latest as $r): ?>
                  <tr>
                    <td>#<?= (int)$r["id_pelaporan"] ?></td>
                    <td><?= htmlspecialchars($r["nis"]) ?></td>
                    <td><?= htmlspecialchars($r["ket_kategori"]) ?></td>
                    <td><?= htmlspecialchars($r["lokasi"]) ?></td>
                    <td><?= htmlspecialchars($r["ket"]) ?></td>
                    <td><span class="badge <?= badgeClass($r["status"]) ?>"><?= htmlspecialchars($r["status"]) ?></span></td>
                    <td><?= htmlspecialchars($r["created_at"]) ?></td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>

      <div class="card side-card">
        <div class="card-title">
          <h2>Quick Actions</h2>
          <span class="muted">Shortcut</span>
        </div>
        <div class="quick">
          <a href="data_aspirasi.php"><div><b>Kelola Status & Feedback</b><br><span class="muted">Menunggu → Proses → Selesai</span></div><span>›</span></a>
          <a href="data_aspirasi.php?status=Menunggu"><div><b>Lihat yang Menunggu</b><br><span class="muted">Butuh tindakan admin</span></div><span>›</span></a>
          <a href="data_aspirasi.php?status=Proses"><div><b>Lihat yang Proses</b><br><span class="muted">Sedang dikerjakan</span></div><span>›</span></a>
          <a href="data_aspirasi.php?status=Selesai"><div><b>Lihat yang Selesai</b><br><span class="muted">Sudah ditutup</span></div><span>›</span></a>
          <a href="data_siswa.php"><span>Data Siswa</span><small>Master</small></a>
            <a href="tambah_siswa.php"><span>Tambah Siswa</span><small>Create</small></a>
        </div>
      </div>
    </section>
  </main>
</div>
</body>
</html>
