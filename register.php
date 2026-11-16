<?php
require_once __DIR__ . '/includes/functions.php';

if (current_user()) redirect('index.php');

$errors = [];
$data = ['nama' => '', 'email' => '', 'no_hp' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $data['nama'] = trim($_POST['nama'] ?? '');
    $data['email'] = strtolower(trim($_POST['email'] ?? ''));
    $data['no_hp'] = trim($_POST['no_hp'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['password_confirm'] ?? '';

    if (mb_strlen($data['nama']) < 3) $errors[] = 'Nama minimal 3 karakter.';
    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'Format email tidak valid.';
    if (!preg_match('/^(\+62|0)[0-9]{8,13}$/', $data['no_hp'])) $errors[] = 'Nomor HP tidak valid, contoh: 081234567890.';
    if (strlen($password) < 8) $errors[] = 'Password minimal 8 karakter.';
    if ($password !== $confirm) $errors[] = 'Konfirmasi password tidak sama.';

    if (!$errors) {
        $stmt = $conn->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->bind_param('s', $data['email']);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $errors[] = 'Email sudah terdaftar. Silakan login.';
        }
    }

    if (!$errors) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare('INSERT INTO users (nama, email, no_hp, password) VALUES (?, ?, ?, ?)');
        $stmt->bind_param('ssss', $data['nama'], $data['email'], $data['no_hp'], $hash);
        $stmt->execute();
        flash('success', 'Pendaftaran berhasil. Silakan login.');
        redirect('login.php');
    }
}

$pageTitle = 'Daftar';
$activePage = 'register';
require __DIR__ . '/includes/header.php';
?>
<div class="container container-narrow">
  <form method="post" class="card-form" novalidate>
    <?= csrf_field() ?>
    <h1>Daftar Akun</h1>
    <?php if ($errors): ?>
      <div class="alert alert-danger"><ul><?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul></div>
    <?php endif; ?>
    <div class="form-group"><label for="nama">Nama Lengkap</label>
      <input class="form-control" id="nama" name="nama" value="<?= e($data['nama']) ?>" required></div>
    <div class="form-group"><label for="email">Email</label>
      <input class="form-control" type="email" id="email" name="email" value="<?= e($data['email']) ?>" required></div>
    <div class="form-group"><label for="no_hp">Nomor HP</label>
      <input class="form-control" id="no_hp" name="no_hp" value="<?= e($data['no_hp']) ?>" placeholder="081234567890" required></div>
    <div class="form-group"><label for="password">Password</label>
      <input class="form-control" type="password" id="password" name="password" minlength="8" required></div>
    <div class="form-group"><label for="password_confirm">Ulangi Password</label>
      <input class="form-control" type="password" id="password_confirm" name="password_confirm" minlength="8" required></div>
    <button class="btn btn-block" type="submit">Daftar</button>
    <p class="form-footer">Sudah punya akun? <a href="login.php">Login di sini</a></p>
  </form>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
