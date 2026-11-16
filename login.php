<?php
require_once __DIR__ . '/includes/functions.php';

if (current_user()) redirect('index.php');

// Hanya izinkan redirect ke halaman lokal
$next = $_GET['next'] ?? $_POST['next'] ?? 'index.php';
if (!preg_match('#^/?[a-zA-Z0-9_\-/]+\.php(\?[^\s]*)?$#', $next)) {
    $next = 'index.php';
}

$error = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $email = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';

    $stmt = $conn->prepare('SELECT id, nama, email, no_hp, password FROM users WHERE email = ?');
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    if ($user && password_verify($password, $user['password'])) {
        session_regenerate_id(true);
        unset($user['password']);
        $_SESSION['user'] = $user;
        flash('success', 'Selamat datang, ' . $user['nama'] . '!');
        redirect($next);
    }
    $error = 'Email atau password salah.';
}

$pageTitle = 'Login';
$activePage = 'login';
require __DIR__ . '/includes/header.php';
?>
<div class="container container-narrow">
  <form method="post" class="card-form">
    <?= csrf_field() ?>
    <input type="hidden" name="next" value="<?= e($next) ?>">
    <h1>Login Pelanggan</h1>
    <?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
    <div class="form-group"><label for="email">Email</label>
      <input class="form-control" type="email" id="email" name="email" value="<?= e($email) ?>" required autofocus></div>
    <div class="form-group"><label for="password">Password</label>
      <input class="form-control" type="password" id="password" name="password" required></div>
    <button class="btn btn-block" type="submit">Login</button>
    <p class="form-footer">Belum punya akun? <a href="register.php">Daftar di sini</a></p>
    <p class="form-footer"><a href="admin/login.php" style="font-weight:400">Login sebagai admin</a></p>
  </form>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
