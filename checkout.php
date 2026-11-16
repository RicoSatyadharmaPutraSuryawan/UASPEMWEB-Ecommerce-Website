<?php
require_once __DIR__ . '/includes/functions.php';
require_user();

$user = current_user();
$cart = cart();
if (!$cart) {
    flash('info', 'Keranjang masih kosong.');
    redirect('cart.php');
}

// Ambil produk di keranjang
$ids = array_keys($cart);
$placeholders = implode(',', array_fill(0, count($ids), '?'));
$stmt = $conn->prepare("SELECT * FROM produk WHERE id IN ($placeholders)");
$stmt->bind_param(str_repeat('i', count($ids)), ...$ids);
$stmt->execute();
$items = [];
$total = 0;
foreach ($stmt->get_result() as $p) {
    $p['qty'] = $cart[$p['id']];
    $total += $p['qty'] * $p['harga_produk'];
    $items[] = $p;
}

$errors = [];
$data = [
    'nama' => $user['nama'],
    'no_hp' => $user['no_hp'],
    'alamat' => '',
    'metode' => 'transfer',
    'catatan' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    foreach ($data as $key => $_) {
        $data[$key] = trim($_POST[$key] ?? '');
    }

    if (mb_strlen($data['nama']) < 3) $errors[] = 'Nama penerima minimal 3 karakter.';
    if (!preg_match('/^(\+62|0)[0-9]{8,13}$/', $data['no_hp'])) $errors[] = 'Nomor HP tidak valid, contoh: 081234567890.';
    if (mb_strlen($data['alamat']) < 10) $errors[] = 'Alamat terlalu pendek, tulis alamat lengkap.';
    if (!in_array($data['metode'], ['transfer', 'cod'], true)) $errors[] = 'Pilih metode pembayaran.';

    if (!$errors) {
        $conn->begin_transaction();
        try {
            // Kunci baris produk agar stok tidak dipakai dua pesanan bersamaan
            $lock = $conn->prepare('SELECT id, nama_produk, harga_produk, stok_produk FROM produk WHERE id = ? FOR UPDATE');
            $fresh = [];
            $total = 0;
            foreach ($cart as $pid => $qty) {
                $lock->bind_param('i', $pid);
                $lock->execute();
                $row = $lock->get_result()->fetch_assoc();
                if (!$row || $row['stok_produk'] < $qty) {
                    throw new RuntimeException('Stok ' . ($row['nama_produk'] ?? 'produk') . ' tidak mencukupi. Silakan ubah jumlah di keranjang.');
                }
                $row['qty'] = $qty;
                $total += $qty * $row['harga_produk'];
                $fresh[] = $row;
            }

            $stmt = $conn->prepare('INSERT INTO orders (user_id, nama_penerima, no_hp, alamat, metode_pembayaran, catatan, total) VALUES (?, ?, ?, ?, ?, ?, ?)');
            $stmt->bind_param('isssssd', $user['id'], $data['nama'], $data['no_hp'], $data['alamat'], $data['metode'], $data['catatan'], $total);
            $stmt->execute();
            $orderId = $conn->insert_id;

            $insItem = $conn->prepare('INSERT INTO order_items (order_id, produk_id, nama_produk, harga, jumlah) VALUES (?, ?, ?, ?, ?)');
            $updStock = $conn->prepare('UPDATE produk SET stok_produk = stok_produk - ? WHERE id = ?');
            foreach ($fresh as $row) {
                $insItem->bind_param('iisdi', $orderId, $row['id'], $row['nama_produk'], $row['harga_produk'], $row['qty']);
                $insItem->execute();
                $updStock->bind_param('ii', $row['qty'], $row['id']);
                $updStock->execute();
            }

            $conn->commit();
            $_SESSION['cart'] = [];
            flash('success', 'Pesanan #' . $orderId . ' berhasil dibuat. Terima kasih sudah berbelanja!');
            redirect('my_orders.php?id=' . $orderId);
        } catch (RuntimeException $ex) {
            $conn->rollback();
            $errors[] = $ex->getMessage();
        }
    }
}

$pageTitle = 'Checkout';
$activePage = 'cart';
require __DIR__ . '/includes/header.php';
?>
<div class="container container-mid">
  <h1 class="page-title">Checkout</h1>
  <p class="page-subtitle">Isi data pengiriman, lalu buat pesanan.</p>

  <?php if ($errors): ?>
    <div class="alert alert-danger"><ul><?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul></div>
  <?php endif; ?>

  <div class="table-wrap" style="margin-bottom:24px">
    <table class="table">
      <thead><tr><th>Produk</th><th>Jumlah</th><th class="num">Subtotal</th></tr></thead>
      <tbody>
      <?php foreach ($items as $it): ?>
        <tr><td><?= e($it['nama_produk']) ?></td><td><?= (int) $it['qty'] ?></td><td class="num"><?= rupiah($it['qty'] * $it['harga_produk']) ?></td></tr>
      <?php endforeach; ?>
        <tr><th colspan="2">Total</th><th class="num"><?= rupiah($total) ?></th></tr>
      </tbody>
    </table>
  </div>

  <form method="post" class="card-form">
    <?= csrf_field() ?>
    <h2>Data Pengiriman</h2>
    <div class="form-group">
      <label for="nama">Nama Penerima</label>
      <input class="form-control" id="nama" name="nama" value="<?= e($data['nama']) ?>" required minlength="3">
    </div>
    <div class="form-group">
      <label for="no_hp">Nomor HP</label>
      <input class="form-control" id="no_hp" name="no_hp" value="<?= e($data['no_hp']) ?>" required placeholder="081234567890">
    </div>
    <div class="form-group">
      <label for="alamat">Alamat Lengkap</label>
      <textarea class="form-control" id="alamat" name="alamat" required minlength="10"><?= e($data['alamat']) ?></textarea>
    </div>
    <div class="form-group">
      <label>Metode Pembayaran</label>
      <div class="radio-group">
        <label><input type="radio" name="metode" value="transfer" <?= $data['metode'] === 'transfer' ? 'checked' : '' ?>> Transfer Bank</label>
        <label><input type="radio" name="metode" value="cod" <?= $data['metode'] === 'cod' ? 'checked' : '' ?>> Bayar di Tempat (COD)</label>
      </div>
    </div>
    <div class="form-group">
      <label for="catatan">Catatan (opsional)</label>
      <textarea class="form-control" id="catatan" name="catatan" style="min-height:70px"><?= e($data['catatan']) ?></textarea>
    </div>
    <button class="btn btn-block" type="submit"><i class="fas fa-check"></i> Buat Pesanan</button>
  </form>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
