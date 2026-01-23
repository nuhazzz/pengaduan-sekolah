<?php
session_start();

$error = $_SESSION['login_error'] ?? '';
unset($_SESSION['login_error']);

// kalau sudah login, lempar sesuai role
if (!empty($_SESSION['admin_username'])) {
  header("Location: dashboard.php");
  exit;
}
if (!empty($_SESSION['siswa_nis'])) {
  header("Location: siswa_dashboard.php");
  exit;
}
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Login - Aspirasi Sarana</title>
  <style>
    :root{
      --bg:#fff; --card:#fff; --text:#0f172a; --muted:#64748b;
      --border:#e2e8f0; --shadow:0 20px 45px rgba(15,23,42,.08);
      --primary:#2563eb; --ring:rgba(37,99,235,.18);
      --radius:18px;
    }
    *{box-sizing:border-box}
    body{
      margin:0; font-family:system-ui,-apple-system,Segoe UI,Roboto,Arial;
      background:var(--bg); color:var(--text);
      min-height:100vh; display:flex; align-items:center; justify-content:center;
      padding:28px 16px;
    }
    .shell{width:min(520px,100%)}
    .card{
      border:1px solid var(--border);
      border-radius:var(--radius);
      background:var(--card);
      box-shadow:var(--shadow);
      padding:22px;
    }
    h1{margin:0 0 6px;font-size:20px}
    .hint{margin:0 0 16px;color:var(--muted);font-size:13px}
    .field{margin-bottom:12px}
    label{display:block;margin:0 0 6px;font-size:13px;color:#334155}
    .input{
      width:100%; display:flex; align-items:center; gap:10px;
      border:1px solid var(--border); border-radius:14px;
      padding:12px; background:#fff; transition:.15s;
    }
    .input:focus-within{border-color:rgba(37,99,235,.55); box-shadow:0 0 0 6px var(--ring);}
    input, select{
      width:100%; border:0; outline:0;
      font-size:14px; background:transparent; color:var(--text);
    }
    select{appearance:none}
    .btn-ghost{
      border:0;background:transparent;cursor:pointer;font-size:13px;
      color:#475569;padding:6px 8px;border-radius:10px;
    }
    .btn-ghost:hover{background:rgba(15,23,42,.04)}
    .btn{
      width:100%; border:0; cursor:pointer;
      padding:12px 14px; border-radius:14px;
      background:linear-gradient(135deg, var(--primary), rgba(99,102,241,.95));
      color:#fff; font-weight:800; font-size:14px;
      box-shadow:0 16px 28px rgba(37,99,235,.18);
      margin-top:10px;
    }
    .error{
      margin-top:12px; padding:10px 12px; border-radius:14px;
      border:1px solid rgba(239,68,68,.35);
      background:rgba(239,68,68,.06);
      color:#991b1b; font-size:13px;
    }
    .footer{margin-top:12px;text-align:center;color:var(--muted);font-size:12px}
    .row{display:flex;gap:10px}
  </style>
</head>
<body>
  <main class="shell">
    <section class="card">
      <h1>Login</h1>
      <p class="hint">Pilih role (Admin / Siswa), lalu masukkan akun.</p>

      <form action="proses_login.php" method="post" novalidate>
        <div class="field">
          <label for="role">Masuk sebagai</label>
          <div class="input">
            <select id="role" name="role" required>
              <option value="SISWA">Siswa</option>
              <option value="ADMIN">Admin</option>
            </select>
          </div>
        </div>

        <div class="field">
          <label for="username">Username / NIS</label>
          <div class="input">
            <input id="username" name="username" type="text" placeholder="contoh: admin / 23001" required>
          </div>
        </div>

        <div class="field">
          <label for="password">Password</label>
          <div class="input">
            <input id="password" name="password" type="password" placeholder="••••••••" required minlength="6">
            <button class="btn-ghost" type="button" id="togglePw">Lihat</button>
          </div>
        </div>

        <?php if ($error): ?>
          <div class="error" role="alert"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <button class="btn" type="submit">Masuk</button>
      </form>

      <div class="footer">© 2025/2026 • Aplikasi Pengaduan Sarana Sekolah</div>
    </section>
  </main>

  <script>
    const pw = document.getElementById('password');
    const toggle = document.getElementById('togglePw');
    toggle.addEventListener('click', () => {
      const isHidden = pw.type === 'password';
      pw.type = isHidden ? 'text' : 'password';
      toggle.textContent = isHidden ? 'Sembunyi' : 'Lihat';
      pw.focus();
    });
  </script>
</body>
</html>
