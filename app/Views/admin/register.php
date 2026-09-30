<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Daftar Akun Pengurus | RW 05</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,600;9..144,700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= base_url('assets/admin.css') ?>?v=admin-refresh-20260930">
</head>
<body class="login-page register-page">
  <div class="login-shell register-shell">
    <section class="login-brand">
      <a href="<?= site_url('admin/login') ?>" class="login-back">&#8592; Kembali ke halaman login</a>
      <div class="login-brand-card register-brand-card">
        <div class="login-brand-head">
          <span class="brand-mark login-logo-mark"><img src="<?= base_url('assets/logo-rw05.png') ?>" alt="Logo RW 05"></span>
          <div>
            <strong>Pendaftaran Pengurus</strong>
            <p>Akses panel diberikan hanya kepada pengurus RW, operator, kader, atau tenaga kesehatan yang telah diverifikasi.</p>
          </div>
        </div>
        <ol class="register-steps">
          <li><b>1</b><span>Isi identitas dan pilih peran.</span></li>
          <li><b>2</b><span>Super Admin memeriksa pendaftaran.</span></li>
          <li><b>3</b><span>Login setelah status akun disetujui.</span></li>
        </ol>
      </div>
    </section>

    <form method="post" action="<?= site_url('admin/daftar') ?>" class="login-box register-box">
      <?= csrf_field() ?>
      <p class="admin-kicker">Akun baru</p>
      <h1>Daftar Akses Panel</h1>
      <p>Gunakan data yang dapat dikenali oleh Super Admin RW 05.</p>

      <?php if ($error): ?><div class="alert error"><?= rw_esc($error) ?></div><?php endif; ?>
      <?php if ($success): ?><div class="alert success"><?= rw_esc($success) ?></div><?php endif; ?>

      <div class="register-grid">
        <label>Nama Lengkap
          <input type="text" name="nama" value="<?= field_value('nama') ?>" maxlength="120" autocomplete="name" required autofocus>
        </label>
        <label>Nomor WhatsApp
          <input type="tel" name="no_hp" value="<?= field_value('no_hp') ?>" maxlength="30" placeholder="08xxxxxxxxxx" autocomplete="tel" required>
        </label>
        <label>Username
          <input type="text" name="username" value="<?= field_value('username') ?>" minlength="3" maxlength="40" autocomplete="username" required>
        </label>
        <label>Peran yang Diajukan
          <select name="role" required>
            <option value="">Pilih peran</option>
            <?php foreach ($roleOptions as $value => $label): ?>
              <option value="<?= rw_esc($value) ?>" <?= is_selected(old('role'), $value) ?>><?= rw_esc($label) ?></option>
            <?php endforeach; ?>
          </select>
        </label>
        <label>Password
          <input type="password" name="password" minlength="10" autocomplete="new-password" required>
          <span class="field-note">Minimal 10 karakter.</span>
        </label>
        <label>Ulangi Password
          <input type="password" name="password_confirmation" minlength="10" autocomplete="new-password" required>
        </label>
        <label class="full">Keterangan untuk Super Admin <span class="field-note">(opsional)</span>
          <textarea name="catatan_pendaftaran" rows="3" maxlength="500" placeholder="Contoh: Kader Posyandu RT 03"><?= field_value('catatan_pendaftaran') ?></textarea>
        </label>
      </div>
      <button type="submit">Kirim Pendaftaran</button>
      <div class="login-note">Pendaftaran tidak langsung memberikan akses. Super Admin harus menyetujuinya terlebih dahulu.</div>
    </form>
  </div>
</body>
</html>
