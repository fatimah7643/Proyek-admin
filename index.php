<?php
// index.php
session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax']);
session_start();
require_once __DIR__ . '/env.php';
require_once __DIR__ . '/koneksi.php';
require_once __DIR__ . '/lib/supabase.php';
require_once __DIR__ . '/lib/akses.php';

$hal  = $_GET['hal']  ?? 'dashboard';
$aksi = $_GET['aksi'] ?? 'index';

// Daftar putih (whitelist) route => mode tampilan
$peta = [
    'dashboard' => ['index' => 'layout'],
    'beranda'   => ['index' => 'mandiri'],
    'login'     => [
                    'index' => 'mandiri', 
                    'logout' => 'mandiri', 
                    'oauth' => 'mandiri',
                    'callback' => 'mandiri', 
                    'api' => 'mandiri', 
                    'lupa' => 'mandiri'
                    ],
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

if (!isset($peta[$hal][$aksi])) {
    http_response_code(404);
    exit('Halaman tidak ditemukan.');
}

// Penjaga: harus login, lalu cek hak akses sesuai role dari database
if ($hal !== 'login') {
    if (!sesi_valid()) {
        $_SESSION = [];
        redirect('login');
    }
    if (!boleh_akses($hal)) {
        redirect(halaman_awal());
    }
}

// Sudah login tapi buka halaman login -> arahkan sesuai role
if ($hal === 'login' && $aksi === 'index' && sesi_valid()) {
    redirect(halaman_awal());
}

$file = ROOT_PATH . "/halaman/$hal/$aksi.php";
$mode = $peta[$hal][$aksi];

if ($mode === 'mandiri') {
    require $file;
    exit;
}

ob_start();
require $file;
$konten = ob_get_clean();

require ROOT_PATH . '/template/header.php';
require ROOT_PATH . '/template/sidebar.php';
echo $konten;
require ROOT_PATH . '/template/footer.php';