<?php
require_once __DIR__ . '/includes/functions.php';

$q = trim($_GET['q'] ?? '');
$sort = $_GET['sort'] ?? 'terbaru';
$orderBy = [
    'terbaru' => 'created_at DESC, id DESC',
    'termurah' => 'harga_produk ASC',
    'termahal' => 'harga_produk DESC',
    'nama' => 'nama_produk ASC',
][$sort] ?? 'created_at DESC, id DESC';

if ($q !== '') {
    $stmt = $conn->prepare("SELECT * FROM produk WHERE nama_produk LIKE ? OR deskripsi_produk LIKE ? ORDER BY $orderBy");
    $like = '%' . $q . '%';
    $stmt->bind_param('ss', $like, $like);
    $stmt->execute();
    $products = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
} else {
    $products = $conn->query("SELECT * FROM produk ORDER BY $orderBy")->fetch_all(MYSQLI_ASSOC);
}

$pageTitle = 'Produk';
$activePage = 'produk';
require __DIR__ . '/includes/header.php';
?>
<div class="container">
  <h1 class="page-title">Semua Produk</h1>
  <p class="page-subtitle"><?= count($products) ?> produk<?= $q !== '' ? ' untuk "' . e($q) . '"' : '' ?></p>

  <form class="toolbar" method="get">
    <input class="form-control" type="search" name="q" value="<?= e($q) ?>" placeholder="Cari produk, misalnya jaket atau hoodie">
    <select class="form-control" name="sort" onchange="this.form.submit()">
      <option value="terbaru" <?= $sort === 'terbaru' ? 'selected' : '' ?>>Terbaru</option>
      <option value="termurah" <?= $sort === 'termurah' ? 'selected' : '' ?>>Harga termurah</option>
      <option value="termahal" <?= $sort === 'termahal' ? 'selected' : '' ?>>Harga termahal</option>
      <option value="nama" <?= $sort === 'nama' ? 'selected' : '' ?>>Nama A-Z</option>
    </select>
    <button class="btn" type="submit"><i class="fas fa-search"></i> Cari</button>
  </form>

  <?php if ($products): ?>
    <div class="product-grid">
      <?php foreach ($products as $p) include __DIR__ . '/includes/product_card.php'; ?>
    </div>
  <?php else: ?>
    <div class="empty"><i class="fas fa-box-open"></i>Produk tidak ditemukan.</div>
  <?php endif; ?>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
