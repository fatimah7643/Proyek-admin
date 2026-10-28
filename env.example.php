<?php
// env.example.php — salin jadi env.php lalu isi nilai aslinya
define('APP_NAME', 'Perpustakaan Admin');
define('BASE_URL', 'http://localhost/proyek-admin/');
define('ROOT_PATH', __DIR__);

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'perpustakaan');

// Supabase Auth (Project Settings -> API Keys)
define('SUPABASE_URL', 'https://xxxxx.supabase.co');
define('SUPABASE_PUBLISHABLE_KEY', 'sb_publishable_xxxxx');

// Fungsi bantu: escape output agar aman dari XSS
function e(?string $teks): string {
    return htmlspecialchars($teks ?? '', ENT_QUOTES, 'UTF-8');
}

function redirect(string $hal, string $aksi = 'index'): never {
    header('Location: ' . BASE_URL . "index.php?hal=$hal&aksi=$aksi");
    exit;
}