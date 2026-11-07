# Perpustakaan Admin

Aplikasi admin perpustakaan berbasis PHP native + MySQL dengan autentikasi lewat **Supabase Auth (JWT)**. Dibuat untuk tugas praktik Pemrograman Web.

## Fitur

- **Register publik**: cek dulu apakah email sudah terdaftar sebagai anggota. Kalau sudah, data anggota dipakai ulang (tanpa duplikat). Kalau belum, data anggota dan akun dibuat sekaligus.
- **Login**: email + kata sandi, Google, dan GitHub.
- **API Callback login**: setiap login berhasil, sistem mengecek database, mengambil role (`admin`, `petugas`, `anggota`), dan menentukan hak akses.
- **Session + JWT**: JWT dari Supabase disimpan di session server, dan di-refresh otomatis saat kedaluwarsa.
- **Lupa kata sandi dengan OTP**: kode 6 digit dikirim ke email, lalu pengguna memperbarui kata sandi.
- **Hak akses per role** (lihat tabel di bawah).

## Hak akses

| Role    | Dashboard | Data Buku | Data Anggota | Hapus Anggota | Beranda (anggota) |
|---------|-----------|-----------|--------------|---------------|-------------------|
| admin   | ya        | ya        | ya           | ya            | ya                |
| petugas | ya        | ya        | ya           | tidak         | ya                |
| anggota | tidak     | tidak     | tidak        | tidak         | ya                |

## Teknologi

PHP 8.1+ (ekstensi cURL aktif), MySQL/MariaDB, AdminLTE, dan Supabase Auth.

## Struktur singkat

```
index.php              router + penjaga login/hak akses
env.example.php        contoh konfigurasi (salin jadi env.php)
koneksi.php            koneksi MySQL
lib/supabase.php       helper Supabase Auth (login, refresh, logout, JWT)
lib/akses.php          role, hak akses, callback login, register publik
halaman/login/        login, daftar, lupa kata sandi, oauth, callback, api
halaman/dashboard/    dashboard admin/petugas
halaman/buku/         CRUD buku
halaman/anggota/      CRUD anggota
halaman/beranda/      halaman anggota perpustakaan
```

## Cara menjalankan

### 1. Persiapan
- Laragon atau XAMPP (PHP 8.1+, MySQL).
- Akun Supabase (gratis) dan satu project.

### 2. Taruh proyek
Salin folder ke `www` (Laragon) atau `htdocs` (XAMPP). Nama folder harus `proyek-admin`, atau sesuaikan `BASE_URL` di langkah 4.

### 3. Import database
1. Buat database `perpustakaan` di phpMyAdmin.
2. Import file `perpustakaan.sql`.

Kalau belum ada tabel `akun` di dump-nya, jalankan SQL ini:

```sql
ALTER TABLE anggota ADD UNIQUE KEY uq_anggota_email (email);

CREATE TABLE akun (
  id           INT AUTO_INCREMENT PRIMARY KEY,
  anggota_id   INT NULL,
  supabase_uid CHAR(36) NULL UNIQUE,
  email        VARCHAR(100) NOT NULL UNIQUE,
  role         ENUM('admin','petugas','anggota') NOT NULL DEFAULT 'anggota',
  dibuat_pada  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_akun_anggota FOREIGN KEY (anggota_id) REFERENCES anggota(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### 4. Konfigurasi
Salin `env.example.php` menjadi `env.php`, lalu isi:

```php
define('BASE_URL', 'http://localhost/proyek-admin/');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'perpustakaan');
define('SUPABASE_URL', 'https://xxxxx.supabase.co');
define('SUPABASE_PUBLISHABLE_KEY', 'sb_publishable_xxxxx');
```

`SUPABASE_URL` dan key ada di Supabase: **Project Settings → API Keys**. Pakai **publishable key**, jangan secret key. File `env.php` tidak ikut ke Git.

### 5. Pengaturan Supabase
1. **Sign in / Providers → Email**: pastikan sign up diizinkan. Nyalakan **Confirm email** (disarankan, supaya role admin/petugas aman). Atur **Email OTP Length** ke `6`.
2. **SMTP** (supaya template email bisa diedit): **Authentication → Emails → Set up SMTP**. Contoh pakai Gmail: host `smtp.gmail.com`, port `465`, username Gmail, password berupa App Password.
3. **Template Reset password** (Authentication → Emails), isi body dengan:
   ```html
   <h2>Reset kata sandi</h2>
   <p>Masukkan kode berikut di halaman lupa kata sandi:</p>
   <p style="font-size:28px;font-weight:bold;letter-spacing:6px">{{ .Token }}</p>
   <p>Kalau kamu tidak merasa memintanya, abaikan email ini.</p>
   ```
4. **URL Configuration**: Site URL `http://localhost/proyek-admin/`, dan tambahkan Redirect URL `http://localhost/proyek-admin/**`.
5. **Google dan GitHub** (opsional): aktifkan di Sign In / Providers, isi Client ID dan Secret. Callback URL di sisi Google/GitHub adalah `https://<project-ref>.supabase.co/auth/v1/callback`.

### 6. Buat akun admin
1. Di Supabase: **Authentication → Users → Add user**, isi email dan kata sandi, centang **Auto Confirm User**.
2. Di phpMyAdmin, daftarkan sebagai admin (ganti emailnya):
   ```sql
   INSERT INTO akun (anggota_id, email, role) VALUES (NULL, 'admin@email.com', 'admin');
   ```
3. Login dengan akun itu. Saat login pertama, akun Supabase otomatis disambungkan ke baris `akun`.

Untuk **petugas**: buat user di Supabase dengan cara yang sama, lalu `INSERT INTO akun (email, role) VALUES ('petugas@email.com', 'petugas');` atau `UPDATE akun SET role='petugas' WHERE email='...';` untuk akun yang sudah ada. Perubahan role berlaku setelah pengguna login ulang.

### 7. Buka aplikasi
`http://localhost/proyek-admin/`

## Alur autentikasi

1. **Daftar**: cek `akun` (sudah punya akun? arahkan ke login), cek `anggota` (sudah ada? pakai ulang), buat akun Supabase. Jika gagal, perubahan database dibatalkan.
2. **Login**: Supabase memverifikasi kredensial dan mengirim JWT, lalu `selesaikan_login()` mencocokkan dengan tabel `akun`, mengambil role, dan menyimpannya di session.
3. **Pengguna baru lewat Google/GitHub**: otomatis dibuatkan anggota dan akun (atau disambungkan ke anggota yang emailnya sama).
4. **Lupa kata sandi**: email → kode OTP 6 digit → verifikasi → kata sandi baru → login ulang.

## Endpoint API

`GET index.php?hal=login&aksi=api` (harus sudah login) mengembalikan JSON:

```json
{
  "status": "ok",
  "email": "user@email.com",
  "peran": "anggota",
  "anggota_id": 3,
  "hak_akses": ["beranda"],
  "token_berlaku_sampai": "2026-11-07T16:30:00+08:00"
}
```

Jika belum login, responsnya `401` dengan `{"status":"error","pesan":"Belum login."}`.

## Catatan

- OTP dikirim lewat **email**. OTP ke nomor HP membutuhkan penyedia SMS dan belum diimplementasikan.
- Password tidak disimpan di MySQL, hanya dikelola Supabase Auth.
- Jangan commit `env.php` atau App Password Gmail.

## Troubleshooting

| Masalah | Penyebab umum |
|---|---|
| `Call to undefined function curl_init` | Aktifkan `extension=curl` di `php.ini`. |
| cURL error 60 (SSL certificate) | Set `curl.cainfo` ke file `cacert.pem` di `php.ini`, lalu restart web server. |
| Email OTP berisi link, bukan kode | Template Reset password belum memakai `{{ .Token }}`. |
| Email OTP tidak masuk | Cek folder spam, pengaturan SMTP, dan log Auth di Supabase. |
| Login Google/GitHub balik ke halaman login | Redirect URL belum ditambahkan di URL Configuration. |
| Admin masuk ke halaman anggota | Baris admin di tabel `akun` belum dibuat, atau belum login ulang. |