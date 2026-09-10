<?= $this->extend('layouts/public') ?>

<?= $this->section('content') ?>
<section class="page-hero health-page-hero">
  <div class="container page-hero-grid">
    <div data-reveal>
      <p class="eyebrow"><?= rw_esc($service['eyebrow']) ?></p>
      <h1><?= rw_esc($service['title']) ?></h1>
      <p class="hero-text"><?= rw_esc($service['description']) ?></p>
      <div class="hero-actions">
        <a href="#jadwal-layanan" class="btn light">Lihat Jadwal</a>
        <a href="<?= site_url('kesehatan') ?>" class="btn outline-light">Kembali ke Kesehatan</a>
      </div>
    </div>
    <aside class="page-callout health-callout" data-reveal>
      <span>Informasi untuk warga</span>
      <strong><?= rw_esc(ucfirst($serviceType)) ?></strong>
      <p>Jadwal dan informasi umum dapat dilihat warga. Data peserta dan catatan kader hanya tersedia di area admin.</p>
    </aside>
  </div>
</section>

<section class="section white-section" aria-labelledby="service-info-title">
  <div class="container health-service-grid">
    <article class="health-schedule-card" data-reveal>
      <p class="eyebrow">Layanan utama</p>
      <h2 id="service-info-title">Yang dapat disiapkan warga.</h2>
      <ul class="health-step-list">
        <?php foreach ($service['items'] as $index => $item): ?>
          <li><span><?= rw_esc((string) ($index + 1)) ?></span><div><strong><?= rw_esc($item) ?></strong><p>Konfirmasi jadwal dan kebutuhan kepada kader sebelum datang.</p></div></li>
        <?php endforeach; ?>
      </ul>
    </article>
    <aside class="health-help-card" data-reveal>
      <p class="eyebrow">Privasi warga</p>
      <h2>Data peserta tetap terlindungi.</h2>
      <p>Website publik hanya menampilkan informasi layanan dan jadwal. Data peserta serta catatan kunjungan dikelola kader melalui login admin.</p>
      <a href="<?= rw_esc($waLink ?: site_url('aspirasi')) ?>"<?= ! empty($waLink) ? ' target="_blank" rel="noopener noreferrer"' : '' ?> class="btn primary">Hubungi Pengurus</a>
    </aside>
  </div>
</section>

<section class="section health-calendar-section" id="jadwal-layanan" aria-labelledby="service-schedule-title">
  <div class="container">
    <div class="section-title" data-reveal>
      <p class="eyebrow">Jadwal layanan</p>
      <h2 id="service-schedule-title">Jadwal <?= rw_esc(ucfirst($serviceType)) ?> terdekat.</h2>
      <p>Datang sesuai waktu dan lokasi yang diumumkan oleh kader.</p>
    </div>
    <?php if (! empty($healthSchedules)): ?>
      <div class="health-calendar-grid">
        <?php foreach ($healthSchedules as $schedule): ?>
          <article class="health-calendar-card" data-reveal>
            <div class="health-calendar-date"><strong><?= rw_esc(date('d', strtotime((string) $schedule['tanggal']))) ?></strong><span><?= rw_esc(strtoupper(date('M', strtotime((string) $schedule['tanggal'])))) ?></span></div>
            <div><span class="health-calendar-type"><?= rw_esc(ucfirst($serviceType)) ?></span><h3><?= rw_esc($schedule['judul']) ?></h3><p><?= rw_esc($schedule['lokasi']) ?><?= ! empty($schedule['waktu']) ? ' · ' . rw_esc($schedule['waktu']) : '' ?></p><?php if (! empty($schedule['deskripsi'])): ?><p class="muted"><?= nl2br(rw_esc($schedule['deskripsi'])) ?></p><?php endif; ?><?php if (! empty($schedule['penanggung_jawab'])): ?><small>Penanggung jawab: <?= rw_esc($schedule['penanggung_jawab']) ?></small><?php endif; ?></div>
          </article>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <div class="health-empty-schedule" data-reveal><strong>Jadwal belum diumumkan.</strong><p>Hubungi pengurus atau kader RW untuk mendapatkan informasi jadwal terbaru.</p></div>
    <?php endif; ?>
  </div>
</section>
<?= $this->endSection() ?>
