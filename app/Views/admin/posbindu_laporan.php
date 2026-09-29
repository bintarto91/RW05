<?= $this->extend('layouts/kesehatan_admin') ?>
<?= $this->section('content') ?>

<section class="panel health-workflow-hero">
  <div class="section-heading">
    <div>
      <p class="admin-kicker">Alur khusus Posbindu</p>
      <h1>Laporan Posbindu</h1>
      <p class="muted">Satu halaman untuk memilih tanggal, melengkapi pemeriksaan, lalu mengunduh laporan Puskesmas dengan format Excel asli.</p>
    </div>
    <a class="btn-light" href="<?= site_url('admin/kesehatan-data?jenis=posbindu&jenis_kegiatan=posbindu') ?>">Daftar peserta & isi pemeriksaan</a>
  </div>
  <?php if ($error !== ''): ?><div class="alert error"><?= rw_esc($error) ?></div><?php endif; ?>
  <?php if ($success !== ''): ?><div class="alert success"><?= rw_esc($success) ?></div><?php endif; ?>
  <div class="health-workflow-steps">
    <div><strong>1</strong><span>Pilih tanggal kegiatan</span></div>
    <div><strong>2</strong><span>Isi atau lengkapi pemeriksaan</span></div>
    <div><strong>3</strong><span>Unduh Excel untuk Puskesmas</span></div>
  </div>
</section>

<section class="panel">
  <form method="get" action="<?= site_url('admin/posbindu-laporan') ?>" class="grid-form health-report-filter">
    <label>Tanggal kegiatan
      <input type="date" name="tanggal" value="<?= rw_esc($reportDate) ?>" required>
    </label>
    <div class="form-actions">
      <button type="submit">Tampilkan</button>
      <a class="btn-light" href="<?= site_url('admin/posbindu-laporan?export=xlsx&tanggal=' . rawurlencode($reportDate)) ?>">Unduh Excel Persis Format Asli</a>
    </div>
  </form>
  <div class="stat-grid health-report-stats">
    <article><strong><?= count($rows) ?></strong><span>Peserta terdaftar</span></article>
    <article><strong><?= (int) $presentCount ?></strong><span>Hadir / sudah dicatat</span></article>
    <article><strong><?= (int) $completeCount ?></strong><span>Data inti lengkap</span></article>
  </div>
</section>

<section class="panel">
  <div class="section-heading">
    <div><h2>Daftar pemeriksaan <?= rw_esc(date('d-m-Y', strtotime($reportDate))) ?></h2><p class="muted">Kolom kosong di Excel tetap dibiarkan kosong. Isi hanya hasil pemeriksaan yang benar-benar dilakukan.</p></div>
  </div>
  <div class="table-wrap">
    <table>
      <thead><tr><th>Peserta</th><th>NIK</th><th>RT</th><th>Hasil inti</th><th>Status</th><th>Aksi</th></tr></thead>
      <tbody>
        <?php foreach ($rows as $row): ?>
          <tr>
            <td><strong><?= rw_esc($row['nama']) ?></strong></td>
            <td><?= rw_esc($row['nik'] ?: '—') ?></td>
            <td><?= rw_esc($row['rt'] ?: '—') ?></td>
            <td><?= $row['kunjungan_id'] ? rw_esc(trim(($row['tekanan_sistolik'] ?? '') . '/' . ($row['tekanan_diastolik'] ?? ''), '/') ?: 'Belum lengkap') : '—' ?></td>
            <td><span class="report-status <?= $row['report_status'] === 'Siap dilaporkan' ? 'is-ready' : ($row['report_status'] === 'Perlu dilengkapi' ? 'is-warning' : '') ?>"><?= rw_esc($row['report_status']) ?></span></td>
            <td><a href="<?= site_url('admin/kesehatan-data?jenis=posbindu&peserta_id=' . (int) $row['id'] . '&jenis_kegiatan=posbindu&tanggal_kegiatan=' . rawurlencode($reportDate)) ?>"><?= $row['kunjungan_id'] ? 'Lengkapi' : 'Isi pemeriksaan' ?></a></td>
          </tr>
        <?php endforeach; ?>
        <?php if (empty($rows)): ?><tr><td colspan="6" class="table-empty">Belum ada peserta dewasa atau lansia.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</section>

<?= $this->endSection() ?>
