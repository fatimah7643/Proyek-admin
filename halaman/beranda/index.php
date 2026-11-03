<?php
// halaman/beranda/index.php — halaman anggota perpustakaan (hanya baca)

// Nama & foto dari metadata JWT (Google/GitHub mengisi name/avatar_url, form daftar mengisi nama)
$m    = jwt_payload($_SESSION['access_token'] ?? '')['user_metadata'] ?? [];
$nama = (string) ($m['nama'] ?? $m['full_name'] ?? $m['name'] ?? $m['user_name'] ?? $_SESSION['user']);
$foto = (string) ($m['avatar_url'] ?? $m['picture'] ?? '');
if (!str_starts_with($foto, 'https://')) {
    $foto = '';
}
$inisial = mb_strtoupper(mb_substr($nama, 0, 1));

// Ringkasan
$ringkas = $db->query(
    'SELECT COUNT(*) AS total, COUNT(DISTINCT penulis) AS penulis, MAX(tahun) AS terbaru FROM buku'
)->fetch_assoc();

// Pencarian + urutan (urutan dibatasi whitelist)
$q     = trim($_GET['q'] ?? '');
$urut  = ($_GET['urut'] ?? 'judul') === 'terbaru' ? 'terbaru' : 'judul';
$order = $urut === 'terbaru' ? 'tahun DESC, judul ASC' : 'judul ASC';

if ($q !== '') {
    $like = '%' . $q . '%';
    $stmt = $db->prepare("SELECT judul, penulis, tahun FROM buku WHERE judul LIKE ? OR penulis LIKE ? ORDER BY $order");
    $stmt->bind_param('ss', $like, $like);
    $stmt->execute();
    $hasil = $stmt->get_result();
} else {
    $hasil = $db->query("SELECT judul, penulis, tahun FROM buku ORDER BY $order");
}
$jumlah = $hasil->num_rows;
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Beranda | <?= e(APP_NAME) ?></title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" crossorigin="anonymous" />
  <style>
    :root { --hijau:#123d35; --hijau-gelap:#0d2e28; --aksen:#f4a98a;
            --teks:#1c2a26; --abu:#6b7873; --garis:#e1e5e0; }
    * { box-sizing: border-box; }
    body { margin:0; background:#f7f7f3; color:var(--teks);
           font-family: Inter, system-ui, -apple-system, "Segoe UI", sans-serif; }
    a { color:inherit; }

    .navbar { background:var(--hijau); color:#fff; position:sticky; top:0; z-index:10; }
    .navbar .isi { max-width:1100px; margin:0 auto; padding:12px 24px; display:flex; align-items:center; gap:14px; }
    .brand { display:flex; align-items:center; gap:10px; font-weight:700; font-size:1.05rem; }
    .brand i { color:var(--aksen); font-size:1.3rem; }
    .spacer { flex:1; }
    .profil { display:flex; align-items:center; gap:10px; }
    .avatar { width:36px; height:36px; border-radius:50%; object-fit:cover; background:var(--aksen); color:var(--hijau);
              display:grid; place-items:center; font-weight:700; }
    .profil .nama { font-size:.9rem; line-height:1.2; max-width:200px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
    .profil small { display:block; color:rgba(255,255,255,.6); font-size:.72rem; }
    .keluar { text-decoration:none; font-size:.88rem; padding:8px 14px; border-radius:8px;
              border:1px solid rgba(255,255,255,.35); transition:background .2s; }
    .keluar:hover { background:rgba(255,255,255,.12); }

    .wrap { max-width:1100px; margin:0 auto; padding:28px 24px 48px; }
    .sapaan h1 { font-size:1.7rem; margin:0 0 4px; }
    .sapaan p { margin:0; color:var(--abu); }

    .ringkas { display:grid; grid-template-columns:repeat(3,1fr); gap:14px; margin:22px 0 28px; }
    .kartu-stat { background:#fff; border:1px solid var(--garis); border-radius:14px; padding:18px;
                  display:flex; align-items:center; gap:14px; }
    .kartu-stat .ikon { width:46px; height:46px; border-radius:12px; background:#e8f1ee; color:var(--hijau);
                        display:grid; place-items:center; font-size:1.3rem; flex:none; }
    .kartu-stat b { display:block; font-size:1.5rem; line-height:1.1; }
    .kartu-stat span { color:var(--abu); font-size:.85rem; }

    .bar { display:flex; gap:10px; flex-wrap:wrap; align-items:center; margin-bottom:6px; }
    .bar h2 { margin:0; font-size:1.2rem; flex:1; min-width:140px; }
    .cari { position:relative; flex:1 1 260px; max-width:380px; }
    .cari i { position:absolute; left:13px; top:50%; transform:translateY(-50%); color:var(--abu); }
    .cari input { width:100%; padding:10px 14px 10px 38px; font:inherit; border:1px solid var(--garis);
                  border-radius:10px; background:#fff; outline:none; }
    .cari input:focus, .bar select:focus { border-color:var(--hijau); box-shadow:0 0 0 3px rgba(18,61,53,.12); }
    .bar select { padding:10px 12px; font:inherit; border:1px solid var(--garis); border-radius:10px; background:#fff; outline:none; }
    .bar button { padding:10px 16px; font:inherit; font-weight:600; color:#fff; background:var(--hijau);
                  border:0; border-radius:10px; cursor:pointer; }
    .bar button:hover { background:var(--hijau-gelap); }
    .info { color:var(--abu); font-size:.88rem; margin:10px 0 16px; }

    .grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(190px,1fr)); gap:16px; }
    .buku { background:#fff; border:1px solid var(--garis); border-radius:14px; overflow:hidden;
            transition:transform .2s, box-shadow .2s; }
    .buku:hover { transform:translateY(-4px); box-shadow:0 10px 24px rgba(18,61,53,.12); }
    .sampul { height:130px; display:grid; place-items:center; color:rgba(255,255,255,.92);
              font-size:3rem; font-weight:700; }
    .buku .teks { padding:14px; }
    .buku h3 { margin:0 0 4px; font-size:.98rem; line-height:1.3; }
    .buku .penulis { color:var(--abu); font-size:.86rem; margin-bottom:10px; }
    .tahun { display:inline-block; font-size:.76rem; font-weight:600; color:var(--hijau);
             background:#e8f1ee; padding:3px 10px; border-radius:20px; }

    .kosong { text-align:center; padding:56px 20px; background:#fff; border:1px dashed var(--garis);
              border-radius:14px; color:var(--abu); }
    .kosong i { font-size:2.4rem; display:block; margin-bottom:8px; }

    footer { text-align:center; color:var(--abu); font-size:.8rem; padding:0 24px 28px; }

    @media (max-width: 640px) {
      .ringkas { grid-template-columns:1fr; }
      .profil .nama { display:none; }
    }
  </style>
</head>
<body>

  <header class="navbar">
    <div class="isi">
      <div class="brand"><i class="bi bi-book-half"></i> <?= e(APP_NAME) ?></div>
      <div class="spacer"></div>
      <div class="profil">
        <?php if ($foto): ?>
          <img class="avatar" src="<?= e($foto) ?>" alt="Foto profil" referrerpolicy="no-referrer">
        <?php else: ?>
          <div class="avatar"><?= e($inisial) ?></div>
        <?php endif; ?>
        <div class="nama"><?= e($nama) ?><small>Anggota</small></div>
      </div>
      <a class="keluar" href="<?= BASE_URL ?>index.php?hal=login&aksi=logout"><i class="bi bi-box-arrow-right"></i> Keluar</a>
    </div>
  </header>

  <main class="wrap">
    <div class="sapaan">
      <h1>Halo, <?= e(strtok($nama, ' @')) ?> 👋</h1>
      <p>Jelajahi koleksi buku perpustakaan kami.</p>
    </div>

    <div class="ringkas">
      <div class="kartu-stat">
        <div class="ikon"><i class="bi bi-book"></i></div>
        <div><b><?= (int) $ringkas['total'] ?></b><span>Total buku</span></div>
      </div>
      <div class="kartu-stat">
        <div class="ikon"><i class="bi bi-person-lines-fill"></i></div>
        <div><b><?= (int) $ringkas['penulis'] ?></b><span>Penulis</span></div>
      </div>
      <div class="kartu-stat">
        <div class="ikon"><i class="bi bi-calendar-check"></i></div>
        <div><b><?= e((string) ($ringkas['terbaru'] ?? '-')) ?></b><span>Tahun terbit terbaru</span></div>
      </div>
    </div>

    <form method="get" class="bar">
      <input type="hidden" name="hal" value="beranda">
      <h2>Katalog Buku</h2>
      <div class="cari">
        <i class="bi bi-search"></i>
        <input type="search" name="q" value="<?= e($q) ?>" placeholder="Cari judul atau penulis...">
      </div>
      <select name="urut" aria-label="Urutkan">
        <option value="judul"   <?= $urut === 'judul'   ? 'selected' : '' ?>>Judul A-Z</option>
        <option value="terbaru" <?= $urut === 'terbaru' ? 'selected' : '' ?>>Terbaru</option>
      </select>
      <button type="submit">Cari</button>
    </form>

    <p class="info">
      <?= $q !== '' ? "Hasil untuk &ldquo;" . e($q) . "&rdquo;: " : 'Menampilkan ' ?><?= (int) $jumlah ?> buku
      <?php if ($q !== ''): ?> · <a href="<?= BASE_URL ?>index.php?hal=beranda">Hapus pencarian</a><?php endif; ?>
    </p>

    <?php if ($jumlah === 0): ?>
      <div class="kosong">
        <i class="bi bi-journal-x"></i>
        Buku tidak ditemukan. Coba kata kunci lain.
      </div>
    <?php else: ?>
      <div class="grid">
        <?php while ($b = $hasil->fetch_assoc()):
            $hue = crc32($b['judul']) % 360; ?>
          <article class="buku">
            <div class="sampul" style="background:linear-gradient(135deg,hsl(<?= $hue ?>,38%,32%),hsl(<?= ($hue + 40) % 360 ?>,40%,22%))">
              <?= e(mb_strtoupper(mb_substr($b['judul'], 0, 1))) ?>
            </div>
            <div class="teks">
              <h3><?= e($b['judul']) ?></h3>
              <div class="penulis"><?= e($b['penulis']) ?></div>
              <span class="tahun"><?= e((string) $b['tahun']) ?></span>
            </div>
          </article>
        <?php endwhile; ?>
      </div>
    <?php endif; ?>
  </main>

  <footer>&copy; <?= date('Y') ?> <?= e(APP_NAME) ?></footer>
</body>
</html>