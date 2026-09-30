<?php
$currentPage = $currentPage ?? 'home';
$siteDisplayName = $siteDisplayName ?? 'RW 05 Lamajang Peuntas';
$pageTitle = $pageTitle ?? $siteDisplayName;
$documentTitle = stripos($pageTitle, $siteDisplayName) === false
  ? $pageTitle . ' | ' . $siteDisplayName . ' | Portal Warga'
  : $pageTitle . ' | Portal Warga';
$metaDescription = $metaDescription ?? ($identity['metaDescription'] ?? 'Portal resmi RW 05 Lamajang Peuntas untuk layanan dan informasi warga.');
$canonicalUrl = current_url();
$primaryNavItems = [
    'home' => ['label' => 'Beranda', 'href' => site_url('/')],
    'layanan' => ['label' => 'Layanan', 'href' => site_url('layanan')],
    'kesehatan' => ['label' => 'Kesehatan', 'href' => site_url('kesehatan')],
    'keuangan' => ['label' => 'Transparansi', 'href' => site_url('keuangan')],
    'kegiatan' => ['label' => 'Kegiatan', 'href' => site_url('kegiatan')],
];
$secondaryNavItems = [
    'profil' => ['label' => 'Profil RW', 'href' => site_url('profil')],
    'pengurus' => ['label' => 'Pengurus', 'href' => site_url('pengurus')],
    'aspirasi' => ['label' => 'Aspirasi', 'href' => site_url('aspirasi')],
];
$navItems = $primaryNavItems + $secondaryNavItems;
$popularServices = [
    ['label' => 'Layanan Warga', 'href' => site_url('layanan')],
    ['label' => 'Kesehatan Warga', 'href' => site_url('kesehatan')],
    ['label' => 'Edukasi Kesehatan', 'href' => site_url('edukasi-kesehatan')],
    ['label' => 'Ajukan Surat', 'href' => site_url('layanan-online')],
    ['label' => 'Laporan Keuangan', 'href' => site_url('keuangan')],
    ['label' => 'Surat Pengantar', 'href' => site_url('layanan') . '#surat-pengantar'],
    ['label' => 'Surat Edaran', 'href' => site_url('layanan') . '#surat-edaran'],
];
$footerAddress = trim((string) ($profil['alamat'] ?? '')) ?: 'Lamajang Peuntas, ' . ($siteSubtitle ?? 'Desa Citeureup, Kecamatan Dayeuhkolot, Kabupaten Bandung');
$footerWhatsapp = trim((string) ($profil['whatsapp'] ?? '')) ?: 'Belum tersedia';
$footerEmail = rw_official_email($profil['email'] ?? '');
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
  <title><?= rw_esc($documentTitle) ?></title>
  <meta name="description" content="<?= rw_esc($metaDescription) ?>">
  <link rel="canonical" href="<?= rw_esc($canonicalUrl) ?>">
  <meta property="og:locale" content="id_ID">
  <meta property="og:type" content="website">
  <meta property="og:title" content="<?= rw_esc($documentTitle) ?>">
  <meta property="og:description" content="<?= rw_esc($metaDescription) ?>">
  <meta property="og:url" content="<?= rw_esc($canonicalUrl) ?>">
  <meta property="og:image" content="<?= base_url('assets/logo-rw05.png') ?>">
  <meta name="theme-color" content="#12382a">
  <meta name="mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
  <link rel="icon" type="image/svg+xml" href="<?= base_url('favicon.svg') ?>?v=rw05-20260706">
  <link rel="shortcut icon" href="<?= base_url('favicon.svg') ?>?v=rw05-20260706">
  <link rel="manifest" href="<?= base_url('manifest.webmanifest') ?>">
  <link rel="apple-touch-icon" href="<?= base_url('assets/logo-rw05.png') ?>">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Atkinson+Hyperlegible:wght@400;700&family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= base_url('assets/style.css') ?>?v=pwa-20260930-14">
</head>
<body>
<header class="topbar">
  <div class="topline">
    <div class="container topline-inner">
      <span>Portal warga <?= rw_esc($siteDisplayName ?? 'RW 05 Lamajang Peuntas') ?></span>
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
        <img src="<?= base_url('assets/logo-rw05.png') ?>" alt="Logo RW 05 Lamajang Peuntas" class="brand-logo">
      </span>
      <span class="brand-copy">
        <strong>RW 05</strong>
        <span>Lamajang Peuntas</span>
        <small>Desa <?= rw_esc($desa ?? 'Citeureup') ?></small>
      </span>
    </a>
    <button class="menu-btn" id="menuBtn" type="button" aria-label="Buka menu navigasi" aria-expanded="false" aria-controls="menu">Menu</button>
    <nav class="menu" id="menu" aria-label="Menu utama">
      <?php foreach ($primaryNavItems as $key => $item): ?>
        <?php if ($key === 'kesehatan'): ?>
          <details class="menu-more menu-health">
            <summary class="<?= $currentPage === 'kesehatan' ? 'is-active' : '' ?>">Kesehatan</summary>
            <div class="menu-more-panel">
              <a href="<?= site_url('kesehatan') ?>"><span class="health-menu-icon" aria-hidden="true">+</span><span>Ringkasan Kesehatan</span></a>
              <a href="<?= site_url('posyandu') ?>"><span class="health-menu-icon" aria-hidden="true">PY</span><span>Posyandu</span></a>
              <a href="<?= site_url('posbindu') ?>"><span class="health-menu-icon" aria-hidden="true">PB</span><span>Posbindu</span></a>
              <a href="<?= site_url('edukasi-kesehatan') ?>"><span class="health-menu-icon" aria-hidden="true">i</span><span>Edukasi Kesehatan</span></a>
            </div>
          </details>
        <?php else: ?>
          <a href="<?= rw_esc($item['href']) ?>" class="<?= $currentPage === $key ? 'is-active' : '' ?>"><?= rw_esc($item['label']) ?></a>
        <?php endif; ?>
      <?php endforeach; ?>
      <details class="menu-more">
        <summary class="<?= array_key_exists($currentPage, $secondaryNavItems) ? 'is-active' : '' ?>">Lainnya</summary>
        <div class="menu-more-panel">
          <?php foreach ($secondaryNavItems as $key => $item): ?>
            <a href="<?= rw_esc($item['href']) ?>" class="<?= $currentPage === $key ? 'is-active' : '' ?>"><?= rw_esc($item['label']) ?></a>
          <?php endforeach; ?>
          <a href="<?= rw_esc($adminEntryUrl ?? site_url('admin/login')) ?>" class="menu-admin"><?= rw_esc($adminEntryLabel ?? 'Login Admin') ?></a>
        </div>
      </details>
    </nav>
  </div>
</header>

<main>
  <?= $this->renderSection('content') ?>
</main>

<footer class="footer">
  <div class="container footer-grid">
    <div class="footer-brand">
      <strong><?= rw_esc($siteName ?? 'RW 05 LAMAJANG PEUNTAS') ?></strong>
      <p><?= rw_esc($siteSubtitle ?? 'Desa Citeureup, Kecamatan Dayeuhkolot, Kabupaten Bandung') ?></p>
      <div class="footer-badge"><?= rw_esc($identity['tagline'] ?? 'Transparan · Tertib · Melayani') ?></div>
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
<nav class="mobile-quick-nav" aria-label="Akses cepat dari ponsel">
  <a href="<?= site_url('/') ?>" class="<?= $currentPage === 'home' ? 'is-active' : '' ?>"><span aria-hidden="true">⌂</span>Beranda</a>
  <a href="<?= site_url('layanan-online') ?>#ajukan-surat"><span aria-hidden="true">＋</span>Surat</a>
  <a href="<?= site_url('layanan-online') ?>#cek-status"><span aria-hidden="true">✓</span>Status</a>
  <?php if (! empty($waLink)): ?>
    <a href="<?= rw_esc($waLink) ?>" target="_blank" rel="noopener noreferrer"><span aria-hidden="true">WA</span>WhatsApp</a>
  <?php else: ?>
    <a href="<?= site_url('aspirasi') ?>"><span aria-hidden="true">✉</span>Aspirasi</a>
  <?php endif; ?>
  <button type="button" data-mobile-menu-trigger aria-label="Buka menu utama" aria-expanded="false" aria-controls="menu"><span aria-hidden="true">☰</span>Menu</button>
</nav>
<script src="<?= base_url('assets/script.js') ?>?v=pwa-20260930-14"></script>
</body>
</html>
