<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Login Admin RW 05</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,600;9..144,700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= base_url('assets/admin.css') ?>">
</head>
<body class="login-page">
  <div class="login-shell">
    <section class="login-brand">
      <a href="<?= site_url('/') ?>" class="login-back">Kembali ke website warga</a>

      <div class="login-brand-card">
        <div class="login-brand-head">
          <span class="brand-mark" aria-hidden="true">RW</span>
          <div>
            <strong>RW 05 Desa Citeureup</strong>
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
      <p class="admin-kicker">Masuk ke area pengurus</p>
      <h1>Login Admin</h1>
      <p>Gunakan akun pengurus untuk mengelola website RW 05 dari satu sistem yang sama.</p>

      <?php if ($error): ?><div class="alert error"><?= rw_esc($error) ?></div><?php endif; ?>

      <label>Username
        <input type="text" name="username" value="<?= field_value('username') ?>" required autofocus>
      </label>
      <label>Password
        <input type="password" name="password" required>
      </label>
      <button type="submit">Masuk ke Dashboard</button>
      <div class="login-note">Jika password awal belum diubah: admin / admin123</div>
    </form>
  </div>
</body>
</html>
