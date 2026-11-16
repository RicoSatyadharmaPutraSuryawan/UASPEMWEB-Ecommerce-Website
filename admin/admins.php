<?php
require_once __DIR__ . '/../includes/functions.php';
require_admin();

$me = current_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = $_POST['action'] ?? '';
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($action === 'create') {
        if (!preg_match('/^[a-zA-Z0-9_]{4,50}$/', $username)) {
            flash('danger', 'Username 4-50 karakter, hanya huruf, angka, dan garis bawah.');
        } elseif (strlen($password) < 8) {
            flash('danger', 'Password minimal 8 karakter.');
        } else {
            $stmt = $conn->prepare('SELECT 1 FROM admins WHERE username = ?');
            $stmt->bind_param('s', $username);
            $stmt->execute();
            if ($stmt->get_result()->num_rows) {
                flash('danger', 'Username sudah dipakai.');
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare('INSERT INTO admins (username, password) VALUES (?, ?)');
                $stmt->bind_param('ss', $username, $hash);
                $stmt->execute();
                flash('success', 'Admin ' . $username . ' ditambahkan.');
            }
        }
    } elseif ($action === 'password') {
        $id = (int) $_POST['id'];
        if (strlen($password) < 8) {
            flash('danger', 'Password minimal 8 karakter.');
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare('UPDATE admins SET password = ? WHERE id = ?');
            $stmt->bind_param('si', $hash, $id);
            $stmt->execute();
            flash('success', 'Password berhasil diganti.');
        }
    } elseif ($action === 'delete') {
        $id = (int) $_POST['id'];
        $total = (int) $conn->query('SELECT COUNT(*) FROM admins')->fetch_row()[0];
        if ($id === (int) $me['id']) {
            flash('danger', 'Tidak bisa menghapus akun yang sedang dipakai.');
        } elseif ($total <= 1) {
            flash('danger', 'Minimal harus ada satu admin.');
        } else {
            $stmt = $conn->prepare('DELETE FROM admins WHERE id = ?');
            $stmt->bind_param('i', $id);
            $stmt->execute();
            flash('success', 'Admin dihapus.');
        }
    }
    redirect('admins.php');
}

$admins = $conn->query('SELECT id, username, created_at FROM admins ORDER BY id')->fetch_all(MYSQLI_ASSOC);

$pageTitle = 'Akun Admin';
$activeMenu = 'admins';
require __DIR__ . '/_header.php';
?>
<div class="row">
  <div class="col-lg-8">
    <div class="card">
      <div class="card-body p-0 table-responsive">
        <table class="table mb-0">
          <thead><tr><th>Username</th><th>Dibuat</th><th>Ganti Password</th><th></th></tr></thead>
          <tbody>
          <?php foreach ($admins as $a): ?>
            <tr>
              <td><?= e($a['username']) ?> <?= $a['id'] == $me['id'] ? '<span class="badge badge-info">Anda</span>' : '' ?></td>
              <td><?= e(date('d M Y', strtotime($a['created_at']))) ?></td>
              <td>
                <form method="post" class="form-inline">
                  <?= csrf_field() ?>
                  <input type="hidden" name="action" value="password">
                  <input type="hidden" name="id" value="<?= $a['id'] ?>">
                  <input class="form-control form-control-sm mr-1" type="password" name="password" placeholder="Password baru" minlength="8" required>
                  <button class="btn btn-sm btn-outline-primary" type="submit">Simpan</button>
                </form>
              </td>
              <td>
                <?php if ($a['id'] != $me['id']): ?>
                <form method="post" onsubmit="return confirm('Hapus admin <?= e($a['username']) ?>?')">
                  <?= csrf_field() ?>
                  <input type="hidden" name="action" value="delete">
                  <input type="hidden" name="id" value="<?= $a['id'] ?>">
                  <button class="btn btn-sm btn-danger" type="submit"><i class="fas fa-trash"></i></button>
                </form>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
  <div class="col-lg-4">
    <div class="card card-primary">
      <div class="card-header"><h3 class="card-title">Tambah Admin</h3></div>
      <form method="post">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="create">
        <div class="card-body">
          <div class="form-group"><label>Username</label><input class="form-control" name="username" pattern="[a-zA-Z0-9_]{4,50}" required></div>
          <div class="form-group"><label>Password</label><input class="form-control" type="password" name="password" minlength="8" required></div>
        </div>
        <div class="card-footer"><button class="btn btn-primary" type="submit"><i class="fas fa-user-plus"></i> Tambah</button></div>
      </form>
    </div>
  </div>
</div>
<?php require __DIR__ . '/_footer.php'; ?>
