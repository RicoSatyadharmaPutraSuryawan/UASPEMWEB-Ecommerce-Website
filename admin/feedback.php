<?php
require_once __DIR__ . '/../includes/functions.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = $_POST['action'] ?? '';
    $reply = trim($_POST['reply'] ?? '');

    if ($action === 'reply') {
        $feedbackId = (int) $_POST['feedback_id'];
        if ($reply === '') {
            flash('danger', 'Balasan tidak boleh kosong.');
        } else {
            $stmt = $conn->prepare('INSERT INTO admin_replies (feedback_id, reply) VALUES (?, ?)');
            $stmt->bind_param('is', $feedbackId, $reply);
            $stmt->execute();
            flash('success', 'Balasan terkirim.');
        }
    } elseif ($action === 'edit_reply') {
        $replyId = (int) $_POST['reply_id'];
        if ($reply === '') {
            flash('danger', 'Balasan tidak boleh kosong.');
        } else {
            $stmt = $conn->prepare('UPDATE admin_replies SET reply = ? WHERE id = ?');
            $stmt->bind_param('si', $reply, $replyId);
            $stmt->execute();
            flash('success', 'Balasan diperbarui.');
        }
    } elseif ($action === 'delete_reply') {
        $replyId = (int) $_POST['reply_id'];
        $stmt = $conn->prepare('DELETE FROM admin_replies WHERE id = ?');
        $stmt->bind_param('i', $replyId);
        $stmt->execute();
        flash('success', 'Balasan dihapus.');
    } elseif ($action === 'delete_feedback') {
        // Balasan ikut terhapus lewat ON DELETE CASCADE
        $feedbackId = (int) $_POST['feedback_id'];
        $stmt = $conn->prepare('DELETE FROM feedbacks WHERE id = ?');
        $stmt->bind_param('i', $feedbackId);
        $stmt->execute();
        flash('success', 'Feedback dihapus.');
    }
    redirect('feedback.php');
}

$rows = $conn->query('SELECT f.*, r.id AS reply_id, r.reply, r.created_at AS replied_at
                      FROM feedbacks f LEFT JOIN admin_replies r ON r.feedback_id = f.id
                      ORDER BY f.created_at DESC, f.id DESC, r.id ASC');
$feedbacks = [];
foreach ($rows as $row) {
    $feedbacks[$row['id']] ??= $row + ['replies' => []];
    if ($row['reply_id']) $feedbacks[$row['id']]['replies'][] = $row;
}

$pageTitle = 'Feedback';
$activeMenu = 'feedback';
require __DIR__ . '/_header.php';
?>
<?php if (!$feedbacks): ?><div class="card"><div class="card-body text-muted">Belum ada feedback.</div></div><?php endif; ?>
<?php foreach ($feedbacks as $f): ?>
  <div class="card <?= $f['replies'] ? '' : 'card-outline card-warning' ?>">
    <div class="card-header">
      <h3 class="card-title"><strong><?= e($f['name']) ?></strong> &lt;<?= e($f['email']) ?>&gt;
        <small class="text-muted ml-2"><?= e(date('d M Y H:i', strtotime($f['created_at']))) ?></small></h3>
      <div class="card-tools">
        <?php if (!$f['replies']): ?><span class="badge badge-warning">Belum dibalas</span><?php endif; ?>
        <form method="post" class="d-inline" onsubmit="return confirm('Hapus feedback ini beserta balasannya?')">
          <?= csrf_field() ?>
          <input type="hidden" name="action" value="delete_feedback">
          <input type="hidden" name="feedback_id" value="<?= $f['id'] ?>">
          <button class="btn btn-tool text-danger" type="submit" title="Hapus feedback"><i class="fas fa-trash"></i></button>
        </form>
      </div>
    </div>
    <div class="card-body">
      <p><?= nl2br(e($f['feedback'])) ?></p>

      <?php foreach ($f['replies'] as $r): ?>
        <div class="callout callout-info">
          <form method="post" class="mb-0">
            <?= csrf_field() ?>
            <input type="hidden" name="reply_id" value="<?= $r['reply_id'] ?>">
            <label class="mb-1">Balasan admin <small class="text-muted"><?= e(date('d M Y H:i', strtotime($r['replied_at']))) ?></small></label>
            <textarea class="form-control mb-2" name="reply" rows="2"><?= e($r['reply']) ?></textarea>
            <button class="btn btn-sm btn-outline-primary" name="action" value="edit_reply"><i class="fas fa-save"></i> Simpan</button>
            <button class="btn btn-sm btn-outline-danger" name="action" value="delete_reply" onclick="return confirm('Hapus balasan ini?')"><i class="fas fa-trash"></i> Hapus</button>
          </form>
        </div>
      <?php endforeach; ?>

      <form method="post">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="reply">
        <input type="hidden" name="feedback_id" value="<?= $f['id'] ?>">
        <div class="input-group">
          <textarea class="form-control" name="reply" rows="1" placeholder="Tulis balasan..." required></textarea>
          <div class="input-group-append"><button class="btn btn-primary" type="submit"><i class="fas fa-reply"></i> Balas</button></div>
        </div>
      </form>
    </div>
  </div>
<?php endforeach; ?>
<?php require __DIR__ . '/_footer.php'; ?>
