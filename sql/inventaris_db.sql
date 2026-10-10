-- RSPEED STOCK - skema + seed
-- cPanel: buat database lewat "MySQL Databases", lalu import file ini lewat phpMyAdmin (pilih database-nya dulu).
-- Jika di localhost, hapus tanda komentar 2 baris di bawah:
-- CREATE DATABASE IF NOT EXISTS inventaris_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
-- USE inventaris_db;

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS log_aktivitas;
DROP TABLE IF EXISTS produk;
DROP TABLE IF EXISTS supplier;
DROP TABLE IF EXISTS kategori;
SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE kategori (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nama_kategori VARCHAR(80) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE supplier (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nama_supplier VARCHAR(120) NOT NULL,
  telepon VARCHAR(20) NOT NULL,
  alamat VARCHAR(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE produk (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  kode_sku VARCHAR(30) NOT NULL UNIQUE,
  nama_produk VARCHAR(150) NOT NULL,
  kategori_id INT UNSIGNED NOT NULL,
  supplier_id INT UNSIGNED NOT NULL,
  stok INT UNSIGNED NOT NULL DEFAULT 0,
  stok_minimum INT UNSIGNED NOT NULL DEFAULT 5,
  harga_beli DECIMAL(12,0) NOT NULL DEFAULT 0,
  harga_jual DECIMAL(12,0) NOT NULL DEFAULT 0,
  satuan VARCHAR(20) NOT NULL DEFAULT 'pcs',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_produk_kategori FOREIGN KEY (kategori_id) REFERENCES kategori(id) ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT fk_produk_supplier FOREIGN KEY (supplier_id) REFERENCES supplier(id) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Bonus: tabel log aktivitas (dipakai transaction pada create/update/delete)
CREATE TABLE log_aktivitas (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  aksi ENUM('TAMBAH','UBAH','HAPUS') NOT NULL,
  produk_id INT UNSIGNED NULL,
  detail VARCHAR(255) NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO kategori (nama_kategori) VALUES
('Oli & Pelumas'),('Ban & Velg'),('Kelistrikan'),('Sistem Rem'),('Filter'),('Aksesori');

INSERT INTO supplier (nama_supplier, telepon, alamat) VALUES
('PT Mitra Otomotif Medan','061-4521100','Jl. Gatot Subroto No. 88, Medan'),
('CV Sinar Motor Jaya','061-7366201','Jl. Sisingamangaraja KM 5, Medan'),
('UD Bintang Ban','0812-6001-2233','Jl. Brigjend Katamso No. 12, Medan'),
('Toko Listrik Maju Terus','0813-7010-4455','Jl. Thamrin No. 45, Medan'),
('Distributor Rem Prima','061-8812345','Jl. Williem Iskandar, Deli Serdang');

INSERT INTO produk (kode_sku, nama_produk, kategori_id, supplier_id, stok, stok_minimum, harga_beli, harga_jual, satuan) VALUES
('OLI-001','Oli Mesin 10W-40 1L',1,1,48,10,52000,68000,'botol'),
('OLI-002','Oli Gardan 150ml',1,1,3,8,11000,16000,'botol'),
('BAN-001','Ban Tubeless 90/80-14',2,3,12,4,185000,235000,'pcs'),
('BAN-002','Ban Dalam 275/300-17',2,3,25,6,28000,40000,'pcs'),
('LST-001','Aki 12V 5Ah MF',3,4,9,3,165000,210000,'pcs'),
('LST-002','Busi Iridium',3,4,2,10,32000,48000,'pcs'),
('REM-001','Kampas Rem Depan',4,5,30,8,24000,38000,'set'),
('REM-002','Minyak Rem DOT 3 100ml',4,5,4,6,9000,15000,'botol'),
('FLT-001','Filter Udara Standar',5,2,18,5,26000,39000,'pcs'),
('ACC-001','Spion Racing Pasang',6,2,14,4,45000,70000,'set');

INSERT INTO log_aktivitas (aksi, produk_id, detail) VALUES
('TAMBAH',1,'Seed awal: Oli Mesin 10W-40 1L'),
('TAMBAH',6,'Seed awal: Busi Iridium');
