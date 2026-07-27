<?php
$currentPage = $currentPage ?? 'home';
$pageTitle = $pageTitle ?? ($siteName ?? 'RW 05 Desa Citeureup');
$navItems = [
    'home' => ['label' => 'Beranda', 'href' => site_url('/')],
    'profil' => ['label' => 'Profil', 'href' => site_url('profil')],
    'layanan' => ['label' => 'Layanan', 'href' => site_url('layanan')],
    'kesehatan' => ['label' => 'Kesehatan', 'href' => site_url('kesehatan')],
    'keuangan' => ['label' => 'Keuangan', 'href' => site_url('keuangan')],
    'kegiatan' => ['label' => 'Kegiatan', 'href' => site_url('kegiatan')],
    'pengurus' => ['label' => 'Pengurus', 'href' => site_url('pengurus')],
    'aspirasi' => ['label' => 'Aspirasi', 'href' => site_url('aspirasi')],
];
$popularServices = [
    ['label' => 'Layanan Warga', 'href' => site_url('layanan')],
    ['label' => 'Kesehatan Warga', 'href' => site_url('kesehatan')],
    ['label' => 'Edukasi Kesehatan', 'href' => site_url('edukasi-kesehatan')],
    ['label' => 'Ajukan Surat', 'href' => site_url('layanan-online')],
    ['label' => 'Laporan Keuangan', 'href' => site_url('keuangan')],
    ['label' => 'Surat Pengantar', 'href' => site_url('layanan') . '#surat-pengantar'],
    ['label' => 'Surat Edaran', 'href' => site_url('layanan') . '#surat-edaran'],
];
$footerAddress = trim((string) ($profil['alamat'] ?? '')) ?: 'Sekretariat RW 05 Desa Citeureup';
$footerWhatsapp = trim((string) ($profil['whatsapp'] ?? '')) ?: 'Belum tersedia';
$footerEmail = rw_official_email($profil['email'] ?? '');
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= rw_esc($pageTitle) ?> | Portal Warga</title>
  <meta name="description" content="Portal resmi RW 05 Desa Citeureup untuk layanan warga, kegiatan, pengurus, dan aspirasi masyarakat.">
  <meta name="theme-color" content="#12382a">
  <link rel="icon" type="image/svg+xml" href="<?= base_url('favicon.svg') ?>?v=rw05-20260706">
  <link rel="shortcut icon" href="<?= base_url('favicon.svg') ?>?v=rw05-20260706">
  <link rel="manifest" href="<?= base_url('manifest.webmanifest') ?>">
  <link rel="apple-touch-icon" href="<?= base_url('assets/logo-rw05.png') ?>">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Atkinson+Hyperlegible:wght@400;700&family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= base_url('assets/style.css') ?>?v=smart-rw-portal-20260727c">
</head>
<body>
<header class="topbar">
  <div class="topline">
    <div class="container topline-inner">
      <span>Portal layanan warga RW 05 Desa <?= rw_esc($desa ?? 'Citeureup') ?></span>
      <div class="topline-actions">
        <?php if (! empty($waLink)): ?>
          <a href="<?= rw_esc($waLink) ?>" target="_blank" rel="noopener noreferrer">WhatsApp RW</a>
        <?php endif; ?>
        <a href="<?= site_url('aspirasi') ?>">Kirim Aspirasi</a>
      </div>
    </div>
  </div>
  <div class="container nav">
    <a href="<?= site_url('/') ?>" class="brand" aria-label="Kembali ke beranda">
      <span class="brand-frame">
        <img src="<?= base_url('assets/logo-rw05.png') ?>" alt="Logo RW 05" class="brand-logo">
      </span>
      <span class="brand-copy">
        <strong>RW 05</strong>
        <span>Desa <?= rw_esc($desa ?? 'Citeureup') ?></span>
        <small>Portal Layanan Warga</small>
      </span>
    </a>
    <button class="menu-btn" id="menuBtn" type="button" aria-label="Buka menu navigasi" aria-expanded="false" aria-controls="menu">Menu</button>
    <nav class="menu" id="menu" aria-label="Menu utama">
      <?php foreach ($navItems as $key => $item): ?>
        <a href="<?= rw_esc($item['href']) ?>" class="<?= $currentPage === $key ? 'is-active' : '' ?>"><?= rw_esc($item['label']) ?></a>
      <?php endforeach; ?>
      <a href="<?= rw_esc($adminEntryUrl ?? site_url('admin/login')) ?>" class="menu-admin"><?= rw_esc($adminEntryLabel ?? 'Login Admin') ?></a>
    </nav>
  </div>
</header>

<main>
  <?= $this->renderSection('content') ?>
</main>

<footer class="footer">
  <div class="container footer-grid">
    <div class="footer-brand">
      <strong><?= rw_esc($siteName ?? 'RW 05 Desa Citeureup') ?></strong>
      <p>Portal informasi, layanan administrasi, kegiatan, pengurus, dan aspirasi warga yang mudah diakses semua umur.</p>
      <div class="footer-badge">Transparan | Tertib | Melayani</div>
    </div>

    <div class="footer-column">
      <h2>Menu</h2>
      <div class="footer-links">
        <?php foreach ($navItems as $item): ?>
          <a href="<?= rw_esc($item['href']) ?>"><?= rw_esc($item['label']) ?></a>
        <?php endforeach; ?>
      </div>
    </div>

    <div class="footer-column">
      <h2>Layanan Populer</h2>
      <div class="footer-links">
        <?php foreach ($popularServices as $service): ?>
          <a href="<?= rw_esc($service['href']) ?>"><?= rw_esc($service['label']) ?></a>
        <?php endforeach; ?>
      </div>
    </div>

    <div class="footer-column">
      <h2>Kontak RW</h2>
      <div class="footer-contact">
        <span>WhatsApp</span>
        <strong><?= rw_esc($footerWhatsapp) ?></strong>
        <span>Email</span>
        <strong><?= rw_esc($footerEmail) ?></strong>
        <span>Alamat</span>
        <strong><?= rw_esc($footerAddress) ?></strong>
      </div>
      <a href="<?= rw_esc($adminEntryUrl ?? site_url('admin/login')) ?>" class="footer-admin"><?= rw_esc($adminEntryLabel ?? 'Login Admin') ?></a>
    </div>
  </div>
</footer>
<script src="<?= base_url('assets/script.js') ?>"></script>
</body>
</html>
