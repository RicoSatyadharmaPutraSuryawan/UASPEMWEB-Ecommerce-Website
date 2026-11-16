-- =========================================================
-- Database Driptique (db_uas)
-- Impor: buat database db_uas, lalu impor file ini.
--   mysql -u root -p -e "CREATE DATABASE db_uas CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
--   mysql -u root -p db_uas < database/db_uas.sql
-- Akun uji:
--   Admin    : admin / admin123
--   Customer : customer@driptique.test / customer123
-- =========================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS order_items, orders, admin_replies, feedbacks, produk, users, admins;
SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE admins (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nama VARCHAR(100) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  no_hp VARCHAR(20) NOT NULL,
  password VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE produk (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nama_produk VARCHAR(150) NOT NULL,
  deskripsi_produk TEXT NOT NULL,
  harga_produk DECIMAL(12,2) NOT NULL,
  stok_produk INT NOT NULL DEFAULT 0,
  gambar_produk VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE feedbacks (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(150) NOT NULL,
  feedback TEXT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE admin_replies (
  id INT AUTO_INCREMENT PRIMARY KEY,
  feedback_id INT NOT NULL,
  reply TEXT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_reply_feedback FOREIGN KEY (feedback_id) REFERENCES feedbacks(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE orders (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  nama_penerima VARCHAR(100) NOT NULL,
  no_hp VARCHAR(20) NOT NULL,
  alamat TEXT NOT NULL,
  metode_pembayaran ENUM('transfer','cod') NOT NULL,
  catatan TEXT NULL,
  total DECIMAL(12,2) NOT NULL,
  status ENUM('menunggu','diproses','dikirim','selesai','dibatalkan') NOT NULL DEFAULT 'menunggu',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_order_user FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE order_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  order_id INT NOT NULL,
  produk_id INT NULL,
  nama_produk VARCHAR(150) NOT NULL,
  harga DECIMAL(12,2) NOT NULL,
  jumlah INT NOT NULL,
  CONSTRAINT fk_item_order FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
  CONSTRAINT fk_item_produk FOREIGN KEY (produk_id) REFERENCES produk(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------- Data awal ----------
INSERT INTO admins (username, password) VALUES
('admin', '$2y$10$Ld4QMLwTFVgmWsrGmVRHmuYlLFIqMGoRdC2AxdXw5QOG.FqstADRS');

INSERT INTO users (nama, email, no_hp, password) VALUES
('Customer Demo', 'customer@driptique.test', '081234567890', '$2y$10$dhlARH/bMwSxkOsisjKUz.hwZWRK4rtOzgxMBsaOOcAU7Fe/JJpXW');

INSERT INTO produk (nama_produk, deskripsi_produk, harga_produk, stok_produk, gambar_produk) VALUES
('Carhartt Detroit Jacket Hitam', 'Jaket kanvas kerah korduroi, kondisi 9/10. Ukuran L (lebar dada 60 cm, panjang 70 cm).', 450000, 1, 'Carhartt 2.png'),
('Carhartt Detroit Jacket Navy', 'Jaket kanvas kerah korduroi, kondisi like new. Ukuran M (lebar dada 57 cm, panjang 68 cm).', 475000, 2, 'Carhartt 4.png'),
('Polo Half-Zip Sweater Krem', 'Sweater half-zip katun, tanpa noda dan lubang. Ukuran L.', 280000, 2, 'Polo 1.png'),
('Polo Shirt Navy', 'Kaos polo katun pique dengan logo besar. Kondisi 9/10. Ukuran M.', 175000, 3, 'Polo 2.png'),
('Polo Zip Hoodie Navy', 'Hoodie resleting bahan fleece, resleting lancar. Ukuran L.', 300000, 1, 'Polo 3.png'),
('Polo Quarter-Zip Pullover Krem', 'Pullover quarter-zip bahan rajut halus. Ukuran XL.', 260000, 1, 'Polo 4.png'),
('Stussy Graphic Tee Putih', 'Kaos grafis, sablon masih tajam. Ukuran M.', 190000, 4, 'Stussy 1.png'),
('Stussy Basic Hoodie Abu Muda', 'Hoodie fleece logo bordir kecil, kondisi 9/10. Ukuran L.', 320000, 2, 'Stussy 3.png'),
('Stussy Arch Zip Hoodie Abu', 'Hoodie resleting logo arch besar, warna tidak pudar. Ukuran M.', 350000, 1, 'Stussy 4.png'),
('Sherpa Fleece Jacket Hijau', 'Jaket sherpa tebal dengan saku dada, sangat hangat. Ukuran L.', 420000, 1, 'Tnf 1.png'),
('TNF Nuptse Puffer Biru Muda', 'Jaket puffer bulu angsa, tidak ada sobek. Ukuran M.', 650000, 1, 'Tnf 2.png'),
('TNF Hooded Jacket Hitam', 'Jaket berkerudung anti angin dan gerimis. Ukuran L.', 350000, 2, 'Tnf 3.png');

INSERT INTO feedbacks (name, email, feedback) VALUES
('Budi', 'budi@example.com', 'Barangnya sesuai deskripsi, pengiriman cepat.');
INSERT INTO admin_replies (feedback_id, reply) VALUES (1, 'Terima kasih, Budi! Ditunggu belanja berikutnya.');
