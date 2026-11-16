<?php
/**
 * Header halaman toko. Sebelum include, set $pageTitle (opsional) dan $activePage (opsional).
 */
$pageTitle = $pageTitle ?? 'Driptique';
$activePage = $activePage ?? '';
$user = current_user();
$nav = fn(string $page) => $activePage === $page ? 'active' : '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($pageTitle) ?> | Driptique</title>
  <link rel="icon" href="aset-gambar/DRIPTIQUE.svg">
  <link rel="stylesheet" href="plugins/fontawesome-free/css/all.min.css">
  <link rel="stylesheet" href="assets/css/store.css">
</head>
<body>
<nav class="navbar" id="navbar">
  <div class="navbar-container">
    <ul class="navbar-nav">
      <li><a href="index.php" class="<?= $nav('home') ?>">Home</a></li>
      <li><a href="product_list.php" class="<?= $nav('produk') ?>">Produk</a></li>
      <li><a href="about.php" class="<?= $nav('about') ?>">About</a></li>
      <li><a href="feedback_form.php" class="<?= $nav('feedback') ?>">Feedback</a></li>
    </ul>

    <a href="index.php" class="navbar-logo"><img src="aset-gambar/DRIPTIQUE.svg" alt="Driptique"></a>
    <button class="menu-toggle" type="button" aria-label="Buka menu"
            onclick="document.getElementById('navbar').classList.toggle('open')"><i class="fas fa-bars"></i></button>

    <ul class="navbar-nav right">
      <li><a href="cart.php" class="<?= $nav('cart') ?>"><i class="fas fa-shopping-bag"></i> Keranjang
        <?php if (cart_count() > 0): ?><span class="badge"><?= cart_count() ?></span><?php endif; ?></a></li>
      <?php if ($user): ?>
        <li><a href="my_orders.php" class="<?= $nav('orders') ?>">Pesanan Saya</a></li>
        <li><a href="logout.php" title="Logout"><i class="fas fa-sign-out-alt"></i> <?= e(strtok($user['nama'], ' ')) ?></a></li>
      <?php else: ?>
        <li><a href="login.php" class="<?= $nav('login') ?>">Login</a></li>
        <li><a href="register.php" class="<?= $nav('register') ?>">Daftar</a></li>
      <?php endif; ?>
    </ul>
  </div>
</nav>
<main>
<?php foreach (get_flashes() as $f): ?>
  <div class="container" style="padding-bottom:0"><div class="alert alert-<?= e($f['type']) ?>"><?= e($f['message']) ?></div></div>
<?php endforeach; ?>
