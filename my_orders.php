<?php
require_once __DIR__ . '/includes/functions.php';
require_user();

$user = current_user();
$statuses = order_statuses();
$id = (int) ($_GET['id'] ?? 0);

$pageTitle = 'Pesanan Saya';
$activePage = 'orders';

if ($id) {
    // Detail satu pesanan, hanya milik user yang login
    $stmt = $conn->prepare('SELECT * FROM orders WHERE id = ? AND user_id = ?');
    $stmt->bind_param('ii', $id, $user['id']);
    $stmt->execute();
    $order = $stmt->get_result()->fetch_assoc();
    if (!$order) {
        flash('danger', 'Pesanan tidak ditemukan.');
        redirect('my_orders.php');
    }
    $stmt = $conn->prepare('SELECT * FROM order_items WHERE order_id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    require __DIR__ . '/includes/header.php';
    ?>
    <div class="container container-mid">
      <p class="text-muted" style="margin-bottom:12px"><a href="my_orders.php">Pesanan Saya</a> / #<?= $order['id'] ?></p>
      <h1 class="page-title">Pesanan #<?= $order['id'] ?>
        <span class="status status-<?= e($order['status']) ?>"><?= e($statuses[$order['status']]) ?></span></h1>
      <p class="page-subtitle">Dibuat <?= e(date('d M Y H:i', strtotime($order['created_at']))) ?></p>

      <div class="table-wrap">
        <table class="table">
          <thead><tr><th>Produk</th><th class="num">Harga</th><th>Jumlah</th><th class="num">Subtotal</th></tr></thead>
          <tbody>
          <?php foreach ($items as $it): ?>
            <tr><td><?= e($it['nama_produk']) ?></td><td class="num"><?= rupiah($it['harga']) ?></td>
                <td><?= (int) $it['jumlah'] ?></td><td class="num"><?= rupiah($it['harga'] * $it['jumlah']) ?></td></tr>
          <?php endforeach; ?>
            <tr><th colspan="3">Total</th><th class="num"><?= rupiah($order['total']) ?></th></tr>
          </tbody>
        </table>
      </div>

      <div class="card-form mt-3">
        <h2>Pengiriman</h2>
        <p><strong><?= e($order['nama_penerima']) ?></strong> (<?= e($order['no_hp']) ?>)</p>
        <p style="white-space:pre-line"><?= e($order['alamat']) ?></p>
        <p class="mt-2">Pembayaran: <strong><?= $order['metode_pembayaran'] === 'cod' ? 'Bayar di Tempat (COD)' : 'Transfer Bank' ?></strong></p>
        <?php if ($order['metode_pembayaran'] === 'transfer' && $order['status'] === 'menunggu'): ?>
          <div class="alert alert-info mt-2">Silakan transfer <?= rupiah($order['total']) ?> ke BCA 1234567890 a.n. Driptique,
            lalu kirim bukti transfer ke WhatsApp toko dengan menyebutkan nomor pesanan #<?= $order['id'] ?>.</div>
        <?php endif; ?>
        <?php if ($order['catatan']): ?><p class="mt-2">Catatan: <?= e($order['catatan']) ?></p><?php endif; ?>
      </div>
    </div>
    <?php
    require __DIR__ . '/includes/footer.php';
    exit;
}

// Daftar pesanan
$stmt = $conn->prepare('SELECT o.*, (SELECT SUM(jumlah) FROM order_items WHERE order_id = o.id) AS jumlah_barang
                        FROM orders o WHERE user_id = ? ORDER BY created_at DESC, id DESC');
$stmt->bind_param('i', $user['id']);
$stmt->execute();
$orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

require __DIR__ . '/includes/header.php';
?>
<div class="container container-mid">
  <h1 class="page-title">Pesanan Saya</h1>
  <p class="page-subtitle">Riwayat dan status pesananmu.</p>
  <?php if (!$orders): ?>
    <div class="empty"><i class="fas fa-receipt"></i>Belum ada pesanan.<br><br><a class="btn" href="product_list.php">Mulai Belanja</a></div>
  <?php else: ?>
    <div class="table-wrap">
      <table class="table">
        <thead><tr><th>No.</th><th>Tanggal</th><th>Barang</th><th class="num">Total</th><th>Status</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($orders as $o): ?>
          <tr>
            <td>#<?= $o['id'] ?></td>
            <td><?= e(date('d M Y', strtotime($o['created_at']))) ?></td>
            <td><?= (int) $o['jumlah_barang'] ?></td>
            <td class="num"><?= rupiah($o['total']) ?></td>
            <td><span class="status status-<?= e($o['status']) ?>"><?= e($statuses[$o['status']]) ?></span></td>
            <td><a href="my_orders.php?id=<?= $o['id'] ?>">Detail</a></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
