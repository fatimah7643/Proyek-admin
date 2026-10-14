<?php

$judulHalaman = 'Dashboard';

// 1. Ambil total jumlah buku
$totalBuku = $db->query('SELECT COUNT(*) FROM buku')->fetch_row()[0];

// 2. Ambil data 1 buku terbaru berdasarkan ID terbesar
$ambilBuku = $db->query('SELECT judul, penulis FROM buku ORDER BY id DESC LIMIT 1');
$bukuTerbaru = $ambilBuku->fetch_assoc();
?>

<main class="app-main">
  <!-- begin::App Content Header -->
  <div class="app-content-header">
    <div class="container-fluid">
      <div class="row">
        <div class="col-sm-6"><h1 class="mb-0 fs-3">Dashboard</h1></div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-end">
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>">Home</a></li>
            <li class="breadcrumb-item active">Dashboard</li>
          </ol>
        </div>
      </div>
    </div>
  </div>
  <!-- end::App Content Header -->

  <!-- begin::App Content -->
  <div class="app-content">
    <div class="container-fluid">
      <!-- Menggunakan baris pembagi (row) agar kedua kartu sejajar ke samping -->
      <div class="row">
        
        <!-- Kartu Kiri: Ringkasan Total Buku -->
        <div class="col-md-6">
          <div class="card mb-4">
            <div class="card-header">
              <h3 class="card-title">Ringkasan</h3>
            </div>
            <div class="card-body">
              Total buku: <strong><?= (int)$totalBuku ?></strong>
            </div>
          </div>
        </div>

        <!-- Kartu Kanan: Tampilan Buku Terbaru (Jawaban Latihan 7) -->
        <div class="col-md-6">
          <div class="card mb-4">
            <div class="card-header">
              <h3 class="card-title">Buku Terbaru</h3>
            </div>
            <div class="card-body">
              <?php if ($bukuTerbaru): ?>
                Judul: <strong><?= e($bukuTerbaru['judul']) ?></strong> <br>
                Penulis: <span class="text-muted"><?= e($bukuTerbaru['penulis']) ?></span>
              <?php else: ?>
                <span class="text-muted">Belum ada data buku.</span>
              <?php endif; ?>
            </div>
          </div>
        </div>

      </div>
    </div>
  </div>
  <!-- end::App Content -->
</main>