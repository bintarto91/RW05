<?= $this->extend('layouts/public') ?>

<?= $this->section('content') ?>
<section class="page-hero education-page-hero">
  <div class="container page-hero-grid">
    <div data-reveal>
      <p class="eyebrow">Edukasi kesehatan</p>
      <h1>Karya edukasi yang mudah ditemukan warga.</h1>
      <p class="hero-text">Pilih topik kesehatan keluarga, lalu buka poster, video, atau artikel karya dosen dan sumber resmi yang sudah ditayangkan.</p>
      <div class="hero-actions">
        <a href="#daftar-edukasi" class="btn light">Pilih Topik</a>
        <a href="<?= site_url('kesehatan') ?>" class="btn outline-light">Kembali ke Kesehatan</a>
      </div>
    </div>
    <aside class="page-callout education-callout" data-reveal>
      <span>Perpustakaan kesehatan RW</span>
      <strong>Poster, video, dan artikel</strong>
      <p>Materi dikelompokkan berdasarkan kebutuhan warga sehingga tampil rapi dan mudah dibuka dari ponsel.</p>
      <div class="education-source-badge">Karya Dosen & Sumber Resmi</div>
    </aside>
  </div>
</section>

<section class="section education-index-section" id="daftar-edukasi" aria-labelledby="education-index-title">
  <div class="container">
    <div class="section-title" data-reveal>
      <p class="eyebrow">Pilih topik</p>
      <h2 id="education-index-title">Edukasi untuk seluruh keluarga.</h2>
      <p>Setiap topik memiliki halaman sendiri agar poster, video, dan artikel tidak bercampur.</p>
    </div>
    <nav class="education-topic-nav" aria-label="Daftar topik edukasi" data-reveal>
      <?php foreach ($educationTopics as $slug => $topic): ?>
        <?php $topicCount = count($materialsByCategory[$slug] ?? []); ?>
        <a href="<?= site_url('edukasi-kesehatan/' . $slug) ?>">
          <span><?= rw_esc($topic['number']) ?></span>
          <div>
            <strong><?= rw_esc($topic['title']) ?></strong>
            <small><?= rw_esc((string) $topicCount) ?> materi tersedia</small>
          </div>
        </a>
      <?php endforeach; ?>
    </nav>
  </div>
</section>

<section class="section education-list-section" aria-label="Kategori edukasi kesehatan">
  <div class="container education-grid">
    <?php foreach ($educationTopics as $slug => $topic): ?>
      <?php
      $topicMaterials = $materialsByCategory[$slug] ?? [];
      $typeCounts = ['poster' => 0, 'video' => 0, 'artikel' => 0];
      foreach ($topicMaterials as $material) {
          $type = (string) ($material['jenis'] ?? '');
          if (isset($typeCounts[$type])) {
              $typeCounts[$type]++;
          }
      }
      ?>
      <a class="education-card" href="<?= site_url('edukasi-kesehatan/' . $slug) ?>" aria-label="Buka materi <?= rw_esc($topic['title']) ?>" data-reveal>
        <div class="education-card-head">
          <span><?= rw_esc($topic['number']) ?></span>
          <div>
            <small>Materi keluarga</small>
            <h2><?= rw_esc($topic['title']) ?></h2>
          </div>
        </div>
        <p><?= rw_esc($topic['description']) ?></p>
        <div class="education-learn-list">
          <strong>Yang dapat dipelajari</strong>
          <ul>
            <?php foreach ($topic['items'] as $item): ?>
              <li><?= rw_esc($item) ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
        <div class="education-material-counts" aria-label="Jumlah materi berdasarkan jenis">
          <span>Poster <b><?= rw_esc((string) $typeCounts['poster']) ?></b></span>
          <span>Video <b><?= rw_esc((string) $typeCounts['video']) ?></b></span>
          <span>Artikel <b><?= rw_esc((string) $typeCounts['artikel']) ?></b></span>
        </div>
        <span class="education-card-cta">
          <?= count($topicMaterials) > 0 ? 'Buka ' . rw_esc((string) count($topicMaterials)) . ' Materi' : 'Belum ada materi' ?>
          <b aria-hidden="true">→</b>
        </span>
      </a>
    <?php endforeach; ?>
  </div>
</section>

<section class="section education-note-section">
  <div class="container">
    <div class="education-note" data-reveal>
      <div>
        <p class="eyebrow">Catatan penting</p>
        <h2>Edukasi membantu memahami, bukan menggantikan pemeriksaan.</h2>
        <p>Untuk keluhan, diagnosis, atau penanganan kesehatan, hubungi tenaga dan fasilitas kesehatan. Hindari membagikan data kesehatan pribadi melalui ruang publik.</p>
      </div>
      <a href="<?= site_url('kesehatan') ?>#jadwal-bantuan" class="btn primary">Lihat Jalur Bantuan</a>
    </div>
  </div>
</section>
<?= $this->endSection() ?>
