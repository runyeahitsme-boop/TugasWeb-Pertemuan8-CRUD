<?php
require_once __DIR__ . '/includes/helpers.php';
$id = (int)($_GET['id'] ?? 0);
$st = $pdo->prepare('SELECT * FROM produk WHERE id = ?');
$st->execute([$id]);
$old = $st->fetch();
if (!$old) { flash('error', 'Produk tidak ditemukan.'); redirect('index.php'); }

[$kategori, $supplier] = get_dropdowns($pdo);
$d = $old; $errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    [$d, $errors] = validate_produk($pdo, $_POST);
    if (!$errors) {
        try {
            $pdo->beginTransaction();
            $st = $pdo->prepare('UPDATE produk SET kode_sku=?, nama_produk=?, kategori_id=?, supplier_id=?, stok=?, stok_minimum=?, harga_beli=?, harga_jual=?, satuan=? WHERE id=?');
            $st->execute([$d['kode_sku'], $d['nama_produk'], $d['kategori_id'], $d['supplier_id'], $d['stok'], $d['stok_minimum'], $d['harga_beli'], $d['harga_jual'], $d['satuan'], $id]);
            log_aktivitas($pdo, 'UBAH', $id, 'Mengubah produk: ' . $d['nama_produk'] . ' (stok ' . $old['stok'] . ' menjadi ' . $d['stok'] . ')');
            $pdo->commit();
            flash('success', 'Produk "' . $d['nama_produk'] . '" berhasil diperbarui.');
            redirect('index.php');
        } catch (PDOException $ex) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            $errors[] = $ex->getCode() === '23000' ? 'Kode SKU sudah dipakai produk lain.' : 'Gagal memperbarui data.';
        }
    }
}
$title = 'Edit Produk'; $active = 'home';
$heading = 'Edit Produk'; $submitLabel = 'Simpan Perubahan';
require __DIR__ . '/includes/header.php';
require __DIR__ . '/includes/form.php';
require __DIR__ . '/includes/footer.php';
