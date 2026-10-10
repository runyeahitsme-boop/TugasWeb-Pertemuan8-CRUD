<?php
// variabel: $title, $active
$lowCount = (int)$pdo->query('SELECT COUNT(*) FROM produk WHERE stok <= stok_minimum')->fetchColumn();
$f = flash();
$svg = fn(string $p) => '<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $p . '</svg>';
$nav = [
    'home'   => ['index.php', $svg('<path d="M3 11l9-8 9 8"/><path d="M5 10v10h14V10"/><path d="M10 20v-6h4v6"/>'), 'Dashboard'],
    'tambah' => ['produk_create.php', $svg('<path d="M12 5v14M5 12h14"/>'), 'Tambah Produk'],
    'export' => ['export.php', $svg('<path d="M12 3v12"/><path d="M7 11l5 5 5-5"/><path d="M4 20h16"/>'), 'Export CSV'],
];
?><!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($title) ?> — <?= e(APP_NAME) ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/style.css">
</head>
<body>
<div class="app">
  <aside class="sidebar">
    <a class="logo" href="index.php" title="<?= e(APP_NAME) ?>">R</a>
    <nav>
      <?php foreach ($nav as $key => [$href, $icon, $label]): ?>
        <a href="<?= $href ?>" class="nav-btn<?= $active === $key ? ' active' : '' ?>" title="<?= e($label) ?>" aria-label="<?= e($label) ?>"><?= $icon /* SVG statis, aman */ ?></a>
      <?php endforeach; ?>
    </nav>
  </aside>
  <main class="main">
    <header class="topbar">
      <form class="search" action="index.php" method="get">
        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="M20 20l-4-4"/></svg>
        <input type="search" name="q" value="<?= e($_GET['q'] ?? '') ?>" placeholder="Cari nama produk atau SKU…">
      </form>
      <div class="top-right">
        <a class="badge" href="index.php?stok=rendah" title="Stok menipis"><?= $lowCount ?></a>
        <span class="avatar" title="Admin <?= e(APP_NAME) ?>">RS</span>
      </div>
    </header>
    <?php if ($f): ?>
      <div class="flash <?= $f['t'] === 'success' ? 'ok' : 'bad' ?>" role="alert"><?= e($f['m']) ?></div>
    <?php endif; ?>
