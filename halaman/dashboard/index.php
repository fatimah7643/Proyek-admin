<?php
// halaman/dashboard/index.php
$judulHalaman = 'Dashboard';

// Kartu ringkasan
$totalBuku    = $db->query('SELECT COUNT(*) FROM buku')->fetch_row()[0];
$totalAnggota = $db->query('SELECT COUNT(*) FROM anggota')->fetch_row()[0];
$totalPenulis = $db->query('SELECT COUNT(DISTINCT penulis) FROM buku')->fetch_row()[0];
$bukuTahunIni = $db->query('SELECT COUNT(*) FROM buku WHERE tahun = YEAR(CURDATE())')->fetch_row()[0];

// Data grafik: jumlah buku per tahun terbit
$labels = [];
$jumlah = [];
$grafik = $db->query('SELECT tahun, COUNT(*) AS jml FROM buku GROUP BY tahun ORDER BY tahun');
while ($g = $grafik->fetch_assoc()) {
    $labels[] = (string)$g['tahun'];
    $jumlah[] = (int)$g['jml'];
}

// 5 buku terbaru
$terbaru = $db->query('SELECT judul, penulis, tahun FROM buku ORDER BY id DESC LIMIT 5');
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

      <!-- Baris 1: kartu ringkasan -->
      <div class="row">
        <div class="col-lg-3 col-6">
          <div class="small-box text-bg-primary">
            <div class="inner">
              <h3><?= (int)$totalBuku ?></h3>
              <p>Total Buku</p>
            </div>
            <i class="bi bi-book small-box-icon"></i>
            <a href="<?= BASE_URL ?>index.php?hal=buku"
               class="small-box-footer link-light link-underline-opacity-0 link-underline-opacity-50-hover">
              Lihat data <i class="bi bi-link-45deg"></i>
            </a>
          </div>
        </div>

        <div class="col-lg-3 col-6">
          <div class="small-box text-bg-success">
            <div class="inner">
              <h3><?= (int)$totalAnggota ?></h3>
              <p>Total Anggota</p>
            </div>
            <i class="bi bi-people small-box-icon"></i>
            <a href="<?= BASE_URL ?>index.php?hal=anggota"
               class="small-box-footer link-light link-underline-opacity-0 link-underline-opacity-50-hover">
              Lihat data <i class="bi bi-link-45deg"></i>
            </a>
          </div>
        </div>

        <div class="col-lg-3 col-6">
          <div class="small-box text-bg-warning">
            <div class="inner">
              <h3><?= (int)$totalPenulis ?></h3>
              <p>Penulis</p>
            </div>
            <i class="bi bi-person-lines-fill small-box-icon"></i>
            <a href="<?= BASE_URL ?>index.php?hal=buku"
               class="small-box-footer link-dark link-underline-opacity-0 link-underline-opacity-50-hover">
              Lihat data <i class="bi bi-link-45deg"></i>
            </a>
          </div>
        </div>

        <div class="col-lg-3 col-6">
          <div class="small-box text-bg-danger">
            <div class="inner">
              <h3><?= (int)$bukuTahunIni ?></h3>
              <p>Buku Terbit Tahun Ini</p>
            </div>
            <i class="bi bi-calendar-check small-box-icon"></i>
            <a href="<?= BASE_URL ?>index.php?hal=buku"
               class="small-box-footer link-light link-underline-opacity-0 link-underline-opacity-50-hover">
              Lihat data <i class="bi bi-link-45deg"></i>
            </a>
          </div>
        </div>
      </div>

      <!-- Baris 2: grafik dan buku terbaru -->
      <div class="row">
        <div class="col-lg-7 mb-4">
          <div class="card h-100">
            <div class="card-header"><h3 class="card-title">Buku per Tahun Terbit</h3></div>
            <div class="card-body">
              <?php if ($labels): ?>
                <div id="grafik-buku"></div>
              <?php else: ?>
                <span class="text-muted">Belum ada data untuk ditampilkan.</span>
              <?php endif; ?>
            </div>
          </div>
        </div>

        <div class="col-lg-5 mb-4">
          <div class="card h-100">
            <div class="card-header"><h3 class="card-title">Buku Terbaru</h3></div>
            <div class="card-body p-0">
              <table class="table table-striped mb-0">
                <thead><tr><th>Judul</th><th>Penulis</th><th>Tahun</th></tr></thead>
                <tbody>
                <?php if ($terbaru->num_rows === 0): ?>
                  <tr><td colspan="3" class="text-muted">Belum ada data buku.</td></tr>
                <?php else: while ($b = $terbaru->fetch_assoc()): ?>
                  <tr>
                    <td><?= e($b['judul']) ?></td>
                    <td><?= e($b['penulis']) ?></td>
                    <td><?= e($b['tahun']) ?></td>
                  </tr>
                <?php endwhile; endif; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>
  <!-- end::App Content -->
</main>

<?php if ($labels): ?>
<script src="https://cdn.jsdelivr.net/npm/apexcharts@3.37.1/dist/apexcharts.min.js"></script>
<script>
  const gelap = document.documentElement.getAttribute('data-bs-theme') === 'dark';
  new ApexCharts(document.querySelector('#grafik-buku'), {
    chart: { type: 'bar', height: 300, toolbar: { show: false }, background: 'transparent' },
    theme: { mode: gelap ? 'dark' : 'light' },
    series: [{ name: 'Jumlah buku', data: <?= json_encode($jumlah) ?> }],
    xaxis: { categories: <?= json_encode($labels, JSON_HEX_TAG | JSON_HEX_AMP) ?> },
    yaxis: { labels: { formatter: (v) => Math.round(v) }, forceNiceScale: true },
    dataLabels: { enabled: false },
    plotOptions: { bar: { borderRadius: 4, columnWidth: '45%' } },
    colors: ['#0d6efd']
  }).render();
</script>
<?php endif; ?>



