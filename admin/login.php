<?php
require_once __DIR__ . '/../includes/functions.php';

if (current_admin()) redirect('index.php');

$error = '';
$username = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $conn->prepare('SELECT id, username, password FROM admins WHERE username = ?');
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $admin = $stmt->get_result()->fetch_assoc();

    if ($admin && password_verify($password, $admin['password'])) {
        session_regenerate_id(true);
        $_SESSION['admin'] = ['id' => $admin['id'], 'username' => $admin['username']];
        redirect('index.php');
    }
    $error = 'Username atau password salah.';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Login Admin | Driptique</title>
  <link rel="icon" href="../aset-gambar/DRIPTIQUE.svg">
  <link rel="stylesheet" href="../plugins/fontawesome-free/css/all.min.css">
  <link rel="stylesheet" href="../dist/css/adminlte.min.css">
  <style>body.login-page { background: #1f1f3a; } .login-logo img { width: 80px; background: #fff; border-radius: 50%; padding: 6px; }</style>
</head>
<body class="hold-transition login-page">
<div class="login-box">
  <div class="login-logo"><img src="../aset-gambar/DRIPTIQUE.svg" alt="Driptique"></div>
  <div class="card">
    <div class="card-body login-card-body">
      <p class="login-box-msg">Login Admin Driptique</p>
      <?php foreach (get_flashes() as $f): ?><div class="alert alert-<?= e($f['type']) ?>"><?= e($f['message']) ?></div><?php endforeach; ?>
      <?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
      <form method="post">
        <?= csrf_field() ?>
        <div class="input-group mb-3">
          <input type="text" name="username" class="form-control" placeholder="Username" value="<?= e($username) ?>" required autofocus>
          <div class="input-group-append"><div class="input-group-text"><span class="fas fa-user"></span></div></div>
        </div>
        <div class="input-group mb-3">
          <input type="password" name="password" class="form-control" placeholder="Password" required>
          <div class="input-group-append"><div class="input-group-text"><span class="fas fa-lock"></span></div></div>
        </div>
        <button type="submit" class="btn btn-dark btn-block">Login</button>
      </form>
      <p class="mt-3 mb-0 text-center"><a href="../index.php">&larr; Kembali ke toko</a></p>
    </div>
  </div>
</div>
</body>
</html>
