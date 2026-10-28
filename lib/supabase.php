<?php
function supabase_request(string $method, string $path, ?array $body = null, ?string $token = null): array {
    $headers = ['Content-Type: application/json', 'apikey: ' . SUPABASE_PUBLISHABLE_KEY];
    if ($token) $headers[] = 'Authorization: Bearer ' . $token;

    $ch = curl_init(SUPABASE_URL . $path);
    curl_setopt_array($ch, [
        CURLOPT_CUSTOMREQUEST  => $method,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => $headers,
        CURLOPT_TIMEOUT        => 10,
    ]);
    if ($body !== null) curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));

    $hasil  = curl_exec($ch);
    $galat  = curl_error($ch);
    $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if ($hasil === false) return ['status' => 0, 'data' => ['error' => $galat]];
    return ['status' => $status, 'data' => json_decode($hasil, true) ?? []];
}

function simpan_sesi(array $d): void {
    $_SESSION['access_token']  = $d['access_token'];
    $_SESSION['refresh_token'] = $d['refresh_token'] ?? '';
    $_SESSION['expires_at']    = (int) ($d['expires_at'] ?? (time() + (int) ($d['expires_in'] ?? 3600)));
    $_SESSION['user']          = $d['user']['email'] ?? ($_SESSION['user'] ?? 'admin');
}

function supabase_login(string $email, string $password): ?string {
    $r = supabase_request('POST', '/auth/v1/token?grant_type=password', [
        'email' => $email, 'password' => $password,
    ]);
    if ($r['status'] === 200 && !empty($r['data']['access_token'])) {
        session_regenerate_id(true);
        simpan_sesi($r['data']);
        return null; // sukses
    }
    if ($r['status'] === 0) return 'Gagal konek ke Supabase: ' . ($r['data']['error'] ?? '');
    return 'Email atau password salah.';
}

function sesi_valid(): bool {
    if (empty($_SESSION['access_token'])) return false;
    if (time() < ($_SESSION['expires_at'] ?? 0) - 30) return true;

    // token habis -> refresh otomatis
    $r = supabase_request('POST', '/auth/v1/token?grant_type=refresh_token', [
        'refresh_token' => $_SESSION['refresh_token'] ?? '',
    ]);
    if ($r['status'] === 200 && !empty($r['data']['access_token'])) {
        simpan_sesi($r['data']);
        return true;
    }
    return false;
}

function supabase_user(string $token): ?array {
    $r = supabase_request('GET', '/auth/v1/user', null, $token);
    return $r['status'] === 200 ? $r['data'] : null;
}

function supabase_logout(): void {
    if (!empty($_SESSION['access_token'])) {
        supabase_request('POST', '/auth/v1/logout', null, $_SESSION['access_token']);
    }
    $_SESSION = [];
    session_destroy();
}

function jwt_payload(string $jwt): array {
    $p = explode('.', $jwt);
    if (count($p) !== 3) return [];
    $b64 = strtr($p[1], '-_', '+/');
    return json_decode(base64_decode($b64 . str_repeat('=', (4 - strlen($b64) % 4) % 4)) ?: '', true) ?? [];
}