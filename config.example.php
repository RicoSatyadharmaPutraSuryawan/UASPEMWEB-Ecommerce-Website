<?php
/**
 * Contoh konfigurasi. Salin file ini menjadi config.php, lalu isi sesuai MySQL di komputermu.
 * config.php tidak ikut di-push ke GitHub (lihat .gitignore), jadi tiap orang punya password sendiri.
 */
return [
    'db_host' => 'localhost',
    'db_user' => 'root',
    'db_pass' => '',          // isi password root MySQL-mu
    'db_name' => 'db_uas',
    'debug'   => true,        // false saat demo/produksi supaya error tidak tampil ke pengguna
];
