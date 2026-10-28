<?php
// halaman/login/index.php — login via Supabase Auth (JWT)
$galat = '';
$peran = in_array($_POST['peran'] ?? '', ['admin', 'anggota'], true) ? $_POST['peran'] : '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $galat = supabase_login(trim($_POST['email'] ?? ''), $_POST['password'] ?? '');
    if ($galat === null) {
        $_SESSION['peran'] = $peran ?: 'admin';
        redirect('dashboard');
    }
}
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Login | <?= e(APP_NAME) ?></title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" crossorigin="anonymous" />
  <style>
    :root { --hijau:#123d35; --hijau-gelap:#0d2e28; --aksen:#f4a98a; --merah:#b42318;
            --teks:#1c2a26; --abu:#6b7873; --garis:#e1e5e0; }
    * { box-sizing: border-box; }
    [hidden] { display: none !important; }
    body { margin:0; min-height:100vh; display:grid; place-items:center; padding:24px;
           background:#f7f7f3; color:var(--teks);
           font-family: Inter, system-ui, -apple-system, "Segoe UI", sans-serif; }

    .kartu { display:grid; grid-template-columns:1.1fr 1fr; width:min(1080px,100%); min-height:600px;
             background:#fff; border-radius:16px; overflow:hidden;
             box-shadow:0 20px 50px rgba(18,61,53,.15); }

    /* ===== Panel kiri ===== */
    .kiri { background:linear-gradient(160deg,var(--hijau),var(--hijau-gelap)); color:#fff;
            padding:48px; display:flex; flex-direction:column; justify-content:space-between; gap:32px; }
    .brand { display:flex; align-items:center; gap:12px; }
    .brand .logo { width:44px; height:44px; border-radius:10px; background:rgba(255,255,255,.12);
                   display:grid; place-items:center; font-size:1.4rem; }
    .brand small { display:block; color:var(--aksen); font-size:.7rem; letter-spacing:.08em; text-transform:uppercase; }
    .brand strong { font-size:1.15rem; }
    .kiri h2 { font-size:2.1rem; line-height:1.15; margin:0 0 14px; }
    .kiri p { margin:0; color:rgba(255,255,255,.72); line-height:1.6; }
    .fitur { display:grid; gap:10px; }
    .fitur div { display:flex; align-items:center; gap:12px; padding:12px 14px; border-radius:10px;
                 background:rgba(255,255,255,.07); border:1px solid rgba(255,255,255,.1); }
    .fitur i { color:var(--aksen); font-size:1.15rem; }
    .kiri .catatan { font-size:.8rem; color:rgba(255,255,255,.6); display:flex; align-items:center; gap:8px; }

    /* ===== Panel kanan ===== */
    .kanan { padding:48px; display:flex; flex-direction:column; justify-content:center; }
    .label-atas { color:var(--merah); font-size:.72rem; font-weight:600; letter-spacing:.08em;
                  text-transform:uppercase; margin-bottom:10px; }
    .kanan h1 { font-size:1.75rem; margin:0 0 8px; }
    .kanan .ket { color:var(--abu); margin:0 0 24px; line-height:1.55; }

    .tampil { animation: masuk .38s ease both; }
    @keyframes masuk { from { opacity:0; transform:translateX(24px); } to { opacity:1; transform:none; } }

    .pilihan { width:100%; display:flex; align-items:center; gap:16px; text-align:left; cursor:pointer;
               padding:18px; margin-bottom:14px; background:#fff; font:inherit; color:inherit;
               border:1px solid var(--garis); border-radius:12px;
               transition:transform .2s, box-shadow .2s, border-color .2s; }
    .pilihan:hover { transform:translateY(-3px); border-color:var(--hijau); box-shadow:0 8px 20px rgba(18,61,53,.12); }
    .pilihan .ikon { width:48px; height:48px; border-radius:12px; flex:none; display:grid; place-items:center;
                     background:#e8f1ee; color:var(--hijau); font-size:1.4rem; }
    .pilihan b { display:block; margin-bottom:2px; }
    .pilihan span { color:var(--abu); font-size:.88rem; }
    .pilihan .panah { margin-left:auto; color:var(--abu); }

    .kembali { background:none; border:0; padding:0; margin-bottom:18px; cursor:pointer;
               color:var(--abu); font:inherit; font-size:.9rem; }
    .kembali:hover { color:var(--hijau); }

    .alert { background:#fdecea; color:#8a1c12; border:1px solid #f5c6c0; border-radius:10px;
             padding:10px 14px; margin-bottom:16px; font-size:.9rem; }
    label { display:block; font-size:.88rem; font-weight:600; margin:0 0 6px; }
    .field { position:relative; margin-bottom:16px; }
    .field > i { position:absolute; left:14px; top:50%; transform:translateY(-50%); color:var(--abu); }
    .field input { width:100%; padding:12px 44px 12px 42px; font:inherit; background:#f4f5f1;
                   border:1px solid var(--garis); border-radius:10px; outline:none; }
    .field input:focus { border-color:var(--hijau); box-shadow:0 0 0 3px rgba(18,61,53,.12); background:#fff; }
    .mata { position:absolute; right:6px; top:50%; transform:translateY(-50%); background:none; border:0;
            padding:8px; cursor:pointer; color:var(--abu); }
    .tombol { width:100%; padding:13px; border:0; border-radius:10px; cursor:pointer; font:inherit;
              font-weight:600; color:#fff; background:var(--hijau); transition:background .2s; margin-top:6px; }
    .tombol:hover { background:var(--hijau-gelap); }
    .tombol:disabled { opacity:.7; cursor:wait; }

    @media (max-width: 860px) {
      .kartu { grid-template-columns:1fr; }
      .kiri { display:none; }
      .kanan { padding:32px 24px; }
    }
  </style>
</head>
<body>
  <div class="kartu">

    <!-- Panel kiri -->
    <aside class="kiri">
      <div class="brand">
        <div class="logo"><i class="bi bi-book-half"></i></div>
        <div><small>Sistem Pengelolaan Perpustakaan</small><strong><?= e(APP_NAME) ?></strong></div>
      </div>
      <div>
        <h2>Kelola Koleksi &amp; Anggota dengan Mudah</h2>
        <p>Satu tempat untuk mencatat buku, mengelola data anggota, dan mencetak laporan perpustakaan.</p>
      </div>
      <div class="fitur">
        <div><i class="bi bi-book"></i> Data buku</div>
        <div><i class="bi bi-people"></i> Data anggota</div>
        <div><i class="bi bi-printer"></i> Laporan siap cetak</div>
      </div>
      <div class="catatan"><i class="bi bi-shield-check"></i> Login aman dengan Supabase Auth (JWT)</div>
    </aside>

    <!-- Panel kanan -->
    <main class="kanan">

      <!-- Tampilan 1: pilih peran -->
      <section id="pilih" class="tampil">
        <div class="label-atas"><i class="bi bi-shield-lock"></i> Portal Akses</div>
        <h1>Selamat Datang</h1>
        <p class="ket">Pilih jenis akun untuk melanjutkan masuk.</p>

        <button type="button" class="pilihan" data-peran="admin">
          <div class="ikon"><i class="bi bi-person-gear"></i></div>
          <div><b>Login sebagai Admin</b><span>Kelola data buku, anggota, dan laporan</span></div>
          <i class="bi bi-arrow-right panah"></i>
        </button>

        <button type="button" class="pilihan" data-peran="anggota">
          <div class="ikon"><i class="bi bi-person-badge"></i></div>
          <div><b>Login sebagai Anggota Perpus</b><span>Masuk sebagai anggota perpustakaan</span></div>
          <i class="bi bi-arrow-right panah"></i>
        </button>
      </section>

      <!-- Tampilan 2: form login -->
      <section id="form-wrap" hidden>
        <button type="button" class="kembali" id="kembali"><i class="bi bi-arrow-left"></i> Kembali</button>
        <div class="label-atas" id="label-peran"></div>
        <h1 id="judul-form"></h1>
        <p class="ket" id="ket-form"></p>

        <?php if ($galat): ?>
          <div class="alert" role="alert"><i class="bi bi-exclamation-triangle me-1"></i> <?= e($galat) ?></div>
        <?php endif; ?>

        <form method="post" id="form-login">
          <input type="hidden" name="peran" id="peran" value="<?= e($peran) ?>">

          <label for="email">Email</label>
          <div class="field">
            <i class="bi bi-envelope"></i>
            <input type="email" id="email" name="email" placeholder="nama@email.com"
                   value="<?= e($_POST['email'] ?? '') ?>" required>
          </div>

          <label for="password">Kata sandi</label>
          <div class="field">
            <i class="bi bi-lock"></i>
            <input type="password" id="password" name="password" placeholder="Masukkan kata sandi" required>
            <button type="button" class="mata" id="toggle-pass" aria-label="Tampilkan kata sandi">
              <i class="bi bi-eye" id="ikon-pass"></i>
            </button>
          </div>

          <button type="submit" class="tombol" id="tombol-masuk"></button>
        </form>
      </section>

    </main>
  </div>

  <script>
    const info = {
      admin:   { label: 'Portal Admin',   judul: 'Login Admin',
                 ket: 'Masuk ke konsol administrator untuk mengelola data buku, anggota, dan laporan.',
                 tombol: 'Masuk sebagai Admin' },
      anggota: { label: 'Portal Anggota', judul: 'Login Anggota',
                 ket: 'Masuk dengan akun anggota perpustakaan kamu.',
                 tombol: 'Masuk sebagai Anggota' }
    };
    const pilih = document.getElementById('pilih');
    const formWrap = document.getElementById('form-wrap');

    function animasi(el) {
      el.hidden = false;
      el.classList.remove('tampil'); void el.offsetWidth; el.classList.add('tampil');
    }
    function buka(peran) {
      const d = info[peran];
      document.getElementById('peran').value = peran;
      document.getElementById('label-peran').textContent = d.label;
      document.getElementById('judul-form').textContent = d.judul;
      document.getElementById('ket-form').textContent = d.ket;
      document.getElementById('tombol-masuk').textContent = d.tombol;
      pilih.hidden = true;
      animasi(formWrap);
      document.getElementById('email').focus();
    }

    document.querySelectorAll('.pilihan').forEach(b =>
      b.addEventListener('click', () => buka(b.dataset.peran)));
    document.getElementById('kembali').addEventListener('click', () => {
      formWrap.hidden = true;
      animasi(pilih);
    });

    const pass = document.getElementById('password');
    const ikon = document.getElementById('ikon-pass');
    document.getElementById('toggle-pass').addEventListener('click', () => {
      const tampil = pass.type === 'password';
      pass.type = tampil ? 'text' : 'password';
      ikon.className = tampil ? 'bi bi-eye-slash' : 'bi bi-eye';
    });

    document.getElementById('form-login').addEventListener('submit', () => {
      const t = document.getElementById('tombol-masuk');
      t.disabled = true; t.textContent = 'Memproses...';
    });

    <?php if ($peran): ?>buka('<?= $peran ?>');<?php endif; ?>
  </script>
</body>
</html>