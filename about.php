<?php
require_once __DIR__ . '/includes/functions.php';
$pageTitle = 'About';
$activePage = 'about';
require __DIR__ . '/includes/header.php';
?>
<div class="container about">
  <h1 class="page-title">Tentang Driptique</h1>
  <p class="page-subtitle">Thrift shop online untuk pakaian bekas pilihan.</p>
  <p>Driptique menjual pakaian thrift yang sudah dikurasi: dicek kondisinya, dicuci, difoto apa adanya,
     dan diberi ukuran yang jelas supaya pembeli tidak salah pilih.</p>
  <p>Kami percaya gaya yang bagus tidak harus mahal, dan memakai ulang pakaian adalah cara sederhana
     untuk mengurangi limbah tekstil.</p>
  <div class="about-images">
    <img src="aset-gambar/polo.png" alt="Koleksi Polo">
    <img src="aset-gambar/carhartt.png" alt="Koleksi Carhartt">
    <img src="aset-gambar/stussy.png" alt="Koleksi Stussy">
    <img src="aset-gambar/tnf.png" alt="Koleksi The North Face">
  </div>
  <a href="product_list.php" class="btn">Lihat Produk</a>
  <hr class="divider">
  <h2 class="page-title">Lokasi Kami</h2>
  <iframe class="map-frame mt-2" loading="lazy" referrerpolicy="no-referrer-when-downgrade"
    src="https://www.google.com/maps?q=UPN+Veteran+Jawa+Timur&output=embed" title="Peta lokasi"></iframe>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
