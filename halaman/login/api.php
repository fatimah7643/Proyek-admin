<?php
// halaman/login/api.php — API: identitas, role, dan hak akses user yang sedang login
header('Content-Type: application/json; charset=utf-8');

if (!sesi_valid()) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'pesan' => 'Belum login.']);
    exit;
}

$peran = peran_user();
echo json_encode([
    'status'               => 'ok',
    'email'                => $_SESSION['user'],
    'peran'                => $peran,
    'anggota_id'           => $_SESSION['anggota_id'] ?? null,
    'hak_akses'            => HAK_AKSES[$peran] ?? ['beranda'],
    'token_berlaku_sampai' => date('c', (int) $_SESSION['expires_at']),
], JSON_UNESCAPED_UNICODE);