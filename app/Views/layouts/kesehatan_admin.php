<?php
$currentPage = $currentPage ?? 'kesehatan-dashboard';
$navItems = [
    'kesehatan-dashboard' => ['label' => 'Beranda Kesehatan', 'href' => site_url('admin/kesehatan-dashboard')],
    'posyandu' => ['label' => 'Posyandu', 'href' => site_url('admin/kesehatan-data?jenis=posyandu&jenis_kegiatan=posyandu')],
    'posbindu' => ['label' => 'Posbindu', 'href' => site_url('admin/posbindu-laporan')],
    'kesehatan-tindak-lanjut' => ['label' => 'Tindak Lanjut', 'href' => site_url('admin/kesehatan-tindak-lanjut')],
    'kesehatan-jadwal' => ['label' => 'Jadwal', 'href' => site_url('admin/kesehatan-jadwal')],
];
$currentLabel = $navItems[$currentPage]['label'] ?? 'Ruang Kerja Kesehatan';
$role = (string) (session('admin_role') ?? '');
$roleLabel = admin_role_options()[$role] ?? 'Petugas Kesehatan';
$canReturnToRwAdmin = $role !== 'kader_kesehatan';
$workspaceError = session()->getFlashdata('workspace_error') ?: '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
  <title><?= rw_esc($currentLabel) ?> | Kesehatan RW 05</title>
  <link rel="icon" type="image/svg+xml" href="<?= base_url('favicon.svg') ?>?v=rw05-20260706">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,600;9..144,700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= base_url('assets/admin.css') ?>?v=org-chart-20260930-2">
</head>
<body class="admin-body health-admin-body">
  <div class="admin-shell health-admin-shell">
    <aside class="sidebar health-sidebar">
      <a href="<?= site_url('admin/kesehatan-dashboard') ?>" class="brand-admin">
        <span class="brand-mark health-brand-mark" aria-hidden="true">+</span>
        <span class="brand-copy">
          <strong>Kesehatan RW 05</strong>
          <span>Posyandu ILP & Posbindu</span>
        </span>
      </a>

      <div class="admin-user">
        <span class="admin-user-label">Petugas yang masuk</span>
        <strong><?= rw_esc(session('admin_nama') ?? 'Petugas') ?></strong>
        <span class="admin-user-role"><?= rw_esc($roleLabel) ?></span>
      </div>

      <div class="nav-group">
        <p class="nav-caption">Ruang kerja kesehatan</p>
        <nav class="admin-nav">
          <?php foreach ($navItems as $key => $item): ?>
            <a href="<?= rw_esc($item['href']) ?>" class="<?= $currentPage === $key ? 'is-active' : '' ?>">
              <span><?= rw_esc($item['label']) ?></span>
            </a>
          <?php endforeach; ?>
        </nav>
      </div>

      <details class="health-mobile-account">
        <summary>Akun &amp; menu lainnya</summary>
        <div>
          <strong><?= rw_esc(session('admin_nama') ?? 'Petugas') ?></strong>
          <span><?= rw_esc($roleLabel) ?></span>
          <a href="<?= site_url('kesehatan') ?>" target="_blank" rel="noreferrer">Halaman warga</a>
          <?php if ($canReturnToRwAdmin): ?><a href="<?= site_url('admin') ?>">Panel RW</a><?php endif; ?>
          <form method="post" action="<?= site_url('admin/logout') ?>"><?= csrf_field() ?><button type="submit">Logout</button></form>
        </div>
      </details>

      <div class="sidebar-actions">
        <a href="<?= site_url('kesehatan') ?>" target="_blank" rel="noreferrer">Halaman Kesehatan Warga</a>
        <?php if ($canReturnToRwAdmin): ?>
          <a href="<?= site_url('admin') ?>">Kembali ke Panel RW</a>
        <?php endif; ?>
        <form method="post" action="<?= site_url('admin/logout') ?>">
          <?= csrf_field() ?>
          <button type="submit" class="is-danger">Logout</button>
        </form>
      </div>
    </aside>

    <main class="admin-main health-admin-main">
      <header class="admin-topbar health-topbar">
        <div>
          <p class="admin-kicker">Ruang Kerja Kesehatan RW 05</p>
          <strong><?= rw_esc($currentLabel) ?></strong>
          <span>Pencatatan operasional kader, pemantauan, dan tindak lanjut layanan warga.</span>
        </div>
        <a href="<?= site_url('kesehatan') ?>" class="topbar-link" target="_blank" rel="noreferrer">Lihat Informasi Warga</a>
      </header>

      <?php if ($workspaceError !== ''): ?>
        <div class="alert warning"><?= rw_esc($workspaceError) ?></div>
      <?php endif; ?>

      <?= $this->renderSection('content') ?>
    </main>
  </div>
  <script>if('serviceWorker' in navigator){window.addEventListener('load',()=>navigator.serviceWorker.register('/service-worker.js',{updateViaCache:'none'}).then(r=>r.update()).catch(()=>{}));}</script>
</body>
</html>
