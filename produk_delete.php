<?php
require_once __DIR__ . '/includes/helpers.php';
$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);

$st = $pdo->prepare('SELECT p.*, k.nama_kategori, s.nama_supplier FROM produk p JOIN kategori k ON k.id=p.kategori_id JOIN supplier s ON s.id=p.supplier_id WHERE p.id = ?');
$st->execute([$id]);
$p = $st->fetch();
if (!$p) { flash('error', 'Produk tidak ditemukan.'); redirect('index.php'); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    try {
        // BONUS: transaction — catat log dulu, baru hapus. Gagal salah satu = rollback keduanya.
        $pdo->beginTransaction();
        log_aktivitas($pdo, 'HAPUS', null, 'Menghapus produk: ' . $p['nama_produk'] . ' (' . $p['kode_sku'] . '), sisa stok ' . $p['stok']);
        $del = $pdo->prepare('DELETE FROM produk WHERE id = ?');
        $del->execute([$id]);
        $pdo->commit();
        flash('success', 'Produk "' . $p['nama_produk'] . '" berhasil dihapus.');
    } catch (PDOException $ex) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        flash('error', 'Gagal menghapus produk.');
    }
    redirect('index.php');
}
$title = 'Hapus Produk'; $active = 'home';
require __DIR__ . '/includes/header.php';
?>
<section class="card form-card confirm">
  <h1 class="h1">Hapus produk ini?</h1>
  <p class="muted">Tindakan ini tidak dapat dibatalkan, tetapi akan tercatat di Activity Log.</p>
  <dl class="detail">
    <dt>Nama</dt><dd><?= e($p['nama_produk']) ?></dd>
    <dt>SKU</dt><dd><?= e($p['kode_sku']) ?></dd>
    <dt>Kategori</dt><dd><?= e($p['nama_kategori']) ?></dd>
    <dt>Supplier</dt><dd><?= e($p['nama_supplier']) ?></dd>
    <dt>Stok</dt><dd><?= (int)$p['stok'] ?> <?= e($p['satuan']) ?></dd>
  </dl>
  <form method="post" class="form-actions">
    <?= csrf_field() ?>
    <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
    <button class="btn danger" type="submit">Ya, hapus</button>
    <a class="btn ghost" href="index.php">Batal</a>
  </form>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
