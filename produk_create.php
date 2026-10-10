<?php
require_once __DIR__ . '/includes/helpers.php';
[$kategori, $supplier] = get_dropdowns($pdo);
$d = ['kode_sku'=>'','nama_produk'=>'','kategori_id'=>0,'supplier_id'=>0,'stok'=>'0','stok_minimum'=>'5','harga_beli'=>'','harga_jual'=>'','satuan'=>'pcs'];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    [$d, $errors] = validate_produk($pdo, $_POST);
    if (!$errors) {
        try {
            $pdo->beginTransaction();
            $st = $pdo->prepare('INSERT INTO produk (kode_sku, nama_produk, kategori_id, supplier_id, stok, stok_minimum, harga_beli, harga_jual, satuan) VALUES (?,?,?,?,?,?,?,?,?)');
            $st->execute([$d['kode_sku'], $d['nama_produk'], $d['kategori_id'], $d['supplier_id'], $d['stok'], $d['stok_minimum'], $d['harga_beli'], $d['harga_jual'], $d['satuan']]);
            log_aktivitas($pdo, 'TAMBAH', (int)$pdo->lastInsertId(), 'Menambah produk: ' . $d['nama_produk'] . ' (' . $d['kode_sku'] . ')');
            $pdo->commit();
            flash('success', 'Produk "' . $d['nama_produk'] . '" berhasil ditambahkan.');
            redirect('index.php');
        } catch (PDOException $ex) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            $errors[] = $ex->getCode() === '23000' ? 'Kode SKU sudah dipakai produk lain.' : 'Gagal menyimpan data.';
        }
    }
}
$title = 'Tambah Produk'; $active = 'tambah';
$heading = 'Tambah Produk'; $submitLabel = 'Simpan Produk';
require __DIR__ . '/includes/header.php';
require __DIR__ . '/includes/form.php';
require __DIR__ . '/includes/footer.php';
