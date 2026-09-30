<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<h1><?= rw_esc($config['title']) ?></h1>
<?php if (! empty($config['description'])): ?>
  <p class="muted"><?= rw_esc($config['description']) ?></p>
<?php endif; ?>

<?php if (session()->getFlashdata('success')): ?>
  <div class="alert success"><?= rw_esc(session()->getFlashdata('success')) ?></div>
<?php endif; ?>
<?php if (session()->getFlashdata('error')): ?>
  <div class="alert error"><?= rw_esc(session()->getFlashdata('error')) ?></div>
<?php endif; ?>

<?php if (! empty($config['notice'])): ?>
  <div class="alert warning"><?= rw_esc($config['notice']) ?></div>
<?php endif; ?>

<?php if (! empty($config['imageUpload'])): ?>
  <section class="panel">
    <div class="section-heading compact-heading">
      <div>
        <h2><?= rw_esc($config['imageUpload']['title']) ?></h2>
        <p class="muted"><?= rw_esc($config['imageUpload']['description']) ?></p>
      </div>
    </div>
    <div class="alert info">
      Gambar ini menjadi acuan utama halaman Pengurus di web warga. Setelah upload ulang, halaman warga akan memakai gambar terbaru.
    </div>
    <?php if (! empty($config['imageUpload']['imageUrl'])): ?>
      <div class="structure-image-preview">
        <img src="<?= rw_esc($config['imageUpload']['imageUrl']) ?>" alt="Gambar struktur organisasi">
      </div>
    <?php endif; ?>
    <form method="post" action="<?= rw_esc($config['imageUpload']['uploadUrl']) ?>" enctype="multipart/form-data" class="grid-form upload-form">
      <?= csrf_field() ?>
      <label class="full">File Gambar
        <input type="file" name="struktur_gambar" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" required>
      </label>
      <div class="full form-actions">
        <button type="submit">Upload Gambar</button>
        <?php if (! empty($config['imageUpload']['imageUrl'])): ?>
          <button type="submit" form="deleteStructureImageForm" class="btn-danger">Hapus Gambar</button>
        <?php endif; ?>
      </div>
    </form>
    <?php if (! empty($config['imageUpload']['imageUrl'])): ?>
      <form id="deleteStructureImageForm" method="post" action="<?= rw_esc($config['imageUpload']['deleteUrl']) ?>" onsubmit="return confirm('Hapus gambar struktur organisasi?')"><?= csrf_field() ?></form>
    <?php endif; ?>
    <form method="post" action="<?= rw_esc($config['imageUpload']['descriptionSaveUrl']) ?>" class="grid-form structure-text-form">
      <?= csrf_field() ?>
      <label class="full">Penjelasan Struktur Organisasi
        <textarea name="struktur_penjelasan" rows="6" placeholder="Contoh: Ketua RW berada di posisi tertinggi, dibantu Sekretaris dan Bendahara. Ketua RT 01 sampai RT 06 menjadi koordinator wilayah masing-masing."><?= rw_esc($config['imageUpload']['descriptionValue'] ?? '') ?></textarea>
        <span class="field-note">Teks ini akan tampil di bawah gambar struktur organisasi pada halaman warga.</span>
      </label>
      <div class="full form-actions">
        <button type="submit">Simpan Penjelasan</button>
      </div>
    </form>
  </section>
<?php endif; ?>

<?php if (! empty($config['importType'])): ?>
  <section class="panel compact-panel">
    <div class="section-heading compact-heading">
      <div>
        <h2>Upload dari Template</h2>
        <p class="muted">Gunakan template CSV kalau data pengurus ingin diisi sekaligus dari Excel atau Google Sheets.</p>
      </div>
      <div class="page-actions">
        <a class="btn-light" href="<?= site_url('admin/import/template/' . $config['importType']) ?>">Download Template</a>
        <a class="btn-light" href="<?= site_url('admin/import?type=' . $config['importType']) ?>">Import CSV</a>
      </div>
    </div>
  </section>
<?php endif; ?>

<?php if (! empty($config['orgChart'])): ?>
  <?php $chartGroups = pengurus_chart_groups($rows ?? []); ?>
  <section class="panel admin-org-chart-panel">
    <div class="section-heading compact-heading">
      <div>
        <p class="admin-kicker">Preview otomatis</p>
        <h2>Diagram Kepengurusan</h2>
        <p class="muted">Diagram ini dibuat langsung dari data pengurus aktif. Nama, jabatan, atau urutan yang diubah di bawah akan otomatis memperbarui diagram dan halaman warga.</p>
      </div>
      <a class="btn-light" href="<?= site_url('pengurus') ?>" target="_blank" rel="noopener noreferrer">Lihat Halaman Warga</a>
    </div>
    <?php if ($chartGroups): ?>
      <?php $diagramRows = [['command', ['pembina', 'ketua', 'penasihat']], ['core', ['inti', 'wilayah']], ['main', ['unit', 'bidang']], ['support', ['mitra']]]; ?>
      <div class="org-chart admin-org-diagram" aria-label="Diagram kepengurusan RW 05">
        <?php foreach ($diagramRows as [$rowClass, $groupKeys]): ?>
          <div class="admin-org-diagram-row admin-org-diagram-row-<?= rw_esc($rowClass) ?>">
            <?php foreach ($groupKeys as $groupKey): ?>
              <?php if (empty($chartGroups[$groupKey])): ?><?php continue; ?><?php endif; ?>
              <?php $group = $chartGroups[$groupKey]; ?>
              <section class="admin-org-diagram-group admin-org-diagram-group-<?= rw_esc($groupKey) ?>">
                <h3><?= rw_esc($group['label']) ?></h3>
                <div class="org-chart-nodes">
                  <?php foreach ($group['items'] as $person): ?>
                    <article class="org-chart-node">
                      <span><?= rw_esc(strtoupper(substr((string) ($person['nama'] ?? 'P'), 0, 1))) ?></span>
                      <div><strong><?= rw_esc($person['nama'] ?? '') ?></strong><small><?= rw_esc($person['jabatan'] ?? '') ?><?= ! empty($person['rt']) ? ' · RT ' . rw_esc($person['rt']) : '' ?></small></div>
                    </article>
                  <?php endforeach; ?>
                </div>
              </section>
            <?php endforeach; ?>
          </div>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <p class="empty-state">Belum ada pengurus aktif. Tambahkan data pertama agar diagram terbentuk otomatis.</p>
    <?php endif; ?>
  </section>
<?php endif; ?>

<section class="panel">
  <h2><?= $edit ? 'Edit Data' : 'Tambah Data' ?></h2>
  <form method="post" action="<?= site_url('admin/' . $page) ?>" class="grid-form">
    <?= csrf_field() ?>
    <input type="hidden" name="id" value="<?= rw_esc($edit['id'] ?? '') ?>">

    <?php foreach ($config['fields'] as $name => $field): ?>
      <?php
        $value = $edit[$name] ?? ($field['default'] ?? '');
        $isFull = ! empty($field['full']) ? ' class="full"' : '';
        $required = ! empty($field['required']) ? ' required' : '';
      ?>
      <label<?= $isFull ?>><?= rw_esc($field['label']) ?>
        <?php if (($field['type'] ?? 'text') === 'textarea'): ?>
          <textarea name="<?= rw_esc($name) ?>" rows="4"<?= $required ?>><?= rw_esc($value) ?></textarea>
        <?php elseif (($field['type'] ?? 'text') === 'select'): ?>
          <select name="<?= rw_esc($name) ?>"<?= $required ?>>
            <?php foreach (($field['options'] ?? []) as $optionValue => $optionLabel): ?>
              <option value="<?= rw_esc($optionValue) ?>" <?= is_selected($value, $optionValue) ?>><?= rw_esc($optionLabel) ?></option>
            <?php endforeach; ?>
          </select>
        <?php else: ?>
          <input type="<?= rw_esc($field['type'] ?? 'text') ?>" name="<?= rw_esc($name) ?>" value="<?= rw_esc($value) ?>"<?= $required ?>>
        <?php endif; ?>
      </label>
    <?php endforeach; ?>

    <button type="submit"><?= $edit ? 'Update' : 'Simpan' ?></button>
    <?php if ($edit): ?><a class="btn-light" href="<?= site_url('admin/' . $page) ?>">Batal</a><?php endif; ?>
  </form>
</section>

<section class="panel">
  <h2>Daftar Data</h2>
  <table>
    <thead>
      <tr>
        <?php foreach ($config['columns'] as $label): ?><th><?= rw_esc($label) ?></th><?php endforeach; ?>
        <th>Aksi</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($rows as $row): ?>
        <tr>
          <?php foreach ($config['columns'] as $column => $label): ?>
            <td>
              <?php if ($column === 'tanggal'): ?>
                <?= rw_esc(fmt_date($row[$column] ?? '')) ?>
              <?php elseif (in_array($column, ['deskripsi', 'tugas', 'isi', 'keterangan'], true)): ?>
                <?= nl2br(rw_esc($row[$column] ?? '')) ?>
              <?php else: ?>
                <?= rw_esc($row[$column] ?? '') ?>
              <?php endif; ?>
            </td>
          <?php endforeach; ?>
          <td><div class="table-actions">
            <a class="btn-table-edit" href="<?= site_url('admin/' . $page . '?action=edit&id=' . ($row['id'] ?? 0)) ?>">Edit</a>
            <?php $archiveLabel = empty($config['archiveStatus']) ? 'Hapus' : 'Arsipkan'; ?>
            <form method="post" action="<?= site_url('admin/' . $page) ?>" class="inline-form" onsubmit="return confirm('<?= rw_esc($archiveLabel) ?> data ini?')">
              <?= csrf_field() ?>
              <input type="hidden" name="action" value="delete">
              <input type="hidden" name="id" value="<?= (int) ($row['id'] ?? 0) ?>">
              <button type="submit" class="btn-link-danger"><?= rw_esc($archiveLabel) ?></button>
            </form>
          </div></td>
        </tr>
      <?php endforeach; ?>
      <?php if (empty($rows)): ?>
        <tr><td colspan="<?= count($config['columns']) + 1 ?>" class="table-empty">Belum ada data.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</section>
<?= $this->endSection() ?>
