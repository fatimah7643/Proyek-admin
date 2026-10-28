<?php
$galat = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $galat = supabase_login(trim($_POST['email'] ?? ''), $_POST['password'] ?? '');
    if ($galat === null) {
        redirect('dashboard');
    }
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
          <input class="form-control mb-3" type="email" name="email" placeholder="Email" required autofocus>
          <input class="form-control mb-3" type="password" name="password" placeholder="Password" required>
          <button class="btn btn-primary w-100">Masuk</button>
        </form>
      </div>
    </div>
  </div>
</body>
</html>
