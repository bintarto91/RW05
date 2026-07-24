<?= $this->extend('layouts/public') ?>

<?= $this->section('content') ?>
<section class="page-hero">
  <div class="container page-hero-grid">
    <div data-reveal>
      <p class="eyebrow">Kegiatan dan program</p>
      <h1>Informasi resmi untuk warga.</h1>
      <p class="hero-text">Kegiatan, pengumuman, dan program kerja ditampilkan ringkas agar warga cepat memahami informasi terbaru.</p>
    </div>
    <div class="page-callout" data-reveal>
      <span>Data aktif</span>
      <strong><?= rw_esc(count($kegiatan)) ?> kegiatan</strong>
      <p><?= rw_esc(count($programs)) ?> program kerja aktif tercatat.</p>
    </div>
  </div>
</section>

<section class="section">
  <div class="container split-stack">
    <div class="stack-column" data-reveal>
      <div class="section-title left">
        <p class="eyebrow">Kegiatan terbaru</p>
        <h2>Agenda dan pengumuman.</h2>
      </div>
      <?php if ($kegiatan): ?>
        <div class="news-list">
          <?php foreach ($kegiatan as $item): ?>
            <article class="news-item">
              <time><?= rw_esc(format_date_id($item['tanggal'])) ?></time>
              <h3><?= rw_esc($item['judul']) ?></h3>
              <p><?= rw_esc($item['isi']) ?></p>
            </article>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <p class="empty-state">Belum ada kegiatan atau pengumuman yang dipublikasikan.</p>
      <?php endif; ?>
    </div>

    <div class="stack-column" data-reveal>
      <div class="section-title left">
        <p class="eyebrow">Program kerja</p>
        <h2>Prioritas pengurus.</h2>
      </div>
      <?php if ($programs): ?>
        <div class="program-list">
          <?php foreach ($programs as $program): ?>
            <article class="program-card">
              <span><?= rw_esc(str_pad((string) $program['nomor'], 2, '0', STR_PAD_LEFT)) ?></span>
              <div>
                <h3><?= rw_esc($program['judul']) ?></h3>
                <p><?= rw_esc($program['deskripsi']) ?></p>
              </div>
            </article>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <p class="empty-state">Daftar program kerja belum tersedia.</p>
      <?php endif; ?>
    </div>
  </div>
</section>
<?= $this->endSection() ?>
