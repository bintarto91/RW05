<?= $this->extend('layouts/public') ?>

<?= $this->section('content') ?>
<?php
$tentangRw = trim((string) ($profil['tentang_rw'] ?? '')) ?: 'RW 05 Desa Citeureup hadir sebagai wadah pelayanan, komunikasi, koordinasi, dan kebersamaan warga.';
$tagline = trim((string) ($profil['tagline'] ?? '')) ?: 'Transparan | Tertib | Melayani';
$alamatRw = trim((string) ($profil['alamat'] ?? '')) ?: 'Sekretariat RW 05 Desa Citeureup';
$emailRw = rw_official_email($profil['email'] ?? '');
$profileStats = [
    ['value' => (string) $totalKK, 'label' => 'KK tercatat'],
    ['value' => (string) $totalWarga, 'label' => 'Warga'],
    ['value' => (string) count($layanan), 'label' => 'Layanan'],
];
$valueItems = array_filter(array_map('trim', preg_split('/\s*\|\s*/', $tagline)));
if (empty($valueItems)) {
    $valueItems = ['Transparan', 'Tertib', 'Melayani'];
}
?>
<section class="page-hero profile-hero">
  <div class="container page-hero-grid">
    <div data-reveal>
      <p class="eyebrow">Profil RW</p>
      <h1>Profil <?= rw_esc($siteName) ?></h1>
      <p class="hero-text"><?= rw_esc($tagline) ?></p>
    </div>
    <div class="page-callout" data-reveal>
      <span>Data ringkas</span>
      <strong><?= rw_esc($totalKK) ?> KK / <?= rw_esc($totalWarga) ?> warga</strong>
      <p><?= rw_esc($alamatRw) ?></p>
    </div>
  </div>
</section>

<section class="section profile-overview-section">
  <div class="container profile-overview-card" data-reveal>
    <div class="profile-overview-copy">
      <p class="eyebrow">RW 05 Desa <?= rw_esc($desa ?? 'Citeureup') ?></p>
      <h2>Lingkungan yang tertib, rukun, dan mudah terhubung.</h2>
      <p><?= nl2br(rw_esc($tentangRw)) ?></p>
      <div class="profile-value-pills" aria-label="Nilai pelayanan RW">
        <?php foreach ($valueItems as $value): ?>
          <span><?= rw_esc($value) ?></span>
        <?php endforeach; ?>
      </div>
    </div>
    <div class="profile-overview-stats" aria-label="Ringkasan data RW">
      <?php foreach ($profileStats as $stat): ?>
        <article>
          <strong><?= rw_esc($stat['value']) ?></strong>
          <span><?= rw_esc($stat['label']) ?></span>
        </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="section white-section profile-direction-section">
  <div class="container profile-direction-grid">
    <article class="profile-direction-card" data-reveal>
      <p class="eyebrow">Visi</p>
      <h2>Arah pelayanan RW 05.</h2>
      <p><?= rw_esc($profil['visi'] ?? 'Mewujudkan pelayanan warga yang tertib, responsif, dan terbuka.') ?></p>
    </article>
    <article class="profile-direction-card" data-reveal>
      <p class="eyebrow">Misi</p>
      <h2>Langkah yang dijalankan.</h2>
      <p><?= nl2br(rw_esc($profil['misi'] ?? 'Misi RW akan diperbarui oleh pengurus.')) ?></p>
    </article>
  </div>
</section>

<section class="section profile-contact-band">
  <div class="container profile-contact-band-card" data-reveal>
    <div>
      <p class="eyebrow">Kontak RW</p>
      <h2>Hubungi kanal resmi RW 05.</h2>
      <p>Untuk administrasi, informasi, atau aspirasi, warga bisa memakai kanal resmi berikut.</p>
    </div>
    <div class="profile-contact-actions">
      <?php if ($waLink): ?>
        <a href="<?= rw_esc($waLink) ?>" target="_blank" rel="noopener noreferrer">WhatsApp RW</a>
      <?php endif; ?>
      <a href="mailto:<?= rw_esc($emailRw) ?>">Email RW</a>
      <span><?= rw_esc($alamatRw) ?></span>
    </div>
  </div>
</section>
<?= $this->endSection() ?>
