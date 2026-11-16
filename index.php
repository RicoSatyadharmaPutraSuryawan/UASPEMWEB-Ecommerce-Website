<?php
require_once __DIR__ . '/includes/functions.php';

// Tampilkan 4 produk terbaru yang masih ada stok
$latest = $conn->query('SELECT * FROM produk WHERE stok_produk > 0 ORDER BY created_at DESC, id DESC LIMIT 4')->fetch_all(MYSQLI_ASSOC);

$pageTitle = 'Home';
$activePage = 'home';
require __DIR__ . '/includes/header.php';
?>
<section class="hero">
  <h1>STAY DRIPPED, NEVER BROKE.</h1>
  <p>Selamat datang di Driptique, tempat pakaian thrift pilihan. Rapi, sadar lingkungan, dan tetap gaya.</p>
</section>

<section class="hero-content">
  <img src="aset-gambar/Frame 55.png" alt="Koleksi Driptique">
  <div>
    <p>Setiap barang yang kami jual sudah dicek kondisinya, difoto apa adanya, dan diberi ukuran yang jelas.
       Thrifting jadi lebih mudah: pilih, masukkan ke keranjang, dan pesan dari rumah.</p>
    <a href="product_list.php" class="btn">Lihat Produk <i class="fas fa-arrow-right"></i></a>
  </div>
</section>

<?php if ($latest): ?>
<section class="container">
  <hr class="divider">
  <h2 class="page-title">Produk Terbaru</h2>
  <p class="page-subtitle">Stok terbatas, satu barang biasanya hanya ada satu.</p>
  <div class="product-grid">
    <?php foreach ($latest as $p) include __DIR__ . '/includes/product_card.php'; ?>
  </div>
</section>
<?php endif; ?>
<?php require __DIR__ . '/includes/footer.php'; ?>
