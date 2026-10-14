<?php
// index.php
session_start();
require_once __DIR__ . '/env.php';
require_once __DIR__ . '/koneksi.php';

$hal  = $_GET['hal']  ?? 'dashboard';
$aksi = $_GET['aksi'] ?? 'index';

// Daftar putih (whitelist) route => mode tampilan
$peta = [
    'dashboard' => ['index'  => 'layout'],
    'login'     => ['index'  => 'mandiri', 'logout' => 'mandiri'],
    'buku'      => [
        'index'  => 'layout',
        'create' => 'layout',
        'update' => 'layout',
        'print'  => 'mandiri',
    ],
    'anggota'   => [
        'index'  => 'layout',
        'create' => 'layout',
        'update' => 'layout',
    ],
];

// Validasi Whitelist: Jika halaman/aksi tidak terdaftar, lempar error 404
if (!isset($peta[$hal][$aksi])) {
    http_response_code(404);
    exit('Halaman tidak ditemukan.');
}

// Penjaga login: Jika bukan halaman login dan sesi user kosong, tendang ke login
if ($hal !== 'login' && empty($_SESSION['user'])) {
    redirect('login');
}

$file = ROOT_PATH . "/halaman/$hal/$aksi.php";
$mode = $peta[$hal][$aksi];

// Jika mode 'mandiri' (seperti cetak laporan atau login), langsung muat file halaman
if ($mode === 'mandiri') {
    require $file;           
    exit;
}

// Jika mode 'layout': jalankan halaman DULU dan simpan ke dalam memori buffer (ob_start)
ob_start();
require $file;
$konten = ob_get_clean();

// Gabungkan kerangka AdminLTE dengan isi konten halaman
require ROOT_PATH . '/template/header.php';
require ROOT_PATH . '/template/sidebar.php';
echo $konten;
require ROOT_PATH . '/template/footer.php';