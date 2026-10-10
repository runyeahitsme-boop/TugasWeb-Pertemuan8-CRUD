<?php
require_once __DIR__ . '/includes/helpers.php';

$q    = trim($_GET['q'] ?? '');
$kat  = (int)($_GET['kat'] ?? 0);
$low  = ($_GET['stok'] ?? '') === 'rendah';
$page = max(1, (int)($_GET['page'] ?? 1));

// Filter dinamis (semua nilai lewat prepared statement)
$where = []; $params = [];
if ($q !== '') { $where[] = '(p.nama_produk LIKE ? OR p.kode_sku LIKE ?)'; $params[] = "%$q%"; $params[] = "%$q%"; }
if ($kat > 0)  { $where[] = 'p.kategori_id = ?'; $params[] = $kat; }
if ($low)      { $where[] = 'p.stok <= p.stok_minimum'; }
$w = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$st = $pdo->prepare("SELECT COUNT(*) FROM produk p $w");
$st->execute($params);
$total = (int)$st->fetchColumn();
$pages = max(1, (int)ceil($total / PER_PAGE));
$page  = min($page, $pages);
$offset = ($page - 1) * PER_PAGE;

// Halaman list: JOIN produk + kategori + supplier
$st = $pdo->prepare("SELECT p.*, k.nama_kategori, s.nama_supplier
    FROM produk p
    JOIN kategori k ON k.id = p.kategori_id
    JOIN supplier s ON s.id = p.supplier_id
    $w ORDER BY p.id DESC LIMIT ? OFFSET ?");
$i = 1;
foreach ($params as $v) $st->bindValue($i++, $v);
$st->bindValue($i++, PER_PAGE, PDO::PARAM_INT);
$st->bindValue($i,   $offset,  PDO::PARAM_INT);
$st->execute();
$rows = $st->fetchAll();

$stat = $pdo->query('SELECT COUNT(*) jml, COALESCE(SUM(stok),0) stok, COALESCE(SUM(stok*harga_beli),0) nilai, COALESCE(SUM(stok<=stok_minimum),0) low FROM produk')->fetch();
$logs = $pdo->query('SELECT * FROM log_aktivitas ORDER BY id DESC LIMIT 6')->fetchAll();
$lowItems = $pdo->query('SELECT nama_produk, stok, satuan FROM produk WHERE stok <= stok_minimum ORDER BY stok ASC LIMIT 5')->fetchAll();
$kategori = $pdo->query('SELECT id, nama_kategori FROM kategori ORDER BY nama_kategori')->fetchAll();

function page_url(int $p): string { $g = $_GET; $g['page'] = $p; return 'index.php?' . http_build_query($g); }

$title = 'Dashboard'; $active = 'home';
require __DIR__ . '/includes/header.php';
?>
<div class="layout">
  <section class="content">
    <h1 class="h1">RSPEED STOCK <small>Inventaris Sparepart Bengkel</small></h1>

    <div class="stats">
      <div class="stat"><span>Total Produk</span><b><?= (int)$stat['jml'] ?></b></div>
      <div class="stat"><span>Total Stok</span><b><?= number_format((int)$stat['stok'], 0, ',', '.') ?></b></div>
      <div class="stat"><span>Nilai Persediaan</span><b><?= e(rupiah($stat['nilai'])) ?></b></div>
      <div class="stat warn"><span>Stok Menipis</span><b><?= (int)$stat['low'] ?></b></div>
    </div>

    <div class="section-head">
      <h2>Daftar Sparepart</h2>
      <form class="filters" method="get">
        <input type="hidden" name="q" value="<?= e($q) ?>">
        <select name="kat" onchange="this.form.submit()" aria-label="Filter kategori">
          <option value="0">Semua kategori</option>
          <?php foreach ($kategori as $k): ?>
            <option value="<?= (int)$k['id'] ?>" <?= $kat === (int)$k['id'] ? 'selected' : '' ?>><?= e($k['nama_kategori']) ?></option>
          <?php endforeach; ?>
        </select>
        <label class="chk"><input type="checkbox" name="stok" value="rendah" <?= $low ? 'checked' : '' ?> onchange="this.form.submit()"> Stok menipis</label>
        <a class="btn" href="produk_create.php"><svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>Tambah</a>
      </form>
    </div>

    <div class="table-wrap">
      <table class="tbl">
        <thead><tr><th>Produk</th><th>Kategori</th><th>Supplier</th><th class="num">Stok</th><th class="num">Harga Jual</th><th class="act">Aksi</th></tr></thead>
        <tbody>
        <?php foreach ($rows as $r): $isLow = (int)$r['stok'] <= (int)$r['stok_minimum']; ?>
          <tr>
            <td data-label="Produk">
              <div class="prod">
                <span class="thumb"><?= e(mb_strtoupper(mb_substr($r['nama_kategori'], 0, 1))) ?></span>
                <div><strong><?= e($r['nama_produk']) ?></strong><small><?= e($r['kode_sku']) ?></small></div>
              </div>
            </td>
            <td data-label="Kategori"><?= e($r['nama_kategori']) ?></td>
            <td data-label="Supplier"><?= e($r['nama_supplier']) ?></td>
            <td data-label="Stok" class="num"><span class="pill <?= $isLow ? 'low' : 'okk' ?>"><?= (int)$r['stok'] ?> <?= e($r['satuan']) ?></span></td>
            <td data-label="Harga Jual" class="num"><?= e(rupiah($r['harga_jual'])) ?></td>
            <td data-label="Aksi" class="act">
              <a class="mini" href="produk_edit.php?id=<?= (int)$r['id'] ?>">Edit</a>
              <a class="mini danger" href="produk_delete.php?id=<?= (int)$r['id'] ?>">Hapus</a>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if (!$rows): ?><tr class="empty"><td colspan="6">Tidak ada data yang cocok.</td></tr><?php endif; ?>
        </tbody>
      </table>
    </div>

    <?php if ($pages > 1): ?>
    <nav class="pager" aria-label="Pagination">
      <?php if ($page > 1): ?><a href="<?= e(page_url($page - 1)) ?>" aria-label="Sebelumnya"><svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 6l-6 6 6 6"/></svg></a><?php endif; ?>
      <?php for ($p = 1; $p <= $pages; $p++): ?>
        <a href="<?= e(page_url($p)) ?>" class="<?= $p === $page ? 'cur' : '' ?>"><?= $p ?></a>
      <?php endfor; ?>
      <?php if ($page < $pages): ?><a href="<?= e(page_url($page + 1)) ?>" aria-label="Berikutnya"><svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 6l6 6-6 6"/></svg></a><?php endif; ?>
    </nav>
    <?php endif; ?>
    <p class="muted"><?= $total ?> produk ditemukan - halaman <?= $page ?> dari <?= $pages ?></p>
  </section>

  <aside class="rail">
    <h2>Activity Log</h2>
    <ul class="log">
      <?php foreach ($logs as $l): ?>
        <li>
          <span class="tag t-<?= strtolower(e($l['aksi'])) ?>"><?= e($l['aksi']) ?></span>
          <div><strong><?= e($l['detail']) ?></strong><small><?= e(date('d/m/y H:i', strtotime($l['created_at']))) ?></small></div>
        </li>
      <?php endforeach; ?>
      <?php if (!$logs): ?><li class="muted">Belum ada aktivitas.</li><?php endif; ?>
    </ul>

    <div class="dark-card">
      <h3>Perlu Restock</h3>
      <?php foreach ($lowItems as $li): ?>
        <div class="dc-row"><span><?= e($li['nama_produk']) ?></span><b><?= (int)$li['stok'] ?> <?= e($li['satuan']) ?></b></div>
      <?php endforeach; ?>
      <?php if (!$lowItems): ?><div class="dc-row"><span>Semua stok aman</span></div><?php endif; ?>
      <a class="dc-link" href="export.php">Export laporan CSV <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg></a>
    </div>
  </aside>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
