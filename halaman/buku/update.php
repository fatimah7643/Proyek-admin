<?php
// halaman/buku/update.php
$judulHalaman = 'Edit Buku';
$id = (int)($_GET['id'] ?? 0);

$stmt = $db->prepare('SELECT * FROM buku WHERE id = ?');
$stmt->bind_param('i', $id);
$stmt->execute();
$buku = $stmt->get_result()->fetch_assoc();
if (!$buku) redirect('buku');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $db->prepare('UPDATE buku SET judul=?, penulis=?, tahun=? WHERE id=?');
    $stmt->bind_param('ssii', $_POST['judul'], $_POST['penulis'], $_POST['tahun'], $id);
    $stmt->execute();
    redirect('buku');
}
?>
<main class="app-main">
  <div class="app-content-header"><div class="container-fluid">
    <div class="row">
      <div class="col-sm-6"><h1 class="mb-0 fs-3">Edit Buku</h1></div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end">
          <li class="breadcrumb-item"><a href="<?= BASE_URL ?>">Home</a></li>
          <li class="breadcrumb-item"><a href="<?= BASE_URL ?>index.php?hal=buku">Data Buku</a></li>
          <li class="breadcrumb-item active">Edit</li>
        </ol>
      </div>
    </div>
  </div></div>
  <div class="app-content"><div class="container-fluid">
    <div class="card"><form method="post">
      <div class="card-body">
        <div class="mb-3"><label class="form-label">Judul</label>
          <input name="judul" class="form-control" maxlength="150" value="<?= e($buku['judul']) ?>" required></div>
        <div class="mb-3"><label class="form-label">Penulis</label>
          <input name="penulis" class="form-control" maxlength="100" value="<?= e($buku['penulis']) ?>" required></div>
        <div class="mb-3"><label class="form-label">Tahun</label>
          <input name="tahun" type="number" min="1901" max="2155" class="form-control" value="<?= e($buku['tahun']) ?>" required></div>
      </div>
      <div class="card-footer">
        <button class="btn btn-primary">Simpan Perubahan</button>
        <a href="<?= BASE_URL ?>index.php?hal=buku" class="btn btn-link">Batal</a>
      </div>
    </form></div>
  </div></div>
</main>