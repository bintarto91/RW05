<?= $this->extend('layouts/public') ?>

<?= $this->section('content') ?>
<?php $kategoriOptions = ['Aspirasi', 'Laporan Lingkungan', 'Keamanan', 'Kebersihan', 'Layanan Administrasi']; ?>
<section class="page-hero contact-page-hero">
  <div class="container page-hero-grid">
    <div data-reveal>
      <p class="eyebrow">Aspirasi warga</p>
      <h1>Kirim pesan ke pengurus RW.</h1>
      <p class="hero-text">Gunakan form ini untuk saran, laporan lingkungan, keamanan, kebersihan, atau kebutuhan layanan. Jangan kirim NIK, nomor KK, atau dokumen pribadi.</p>
    </div>
    <div class="page-callout" data-reveal>
      <span>Aspirasi masuk</span>
      <strong><?= rw_esc($totalAspirasi) ?> pesan</strong>
      <p>Pesan warga akan ditinjau pengurus melalui panel admin.</p>
    </div>
  </div>
</section>

<section class="section contact-section">
  <div class="container contact-layout">
    <div class="contact-copy" data-reveal>
      <p class="eyebrow">Kontak resmi</p>
      <h2>Hubungi RW 05.</h2>
      <div class="contact-list">
        <div>
          <span>WhatsApp</span>
          <strong><?= rw_esc($profil['whatsapp'] ?? 'Belum tersedia') ?></strong>
        </div>
        <div>
          <span>Instagram</span>
          <strong><?= rw_esc($profil['instagram'] ?? 'Belum tersedia') ?></strong>
        </div>
        <div>
          <span>Email</span>
          <strong><?= rw_esc(rw_official_email($profil['email'] ?? '')) ?></strong>
        </div>
        <div>
          <span>Alamat</span>
          <strong><?= rw_esc($profil['alamat'] ?? 'Sekretariat RW 05 Desa Citeureup') ?></strong>
        </div>
      </div>
      <div class="contact-actions">
        <?php if ($waLink): ?>
          <a href="<?= rw_esc($waLink) ?>" class="btn light" target="_blank" rel="noopener noreferrer">Chat WhatsApp</a>
        <?php endif; ?>
        <?php if ($instagramLink): ?>
          <a href="<?= rw_esc($instagramLink) ?>" class="btn outline-light" target="_blank" rel="noopener noreferrer">Buka Instagram</a>
        <?php endif; ?>
      </div>
    </div>

    <form method="post" action="<?= site_url('aspirasi') ?>" class="aspirasi-form" data-reveal>
      <?php if ($success ?? false): ?>
        <div class="alert success">Terima kasih. Aspirasi sudah terkirim dan akan ditinjau pengurus.</div>
      <?php endif; ?>
      <?php if (! empty($error)): ?>
        <div class="alert error"><?= rw_esc($error) ?></div>
      <?php endif; ?>
      <div class="form-grid">
        <label>Nama
          <input type="text" name="nama" placeholder="Nama warga" value="<?= field_value('nama') ?>" required>
        </label>
        <label>No HP / WhatsApp
          <input type="text" name="no_hp" placeholder="08xx" value="<?= field_value('no_hp') ?>">
        </label>
        <label>RT
          <input type="text" name="rt" placeholder="Contoh: 01" value="<?= field_value('rt') ?>">
        </label>
        <label>Kategori
          <select name="kategori">
            <?php foreach ($kategoriOptions as $option): ?>
              <option value="<?= rw_esc($option) ?>" <?= is_selected(old('kategori', 'Aspirasi'), $option) ?>><?= rw_esc($option) ?></option>
            <?php endforeach; ?>
          </select>
        </label>
        <label class="full">Pesan
          <textarea name="pesan" rows="5" placeholder="Tulis pesan singkat dan jelas..." required><?= field_value('pesan') ?></textarea>
        </label>
      </div>
      <p class="form-note">Pesan akan dibaca pengurus RW. Sertakan detail seperlunya agar tindak lanjut lebih cepat.</p>
      <button type="submit" class="btn primary full-button">Kirim Aspirasi</button>
    </form>
  </div>
</section>
<?= $this->endSection() ?>
