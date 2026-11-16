<?php
require_once __DIR__ . '/includes/functions.php';

$id = (int) ($_GET['id'] ?? 0);
$stmt = $conn->prepare('SELECT * FROM produk WHERE id = ?');
$stmt->bind_param('i', $id);
$stmt->execute();
$p = $stmt->get_result()->fetch_assoc();

if (!$p) {
    http_response_code(404);
    $pageTitle = 'Produk tidak ditemukan';
    require __DIR__ . '/includes/header.php';
    echo '<div class="container empty"><i class="fas fa-search"></i>Produk tidak ditemukan.<br><br><a class="btn" href="product_list.php">Kembali ke daftar produk</a></div>';
    require __DIR__ . '/includes/footer.php';
    exit;
}

$inCart = cart()[$p['id']] ?? 0;
$pageTitle = $p['nama_produk'];
$activePage = 'produk';
require __DIR__ . '/includes/header.php';
?>
<div class="container">
  <p class="text-muted" style="margin-bottom:20px"><a href="product_list.php">Produk</a> / <?= e($p['nama_produk']) ?></p>
  <div class="product-detail">
    <img src="<?= e(product_image_url($p['gambar_produk'])) ?>" alt="<?= e($p['nama_produk']) ?>">
    <div>
      <h1><?= e($p['nama_produk']) ?></h1>
      <div class="price"><?= rupiah($p['harga_produk']) ?></div>
      <?php if ($p['stok_produk'] > 0): ?>
        <p>Stok tersedia: <strong><?= (int) $p['stok_produk'] ?></strong>
          <?= $inCart ? '<span class="text-muted">(' . (int) $inCart . ' di keranjang)</span>' : '' ?></p>
      <?php else: ?>
        <p class="sold-out">Stok habis</p>
      <?php endif; ?>
      <div class="desc"><?= e($p['deskripsi_produk']) ?></div>

      <?php if ($p['stok_produk'] > 0): ?>
      <form method="post" action="cart.php" class="qty-row">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="add">
        <input type="hidden" name="product_id" value="<?= (int) $p['id'] ?>">
        <label for="qty">Jumlah</label>
        <input id="qty" class="form-control" type="number" name="qty" value="1" min="1" max="<?= (int) $p['stok_produk'] ?>">
        <button class="btn" type="submit"><i class="fas fa-cart-plus"></i> Tambah ke Keranjang</button>
      </form>
      <?php endif; ?>
    </div>
  </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
