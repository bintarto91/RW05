<?php
$isEditing = ! empty($edit);
$currentFilePath = trim((string) ($edit['file_path'] ?? ''));
$currentFileUrl = $currentFilePath !== '' ? base_url(ltrim(str_replace('\\', '/', $currentFilePath), '/')) : '';
$selectedType = (string) old('jenis', $edit['jenis'] ?? 'poster');
?>
<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<div class="section-heading">
  <div>
    <h1>Materi Edukasi</h1>
    <p class="muted">Kelola karya dosen dan sumber resmi untuk setiap topik kesehatan RW.</p>
  </div>
  <a href="<?= site_url('edukasi-kesehatan') ?>" target="_blank" rel="noopener noreferrer">Lihat Halaman Warga</a>
</div>

<?php if ($success !== ''): ?>
  <div class="alert success"><?= rw_esc($success) ?></div>
<?php endif; ?>
<?php if ($error !== ''): ?>
  <div class="alert error"><?= rw_esc($error) ?></div>
<?php endif; ?>

<section class="education-admin-summary" aria-label="Ringkasan materi edukasi">
  <?php
  $summaryCards = [
      ['label' => 'Total Materi', 'value' => $summary['total'] ?? 0],
      ['label' => 'Tayang', 'value' => $summary['publish'] ?? 0],
      ['label' => 'Draft', 'value' => $summary['draft'] ?? 0],
      ['label' => 'Poster', 'value' => $summary['poster'] ?? 0],
      ['label' => 'Video', 'value' => $summary['video'] ?? 0],
      ['label' => 'Artikel', 'value' => $summary['artikel'] ?? 0],
  ];
  ?>
  <?php foreach ($summaryCards as $card): ?>
    <article class="info-card">
      <span class="info-label"><?= rw_esc($card['label']) ?></span>
      <strong class="education-summary-value"><?= rw_esc((string) $card['value']) ?></strong>
    </article>
  <?php endforeach; ?>
</section>

<section class="panel">
  <div class="section-heading">
    <div>
      <h2><?= $isEditing ? 'Edit Materi' : 'Tambah Materi' ?></h2>
      <p class="muted">Pilih satu dari enam kategori, lalu tentukan bentuk materinya.</p>
    </div>
    <?php if ($isEditing): ?>
      <a href="<?= site_url('admin/edukasi') ?>">Batal Edit</a>
    <?php endif; ?>
  </div>

  <?php if (! $tableReady): ?>
    <div class="alert warning">Form belum dapat digunakan karena penyimpanan materi belum siap.</div>
  <?php else: ?>
    <form method="post" action="<?= site_url('admin/edukasi') ?>" enctype="multipart/form-data" class="grid-form education-form">
      <input type="hidden" name="id" value="<?= rw_esc((string) ($edit['id'] ?? '')) ?>">

      <label>Kategori Edukasi
        <select name="kategori" required>
          <?php foreach ($categoryOptions as $value => $label): ?>
            <option value="<?= rw_esc($value) ?>" <?= is_selected(old('kategori', $edit['kategori'] ?? 'ibu-anak'), $value) ?>><?= rw_esc($label) ?></option>
          <?php endforeach; ?>
        </select>
      </label>

      <label>Jenis Materi
        <select name="jenis" id="educationType" required>
          <?php foreach ($typeOptions as $value => $label): ?>
            <option value="<?= rw_esc($value) ?>" <?= is_selected($selectedType, $value) ?>><?= rw_esc($label) ?></option>
          <?php endforeach; ?>
        </select>
      </label>

      <label class="full">Judul Materi
        <input type="text" name="judul" maxlength="180" value="<?= rw_esc(old('judul', $edit['judul'] ?? '')) ?>" placeholder="Contoh: Dukung Tumbuh Kembang Anak" required>
      </label>

      <label>Nama Dosen / Penulis
        <input type="text" name="penulis" maxlength="160" value="<?= rw_esc(old('penulis', $edit['penulis'] ?? '')) ?>" placeholder="Nama lengkap beserta gelar" required>
      </label>

      <label>Institusi
        <input type="text" name="institusi" maxlength="160" value="<?= rw_esc(old('institusi', $edit['institusi'] ?? '')) ?>" placeholder="Contoh: STIKes Dharma Husada">
      </label>

      <label>Tahun
        <input type="number" name="tahun" min="1900" max="2099" value="<?= rw_esc(old('tahun', $edit['tahun'] ?? date('Y'))) ?>" placeholder="<?= rw_esc(date('Y')) ?>">
      </label>

      <label>Urutan Tampil
        <input type="number" name="urutan" min="0" max="9999" value="<?= rw_esc(old('urutan', (string) ($edit['urutan'] ?? 10))) ?>">
        <span class="field-note">Angka lebih kecil tampil lebih dahulu.</span>
      </label>

      <label class="full">Ringkasan
        <textarea name="ringkasan" rows="4" maxlength="1000" placeholder="Jelaskan singkat isi dan manfaat materi."><?= rw_esc(old('ringkasan', $edit['ringkasan'] ?? '')) ?></textarea>
      </label>

      <label class="full education-field education-url-field">Tautan Materi
        <input type="url" name="tautan" value="<?= rw_esc(old('tautan', $edit['tautan'] ?? '')) ?>" placeholder="https://youtube.com/... atau https://...">
        <span class="field-note" data-url-note>Untuk video, tempel tautan YouTube. Artikel dapat memakai tautan halaman web.</span>
      </label>

      <label class="full education-field education-file-field">Upload File
        <input type="file" name="materi_file" data-education-file>
        <span class="field-note" data-file-note>Poster: JPG, PNG, atau WebP. Artikel: PDF. Ukuran maksimal 5 MB.</span>
        <?php if ($currentFileUrl !== ''): ?>
          <a class="education-current-file" href="<?= rw_esc($currentFileUrl) ?>" target="_blank" rel="noopener noreferrer">Lihat file yang sedang digunakan</a>
        <?php endif; ?>
      </label>

      <label>Status
        <select name="status" required>
          <?php foreach ($statusOptions as $value => $label): ?>
            <option value="<?= rw_esc($value) ?>" <?= is_selected(old('status', $edit['status'] ?? 'draft'), $value) ?>><?= rw_esc($label) ?></option>
          <?php endforeach; ?>
        </select>
        <span class="field-note">Gunakan Draft bila materi masih perlu ditinjau.</span>
      </label>

      <div class="education-review-note">
        <strong>Sebelum ditayangkan</strong>
        <span>Pastikan karya telah mendapat izin publikasi dan isi kesehatan sudah ditinjau oleh pihak yang berwenang.</span>
      </div>

      <div class="full form-actions">
        <button type="submit"><?= $isEditing ? 'Simpan Perubahan' : 'Tambah Materi' ?></button>
        <?php if ($isEditing): ?>
          <a class="btn-light" href="<?= site_url('admin/edukasi') ?>">Batal</a>
        <?php endif; ?>
      </div>
    </form>
  <?php endif; ?>
</section>

<section class="panel">
  <div class="section-heading">
    <div>
      <h2>Daftar Materi</h2>
      <p class="muted">Filter daftar untuk memeriksa isi pada kategori tertentu.</p>
    </div>
  </div>

  <form method="get" action="<?= site_url('admin/edukasi') ?>" class="education-filter-form">
    <label>Kategori
      <select name="kategori">
        <option value="">Semua kategori</option>
        <?php foreach ($categoryOptions as $value => $label): ?>
          <option value="<?= rw_esc($value) ?>" <?= is_selected($filters['kategori'] ?? '', $value) ?>><?= rw_esc($label) ?></option>
        <?php endforeach; ?>
      </select>
    </label>
    <label>Jenis
      <select name="jenis">
        <option value="">Semua jenis</option>
        <?php foreach ($typeOptions as $value => $label): ?>
          <option value="<?= rw_esc($value) ?>" <?= is_selected($filters['jenis'] ?? '', $value) ?>><?= rw_esc($label) ?></option>
        <?php endforeach; ?>
      </select>
    </label>
    <label>Status
      <select name="status">
        <option value="">Semua status</option>
        <?php foreach ($statusOptions as $value => $label): ?>
          <option value="<?= rw_esc($value) ?>" <?= is_selected($filters['status'] ?? '', $value) ?>><?= rw_esc($label) ?></option>
        <?php endforeach; ?>
      </select>
    </label>
    <div class="education-filter-actions">
      <button type="submit">Terapkan</button>
      <a class="btn-light" href="<?= site_url('admin/edukasi') ?>">Reset</a>
    </div>
  </form>

  <div class="table-scroll education-table-wrap">
    <table class="education-table">
      <thead>
        <tr>
          <th>Kategori</th>
          <th>Jenis</th>
          <th>Materi</th>
          <th>Dosen / Penulis</th>
          <th>Media</th>
          <th>Status</th>
          <th>Urutan</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($rows as $row): ?>
          <?php
          $materialUrl = edukasi_material_public_url($row);
          $categoryLabel = $categoryOptions[$row['kategori'] ?? ''] ?? ($row['kategori'] ?? '-');
          $type = (string) ($row['jenis'] ?? '');
          ?>
          <tr>
            <td><span class="education-category-cell"><?= rw_esc($categoryLabel) ?></span></td>
            <td><span class="education-type-badge type-<?= rw_esc($type) ?>"><?= rw_esc($typeOptions[$type] ?? ucfirst($type)) ?></span></td>
            <td>
              <strong class="education-table-title"><?= rw_esc($row['judul'] ?? '') ?></strong>
              <?php if (! empty($row['ringkasan'])): ?>
                <span class="education-table-summary"><?= rw_esc($row['ringkasan']) ?></span>
              <?php endif; ?>
            </td>
            <td>
              <strong><?= rw_esc($row['penulis'] ?? '') ?></strong>
              <?php if (! empty($row['institusi'])): ?><span class="education-table-summary"><?= rw_esc($row['institusi']) ?></span><?php endif; ?>
              <?php if (! empty($row['tahun'])): ?><span class="education-table-summary"><?= rw_esc($row['tahun']) ?></span><?php endif; ?>
            </td>
            <td>
              <?php if ($materialUrl !== ''): ?>
                <a class="education-media-link" href="<?= rw_esc($materialUrl) ?>" target="_blank" rel="noopener noreferrer">Buka</a>
              <?php else: ?>
                <span class="muted">Belum ada</span>
              <?php endif; ?>
            </td>
            <td><span class="badge status-<?= rw_esc($row['status'] ?? 'draft') ?>"><?= rw_esc($statusOptions[$row['status'] ?? 'draft'] ?? ucfirst($row['status'] ?? 'draft')) ?></span></td>
            <td><?= rw_esc((string) ($row['urutan'] ?? 0)) ?></td>
            <td>
              <div class="education-row-actions">
                <a href="<?= site_url('admin/edukasi?action=edit&id=' . (int) ($row['id'] ?? 0)) ?>">Edit</a>
                <form method="post" action="<?= site_url('admin/edukasi/delete/' . (int) ($row['id'] ?? 0)) ?>" onsubmit="return confirm('Hapus materi ini? File upload terkait juga akan dihapus.')">
                  <button type="submit" class="education-delete-button">Hapus</button>
                </form>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if (empty($rows)): ?>
          <tr><td colspan="8" class="table-empty">Belum ada materi sesuai filter.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</section>

<script>
(() => {
  const typeSelect = document.getElementById('educationType');
  const urlField = document.querySelector('.education-url-field');
  const fileField = document.querySelector('.education-file-field');
  const fileInput = document.querySelector('[data-education-file]');
  const urlInput = urlField?.querySelector('input');
  const urlNote = document.querySelector('[data-url-note]');
  const fileNote = document.querySelector('[data-file-note]');
  if (!typeSelect || !urlField || !fileField || !fileInput || !urlInput) return;

  const syncEducationFields = () => {
    const type = typeSelect.value;
    const isPoster = type === 'poster';
    const isVideo = type === 'video';

    urlField.hidden = isPoster;
    urlInput.disabled = isPoster;
    fileField.hidden = isVideo;
    fileInput.disabled = isVideo;
    fileInput.accept = isPoster
      ? '.jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp'
      : '.pdf,application/pdf';

    if (urlNote) {
      urlNote.textContent = isVideo
        ? 'Tempel tautan video YouTube atau platform video resmi.'
        : 'Artikel dapat menggunakan tautan halaman web; bila memakai PDF, tautan boleh dikosongkan.';
    }
    if (fileNote) {
      fileNote.textContent = isPoster
        ? 'Poster: JPG, PNG, atau WebP. Ukuran maksimal 5 MB.'
        : 'Artikel: file PDF. Ukuran maksimal 5 MB; bila memakai tautan, file boleh dikosongkan.';
    }
  };

  typeSelect.addEventListener('change', syncEducationFields);
  syncEducationFields();
})();
</script>
<?= $this->endSection() ?>
