<?php
// halaman/anggota/update.php
$judulHalaman = 'Edit Anggota';
$id = (int)($_GET['id'] ?? 0);

$stmt = $db->prepare('SELECT * FROM anggota WHERE id = ?');
$stmt->bind_param('i', $id);
$stmt->execute();
$anggota = $stmt->get_result()->fetch_assoc();
if (!$anggota) redirect('anggota');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $db->prepare('UPDATE anggota SET nama=?, email=? WHERE id=?');
    $stmt->bind_param('ssi', $_POST['nama'], $_POST['email'], $id);
    $stmt->execute();
    redirect('anggota');
}
?>
<main class="app-main">
  <div class="app-content-header"><div class="container-fluid">
    <div class="row">
      <div class="col-sm-6"><h1 class="mb-0 fs-3">Edit Anggota</h1></div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end">
          <li class="breadcrumb-item"><a href="<?= BASE_URL ?>">Home</a></li>
          <li class="breadcrumb-item"><a href="<?= BASE_URL ?>index.php?hal=anggota">Data Anggota</a></li>
          <li class="breadcrumb-item active">Edit</li>
        </ol>
      </div>
    </div>
  </div></div>
  <div class="app-content"><div class="container-fluid">
    <div class="card"><form method="post">
      <div class="card-body">
        <div class="mb-3"><label class="form-label">Nama</label>
          <input name="nama" class="form-control" maxlength="100" value="<?= e($anggota['nama']) ?>" required></div>
        <div class="mb-3"><label class="form-label">Email</label>
          <input name="email" type="email" class="form-control" maxlength="100" value="<?= e($anggota['email']) ?>" required></div>
      </div>
      <div class="card-footer">
        <button class="btn btn-primary">Simpan Perubahan</button>
        <a href="<?= BASE_URL ?>index.php?hal=anggota" class="btn btn-link">Batal</a>
      </div>
    </form></div>
  </div></div>
</main>