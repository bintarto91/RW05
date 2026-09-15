<?= $this->extend('layouts/kesehatan_admin') ?>

<?= $this->section('content') ?>
<section class="health-dashboard-hero">
  <div>
    <span class="health-dashboard-eyebrow">Posyandu ILP & Posbindu PTM</span>
    <h1>Dashboard Pelayanan Kesehatan</h1>
    <p>Pusat kerja kader dan tenaga kesehatan untuk mencatat kunjungan, melihat sasaran, serta memastikan tindak lanjut warga tidak terlewat.</p>
  </div>
  <div class="health-dashboard-date">
    <span>Hari ini</span>
    <strong><?= rw_esc(format_date_id(date('Y-m-d'))) ?></strong>
    <small>RW 05 Desa Citeureup</small>
  </div>
</section>

<nav class="health-quick-actions" aria-label="Aksi cepat kesehatan">
  <a href="<?= site_url('admin/kesehatan-data') ?>"><strong>Catat layanan</strong><span>Peserta, kehadiran, dan hasil kunjungan</span></a>
  <a href="<?= site_url('admin/kesehatan-tindak-lanjut') ?>"><strong>Cek tindak lanjut</strong><span>Pemantauan, kunjungan rumah, dan rujukan</span></a>
  <a href="<?= site_url('admin/kesehatan-jadwal') ?>"><strong>Atur jadwal</strong><span>Publikasikan kegiatan untuk warga</span></a>
</nav>

<?php if (! ($tableReady ?? false)): ?>
  <div class="alert warning">Penyimpanan data kesehatan belum siap. Coba muat ulang atau hubungi pengelola hosting.</div>
<?php else: ?>
  <div class="stat-grid health-stat-grid">
    <article class="stat"><span>Peserta Aktif</span><strong><?= rw_esc((string) ($healthStats['active'] ?? 0)) ?></strong><small>Semua kelompok siklus hidup</small></article>
    <article class="stat"><span>Kunjungan Bulan Ini</span><strong><?= rw_esc((string) ($healthStats['monthVisits'] ?? 0)) ?></strong><small>Peserta yang tercatat hadir</small></article>
    <article class="stat"><span>Perlu Ditindaklanjuti</span><strong><?= rw_esc((string) ($healthStats['followups'] ?? 0)) ?></strong><small>Pantau atau kunjungan rumah</small></article>
    <article class="stat"><span>Rujukan</span><strong><?= rw_esc((string) ($healthStats['referrals'] ?? 0)) ?></strong><small>Perlu konsultasi Puskesmas</small></article>
    <article class="stat health-stat-overdue"><span>Terlambat</span><strong><?= rw_esc((string) ($healthStats['overdue'] ?? 0)) ?></strong><small>Jadwal tindak lanjut sudah lewat</small></article>
  </div>

  <div class="dashboard-layout dashboard-layout-wide">
    <section class="panel">
      <h2>Peserta Aktif per Layanan</h2>
      <div class="table-scroll">
        <table>
          <thead><tr><th>Layanan</th><th>Jumlah Peserta</th></tr></thead>
          <tbody>
            <?php foreach ($participantTypeOptions as $value => $label): ?>
              <tr><td><?= rw_esc($label) ?></td><td><?= rw_esc((string) ($byJenis[$value] ?? 0)) ?></td></tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </section>

    <section class="panel">
      <h2>Peserta Aktif per Kelompok Siklus Hidup</h2>
      <div class="table-scroll">
        <table>
          <thead><tr><th>Kelompok</th><th>Jumlah Peserta</th></tr></thead>
          <tbody>
            <?php foreach ($lifecycleOptions as $value => $label): ?>
              <tr><td><?= rw_esc($label) ?></td><td><?= rw_esc((string) ($byLifecycle[$value] ?? 0)) ?></td></tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </section>
  </div>

  <section class="panel">
    <h2>Jadwal Kegiatan Terdekat</h2>
    <div class="table-scroll">
      <table>
        <thead><tr><th>Tanggal</th><th>Jenis</th><th>Judul</th><th>Lokasi</th><th>Penanggung Jawab</th></tr></thead>
        <tbody>
          <?php foreach ($upcomingSchedules as $schedule): ?>
            <tr>
              <td><?= rw_esc(format_date_id($schedule['tanggal'])) ?></td>
              <td><?= rw_esc($participantTypeOptions[$schedule['jenis']] ?? $schedule['jenis']) ?></td>
              <td><?= rw_esc($schedule['judul']) ?></td>
              <td><?= rw_esc($schedule['lokasi']) ?></td>
              <td><?= rw_esc($schedule['penanggung_jawab'] ?? '-') ?></td>
            </tr>
          <?php endforeach; ?>
          <?php if (empty($upcomingSchedules)): ?><tr><td colspan="5" class="table-empty">Belum ada jadwal aktif yang akan datang.</td></tr><?php endif; ?>
        </tbody>
      </table>
    </div>
  </section>
<?php endif; ?>
<?= $this->endSection() ?>
