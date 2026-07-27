<?= $this->extend('layouts/public') ?>

<?= $this->section('content') ?>
<section class="page-hero education-page-hero education-topic-page-hero">
  <div class="container page-hero-grid">
    <div data-reveal>
      <p class="eyebrow">Edukasi kesehatan <?= rw_esc($topic['number']) ?></p>
      <h1><?= rw_esc($topic['title']) ?></h1>
      <p class="hero-text"><?= rw_esc($topic['description']) ?></p>
      <div class="hero-actions">
        <a href="#materi-topik" class="btn light">Lihat Materi</a>
        <a href="<?= site_url('edukasi-kesehatan') ?>" class="btn outline-light">Semua Topik</a>
      </div>
    </div>
    <aside class="page-callout education-callout" data-reveal>
      <span>Yang dapat dipelajari</span>
      <ul class="education-topic-summary-list">
        <?php foreach ($topic['items'] as $item): ?>
          <li><?= rw_esc($item) ?></li>
        <?php endforeach; ?>
      </ul>
    </aside>
  </div>
</section>

<section class="section education-resources-section" id="materi-topik" aria-labelledby="education-resources-title">
  <div class="container">
    <div class="education-resource-heading" data-reveal>
      <div class="section-title">
        <p class="eyebrow">Perpustakaan materi</p>
        <h2 id="education-resources-title">Poster, video, dan artikel.</h2>
        <p>Pilih jenis materi atau buka langsung karya yang ingin dipelajari.</p>
      </div>
      <a class="education-back-link" href="<?= site_url('edukasi-kesehatan') ?>">← Kembali ke semua topik</a>
    </div>

    <nav class="education-filter-nav" aria-label="Filter jenis materi" data-reveal>
      <a href="<?= site_url('edukasi-kesehatan/' . $category) ?>" class="<?= $selectedType === '' ? 'is-active' : '' ?>">Semua</a>
      <?php foreach ($typeOptions as $value => $label): ?>
        <a href="<?= site_url('edukasi-kesehatan/' . $category) . '?jenis=' . rawurlencode($value) ?>" class="<?= $selectedType === $value ? 'is-active' : '' ?>"><?= rw_esc($label) ?></a>
      <?php endforeach; ?>
    </nav>

    <?php if (! empty($materials)): ?>
      <div class="education-resource-grid">
        <?php foreach ($materials as $material): ?>
          <?php
          $type = (string) ($material['jenis'] ?? '');
          $materialUrl = edukasi_material_public_url($material);
          $hasFile = trim((string) ($material['file_path'] ?? '')) !== '';
          $meta = array_filter([
              trim((string) ($material['institusi'] ?? '')),
              trim((string) ($material['tahun'] ?? '')),
          ], static fn ($value) => $value !== '');
          ?>
          <article class="education-resource-card type-<?= rw_esc($type) ?>" data-reveal>
            <div class="education-resource-head">
              <span class="education-type-label"><?= rw_esc($typeOptions[$type] ?? ucfirst($type)) ?></span>
              <span class="education-resource-number"><?= rw_esc($topic['number']) ?></span>
            </div>
            <div class="education-resource-copy">
              <h2><?= rw_esc($material['judul'] ?? '') ?></h2>
              <?php if (! empty($material['ringkasan'])): ?>
                <p><?= rw_esc($material['ringkasan']) ?></p>
              <?php endif; ?>
            </div>
            <div class="education-resource-author">
              <span>Dosen / Penulis</span>
              <strong><?= rw_esc($material['penulis'] ?? '') ?></strong>
              <?php if (! empty($meta)): ?><small><?= rw_esc(implode(' • ', $meta)) ?></small><?php endif; ?>
            </div>
            <?php if ($materialUrl !== ''): ?>
              <a href="<?= rw_esc($materialUrl) ?>" class="btn primary education-resource-action" target="_blank" rel="noopener noreferrer">
                <?= rw_esc(edukasi_material_action_label($type, $hasFile)) ?>
              </a>
            <?php else: ?>
              <span class="education-resource-unavailable">Media belum tersedia</span>
            <?php endif; ?>
          </article>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <div class="education-empty-state" data-reveal>
        <strong>Belum ada materi yang ditayangkan.</strong>
        <p>Coba pilih jenis materi lain atau kembali lagi setelah admin menambahkan karya baru.</p>
        <a href="<?= site_url('edukasi-kesehatan/' . $category) ?>" class="btn secondary">Lihat Semua Jenis</a>
      </div>
    <?php endif; ?>
  </div>
</section>

<section class="section education-note-section">
  <div class="container">
    <div class="education-note" data-reveal>
      <div>
        <p class="eyebrow">Catatan penting</p>
        <h2>Gunakan materi kesehatan sebagai edukasi.</h2>
        <p>Materi ini tidak menggantikan diagnosis atau pemeriksaan tenaga kesehatan. Untuk keluhan dan penanganan, hubungi fasilitas kesehatan.</p>
      </div>
      <a href="<?= site_url('kesehatan') ?>#jadwal-bantuan" class="btn primary">Lihat Jalur Bantuan</a>
    </div>
  </div>
</section>
<?= $this->endSection() ?>
