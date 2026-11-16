<?php
/**
 * Layout atas halaman admin (AdminLTE). Set $pageTitle dan $activeMenu sebelum include.
 */
$pageTitle = $pageTitle ?? 'Dashboard';
$activeMenu = $activeMenu ?? '';
$admin = current_admin();
$menu = [
    'dashboard' => ['index.php', 'fas fa-tachometer-alt', 'Dashboard'],
    'products'  => ['products.php', 'fas fa-tshirt', 'Produk'],
    'orders'    => ['orders.php', 'fas fa-receipt', 'Pesanan'],
    'customers' => ['customers.php', 'fas fa-users', 'Pelanggan'],
    'feedback'  => ['feedback.php', 'fas fa-comments', 'Feedback'],
    'admins'    => ['admins.php', 'fas fa-user-shield', 'Akun Admin'],
];
$pendingOrders = (int) $conn->query("SELECT COUNT(*) FROM orders WHERE status = 'menunggu'")->fetch_row()[0];
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($pageTitle) ?> | Admin Driptique</title>
  <link rel="icon" href="../aset-gambar/DRIPTIQUE.svg">
  <link rel="stylesheet" href="../plugins/fontawesome-free/css/all.min.css">
  <link rel="stylesheet" href="../dist/css/adminlte.min.css">
  <style>
    .main-sidebar, .main-sidebar .sidebar { background-color: #1f1f3a; }
    .brand-link .brand-image { background: #fff; padding: 2px; }
    .table td, .table th { vertical-align: middle; }
    .thumb { width: 56px; height: 56px; object-fit: cover; border-radius: 6px; }
    .badge-menunggu { background: #ffc107; } .badge-diproses { background: #17a2b8; color: #fff; }
    .badge-dikirim { background: #6f42c1; color: #fff; } .badge-selesai { background: #28a745; color: #fff; }
    .badge-dibatalkan { background: #dc3545; color: #fff; }
  </style>
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">
  <nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <ul class="navbar-nav">
      <li class="nav-item"><a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a></li>
      <li class="nav-item d-none d-sm-inline-block"><a href="../index.php" class="nav-link" target="_blank"><i class="fas fa-store"></i> Lihat Toko</a></li>
    </ul>
    <ul class="navbar-nav ml-auto">
      <li class="nav-item"><span class="nav-link"><i class="fas fa-user-circle"></i> <?= e($admin['username']) ?></span></li>
      <li class="nav-item"><a class="nav-link" href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
    </ul>
  </nav>

  <aside class="main-sidebar sidebar-dark-primary elevation-4">
    <a href="index.php" class="brand-link">
      <img src="../aset-gambar/DRIPTIQUE.svg" alt="Driptique" class="brand-image img-circle elevation-3">
      <span class="brand-text font-weight-light">DRIPTIQUE Admin</span>
    </a>
    <div class="sidebar">
      <nav class="mt-3">
        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu">
          <?php foreach ($menu as $key => [$href, $icon, $label]): ?>
            <li class="nav-item">
              <a href="<?= $href ?>" class="nav-link <?= $activeMenu === $key ? 'active' : '' ?>">
                <i class="nav-icon <?= $icon ?>"></i>
                <p><?= $label ?>
                  <?php if ($key === 'orders' && $pendingOrders): ?><span class="right badge badge-warning"><?= $pendingOrders ?></span><?php endif; ?>
                </p>
              </a>
            </li>
          <?php endforeach; ?>
        </ul>
      </nav>
    </div>
  </aside>

  <div class="content-wrapper">
    <div class="content-header">
      <div class="container-fluid"><h1 class="m-0"><?= e($pageTitle) ?></h1></div>
    </div>
    <section class="content">
      <div class="container-fluid">
        <?php foreach (get_flashes() as $f): ?>
          <div class="alert alert-<?= e($f['type']) ?> alert-dismissible">
            <button type="button" class="close" data-dismiss="alert">&times;</button><?= e($f['message']) ?>
          </div>
        <?php endforeach; ?>
