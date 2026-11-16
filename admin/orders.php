<?php
require_once __DIR__ . '/../includes/functions.php';
require_admin();

$statuses = order_statuses();

// Ubah status pesanan
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $id = (int) $_POST['id'];
    $new = $_POST['status'] ?? '';
    if (!isset($statuses[$new])) {
        flash('danger', 'Status tidak valid.');
        redirect('orders.php?id=' . $id);
    }

    $conn->begin_transaction();
    $stmt = $conn->prepare('SELECT status FROM orders WHERE id = ? FOR UPDATE');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $old = $stmt->get_result()->fetch_row()[0] ?? null;

    if ($old === null) {
        $conn->rollback();
        flash('danger', 'Pesanan tidak ditemukan.');
    } elseif ($old === 'dibatalkan') {
        $conn->rollback();
        flash('warning', 'Pesanan yang sudah dibatalkan tidak bisa diubah lagi.');
    } else {
        if ($new === 'dibatalkan') {
            // Kembalikan stok barang ke produk
            $stmt = $conn->prepare('UPDATE produk p JOIN order_items i ON i.produk_id = p.id SET p.stok_produk = p.stok_produk + i.jumlah WHERE i.order_id = ?');
            $stmt->bind_param('i', $id);
            $stmt->execute();
        }
        $stmt = $conn->prepare('UPDATE orders SET status = ? WHERE id = ?');
        $stmt->bind_param('si', $new, $id);
        $stmt->execute();
        $conn->commit();
        flash('success', 'Status pesanan #' . $id . ' diubah menjadi ' . $statuses[$new] . '.');
    }
    redirect('orders.php?id=' . $id);
}

$id = (int) ($_GET['id'] ?? 0);
$pageTitle = 'Pesanan';
$activeMenu = 'orders';

if ($id) {
    $stmt = $conn->prepare('SELECT o.*, u.email FROM orders o JOIN users u ON u.id = o.user_id WHERE o.id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $order = $stmt->get_result()->fetch_assoc();
    if (!$order) {
        flash('danger', 'Pesanan tidak ditemukan.');
        redirect('orders.php');
    }
    $stmt = $conn->prepare('SELECT * FROM order_items WHERE order_id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    $pageTitle = 'Pesanan #' . $id;
    require __DIR__ . '/_header.php';
    ?>
    <a href="orders.php" class="btn btn-link px-0 mb-2">&larr; Semua pesanan</a>
    <div class="row">
      <div class="col-lg-8">
        <div class="card">
          <div class="card-header"><h3 class="card-title">Barang</h3></div>
          <div class="card-body p-0">
            <table class="table mb-0">
              <thead><tr><th>Produk</th><th class="text-right">Harga</th><th class="text-center">Jumlah</th><th class="text-right">Subtotal</th></tr></thead>
              <tbody>
              <?php foreach ($items as $it): ?>
                <tr><td><?= e($it['nama_produk']) ?><?= $it['produk_id'] ? '' : ' <small class="text-muted">(produk sudah dihapus)</small>' ?></td>
                  <td class="text-right"><?= rupiah($it['harga']) ?></td><td class="text-center"><?= (int) $it['jumlah'] ?></td>
                  <td class="text-right"><?= rupiah($it['harga'] * $it['jumlah']) ?></td></tr>
              <?php endforeach; ?>
              <tr><th colspan="3">Total</th><th class="text-right"><?= rupiah($order['total']) ?></th></tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
      <div class="col-lg-4">
        <div class="card">
          <div class="card-header"><h3 class="card-title">Info Pesanan</h3></div>
          <div class="card-body">
            <p class="mb-1"><strong><?= e($order['nama_penerima']) ?></strong></p>
            <p class="mb-1"><?= e($order['no_hp']) ?> &middot; <?= e($order['email']) ?></p>
            <p style="white-space:pre-line"><?= e($order['alamat']) ?></p>
            <p class="mb-1">Pembayaran: <strong><?= $order['metode_pembayaran'] === 'cod' ? 'COD' : 'Transfer Bank' ?></strong></p>
            <p class="mb-1">Tanggal: <?= e(date('d M Y H:i', strtotime($order['created_at']))) ?></p>
            <?php if ($order['catatan']): ?><p>Catatan: <?= e($order['catatan']) ?></p><?php endif; ?>
            <hr>
            <form method="post">
              <?= csrf_field() ?>
              <input type="hidden" name="id" value="<?= $order['id'] ?>">
              <label for="status">Status</label>
              <select class="form-control mb-2" id="status" name="status" <?= $order['status'] === 'dibatalkan' ? 'disabled' : '' ?>>
                <?php foreach ($statuses as $key => $label): ?>
                  <option value="<?= $key ?>" <?= $order['status'] === $key ? 'selected' : '' ?>><?= $label ?></option>
                <?php endforeach; ?>
              </select>
              <?php if ($order['status'] !== 'dibatalkan'): ?>
                <button class="btn btn-primary btn-block" type="submit" onclick="return this.form.status.value !== 'dibatalkan' || confirm('Batalkan pesanan? Stok akan dikembalikan.')">Simpan Status</button>
              <?php else: ?>
                <small class="text-muted">Pesanan dibatalkan, stok sudah dikembalikan.</small>
              <?php endif; ?>
            </form>
          </div>
        </div>
      </div>
    </div>
    <?php
    require __DIR__ . '/_footer.php';
    exit;
}

$filter = $_GET['status'] ?? '';
if (isset($statuses[$filter])) {
    $stmt = $conn->prepare('SELECT * FROM orders WHERE status = ? ORDER BY created_at DESC, id DESC');
    $stmt->bind_param('s', $filter);
    $stmt->execute();
    $orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
} else {
    $filter = '';
    $orders = $conn->query('SELECT * FROM orders ORDER BY created_at DESC, id DESC')->fetch_all(MYSQLI_ASSOC);
}

require __DIR__ . '/_header.php';
?>
<div class="card">
  <div class="card-header">
    <a href="orders.php" class="btn btn-sm <?= $filter === '' ? 'btn-dark' : 'btn-outline-dark' ?>">Semua</a>
    <?php foreach ($statuses as $key => $label): ?>
      <a href="orders.php?status=<?= $key ?>" class="btn btn-sm <?= $filter === $key ? 'btn-dark' : 'btn-outline-dark' ?>"><?= $label ?></a>
    <?php endforeach; ?>
  </div>
  <div class="card-body p-0 table-responsive">
    <table class="table table-hover mb-0">
      <thead><tr><th>No.</th><th>Tanggal</th><th>Penerima</th><th>Pembayaran</th><th class="text-right">Total</th><th>Status</th><th></th></tr></thead>
      <tbody>
      <?php if (!$orders): ?><tr><td colspan="7" class="text-center text-muted">Tidak ada pesanan.</td></tr><?php endif; ?>
      <?php foreach ($orders as $o): ?>
        <tr>
          <td>#<?= $o['id'] ?></td>
          <td><?= e(date('d M Y H:i', strtotime($o['created_at']))) ?></td>
          <td><?= e($o['nama_penerima']) ?></td>
          <td><?= $o['metode_pembayaran'] === 'cod' ? 'COD' : 'Transfer' ?></td>
          <td class="text-right"><?= rupiah($o['total']) ?></td>
          <td><span class="badge badge-<?= e($o['status']) ?>"><?= e($statuses[$o['status']]) ?></span></td>
          <td><a href="orders.php?id=<?= $o['id'] ?>" class="btn btn-sm btn-outline-primary">Detail</a></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php require __DIR__ . '/_footer.php'; ?>
