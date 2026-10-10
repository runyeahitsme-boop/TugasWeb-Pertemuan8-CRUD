# RSPEED STOCK — Inventaris Sparepart Bengkel (Tugas Rutin 8)

PHP 8 + MySQL (PDO). Tanpa framework, siap upload ke cPanel.

## Instalasi di cPanel
1. cPanel → **MySQL Databases**: buat database (mis. `akun_inventaris_db`), buat user, lalu *Add User To Database* (All Privileges).
2. cPanel → **phpMyAdmin** → pilih database tadi → tab **Import** → pilih `sql/inventaris_db.sql`.
3. Edit `config.php`: isi `DB_NAME`, `DB_USER`, `DB_PASS` sesuai langkah 1.
4. Upload semua file ke `public_html/` (atau subfolder) via File Manager, lalu extract. Buka domain-nya.

Butuh PHP 7.4+ (disarankan 8.x).

## Pemenuhan requirement
| # | Requirement | Lokasi |
|---|---|---|
| 1 | DB `inventaris_db`, 3 tabel + FK | `sql/inventaris_db.sql` (`kategori`, `supplier`, `produk`) |
| 2 | Min. 5 data seed per tabel | `sql/inventaris_db.sql` (6 / 5 / 10) |
| 3 | PDO + Singleton | `includes/Database.php` |
| 4 | List produk dengan JOIN | `index.php` (JOIN kategori + supplier) |
| 5 | Form create + dropdown kategori & supplier | `produk_create.php`, `includes/form.php` |
| 6 | Update (pre-filled) & delete (konfirmasi) | `produk_edit.php`, `produk_delete.php` |
| 7 | Semua query input pakai prepared statement | seluruh file (`?` placeholder) |
| 8 | Output HTML pakai `htmlspecialchars()` | fungsi `e()` di `includes/helpers.php` |
| 9 | Flash message sukses/gagal (redirect pattern) | `flash()` di `helpers.php` + `header.php` |
| 10 | UI rapi & responsive | `assets/style.css` |

## Bonus
- **Transaction pada delete + log aktivitas** (`produk_delete.php`, tabel `log_aktivitas`; create/update juga memakai transaction)
- **Pencarian** (nama/SKU) + **filter kategori** + **filter stok menipis**
- **Pagination** (8 data per halaman, ubah `PER_PAGE` di `config.php`)
- **Export laporan CSV** (`export.php`, bisa dibuka di Excel)

Tambahan: proteksi CSRF pada semua form POST, validasi input di server.
