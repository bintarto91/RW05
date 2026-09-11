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
      <strong><?= rw_esc($service['title']) ?></strong>
      <p><?= rw_esc($service['notice']) ?></p>
    </aside>
  </div>
</section>

<section class="section white-section" aria-labelledby="service-info-title">
  <div class="container health-service-grid">
    <article class="health-schedule-card" data-reveal>
      <p class="eyebrow">Layanan utama</p>
      <h2 id="service-info-title">Apa yang dilakukan saat kegiatan.</h2>
      <ul class="health-step-list">
        <?php foreach ($service['items'] as $index => $item): ?>
          <li><span><?= rw_esc((string) ($index + 1)) ?></span><div><strong><?= rw_esc($item) ?></strong><p>Pelayanan disesuaikan dengan kelompok sasaran serta ketersediaan petugas dan alat.</p></div></li>
        <?php endforeach; ?>
      </ul>
    </article>
    <aside class="health-help-card" data-reveal>
      <p class="eyebrow">Sasaran layanan</p>
      <h2>Siapa yang dapat mengikuti.</h2>
      <ul class="health-service-audience">
        <?php foreach ($service['audiences'] as $audience): ?><li><?= rw_esc($audience) ?></li><?php endforeach; ?>
      </ul>
      <a href="<?= rw_esc($waLink ?: site_url('aspirasi')) ?>"<?= ! empty($waLink) ? ' target="_blank" rel="noopener noreferrer"' : '' ?> class="btn primary">Hubungi Pengurus</a>
    </aside>
  </div>
</section>

<section class="section health-program-section" aria-labelledby="service-flow-title">
  <div class="container health-service-grid">
    <article class="health-schedule-card" data-reveal>
      <p class="eyebrow">Alur pelayanan</p>
      <h2 id="service-flow-title">Lima langkah dari datang sampai tindak lanjut.</h2>
      <ol class="health-step-list">
        <?php foreach ($service['flow'] as $index => $step): ?>
          <li><span><?= rw_esc((string) ($index + 1)) ?></span><div><strong><?= rw_esc($step) ?></strong></div></li>
        <?php endforeach; ?>
      </ol>
    </article>
    <aside class="health-help-card" data-reveal>
      <p class="eyebrow">Sebelum datang</p>
      <h2>Yang sebaiknya disiapkan.</h2>
      <ul class="health-service-audience">
        <?php foreach ($service['prepare'] as $item): ?><li><?= rw_esc($item) ?></li><?php endforeach; ?>
      </ul>
      <div class="health-service-notice"><strong>Privasi</strong><p>Jangan kirim hasil pemeriksaan, foto identitas, atau diagnosis melalui halaman publik. Sampaikan langsung kepada kader atau tenaga kesehatan.</p></div>
    </aside>
  </div>
</section>

<section class="section health-calendar-section" id="jadwal-layanan" aria-labelledby="service-schedule-title">
  <div class="container">
    <div class="section-title" data-reveal>
      <p class="eyebrow">Jadwal layanan</p>
      <h2 id="service-schedule-title">Jadwal <?= rw_esc($service['title']) ?> terdekat.</h2>
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
