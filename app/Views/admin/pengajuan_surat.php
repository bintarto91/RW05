<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<h1>Pengajuan Surat Online</h1>
<p class="muted">Kelola Surat Pengantar, Surat Keterangan, Undangan, Edaran, Permohonan, Tugas/Mandat, Berita Acara, dan Keputusan RW dari warga.</p>

<?php if (! empty($success)): ?>
  <div class="alert success"><?= rw_esc($success) ?></div>
<?php endif; ?>
<?php if (! empty($error)): ?>
  <div class="alert error"><?= rw_esc($error) ?></div>
<?php endif; ?>

<section class="panel compact-panel">
  <div class="section-heading compact-heading">
    <div>
      <h2>Ringkasan Status</h2>
      <p class="muted">Prioritaskan pengajuan dengan status menunggu dan diproses.</p>
    </div>
    <a href="<?= site_url('layanan-online') ?>" target="_blank" rel="noreferrer">Buka Form Warga</a>
  </div>
  <div class="letter-status-grid">
    <?php foreach ($statusOptions as $status => $label): ?>
      <div class="letter-status-card">
        <span class="badge status-<?= rw_esc($status) ?>"><?= rw_esc($label) ?></span>
        <strong><?= rw_esc((string) ($statusCounts[$status] ?? 0)) ?></strong>
      </div>
    <?php endforeach; ?>
  </div>
</section>

<section class="panel">
  <h2>Daftar Pengajuan</h2>
  <table>
    <thead>
      <tr>
        <th>Tanggal</th>
        <th>Kode</th>
        <th>Pemohon</th>
        <th>Jenis & Keperluan</th>
        <th>Detail</th>
        <th>Status Admin</th>
        <th>Aksi</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($rows as $row): ?>
        <?php $structuredEntries = surat_request_data_entries($row['jenis_surat'] ?? '', $row['detail_json'] ?? ''); ?>
        <tr>
          <td><?= rw_esc(date('d/m/Y H:i', strtotime($row['created_at']))) ?></td>
          <td><strong><?= rw_esc($row['kode_pengajuan']) ?></strong></td>
          <td>
            <?= rw_esc($row['nama']) ?><br>
            <small>RT <?= rw_esc($row['rt']) ?> | <?= rw_esc($row['no_hp']) ?></small><br>
            <small><?= rw_esc($row['alamat']) ?></small>
          </td>
          <td>
            <strong><?= rw_esc($row['jenis_surat']) ?></strong><br>
            <small><?= rw_esc($row['keperluan']) ?></small>
          </td>
          <td>
            <?php if (! empty($row['detail'])): ?>
              <?= nl2br(rw_esc($row['detail'])) ?>
            <?php endif; ?>
            <?php foreach ($structuredEntries as $entry): ?>
              <?php if (! empty($row['detail'])): ?><br><?php endif; ?>
              <small><?= rw_esc($entry['label']) ?>: <?= nl2br(rw_esc($entry['value'])) ?></small>
            <?php endforeach; ?>
            <?php if (empty($row['detail']) && empty($structuredEntries)): ?>-<?php endif; ?>
            <?php if (! empty($row['lampiran_catatan'])): ?>
              <br><small>Lampiran: <?= rw_esc($row['lampiran_catatan']) ?></small>
            <?php endif; ?>
          </td>
          <td>
            <form method="post" action="<?= site_url('admin/pengajuan-surat') ?>" class="inline-form letter-update-form">
              <input type="hidden" name="id" value="<?= rw_esc($row['id']) ?>">
              <select name="status">
                <?php foreach ($statusOptions as $status => $label): ?>
                  <option value="<?= rw_esc($status) ?>" <?= is_selected($row['status'], $status) ?>><?= rw_esc($label) ?></option>
                <?php endforeach; ?>
              </select>
              <input type="text" name="nomor_surat" value="<?= rw_esc($row['nomor_surat'] ?? '') ?>" placeholder="Nomor surat bila ada">
              <textarea name="catatan_admin" rows="3" placeholder="Catatan admin"><?= rw_esc($row['catatan_admin'] ?? '') ?></textarea>
              <button type="submit">Update</button>
            </form>
          </td>
          <td>
            <span class="badge status-<?= rw_esc($row['status']) ?>"><?= rw_esc(surat_status_label($row['status'])) ?></span><br>
            <?php if (in_array($row['status'], ['disetujui', 'selesai'], true)): ?>
              <a href="<?= site_url('layanan-online/surat/' . rawurlencode($row['kode_pengajuan'])) ?>" target="_blank" rel="noopener noreferrer">Download PDF</a><br>
            <?php endif; ?>
            <a href="<?= site_url('admin/pengajuan-surat?action=delete&id=' . $row['id']) ?>" onclick="return confirm('Hapus pengajuan surat ini?')">Hapus</a>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (empty($rows)): ?>
        <tr><td colspan="7" class="table-empty">Belum ada pengajuan surat online.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</section>
<?= $this->endSection() ?>
