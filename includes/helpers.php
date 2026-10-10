<?php
session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/Database.php';

try {
    $pdo = Database::getInstance()->pdo();
} catch (PDOException $ex) {
    http_response_code(500);
    exit('Koneksi database gagal. Periksa config.php.');
}

function e($s): string { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function redirect(string $url): void { header('Location: ' . $url); exit; }
function rupiah($n): string { return 'Rp ' . number_format((float)$n, 0, ',', '.'); }

function flash(?string $type = null, ?string $msg = null): ?array
{
    if ($type !== null) { $_SESSION['flash'] = ['t' => $type, 'm' => $msg]; return null; }
    $f = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $f;
}

function csrf_field(): string
{
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(16));
    return '<input type="hidden" name="csrf" value="' . e($_SESSION['csrf']) . '">';
}
function csrf_check(): void
{
    if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) {
        flash('error', 'Token keamanan tidak valid, silakan ulangi.');
        redirect('index.php');
    }
}

function log_aktivitas(PDO $pdo, string $aksi, ?int $produkId, string $detail): void
{
    $st = $pdo->prepare('INSERT INTO log_aktivitas (aksi, produk_id, detail) VALUES (?, ?, ?)');
    $st->execute([$aksi, $produkId, mb_substr($detail, 0, 255)]);
}

function get_dropdowns(PDO $pdo): array
{
    return [
        $pdo->query('SELECT id, nama_kategori FROM kategori ORDER BY nama_kategori')->fetchAll(),
        $pdo->query('SELECT id, nama_supplier FROM supplier ORDER BY nama_supplier')->fetchAll(),
    ];
}

/** Validasi input produk. Mengembalikan [data bersih, daftar error]. */
function validate_produk(PDO $pdo, array $in): array
{
    $d = [
        'kode_sku'     => strtoupper(trim($in['kode_sku'] ?? '')),
        'nama_produk'  => trim($in['nama_produk'] ?? ''),
        'kategori_id'  => (int)($in['kategori_id'] ?? 0),
        'supplier_id'  => (int)($in['supplier_id'] ?? 0),
        'stok'         => $in['stok'] ?? '',
        'stok_minimum' => $in['stok_minimum'] ?? '',
        'harga_beli'   => $in['harga_beli'] ?? '',
        'harga_jual'   => $in['harga_jual'] ?? '',
        'satuan'       => trim($in['satuan'] ?? ''),
    ];
    $err = [];
    if ($d['kode_sku'] === '' || strlen($d['kode_sku']) > 30) $err[] = 'Kode SKU wajib diisi (maks 30 karakter).';
    if ($d['nama_produk'] === '' || mb_strlen($d['nama_produk']) > 150) $err[] = 'Nama produk wajib diisi (maks 150 karakter).';
    if ($d['satuan'] === '' || mb_strlen($d['satuan']) > 20) $err[] = 'Satuan wajib diisi (maks 20 karakter).';
    foreach (['stok' => 'Stok', 'stok_minimum' => 'Stok minimum', 'harga_beli' => 'Harga beli', 'harga_jual' => 'Harga jual'] as $k => $label) {
        if (!preg_match('/^\d{1,9}$/', (string)$d[$k])) $err[] = "$label harus berupa angka bulat 0 atau lebih.";
    }
    $c = $pdo->prepare('SELECT COUNT(*) FROM kategori WHERE id = ?'); $c->execute([$d['kategori_id']]);
    if (!$c->fetchColumn()) $err[] = 'Pilih kategori yang valid.';
    $c = $pdo->prepare('SELECT COUNT(*) FROM supplier WHERE id = ?'); $c->execute([$d['supplier_id']]);
    if (!$c->fetchColumn()) $err[] = 'Pilih supplier yang valid.';
    return [$d, $err];
}
