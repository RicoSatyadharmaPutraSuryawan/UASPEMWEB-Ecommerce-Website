<?php
/**
 * Koneksi database tunggal. Semua halaman memakai file ini.
 * Pengaturan diambil dari config.php (tidak di-commit).
 */
$configFile = __DIR__ . '/config.php';
if (!file_exists($configFile)) {
    http_response_code(500);
    exit('File config.php belum ada. Salin config.example.php menjadi config.php lalu isi password MySQL.');
}
$config = require $configFile;

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
try {
    $conn = new mysqli($config['db_host'], $config['db_user'], $config['db_pass'], $config['db_name']);
    $conn->set_charset('utf8mb4');
} catch (mysqli_sql_exception $e) {
    http_response_code(500);
    exit(!empty($config['debug'])
        ? 'Koneksi database gagal: ' . htmlspecialchars($e->getMessage())
        : 'Koneksi database gagal. Silakan coba lagi nanti.');
}

if (empty($config['debug'])) {
    ini_set('display_errors', '0');
}
