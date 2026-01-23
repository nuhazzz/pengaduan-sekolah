<?php
session_start();
if (empty($_SESSION['admin_username'])) {
  header("Location: index.php");
  exit;
}
require_once __DIR__ . "/koneksi.php";

$status = $_GET['status'] ?? '';
$nis    = trim($_GET['nis'] ?? '');
$kat    = $_GET['kat'] ?? '';

$flash = $_SESSION['flash'] ?? '';
unset($_SESSION['flash']);

// ambil kategori buat dropdown
$cats = [];
$qCat = mysqli_query($conn, "SELECT id_kategori, ket_kategori FROM kategori ORDER BY ket_kategori ASC");
while ($c = mysqli_fetch_assoc($qCat)) $cats[] = $c;

// query data aspirasi (join laporan + kategori + status)
$sql = "
  SELECT 
    ia.id_pelaporan,
    ia.nis,
    ia.lokasi,
    ia.ket,
    ia.created_at,
    k.id_kategori,
    k.ket_kategori,
    COALESCE(a.status,'Menunggu') AS status,
    COALESCE(a.feedback_text,'') AS feedback_text
  FROM input_aspirasi ia
  JOIN kategori k ON k.id_kategori = ia.id_kategori
  LEFT JOIN aspirasi a ON a.id_pelaporan = ia.id_pelaporan
  WHERE 1=1
";

$params = [];
$types  = "";

if ($status !== '') {
  $sql .= " AND COALESCE(a.status,'Menunggu') = ? ";
  $types .= "s";
  $params[] = $status;
}
if ($nis !== '') {
  $sql .= " AND ia.nis = ? ";
  $types .= "i";
  $params[] = (int)$nis;
}
if ($kat !== '') {
  $sql .= " AND k.id_kategori = ? ";
  $types .= "i";
  $params[] = (int)$kat;
}

$sql .= " ORDER BY ia.created_at DESC, ia.id_pelaporan DESC LIMIT 100";

$stmt = mysqli_prepare($conn, $sql);
if ($types !== "") {
  mysqli_stmt_bind_param($stmt, $types, ...$params);
}
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);

$data = [];
while ($r = mysqli_fetch_assoc($res)) $data[] = $r;

function badgeClass($s){
  return match($s){
    "Menunggu" => "wait",
    "Proses" => "proc",
    "Selesai" => "done",
    default => "wait"
  };
}
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Data Aspirasi</title>
  <style>
    :root{--bg:#fff;--text:#0f172a;--muted:#64748b;--border:#e2e8f0;--shadow:0 18px 40px rgba(15,23,42,.06);--primary:#2563eb;--ring:rgba(37,99,235,.16);--radius:18px;}
    *{box-sizing:border-box}
    body{margin:0;font-family:system-ui,-apple-system,Segoe UI,Roboto,Arial;background:var(--bg);color:var(--text);padding:18px}
    .wrap{max-width:1100px;margin:0 auto}
    .top{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:12px}
    .top a{color:var(--primary);font-weight:800;text-decoration:none}
    .card{border:1px solid var(--border);border-radius:var(--radius);box-shadow:var(--shadow);background:#fff}
    .card.pad{padding:14px}
    .grid{display:grid;grid-template-columns:repeat(12,1fr);gap:12px}
    .col6{grid-column:span 6} .col3{grid-column:span 3} .col12{grid-column:span 12}
    label{display:block;font-size:12px;color:#334155;margin:0 0 6px}
    .input, select, textarea{
      width:100%;border:1px solid var(--border);border-radius:14px;padding:10px 12px;
      outline:0;font-size:13px;background:#fff;
    }
    .input:focus, select:focus, textarea:focus{box-shadow:0 0 0 6px var(--ring);border-color:rgba(37,99,235,.55)}
    .btn{border:1px solid var(--border);background:#fff;padding:10px 12px;border-radius:14px;font-weight:800;cursor:pointer}
    .btn.primary{border:0;background:linear-gradient(135deg,var(--primary),rgba(99,102,241,.95));color:#fff}
    .btn:hover{box-shadow:0 0 0 6px rgba(15,23,42,.04)}
    table{width:100%;border-collapse:collapse;font-size:13px}
    th,td{padding:12px 14px;border-bottom:1px solid var(--border);text-align:left;vertical-align:top}
    th{font-size:12px;color:#334155}
    .badge{font-size:12px;font-weight:900;padding:6px 10px;border-radius:999px;border:1px solid var(--border);display:inline-block;background:#fff}
    .badge.wait{border-color:rgba(245,158,11,.25);background:rgba(245,158,11,.08);color:#92400e}
    .badge.proc{border-color:rgba(37,99,235,.25);background:rgba(37,99,235,.08);color:#1d4ed8}
    .badge.done{border-color:rgba(22,163,74,.25);background:rgba(22,163,74,.06);color:#166534}
    .muted{color:var(--muted)}
    .flash{margin-bottom:12px;padding:10px 12px;border-radius:14px;border:1px solid rgba(22,163,74,.25);background:rgba(22,163,74,.06);color:#166534}
    textarea{min-height:90px;resize:vertical}
    @media (max-width:900px){
      .col6,.col3{grid-column:span 12}
      body{padding:12px}
    }
  </style>
</head>
<body>
<div class="wrap">
  <div class="top">
    <div>
      <h2 style="margin:0">Data Aspirasi</h2>
      <div class="muted" style="font-size:13px;margin-top:4px">Kelola status & feedback admin.</div>
    </div>
    <a href="dashboard.php">← Kembali ke Dashboard</a>
  </div>

  <?php if ($flash): ?>
    <div class="flash"><?= htmlspecialchars($flash) ?></div>
  <?php endif; ?>

  <div class="card pad" style="margin-bottom:12px">
    <form method="get">
      <div class="grid">
        <div class="col3">
          <label>Status</label>
          <select name="status">
            <option value="">Semua</option>
            <option value="Menunggu" <?= $status==='Menunggu'?'selected':'' ?>>Menunggu</option>
            <option value="Proses"   <?= $status==='Proses'?'selected':'' ?>>Proses</option>
            <option value="Selesai"  <?= $status==='Selesai'?'selected':'' ?>>Selesai</option>
          </select>
        </div>

        <div class="col3">
          <label>Kategori</label>
          <select name="kat">
            <option value="">Semua</option>
            <?php foreach ($cats as $c): ?>
              <option value="<?= (int)$c["id_kategori"] ?>" <?= ((string)$kat === (string)$c["id_kategori"]) ? 'selected' : '' ?>>
                <?= htmlspecialchars($c["ket_kategori"]) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="col3">
          <label>NIS</label>
          <input class="input" name="nis" value="<?= htmlspecialchars($nis) ?>" placeholder="contoh: 23001">
        </div>

        <div class="col3" style="display:flex;gap:10px;align-items:end">
          <button class="btn primary" type="submit">Terapkan</button>
          <a class="btn" href="data_aspirasi.php" style="text-align:center;display:inline-block">Reset</a>
        </div>
      </div>
    </form>
  </div>

  <div class="card">
    <div style="padding:14px;border-bottom:1px solid var(--border);display:flex;justify-content:space-between;gap:10px;align-items:center">
      <b>Daftar (maks 100 data)</b>
      <span class="muted" style="font-size:12px">Klik “Simpan” untuk update status/feedback</span>
    </div>

    <div style="overflow:auto">
      <table>
        <thead>
          <tr>
            <th>ID</th>
            <th>NIS</th>
            <th>Kategori</th>
            <th>Lokasi</th>
            <th>Keterangan</th>
            <th>Status</th>
            <th>Feedback</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
        <?php if (count($data) === 0): ?>
          <tr><td colspan="8" class="muted">Tidak ada data.</td></tr>
        <?php else: ?>
          <?php foreach ($data as $r): ?>
            <tr>
              <td>#<?= (int)$r["id_pelaporan"] ?><br><span class="muted" style="font-size:12px"><?= htmlspecialchars($r["created_at"]) ?></span></td>
              <td><?= htmlspecialchars($r["nis"]) ?></td>
              <td><?= htmlspecialchars($r["ket_kategori"]) ?></td>
              <td><?= htmlspecialchars($r["lokasi"]) ?></td>
              <td><?= htmlspecialchars($r["ket"]) ?></td>
              <td><span class="badge <?= badgeClass($r["status"]) ?>"><?= htmlspecialchars($r["status"]) ?></span></td>

              <td style="min-width:260px">
                <form method="post" action="proses_update_aspirasi.php">
                  <input type="hidden" name="id_pelaporan" value="<?= (int)$r["id_pelaporan"] ?>">

                  <label style="margin-top:0">Ubah Status</label>
                  <select name="status">
                    <option value="Menunggu" <?= $r["status"]==='Menunggu'?'selected':'' ?>>Menunggu</option>
                    <option value="Proses"   <?= $r["status"]==='Proses'?'selected':'' ?>>Proses</option>
                    <option value="Selesai"  <?= $r["status"]==='Selesai'?'selected':'' ?>>Selesai</option>
                  </select>

                  <label style="margin-top:10px">Feedback Admin</label>
                  <textarea name="feedback_text" placeholder="Tulis feedback..."><?= htmlspecialchars($r["feedback_text"]) ?></textarea>

                  <div style="margin-top:10px;display:flex;gap:10px">
                    <button class="btn primary" type="submit">Simpan</button>
                    <a class="btn" href="data_aspirasi.php?id=<?= (int)$r["id_pelaporan"] ?>" onclick="return false;">Detail</a>
                  </div>
                </form>
              </td>

              <td class="muted">—</td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
</body>
</html>
