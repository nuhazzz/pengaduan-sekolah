<?php
session_start();
if (empty($_SESSION['siswa_nis'])) {
  header("Location: index.php");
  exit;
}
require_once __DIR__ . "/koneksi.php";

$nis = (int)$_SESSION['siswa_nis'];

$flash_ok = $_SESSION['flash_ok'] ?? '';
$flash_err = $_SESSION['flash_err'] ?? '';
unset($_SESSION['flash_ok'], $_SESSION['flash_err']);

// ambil kategori
$cats = [];
$q = mysqli_query($conn, "SELECT id_kategori, ket_kategori FROM kategori ORDER BY ket_kategori ASC");
while ($c = mysqli_fetch_assoc($q)) $cats[] = $c;
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Buat Laporan</title>
  <style>
    :root{--bg:#fff;--text:#0f172a;--muted:#64748b;--border:#e2e8f0;--shadow:0 18px 40px rgba(15,23,42,.06);--primary:#2563eb;--ring:rgba(37,99,235,.16);--radius:18px}
    *{box-sizing:border-box}
    body{margin:0;font-family:system-ui,-apple-system,Segoe UI,Roboto,Arial;background:var(--bg);color:var(--text);padding:18px}
    .wrap{max-width:860px;margin:0 auto}
    .top{display:flex;justify-content:space-between;gap:12px;align-items:flex-start;margin-bottom:12px}
    a{color:var(--primary);font-weight:900;text-decoration:none}
    a:hover{text-decoration:underline}
    .card{border:1px solid var(--border);border-radius:var(--radius);background:#fff;box-shadow:var(--shadow)}
    .pad{padding:16px}
    label{display:block;font-size:12px;color:#334155;margin:0 0 6px}
    input,select,textarea{width:100%;border:1px solid var(--border);border-radius:14px;padding:10px 12px;font-size:13px;outline:0;background:#fff}
    input:focus,select:focus,textarea:focus{border-color:rgba(37,99,235,.55);box-shadow:0 0 0 6px var(--ring)}
    textarea{min-height:120px;resize:vertical}
    .grid{display:grid;grid-template-columns:repeat(12,1fr);gap:12px}
    .col6{grid-column:span 6}
    .col12{grid-column:span 12}
    .btn{border:1px solid var(--border);background:#fff;padding:10px 12px;border-radius:14px;font-weight:900;cursor:pointer}
    .btn.primary{border:0;color:#fff;background:linear-gradient(135deg,var(--primary),rgba(99,102,241,.95));box-shadow:0 16px 28px rgba(37,99,235,.18)}
    .btn:hover{box-shadow:0 0 0 6px rgba(15,23,42,.04)}
    .alert{margin-bottom:12px;padding:10px 12px;border-radius:14px;font-size:13px;border:1px solid var(--border);background:#fff}
    .ok{border-color:rgba(22,163,74,.25);background:rgba(22,163,74,.06);color:#166534}
    .err{border-color:rgba(239,68,68,.25);background:rgba(239,68,68,.06);color:#991b1b}
    @media(max-width:720px){.col6{grid-column:span 12}}
  </style>
</head>
<body>
<div class="wrap">
  <div class="top">
    <div>
      <h2 style="margin:0">Buat Laporan</h2>
      <div style="margin-top:6px;color:#64748b;font-size:13px">NIS: <b><?= htmlspecialchars((string)$nis) ?></b></div>
    </div>
    <a href="siswa_dashboard.php">← Dashboard</a>
  </div>

  <?php if ($flash_ok): ?><div class="alert ok"><?= htmlspecialchars($flash_ok) ?></div><?php endif; ?>
  <?php if ($flash_err): ?><div class="alert err"><?= htmlspecialchars($flash_err) ?></div><?php endif; ?>

  <div class="card pad">
    <form method="post" action="proses_buat_laporan.php">
      <div class="grid">
        <div class="col6">
          <label>Kategori</label>
          <select name="id_kategori" required>
            <option value="">-- pilih kategori --</option>
            <?php foreach ($cats as $c): ?>
              <option value="<?= (int)$c["id_kategori"] ?>"><?= htmlspecialchars($c["ket_kategori"]) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="col6">
          <label>Lokasi</label>
          <input name="lokasi" placeholder="contoh: Lab Komputer / Kelas X RPL 2" required>
        </div>

        <div class="col12">
          <label>Keterangan</label>
          <textarea name="ket" placeholder="Tulis detail masalah/aspirasi..." required></textarea>
        </div>

        <div class="col12" style="display:flex;gap:10px;flex-wrap:wrap">
          <button class="btn primary" type="submit">Kirim Laporan</button>
          <a class="btn" href="siswa_dashboard.php" style="text-decoration:none;color:inherit">Batal</a>
        </div>
      </div>
    </form>
  </div>
</div>
</body>
</html>
