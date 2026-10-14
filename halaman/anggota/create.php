<?php
// halaman/anggota/create.php
$judulHalaman = 'Tambah Anggota';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $db->prepare('INSERT INTO anggota (nama, email) VALUES (?, ?)');
    $stmt->bind_param('ss', $_POST['nama'], $_POST['email']);
    $stmt->execute();
    redirect('anggota');
}
?>
<main class="app-main">
  <div class="app-content-header"><div class="container-fluid">
    <div class="row">
      <div class="col-sm-6"><h1 class="mb-0 fs-3">Tambah Anggota</h1></div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end">
          <li class="breadcrumb-item"><a href="<?= BASE_URL ?>">Home</a></li>
          <li class="breadcrumb-item"><a href="<?= BASE_URL ?>index.php?hal=anggota">Data Anggota</a></li>
          <li class="breadcrumb-item active">Tambah</li>
        </ol>
      </div>
    </div>
  </div></div>
  <div class="app-content"><div class="container-fluid">
    <div class="card"><form method="post">
      <div class="card-body">
        <div class="mb-3"><label class="form-label">Nama</label>
          <input name="nama" class="form-control" maxlength="100" required></div>
        <div class="mb-3"><label class="form-label">Email</label>
          <input name="email" type="email" class="form-control" maxlength="100" required></div>
      </div>
      <div class="card-footer">
        <button class="btn btn-primary">Simpan</button>
        <a href="<?= BASE_URL ?>index.php?hal=anggota" class="btn btn-link">Batal</a>
      </div>
    </form></div>
  </div></div>
</main>