<?= $this->extend('layouts/public') ?>

<?= $this->section('content') ?>
<?php
$tagline = trim((string) ($profil['tagline'] ?? '')) ?: 'Transparan | Tertib | Melayani';
$heroSummary = trim((string) ($profil['visi'] ?? '')) ?: 'Pusat layanan warga RW 05 untuk pengajuan surat, cek status, informasi kas, kegiatan, pengurus, dan aspirasi lingkungan.';
$latestAgenda = $kegiatan[0] ?? null;
$heroActions = [
    [
        'label' => 'Paling sering',
        'title' => 'Ajukan Surat',
        'text' => 'Pengantar, keterangan, undangan, edaran, dan surat serbaguna.',
        'href' => site_url('layanan-online'),
    ],
    [
        'label' => 'Pantau',
        'title' => 'Cek Status',
        'text' => 'Cari pengajuan memakai kode, nama pemohon, atau RT.',
        'href' => site_url('layanan-online'),
    ],
    [
        'label' => 'Terbuka',
        'title' => 'Lihat Kas',
        'text' => 'Rekap pemasukan, pengeluaran, dan saldo RW/RT/unit.',
        'href' => site_url('keuangan'),
    ],
    [
        'label' => 'Laporkan',
        'title' => 'Kirim Aspirasi',
        'text' => 'Saran, laporan lingkungan, dan masukan untuk pengurus.',
        'href' => site_url('aspirasi'),
    ],
];
$quickItems = [
    [
        'code' => '01',
        'title' => 'Surat Online',
        'text' => 'Ajukan surat dari HP tanpa harus menunggu bertemu pengurus.',
        'href' => site_url('layanan-online'),
        'class' => 'shortcut-card-priority',
    ],
    [
        'code' => '02',
        'title' => 'Cek Status',
        'text' => 'Lihat proses surat dan unduh PDF setelah disetujui admin.',
        'href' => site_url('layanan-online'),
        'class' => '',
    ],
    [
        'code' => '03',
        'title' => 'Keuangan',
        'text' => 'Pantau kas RW, RT, panitia, dan unit kegiatan secara jelas.',
        'href' => site_url('keuangan'),
        'class' => '',
    ],
    [
        'code' => '04',
        'title' => 'Kegiatan',
        'text' => 'Baca agenda, pengumuman, dan informasi resmi lingkungan.',
        'href' => site_url('kegiatan'),
        'class' => '',
    ],
    [
        'code' => '05',
        'title' => 'Pengurus',
        'text' => 'Lihat struktur, jabatan, dan kontak pengurus RW 05.',
        'href' => site_url('pengurus'),
        'class' => '',
    ],
    [
        'code' => '06',
        'title' => 'Aspirasi',
        'text' => 'Kirim saran atau laporan agar tercatat dan bisa ditindaklanjuti.',
        'href' => site_url('aspirasi'),
        'class' => '',
    ],
];
$featureCards = [
    [
        'label' => 'Administrasi',
        'title' => 'Surat warga lebih tertib dan mudah dilacak.',
        'text' => 'Warga mendapat kode pengajuan setelah mengirim form. Status bisa dicek kembali sampai surat siap diunduh.',
        'href' => site_url('layanan-online'),
        'link' => 'Buka Layanan Surat',
    ],
    [
        'label' => 'Transparansi',
        'title' => 'Kas lingkungan tampil ringkas untuk warga.',
        'text' => 'Pemasukan, pengeluaran, dan saldo dibuat terpisah agar RW, RT, dan unit kegiatan mudah dibaca.',
        'href' => site_url('keuangan'),
        'link' => 'Lihat Keuangan',
    ],
    [
        'label' => 'Kebersamaan',
        'title' => 'Aspirasi warga masuk ke jalur resmi.',
        'text' => 'Masukan warga tidak tercecer karena tersimpan dan bisa dibahas pengurus sesuai prioritas lingkungan.',
        'href' => site_url('aspirasi'),
        'link' => 'Kirim Aspirasi',
    ],
];
$flowSteps = [
    ['step' => '1', 'title' => 'Pilih kebutuhan', 'text' => 'Mulai dari surat, kas, kegiatan, pengurus, atau aspirasi.'],
    ['step' => '2', 'title' => 'Isi seperlunya', 'text' => 'Form dibuat ringkas agar nyaman dipakai dari HP.'],
    ['step' => '3', 'title' => 'Diproses pengurus', 'text' => 'Admin meninjau data yang masuk dan memberi status.'],
    ['step' => '4', 'title' => 'Pantau hasil', 'text' => 'Warga bisa cek status, membaca info, atau mengunduh surat.'],
];
?>
<section class="hero home-hero smart-home-hero">
  <div class="container hero-layout">
    <div class="hero-copy smart-hero-copy" data-reveal>
      <p class="eyebrow">Portal layanan warga RW 05</p>
      <h1><?= rw_esc($siteName) ?></h1>
      <p class="hero-text"><?= rw_esc($heroSummary) ?></p>
      <div class="hero-actions" aria-label="Aksi cepat">
        <a href="<?= site_url('layanan-online') ?>" class="btn primary">Ajukan Surat Online</a>
        <a href="<?= site_url('layanan-online') ?>" class="btn secondary">Cek Status Pengajuan</a>
        <?php if ($waLink): ?>
          <a href="<?= rw_esc($waLink) ?>" class="btn tertiary" target="_blank" rel="noopener noreferrer">WhatsApp RW</a>
        <?php endif; ?>
      </div>
      <div class="hero-trust-list" aria-label="Nilai layanan RW">
        <span>Surat online 24 jam</span>
        <span>Keuangan terbuka</span>
        <span>Info resmi warga</span>
      </div>
    </div>

    <aside class="hero-citizen-panel" data-reveal aria-label="Akses cepat warga">
      <div class="citizen-panel-head">
        <p class="eyebrow">Butuh apa hari ini?</p>
        <h2>Mulai dari pilihan yang paling sering dipakai warga.</h2>
        <p>Menu dibuat sederhana agar lansia, orang tua, pemuda, dan pengurus bisa langsung paham dari HP.</p>
      </div>
      <div class="citizen-action-list">
        <?php foreach ($heroActions as $item): ?>
          <a href="<?= rw_esc($item['href']) ?>">
            <span><?= rw_esc($item['label']) ?></span>
            <strong><?= rw_esc($item['title']) ?></strong>
            <small><?= rw_esc($item['text']) ?></small>
          </a>
        <?php endforeach; ?>
      </div>
      <div class="citizen-info-card">
        <div>
          <span>Info terbaru</span>
          <strong><?= $latestAgenda ? rw_esc(format_date_id($latestAgenda['tanggal'])) : 'Info RW' ?></strong>
        </div>
        <h2><?= $latestAgenda ? rw_esc($latestAgenda['judul']) : 'Portal warga siap digunakan.' ?></h2>
        <p><?= $latestAgenda ? rw_esc($latestAgenda['isi']) : 'Pengumuman dan agenda resmi RW akan tampil otomatis di beranda.' ?></p>
      </div>
      <div class="citizen-metric-row" aria-label="Ringkasan portal RW">
        <a href="<?= site_url('layanan') ?>">
          <strong><?= rw_esc((string) count($layanan)) ?></strong>
          <span>Layanan</span>
        </a>
        <a href="<?= site_url('profil') ?>">
          <strong><?= rw_esc((string) $totalKK) ?></strong>
          <span>KK</span>
        </a>
        <a href="<?= site_url('aspirasi') ?>">
          <strong><?= rw_esc((string) $totalAspirasi) ?></strong>
          <span>Aspirasi</span>
        </a>
      </div>
    </aside>
  </div>
</section>

<section class="quick-access home-service-hub citizen-service-section" aria-labelledby="quick-title">
  <div class="container">
    <div class="service-section-head" data-reveal>
      <p class="eyebrow">Pusat layanan warga</p>
      <div>
        <h2 id="quick-title">Satu halaman untuk kebutuhan RW yang paling sering dicari.</h2>
        <p>Pilih layanan yang dibutuhkan. Semua dibuat singkat, jelas, dan bisa dibuka dari HP.</p>
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

<section class="section home-feature-section smart-focus-section">
  <div class="container">
    <div class="section-title left" data-reveal>
      <p class="eyebrow">Kenapa portal ini dipakai</p>
      <h2>Warga cepat paham, pengurus lebih mudah menindaklanjuti.</h2>
    </div>
    <div class="home-feature-grid smart-feature-grid">
      <?php foreach ($featureCards as $card): ?>
        <article class="feature-card" data-reveal>
          <p class="eyebrow"><?= rw_esc($card['label']) ?></p>
          <h2><?= rw_esc($card['title']) ?></h2>
          <p><?= rw_esc($card['text']) ?></p>
          <a href="<?= rw_esc($card['href']) ?>" class="text-link"><?= rw_esc($card['link']) ?></a>
        </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="section home-flow-section citizen-flow-section">
  <div class="container">
    <div class="flow-panel" data-reveal>
      <div class="section-title left">
        <p class="eyebrow">Alur singkat</p>
        <h2>Dari kebutuhan warga sampai hasilnya jelas.</h2>
        <p>Beranda ini dibuat sebagai pintu masuk, bukan halaman panjang yang bikin bingung.</p>
      </div>
      <div class="flow-steps citizen-flow-steps">
        <?php foreach ($flowSteps as $item): ?>
          <article>
            <span><?= rw_esc($item['step']) ?></span>
            <strong><?= rw_esc($item['title']) ?></strong>
            <p><?= rw_esc($item['text']) ?></p>
          </article>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</section>

<section class="section white-section smart-live-section">
  <div class="container split-stack home-live-grid">
    <div class="stack-column live-column smart-news-column" data-reveal>
      <div class="section-title left">
        <p class="eyebrow">Info warga</p>
        <h2>Yang terbaru dari lingkungan.</h2>
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
      <a href="<?= site_url('kegiatan') ?>" class="text-link">Lihat Semua Kegiatan</a>
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
        <a href="<?= site_url('pengurus') ?>" class="text-link">Lihat Semua Pengurus</a>
      <?php else: ?>
        <p class="empty-state">Data pengurus aktif belum tersedia.</p>
      <?php endif; ?>
    </div>
  </div>
</section>
<?= $this->endSection() ?>
