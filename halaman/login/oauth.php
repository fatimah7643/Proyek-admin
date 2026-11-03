<?php
// halaman/login/oauth.php
$provider = $_GET['provider'] ?? '';
if (!in_array($provider, ['google', 'github'], true)) {
    http_response_code(400);
    exit('Provider tidak valid.');
}

// PKCE: verifier disimpan di session, challenge (hash-nya) dikirim ke Supabase
$verifier  = rtrim(strtr(base64_encode(random_bytes(64)), '+/', '-_'), '=');
$challenge = rtrim(strtr(base64_encode(hash('sha256', $verifier, true)), '+/', '-_'), '=');
$_SESSION['pkce_verifier'] = $verifier;

header('Location: ' . SUPABASE_URL . '/auth/v1/authorize?' . http_build_query([
    'provider'              => $provider,
    'redirect_to'           => BASE_URL . 'index.php?hal=login&aksi=callback',
    'code_challenge'        => $challenge,
    'code_challenge_method' => 's256',
]));
exit;