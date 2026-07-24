<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<h1>Profil RW</h1>

<?php if ($success): ?><div class="alert success"><?= rw_esc($success) ?></div><?php endif; ?>

<section class="panel">
  <form method="post" action="<?= site_url('admin/profil') ?>" class="grid-form">
    <label>Nama RW <input type="text" name="nama_rw" value="<?= rw_esc($profil['nama_rw'] ?? '') ?>" required></label>
    <label>Desa <input type="text" name="desa" value="<?= rw_esc($profil['desa'] ?? '') ?>" required></label>
    <label>Kecamatan <input type="text" name="kecamatan" value="<?= rw_esc($profil['kecamatan'] ?? '') ?>" required></label>
    <label>Kabupaten <input type="text" name="kabupaten" value="<?= rw_esc($profil['kabupaten'] ?? '') ?>" required></label>
    <label>Instagram <input type="text" name="instagram" value="<?= rw_esc($profil['instagram'] ?? '') ?>"></label>
    <label>WhatsApp <input type="text" name="whatsapp" value="<?= rw_esc($profil['whatsapp'] ?? '') ?>"></label>
    <label>Email <input type="text" name="email" value="<?= rw_esc($profil['email'] ?? '') ?>"></label>
    <label>Tagline <input type="text" name="tagline" value="<?= rw_esc($profil['tagline'] ?? '') ?>"></label>
    <label class="full">Alamat <textarea name="alamat" rows="3"><?= rw_esc($profil['alamat'] ?? '') ?></textarea></label>
    <label class="full">Tentang RW <textarea name="tentang_rw" rows="6"><?= rw_esc($profil['tentang_rw'] ?? '') ?></textarea></label>
    <label class="full">Sambutan Ketua <textarea name="sambutan_ketua" rows="7"><?= rw_esc($profil['sambutan_ketua'] ?? '') ?></textarea></label>
    <label class="full">Visi <textarea name="visi" rows="3"><?= rw_esc($profil['visi'] ?? '') ?></textarea></label>
    <label class="full">Misi <textarea name="misi" rows="7"><?= rw_esc($profil['misi'] ?? '') ?></textarea></label>
    <button type="submit">Simpan Profil</button>
  </form>
</section>
<?= $this->endSection() ?>
