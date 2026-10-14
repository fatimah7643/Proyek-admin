<?php
// halaman/buku/print.php
$hasil = $db->query('SELECT * FROM buku ORDER BY judul');
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8" />
  <title>Laporan Buku</title>
  <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/adminlte.css" />
  
  <!-- JAWABAN LATIHAN 9: Menyembunyikan tombol saat dicetak -->
  <style>
    @media print {
      .btn-print {
        display: none !important;
      }
    }
  </style>
</head>
<body onload="window.print()" class="p-4">

  <!-- Tombol pemicu cetak ulang jika dialog cetak tidak sengaja tertutup -->
  <div class="mb-3 text-end btn-print">
    <button onclick="window.print()" class="btn btn-success">🖨️ Cetak Halaman</button>
  </div>

  <h3 class="text-center">Laporan Data Buku</h3>
  <table class="table table-bordered mt-3">
    <thead>
      <tr>
        <th>No</th>
        <th>Judul</th>
        <th>Penulis</th>
        <th>Tahun</th>
      </tr>
    </thead>
    <tbody>
    <?php $no = 1; while ($b = $hasil->fetch_assoc()): ?>
      <tr>
        <td><?= $no++ ?></td>
        <td><?= e($b['judul']) ?></td>
        <td><?= e($b['penulis']) ?></td>
        <td><?= e($b['tahun']) ?></td>
      </tr>
    <?php endwhile; ?>
    </tbody>
  </table>
</body>
</html>