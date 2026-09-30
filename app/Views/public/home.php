<?= $this->extend('layouts/public') ?>

<?= $this->section('content') ?>
<?php
$heroSummary = 'Pusat informasi, pelayanan administrasi, transparansi, kegiatan, kesehatan, dan aspirasi warga RW 05.';
$latestAgenda = $kegiatan[0] ?? null;
$healthSchedules = is_array($healthSchedules ?? null) ? $healthSchedules : ['posyandu' => null, 'posbindu' => null];
$financeOverview = is_array($financeOverview ?? null) ? $financeOverview : ['available' => false];
$quickItems = [
    [
        'code' => '01',
        'title' => 'Ajukan Surat',
        'text' => 'Isi pengajuan dari HP dan pantau sampai surat selesai.',
        'href' => site_url('layanan-online') . '#ajukan-surat',
        'class' => 'shortcut-card-priority',
    ],
    [
        'code' => '02',
        'title' => 'Cek Status',
        'text' => 'Gunakan kode pengajuan dan 4 angka terakhir WhatsApp.',
        'href' => site_url('layanan-online') . '#cek-status',
        'class' => '',
    ],
    [
        'code' => '03',
        'title' => 'Transparansi',
        'text' => 'Lihat ringkasan pemasukan, pengeluaran, dan saldo.',
        'href' => site_url('keuangan'),
        'class' => '',
    ],
    [
        'code' => '04',
        'title' => 'Info & Kegiatan',
        'text' => 'Baca agenda, pengumuman, dan informasi lingkungan.',
        'href' => site_url('kegiatan'),
        'class' => '',
    ],
    [
        'code' => '05',
        'title' => 'Kesehatan',
        'text' => 'Temukan program, jadwal, poster, artikel, dan video edukasi.',
        'href' => site_url('kesehatan'),
        'class' => '',
    ],
    [
        'code' => '06',
        'title' => 'Kirim Aspirasi',
        'text' => 'Sampaikan saran atau laporan agar tercatat oleh pengurus.',
        'href' => site_url('aspirasi'),
        'class' => '',
    ],
];
?>

<section class="hero home-hero smart-home-hero compact-home-hero">
  <div class="container hero-layout">
    <div class="hero-copy smart-hero-copy" data-reveal>
      <p class="eyebrow">Portal Warga RW 05</p>
      <h1><?= rw_esc($siteName) ?></h1>
      <p class="hero-region"><?= rw_esc($siteSubtitle) ?></p>
      <p class="hero-text"><?= rw_esc($heroSummary) ?></p>
      <div class="hero-actions" aria-label="Aksi cepat">
        <a href="<?= site_url('layanan-online') ?>#ajukan-surat" class="btn primary">Ajukan Surat</a>
        <a href="<?= site_url('layanan-online') ?>#cek-status" class="btn secondary">Cek Status</a>
        <?php if ($waLink): ?>
          <a href="<?= rw_esc($waLink) ?>" class="btn tertiary" target="_blank" rel="noopener noreferrer">WhatsApp RW</a>
        <?php endif; ?>
      </div>
      <div class="hero-trust-list" aria-label="Nilai layanan RW">
        <span><?= rw_esc($identity['tagline'] ?? 'Transparan · Tertib · Melayani') ?></span>
      </div>
    </div>

    <aside class="hero-citizen-panel compact-citizen-panel" data-reveal aria-label="Informasi terbaru warga">
      <div class="citizen-panel-head">
        <p class="eyebrow">Informasi terbaru</p>
        <h2>Ketahui kabar lingkungan tanpa mencari ke banyak tempat.</h2>
      </div>
      <div class="citizen-info-card home-highlight-card">
        <div>
          <span><?= $latestAgenda ? 'Info warga' : 'Portal RW' ?></span>
          <strong><?= $latestAgenda ? rw_esc(format_date_id($latestAgenda['tanggal'])) : 'Siap digunakan' ?></strong>
        </div>
        <h2><?= $latestAgenda ? rw_esc($latestAgenda['judul']) : 'Belum ada informasi baru.' ?></h2>
        <p><?= $latestAgenda ? rw_esc($latestAgenda['isi']) : 'Pengumuman dan agenda resmi RW akan tampil otomatis di bagian ini.' ?></p>
        <?php if ($latestAgenda): ?>
          <a href="<?= site_url('kegiatan') ?>" class="text-link">Baca informasi</a>
        <?php endif; ?>
      </div>
      <div class="citizen-metric-row" aria-label="Ringkasan portal RW">
        <a href="<?= site_url('layanan') ?>">
          <strong><?= rw_esc((string) count($layanan)) ?></strong>
          <span>Bidang layanan</span>
        </a>
        <a href="<?= site_url('profil') ?>">
          <strong><?= rw_esc((string) $totalKK) ?></strong>
          <span>KK</span>
        </a>
        <a href="<?= site_url('kegiatan') ?>">
          <strong><?= rw_esc((string) count($kegiatan)) ?></strong>
          <span>Info aktif</span>
        </a>
      </div>
    </aside>
  </div>
</section>

<section class="quick-access home-service-hub citizen-service-section" aria-labelledby="quick-title">
  <div class="container">
    <div class="service-section-head" data-reveal>
      <p class="eyebrow">Butuh apa hari ini?</p>
      <div>
        <h2 id="quick-title">Pilih kebutuhan, lalu langsung kerjakan.</h2>
        <p>Enam menu utama untuk warga dan pengunjung umum. Menu lainnya tersedia di navigasi atas.</p>
      </div>
    </div>
    <div class="shortcut-grid service-hub-grid citizen-shortcut-grid">
      <?php foreach ($quickItems as $item): ?>
        <a href="<?= rw_esc($item['href']) ?>" class="shortcut-card <?= rw_esc($item['class']) ?>" data-reveal>
          <span><?= rw_esc($item['code']) ?></span>
          <strong><?= rw_esc($item['title']) ?></strong>
          <small><?= rw_esc($item['text']) ?></small>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="section home-overview-section" aria-label="Jadwal kesehatan dan transparansi keuangan">
  <div class="container home-overview-grid">
    <article class="home-overview-card" data-reveal>
      <div class="home-overview-head">
        <div><p class="eyebrow">Jadwal kesehatan</p><h2>Posyandu dan Posbindu terdekat.</h2></div>
        <a href="<?= site_url('kesehatan') ?>" class="text-link">Lihat semua jadwal</a>
      </div>
      <div class="home-schedule-list">
        <?php foreach (['posyandu' => 'Posyandu', 'posbindu' => 'Posbindu'] as $type => $label): ?>
          <?php $schedule = $healthSchedules[$type] ?? null; ?>
          <article>
            <span><?= rw_esc($label) ?></span>
            <?php if ($schedule): ?>
              <strong><?= rw_esc(format_date_id($schedule['tanggal'] ?? '')) ?></strong>
              <small><?= rw_esc(trim((string) ($schedule['waktu'] ?? '')) ?: 'Waktu mengikuti informasi kader') ?> · <?= rw_esc(trim((string) ($schedule['lokasi'] ?? '')) ?: 'Lokasi akan diumumkan') ?></small>
            <?php else: ?>
              <strong>Belum ada jadwal aktif</strong>
              <small>Jadwal akan tampil setelah diterbitkan oleh pengurus.</small>
            <?php endif; ?>
          </article>
        <?php endforeach; ?>
      </div>
    </article>

    <article class="home-overview-card finance-home-card" data-reveal>
      <div class="home-overview-head">
        <div><p class="eyebrow">Transparansi</p><h2>Ringkasan keuangan terbaru.</h2></div>
        <a href="<?= site_url('keuangan') ?>" class="text-link">Buka laporan</a>
      </div>
      <?php if (! empty($financeOverview['available'])): ?>
        <p class="home-overview-period"><?= rw_esc($financeOverview['periodLabel'] ?? '') ?></p>
        <div class="home-finance-summary">
          <div><span>Pemasukan</span><strong><?= rw_esc(fmt_currency($financeOverview['income'] ?? 0)) ?></strong></div>
          <div><span>Pengeluaran</span><strong><?= rw_esc(fmt_currency($financeOverview['expense'] ?? 0)) ?></strong></div>
          <div class="is-balance"><span>Saldo periode</span><strong><?= rw_esc(fmt_currency($financeOverview['balance'] ?? 0)) ?></strong></div>
        </div>
      <?php else: ?>
        <p class="empty-state">Ringkasan keuangan belum tersedia. Data akan tampil setelah transaksi diterbitkan pengurus.</p>
      <?php endif; ?>
    </article>
  </div>
</section>

<section class="section white-section smart-live-section compact-live-section">
  <div class="container split-stack home-live-grid">
    <div class="stack-column live-column smart-news-column" data-reveal>
      <div class="section-title left">
        <p class="eyebrow">Info warga</p>
        <h2>Pengumuman dan kegiatan terbaru.</h2>
      </div>
      <?php if ($kegiatan): ?>
        <div class="news-list">
          <?php foreach (array_slice($kegiatan, 0, 3) as $item): ?>
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
      <a href="<?= site_url('kegiatan') ?>" class="text-link">Lihat semua kegiatan</a>
    </div>

    <div class="stack-column live-column smart-people-column" data-reveal>
      <div class="section-title left">
        <p class="eyebrow">Pengurus RW</p>
        <h2>Kenali pengurus yang melayani warga.</h2>
      </div>
      <?php if ($pengurus): ?>
        <div class="mini-people-list">
          <?php foreach (array_slice($pengurus, 0, 4) as $row): ?>
            <article>
              <strong><?= rw_esc($row['jabatan']) ?></strong>
              <span><?= rw_esc($row['nama']) ?></span>
            </article>
          <?php endforeach; ?>
        </div>
        <a href="<?= site_url('pengurus') ?>" class="text-link">Lihat semua pengurus</a>
      <?php else: ?>
        <p class="empty-state">Data pengurus aktif belum tersedia.</p>
      <?php endif; ?>
    </div>
  </div>
</section>
<?= $this->endSection() ?>
