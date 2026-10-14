<?php
// halaman/login/index.php
if ($aksi === 'logout') {
    session_destroy();
    redirect('login');
}

$galat = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $db->prepare('SELECT password FROM pengguna WHERE username = ?');
    $stmt->bind_param('s', $_POST['username']);
    $stmt->execute();
    $baris = $stmt->get_result()->fetch_assoc();

    if ($baris && password_verify($_POST['password'], $baris['password'])) {
        session_regenerate_id(true);
        $_SESSION['user'] = $_POST['username'];
        redirect('dashboard');
    }
    $galat = 'Username atau password salah.';
}
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Login | <?= APP_NAME ?></title>
  <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/adminlte.css" />
</head>
<body class="login-page bg-body-secondary">
  <div class="login-box">
    <div class="card">
      <div class="card-body login-card-body">
        <?php if ($galat): ?>
          <div class="alert alert-danger"><?= e($galat) ?></div>
        <?php endif; ?>
        <form method="post">
          <input class="form-control mb-3" name="username" placeholder="Username" required>
          <input class="form-control mb-3" type="password" name="password" placeholder="Password" required>
          <button class="btn btn-primary w-100">Masuk</button>
        </form>
      </div>
    </div>
  </div>
</body>
</html>
