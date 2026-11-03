<?php
// halaman/login/index.php — login / daftar via Supabase Auth (JWT)
$galat = $_SESSION['galat_login'] ?? '';
unset($_SESSION['galat_login']);
$info  = '';
$peran = in_array($_POST['peran'] ?? '', ['admin', 'anggota'], true) ? $_POST['peran'] : '';
$mode  = ($_POST['mode'] ?? '') === 'daftar' ? 'daftar' : 'masuk';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $pass  = $_POST['password'] ?? '';

    if ($mode === 'daftar' && $peran === 'anggota') {
        if (strlen($pass) < 8) {
            $galat = 'Kata sandi minimal 8 karakter.';
        } elseif ($pass !== ($_POST['password2'] ?? '')) {
            $galat = 'Konfirmasi kata sandi tidak sama.';
        } else {
            [$galat, $butuhKonfirmasi] = supabase_daftar(trim($_POST['nama'] ?? ''), $email, $pass);
            if ($galat === null && !$butuhKonfirmasi) {
                redirect(halaman_awal());
            }
            if ($galat === null) {
                $galat = '';
                $info  = 'Pendaftaran berhasil. Cek email kamu untuk konfirmasi, lalu masuk.';
                $mode  = 'masuk';
            }
        }
    } else {
        $galat = supabase_login($email, $pass);
        if ($galat === null) {
            if ($peran === 'admin' && peran_user() !== 'admin') {
                supabase_request('POST', '/auth/v1/logout', null, $_SESSION['access_token']);
                $_SESSION = [];
                $galat = 'Akun ini bukan admin.';
            } else {
                redirect(halaman_awal());
            }
        }
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
    .kartu { display:grid; grid-template-columns:1.1fr 1fr; width:min(1080px,100%); min-height:620px;
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
    .fitur { display:grid; gap:10px; }
    .fitur div { display:flex; align-items:center; gap:12px; padding:12px 14px; border-radius:10px;
                 background:rgba(255,255,255,.07); border:1px solid rgba(255,255,255,.1); }
    .fitur i { color:var(--aksen); font-size:1.15rem; }
    .kiri .catatan { font-size:.8rem; color:rgba(255,255,255,.6); display:flex; align-items:center; gap:8px; }

    .kanan { padding:44px 48px; display:flex; flex-direction:column; justify-content:center; }
    .label-atas { color:var(--merah); font-size:.72rem; font-weight:600; letter-spacing:.08em;
                  text-transform:uppercase; margin-bottom:10px; }
    .kanan h1 { font-size:1.75rem; margin:0 0 8px; }
    .kanan .ket { color:var(--abu); margin:0 0 20px; line-height:1.55; }

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

    .kembali { background:none; border:0; padding:0; margin-bottom:16px; cursor:pointer;
               color:var(--abu); font:inherit; font-size:.9rem; }
    .kembali:hover { color:var(--hijau); }

    .tab { display:flex; gap:6px; padding:4px; background:#f0f1ed; border-radius:10px; margin-bottom:18px; }
    .tab button { flex:1; padding:9px; border:0; border-radius:8px; background:none; cursor:pointer;
                  font:inherit; font-weight:600; color:var(--abu); }
    .tab button.aktif { background:#fff; color:var(--hijau); box-shadow:0 1px 4px rgba(0,0,0,.1); }

    .sosial { display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-bottom:14px; }
    .sosial a { display:flex; align-items:center; justify-content:center; gap:8px; padding:11px;
                border:1px solid var(--garis); border-radius:10px; text-decoration:none; color:var(--teks);
                font-weight:600; font-size:.92rem; transition:background .2s, border-color .2s; }
    .sosial a:hover { background:#f4f5f1; border-color:var(--hijau); }
    .atau { display:flex; align-items:center; gap:10px; color:var(--abu); font-size:.78rem;
            text-transform:uppercase; letter-spacing:.06em; margin-bottom:14px; }
    .atau::before, .atau::after { content:""; flex:1; height:1px; background:var(--garis); }

    .alert { border-radius:10px; padding:10px 14px; margin-bottom:14px; font-size:.9rem; }
    .alert.merah { background:#fdecea; color:#8a1c12; border:1px solid #f5c6c0; }
    .alert.hijau { background:#e8f5ee; color:#14532d; border:1px solid #bfe3cd; }

    label { display:block; font-size:.88rem; font-weight:600; margin:0 0 6px; }
    .field { position:relative; margin-bottom:14px; }
    .field > i { position:absolute; left:14px; top:50%; transform:translateY(-50%); color:var(--abu); }
    .field input { width:100%; padding:12px 44px 12px 42px; font:inherit; background:#f4f5f1;
                   border:1px solid var(--garis); border-radius:10px; outline:none; }
    .field input:focus { border-color:var(--hijau); box-shadow:0 0 0 3px rgba(18,61,53,.12); background:#fff; }
    .mata { position:absolute; right:6px; top:50%; transform:translateY(-50%); background:none; border:0;
            padding:8px; cursor:pointer; color:var(--abu); }
    .tombol { width:100%; padding:13px; border:0; border-radius:10px; cursor:pointer; font:inherit;
              font-weight:600; color:#fff; background:var(--hijau); transition:background .2s; margin-top:4px; }
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

    <main class="kanan">

      <!-- Tampilan 1: pilih peran -->
      <section id="pilih" class="tampil">
        <div class="label-atas"><i class="bi bi-shield-lock"></i> Portal Akses</div>
        <h1>Selamat Datang</h1>
        <p class="ket">Pilih jenis akun untuk melanjutkan.</p>

        <button type="button" class="pilihan" data-pilih="admin">
          <div class="ikon"><i class="bi bi-person-gear"></i></div>
          <div><b>Login sebagai Admin</b><span>Kelola data buku, anggota, dan laporan</span></div>
          <i class="bi bi-arrow-right panah"></i>
        </button>

        <button type="button" class="pilihan" data-pilih="anggota">
          <div class="ikon"><i class="bi bi-person-badge"></i></div>
          <div><b>Login sebagai Anggota Perpus</b><span>Masuk atau daftar sebagai anggota</span></div>
          <i class="bi bi-arrow-right panah"></i>
        </button>
      </section>

      <!-- Tampilan 2: form -->
      <section id="form-wrap" hidden>
        <button type="button" class="kembali" id="kembali"><i class="bi bi-arrow-left"></i> Kembali</button>
        <div class="label-atas" id="label-peran"></div>
        <h1 id="judul-form"></h1>
        <p class="ket" id="ket-form"></p>

        <div class="tab" data-peran="anggota">
          <button type="button" data-tab="masuk">Masuk</button>
          <button type="button" data-tab="daftar">Daftar</button>
        </div>

        <?php if ($galat): ?>
          <div class="alert merah" role="alert"><i class="bi bi-exclamation-triangle"></i> <?= e($galat) ?></div>
        <?php endif; ?>
        <?php if ($info): ?>
          <div class="alert hijau" role="status"><i class="bi bi-check-circle"></i> <?= e($info) ?></div>
        <?php endif; ?>

        <div data-peran="anggota">
          <div class="sosial">
            <a href="<?= BASE_URL ?>index.php?hal=login&aksi=oauth&provider=google"><i class="bi bi-google"></i> Google</a>
            <a href="<?= BASE_URL ?>index.php?hal=login&aksi=oauth&provider=github"><i class="bi bi-github"></i> GitHub</a>
          </div>
          <div class="atau">atau dengan email</div>
        </div>

        <form method="post" id="form-login">
          <input type="hidden" name="peran" id="peran" value="<?= e($peran) ?>">
          <input type="hidden" name="mode" id="mode" value="<?= e($mode) ?>">

          <div data-peran="anggota" data-mode="daftar">
            <label for="nama">Nama lengkap</label>
            <div class="field">
              <i class="bi bi-person"></i>
              <input type="text" id="nama" name="nama" placeholder="Nama kamu" maxlength="100"
                     value="<?= e($_POST['nama'] ?? '') ?>" required>
            </div>
          </div>

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

          <div data-peran="anggota" data-mode="daftar">
            <label for="password2">Ulangi kata sandi</label>
            <div class="field">
              <i class="bi bi-lock-fill"></i>
              <input type="password" id="password2" name="password2" placeholder="Ketik ulang kata sandi" required>
            </div>
          </div>

          <button type="submit" class="tombol" id="tombol-masuk"></button>
        </form>
      </section>

    </main>
  </div>

  <script>
    const state = { peran: '<?= e($peran) ?>', mode: '<?= e($mode) ?>' };
    const teks = {
      admin:   { label: 'Portal Admin',   judul: 'Login Admin',
                 ket: 'Masuk ke konsol administrator untuk mengelola data perpustakaan.' },
      anggota: { masuk:  { judul: 'Masuk Anggota',  ket: 'Masuk dengan akun anggota perpustakaan kamu.' },
                 daftar: { judul: 'Daftar Anggota', ket: 'Buat akun baru untuk menjadi anggota perpustakaan.' } }
    };
    const $ = id => document.getElementById(id);

    function animasi(el) {
      el.hidden = false;
      el.classList.remove('tampil'); void el.offsetWidth; el.classList.add('tampil');
    }

    function render() {
      const { peran, mode } = state;
      $('peran').value = peran;
      $('mode').value = mode;

      // tampilkan elemen sesuai peran/mode; input di elemen tersembunyi dimatikan
      document.querySelectorAll('[data-peran], [data-mode]').forEach(el => {
        const ok = (!el.dataset.peran || el.dataset.peran === peran) &&
                   (!el.dataset.mode  || el.dataset.mode  === mode);
        el.hidden = !ok;
        el.querySelectorAll('input').forEach(i => i.disabled = !ok);
      });
      document.querySelectorAll('[data-tab]').forEach(b =>
        b.classList.toggle('aktif', b.dataset.tab === mode));

      if (peran === 'admin') {
        $('label-peran').textContent = teks.admin.label;
        $('judul-form').textContent  = teks.admin.judul;
        $('ket-form').textContent    = teks.admin.ket;
        $('tombol-masuk').textContent = 'Masuk sebagai Admin';
      } else {
        $('label-peran').textContent = 'Portal Anggota';
        $('judul-form').textContent  = teks.anggota[mode].judul;
        $('ket-form').textContent    = teks.anggota[mode].ket;
        $('tombol-masuk').textContent = mode === 'daftar' ? 'Daftar Sekarang' : 'Masuk';
      }
    }

    function buka(peran, mode = 'masuk') {
      state.peran = peran; state.mode = mode;
      $('pilih').hidden = true;
      render();
      animasi($('form-wrap'));
    }

    document.querySelectorAll('[data-pilih]').forEach(b =>
      b.addEventListener('click', () => buka(b.dataset.pilih)));
    document.querySelectorAll('[data-tab]').forEach(b =>
      b.addEventListener('click', () => { state.mode = b.dataset.tab; render(); }));
    $('kembali').addEventListener('click', () => {
      $('form-wrap').hidden = true;
      animasi($('pilih'));
    });

    $('toggle-pass').addEventListener('click', () => {
      const tampil = $('password').type === 'password';
      $('password').type = tampil ? 'text' : 'password';
      $('ikon-pass').className = tampil ? 'bi bi-eye-slash' : 'bi bi-eye';
    });

    $('form-login').addEventListener('submit', () => {
      const t = $('tombol-masuk');
      t.disabled = true; t.textContent = 'Memproses...';
    });

    <?php if ($peran || $galat || $info): ?>
    buka('<?= $peran ?: 'anggota' ?>', '<?= e($mode) ?>');
    <?php endif; ?>
  </script>
</body>
</html>