<?php
// variabel: $d (data), $errors, $kategori, $supplier, $heading, $submitLabel
?>
<section class="card form-card">
  <h1 class="h1"><?= e($heading) ?></h1>
  <?php if ($errors): ?>
    <div class="flash bad"><ul><?php foreach ($errors as $er): ?><li><?= e($er) ?></li><?php endforeach; ?></ul></div>
  <?php endif; ?>
  <form method="post" class="form-grid" novalidate>
    <?= csrf_field() ?>
    <label>Kode SKU<input name="kode_sku" maxlength="30" value="<?= e($d['kode_sku']) ?>" required></label>
    <label>Nama Produk<input name="nama_produk" maxlength="150" value="<?= e($d['nama_produk']) ?>" required></label>
    <label>Kategori
      <select name="kategori_id" required>
        <option value="">— pilih kategori —</option>
        <?php foreach ($kategori as $k): ?>
          <option value="<?= (int)$k['id'] ?>" <?= (int)$d['kategori_id'] === (int)$k['id'] ? 'selected' : '' ?>><?= e($k['nama_kategori']) ?></option>
        <?php endforeach; ?>
      </select>
    </label>
    <label>Supplier
      <select name="supplier_id" required>
        <option value="">— pilih supplier —</option>
        <?php foreach ($supplier as $s): ?>
          <option value="<?= (int)$s['id'] ?>" <?= (int)$d['supplier_id'] === (int)$s['id'] ? 'selected' : '' ?>><?= e($s['nama_supplier']) ?></option>
        <?php endforeach; ?>
      </select>
    </label>
    <label>Stok<input type="number" min="0" name="stok" value="<?= e($d['stok']) ?>" required></label>
    <label>Stok Minimum<input type="number" min="0" name="stok_minimum" value="<?= e($d['stok_minimum']) ?>" required></label>
    <label>Harga Beli (Rp)<input type="number" min="0" name="harga_beli" value="<?= e($d['harga_beli']) ?>" required></label>
    <label>Harga Jual (Rp)<input type="number" min="0" name="harga_jual" value="<?= e($d['harga_jual']) ?>" required></label>
    <label>Satuan<input name="satuan" maxlength="20" value="<?= e($d['satuan']) ?>" placeholder="pcs / botol / set" required></label>
    <div class="form-actions">
      <button class="btn" type="submit"><?= e($submitLabel) ?></button>
      <a class="btn ghost" href="index.php">Batal</a>
    </div>
  </form>
</section>
