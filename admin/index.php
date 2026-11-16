<?php
require_once __DIR__ . '/../includes/functions.php';
require_admin();

$count = fn(string $sql) => (int) $conn->query($sql)->fetch_row()[0];
$stats = [
    ['Produk', $count('SELECT COUNT(*) FROM produk'), 'fas fa-tshirt', 'bg-info', 'products.php'],
    ['Pesanan Baru', $count("SELECT COUNT(*) FROM orders WHERE status = 'menunggu'"), 'fas fa-receipt', 'bg-warning', 'orders.php?status=menunggu'],
    ['Pelanggan', $count('SELECT COUNT(*) FROM users'), 'fas fa-users', 'bg-success', 'customers.php'],
    ['Feedback Belum Dibalas', $count('SELECT COUNT(*) FROM feedbacks f WHERE NOT EXISTS (SELECT 1 FROM admin_replies r WHERE r.feedback_id = f.id)'), 'fas fa-comments', 'bg-danger', 'feedback.php'],
];
$revenue = (float) $conn->query("SELECT COALESCE(SUM(total), 0) FROM orders WHERE status <> 'dibatalkan'")->fetch_row()[0];
$lowStock = $conn->query('SELECT id, nama_produk, stok_produk FROM produk WHERE stok_produk <= 1 ORDER BY stok_produk, nama_produk LIMIT 5')->fetch_all(MYSQLI_ASSOC);
$recent = $conn->query('SELECT o.id, o.nama_penerima, o.total, o.status, o.created_at FROM orders o ORDER BY o.created_at DESC, o.id DESC LIMIT 5')->fetch_all(MYSQLI_ASSOC);
$statuses = order_statuses();

$pageTitle = 'Dashboard';
$activeMenu = 'dashboard';
require __DIR__ . '/_header.php';
?>
<div class="row">
  <?php foreach ($stats as [$label, $value, $icon, $bg, $href]): ?>
    <div class="col-lg-3 col-6">
      <div class="small-box <?= $bg ?>">
        <div class="inner"><h3><?= $value ?></h3><p><?= $label ?></p></div>
        <div class="icon"><i class="<?= $icon ?>"></i></div>
        <a href="<?= $href ?>" class="small-box-footer">Lihat <i class="fas fa-arrow-circle-right"></i></a>
      </div>
    </div>
  <?php endforeach; ?>
</div>

<div class="row">
  <div class="col-lg-8">
    <div class="card">
      <div class="card-header"><h3 class="card-title">Pesanan Terbaru</h3>
        <div class="card-tools">Pendapatan (tanpa yang dibatalkan): <strong><?= rupiah($revenue) ?></strong></div></div>
      <div class="card-body p-0">
        <table class="table table-striped mb-0">
          <thead><tr><th>No.</th><th>Penerima</th><th>Tanggal</th><th class="text-right">Total</th><th>Status</th></tr></thead>
          <tbody>
          <?php if (!$recent): ?><tr><td colspan="5" class="text-center text-muted">Belum ada pesanan.</td></tr><?php endif; ?>
          <?php foreach ($recent as $o): ?>
            <tr>
              <td><a href="orders.php?id=<?= $o['id'] ?>">#<?= $o['id'] ?></a></td>
              <td><?= e($o['nama_penerima']) ?></td>
              <td><?= e(date('d M Y H:i', strtotime($o['created_at']))) ?></td>
              <td class="text-right"><?= rupiah($o['total']) ?></td>
              <td><span class="badge badge-<?= e($o['status']) ?>"><?= e($statuses[$o['status']]) ?></span></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
  <div class="col-lg-4">
    <div class="card">
      <div class="card-header"><h3 class="card-title">Stok Menipis</h3></div>
      <ul class="list-group list-group-flush">
        <?php if (!$lowStock): ?><li class="list-group-item text-muted">Semua stok aman.</li><?php endif; ?>
        <?php foreach ($lowStock as $p): ?>
          <li class="list-group-item d-flex justify-content-between">
            <a href="product_form.php?id=<?= $p['id'] ?>"><?= e($p['nama_produk']) ?></a>
            <span class="badge <?= $p['stok_produk'] ? 'badge-warning' : 'badge-danger' ?>"><?= (int) $p['stok_produk'] ?></span>
          </li>
        <?php endforeach; ?>
      </ul>
    </div>
  </div>
</div>
<?php require __DIR__ . '/_footer.php'; ?>
