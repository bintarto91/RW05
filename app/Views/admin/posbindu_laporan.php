<?= $this->extend('layouts/kesehatan_admin') ?>
<?= $this->section('content') ?>

<section class="panel health-workflow-hero">
  <div class="section-heading">
    <div>
      <p class="admin-kicker">Alur khusus Posbindu</p>
      <h1>Laporan Posbindu</h1>
      <p class="muted">Pilih tanggal, lengkapi hasil peserta, lalu unduh satu laporan Excel dengan susunan kolom yang sama seperti format Puskesmas.</p>
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
    <article><strong><?= (int) $presentCount ?></strong><span>Hadir</span></article>
    <article><strong><?= (int) $completeCount ?></strong><span>Siap diekspor</span></article>
    <article class="is-warning"><strong><?= (int) ($attentionCount ?? 0) ?></strong><span>Perlu perhatian</span></article>
    <article class="is-danger"><strong><?= (int) ($referralCount ?? 0) ?></strong><span>Rujukan</span></article>
  </div>
</section>

<section class="panel">
  <div class="section-heading">
    <div><h2>Daftar pemeriksaan <?= rw_esc(date('d-m-Y', strtotime($reportDate))) ?></h2><p class="muted">Kolom kosong di Excel tetap dibiarkan kosong. Isi hanya hasil pemeriksaan yang benar-benar dilakukan.</p></div>
  </div>
  <div class="table-wrap health-mobile-table">
    <table>
      <thead><tr><th>Peserta</th><th>NIK</th><th>RT</th><th>Ringkasan hasil</th><th>Penanda skrining</th><th>Status laporan</th><th>Aksi</th></tr></thead>
      <tbody>
        <?php foreach ($rows as $row): ?>
          <tr>
            <td data-label="Peserta"><strong><?= rw_esc($row['nama']) ?></strong></td>
            <td data-label="NIK"><?= rw_esc($row['nik'] ?: '—') ?></td>
            <td data-label="RT"><?= rw_esc($row['rt'] ?: '—') ?></td>
            <td data-label="Ringkasan hasil">
              <?php if ($row['kunjungan_id']): ?>
                <?= ($row['tekanan_sistolik'] !== null || $row['tekanan_diastolik'] !== null) ? 'TD ' . rw_esc(($row['tekanan_sistolik'] ?? '—') . '/' . ($row['tekanan_diastolik'] ?? '—')) . '<br>' : '' ?>
                <?= ($row['berat_kg'] !== null || $row['tinggi_cm'] !== null) ? '<small>BB/TB ' . rw_esc(($row['berat_kg'] ?? '—') . ' kg / ' . ($row['tinggi_cm'] ?? '—') . ' cm') . '</small>' : '<small>Hasil belum diisi</small>' ?>
              <?php else: ?>—<?php endif; ?>
            </td>
            <?php $screening = $row['screening_status'] ?? ['key' => 'belum_dicatat', 'label' => 'Belum dicatat', 'reasons' => []]; ?>
            <td data-label="Penanda"><span class="health-screening-status is-<?= rw_esc($screening['key']) ?>" title="<?= rw_esc(implode(' · ', $screening['reasons'])) ?>"><?= rw_esc($screening['label']) ?></span><?php if ($screening['reasons']): ?><small class="health-status-reason"><?= rw_esc(implode(' · ', array_slice($screening['reasons'], 0, 2))) ?></small><?php endif; ?></td>
            <td data-label="Status laporan"><span class="report-status <?= $row['report_status'] === 'Siap diekspor' ? 'is-ready' : ($row['report_status'] !== 'Belum dicatat' ? 'is-warning' : '') ?>"><?= rw_esc($row['report_status']) ?></span></td>
            <td data-label="Aksi"><a class="health-row-action" href="<?= site_url('admin/kesehatan-data?jenis=posbindu&peserta_id=' . (int) $row['id'] . '&jenis_kegiatan=posbindu&tab=pemeriksaan&tanggal_kegiatan=' . rawurlencode($reportDate)) ?>"><?= $row['kunjungan_id'] ? 'Edit hasil' : 'Isi pemeriksaan' ?></a></td>
          </tr>
        <?php endforeach; ?>
        <?php if (empty($rows)): ?><tr><td colspan="7" class="table-empty">Belum ada peserta dewasa atau lansia.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</section>

<?= $this->endSection() ?>
