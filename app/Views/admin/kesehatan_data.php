<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<?php
$isEditing = ! empty($edit);
$selectedParticipant = $selectedParticipant ?? null;
$selectedJenis = old('jenis', $edit['jenis'] ?? 'posyandu');
$selectedGender = old('jenis_kelamin', $edit['jenis_kelamin'] ?? '');
$selectedStatus = old('status', $edit['status'] ?? 'aktif');
?>
<div class="section-heading">
  <div>
    <h1>Data Kader Posyandu & Posbindu</h1>
    <p class="muted">Data ini privat untuk admin/kader yang login. Jangan masukkan NIK atau diagnosis medis lengkap.</p>
  </div>
  <a href="<?= site_url('kesehatan') ?>" target="_blank" rel="noopener noreferrer">Lihat Halaman Warga</a>
</div>

<?php if ($success !== ''): ?><div class="alert success"><?= rw_esc($success) ?></div><?php endif; ?>
<?php if ($error !== ''): ?><div class="alert error"><?= rw_esc($error) ?></div><?php endif; ?>

<section class="panel">
  <div class="section-heading">
    <div>
      <h2>Kegiatan Hari Ini</h2>
      <p class="muted">Pilih layanan dan tanggal. Peserta aktif akan muncul otomatis untuk dicentang hadir.</p>
    </div>
    <a href="<?= site_url('admin/kesehatan-data?print=1&jenis_kegiatan=' . rawurlencode($activityJenis) . '&tanggal_kegiatan=' . rawurlencode($activityDate)) ?>" target="_blank" rel="noopener noreferrer">Preview / Cetak</a>
  </div>
  <form method="get" action="<?= site_url('admin/kesehatan-data') ?>" class="grid-form">
    <label>Jenis Kegiatan
      <select name="jenis_kegiatan">
        <?php foreach ($participantTypeOptions as $value => $label): ?><option value="<?= rw_esc($value) ?>" <?= is_selected($activityJenis, $value) ?>><?= rw_esc($label) ?></option><?php endforeach; ?>
      </select>
    </label>
    <label>Tanggal Kegiatan
      <input type="date" name="tanggal_kegiatan" value="<?= rw_esc($activityDate) ?>" required>
    </label>
    <div class="form-actions"><button type="submit">Tampilkan Daftar</button></div>
  </form>
  <form method="post" action="<?= site_url('admin/kesehatan-data') ?>">
    <input type="hidden" name="action" value="save_attendance">
    <input type="hidden" name="jenis_kegiatan" value="<?= rw_esc($activityJenis) ?>">
    <input type="hidden" name="tanggal_kegiatan" value="<?= rw_esc($activityDate) ?>">
    <div class="table-scroll">
      <table>
        <thead><tr><th>Hadir</th><th>Peserta</th><th>RT</th><th>Catatan Hari Ini</th></tr></thead>
        <tbody>
          <?php foreach ($attendanceParticipants as $participant): ?>
            <?php $attendance = $attendanceMap[(int) $participant['id']] ?? null; ?>
            <tr>
              <td><input type="checkbox" name="hadir[<?= (int) $participant['id'] ?>]" value="1" <?= ($attendance['hadir'] ?? '') === 'ya' ? 'checked' : '' ?> aria-label="Hadir: <?= rw_esc($participant['nama']) ?>"></td>
              <td><strong><?= rw_esc($participant['nama']) ?></strong><?= ! empty($participant['nama_wali']) ? '<br><small>Wali: ' . rw_esc($participant['nama_wali']) . '</small>' : '' ?></td>
              <td><?= rw_esc($participant['rt'] ?? '-') ?></td>
              <td><?= ! empty($attendance['catatan']) ? rw_esc($attendance['catatan']) : '-' ?></td>
            </tr>
          <?php endforeach; ?>
          <?php if (empty($attendanceParticipants)): ?><tr><td colspan="4" class="table-empty">Belum ada peserta aktif untuk jenis layanan ini.</td></tr><?php endif; ?>
        </tbody>
      </table>
    </div>
    <div class="form-actions"><button type="submit"<?= empty($attendanceParticipants) ? ' disabled' : '' ?>>Simpan Daftar Hadir</button></div>
  </form>
</section>

<section class="panel">
  <div class="section-heading">
    <div>
      <h2><?= $isEditing ? 'Edit Peserta' : 'Daftarkan Peserta' ?></h2>
      <p class="muted">Simpan data minimum yang diperlukan untuk pelaksanaan kegiatan dan tindak lanjut kader.</p>
    </div>
    <?php if ($isEditing): ?><a href="<?= site_url('admin/kesehatan-data') ?>">Batal Edit</a><?php endif; ?>
  </div>
  <?php if (! $tableReady): ?>
    <div class="alert warning">Penyimpanan data kader belum siap.</div>
  <?php else: ?>
    <form method="post" action="<?= site_url('admin/kesehatan-data') ?>" class="grid-form">
      <input type="hidden" name="action" value="save_participant">
      <input type="hidden" name="id" value="<?= rw_esc((string) ($edit['id'] ?? '')) ?>">
      <label>Jenis Layanan
        <select name="jenis" required>
          <?php foreach ($participantTypeOptions as $value => $label): ?>
            <option value="<?= rw_esc($value) ?>" <?= is_selected($selectedJenis, $value) ?>><?= rw_esc($label) ?></option>
          <?php endforeach; ?>
        </select>
      </label>
      <label>Nama Peserta
        <input type="text" name="nama" maxlength="160" value="<?= rw_esc(old('nama', $edit['nama'] ?? '')) ?>" required>
      </label>
      <label>Tanggal Lahir
        <input type="date" name="tanggal_lahir" value="<?= rw_esc(old('tanggal_lahir', $edit['tanggal_lahir'] ?? '')) ?>">
      </label>
      <label>Jenis Kelamin
        <select name="jenis_kelamin">
          <option value="">Pilih bila diperlukan</option>
          <?php foreach ($genderOptions as $value => $label): ?>
            <option value="<?= rw_esc($value) ?>" <?= is_selected($selectedGender, $value) ?>><?= rw_esc($label) ?></option>
          <?php endforeach; ?>
        </select>
      </label>
      <label>Nama Orang Tua / Wali
        <input type="text" name="nama_wali" maxlength="160" value="<?= rw_esc(old('nama_wali', $edit['nama_wali'] ?? '')) ?>">
      </label>
      <label>RT
        <input type="text" name="rt" maxlength="20" value="<?= rw_esc(old('rt', $edit['rt'] ?? '')) ?>" placeholder="Contoh: 01">
      </label>
      <label>No. HP Kontak
        <input type="text" name="no_hp" maxlength="40" value="<?= rw_esc(old('no_hp', $edit['no_hp'] ?? '')) ?>">
      </label>
      <label>Status
        <select name="status">
          <?php foreach ($statusOptions as $value => $label): ?>
            <option value="<?= rw_esc($value) ?>" <?= is_selected($selectedStatus, $value) ?>><?= rw_esc($label) ?></option>
          <?php endforeach; ?>
        </select>
      </label>
      <label class="full">Alamat Singkat
        <input type="text" name="alamat" maxlength="255" value="<?= rw_esc(old('alamat', $edit['alamat'] ?? '')) ?>">
      </label>
      <label class="full">Catatan Kader
        <textarea name="catatan" rows="3" maxlength="2000" placeholder="Catatan non-sensitif untuk kebutuhan pendampingan."><?= rw_esc(old('catatan', $edit['catatan'] ?? '')) ?></textarea>
      </label>
      <div class="full form-actions">
        <button type="submit"><?= $isEditing ? 'Simpan Perubahan' : 'Simpan Peserta' ?></button>
        <?php if ($isEditing): ?><a class="btn-light" href="<?= site_url('admin/kesehatan-data') ?>">Batal</a><?php endif; ?>
      </div>
    </form>
  <?php endif; ?>
</section>

<section class="panel">
  <div class="section-heading">
    <div>
      <h2>Daftar Peserta</h2>
      <p class="muted">Gunakan tombol Kunjungan untuk mencatat kehadiran dan pemeriksaan dasar.</p>
    </div>
  </div>
  <form method="get" action="<?= site_url('admin/kesehatan-data') ?>" class="grid-form">
    <label>Jenis
      <select name="jenis">
        <option value="">Semua</option>
        <?php foreach ($participantTypeOptions as $value => $label): ?><option value="<?= rw_esc($value) ?>" <?= is_selected($filterJenis, $value) ?>><?= rw_esc($label) ?></option><?php endforeach; ?>
      </select>
    </label>
    <label>Cari Nama / Wali
      <input type="search" name="q" value="<?= rw_esc($filterSearch) ?>" placeholder="Ketik nama">
    </label>
    <div class="form-actions"><button type="submit">Filter</button><a class="btn-light" href="<?= site_url('admin/kesehatan-data') ?>">Reset</a></div>
  </form>
  <div class="table-scroll">
    <table>
      <thead><tr><th>Jenis</th><th>Peserta</th><th>Kontak Lingkungan</th><th>Status</th><th>Aksi</th></tr></thead>
      <tbody>
        <?php foreach ($participants as $participant): ?>
          <tr>
            <td><?= rw_esc($participantTypeOptions[$participant['jenis'] ?? ''] ?? ucfirst((string) ($participant['jenis'] ?? '-'))) ?></td>
            <td><strong><?= rw_esc($participant['nama'] ?? '') ?></strong><br><small><?= rw_esc($participant['tanggal_lahir'] ?? '-') ?><?= ! empty($participant['nama_wali']) ? ' · Wali: ' . rw_esc($participant['nama_wali']) : '' ?></small></td>
            <td>RT <?= rw_esc($participant['rt'] ?? '-') ?><br><small><?= rw_esc($participant['no_hp'] ?? '') ?></small></td>
            <td><?= rw_esc($statusOptions[$participant['status'] ?? ''] ?? ucfirst((string) ($participant['status'] ?? '-'))) ?></td>
            <td><div class="table-actions"><a href="<?= site_url('admin/kesehatan-data?peserta_id=' . (int) $participant['id']) ?>">Kunjungan</a><a href="<?= site_url('admin/kesehatan-data?action=edit&id=' . (int) $participant['id']) ?>">Edit</a><a class="btn-link-danger" href="<?= site_url('admin/kesehatan-data?action=delete&id=' . (int) $participant['id']) ?>" onclick="return confirm('Hapus peserta dan seluruh catatan kunjungannya?')">Hapus</a></div></td>
          </tr>
        <?php endforeach; ?>
        <?php if (empty($participants)): ?><tr><td colspan="5" class="table-empty">Belum ada peserta sesuai filter.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</section>

<section class="panel">
  <div class="section-heading">
    <div><h2>Catat Kunjungan / Pemeriksaan</h2><p class="muted">Untuk Posyandu gunakan berat/tinggi. Untuk Posbindu gunakan tekanan darah atau gula darah bila memang diperiksa.</p></div>
    <?php if ($selectedParticipant): ?><strong><?= rw_esc($selectedParticipant['nama']) ?></strong><?php endif; ?>
  </div>
  <form method="post" action="<?= site_url('admin/kesehatan-data') ?>" class="grid-form">
    <input type="hidden" name="action" value="save_visit">
    <label>Peserta
      <select name="peserta_id" required>
        <option value="">Pilih peserta</option>
        <?php foreach ($participants as $participant): ?><option value="<?= (int) $participant['id'] ?>" <?= is_selected((string) ($selectedParticipant['id'] ?? ''), (string) $participant['id']) ?>><?= rw_esc(($participantTypeOptions[$participant['jenis']] ?? $participant['jenis']) . ' · ' . $participant['nama']) ?></option><?php endforeach; ?>
      </select>
    </label>
    <label>Tanggal Kunjungan
      <input type="date" name="tanggal" value="<?= rw_esc(old('tanggal', date('Y-m-d'))) ?>" required>
    </label>
    <label>Kehadiran
      <select name="hadir"><option value="ya">Hadir</option><option value="tidak">Tidak hadir</option></select>
    </label>
    <label>Berat (kg)<input type="number" name="berat_kg" min="0" max="300" step="0.01" value="<?= rw_esc(old('berat_kg')) ?>"></label>
    <label>Tinggi (cm)<input type="number" name="tinggi_cm" min="0" max="250" step="0.01" value="<?= rw_esc(old('tinggi_cm')) ?>"></label>
    <label>Tekanan Sistolik<input type="number" name="tekanan_sistolik" min="0" max="300" value="<?= rw_esc(old('tekanan_sistolik')) ?>"></label>
    <label>Tekanan Diastolik<input type="number" name="tekanan_diastolik" min="0" max="300" value="<?= rw_esc(old('tekanan_diastolik')) ?>"></label>
    <label>Gula Darah<input type="number" name="gula_darah" min="0" max="1000" step="0.01" value="<?= rw_esc(old('gula_darah')) ?>"></label>
    <label class="full">Catatan Kunjungan<textarea name="catatan_kunjungan" rows="3" maxlength="2000" placeholder="Catatan tindak lanjut non-diagnosis."><?= rw_esc(old('catatan_kunjungan')) ?></textarea></label>
    <div class="full form-actions"><button type="submit">Simpan Kunjungan</button></div>
  </form>
</section>

<section class="panel">
  <h2>Riwayat Kunjungan Terbaru</h2>
  <div class="table-scroll">
    <table>
      <thead><tr><th>Tanggal</th><th>Peserta</th><th>Hadir</th><th>Pengukuran</th><th>Catatan</th></tr></thead>
      <tbody>
        <?php foreach ($visits as $visit): ?>
          <tr><td><?= rw_esc(date('d/m/Y', strtotime((string) $visit['tanggal']))) ?></td><td><?= rw_esc($visit['nama']) ?><br><small><?= rw_esc($participantTypeOptions[$visit['jenis']] ?? $visit['jenis']) ?></small></td><td><?= rw_esc($visit['hadir']) ?></td><td><?= $visit['berat_kg'] !== null ? 'BB ' . rw_esc($visit['berat_kg']) . ' kg · ' : '' ?><?= $visit['tinggi_cm'] !== null ? 'TB ' . rw_esc($visit['tinggi_cm']) . ' cm · ' : '' ?><?= $visit['tekanan_sistolik'] !== null ? 'TD ' . rw_esc($visit['tekanan_sistolik']) . '/' . rw_esc($visit['tekanan_diastolik']) . ' · ' : '' ?><?= $visit['gula_darah'] !== null ? 'Gula ' . rw_esc($visit['gula_darah']) : '-' ?></td><td><?= nl2br(rw_esc($visit['catatan'] ?? '-')) ?></td></tr>
        <?php endforeach; ?>
        <?php if (empty($visits)): ?><tr><td colspan="5" class="table-empty">Belum ada catatan kunjungan.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</section>
<?= $this->endSection() ?>
