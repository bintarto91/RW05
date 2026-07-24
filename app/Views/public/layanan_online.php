<?= $this->extend('layouts/public') ?>

<?= $this->section('content') ?>
<?php
$selectedSuratType = old('jenis_surat', 'Surat Pengantar');
$suratGuidanceMap = surat_type_detail_guidance_map();
$suratFieldDefinitions = surat_type_field_definitions();
$oldSuratData = old('surat_data');
$oldSuratData = is_array($oldSuratData) ? $oldSuratData : [];
?>
<section class="page-hero">
  <div class="container page-hero-grid">
    <div data-reveal>
      <p class="eyebrow">Layanan online</p>
      <h1>Ajukan surat warga dari rumah.</h1>
      <p class="hero-text">Pilih jenis surat sesuai template RW 05, isi data yang diperlukan, lalu pengurus akan meninjau melalui dashboard.</p>
    </div>
    <div class="page-callout" data-reveal>
      <span>Alur aman</span>
      <strong>Submit, cek, setujui</strong>
      <p>Tanda tangan dan stempel tetap menunggu persetujuan admin RW agar tidak disalahgunakan.</p>
    </div>
  </div>
</section>

<section class="section white-section">
  <div class="container online-service-layout">
    <aside class="online-service-panel" data-reveal>
      <p class="eyebrow">Cara kerja</p>
      <h2>Pengajuan masuk dulu, baru diproses pengurus.</h2>
      <ol class="online-flow">
        <li><strong>Isi form</strong><span>Warga mengirim data dasar dan keperluan surat.</span></li>
        <li><strong>Admin cek</strong><span>Pengurus memverifikasi data, RT, dan kebutuhan surat.</span></li>
        <li><strong>Surat diproses</strong><span>Jika disetujui, surat bisa diterbitkan dengan nomor resmi.</span></li>
      </ol>
      <div class="online-warning">
        <strong>Catatan keamanan</strong>
        <p>Jangan tulis NIK, nomor KK lengkap, foto KTP, atau dokumen sensitif di form publik ini. Dokumen pendukung cukup disampaikan saat pengurus meminta.</p>
      </div>
      <form method="get" action="<?= site_url('layanan-online') ?>" class="online-check-form">
        <label>Kode pengajuan
          <input type="text" name="kode" placeholder="Contoh: RW05-20260702-ABC123" value="<?= rw_esc($lookupCode ?? '') ?>">
        </label>
        <label>Nama pemohon
          <input type="text" name="nama" placeholder="Minimal 3 huruf" value="<?= rw_esc($lookupName ?? '') ?>">
        </label>
        <label>RT
          <input type="text" name="rt" placeholder="Contoh: 01" value="<?= rw_esc($lookupRt ?? '') ?>">
        </label>
        <button type="submit" class="btn secondary full-button">Cek Status</button>
        <p class="form-note">Gunakan kode pengajuan untuk hasil paling tepat. Kalau lupa, cari memakai nama pemohon dan RT.</p>
      </form>
    </aside>

    <form method="post" action="<?= site_url('layanan-online') ?>" class="aspirasi-form online-request-form" data-reveal>
      <?php if (! empty($successCode)): ?>
        <div class="alert success">
          Pengajuan berhasil dikirim. Simpan kode ini untuk cek status: <strong><?= rw_esc($successCode) ?></strong><br>
          <a href="<?= site_url('layanan-online?kode=' . rawurlencode($successCode)) ?>">Cek status pengajuan ini</a>
        </div>
      <?php endif; ?>
      <?php if (! empty($error)): ?>
        <div class="alert error"><?= rw_esc($error) ?></div>
      <?php endif; ?>
      <?php if (! empty($lookupError)): ?>
        <div class="alert error"><?= rw_esc($lookupError) ?></div>
      <?php endif; ?>
      <?php if (! empty($lookupCode) && empty($lookupRow)): ?>
        <div class="alert error">Kode pengajuan <strong><?= rw_esc($lookupCode) ?></strong> tidak ditemukan.</div>
      <?php endif; ?>
      <?php if (! empty($lookupRow)): ?>
        <?php $canPrint = in_array($lookupRow['status'], ['disetujui', 'selesai'], true); ?>
        <?php $lookupStructuredEntries = surat_request_data_entries($lookupRow['jenis_surat'] ?? '', $lookupRow['detail_json'] ?? ''); ?>
        <div class="letter-check-result">
          <span class="letter-status-pill status-<?= rw_esc($lookupRow['status']) ?>"><?= rw_esc(surat_status_label($lookupRow['status'])) ?></span>
          <h2><?= rw_esc($lookupRow['jenis_surat']) ?></h2>
          <p><strong>Kode:</strong> <?= rw_esc($lookupRow['kode_pengajuan']) ?></p>
          <p><strong>Pemohon:</strong> <?= rw_esc($lookupRow['nama']) ?>, RT <?= rw_esc($lookupRow['rt']) ?></p>
          <?php foreach ($lookupStructuredEntries as $entry): ?>
            <p><strong><?= rw_esc($entry['label']) ?>:</strong> <?= nl2br(rw_esc($entry['value'])) ?></p>
          <?php endforeach; ?>
          <?php if (! empty($lookupRow['nomor_surat'])): ?>
            <p><strong>Nomor surat:</strong> <?= rw_esc($lookupRow['nomor_surat']) ?></p>
          <?php endif; ?>
          <?php if (! empty($lookupRow['catatan_admin'])): ?>
            <p><strong>Catatan admin:</strong> <?= nl2br(rw_esc($lookupRow['catatan_admin'])) ?></p>
          <?php endif; ?>
          <?php if ($canPrint): ?>
            <a href="<?= site_url('layanan-online/surat/' . rawurlencode($lookupRow['kode_pengajuan'])) ?>" class="btn primary full-button" target="_blank" rel="noopener noreferrer">Download PDF Surat</a>
          <?php else: ?>
            <p class="form-note">Tombol download akan muncul setelah admin menyetujui atau menyelesaikan pengajuan.</p>
          <?php endif; ?>
        </div>
      <?php endif; ?>
      <?php if (empty($lookupCode) && ! empty($lookupRows)): ?>
        <div class="letter-check-result">
          <span class="letter-status-pill status-diproses">Hasil pencarian</span>
          <h2><?= rw_esc(count($lookupRows)) ?> pengajuan ditemukan</h2>
          <div class="letter-result-list">
            <?php foreach ($lookupRows as $row): ?>
              <?php $canPrintRow = in_array($row['status'], ['disetujui', 'selesai'], true); ?>
              <article>
                <strong><?= rw_esc($row['jenis_surat']) ?></strong>
                <span><?= rw_esc($row['nama']) ?>, RT <?= rw_esc($row['rt']) ?> | <?= rw_esc($row['kode_pengajuan']) ?></span>
                <small>Status: <?= rw_esc(surat_status_label($row['status'])) ?> | <?= rw_esc(date('d/m/Y H:i', strtotime($row['created_at']))) ?></small>
                <a href="<?= site_url('layanan-online?kode=' . rawurlencode($row['kode_pengajuan'])) ?>">Lihat detail</a>
                <?php if ($canPrintRow): ?>
                  <a href="<?= site_url('layanan-online/surat/' . rawurlencode($row['kode_pengajuan'])) ?>" target="_blank" rel="noopener noreferrer">Download PDF</a>
                <?php endif; ?>
              </article>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endif; ?>
      <?php if (empty($lookupCode) && ($lookupName ?? '') !== '' && empty($lookupRows) && empty($lookupError)): ?>
        <div class="alert error">Pengajuan atas nama <strong><?= rw_esc($lookupName) ?></strong><?= ! empty($lookupRt) ? rw_esc(' RT ' . $lookupRt) : '' ?> belum ditemukan.</div>
      <?php endif; ?>
      <?php if (empty($tableReady)): ?>
        <div class="alert error">Tabel pengajuan surat belum siap. Hubungi admin RW untuk menyiapkan database.</div>
      <?php endif; ?>

      <div class="form-grid">
        <label class="full">Jenis Surat
          <select name="jenis_surat" required>
            <?php foreach ($suratTypes as $type): ?>
              <option value="<?= rw_esc($type) ?>" <?= is_selected(old('jenis_surat', 'Surat Pengantar'), $type) ?>><?= rw_esc($type) ?></option>
            <?php endforeach; ?>
          </select>
        </label>
        <div class="full message-box">
          <strong>Petunjuk isi detail sesuai jenis surat</strong>
          <p id="surat-guidance-text"><?= rw_esc(surat_type_detail_guidance($selectedSuratType)) ?></p>
        </div>
        <div id="surat-dynamic-fields" class="full form-grid">
          <?php foreach (surat_type_fields($selectedSuratType) as $field): ?>
            <?php
            $fieldKey = (string) ($field['key'] ?? '');
            $fieldValue = (string) ($oldSuratData[$fieldKey] ?? '');
            $fieldType = (string) ($field['type'] ?? 'text');
            $fieldRows = (int) ($field['rows'] ?? 3);
            $isFull = ! empty($field['full']);
            ?>
            <label class="<?= $isFull ? 'full' : '' ?>"><?= rw_esc($field['label'] ?? '') ?>
              <?php if ($fieldType === 'textarea'): ?>
                <textarea name="surat_data[<?= rw_esc($fieldKey) ?>]" rows="<?= rw_esc((string) $fieldRows) ?>" placeholder="<?= rw_esc($field['placeholder'] ?? '') ?>" <?= ! empty($field['required']) ? 'required' : '' ?>><?= rw_esc($fieldValue) ?></textarea>
              <?php else: ?>
                <input type="<?= rw_esc($fieldType) ?>" name="surat_data[<?= rw_esc($fieldKey) ?>]" value="<?= rw_esc($fieldValue) ?>" placeholder="<?= rw_esc($field['placeholder'] ?? '') ?>" <?= ! empty($field['required']) ? 'required' : '' ?>>
              <?php endif; ?>
            </label>
          <?php endforeach; ?>
        </div>
        <label>Nama Pemohon
          <input type="text" name="nama" placeholder="Nama lengkap" value="<?= field_value('nama') ?>" required>
        </label>
        <label>No WhatsApp
          <input type="text" name="no_hp" placeholder="08xx" value="<?= field_value('no_hp') ?>" required>
        </label>
        <label>RT
          <input type="text" name="rt" placeholder="Contoh: 01" value="<?= field_value('rt') ?>" required>
        </label>
        <label>Keperluan Surat
          <input type="text" name="keperluan" placeholder="Contoh: administrasi sekolah" value="<?= field_value('keperluan') ?>" required>
        </label>
        <label class="full">Alamat di RW 05
          <input type="text" name="alamat" placeholder="Alamat singkat pemohon" value="<?= field_value('alamat') ?>" required>
        </label>
        <label class="full">Detail Tambahan
          <textarea name="detail" rows="5" placeholder="Tulis penjelasan singkat bila ada hal khusus yang perlu diketahui pengurus."><?= field_value('detail') ?></textarea>
        </label>
        <label class="full">Catatan Lampiran
          <textarea name="lampiran_catatan" rows="3" placeholder="Contoh: KTP/KK siap ditunjukkan ke pengurus saat diminta."><?= field_value('lampiran_catatan') ?></textarea>
        </label>
      </div>

      <p class="form-note">Pengajuan akan masuk ke dashboard admin. Pengurus dapat mengubah status menjadi diproses, disetujui, ditolak, atau selesai.</p>
      <button type="submit" class="btn primary full-button" <?= empty($tableReady) ? 'disabled' : '' ?>>Kirim Pengajuan</button>
    </form>
  </div>
</section>

<section class="section">
  <div class="container">
    <div class="section-title" data-reveal>
      <p class="eyebrow">Template RW 05</p>
      <h2>Jenis surat mengikuti paket template resmi.</h2>
      <p>Pengantar, keterangan, undangan, edaran, permohonan, tugas/mandat, berita acara, dan keputusan RW akan memakai susunan kop serta isi sesuai template yang disiapkan pengurus.</p>
    </div>
  </div>
</section>

<script>
  (() => {
    const guidanceMap = <?= json_encode($suratGuidanceMap, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
    const fieldMap = <?= json_encode($suratFieldDefinitions, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
    const storedValues = <?= json_encode($oldSuratData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?> || {};
    const typeSelect = document.querySelector('select[name="jenis_surat"]');
    const guidanceText = document.getElementById('surat-guidance-text');
    const dynamicFields = document.getElementById('surat-dynamic-fields');

    if (!typeSelect || !guidanceText || !dynamicFields) {
      return;
    }

    const captureCurrentValues = () => {
      dynamicFields.querySelectorAll('[name^="surat_data["]').forEach((input) => {
        const match = input.name.match(/^surat_data\[(.+)\]$/);
        if (match) {
          storedValues[match[1]] = input.value;
        }
      });
    };

    const createField = (field) => {
      const label = document.createElement('label');
      if (field.full) {
        label.className = 'full';
      }
      label.append(document.createTextNode(field.label || 'Field'));

      const fieldName = `surat_data[${field.key}]`;
      const value = storedValues[field.key] || '';

      if (field.type === 'textarea') {
        const textarea = document.createElement('textarea');
        textarea.name = fieldName;
        textarea.rows = field.rows || 3;
        textarea.placeholder = field.placeholder || '';
        textarea.required = Boolean(field.required);
        textarea.value = value;
        label.append(textarea);
      } else {
        const input = document.createElement('input');
        input.type = field.type || 'text';
        input.name = fieldName;
        input.placeholder = field.placeholder || '';
        input.required = Boolean(field.required);
        input.value = value;
        label.append(input);
      }

      return label;
    };

    const renderGuidance = () => {
      guidanceText.textContent = guidanceMap[typeSelect.value] || 'Tulis keterangan tambahan yang benar-benar dibutuhkan agar pengurus bisa menyiapkan isi surat dengan tepat.';
    };

    const renderDynamicFields = () => {
      captureCurrentValues();
      dynamicFields.innerHTML = '';
      (fieldMap[typeSelect.value] || []).forEach((field) => {
        dynamicFields.append(createField(field));
      });
    };

    typeSelect.addEventListener('change', () => {
      renderGuidance();
      renderDynamicFields();
    });
    renderGuidance();
    renderDynamicFields();
  })();
</script>
<?= $this->endSection() ?>
