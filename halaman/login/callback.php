<?php
// halaman/login/callback.php
$kode     = $_GET['code'] ?? '';
$verifier = $_SESSION['pkce_verifier'] ?? '';
unset($_SESSION['pkce_verifier']);

if (isset($_GET['error']) || $kode === '' || $verifier === '') {
    $_SESSION['galat_login'] = 'Login dengan akun sosial dibatalkan atau gagal.';
    redirect('login');
}

$r = supabase_request('POST', '/auth/v1/token?grant_type=pkce', [
    'auth_code'     => $kode,
    'code_verifier' => $verifier,
]);

if ($r['status'] === 200 && !empty($r['data']['access_token'])) {
    session_regenerate_id(true);
    simpan_sesi($r['data']);
    redirect(halaman_awal());
}

$_SESSION['galat_login'] = 'Gagal menyelesaikan login. Coba lagi.';
redirect('login');