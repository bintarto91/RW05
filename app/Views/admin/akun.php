<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<?php
$editUser = $editUser ?? null;
$isEditUser = ! empty($editUser);
$roleOptions = $roleOptions ?? admin_role_options();
?>
<h1>Akun Admin</h1>
<p class="muted">Buat beberapa akun login untuk pengurus yang berbeda. Akun nonaktif tidak bisa masuk ke dashboard.</p>
<div class="alert warning">Role admin memakai pilihan tetap dan disimpan di kolom role tabel admin_users. Jadi tidak perlu membuat tabel role terpisah untuk kebutuhan panel ini.</div>

<?php if ($success): ?><div class="alert success"><?= rw_esc($success) ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert error"><?= rw_esc($error) ?></div><?php endif; ?>

<section class="panel compact-panel">
  <div class="section-heading compact-heading">
    <div>
      <h2>Ringkasan Akun</h2>
      <p class="muted">Saat ini Bapak masuk sebagai <?= rw_esc($admin['nama']) ?>.</p>
    </div>
  </div>
  <div class="stat-grid">
    <article class="info-card">
      <span class="info-label">Username Login</span>
      <strong class="info-value"><?= rw_esc($admin['username']) ?></strong>
    </article>
    <article class="info-card">
      <span class="info-label">Role</span>
      <strong class="info-value"><?= rw_esc($admin['role'] ?: 'admin') ?></strong>
    </article>
    <article class="info-card">
      <span class="info-label">Status</span>
      <strong class="info-value"><?= rw_esc($admin['status'] ?: 'aktif') ?></strong>
    </article>
    <article class="info-card">
      <span class="info-label">Total Akun</span>
      <strong class="info-value"><?= rw_esc((string) count($users ?? [])) ?></strong>
    </article>
  </div>
</section>

<section class="panel">
  <h2><?= $isEditUser ? 'Edit Akun Admin' : 'Tambah Akun Admin' ?></h2>
  <form method="post" action="<?= site_url('admin/akun') ?>" class="grid-form">
    <input type="hidden" name="action" value="save_user">
    <input type="hidden" name="user_id" value="<?= rw_esc((string) ($editUser['id'] ?? 0)) ?>">

    <label>Nama Pengurus
      <input type="text" name="nama" value="<?= rw_esc(old('nama', $editUser['nama'] ?? '')) ?>" placeholder="Nama lengkap pengurus" required>
    </label>
    <label>Username
      <input type="text" name="username" value="<?= rw_esc(old('username', $editUser['username'] ?? '')) ?>" placeholder="contoh: sekretaris" required>
      <span class="field-note">Boleh pakai spasi saat mengisi; sistem akan menyimpan sebagai underscore, contoh ketua_rw.</span>
    </label>
    <label>Role
      <select name="role" required>
        <?php foreach ($roleOptions as $value => $label): ?>
          <option value="<?= rw_esc($value) ?>" <?= is_selected(old('role', $editUser['role'] ?? 'admin'), $value) ?>><?= rw_esc($label) ?></option>
        <?php endforeach; ?>
      </select>
    </label>
    <label>Status
      <select name="status">
        <option value="aktif" <?= is_selected(old('status', $editUser['status'] ?? 'aktif'), 'aktif') ?>>Aktif</option>
        <option value="nonaktif" <?= is_selected(old('status', $editUser['status'] ?? 'aktif'), 'nonaktif') ?>>Nonaktif</option>
      </select>
      <?php if (($editUser['id'] ?? 0) === ($admin['id'] ?? -1)): ?>
        <span class="field-note">Akun yang sedang dipakai akan tetap aktif.</span>
      <?php endif; ?>
    </label>
    <label>Password <?= $isEditUser ? 'Baru / Reset' : 'Awal' ?>
      <input type="password" name="new_user_password" minlength="8" autocomplete="new-password" <?= $isEditUser ? '' : 'required' ?>>
      <span class="field-note"><?= $isEditUser ? 'Kosongkan jika password tidak ingin diganti.' : 'Minimal 8 karakter.' ?></span>
    </label>
    <label>Konfirmasi Password
      <input type="password" name="confirm_user_password" minlength="8" autocomplete="new-password" <?= $isEditUser ? '' : 'required' ?>>
    </label>

    <div class="full form-actions">
      <button type="submit"><?= $isEditUser ? 'Update Akun' : 'Buat Akun' ?></button>
      <?php if ($isEditUser): ?><a class="btn-light" href="<?= site_url('admin/akun') ?>">Batal Edit</a><?php endif; ?>
    </div>
  </form>
</section>

<section class="panel">
  <h2>Daftar Akun Admin</h2>
  <table>
    <thead>
      <tr>
        <th>Nama</th>
        <th>Username</th>
        <th>Role</th>
        <th>Status</th>
        <th>Dibuat</th>
        <th>Aksi</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach (($users ?? []) as $user): ?>
        <tr>
          <td><strong><?= rw_esc($user['nama']) ?></strong><?= (int) $user['id'] === (int) $admin['id'] ? '<br><small>Akun sedang dipakai</small>' : '' ?></td>
          <td><?= rw_esc($user['username']) ?></td>
          <td><?= rw_esc($roleOptions[$user['role'] ?? 'admin'] ?? ($user['role'] ?: 'Admin Umum')) ?></td>
          <td><span class="badge status-<?= ($user['status'] ?? '') === 'aktif' ? 'selesai' : 'menunggu' ?>"><?= rw_esc($user['status'] ?: 'aktif') ?></span></td>
          <td><?= rw_esc(fmt_date($user['created_at'])) ?></td>
          <td>
            <a href="<?= site_url('admin/akun?edit_user=' . (int) $user['id']) ?>">Edit</a>
            <?php if ((int) $user['id'] !== (int) $admin['id']): ?>
              <form method="post" action="<?= site_url('admin/akun') ?>" class="inline-form" onsubmit="return confirm('Hapus akun admin ini?')" style="display:inline">
                <input type="hidden" name="action" value="delete_user">
                <input type="hidden" name="user_id" value="<?= rw_esc((string) $user['id']) ?>">
                <button type="submit" class="btn-link-danger">Hapus</button>
              </form>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (empty($users)): ?>
        <tr><td colspan="6" class="table-empty">Belum ada akun admin.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</section>

<section class="panel">
  <h2>Ganti Password Akun Sendiri</h2>
  <p class="muted">Gunakan ini untuk mengganti password akun yang sedang dipakai login.</p>

  <form method="post" action="<?= site_url('admin/akun') ?>" class="grid-form">
    <input type="hidden" name="action" value="change_password">
    <label>Password Saat Ini
      <input type="password" name="current_password" autocomplete="current-password" required>
    </label>
    <label>Password Baru
      <input type="password" name="new_password" minlength="8" autocomplete="new-password" required>
    </label>
    <label>Konfirmasi Password Baru
      <input type="password" name="confirm_password" minlength="8" autocomplete="new-password" required>
    </label>

    <div class="full form-actions">
      <button type="submit">Simpan Password Baru</button>
      <a class="btn-light" href="<?= site_url('admin') ?>">Kembali ke Dashboard</a>
    </div>
  </form>
</section>
<?= $this->endSection() ?>
