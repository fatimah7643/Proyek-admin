<?php
// halaman/anggota/index.php
$judulHalaman = 'Data Anggota';

if (isset($_GET['hapus'])) {              // aksi hapus lewat POST di praktik nyata
    $stmt = $db->prepare('DELETE FROM anggota WHERE id = ?');
    $stmt->bind_param('i', $_GET['hapus']);
    $stmt->execute();
    redirect('anggota');
}
$hasil = $db->query('SELECT * FROM anggota ORDER BY id DESC');
?>
<main class="app-main">
  <div class="app-content-header"><div class="container-fluid">
    <div class="row">
      <div class="col-sm-6"><h1 class="mb-0 fs-3">Data Anggota</h1></div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end">
          <li class="breadcrumb-item"><a href="<?= BASE_URL ?>">Home</a></li>
          <li class="breadcrumb-item active">Data Anggota</li>
        </ol>
      </div>
    </div>
  </div></div>
  <div class="app-content"><div class="container-fluid">
    <div class="card">
      <div class="card-header">
        <a href="<?= BASE_URL ?>index.php?hal=anggota&aksi=create" class="btn btn-primary btn-sm">+ Tambah</a>
      </div>
      <div class="card-body p-0">
        <table class="table table-striped mb-0">
          <thead><tr><th>No</th><th>Nama</th><th>Email</th><th>Aksi</th></tr></thead>
          <tbody>
          <?php $no = 1; while ($a = $hasil->fetch_assoc()): ?>
            <tr>
              <td><?= $no++ ?></td>
              <td><?= e($a['nama']) ?></td>
              <td><?= e($a['email']) ?></td>
              <td>
                <a href="<?= BASE_URL ?>index.php?hal=anggota&aksi=update&id=<?= (int)$a['id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                <a href="<?= BASE_URL ?>index.php?hal=anggota&hapus=<?= (int)$a['id'] ?>"
                   onclick="return confirm('Hapus anggota ini?')" class="btn btn-danger btn-sm">Hapus</a>
              </td>
            </tr>
          <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div></div>
</main>