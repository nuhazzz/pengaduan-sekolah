<?php
session_start();
if (empty($_SESSION['admin_username'])) {
  header("Location: index.php");
  exit;
}

$flash_ok = $_SESSION['flash_ok'] ?? '';
$flash_err = $_SESSION['flash_err'] ?? '';
unset($_SESSION['flash_ok'], $_SESSION['flash_err']);
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Tambah Siswa</title>
  <style>
    :root{--bg:#fff;--text:#0f172a;--muted:#64748b;--border:#e2e8f0;--shadow:0 18px 40px rgba(15,23,42,.06);--primary:#2563eb;--ring:rgba(37,99,235,.16);--radius:18px}
    *{box-sizing:border-box}
    body{margin:0;font-family:system-ui,-apple-system,Segoe UI,Roboto,Arial;background:var(--bg);color:var(--text);padding:18px}
    .wrap{max-width:820px;margin:0 auto}
    .top{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:12px}
    .top a{color:var(--primary);font-weight:900;text-decoration:none}
    .top a:hover{text-decoration:underline}
    .card{border:1px solid var(--border);border-radius:var(--radius);background:#fff;box-shadow:var(--shadow)}
    .card.pad{padding:16px}
    h1{margin:0;font-size:18px}
    .hint{margin:6px 0 0;color:var(--muted);font-size:13px}
    .grid{display:grid;grid-template-columns:repeat(12,1fr);gap:12px;margin-top:12px}
    .col6{grid-column:span 6}
    .col12{grid-column:span 12}
    label{display:block;font-size:12px;color:#334155;margin:0 0 6px}
    input, select{
      width:100%;border:1px solid var(--border);border-radius:14px;
      padding:10px 12px;font-size:13px;outline:0;background:#fff;
    }
    input:focus, select:focus{border-color:rgba(37,99,235,.55);box-shadow:0 0 0 6px var(--ring)}
    .row{display:flex;gap:10px;align-items:center}
    .btn{
      border:1px solid var(--border);background:#fff;
      padding:10px 12px;border-radius:14px;font-weight:900;cursor:pointer;
    }
    .btn.primary{
      border:0;color:#fff;
      background:linear-gradient(135deg,var(--primary),rgba(99,102,241,.95));
      box-shadow:0 16px 28px rgba(37,99,235,.18);
    }
    .btn:hover{box-shadow:0 0 0 6px rgba(15,23,42,.04)}
    .alert{
      margin-top:12px;padding:10px 12px;border-radius:14px;font-size:13px;
      border:1px solid var(--border);background:#fff;
    }
    .ok{border-color:rgba(22,163,74,.25);background:rgba(22,163,74,.06);color:#166534}
    .err{border-color:rgba(239,68,68,.25);background:rgba(239,68,68,.06);color:#991b1b}
    .muted{color:var(--muted);font-size:12px;margin-top:6px}
    @media (max-width:720px){.col6{grid-column:span 12}}
  </style>
</head>
<body>
<div class="wrap">
  <div class="top">
    <div>
      <h1>Tambah Siswa</h1>
      <div class="hint">Admin bisa menambahkan akun siswa (NIS, kelas, password).</div>
    </div>
    <a href="dashboard.php">← Dashboard</a>
  </div>

  <?php if ($flash_ok): ?>
    <div class="alert ok"><?= htmlspecialchars($flash_ok) ?></div>
  <?php endif; ?>
  <?php if ($flash_err): ?>
    <div class="alert err"><?= htmlspecialchars($flash_err) ?></div>
  <?php endif; ?>

  <div class="card pad">
    <form action="proses_tambah_siswa.php" method="post" autocomplete="off">
      <div class="grid">
        <div class="col6">
          <label for="nis">NIS</label>
          <input id="nis" name="nis" type="text" inputmode="numeric" placeholder="contoh: 23001" required>
          <div class="muted">Harus angka, unik.</div>
        </div>

        <div class="col6">
          <label for="kelas">Kelas</label>
          <input id="kelas" name="kelas" type="text" placeholder="contoh: XI RPL 1" required>
        </div>

        <div class="col6">
          <label for="password">Password</label>
          <input id="password" name="password" type="password" placeholder="min. 6 karakter" minlength="6" required>
        </div>

        <div class="col6">
          <label for="password2">Ulangi Password</label>
          <input id="password2" name="password2" type="password" placeholder="ulang password" minlength="6" required>
        </div>

        <div class="col12">
          <div class="row">
            <button class="btn primary" type="submit">Simpan</button>
            <button class="btn" type="reset">Reset</button>
            <a class="btn" href="data_siswa.php" onclick="return false;" style="text-decoration:none">Lihat Data Siswa (opsional)</a>
          </div>
        </div>
      </div>
    </form>
  </div>
</div>
</body>
</html>
