<?php
/**
 * Fungsi bantu yang dipakai di seluruh aplikasi.
 */
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax']);
    session_start();
}

require_once __DIR__ . '/../db.php';

/** Escape output HTML untuk mencegah XSS. */
function e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

/** Format angka menjadi rupiah, contoh: Rp150.000 */
function rupiah($amount): string
{
    return 'Rp' . number_format((float) $amount, 0, ',', '.');
}

function redirect(string $url): void
{
    header('Location: ' . $url);
    exit;
}

/* ---------- Pesan flash (tampil sekali setelah redirect) ---------- */
function flash(string $type, string $message): void
{
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

function get_flashes(): array
{
    $messages = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $messages;
}

/* ---------- Proteksi CSRF untuk semua form POST ---------- */
function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . csrf_token() . '">';
}

function verify_csrf(): void
{
    $token = $_POST['csrf_token'] ?? '';
    if (!hash_equals(csrf_token(), $token)) {
        http_response_code(419);
        exit('Sesi formulir kedaluwarsa. Muat ulang halaman lalu coba lagi.');
    }
}

/* ---------- Autentikasi ---------- */
function current_admin(): ?array
{
    // Abaikan sisa session dari versi lama (dulu berisi angka id, bukan array)
    return is_array($_SESSION['admin'] ?? null) ? $_SESSION['admin'] : null;
}

function current_user(): ?array
{
    return is_array($_SESSION['user'] ?? null) ? $_SESSION['user'] : null;
}

/** Wajib dipanggil di awal setiap halaman admin. */
function require_admin(): void
{
    if (!current_admin()) {
        flash('warning', 'Silakan login sebagai admin terlebih dahulu.');
        redirect('login.php');
    }
}

/** Wajib login customer, misalnya untuk checkout. */
function require_user(): void
{
    if (!current_user()) {
        flash('warning', 'Silakan login untuk melanjutkan.');
        redirect('login.php?next=' . urlencode($_SERVER['REQUEST_URI']));
    }
}

/* ---------- Keranjang (disimpan di session) ---------- */
function cart(): array
{
    return is_array($_SESSION['cart'] ?? null) ? $_SESSION['cart'] : [];
}

function cart_count(): int
{
    return array_sum(cart());
}

/* ---------- Upload gambar produk ---------- */
/**
 * Menyimpan gambar ke images/uploads dengan nama acak.
 * Mengembalikan path relatif (uploads/xxx.jpg) atau melempar Exception dengan pesan untuk pengguna.
 */
function upload_product_image(array $file): string
{
    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Gambar gagal diunggah.');
    }
    if ($file['size'] > 2 * 1024 * 1024) {
        throw new RuntimeException('Ukuran gambar maksimal 2 MB.');
    }
    $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif', 'image/webp' => 'webp'];
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
    if (!isset($allowed[$mime])) {
        throw new RuntimeException('Format gambar harus JPG, PNG, GIF, atau WEBP.');
    }
    $name = 'uploads/' . bin2hex(random_bytes(8)) . '.' . $allowed[$mime];
    if (!move_uploaded_file($file['tmp_name'], __DIR__ . '/../images/' . $name)) {
        throw new RuntimeException('Gambar gagal disimpan ke server.');
    }
    return $name;
}

/** Hapus file gambar produk lama (hanya file hasil upload). */
function delete_product_image(?string $name): void
{
    if ($name && str_starts_with($name, 'uploads/')) {
        $path = __DIR__ . '/../images/uploads/' . basename($name);
        if (is_file($path)) {
            unlink($path);
        }
    }
}

/** Label dan warna status pesanan. */
function order_statuses(): array
{
    return [
        'menunggu'   => 'Menunggu Pembayaran',
        'diproses'   => 'Diproses',
        'dikirim'    => 'Dikirim',
        'selesai'    => 'Selesai',
        'dibatalkan' => 'Dibatalkan',
    ];
}

/** URL gambar produk (aman untuk nama file yang mengandung spasi). */
function product_image_url(string $name, string $prefix = ''): string
{
    return $prefix . 'images/' . implode('/', array_map('rawurlencode', explode('/', $name)));
}
