<?php
session_start();
if (empty($_SESSION['admin_username'])) {
  header("Location: index.php");
  exit;
}
require_once __DIR__ . "/koneksi.php";

$q = trim($_GET['q'] ?? '');

$flash_ok = $_SESSION['flash_ok'] ?? '';
$flash_err = $_SESSION['flash_err'] ?? '';
unset($_SESSION['flash_ok'], $_SESSION['flash_err']);

$sql = "SELECT nis, kelas FROM siswa WHERE 1=1";
$params = [];
$types = "";

if ($q !== '') {
  // cari by NIS atau kelas
  $sql .= " AND (CAST(nis AS CHAR) LIKE CONCAT('%', ?, '%') OR kelas LIKE CONCAT('%', ?, '%')) ";
  $types .= "ss";
  $params[] = $q;
  $params[] = $q;
}
$sql .= " ORDER BY nis ASC LIMIT 300";

$stmt = mysqli_prepare($conn, $sql);
if ($types !== "") {
  mysqli_stmt_bind_param($stmt, $types, ...$params);
}
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);

$siswa = [];
while ($row = mysqli_fetch_assoc($res)) $siswa[] = $row;
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Data Siswa</title>
  <style>
    :root{--bg:#fff;--text:#0f172a;--muted:#64748b;--border:#e2e8f0;--shadow:0 18px 40px rgba(15,23,42,.06);--primary:#2563eb;--ring:rgba(37,99,235,.16);--radius:18px}
    *{box-sizing:border-box}
    body{margin:0;font-family:system-ui,-apple-system,Segoe UI,Roboto,Arial;background:var(--bg);color:var(--text);padding:18px}
    .wrap{max-width:1100px;margin:0 auto}
    .top{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:12px}
    .top a{color:var(--primary);font-weight:900;text-decoration:none}
    .top a:hover{text-decoration:underline}
    .card{border:1px solid var(--border);border-radius:var(--radius);background:#fff;box-shadow:var(--shadow)}
    .card.pad{padding:14px}
    h1{margin:0;font-size:18px}
    .hint{margin:6px 0 0;color:var(--muted);font-size:13px}
    .grid{display:grid;grid-template-columns:repeat(12,1fr);gap:12px}
    .col8{grid-column:span 8}
    .col4{grid-column:span 4}
    label{display:block;font-size:12px;color:#334155;margin:0 0 6px}
    input{
      width:100%;border:1px solid var(--border);border-radius:14px;
      padding:10px 12px;font-size:13px;outline:0;background:#fff;
    }
    input:focus{border-color:rgba(37,99,235,.55);box-shadow:0 0 0 6px var(--ring)}
    .row{display:flex;gap:10px;align-items:center}
    .btn{
      border:1px solid var(--border);background:#fff;
      padding:10px 12px;border-radius:14px;font-weight:900;cursor:pointer;
      text-decoration:none;display:inline-block;text-align:center;
    }
    .btn.primary{border:0;color:#fff;background:linear-gradient(135deg,var(--primary),rgba(99,102,241,.95));box-shadow:0 16px 28px rgba(37,99,235,.18)}
    .btn.danger{border-color:rgba(239,68,68,.25);background:rgba(239,68,68,.06);color:#991b1b}
    .btn:hover{box-shadow:0 0 0 6px rgba(15,23,42,.04)}
    .alert{margin-bottom:12px;padding:10px 12px;border-radius:14px;font-size:13px;border:1px solid var(--border);background:#fff}
    .ok{border-color:rgba(22,163,74,.25);background:rgba(22,163,74,.06);color:#166534}
    .err{border-color:rgba(239,68,68,.25);background:rgba(239,68,68,.06);color:#991b1b}
    table{width:100%;border-collapse:collapse;font-size:13px}
    th,td{padding:12px 14px;border-bottom:1px solid var(--border);text-align:left;vertical-align:top;white-space:nowrap}
    th{font-size:12px;color:#334155}
    .muted{color:var(--muted)}
    .actions{display:flex;gap:8px;flex-wrap:wrap}
    .mini{padding:8px 10px;border-radius:12px;font-size:12px}
    .inline{
      display:flex;gap:8px;align-items:center;flex-wrap:wrap;
    }
    .inline input{width:220px}
    @media (max-width:900px){
      .col8,.col4{grid-column:span 12}
      .inline input{width:100%}
    }
  </style>
</head>
<body>
<div class="wrap">
  <div class="top">
    <div>
      <h1>Data Siswa</h1>
      <div class="hint">Cari siswa, ubah kelas, reset password, atau hapus.</div>
    </div>
    <div class="row">
      <a class="btn" href="dashboard.php">← Dashboard</a>
      <a class="btn primary" href="tambah_siswa.php">+ Tambah Siswa</a>
    </div>
  </div>

  <?php if ($flash_ok): ?><div class="alert ok"><?= htmlspecialchars($flash_ok) ?></div><?php endif; ?>
  <?php if ($flash_err): ?><div class="alert err"><?= htmlspecialchars($flash_err) ?></div><?php endif; ?>

  <div class="card pad" style="margin-bottom:12px">
    <form method="get">
      <div class="grid">
        <div class="col8">
          <label>Cari (NIS / Kelas)</label>
          <input name="q" value="<?= htmlspecialchars($q) ?>" placeholder="contoh: 23001 atau XI RPL 1">
        </div>
        <div class="col4" style="display:flex;gap:10px;align-items:end">
          <button class="btn primary" type="submit">Cari</button>
          <a class="btn" href="data_siswa.php">Reset</a>
        </div>
      </div>
    </form>
  </div>

  <div class="card">
    <div style="padding:14px;border-bottom:1px solid var(--border);display:flex;justify-content:space-between;gap:10px;align-items:center">
      <b>Daftar Siswa (maks 300)</b>
      <span class="muted" style="font-size:12px">Password disimpan hash (aman)</span>
    </div>

    <div style="overflow:auto">
      <table>
        <thead>
          <tr>
            <th>NIS</th>
            <th>Kelas</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
        <?php if (count($siswa) === 0): ?>
          <tr><td colspan="3" class="muted">Data tidak ditemukan.</td></tr>
        <?php else: ?>
          <?php foreach ($siswa as $s): ?>
            <tr>
              <td><b><?= htmlspecialchars($s['nis']) ?></b></td>

              <td style="min-width:320px">
                <form class="inline" method="post" action="proses_update_siswa.php">
                  <input type="hidden" name="nis" value="<?= (int)$s['nis'] ?>">
                  <input name="kelas" value="<?= htmlspecialchars($s['kelas']) ?>" placeholder="Kelas">
                  <button class="btn mini" type="submit" name="aksi" value="update_kelas">Simpan Kelas</button>
                </form>
              </td>

              <td style="min-width:420px">
                <div class="actions">
                  <form method="post" action="proses_update_siswa.php" class="inline">
                    <input type="hidden" name="nis" value="<?= (int)$s['nis'] ?>">
                    <input name="new_password" type="password" minlength="6" placeholder="Password baru (min 6)">
                    <button class="btn mini" type="submit" name="aksi" value="reset_password">Reset Password</button>
                  </form>

                  <form method="post" action="proses_hapus_siswa.php" onsubmit="return confirm('Yakin hapus siswa NIS <?= htmlspecialchars($s['nis']) ?>?');">
                    <input type="hidden" name="nis" value="<?= (int)$s['nis'] ?>">
                    <button class="btn mini danger" type="submit">Hapus</button>
                  </form>
                </div>
                <div class="muted" style="font-size:12px;margin-top:6px">
                  Tips: reset password lalu kasih ke siswa untuk login.
                </div>
              </td>
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
