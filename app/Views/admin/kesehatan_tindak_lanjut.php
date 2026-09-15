<?= $this->extend('layouts/kesehatan_admin') ?>

<?= $this->section('content') ?>
<div class="section-heading">
  <div>
    <h1>Tindak Lanjut & Rujukan</h1>
    <p class="muted">Daftar kunjungan yang masih perlu dipantau, dikunjungi rumah, atau dirujuk ke Puskesmas.</p>
  </div>
  <div class="form-actions">
    <a href="<?= site_url('admin/kesehatan-dashboard') ?>">Dashboard Kesehatan</a>
    <a href="<?= site_url('admin/kesehatan-data') ?>">Layanan Kesehatan</a>
  </div>
</div>

<?php if ($success !== ''): ?><div class="alert success"><?= rw_esc($success) ?></div><?php endif; ?>
<?php if ($error !== ''): ?><div class="alert error"><?= rw_esc($error) ?></div><?php endif; ?>

<?php if (! ($tableReady ?? false)): ?>
  <div class="alert warning">Penyimpanan data kesehatan belum siap. Coba muat ulang atau hubungi pengelola hosting.</div>
<?php else: ?>
  <?php if (! ($canValidate ?? true)): ?>
    <div class="alert warning">Akun Anda berperan sebagai kader. Validasi kunjungan hanya dapat dilakukan oleh akun nakes/admin.</div>
  <?php endif; ?>

  <section class="panel">
    <form method="get" action="<?= site_url('admin/kesehatan-tindak-lanjut') ?>" class="grid-form">
      <label>Layanan
        <select name="jenis">
          <option value="">Semua</option>
          <?php foreach ($participantTypeOptions as $value => $label): ?><option value="<?= rw_esc($value) ?>" <?= is_selected($filterJenis, $value) ?>><?= rw_esc($label) ?></option><?php endforeach; ?>
        </select>
      </label>
      <label>Jenis Tindak Lanjut
        <select name="tindak_lanjut">
          <option value="">Semua</option>
          <?php foreach ($followupOptions as $value => $label): ?>
            <?php if ($value === 'selesai') { continue; } ?>
            <option value="<?= rw_esc($value) ?>" <?= is_selected($filterFollowup, $value) ?>><?= rw_esc($label) ?></option>
          <?php endforeach; ?>
        </select>
      </label>
      <div class="form-actions"><button type="submit">Filter</button><a class="btn-light" href="<?= site_url('admin/kesehatan-tindak-lanjut') ?>">Reset</a></div>
    </form>
  </section>

  <section class="panel">
    <div class="table-scroll">
      <table>
        <thead><tr><th>Jadwal</th><th>Peserta</th><th>Layanan</th><th>Tindak Lanjut</th><th>Tujuan Rujukan</th><th>Validasi</th><th>Aksi</th></tr></thead>
        <tbody>
          <?php foreach ($rows as $row): ?>
            <tr>
              <td><?= ! empty($row['tanggal_tindak_lanjut']) ? rw_esc(format_date_id($row['tanggal_tindak_lanjut'])) : '-' ?><br><small>Kunjungan <?= rw_esc(date('d/m/Y', strtotime((string) $row['tanggal']))) ?></small></td>
              <td><strong><?= rw_esc($row['nama']) ?></strong><br><small>RT <?= rw_esc($row['rt'] ?? '-') ?><?= ! empty($row['no_hp']) ? ' · ' . rw_esc($row['no_hp']) : '' ?></small></td>
              <td><?= rw_esc($participantTypeOptions[$row['jenis_layanan'] ?? ''] ?? ($row['jenis_layanan'] ?? '-')) ?></td>
              <td><?= rw_esc($followupOptions[$row['tindak_lanjut']] ?? $row['tindak_lanjut']) ?></td>
              <td><?= rw_esc($row['tujuan_rujukan'] ?? '-') ?></td>
              <td><?= ($row['status_validasi'] ?? 'dicatat') === 'divalidasi' ? 'Divalidasi' : 'Dicatat kader' ?></td>
              <td>
                <div class="table-actions">
                  <form method="post" action="<?= site_url('admin/kesehatan-tindak-lanjut') ?>" onsubmit="return confirm('Tandai tindak lanjut ini selesai?')">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="mark_selesai">
                    <input type="hidden" name="id" value="<?= (int) $row['id'] ?>">
                    <button type="submit">Tandai Selesai</button>
                  </form>
                  <?php if (($canValidate ?? true) && ($row['status_validasi'] ?? 'dicatat') !== 'divalidasi'): ?>
                    <form method="post" action="<?= site_url('admin/kesehatan-tindak-lanjut') ?>">
                      <?= csrf_field() ?>
                      <input type="hidden" name="action" value="mark_validasi">
                      <input type="hidden" name="id" value="<?= (int) $row['id'] ?>">
                      <button type="submit">Validasi</button>
                    </form>
                  <?php endif; ?>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
          <?php if (empty($rows)): ?><tr><td colspan="7" class="table-empty">Tidak ada tindak lanjut atau rujukan yang menunggu.</td></tr><?php endif; ?>
        </tbody>
      </table>
    </div>
  </section>
<?php endif; ?>
<?= $this->endSection() ?>
