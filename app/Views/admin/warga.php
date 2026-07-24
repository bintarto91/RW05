<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<?php
$baseWargaUrl = site_url('admin/warga');
$filterQuery = array_filter([
    'rt' => $filters['rt'] ?? '',
    'status_tinggal' => $filters['status_tinggal'] ?? '',
    'kategori_kesejahteraan' => $filters['kategori_kesejahteraan'] ?? '',
    'penerima_bantuan' => $filters['penerima_bantuan'] ?? '',
], static fn ($value) => (string) $value !== '');
$wargaUrl = static function (array $extra = []) use ($baseWargaUrl, $filterQuery): string {
    $query = array_merge($filterQuery, $extra);

    return $query === [] ? $baseWargaUrl : $baseWargaUrl . '?' . http_build_query($query);
};
$activeRtLabel = ($filters['rt'] ?? '') !== '' ? 'RT ' . normalize_rt_code($filters['rt']) : 'Semua RT';
$hasOldWargaInput = old('nama_kepala_keluarga') !== null || old('rt') !== null;
$wargaModalOpen = ! empty($edit) || $hasOldWargaInput;
$formTitle = ! empty($edit) ? 'Edit Data Warga' : 'Tambah Data Warga';
?>

<h1>Data Warga</h1>
<p class="muted">Pendataan warga hanya tersedia di dashboard admin/pengurus. Download laporan tidak dibuka di halaman publik warga.</p>

<?php if ($success): ?>
  <div class="alert success"><?= rw_esc($success) ?></div>
<?php endif; ?>
<?php if ($error): ?>
  <div class="alert error"><?= rw_esc($error) ?></div>
<?php endif; ?>

<div class="alert warning">Jangan simpan NIK, nomor KK, foto KTP, slip gaji, atau dokumen pribadi lain di modul warga. Fokuskan pada pendataan lingkungan dan kebutuhan sosial dasar.</div>

<section class="panel compact-panel warga-summary-panel">
  <div class="section-heading compact-heading">
    <div>
      <h2>Ringkasan Cepat</h2>
      <p class="muted">Rekap aktif: <?= rw_esc($activeRtLabel) ?>. Total warga dihitung dari penjumlahan kolom jumlah anggota pada data keluarga yang tampil.</p>
    </div>
    <div class="page-actions warga-main-actions">
      <button type="button" class="btn-strong" data-open-modal="wargaFormModal">Tambah Warga</button>
      <button type="button" class="btn-light" data-open-modal="wargaImportModal">Import CSV</button>
    </div>
  </div>

  <div class="stat-grid">
    <article class="info-card">
      <span class="info-label">Total KK</span>
      <strong class="info-value"><?= rw_esc((string) ($summary['totalKk'] ?? 0)) ?></strong>
    </article>
    <article class="info-card">
      <span class="info-label">Total Warga</span>
      <strong class="info-value"><?= rw_esc((string) ($summary['totalWarga'] ?? 0)) ?> jiwa</strong>
    </article>
    <article class="info-card">
      <span class="info-label">Kurang Mampu</span>
      <strong class="info-value"><?= rw_esc((string) ($summary['kurangMampu'] ?? 0)) ?> KK</strong>
    </article>
    <article class="info-card">
      <span class="info-label">Penerima Bantuan</span>
      <strong class="info-value"><?= rw_esc((string) ($summary['penerimaBantuan'] ?? 0)) ?> KK</strong>
    </article>
  </div>

  <?php if (! empty($bantuanBreakdown)): ?>
    <div class="rt-list warga-help-list">
      <?php foreach ($bantuanBreakdown as $item): ?>
        <div class="rt-row">
          <strong><?= rw_esc($item['label']) ?></strong>
          <span><?= rw_esc((string) $item['total']) ?> KK</span>
          <small>penerima bantuan pada filter aktif</small>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</section>

<section class="panel compact-panel">
  <div class="section-heading compact-heading">
    <div>
      <h2>Filter Data</h2>
      <p class="muted">Saring data per RT, status tinggal, kondisi kesejahteraan, atau status bantuan. Setelah tampil, daftar warga langsung ada di bawah filter.</p>
    </div>
  </div>
  <form method="get" action="<?= site_url('admin/warga') ?>" class="grid-form finance-filter-form" id="wargaFilterForm">
    <label>RT
      <select name="rt">
        <option value="">Semua RT</option>
        <?php foreach ($rtOptions as $value => $label): ?>
          <option value="<?= rw_esc($value) ?>" <?= is_selected($filters['rt'] ?? '', $value) ?>><?= rw_esc($label) ?></option>
        <?php endforeach; ?>
      </select>
    </label>
    <label>Status Tinggal
      <select name="status_tinggal">
        <option value="">Semua Status</option>
        <?php foreach ($statusOptions as $value => $label): ?>
          <option value="<?= rw_esc($value) ?>" <?= is_selected($filters['status_tinggal'] ?? '', $value) ?>><?= rw_esc($label) ?></option>
        <?php endforeach; ?>
      </select>
    </label>
    <label>Kesejahteraan
      <select name="kategori_kesejahteraan">
        <option value="">Semua Kategori</option>
        <?php foreach ($kesejahteraanOptions as $value => $label): ?>
          <option value="<?= rw_esc($value) ?>" <?= is_selected($filters['kategori_kesejahteraan'] ?? '', $value) ?>><?= rw_esc($label) ?></option>
        <?php endforeach; ?>
      </select>
    </label>
    <label>Penerima Bantuan
      <select name="penerima_bantuan">
        <option value="">Semua</option>
        <?php foreach ($bantuanOptions as $value => $label): ?>
          <option value="<?= rw_esc($value) ?>" <?= is_selected($filters['penerima_bantuan'] ?? '', $value) ?>><?= rw_esc($label) ?></option>
        <?php endforeach; ?>
      </select>
    </label>
    <div class="full finance-filter-toolbar">
      <div class="finance-filter-actions warga-filter-actions">
        <button type="submit">Tampilkan Data</button>
        <a class="btn-light" href="<?= site_url('admin/warga') ?>">Reset</a>
      </div>
    </div>
  </form>
</section>

<section class="panel table-card warga-table-panel">
  <div class="section-heading compact-heading">
    <div>
      <h2>Daftar Warga</h2>
      <p class="muted">Data berikut sudah mengikuti filter aktif. Tambah, edit, import, dan hapus data dibuka lewat popup agar daftar tetap mudah dipantau.</p>
    </div>
    <div class="page-actions">
      <button type="button" class="btn-light" data-open-modal="wargaDownloadModal">Download Laporan</button>
      <button type="button" class="btn-light" data-open-modal="wargaFormModal">Tambah Warga</button>
    </div>
  </div>

  <div class="table-scroll">
    <table>
      <thead>
        <tr>
          <th>Profil KK</th>
          <th>Kontak</th>
          <th>Sosial Ekonomi</th>
          <th>Catatan</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($rows as $row): ?>
          <tr>
            <td>
              <strong><?= rw_esc($row['nama_kepala_keluarga'] ?? '') ?></strong><br>
              <small>RT <?= rw_esc(normalize_rt_code($row['rt'] ?? '')) ?> | <?= rw_esc((string) ($row['jumlah_anggota'] ?? 0)) ?> jiwa</small><br>
              <small><?= rw_esc($row['alamat'] ?? '-') ?></small>
            </td>
            <td>
              <strong><?= rw_esc($row['no_hp'] ?: '-') ?></strong><br>
              <small>Pekerjaan: <?= rw_esc($row['pekerjaan_kepala_keluarga'] ?: '-') ?></small><br>
              <small>Status tinggal: <?= rw_esc(warga_option_label($statusOptions, $row['status_tinggal'] ?? '')) ?></small>
            </td>
            <td>
              <strong><?= rw_esc(warga_option_label($kesejahteraanOptions, $row['kategori_kesejahteraan'] ?? '')) ?></strong><br>
              <small>Penerima bantuan: <?= rw_esc(warga_option_label($bantuanOptions, $row['penerima_bantuan'] ?? '')) ?></small><br>
              <small>Jenis bantuan: <?= rw_esc($row['jenis_bantuan'] ?: '-') ?></small>
            </td>
            <td>
              <?= nl2br(rw_esc($row['kondisi_khusus'] ?: '-')) ?>
              <?php if (! empty($row['keterangan'])): ?>
                <br><small><?= nl2br(rw_esc($row['keterangan'])) ?></small>
              <?php endif; ?>
            </td>
            <td>
              <div class="table-actions">
                <a href="<?= rw_esc($wargaUrl(['action' => 'edit', 'id' => $row['id'] ?? 0])) ?>">Edit</a>
                <button
                  type="button"
                  class="btn-link-danger"
                  data-delete-url="<?= rw_esc($wargaUrl(['action' => 'delete', 'id' => $row['id'] ?? 0])) ?>"
                  data-delete-name="<?= rw_esc($row['nama_kepala_keluarga'] ?? 'data warga ini') ?>"
                >Hapus</button>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if (empty($rows)): ?>
          <tr><td colspan="5" class="table-empty">Belum ada data warga pada filter ini.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</section>

<div class="admin-modal <?= $wargaModalOpen ? 'is-open' : '' ?>" id="wargaFormModal" aria-hidden="<?= $wargaModalOpen ? 'false' : 'true' ?>">
  <div class="admin-modal-backdrop" data-close-modal></div>
  <div class="admin-modal-dialog admin-modal-wide" role="dialog" aria-modal="true" aria-labelledby="wargaFormTitle">
    <div class="admin-modal-header">
      <div>
        <p class="admin-kicker">Data keluarga</p>
        <h2 id="wargaFormTitle"><?= rw_esc($formTitle) ?></h2>
      </div>
      <button type="button" class="modal-close" data-close-modal aria-label="Tutup popup">Tutup</button>
    </div>

    <form method="post" action="<?= rw_esc($formAction) ?>" class="grid-form">
      <input type="hidden" name="id" value="<?= rw_esc($edit['id'] ?? '') ?>">

      <label>Nama Kepala Keluarga
        <input type="text" name="nama_kepala_keluarga" value="<?= rw_esc(old('nama_kepala_keluarga', $edit['nama_kepala_keluarga'] ?? '')) ?>" required>
      </label>
      <label>RT
        <input type="text" name="rt" value="<?= rw_esc(old('rt', $edit['rt'] ?? '')) ?>" placeholder="Contoh: 01" required>
      </label>
      <label>Jumlah Jiwa / Anggota KK
        <input type="number" name="jumlah_anggota" min="1" value="<?= rw_esc(old('jumlah_anggota', (string) ($edit['jumlah_anggota'] ?? 1))) ?>" required>
      </label>
      <label>No HP
        <input type="text" name="no_hp" value="<?= rw_esc(old('no_hp', $edit['no_hp'] ?? '')) ?>" placeholder="08xx">
      </label>
      <label>Pekerjaan Kepala Keluarga
        <input type="text" name="pekerjaan_kepala_keluarga" value="<?= rw_esc(old('pekerjaan_kepala_keluarga', $edit['pekerjaan_kepala_keluarga'] ?? '')) ?>" placeholder="Contoh: Buruh harian, pedagang, karyawan">
      </label>
      <label>Status Tinggal
        <select name="status_tinggal" required>
          <?php foreach ($statusOptions as $value => $label): ?>
            <option value="<?= rw_esc($value) ?>" <?= is_selected(old('status_tinggal', $edit['status_tinggal'] ?? 'tetap'), $value) ?>><?= rw_esc($label) ?></option>
          <?php endforeach; ?>
        </select>
      </label>
      <label>Kategori Kesejahteraan
        <select name="kategori_kesejahteraan" required>
          <?php foreach ($kesejahteraanOptions as $value => $label): ?>
            <option value="<?= rw_esc($value) ?>" <?= is_selected(old('kategori_kesejahteraan', $edit['kategori_kesejahteraan'] ?? 'umum'), $value) ?>><?= rw_esc($label) ?></option>
          <?php endforeach; ?>
        </select>
      </label>
      <label>Penerima Bantuan
        <select name="penerima_bantuan" required>
          <?php foreach ($bantuanOptions as $value => $label): ?>
            <option value="<?= rw_esc($value) ?>" <?= is_selected(old('penerima_bantuan', $edit['penerima_bantuan'] ?? 'tidak'), $value) ?>><?= rw_esc($label) ?></option>
          <?php endforeach; ?>
        </select>
      </label>
      <label class="full">Alamat
        <input type="text" name="alamat" value="<?= rw_esc(old('alamat', $edit['alamat'] ?? '')) ?>" placeholder="Alamat singkat keluarga">
      </label>
      <label class="full">Jenis Bantuan
        <input type="text" name="jenis_bantuan" value="<?= rw_esc(old('jenis_bantuan', $edit['jenis_bantuan'] ?? '')) ?>" placeholder="Contoh: PKH, BPNT, bantuan pendidikan, sembako">
        <span class="field-note">Wajib diisi jika keluarga penerima bantuan.</span>
      </label>
      <label class="full">Kondisi Khusus
        <textarea name="kondisi_khusus" rows="3" placeholder="Contoh: Ada lansia, disabilitas, anak sekolah, orang sakit menahun"><?= rw_esc(old('kondisi_khusus', $edit['kondisi_khusus'] ?? '')) ?></textarea>
      </label>
      <label class="full">Keterangan Tambahan
        <textarea name="keterangan" rows="4" placeholder="Catatan lingkungan, kebutuhan monitoring, atau informasi non-sensitif lain"><?= rw_esc(old('keterangan', $edit['keterangan'] ?? '')) ?></textarea>
      </label>

      <div class="full form-actions modal-actions">
        <button type="submit"><?= ! empty($edit) ? 'Update Data' : 'Simpan Data' ?></button>
        <button type="button" class="btn-light" data-close-modal>Batal</button>
        <?php if (! empty($edit)): ?>
          <a class="btn-light" href="<?= rw_esc($wargaUrl()) ?>">Keluar Edit</a>
        <?php endif; ?>
      </div>
    </form>
  </div>
</div>

<div class="admin-modal" id="wargaDownloadModal" aria-hidden="true">
  <div class="admin-modal-backdrop" data-close-modal></div>
  <div class="admin-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="wargaDownloadTitle">
    <div class="admin-modal-header">
      <div>
        <p class="admin-kicker">Laporan warga</p>
        <h2 id="wargaDownloadTitle">Preview dan Cetak Data</h2>
      </div>
      <button type="button" class="modal-close" data-close-modal aria-label="Tutup popup">Tutup</button>
    </div>

    <div class="download-choice-grid">
      <article class="download-choice-card">
        <span class="info-label">Berdasarkan filter</span>
        <strong id="downloadFilterTitle">Data warga <?= rw_esc($activeRtLabel) ?></strong>
        <p class="muted" id="downloadFilterText">Mengikuti pilihan filter yang sedang dipakai di form.</p>
        <ul class="filter-summary-list" id="downloadFilterList">
          <li>RT: <?= rw_esc($activeRtLabel) ?></li>
          <li>Status tinggal: <?= rw_esc(($filters['status_tinggal'] ?? '') !== '' ? warga_option_label($statusOptions, $filters['status_tinggal']) : 'Semua Status') ?></li>
          <li>Kesejahteraan: <?= rw_esc(($filters['kategori_kesejahteraan'] ?? '') !== '' ? warga_option_label($kesejahteraanOptions, $filters['kategori_kesejahteraan']) : 'Semua Kategori') ?></li>
          <li>Bantuan: <?= rw_esc(($filters['penerima_bantuan'] ?? '') !== '' ? warga_option_label($bantuanOptions, $filters['penerima_bantuan']) : 'Semua') ?></li>
        </ul>
        <div class="form-actions modal-actions">
          <a class="btn-light" href="<?= rw_esc($cetakUrl) ?>" id="downloadFilterPrint" target="_blank" rel="noopener noreferrer">Preview / Cetak</a>
        </div>
      </article>

      <article class="download-choice-card">
        <span class="info-label">Semua data warga</span>
        <strong>Semua RT RW 05</strong>
        <p class="muted">Mengambil seluruh data warga tanpa mengikuti pilihan filter.</p>
        <div class="form-actions modal-actions">
          <a class="btn-light" href="<?= rw_esc($allCetakUrl) ?>" target="_blank" rel="noopener noreferrer">Preview / Cetak</a>
        </div>
      </article>
    </div>
  </div>
</div>

<div class="admin-modal" id="wargaImportModal" aria-hidden="true">
  <div class="admin-modal-backdrop" data-close-modal></div>
  <div class="admin-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="wargaImportTitle">
    <div class="admin-modal-header">
      <div>
        <p class="admin-kicker">Import data</p>
        <h2 id="wargaImportTitle">Import CSV Warga</h2>
      </div>
      <button type="button" class="modal-close" data-close-modal aria-label="Tutup popup">Tutup</button>
    </div>

    <form method="post" action="<?= site_url('admin/import') ?>" enctype="multipart/form-data" class="grid-form">
      <input type="hidden" name="dataset" value="warga">
      <input type="hidden" name="return_to" value="warga">

      <label>Mode Import
        <select name="mode" required>
          <option value="append">Tambah data baru</option>
          <option value="replace">Hapus semua data warga lalu import</option>
        </select>
        <span class="field-note">Mode hapus semua dipakai hanya kalau ingin mengganti seluruh data warga.</span>
      </label>

      <label>File CSV
        <input type="file" name="csv_file" accept=".csv,text/csv" required>
      </label>

      <div class="full helper-note helper-note-tight">
        <strong>Format CSV data warga</strong>
        <ul class="helper-list compact">
          <li>Kolom: <code><?= rw_esc(implode(', ', warga_csv_columns())) ?></code></li>
          <?php foreach (warga_csv_notes() as $note): ?>
            <li><?= rw_esc($note) ?></li>
          <?php endforeach; ?>
        </ul>
      </div>

      <div class="full form-actions modal-actions">
        <button type="submit">Import Data</button>
        <a class="btn-light" href="<?= rw_esc($templateUrl) ?>">Download Template</a>
        <button type="button" class="btn-light" data-close-modal>Batal</button>
      </div>
    </form>
  </div>
</div>

<div class="admin-modal" id="wargaDeleteModal" aria-hidden="true">
  <div class="admin-modal-backdrop" data-close-modal></div>
  <div class="admin-modal-dialog admin-modal-small" role="dialog" aria-modal="true" aria-labelledby="wargaDeleteTitle">
    <div class="admin-modal-header">
      <div>
        <p class="admin-kicker">Konfirmasi</p>
        <h2 id="wargaDeleteTitle">Hapus Data Warga?</h2>
      </div>
      <button type="button" class="modal-close" data-close-modal aria-label="Tutup popup">Tutup</button>
    </div>
    <p class="muted">Data <strong id="deleteWargaName">warga ini</strong> akan dihapus dari daftar warga. Aksi ini tidak bisa dibatalkan dari halaman ini.</p>
    <div class="form-actions modal-actions">
      <a href="#" class="btn-danger" id="confirmDeleteWarga">Ya, Hapus</a>
      <button type="button" class="btn-light" data-close-modal>Batal</button>
    </div>
  </div>
</div>

<script>
(function () {
  const body = document.body;
  const modals = Array.from(document.querySelectorAll('.admin-modal'));
  const deleteName = document.getElementById('deleteWargaName');
  const deleteConfirm = document.getElementById('confirmDeleteWarga');
  const wargaBaseUrl = <?= json_encode($baseWargaUrl, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
  const filterForm = document.getElementById('wargaFilterForm');
  const downloadFilterTitle = document.getElementById('downloadFilterTitle');
  const downloadFilterText = document.getElementById('downloadFilterText');
  const downloadFilterList = document.getElementById('downloadFilterList');
  const downloadFilterPrint = document.getElementById('downloadFilterPrint');

  function selectedLabel(name, fallback) {
    if (!filterForm) {
      return fallback;
    }

    const field = filterForm.querySelector(`[name="${name}"]`);
    if (!field) {
      return fallback;
    }

    const option = field.options ? field.options[field.selectedIndex] : null;
    return option ? option.text.trim() : fallback;
  }

  function selectedValue(name) {
    if (!filterForm) {
      return '';
    }

    const field = filterForm.querySelector(`[name="${name}"]`);
    return field ? field.value.trim() : '';
  }

  function filteredReportUrl(exportType) {
    const params = new URLSearchParams();
    if (filterForm) {
      new FormData(filterForm).forEach((value, key) => {
        value = String(value).trim();
        if (value !== '') {
          params.set(key, value);
        }
      });
    }
    params.set('export', exportType);

    return `${wargaBaseUrl}?${params.toString()}`;
  }

  function syncDownloadFilterCard() {
    const rtValue = selectedValue('rt');
    const statusValue = selectedValue('status_tinggal');
    const welfareValue = selectedValue('kategori_kesejahteraan');
    const aidValue = selectedValue('penerima_bantuan');
    const rtLabel = selectedLabel('rt', 'Semua RT');
    const statusLabel = selectedLabel('status_tinggal', 'Semua Status');
    const welfareLabel = selectedLabel('kategori_kesejahteraan', 'Semua Kategori');
    const aidLabel = selectedLabel('penerima_bantuan', 'Semua');
    const hasSpecificFilter = Boolean(rtValue || statusValue || welfareValue || aidValue);

    if (downloadFilterTitle) {
      downloadFilterTitle.textContent = rtValue ? `Data warga ${rtLabel}` : 'Data warga sesuai filter';
    }
    if (downloadFilterText) {
      downloadFilterText.textContent = hasSpecificFilter
        ? 'Mengambil data sesuai pilihan filter di sebelah kiri.'
        : 'Belum ada filter khusus. Hasilnya sama dengan semua data warga.';
    }
    if (downloadFilterList) {
      downloadFilterList.innerHTML = [
        `RT: ${rtLabel}`,
        `Status tinggal: ${statusLabel}`,
        `Kesejahteraan: ${welfareLabel}`,
        `Bantuan: ${aidLabel}`,
      ].map((item) => `<li>${item}</li>`).join('');
    }
    if (downloadFilterPrint) {
      downloadFilterPrint.href = filteredReportUrl('cetak');
    }
  }

  function openModal(id) {
    const modal = document.getElementById(id);
    if (!modal) {
      return;
    }

    if (id === 'wargaDownloadModal') {
      syncDownloadFilterCard();
    }
    modal.classList.add('is-open');
    modal.setAttribute('aria-hidden', 'false');
    body.classList.add('modal-open');
    const focusable = modal.querySelector('input, select, textarea, button, a');
    if (focusable) {
      window.setTimeout(() => focusable.focus(), 40);
    }
  }

  function closeModal(modal) {
    modal.classList.remove('is-open');
    modal.setAttribute('aria-hidden', 'true');
    if (!document.querySelector('.admin-modal.is-open')) {
      body.classList.remove('modal-open');
    }
  }

  document.querySelectorAll('[data-open-modal]').forEach((trigger) => {
    trigger.addEventListener('click', () => openModal(trigger.getAttribute('data-open-modal')));
  });

  document.querySelectorAll('[data-close-modal]').forEach((trigger) => {
    trigger.addEventListener('click', () => {
      const modal = trigger.closest('.admin-modal');
      if (modal) {
        closeModal(modal);
      }
    });
  });

  document.querySelectorAll('[data-delete-url]').forEach((trigger) => {
    trigger.addEventListener('click', () => {
      if (deleteName) {
        deleteName.textContent = trigger.getAttribute('data-delete-name') || 'warga ini';
      }
      if (deleteConfirm) {
        deleteConfirm.setAttribute('href', trigger.getAttribute('data-delete-url') || '#');
      }
      openModal('wargaDeleteModal');
    });
  });

  document.addEventListener('keydown', (event) => {
    if (event.key !== 'Escape') {
      return;
    }
    modals.filter((modal) => modal.classList.contains('is-open')).forEach(closeModal);
  });

  if (document.querySelector('.admin-modal.is-open')) {
    body.classList.add('modal-open');
  }
})();
</script>
<?= $this->endSection() ?>
