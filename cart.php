<?php
require_once __DIR__ . '/includes/functions.php';

/** Ambil stok terbaru produk dari database. */
function product_stock(mysqli $conn, int $id): ?array
{
    $stmt = $conn->prepare('SELECT id, nama_produk, stok_produk FROM produk WHERE id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc() ?: null;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = $_POST['action'] ?? '';
    $id = (int) ($_POST['product_id'] ?? 0);
    $qty = max(1, (int) ($_POST['qty'] ?? 1));
    $cart = cart();

    if ($action === 'add' || $action === 'update') {
        $product = product_stock($conn, $id);
        if (!$product) {
            flash('danger', 'Produk tidak ditemukan.');
        } else {
            $newQty = $action === 'add' ? ($cart[$id] ?? 0) + $qty : $qty;
            if ($newQty > $product['stok_produk']) {
                $newQty = (int) $product['stok_produk'];
                flash('warning', 'Jumlah disesuaikan dengan stok yang tersedia (' . $newQty . ').');
            }
            if ($newQty > 0) {
                $cart[$id] = $newQty;
                if ($action === 'add') {
                    flash('success', $product['nama_produk'] . ' ditambahkan ke keranjang.');
                }
            } else {
                unset($cart[$id]);
                flash('danger', 'Stok ' . $product['nama_produk'] . ' sudah habis.');
            }
        }
    } elseif ($action === 'remove') {
        unset($cart[$id]);
        flash('info', 'Produk dihapus dari keranjang.');
    } elseif ($action === 'clear') {
        $cart = [];
    }

    $_SESSION['cart'] = $cart;
    redirect($action === 'add' ? 'product_detail.php?id=' . $id : 'cart.php');
}

// Tampilkan isi keranjang
$items = [];
$total = 0;
if ($cart = cart()) {
    $ids = array_keys($cart);
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $conn->prepare("SELECT * FROM produk WHERE id IN ($placeholders)");
    $stmt->bind_param(str_repeat('i', count($ids)), ...$ids);
    $stmt->execute();
    foreach ($stmt->get_result() as $p) {
        $p['qty'] = $cart[$p['id']];
        $p['subtotal'] = $p['qty'] * $p['harga_produk'];
        $total += $p['subtotal'];
        $items[] = $p;
    }
}

$pageTitle = 'Keranjang';
$activePage = 'cart';
require __DIR__ . '/includes/header.php';
?>
<div class="container container-mid">
  <h1 class="page-title">Keranjang Belanja</h1>
  <p class="page-subtitle">Periksa kembali barang sebelum checkout.</p>

  <?php if (!$items): ?>
    <div class="empty"><i class="fas fa-shopping-bag"></i>Keranjang masih kosong.<br><br>
      <a class="btn" href="product_list.php">Belanja Sekarang</a></div>
  <?php else: ?>
    <div class="table-wrap">
      <table class="table">
        <thead><tr><th>Produk</th><th class="num">Harga</th><th>Jumlah</th><th class="num">Subtotal</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($items as $it): ?>
          <tr>
            <td><div style="display:flex;gap:12px;align-items:center">
              <img src="<?= e(product_image_url($it['gambar_produk'])) ?>" alt="">
              <a href="product_detail.php?id=<?= (int) $it['id'] ?>"><?= e($it['nama_produk']) ?></a>
            </div></td>
            <td class="num"><?= rupiah($it['harga_produk']) ?></td>
            <td>
              <form method="post" style="display:flex;gap:6px">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="product_id" value="<?= (int) $it['id'] ?>">
                <input class="form-control" type="number" name="qty" value="<?= (int) $it['qty'] ?>" min="1" max="<?= (int) $it['stok_produk'] ?>">
                <button class="link-btn" type="submit">Ubah</button>
              </form>
            </td>
            <td class="num"><?= rupiah($it['subtotal']) ?></td>
            <td>
              <form method="post">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="remove">
                <input type="hidden" name="product_id" value="<?= (int) $it['id'] ?>">
                <button class="link-btn" type="submit" title="Hapus"><i class="fas fa-trash"></i></button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <div class="summary">
      <a href="product_list.php" class="btn btn-outline">Lanjut Belanja</a>
      <div class="total">Total: <?= rupiah($total) ?></div>
      <a href="checkout.php" class="btn">Checkout <i class="fas fa-arrow-right"></i></a>
    </div>
  <?php endif; ?>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
