<?php $adminIdentity = rw_site_identity(); ?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Login Admin <?= rw_esc($adminIdentity['displayName']) ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="robots" content="noindex,nofollow,noarchive">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,600;9..144,700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= base_url('assets/admin.css') ?>?v=admin-refresh-20260930-2">
</head>
<body class="login-page">
  <div class="login-shell">
    <section class="login-brand">
      <a href="<?= site_url('/') ?>" class="login-back">Kembali ke website warga</a>

      <div class="login-brand-card">
        <div class="login-brand-head">
          <span class="brand-mark login-logo-mark"><img src="<?= base_url('assets/logo-rw05.png') ?>" alt="Logo RW 05"></span>
          <div>
            <strong><?= rw_esc($adminIdentity['name']) ?></strong>
            <small>Desa Citeureup</small>
            <p>Panel admin ini menjadi bagian dari website yang sama, jadi pengurus bisa memperbarui konten warga dari satu tempat.</p>
          </div>
        </div>

        <ul class="login-points">
          <li>Kelola profil, layanan, program, kegiatan, dan pengurus.</li>
          <li>Perubahan data langsung tampil di website warga.</li>
          <li>Import CSV tetap tersedia untuk pengisian data awal yang cepat.</li>
        </ul>
      </div>
    </section>

    <form method="post" action="<?= site_url('admin/login') ?>" class="login-box">
      <?= csrf_field() ?>
      <p class="admin-kicker">Masuk ke area pengurus</p>
      <h1>Login Admin</h1>
      <p>Gunakan akun pengurus untuk mengelola website RW 05 dari satu sistem yang sama.</p>

      <?php if ($error): ?><div class="alert error"><?= rw_esc($error) ?></div><?php endif; ?>

      <label>Username
        <input type="text" name="username" value="<?= field_value('username') ?>" maxlength="40" autocomplete="username" autocapitalize="none" spellcheck="false" required autofocus>
      </label>
      <label>Password
        <input type="password" name="password" maxlength="1024" autocomplete="current-password" required>
      </label>
      <button type="submit">Masuk ke Dashboard</button>
      <div class="login-register-callout">
        <strong>Belum memiliki akun?</strong>
        <span>Pengurus RW dan kader dapat mendaftar. Akun baru tetap harus disetujui Super Admin.</span>
        <a href="<?= site_url('admin/daftar') ?>">Daftar akun baru</a>
      </div>
      <div class="login-note">Jika lupa akses login, hubungi Super Admin untuk reset akun.</div>
    </form>
  </div>
</body>
</html>
