<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<section class="dashboard-hero">
  <div>
    <span class="dashboard-eyebrow">Hari ini, <?= rw_esc(fmt_date(date('Y-m-d'))) ?></span>
    <h1>Dashboard RW 05</h1>
    <p class="muted">Selamat datang, <?= rw_esc(session('admin_nama') ?? 'Admin') ?>. Pantau data warga, kegiatan, layanan, aspirasi, dan ringkasan keuangan dari satu halaman.</p>
  </div>
  <div class="hero-summary hero-notification-summary" aria-label="Ringkasan notifikasi baru">
    <a href="<?= site_url('admin/pengajuan-surat') ?>">
      <span>Surat Menunggu</span>
      <strong><?= rw_esc($suratMenunggu ?? 0) ?></strong>
      <small><?= rw_esc($suratTotal ?? 0) ?> total pengajuan</small>
    </a>
    <a href="<?= site_url('admin/aspirasi') ?>">
      <span>Aspirasi Baru</span>
      <strong><?= rw_esc($aspirasiBaru) ?></strong>
      <small><?= rw_esc($aspirasiTotal) ?> total aspirasi</small>
    </a>
  </div>
</section>

<div class="stat-grid">
  <?php foreach ($stats as $item): ?>
    <a class="stat dashboard-stat <?= rw_esc('stat-' . $item['tone']) ?>" href="<?= rw_esc($item['href']) ?>">
      <span><?= rw_esc($item['label']) ?></span>
      <strong><?= rw_esc($item['value']) ?></strong>
      <small><?= rw_esc($item['meta']) ?></small>
    </a>
  <?php endforeach; ?>
</div>

<div class="dashboard-layout">
  <section class="dashboard-card work-card">
    <div class="section-heading">
      <div>
        <p class="admin-kicker">Prioritas</p>
        <h2>Perlu Ditangani</h2>
      </div>
      <a href="<?= site_url('admin/aspirasi') ?>">Lihat Semua</a>
    </div>

    <div class="work-list">
      <?php foreach ($workItems as $item): ?>
        <a href="<?= rw_esc($item['href']) ?>" class="work-item">
          <span></span>
          <strong><?= rw_esc($item['label']) ?></strong>
          <small><?= rw_esc($item['detail']) ?></small>
        </a>
      <?php endforeach; ?>
    </div>
  </section>

  <section class="dashboard-card">
    <div class="section-heading">
      <div>
        <p class="admin-kicker">Aksi Cepat</p>
        <h2>Kelola Data</h2>
      </div>
    </div>

    <div class="quick-actions">
      <a href="<?= site_url('admin/kegiatan') ?>"><strong>Tambah Kegiatan</strong><span>Agenda dan pengumuman</span></a>
      <a href="<?= site_url('admin/pengajuan-surat') ?>"><strong>Pengajuan Surat</strong><span>Verifikasi surat online</span></a>
      <a href="<?= site_url('admin/warga') ?>"><strong>Input Warga</strong><span>Data keluarga ringkas</span></a>
      <a href="<?= site_url('admin/keuangan') ?>"><strong>Keuangan</strong><span>Kas RT dan pemasukan RW</span></a>
      <a href="<?= site_url('admin/aspirasi') ?>"><strong>Kelola Aspirasi</strong><span>Respon laporan warga</span></a>
      <a href="<?= site_url('admin/import') ?>"><strong>Import Massal</strong><span>Upload data dari template CSV</span></a>
    </div>
  </section>
</div>

<section class="dashboard-card table-card">
  <div class="section-heading">
    <div>
      <p class="admin-kicker">Keuangan</p>
      <h2>Ringkasan Saldo RW</h2>
    </div>
    <a href="<?= site_url('admin/keuangan') ?>">Buka Keuangan</a>
  </div>
  <div class="helper-grid">
    <?php $financeChartMax = max(1, (float) ($financeSummary['income'] ?? 0), (float) ($financeSummary['expense'] ?? 0)); ?>
    <article class="helper-card helper-card-accent">
      <strong>Saldo RW <?= rw_esc($financeSummary['label']) ?></strong>
      <p><?= rw_esc(fmt_currency($financeSummary['balance'])) ?></p>
      <p class="muted">Pemasukan <?= rw_esc(fmt_currency($financeSummary['income'])) ?> dan pengeluaran <?= rw_esc(fmt_currency($financeSummary['expense'])) ?> pada bulan berjalan.</p>
      <div class="admin-mini-chart">
        <div>
          <span>Masuk</span>
          <i style="width: <?= rw_esc((string) max(3, round(((float) ($financeSummary['income'] ?? 0) / $financeChartMax) * 100))) ?>%"></i>
        </div>
        <div>
          <span>Keluar</span>
          <i class="is-expense" style="width: <?= rw_esc((string) max(3, round(((float) ($financeSummary['expense'] ?? 0) / $financeChartMax) * 100))) ?>%"></i>
        </div>
      </div>
    </article>
    <article class="helper-note helper-note-tight">
      <strong>Akses Cepat Keuangan</strong>
      <p class="muted">Masuk ke halaman keuangan untuk filter per RT, input transaksi, dan export PDF laporan bulanan.</p>
    </article>
  </div>
</section>

<div class="dashboard-layout dashboard-layout-wide">
  <section class="dashboard-card">
    <div class="section-heading">
      <div>
        <p class="admin-kicker">Status</p>
        <h2>Aspirasi Warga</h2>
      </div>
      <a href="<?= site_url('admin/aspirasi') ?>">Kelola</a>
    </div>

    <div class="status-stack">
      <?php foreach ($aspirasiStatus as $status => $total): ?>
        <?php $percent = $aspirasiTotal > 0 ? round(($total / $aspirasiTotal) * 100) : 0; ?>
        <div class="status-row">
          <div>
            <span class="badge status-<?= rw_esc($status) ?>"><?= rw_esc(ucfirst($status)) ?></span>
            <strong><?= rw_esc($total) ?></strong>
          </div>
          <div class="progress-track" aria-label="<?= rw_esc($status) ?> <?= rw_esc($percent) ?> persen">
            <span style="width: <?= rw_esc($percent) ?>%"></span>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </section>

  <section class="dashboard-card">
    <div class="section-heading">
      <div>
        <p class="admin-kicker">Sebaran</p>
        <h2>Warga per RT</h2>
      </div>
      <a href="<?= site_url('admin/warga') ?>">Data Warga</a>
    </div>

    <?php if (! empty($wargaByRt)): ?>
      <div class="rt-list">
        <?php foreach ($wargaByRt as $row): ?>
          <div class="rt-row">
            <strong>RT <?= rw_esc($row['rt']) ?></strong>
            <span><?= rw_esc($row['total_kk']) ?> KK</span>
            <small><?= rw_esc($row['total_warga']) ?> jiwa</small>
          </div>
        <?php endforeach; ?>
      </div>

      <div class="rt-list" style="margin-top: 12px;">
        <div class="rt-row">
          <strong>Kurang Mampu</strong>
          <span><?= rw_esc((string) ($wargaSocialSummary['kurangMampu'] ?? 0)) ?> KK</span>
          <small>keluarga perlu pemantauan</small>
        </div>
        <div class="rt-row">
          <strong>Penerima Bantuan</strong>
          <span><?= rw_esc((string) ($wargaSocialSummary['penerimaBantuan'] ?? 0)) ?> KK</span>
          <small>tercatat menerima bantuan</small>
        </div>
        <?php if (! empty($wargaSocialSummary['topRt'])): ?>
          <div class="rt-row">
            <strong>RT Perlu Fokus</strong>
            <span>RT <?= rw_esc($wargaSocialSummary['topRt']) ?></span>
            <small><?= rw_esc((string) ($wargaSocialSummary['topRtNeedCount'] ?? 0)) ?> catatan bantuan/kesejahteraan</small>
          </div>
        <?php endif; ?>
      </div>
    <?php else: ?>
      <p class="empty-state">Belum ada data warga.</p>
    <?php endif; ?>
  </section>
</div>

<section class="dashboard-card table-card">
  <div class="section-heading">
    <div>
      <p class="admin-kicker">Terbaru</p>
      <h2>Aspirasi Masuk</h2>
    </div>
    <a href="<?= site_url('admin/aspirasi') ?>">Kelola Aspirasi</a>
  </div>
  <table>
    <thead><tr><th>Tanggal</th><th>Nama</th><th>RT</th><th>Kategori</th><th>Status</th></tr></thead>
    <tbody>
      <?php foreach ($latestAspirasi as $row): ?>
        <tr>
          <td><?= rw_esc(date('d/m/Y H:i', strtotime($row['created_at']))) ?></td>
          <td><?= rw_esc($row['nama']) ?></td>
          <td><?= rw_esc($row['rt']) ?></td>
          <td><?= rw_esc($row['kategori']) ?></td>
          <td><span class="badge status-<?= rw_esc($row['status']) ?>"><?= rw_esc($row['status']) ?></span></td>
        </tr>
      <?php endforeach; ?>
      <?php if (empty($latestAspirasi)): ?>
        <tr><td colspan="5" class="table-empty">Belum ada aspirasi masuk.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</section>

<section class="dashboard-card table-card">
  <div class="section-heading">
    <div>
      <p class="admin-kicker">Agenda</p>
      <h2>Kegiatan Terbaru</h2>
    </div>
    <a href="<?= site_url('admin/kegiatan') ?>">Kelola Kegiatan</a>
  </div>
  <table>
    <thead><tr><th>Tanggal</th><th>Judul</th><th>Kategori</th><th>Status</th></tr></thead>
    <tbody>
      <?php foreach ($latestKegiatan as $row): ?>
        <tr>
          <td><?= rw_esc(fmt_date($row['tanggal'])) ?></td>
          <td><?= rw_esc($row['judul']) ?></td>
          <td><?= rw_esc($row['kategori']) ?></td>
          <td><span class="badge status-<?= rw_esc($row['status']) ?>"><?= rw_esc($row['status']) ?></span></td>
        </tr>
      <?php endforeach; ?>
      <?php if (empty($latestKegiatan)): ?>
        <tr><td colspan="4" class="table-empty">Belum ada kegiatan tercatat.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</section>
<?= $this->endSection() ?>
