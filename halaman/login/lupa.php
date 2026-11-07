<?php
// halaman/login/lupa.php — lupa kata sandi: kirim kode OTP ke email, lalu perbarui kata sandi
$galat = '';
$info  = '';
$email = $_SESSION['reset_email'] ?? '';

// Ganti email / mulai dari awal
if (isset($_GET['ulang'])) {
    unset($_SESSION['reset_email'], $_SESSION['reset_kirim']);
    redirect('login', 'lupa');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tindakan = $_POST['tindakan'] ?? '';

    // --- Kirim (atau kirim ulang) kode OTP ---
    if ($tindakan === 'kirim' || $tindakan === 'kirim_ulang') {
        $tujuan = $tindakan === 'kirim' ? trim($_POST['email'] ?? '') : $email;
        $sisa   = 60 - (time() - ($_SESSION['reset_kirim'] ?? 0));

        if (!filter_var($tujuan, FILTER_VALIDATE_EMAIL)) {
            $galat = 'Format email tidak valid.';
        } elseif ($sisa > 0) {
            $galat = "Tunggu $sisa detik sebelum meminta kode lagi.";
        } else {
            $r = supabase_request('POST', '/auth/v1/recover', ['email' => $tujuan]);
            if ($r['status'] === 200) {
                $_SESSION['reset_email'] = $email = $tujuan;
                $_SESSION['reset_kirim'] = time();
                // Pesan sengaja umum: tidak membocorkan email itu terdaftar atau tidak
                $info = 'Jika email terdaftar, kode OTP sudah dikirim. Cek inbox atau folder spam.';
            } else {
                // Detail error dari Supabase (hapus bagian detail ini kalau sudah beres)
                $alasan = $r['data']['msg'] ?? $r['data']['error_description']
                       ?? $r['data']['message'] ?? $r['data']['error'] ?? 'tanpa detail';
                $galat = 'Gagal mengirim kode (' . $r['status'] . '): ' . $alasan;
            }
        }
    }

    // --- Verifikasi kode lalu perbarui kata sandi ---
    if ($tindakan === 'reset' && $email !== '') {
        $kode = preg_replace('/\D/', '', $_POST['kode'] ?? '');
        $pass = $_POST['password'] ?? '';

        if (strlen($kode) !==6) {
            $galat = 'Masukkan kode OTP dari email.';
        } elseif (strlen($pass) < 8) {
            $galat = 'Kata sandi minimal 8 karakter.';
        } elseif ($pass !== ($_POST['password2'] ?? '')) {
            $galat = 'Konfirmasi kata sandi tidak sama.';
        } else {
            $v = supabase_request('POST', '/auth/v1/verify', [
                'type' => 'recovery', 'email' => $email, 'token' => $kode,
            ]);
            if ($v['status'] !== 200 || empty($v['data']['access_token'])) {
                $galat = 'Kode salah atau sudah kedaluwarsa.';
            } else {
                $token = $v['data']['access_token'];
                $u = supabase_request('PUT', '/auth/v1/user', ['password' => $pass], $token);
                supabase_request('POST', '/auth/v1/logout', null, $token); // buang sesi sementara

                if ($u['status'] === 200) {
                    unset($_SESSION['reset_email'], $_SESSION['reset_kirim']);
                    $_SESSION['info_login'] = 'Kata sandi berhasil diperbarui. Silakan masuk dengan kata sandi baru.';
                    redirect('login');
                }
                $galat = ($u['data']['msg'] ?? $u['data']['message'] ?? 'Gagal memperbarui kata sandi.')
                       . ' Kode tadi sudah terpakai, klik "Kirim ulang" untuk kode baru.';
            }
        }
    }
}

$langkah = $email !== '' ? 'kode' : 'email';
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Lupa Kata Sandi | <?= e(APP_NAME) ?></title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" crossorigin="anonymous" />
  <style>
    :root { --hijau:#123d35; --hijau-gelap:#0d2e28; --aksen:#f4a98a; --merah:#b42318;
            --teks:#1c2a26; --abu:#6b7873; --garis:#e1e5e0; }
    * { box-sizing: border-box; }
    body { margin:0; min-height:100vh; display:grid; place-items:center; padding:24px;
           background:#f7f7f3; color:var(--teks);
           font-family: Inter, system-ui, -apple-system, "Segoe UI", sans-serif; }
    .kartu { display:grid; grid-template-columns:1.1fr 1fr; width:min(1080px,100%); min-height:560px;
             background:#fff; border-radius:16px; overflow:hidden; box-shadow:0 20px 50px rgba(18,61,53,.15); }

    .kiri { background:linear-gradient(160deg,var(--hijau),var(--hijau-gelap)); color:#fff;
            padding:48px; display:flex; flex-direction:column; justify-content:space-between; gap:32px; }
    .brand { display:flex; align-items:center; gap:12px; }
    .brand .logo { width:44px; height:44px; border-radius:10px; background:rgba(255,255,255,.12);
                   display:grid; place-items:center; font-size:1.4rem; }
    .brand small { display:block; color:var(--aksen); font-size:.7rem; letter-spacing:.08em; text-transform:uppercase; }
    .brand strong { font-size:1.15rem; }
    .kiri h2 { font-size:2.1rem; line-height:1.15; margin:0 0 14px; }
    .kiri p { margin:0; color:rgba(255,255,255,.72); line-height:1.6; }
    .kiri .catatan { font-size:.8rem; color:rgba(255,255,255,.6); display:flex; align-items:center; gap:8px; }

    .kanan { padding:44px 48px; display:flex; flex-direction:column; justify-content:center; animation:masuk .38s ease both; }
    @keyframes masuk { from { opacity:0; transform:translateX(24px); } to { opacity:1; transform:none; } }
    .label-atas { color:var(--merah); font-size:.72rem; font-weight:600; letter-spacing:.08em;
                  text-transform:uppercase; margin-bottom:10px; }
    .kanan h1 { font-size:1.75rem; margin:0 0 8px; }
    .kanan .ket { color:var(--abu); margin:0 0 20px; line-height:1.55; }
    .kanan .ket b { color:var(--teks); }

    .alert { border-radius:10px; padding:10px 14px; margin-bottom:14px; font-size:.9rem; }
    .alert.merah { background:#fdecea; color:#8a1c12; border:1px solid #f5c6c0; }
    .alert.hijau { background:#e8f5ee; color:#14532d; border:1px solid #bfe3cd; }

    label { display:block; font-size:.88rem; font-weight:600; margin:0 0 6px; }
    .field { position:relative; margin-bottom:14px; }
    .field > i { position:absolute; left:14px; top:50%; transform:translateY(-50%); color:var(--abu); }
    .field input { width:100%; padding:12px 44px 12px 42px; font:inherit; background:#f4f5f1;
                   border:1px solid var(--garis); border-radius:10px; outline:none; }
    .field input:focus { border-color:var(--hijau); box-shadow:0 0 0 3px rgba(18,61,53,.12); background:#fff; }
    .field input.kode { font-size:1.2rem; letter-spacing:.35em; font-weight:600; }
    .mata { position:absolute; right:6px; top:50%; transform:translateY(-50%); background:none; border:0;
            padding:8px; cursor:pointer; color:var(--abu); }
    .tombol { width:100%; padding:13px; border:0; border-radius:10px; cursor:pointer; font:inherit;
              font-weight:600; color:#fff; background:var(--hijau); transition:background .2s; margin-top:4px; }
    .tombol:hover { background:var(--hijau-gelap); }
    .tombol:disabled { opacity:.7; cursor:wait; }

    .bawah { margin-top:16px; font-size:.88rem; color:var(--abu); display:flex; justify-content:space-between;
             align-items:center; gap:10px; flex-wrap:wrap; }
    .bawah a, .bawah button { color:var(--hijau); font:inherit; font-weight:600; background:none; border:0;
                              padding:0; cursor:pointer; text-decoration:none; }
    .bawah a:hover, .bawah button:hover { text-decoration:underline; }
    .bawah form { margin:0; display:inline; }

    @media (max-width: 860px) {
      .kartu { grid-template-columns:1fr; }
      .kiri { display:none; }
      .kanan { padding:32px 24px; }
    }
  </style>
</head>
<body>
  <div class="kartu">

    <aside class="kiri">
      <div class="brand">
        <div class="logo"><i class="bi bi-book-half"></i></div>
        <div><small>Sistem Pengelolaan Perpustakaan</small><strong><?= e(APP_NAME) ?></strong></div>
      </div>
      <div>
        <h2>Lupa kata sandi? Tenang aja.</h2>
        <p>Kami kirim kode OTP ke email kamu, lalu kamu bisa buat kata sandi baru dalam hitungan menit.</p>
      </div>
      <div class="catatan"><i class="bi bi-shield-check"></i> Kode hanya bisa dipakai sekali</div>
    </aside>

    <main class="kanan">
      <div class="label-atas"><i class="bi bi-key"></i> Pemulihan Akun</div>

      <?php if ($langkah === 'email'): ?>
        <h1>Lupa Kata Sandi</h1>
        <p class="ket">Masukkan email akun kamu, nanti kami kirim kode OTP 6 digit.</p>

        <?php if ($galat): ?>
          <div class="alert merah" role="alert"><i class="bi bi-exclamation-triangle"></i> <?= e($galat) ?></div>
        <?php endif; ?>

        <form method="post" id="form-kirim">
          <input type="hidden" name="tindakan" value="kirim">
          <label for="email">Email</label>
          <div class="field">
            <i class="bi bi-envelope"></i>
            <input type="email" id="email" name="email" placeholder="nama@email.com"
                   value="<?= e($_POST['email'] ?? '') ?>" required autofocus>
          </div>
          <button type="submit" class="tombol" id="tombol-kirim">Kirim Kode OTP</button>
        </form>

        <div class="bawah">
          <a href="<?= BASE_URL ?>index.php?hal=login"><i class="bi bi-arrow-left"></i> Kembali ke login</a>
        </div>

        <script>
          document.getElementById('form-kirim').addEventListener('submit', () => {
            const t = document.getElementById('tombol-kirim');
            t.disabled = true; t.textContent = 'Mengirim kode...';
          });
        </script>

      <?php else: ?>
        <h1>Masukkan Kode OTP</h1>
        <p class="ket">Kode dikirim ke <b><?= e($email) ?></b>.
          <a href="<?= BASE_URL ?>index.php?hal=login&aksi=lupa&ulang=1" style="color:var(--hijau);font-weight:600">Ganti email</a></p>

        <?php if ($galat): ?>
          <div class="alert merah" role="alert"><i class="bi bi-exclamation-triangle"></i> <?= e($galat) ?></div>
        <?php endif; ?>
        <?php if ($info): ?>
          <div class="alert hijau" role="status"><i class="bi bi-check-circle"></i> <?= e($info) ?></div>
        <?php endif; ?>

        <form method="post" id="form-reset">
          <input type="hidden" name="tindakan" value="reset">

          <label for="kode">Kode OTP</label>
          <div class="field">
            <i class="bi bi-123"></i>
                <input type="text" id="kode" name="kode" class="kode" inputmode="numeric" maxlength="6"
                   autocomplete="one-time-code" placeholder="••••••" required autofocus>
          </div>

          <label for="password">Kata sandi baru</label>
          <div class="field">
            <i class="bi bi-lock"></i>
            <input type="password" id="password" name="password" placeholder="Minimal 8 karakter" required>
            <button type="button" class="mata" id="toggle-pass" aria-label="Tampilkan kata sandi">
              <i class="bi bi-eye" id="ikon-pass"></i>
            </button>
          </div>

          <label for="password2">Ulangi kata sandi baru</label>
          <div class="field">
            <i class="bi bi-lock-fill"></i>
            <input type="password" id="password2" name="password2" placeholder="Ketik ulang kata sandi" required>
          </div>

          <button type="submit" class="tombol" id="tombol-reset">Perbarui Kata Sandi</button>
        </form>

        <div class="bawah">
          <span>Belum menerima kode?
            <form method="post" onsubmit="const b=this.querySelector('button'); b.disabled=true; b.textContent='Mengirim...';">
              <input type="hidden" name="tindakan" value="kirim_ulang">
              <button type="submit">Kirim ulang</button>
            </form>
          </span>
          <a href="<?= BASE_URL ?>index.php?hal=login"><i class="bi bi-arrow-left"></i> Kembali ke login</a>
        </div>

        <script>
          document.getElementById('toggle-pass').addEventListener('click', () => {
            const p = document.getElementById('password');
            const tampil = p.type === 'password';
            p.type = tampil ? 'text' : 'password';
            document.getElementById('ikon-pass').className = tampil ? 'bi bi-eye-slash' : 'bi bi-eye';
          });
          document.getElementById('form-reset').addEventListener('submit', () => {
            const t = document.getElementById('tombol-reset');
            t.disabled = true; t.textContent = 'Memproses...';
          });
        </script>
      <?php endif; ?>
    </main>
  </div>
</body>
</html>