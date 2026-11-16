<?php
require_once __DIR__ . '/../includes/functions.php';
require_admin();

// Hapus produk (POST + CSRF)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    verify_csrf();
    $id = (int) $_POST['id'];
    $stmt = $conn->prepare('SELECT gambar_produk FROM produk WHERE id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    if ($row = $stmt->get_result()->fetch_assoc()) {
        $stmt = $conn->prepare('DELETE FROM produk WHERE id = ?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        delete_product_image($row['gambar_produk']);
        flash('success', 'Produk berhasil dihapus.');
    }
    redirect('products.php');
}

$q = trim($_GET['q'] ?? '');
if ($q !== '') {
    $stmt = $conn->prepare('SELECT * FROM produk WHERE nama_produk LIKE ? ORDER BY id DESC');
    $like = "%$q%";
    $stmt->bind_param('s', $like);
    $stmt->execute();
    $products = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
} else {
    $products = $conn->query('SELECT * FROM produk ORDER BY id DESC')->fetch_all(MYSQLI_ASSOC);
}

$pageTitle = 'Produk';
$activeMenu = 'products';
require __DIR__ . '/_header.php';
?>
<div class="card">
  <div class="card-header d-flex align-items-center">
    <form class="form-inline" method="get">
      <input class="form-control mr-2" type="search" name="q" value="<?= e($q) ?>" placeholder="Cari nama produk">
      <button class="btn btn-outline-secondary" type="submit"><i class="fas fa-search"></i></button>
    </form>
    <a href="product_form.php" class="btn btn-primary ml-auto"><i class="fas fa-plus"></i> Tambah Produk</a>
  </div>
  <div class="card-body p-0 table-responsive">
    <table class="table table-hover mb-0">
      <thead><tr><th>Gambar</th><th>Nama</th><th class="text-right">Harga</th><th class="text-center">Stok</th><th style="width:170px">Aksi</th></tr></thead>
      <tbody>
      <?php if (!$products): ?><tr><td colspan="5" class="text-center text-muted">Belum ada produk.</td></tr><?php endif; ?>
      <?php foreach ($products as $p): ?>
        <tr>
          <td><img class="thumb" src="<?= e(product_image_url($p['gambar_produk'], '../')) ?>" alt=""></td>
          <td><?= e($p['nama_produk']) ?><br><small class="text-muted"><?= e(mb_strimwidth($p['deskripsi_produk'], 0, 70, '...')) ?></small></td>
          <td class="text-right"><?= rupiah($p['harga_produk']) ?></td>
          <td class="text-center"><span class="badge <?= $p['stok_produk'] > 1 ? 'badge-success' : ($p['stok_produk'] == 1 ? 'badge-warning' : 'badge-danger') ?>"><?= (int) $p['stok_produk'] ?></span></td>
          <td>
            <a href="product_form.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i> Edit</a>
            <form method="post" class="d-inline" onsubmit="return confirm('Hapus produk <?= e(addslashes($p['nama_produk'])) ?>?')">
              <?= csrf_field() ?>
              <input type="hidden" name="action" value="delete">
              <input type="hidden" name="id" value="<?= $p['id'] ?>">
              <button class="btn btn-sm btn-danger" type="submit"><i class="fas fa-trash"></i></button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php require __DIR__ . '/_footer.php'; ?>
