<?= $this->extend('layouts/kesehatan_admin') ?>

<?= $this->section('content') ?>
<?php
$isEditing = ! empty($edit);
$selectedJenis = old('jenis', $edit['jenis'] ?? 'posyandu');
$selectedStatus = old('status', $edit['status'] ?? 'aktif');
?>
<div class="section-heading">
  <div>
    <h1>Jadwal Posyandu & Posbindu</h1>
    <p class="muted">Atur jadwal layanan kesehatan warga. Jangan masukkan hasil pemeriksaan atau data kesehatan pribadi di sini.</p>
  </div>
  <a href="<?= site_url('kesehatan#jadwal-bantuan') ?>" target="_blank" rel="noopener noreferrer">Lihat Halaman Warga</a>
</div>

<?php if ($success !== ''): ?>
  <div class="alert success"><?= rw_esc($success) ?></div>
<?php endif; ?>
<?php if ($error !== ''): ?>
  <div class="alert error"><?= rw_esc($error) ?></div>
<?php endif; ?>

<section class="panel">
  <div class="section-heading">
    <div>
      <h2><?= $isEditing ? 'Edit Jadwal' : 'Tambah Jadwal' ?></h2>
      <p class="muted">Informasi ini akan tampil pada halaman Kesehatan warga jika statusnya Tayang dan tanggalnya belum lewat.</p>
    </div>
    <?php if ($isEditing): ?>
      <a href="<?= site_url('admin/kesehatan-jadwal') ?>">Batal Edit</a>
    <?php endif; ?>
  </div>

  <?php if (! $tableReady): ?>
    <div class="alert warning">Form belum dapat digunakan karena penyimpanan jadwal belum siap.</div>
  <?php else: ?>
    <form method="post" action="<?= site_url('admin/kesehatan-jadwal') ?>" class="grid-form">
      <?= csrf_field() ?>
      <input type="hidden" name="id" value="<?= rw_esc((string) ($edit['id'] ?? '')) ?>">
      <label>Jenis Kegiatan
        <select name="jenis" required>
          <?php foreach ($typeOptions as $value => $label): ?>
            <option value="<?= rw_esc($value) ?>" <?= is_selected($selectedJenis, $value) ?>><?= rw_esc($label) ?></option>
          <?php endforeach; ?>
        </select>
      </label>
      <label>Tanggal
        <input type="date" name="tanggal" value="<?= rw_esc(old('tanggal', $edit['tanggal'] ?? date('Y-m-d'))) ?>" required>
      </label>
      <label>Waktu
        <input type="text" name="waktu" maxlength="80" value="<?= rw_esc(old('waktu', $edit['waktu'] ?? '')) ?>" placeholder="Contoh: 08.00 - 11.00 WIB">
      </label>
      <label>Lokasi
        <input type="text" name="lokasi" maxlength="180" value="<?= rw_esc(old('lokasi', $edit['lokasi'] ?? '')) ?>" placeholder="Contoh: Balai RW 05" required>
      </label>
      <label>Penanggung Jawab
        <input type="text" name="penanggung_jawab" maxlength="160" value="<?= rw_esc(old('penanggung_jawab', $edit['penanggung_jawab'] ?? '')) ?>" placeholder="Contoh: Kader Posyandu RW 05">
      </label>
      <label>Kontak
        <input type="text" name="kontak" maxlength="80" value="<?= rw_esc(old('kontak', $edit['kontak'] ?? '')) ?>" placeholder="Nomor WhatsApp atau keterangan kontak">
      </label>
      <label>Status
        <select name="status" required>
          <?php foreach ($statusOptions as $value => $label): ?>
            <option value="<?= rw_esc($value) ?>" <?= is_selected($selectedStatus, $value) ?>><?= rw_esc($label) ?></option>
          <?php endforeach; ?>
        </select>
      </label>
      <label class="full">Judul Kegiatan
        <input type="text" name="judul" maxlength="180" value="<?= rw_esc(old('judul', $edit['judul'] ?? '')) ?>" placeholder="Contoh: Posyandu Balita Bulan September" required>
      </label>
      <label class="full">Keterangan
        <textarea name="deskripsi" rows="4" maxlength="2000" placeholder="Layanan atau hal yang perlu disiapkan warga."><?= rw_esc(old('deskripsi', $edit['deskripsi'] ?? '')) ?></textarea>
      </label>
      <div class="full form-actions">
        <button type="submit"><?= $isEditing ? 'Simpan Perubahan' : 'Tambah Jadwal' ?></button>
        <?php if ($isEditing): ?>
          <a class="btn-light" href="<?= site_url('admin/kesehatan-jadwal') ?>">Batal</a>
        <?php endif; ?>
      </div>
    </form>
  <?php endif; ?>
</section>

<section class="panel">
  <div class="section-heading">
    <div>
      <h2>Daftar Jadwal</h2>
      <p class="muted">Jadwal terbaru berada di bagian atas. Jadwal lampau tetap tersimpan untuk riwayat admin.</p>
    </div>
  </div>
  <div class="table-scroll">
    <table>
      <thead>
        <tr>
          <th>Jenis</th>
          <th>Kegiatan</th>
          <th>Waktu</th>
          <th>Lokasi</th>
          <th>Status</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($rows as $row): ?>
          <tr>
            <td><?= rw_esc($typeOptions[$row['jenis'] ?? ''] ?? ucfirst((string) ($row['jenis'] ?? '-'))) ?></td>
            <td>
              <strong><?= rw_esc($row['judul'] ?? '') ?></strong><br>
              <small><?= rw_esc($row['deskripsi'] ?? '') ?></small>
            </td>
            <td><?= rw_esc(date('d/m/Y', strtotime((string) ($row['tanggal'] ?? 'now')))) ?><br><?= rw_esc($row['waktu'] ?? '-') ?></td>
            <td><?= rw_esc($row['lokasi'] ?? '-') ?><br><small><?= rw_esc($row['penanggung_jawab'] ?? '') ?></small></td>
            <td><?= rw_esc($statusOptions[$row['status'] ?? ''] ?? ucfirst((string) ($row['status'] ?? '-'))) ?></td>
            <td>
              <div class="table-actions">
                <a href="<?= site_url('admin/kesehatan-jadwal?action=edit&id=' . (int) ($row['id'] ?? 0)) ?>">Edit</a>
                <a class="btn-link-danger" href="<?= site_url('admin/kesehatan-jadwal?action=delete&id=' . (int) ($row['id'] ?? 0)) ?>" onclick="return confirm('Hapus jadwal ini?')">Hapus</a>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if (empty($rows)): ?>
          <tr><td colspan="6" class="table-empty">Belum ada jadwal Posyandu atau Posbindu.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</section>
<?= $this->endSection() ?>
