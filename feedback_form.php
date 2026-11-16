<?php
require_once __DIR__ . '/includes/functions.php';

$errors = [];
$data = ['name' => current_user()['nama'] ?? '', 'email' => current_user()['email'] ?? '', 'feedback' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    foreach ($data as $key => $_) {
        $data[$key] = trim($_POST[$key] ?? '');
    }
    if (mb_strlen($data['name']) < 2) $errors[] = 'Nama wajib diisi.';
    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'Format email tidak valid.';
    if (mb_strlen($data['feedback']) < 5) $errors[] = 'Feedback minimal 5 karakter.';
    if (mb_strlen($data['feedback']) > 1000) $errors[] = 'Feedback maksimal 1000 karakter.';

    if (!$errors) {
        $stmt = $conn->prepare('INSERT INTO feedbacks (name, email, feedback) VALUES (?, ?, ?)');
        $stmt->bind_param('sss', $data['name'], $data['email'], $data['feedback']);
        $stmt->execute();
        flash('success', 'Terima kasih! Feedback kamu sudah terkirim.');
        redirect('feedback_form.php');
    }
}

// Ambil feedback beserta balasan admin dalam satu query
$rows = $conn->query('SELECT f.id, f.name, f.feedback, f.created_at, r.reply, r.created_at AS replied_at
                      FROM feedbacks f LEFT JOIN admin_replies r ON r.feedback_id = f.id
                      ORDER BY f.created_at DESC, f.id DESC, r.id ASC');
$feedbacks = [];
foreach ($rows as $row) {
    $feedbacks[$row['id']] ??= $row + ['replies' => []];
    if ($row['reply'] !== null) $feedbacks[$row['id']]['replies'][] = $row['reply'];
}

$pageTitle = 'Feedback';
$activePage = 'feedback';
require __DIR__ . '/includes/header.php';
?>
<div class="container container-mid">
  <h1 class="page-title">Feedback</h1>
  <p class="page-subtitle">Kritik dan saran kamu membantu kami jadi lebih baik.</p>

  <form method="post" class="card-form">
    <?= csrf_field() ?>
    <?php if ($errors): ?>
      <div class="alert alert-danger"><ul><?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul></div>
    <?php endif; ?>
    <div class="form-group"><label for="name">Nama</label>
      <input class="form-control" id="name" name="name" value="<?= e($data['name']) ?>" required></div>
    <div class="form-group"><label for="email">Email</label>
      <input class="form-control" type="email" id="email" name="email" value="<?= e($data['email']) ?>" required></div>
    <div class="form-group"><label for="feedback">Pesan</label>
      <textarea class="form-control" id="feedback" name="feedback" maxlength="1000" required><?= e($data['feedback']) ?></textarea></div>
    <button class="btn btn-block" type="submit">Kirim Feedback</button>
  </form>

  <hr class="divider">
  <h2 class="page-title">Feedback Pelanggan</h2>
  <div class="feedback-list mt-2">
    <?php if (!$feedbacks): ?><p class="text-muted">Belum ada feedback.</p><?php endif; ?>
    <?php foreach ($feedbacks as $f): ?>
      <div class="feedback-item">
        <h3><?= e($f['name']) ?></h3>
        <time><?= e(date('d M Y', strtotime($f['created_at']))) ?></time>
        <p><?= nl2br(e($f['feedback'])) ?></p>
        <?php foreach ($f['replies'] as $reply): ?>
          <div class="reply"><strong>Balasan admin:</strong><br><?= nl2br(e($reply)) ?></div>
        <?php endforeach; ?>
      </div>
    <?php endforeach; ?>
  </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
