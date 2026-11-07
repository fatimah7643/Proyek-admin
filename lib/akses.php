<?php
// lib/akses.php — data anggota, akun & role (MySQL) + hak akses

// Halaman yang boleh dibuka tiap role ('beranda' selalu boleh)
const HAK_AKSES = [
    'admin'   => ['dashboard', 'buku', 'anggota', 'beranda'],
    'petugas' => ['dashboard', 'buku', 'anggota', 'beranda'],
    'anggota' => ['beranda'],
];

function normalisasi_email(string $email): string {
    return mb_strtolower(trim($email));
}

function peran_user(): string {
    return $_SESSION['peran'] ?? 'anggota';
}

function boleh_akses(string $hal): bool {
    return $hal === 'beranda' || in_array($hal, HAK_AKSES[peran_user()] ?? [], true);
}

function halaman_awal(): string {
    return boleh_akses('dashboard') ? 'dashboard' : 'beranda';
}

function anggota_id_by_email(mysqli $db, string $email): ?int {
    $st = $db->prepare('SELECT id FROM anggota WHERE email = ?');
    $st->bind_param('s', $email);
    $st->execute();
    $row = $st->get_result()->fetch_assoc();
    return $row ? (int) $row['id'] : null;
}

/**
 * CALLBACK LOGIN — dipanggil setiap kali Supabase sukses mengautentikasi user
 * (login email, Google, GitHub, daftar, refresh token).
 * Mengecek database, mengambil role, lalu menyimpannya di session.
 * Return null kalau sukses, atau pesan error.
 */
function selesaikan_login(mysqli $db, array $user): ?string {
    $uid   = (string) ($user['id'] ?? '');
    $email = normalisasi_email((string) ($user['email'] ?? ''));
    if ($uid === '' || $email === '') {
        return 'Akun tidak punya alamat email. Gunakan metode login lain.';
    }
    $m    = $user['user_metadata'] ?? [];
    $nama = trim((string) ($m['nama'] ?? $m['full_name'] ?? $m['name'] ?? $m['user_name'] ?? ''));
    if ($nama === '') $nama = (string) strstr($email, '@', true);

    try {
        // 1. Cari akun: lewat UID Supabase, kalau belum ada lewat email
        $st = $db->prepare('SELECT id, anggota_id, supabase_uid, role FROM akun WHERE supabase_uid = ?');
        $st->bind_param('s', $uid);
        $st->execute();
        $akun = $st->get_result()->fetch_assoc();

        if (!$akun) {
            $st = $db->prepare('SELECT id, anggota_id, supabase_uid, role FROM akun WHERE email = ?');
            $st->bind_param('s', $email);
            $st->execute();
            $akun = $st->get_result()->fetch_assoc();

            if ($akun && !empty($akun['supabase_uid']) && $akun['supabase_uid'] !== $uid) {
                return 'Akun ini tidak cocok dengan data perpustakaan.';
            }
            if ($akun) {   // akun sudah disiapkan (daftar / dibuat admin): sambungkan ke UID Supabase
                $st = $db->prepare('UPDATE akun SET supabase_uid = ? WHERE id = ?');
                $st->bind_param('si', $uid, $akun['id']);
                $st->execute();
            }
        }

        // 2. Belum punya akun: pakai data anggota yang ada, atau buat anggota baru (tanpa duplikat)
        if (!$akun) {
            $db->begin_transaction();
            $anggotaId = anggota_id_by_email($db, $email);
            if ($anggotaId === null) {
                $st = $db->prepare('INSERT INTO anggota (nama, email) VALUES (?, ?)');
                $st->bind_param('ss', $nama, $email);
                $st->execute();
                $anggotaId = $db->insert_id;
            }
            $st = $db->prepare("INSERT INTO akun (anggota_id, supabase_uid, email, role) VALUES (?, ?, ?, 'anggota')");
            $st->bind_param('iss', $anggotaId, $uid, $email);
            $st->execute();
            $akunId = $db->insert_id;
            $db->commit();
            $akun = ['id' => $akunId, 'anggota_id' => $anggotaId, 'role' => 'anggota'];
        }
    } catch (Throwable $t) {
        try { $db->rollback(); } catch (Throwable $x) {}
        return 'Gagal membaca data akun di database.';
    }

    // 3. Simpan identitas + role di session (dasar hak akses)
    $_SESSION['peran']      = $akun['role'];
    $_SESSION['akun_id']    = (int) $akun['id'];
    $_SESSION['anggota_id'] = $akun['anggota_id'] !== null ? (int) $akun['anggota_id'] : null;
    return null;
}

/**
 * REGISTER PUBLIK — cek anggota/akun di database dulu, baru buat akun Supabase.
 * Return [status, pesan]; status: 'masuk' | 'konfirmasi' | 'ada' | 'galat'
 */
function daftar_publik(mysqli $db, string $nama, string $email, string $password): array {
    $email = normalisasi_email($email);
    if ($nama === '') $nama = (string) strstr($email, '@', true);

    // 1. Sudah punya akun? Jangan buat lagi.
    $st = $db->prepare('SELECT id FROM akun WHERE email = ?');
    $st->bind_param('s', $email);
    $st->execute();
    if ($st->get_result()->fetch_assoc()) {
        return ['ada', 'Email ini sudah punya akun. Silakan masuk, atau pakai "Lupa kata sandi" kalau lupa.'];
    }

    $db->begin_transaction();
    try {
        // 2. Sudah terdaftar sebagai anggota? Pakai datanya. Belum? Buat data anggota.
        $anggotaId = anggota_id_by_email($db, $email);
        if ($anggotaId === null) {
            $st = $db->prepare('INSERT INTO anggota (nama, email) VALUES (?, ?)');
            $st->bind_param('ss', $nama, $email);
            $st->execute();
            $anggotaId = $db->insert_id;
        }
        $st = $db->prepare("INSERT INTO akun (anggota_id, email, role) VALUES (?, ?, 'anggota')");
        $st->bind_param('is', $anggotaId, $email);
        $st->execute();

        // 3. Buat akun di Supabase; kalau gagal, batalkan semua perubahan database
        $r = supabase_request('POST', '/auth/v1/signup', [
            'email' => $email, 'password' => $password, 'data' => ['nama' => $nama],
        ]);
        if ($r['status'] !== 200) {
            $db->rollback();
            return ['galat', $r['data']['msg'] ?? $r['data']['message'] ?? 'Pendaftaran gagal.'];
        }
        $db->commit();
    } catch (Throwable $t) {
        try { $db->rollback(); } catch (Throwable $x) {}
        return ['galat', 'Pendaftaran gagal. Coba lagi.'];
    }

    if (!empty($r['data']['access_token'])) {   // Confirm email nonaktif: langsung login
        session_regenerate_id(true);
        $galat = simpan_sesi($r['data']);
        return $galat === null ? ['masuk', ''] : ['galat', $galat];
    }
    return ['konfirmasi', 'Pendaftaran berhasil. Cek email kamu untuk konfirmasi, lalu masuk.'];
}