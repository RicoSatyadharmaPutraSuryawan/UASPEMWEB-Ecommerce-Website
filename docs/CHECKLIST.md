# Checklist Uji Kompetensi & HKI – Driptique

Deadline: **Senin, 23 November 2026**. Centang (`[x]`) saat selesai, lalu commit.

## Sudah dikerjakan
- [x] Satu `db.php` + `config.php` lokal (password tidak ikut ke GitHub)
- [x] Semua query memakai prepared statement
- [x] Proteksi login di semua halaman admin, CSRF di semua form, hapus lewat POST
- [x] Upload gambar aman (cek tipe asli, maks 2 MB, nama acak)
- [x] Login & registrasi pelanggan di database (password di-hash)
- [x] Detail produk, pencarian, pengurutan
- [x] Keranjang, checkout, riwayat pesanan, stok berkurang otomatis
- [x] Panel admin: dashboard, produk, pesanan + status, pelanggan, feedback, akun admin
- [x] File `database/db_uas.sql` lengkap dengan data awal dan akun uji
- [x] Link rusak diperbaiki, file duplikat dan plugin template yang tidak dipakai dihapus
- [x] README: fitur, instalasi, akun uji, ERD

## Hari 1 – Keputusan (berdua)
- [ ] Sepakati pembagian tugas di bawah (isi nama di kolom PJ)
- [ ] **HKI:** hubungi pemilik repo asal (Antonyy004) – apakah ikut dicatat sebagai pencipta, atau minta izin tertulis
- [ ] **HKI:** tentukan nama aplikasi dan daftar pencipta
- [ ] **HKI:** ganti foto produk dan logo merek (Carhartt, Polo, Stussy, TNF) dengan foto milik sendiri atau produk tanpa merek
- [ ] **HKI:** ganti gambar `aset-gambar/Frame 55.png` jika bukan foto milik sendiri

## Uji di komputer masing-masing (PJ: ____ dan ____)
- [ ] Clone bersih, ikuti README dari nol, catat langkah yang membingungkan
- [ ] Uji alur pelanggan: daftar → login → cari → detail → keranjang → checkout → lihat pesanan
- [ ] Uji alur admin: login → tambah/edit/hapus produk → ubah status pesanan → batalkan pesanan (stok kembali) → balas feedback
- [ ] Uji tampilan di HP (Chrome DevTools, lebar 390 px)
- [ ] Uji input aneh: huruf di kolom harga, file bukan gambar, stok melebihi sisa

## Tambahan opsional (kerjakan jika waktu cukup)
- [ ] Kategori produk (tabel `kategori`, filter di halaman produk)
- [ ] Upload bukti transfer oleh pelanggan
- [ ] Pelanggan bisa membatalkan pesanan berstatus "Menunggu Pembayaran"
- [ ] Laporan penjualan per bulan di dashboard (bisa dicetak)
- [ ] Halaman profil pelanggan dan ganti password
- [ ] Pagination di daftar produk dan pesanan

## Dokumen (PJ: ____)
- [ ] Screenshot setiap halaman (toko dan admin) untuk laporan/manual
- [ ] Use case diagram (aktor: Pengunjung, Pelanggan, Admin)
- [ ] Flowchart/activity diagram alur checkout
- [ ] ERD (sudah ada di README, salin ke laporan)
- [ ] Manual book singkat untuk HKI (cara instal + cara pakai dengan screenshot)
- [ ] Surat pernyataan dan dokumen HKI sesuai format kampus/DJKI

## Sebelum hari H
- [ ] Set `'debug' => false` di `config.php` saat demo
- [ ] Ganti password admin dari `admin123`
- [ ] Siapkan cadangan: zip proyek + file SQL di flashdisk/Drive
- [ ] Latihan demo 5–10 menit: alur pelanggan, lalu alur admin
