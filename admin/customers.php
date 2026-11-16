<?php
require_once __DIR__ . '/../includes/functions.php';
require_admin();

$customers = $conn->query('SELECT u.id, u.nama, u.email, u.no_hp, u.created_at,
                                  COUNT(o.id) AS jumlah_pesanan,
                                  COALESCE(SUM(CASE WHEN o.status <> "dibatalkan" THEN o.total END), 0) AS total_belanja
                           FROM users u LEFT JOIN orders o ON o.user_id = u.id
                           GROUP BY u.id ORDER BY u.created_at DESC')->fetch_all(MYSQLI_ASSOC);

$pageTitle = 'Pelanggan';
$activeMenu = 'customers';
require __DIR__ . '/_header.php';
?>
<div class="card">
  <div class="card-body p-0 table-responsive">
    <table class="table table-hover mb-0">
      <thead><tr><th>Nama</th><th>Email</th><th>No. HP</th><th>Terdaftar</th><th class="text-center">Pesanan</th><th class="text-right">Total Belanja</th></tr></thead>
      <tbody>
      <?php if (!$customers): ?><tr><td colspan="6" class="text-center text-muted">Belum ada pelanggan.</td></tr><?php endif; ?>
      <?php foreach ($customers as $c): ?>
        <tr>
          <td><?= e($c['nama']) ?></td><td><?= e($c['email']) ?></td><td><?= e($c['no_hp']) ?></td>
          <td><?= e(date('d M Y', strtotime($c['created_at']))) ?></td>
          <td class="text-center"><?= (int) $c['jumlah_pesanan'] ?></td>
          <td class="text-right"><?= rupiah($c['total_belanja']) ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php require __DIR__ . '/_footer.php'; ?>
