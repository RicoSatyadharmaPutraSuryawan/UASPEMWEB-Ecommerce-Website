<?php /** Kartu produk. Butuh variabel $p (baris tabel produk). */ ?>
<article class="product-card">
  <a href="product_detail.php?id=<?= (int) $p['id'] ?>">
    <img src="<?= e(product_image_url($p['gambar_produk'])) ?>" alt="<?= e($p['nama_produk']) ?>" loading="lazy">
  </a>
  <div class="body">
    <h3><?= e($p['nama_produk']) ?></h3>
    <div class="price"><?= rupiah($p['harga_produk']) ?></div>
    <?php if ($p['stok_produk'] > 0): ?>
      <div class="stock">Stok: <?= (int) $p['stok_produk'] ?></div>
    <?php else: ?>
      <div class="sold-out">Habis</div>
    <?php endif; ?>
    <a href="product_detail.php?id=<?= (int) $p['id'] ?>" class="btn btn-sm">Lihat Detail</a>
  </div>
</article>
