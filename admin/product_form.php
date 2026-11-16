<?php
require_once __DIR__ . '/../includes/functions.php';
require_admin();

// Satu form untuk tambah (tanpa id) dan edit (dengan id)
$id = (int) ($_GET['id'] ?? 0);
$product = ['nama_produk' => '', 'deskripsi_produk' => '', 'harga_produk' => '', 'stok_produk' => 1, 'gambar_produk' => ''];

if ($id) {
    $stmt = $conn->prepare('SELECT * FROM produk WHERE id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $product = $stmt->get_result()->fetch_assoc();
    if (!$product) {
        flash('danger', 'Produk tidak ditemukan.');
        redirect('products.php');
    }
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $product['nama_produk'] = trim($_POST['nama_produk'] ?? '');
    $product['deskripsi_produk'] = trim($_POST['deskripsi_produk'] ?? '');
    $product['harga_produk'] = $_POST['harga_produk'] ?? '';
    $product['stok_produk'] = $_POST['stok_produk'] ?? '';

    if (mb_strlen($product['nama_produk']) < 3) $errors[] = 'Nama produk minimal 3 karakter.';
    if (mb_strlen($product['deskripsi_produk']) < 10) $errors[] = 'Deskripsi minimal 10 karakter.';
    if (!is_numeric($product['harga_produk']) || $product['harga_produk'] <= 0 || $product['harga_produk'] > 100000000) $errors[] = 'Harga harus angka antara 1 dan 100.000.000.';
    if (filter_var($product['stok_produk'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]) === false) $errors[] = 'Stok harus bilangan bulat, minimal 0.';

    $hasNewImage = ($_FILES['gambar_produk']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE;
    if (!$id && !$hasNewImage) $errors[] = 'Gambar produk wajib diunggah.';

    $newImage = null;
    if (!$errors && $hasNewImage) {
        try {
            $newImage = upload_product_image($_FILES['gambar_produk']);
        } catch (RuntimeException $ex) {
            $errors[] = $ex->getMessage();
        }
    }

    if (!$errors) {
        $harga = (float) $product['harga_produk'];
        $stok = (int) $product['stok_produk'];
        if ($id) {
            $image = $newImage ?? $product['gambar_produk'];
            $stmt = $conn->prepare('UPDATE produk SET nama_produk = ?, deskripsi_produk = ?, harga_produk = ?, stok_produk = ?, gambar_produk = ? WHERE id = ?');
            $stmt->bind_param('ssdisi', $product['nama_produk'], $product['deskripsi_produk'], $harga, $stok, $image, $id);
            $stmt->execute();
            if ($newImage) delete_product_image($product['gambar_produk']);
            flash('success', 'Produk berhasil diperbarui.');
        } else {
            $stmt = $conn->prepare('INSERT INTO produk (nama_produk, deskripsi_produk, harga_produk, stok_produk, gambar_produk) VALUES (?, ?, ?, ?, ?)');
            $stmt->bind_param('ssdis', $product['nama_produk'], $product['deskripsi_produk'], $harga, $stok, $newImage);
            $stmt->execute();
            flash('success', 'Produk berhasil ditambahkan.');
        }
        redirect('products.php');
    }
}

$pageTitle = $id ? 'Edit Produk' : 'Tambah Produk';
$activeMenu = 'products';
require __DIR__ . '/_header.php';
?>
<div class="card card-primary" style="max-width:760px">
  <form method="post" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <div class="card-body">
      <?php if ($errors): ?>
        <div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul></div>
      <?php endif; ?>
      <div class="form-group">
        <label for="nama_produk">Nama Produk</label>
        <input class="form-control" id="nama_produk" name="nama_produk" value="<?= e($product['nama_produk']) ?>" required maxlength="150">
      </div>
      <div class="form-group">
        <label for="deskripsi_produk">Deskripsi (kondisi, ukuran, bahan)</label>
        <textarea class="form-control" id="deskripsi_produk" name="deskripsi_produk" rows="4" required><?= e($product['deskripsi_produk']) ?></textarea>
      </div>
      <div class="form-row">
        <div class="form-group col-md-6">
          <label for="harga_produk">Harga (Rp)</label>
          <input class="form-control" type="number" id="harga_produk" name="harga_produk" min="1" step="1" value="<?= e($product['harga_produk'] === '' ? '' : (int) $product['harga_produk']) ?>" required>
        </div>
        <div class="form-group col-md-6">
          <label for="stok_produk">Stok</label>
          <input class="form-control" type="number" id="stok_produk" name="stok_produk" min="0" step="1" value="<?= e($product['stok_produk']) ?>" required>
        </div>
      </div>
      <div class="form-group">
        <label for="gambar_produk">Gambar <?= $id ? '(kosongkan jika tidak diganti)' : '' ?></label>
        <?php if ($id && $product['gambar_produk']): ?>
          <div class="mb-2"><img class="thumb" style="width:100px;height:100px" src="<?= e(product_image_url($product['gambar_produk'], '../')) ?>" alt=""></div>
        <?php endif; ?>
        <input class="form-control-file" type="file" id="gambar_produk" name="gambar_produk" accept="image/jpeg,image/png,image/gif,image/webp" <?= $id ? '' : 'required' ?>>
        <small class="form-text text-muted">JPG, PNG, GIF, atau WEBP. Maksimal 2 MB.</small>
      </div>
    </div>
    <div class="card-footer">
      <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button>
      <a href="products.php" class="btn btn-secondary">Batal</a>
    </div>
  </form>
</div>
<?php require __DIR__ . '/_footer.php'; ?>
