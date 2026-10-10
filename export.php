<?php
require_once __DIR__ . '/includes/helpers.php';
$rows = $pdo->query('SELECT p.kode_sku, p.nama_produk, k.nama_kategori, s.nama_supplier, p.stok, p.satuan, p.stok_minimum, p.harga_beli, p.harga_jual, (p.stok*p.harga_beli) AS nilai
    FROM produk p JOIN kategori k ON k.id=p.kategori_id JOIN supplier s ON s.id=p.supplier_id ORDER BY k.nama_kategori, p.nama_produk')->fetchAll();

header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="laporan-rspeed-stock-' . date('Ymd-His') . '.csv"');
echo "\xEF\xBB\xBF";
$out = fopen('php://output', 'w');
fputcsv($out, ['SKU','Nama Produk','Kategori','Supplier','Stok','Satuan','Stok Min','Harga Beli','Harga Jual','Nilai Persediaan']);
foreach ($rows as $r) fputcsv($out, array_values($r));
fclose($out);
