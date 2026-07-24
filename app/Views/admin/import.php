<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<h1>Import Massal</h1>
<p class="muted">Menu ini dipakai kalau data dari Excel/Google Sheets ingin dimasukkan sekaligus, misalnya data warga, pengurus, kegiatan, atau layanan. Untuk edit satu-dua data, lebih enak lewat menu masing-masing.</p>

<?php if ($success): ?><div class="alert success"><?= rw_esc($success) ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert error"><?= rw_esc($error) ?></div><?php endif; ?>
<?php if ($details): ?>
  <div class="alert warning">
    <?php foreach ($details as $detail): ?>
      <div><?= rw_esc($detail) ?></div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<section class="panel">
  <h2>Langkah Cepat</h2>
  <div class="workflow-grid">
    <article class="workflow-card">
      <span class="workflow-step">Langkah 1</span>
      <strong>Download template</strong>
      <p>Pilih jenis data, unduh template, lalu isi sesuai kolom yang tersedia.</p>
    </article>
    <article class="workflow-card">
      <span class="workflow-step">Langkah 2</span>
      <strong>Review di spreadsheet</strong>
      <p>Pastikan kolom wajib terisi dan jangan simpan NIK, No KK, atau dokumen pribadi.</p>
    </article>
    <article class="workflow-card">
      <span class="workflow-step">Langkah 3</span>
      <strong>Upload CSV</strong>
      <p>Import ke database dengan mode tambah data atau replace data lama.</p>
    </article>
  </div>
</section>

<section class="panel">
  <h2>Upload File CSV</h2>
  <form method="post" action="<?= site_url('admin/import') ?>" enctype="multipart/form-data" class="grid-form">
    <label>Jenis Data
      <select name="dataset" required>
        <?php foreach ($datasets as $key => $dataset): ?>
          <option value="<?= rw_esc($key) ?>" <?= is_selected($selectedType, $key) ?>><?= rw_esc($dataset['label']) ?></option>
        <?php endforeach; ?>
      </select>
    </label>

    <label>Mode Import
      <select name="mode" required>
        <option value="append">Tambah data baru</option>
        <option value="replace">Hapus data lama lalu import</option>
      </select>
      <span class="field-note">Mode replace akan menghapus semua data di jenis data terpilih.</span>
    </label>

    <label class="full">File CSV
      <input type="file" name="csv_file" accept=".csv,text/csv" required>
    </label>

    <div class="full helper-note helper-note-tight">
      <strong>Kolom untuk <?= rw_esc($currentDataset['label']) ?></strong>
      <ul class="helper-list compact">
        <li>Kolom: <code><?= rw_esc(implode(', ', $currentDataset['columns'])) ?></code></li>
        <li>Kolom wajib: <code><?= rw_esc(implode(', ', $currentDataset['required'])) ?></code></li>
        <li>Pemisah CSV boleh koma, titik koma, tab, atau tanda |.</li>
        <?php foreach (($currentDataset['notes'] ?? []) as $note): ?>
          <li><?= rw_esc($note) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>

    <div class="full form-actions">
      <button type="submit">Import Data</button>
      <a class="btn-light" href="<?= site_url('admin/import/template/' . $selectedType) ?>">Download Template</a>
    </div>
  </form>
</section>
<?= $this->endSection() ?>
