<?php
$currentPage = $currentPage ?? 'dashboard';
$navItems = [
    'dashboard' => ['label' => 'Dashboard', 'href' => site_url('admin')],
    'profil' => ['label' => 'Profil RW', 'href' => site_url('admin/profil')],
    'program' => ['label' => 'Program Kerja', 'href' => site_url('admin/program')],
    'kegiatan' => ['label' => 'Kegiatan', 'href' => site_url('admin/kegiatan')],
    'layanan' => ['label' => 'Layanan', 'href' => site_url('admin/layanan')],
    'edukasi' => ['label' => 'Materi Edukasi', 'href' => site_url('admin/edukasi')],
    'pengajuan-surat' => ['label' => 'Pengajuan Surat', 'href' => site_url('admin/pengajuan-surat')],
    'pengurus' => ['label' => 'Pengurus', 'href' => site_url('admin/pengurus')],
    'warga' => ['label' => 'Data Warga', 'href' => site_url('admin/warga')],
    'keuangan' => ['label' => 'Keuangan', 'href' => site_url('admin/keuangan')],
    'import' => ['label' => 'Import Massal', 'href' => site_url('admin/import')],
    'aspirasi' => ['label' => 'Aspirasi', 'href' => site_url('admin/aspirasi')],
    'akun' => ['label' => 'Akun Admin', 'href' => site_url('admin/akun')],
];
$isSuperAdmin = (string) session('admin_role') === 'superadmin';
if (! $isSuperAdmin) {
    unset($navItems['akun'], $navItems['import']);
}
$healthDashboard = ['label' => 'Buka Dashboard Kesehatan', 'href' => site_url('admin/kesehatan-dashboard')];
$currentLabel = $navItems[$currentPage]['label'] ?? 'Panel Admin';
$notificationCounts = ['pengajuan-surat' => 0, 'aspirasi' => 0, 'akun' => 0];
try {
    $db = db_connect();
    if (ensure_pengajuan_surat_table($db)) {
        $notificationCounts['pengajuan-surat'] = (int) ($db->query("SELECT COUNT(*) AS total FROM pengajuan_surat WHERE status='menunggu'")->getRowArray()['total'] ?? 0);
    }
    $notificationCounts['aspirasi'] = (int) ($db->query("SELECT COUNT(*) AS total FROM aspirasi WHERE status='baru'")->getRowArray()['total'] ?? 0);
    if ($isSuperAdmin && ensure_admin_users_table($db)) {
        $notificationCounts['akun'] = (int) ($db->query("SELECT COUNT(*) AS total FROM admin_users WHERE status='menunggu'")->getRowArray()['total'] ?? 0);
    }
} catch (Throwable $exception) {
    $notificationCounts = ['pengajuan-surat' => 0, 'aspirasi' => 0, 'akun' => 0];
}
$notificationTotal = array_sum($notificationCounts);
$adminIdentity = rw_site_identity();
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="robots" content="noindex,nofollow,noarchive">
  <title><?= rw_esc($currentLabel) ?> | Admin <?= rw_esc($adminIdentity['displayName']) ?></title>
  <link rel="icon" type="image/svg+xml" href="<?= base_url('favicon.svg') ?>?v=rw05-20260706">
  <link rel="shortcut icon" href="<?= base_url('favicon.svg') ?>?v=rw05-20260706">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,600;9..144,700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= base_url('assets/admin.css') ?>?v=org-panel-20261001-6">
</head>
<body class="admin-body">
  <div class="admin-shell">
    <aside class="sidebar">
      <a href="<?= site_url('/') ?>" class="brand-admin" target="_blank" rel="noreferrer">
        <span class="brand-mark" aria-hidden="true">RW</span>
        <span class="brand-copy">
          <strong><?= rw_esc($adminIdentity['name']) ?></strong>
          <span>Desa Citeureup</span>
        </span>
      </a>

      <div class="admin-user">
        <span class="admin-user-label">Masuk sebagai</span>
        <strong><?= rw_esc(session('admin_nama') ?? 'Admin') ?></strong>
        <span class="admin-user-role"><?= rw_esc($currentLabel) ?></span>
      </div>

      <div class="nav-group">
        <p class="nav-caption">Kelola konten warga</p>
        <nav class="admin-nav">
          <?php foreach ($navItems as $key => $item): ?>
            <a href="<?= rw_esc($item['href']) ?>" class="<?= $currentPage === $key ? 'is-active' : '' ?>">
              <span><?= rw_esc($item['label']) ?></span>
              <?php if (($notificationCounts[$key] ?? 0) > 0): ?>
                <em class="nav-badge"><?= rw_esc((string) $notificationCounts[$key]) ?></em>
              <?php endif; ?>
            </a>
          <?php endforeach; ?>
        </nav>
      </div>

      <div class="nav-group admin-health-gateway">
        <p class="nav-caption">Ruang kerja kesehatan</p>
        <a href="<?= rw_esc($healthDashboard['href']) ?>">
          <span>
            <strong><?= rw_esc($healthDashboard['label']) ?></strong>
            <small>Posyandu, Posbindu, jadwal, dan tindak lanjut</small>
          </span>
          <b aria-hidden="true">&#8594;</b>
        </a>
      </div>

      <div class="sidebar-actions">
        <a href="<?= site_url('/') ?>" target="_blank" rel="noreferrer">Lihat Website</a>
        <form method="post" action="<?= site_url('admin/logout') ?>">
          <?= csrf_field() ?>
          <button type="submit" class="is-danger">Logout</button>
        </form>
      </div>
    </aside>

    <main class="admin-main">
      <header class="admin-topbar">
        <div>
          <p class="admin-kicker">Panel Pengurus <?= rw_esc($adminIdentity['displayName']) ?></p>
          <strong><?= rw_esc($currentLabel) ?></strong>
          <span>Semua perubahan di area ini langsung terhubung ke website warga pada domain yang sama.</span>
        </div>
        <a href="<?= site_url('/') ?>" class="topbar-link" target="_blank" rel="noreferrer">Lihat Website Warga</a>
      </header>

      <?php if ($notificationTotal > 0): ?>
        <section class="admin-notice-bar" aria-label="Notifikasi admin">
          <?php if ($notificationCounts['pengajuan-surat'] > 0): ?>
            <a href="<?= site_url('admin/pengajuan-surat') ?>"><strong><?= rw_esc((string) $notificationCounts['pengajuan-surat']) ?></strong> pengajuan surat baru</a>
          <?php endif; ?>
          <?php if ($notificationCounts['aspirasi'] > 0): ?>
            <a href="<?= site_url('admin/aspirasi') ?>"><strong><?= rw_esc((string) $notificationCounts['aspirasi']) ?></strong> aspirasi baru</a>
          <?php endif; ?>
          <?php if ($isSuperAdmin && $notificationCounts['akun'] > 0): ?>
            <a href="<?= site_url('admin/akun') ?>"><strong><?= rw_esc((string) $notificationCounts['akun']) ?></strong> pendaftaran akun menunggu persetujuan</a>
          <?php endif; ?>
        </section>
      <?php endif; ?>

      <?= $this->renderSection('content') ?>
    </main>
  </div>
</body>
</html>
