<?= $this->extend('layouts/kesehatan_admin') ?>

<?= $this->section('content') ?>
<?php
$isEditing = ! empty($edit);
$selectedParticipant = $selectedParticipant ?? null;
$selectedJenis = old('jenis', $edit['jenis'] ?? 'posyandu');
$selectedLifecycle = old('kelompok_siklus', $edit['kelompok_siklus'] ?? 'bayi_balita');
$selectedGender = old('jenis_kelamin', $edit['jenis_kelamin'] ?? '');
$selectedStatus = old('status', $edit['status'] ?? 'aktif');
$selectedVisitType = old('jenis_layanan', $selectedParticipant['jenis'] ?? 'posyandu');
$posbinduOld = old('posbindu');
$posbinduOld = is_array($posbinduOld) ? $posbinduOld : [];
$posbinduYesNoOptions = kesehatan_posbindu_yes_no_options();
?>
<div class="section-heading">
  <div>
    <h1>Posyandu ILP & Posbindu PTM</h1>
    <p class="muted">Catat layanan berdasarkan siklus hidup, hasil pengukuran, edukasi, dan tindak lanjut. Modul RW ini bukan pengganti Buku KIA, ASIK, atau rekam medis Puskesmas.</p>
  </div>
  <div class="form-actions">
    <a href="<?= site_url('admin/kesehatan-dashboard') ?>">Dashboard Kesehatan</a>
    <a href="<?= site_url('admin/posbindu-laporan') ?>">Buka Alur Laporan Posbindu</a>
    <a href="<?= site_url('admin/kesehatan-tindak-lanjut') ?>">Tindak Lanjut & Rujukan</a>
    <a href="<?= site_url('kesehatan') ?>" target="_blank" rel="noopener noreferrer">Lihat Halaman Warga</a>
  </div>
</div>

<?php if ($success !== ''): ?><div class="alert success"><?= rw_esc($success) ?></div><?php endif; ?>
<?php if ($error !== ''): ?><div class="alert error"><?= rw_esc($error) ?></div><?php endif; ?>

<div class="stat-grid health-stat-grid">
  <article class="stat"><span>Peserta Aktif</span><strong><?= rw_esc((string) ($healthStats['active'] ?? 0)) ?></strong><small>Semua kelompok siklus hidup</small></article>
  <article class="stat"><span>Kunjungan Bulan Ini</span><strong><?= rw_esc((string) ($healthStats['monthVisits'] ?? 0)) ?></strong><small>Peserta yang tercatat hadir</small></article>
  <article class="stat"><span>Perlu Ditindaklanjuti</span><strong><?= rw_esc((string) ($healthStats['followups'] ?? 0)) ?></strong><small>Pantau atau kunjungan rumah</small></article>
  <article class="stat"><span>Rujukan</span><strong><?= rw_esc((string) ($healthStats['referrals'] ?? 0)) ?></strong><small>Perlu konsultasi Puskesmas</small></article>
</div>

<section class="panel">
  <div class="section-heading">
    <div>
      <h2>Kegiatan Hari Ini</h2>
      <p class="muted">Pilih layanan dan tanggal. Peserta aktif akan muncul otomatis untuk dicentang hadir.</p>
    </div>
    <div class="form-actions">
      <?php if ($activityJenis === 'posbindu'): ?><a href="<?= site_url('admin/posbindu-laporan?tanggal=' . rawurlencode($activityDate)) ?>">Kelola Laporan Posbindu</a><?php endif; ?>
      <a href="<?= site_url('admin/kesehatan-data?print=1&jenis_kegiatan=' . rawurlencode($activityJenis) . '&tanggal_kegiatan=' . rawurlencode($activityDate)) ?>" target="_blank" rel="noopener noreferrer">Preview / Cetak</a>
    </div>
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
    <?= csrf_field() ?>
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
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="save_participant">
      <input type="hidden" name="id" value="<?= rw_esc((string) ($edit['id'] ?? '')) ?>">
      <label>Layanan Utama
        <select name="jenis" id="healthParticipantType" required>
          <?php foreach ($participantTypeOptions as $value => $label): ?>
            <option value="<?= rw_esc($value) ?>" <?= is_selected($selectedJenis, $value) ?>><?= rw_esc($label) ?></option>
          <?php endforeach; ?>
        </select>
      </label>
      <label>Kelompok Siklus Hidup
        <select name="kelompok_siklus" required>
          <?php foreach ($lifecycleOptions as $value => $label): ?>
            <option value="<?= rw_esc($value) ?>" <?= is_selected($selectedLifecycle, $value) ?>><?= rw_esc($label) ?></option>
          <?php endforeach; ?>
        </select>
      </label>
      <label>Nama Peserta
        <input type="text" name="nama" maxlength="160" value="<?= rw_esc(old('nama', $edit['nama'] ?? '')) ?>" required>
      </label>
      <label data-participant-service="posbindu">NIK
        <input type="text" name="nik" inputmode="numeric" maxlength="32" value="<?= rw_esc(old('nik', $edit['nik'] ?? '')) ?>" placeholder="Sesuai dokumen sumber">
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
      <label data-participant-service="posbindu">Provinsi Asal
        <input type="text" name="provinsi" maxlength="100" value="<?= rw_esc(old('provinsi', $edit['provinsi'] ?? '')) ?>" placeholder="Contoh: Jawa Barat">
      </label>
      <label data-participant-service="posbindu">Kota/Kabupaten Asal
        <input type="text" name="kota_kabupaten" maxlength="120" value="<?= rw_esc(old('kota_kabupaten', $edit['kota_kabupaten'] ?? '')) ?>" placeholder="Contoh: Kab. Bandung">
      </label>
      <label data-participant-service="posbindu">Status Pendidikan
        <input type="text" name="pendidikan" maxlength="80" value="<?= rw_esc(old('pendidikan', $edit['pendidikan'] ?? '')) ?>">
      </label>
      <label data-participant-service="posbindu">Pekerjaan
        <input type="text" name="pekerjaan" maxlength="120" value="<?= rw_esc(old('pekerjaan', $edit['pekerjaan'] ?? '')) ?>">
      </label>
      <label data-participant-service="posbindu">Status Perkawinan
        <input type="text" name="status_perkawinan" maxlength="80" value="<?= rw_esc(old('status_perkawinan', $edit['status_perkawinan'] ?? '')) ?>">
      </label>
      <label data-participant-service="posbindu">Golongan Darah
        <input type="text" name="golongan_darah" maxlength="10" value="<?= rw_esc(old('golongan_darah', $edit['golongan_darah'] ?? '')) ?>">
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
      <label class="full health-consent-check">
        <input type="checkbox" name="persetujuan_data" value="1" <?= ! empty(old('persetujuan_data', $edit['persetujuan_data'] ?? 0)) ? 'checked' : '' ?> <?= $isEditing ? '' : 'required' ?>>
        Peserta atau wali telah diberi tahu bahwa data minimum ini digunakan untuk kegiatan dan tindak lanjut kesehatan RW.
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
    <label>Kelompok Siklus Hidup
      <select name="kelompok_siklus">
        <option value="">Semua kelompok</option>
        <?php foreach ($lifecycleOptions as $value => $label): ?><option value="<?= rw_esc($value) ?>" <?= is_selected($filterLifecycle, $value) ?>><?= rw_esc($label) ?></option><?php endforeach; ?>
      </select>
    </label>
    <label>Cari Nama / Wali
      <input type="search" name="q" value="<?= rw_esc($filterSearch) ?>" placeholder="Ketik nama">
    </label>
    <div class="form-actions"><button type="submit">Filter</button><a class="btn-light" href="<?= site_url('admin/kesehatan-data') ?>">Reset</a></div>
  </form>
  <div class="table-scroll">
    <table>
      <thead><tr><th>Layanan</th><th>Peserta</th><th>Siklus Hidup</th><th>Kontak Lingkungan</th><th>Status</th><th>Aksi</th></tr></thead>
      <tbody>
        <?php foreach ($participants as $participant): ?>
          <tr>
            <td><?= rw_esc($participantTypeOptions[$participant['jenis'] ?? ''] ?? ucfirst((string) ($participant['jenis'] ?? '-'))) ?></td>
            <td><strong><?= rw_esc($participant['nama'] ?? '') ?></strong><br><small><?= ! empty($participant['nik']) ? 'NIK ' . rw_esc($participant['nik']) . ' · ' : '' ?><?= rw_esc($participant['tanggal_lahir'] ?? '-') ?><?= ! empty($participant['nama_wali']) ? ' · Wali: ' . rw_esc($participant['nama_wali']) : '' ?></small></td>
            <td><?= rw_esc($lifecycleOptions[$participant['kelompok_siklus'] ?? ''] ?? 'Belum ditentukan') ?></td>
            <td>RT <?= rw_esc($participant['rt'] ?? '-') ?><br><small><?= rw_esc($participant['no_hp'] ?? '') ?></small></td>
            <td><?= rw_esc($statusOptions[$participant['status'] ?? ''] ?? ucfirst((string) ($participant['status'] ?? '-'))) ?></td>
            <td><div class="table-actions"><a href="<?= site_url('admin/kesehatan-data?peserta_id=' . (int) $participant['id']) ?>">Kunjungan</a><a href="<?= site_url('admin/kesehatan-data?action=edit&id=' . (int) $participant['id']) ?>">Edit</a><form method="post" action="<?= site_url('admin/kesehatan-data') ?>" onsubmit="return confirm('Hapus peserta dan seluruh catatan kunjungannya?')"><?= csrf_field() ?><input type="hidden" name="action" value="delete_participant"><input type="hidden" name="id" value="<?= (int) $participant['id'] ?>"><button type="submit" class="btn-link-danger">Hapus</button></form></div></td>
          </tr>
        <?php endforeach; ?>
        <?php if (empty($participants)): ?><tr><td colspan="6" class="table-empty">Belum ada peserta sesuai filter.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</section>

<section class="panel">
  <div class="section-heading">
    <div><h2>Catat Kunjungan Lima Langkah</h2><p class="muted">Catat yang benar-benar dilakukan. Aplikasi tidak memberikan diagnosis otomatis; hasil yang perlu perhatian harus diverifikasi tenaga kesehatan.</p></div>
    <?php if ($selectedParticipant): ?><strong><?= rw_esc($selectedParticipant['nama']) ?></strong><?php endif; ?>
  </div>
  <form method="post" action="<?= site_url('admin/kesehatan-data') ?>" class="grid-form">
    <?= csrf_field() ?>
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
    <label>Jenis Layanan Kunjungan
      <select name="jenis_layanan" id="healthVisitType" required>
        <?php foreach ($participantTypeOptions as $value => $label): ?><option value="<?= rw_esc($value) ?>" <?= is_selected($selectedVisitType, $value) ?>><?= rw_esc($label) ?></option><?php endforeach; ?>
      </select>
    </label>
    <div class="full health-form-section"><strong>2. Penimbangan dan pengukuran dasar</strong><p class="muted">Isi hanya pengukuran yang benar-benar dilakukan dengan alat yang tersedia.</p></div>
    <label>Berat (kg)<input type="number" name="berat_kg" min="0" max="300" step="0.01" value="<?= rw_esc(old('berat_kg')) ?>"></label>
    <label>Tinggi / Panjang Badan (cm)<input type="number" name="tinggi_cm" min="0" max="250" step="0.01" value="<?= rw_esc(old('tinggi_cm')) ?>"></label>
    <label data-health-service="posyandu">Lingkar Kepala (cm)<input type="number" name="lingkar_kepala_cm" min="0" max="100" step="0.01" value="<?= rw_esc(old('lingkar_kepala_cm')) ?>"></label>
    <label data-health-service="posyandu">Lingkar Lengan / LILA (cm)<input type="number" name="lingkar_lengan_cm" min="0" max="100" step="0.01" value="<?= rw_esc(old('lingkar_lengan_cm')) ?>"></label>
    <label data-health-service="posyandu">Usia Kehamilan (minggu)<input type="number" name="usia_kehamilan_minggu" min="0" max="45" value="<?= rw_esc(old('usia_kehamilan_minggu')) ?>"></label>
    <label data-health-service="posbindu">Lingkar Perut (cm)<input type="number" name="lingkar_perut_cm" min="0" max="250" step="0.01" value="<?= rw_esc(old('lingkar_perut_cm')) ?>"></label>
    <div class="full health-form-section" data-health-service="posbindu"><strong>3. Skrining faktor risiko PTM</strong><p class="muted">Pertanyaan dan pemeriksaan ini untuk skrining, bukan penetapan diagnosis.</p></div>
    <label data-health-service="posbindu">Tekanan Sistolik<input type="number" name="tekanan_sistolik" min="0" max="300" value="<?= rw_esc(old('tekanan_sistolik')) ?>"></label>
    <label data-health-service="posbindu">Tekanan Diastolik<input type="number" name="tekanan_diastolik" min="0" max="300" value="<?= rw_esc(old('tekanan_diastolik')) ?>"></label>
    <label data-health-service="posbindu">Gula Darah (mg/dL)<input type="number" name="gula_darah" min="0" max="1000" step="0.01" value="<?= rw_esc(old('gula_darah')) ?>"></label>
    <label data-health-service="posbindu">Konteks Gula Darah<select name="jenis_gula_darah"><option value="">Pilih bila diperiksa</option><?php foreach ($glucoseContextOptions as $value => $label): ?><option value="<?= rw_esc($value) ?>" <?= is_selected(old('jenis_gula_darah'), $value) ?>><?= rw_esc($label) ?></option><?php endforeach; ?></select></label>
    <label data-health-service="posbindu">Kebiasaan Merokok<select name="faktor_merokok"><option value="">Tidak ditanyakan</option><option value="tidak">Tidak</option><option value="ya">Ya</option><option value="berhenti">Sudah berhenti</option></select></label>
    <label data-health-service="posbindu">Aktivitas Fisik<select name="aktivitas_fisik"><option value="">Tidak ditanyakan</option><option value="cukup">Cukup</option><option value="kurang">Kurang</option></select></label>
    <label data-health-service="posbindu">Konsumsi Buah & Sayur<select name="konsumsi_buah_sayur"><option value="">Tidak ditanyakan</option><option value="cukup">Cukup</option><option value="kurang">Kurang</option></select></label>
    <details class="full health-form-section" data-health-service="posbindu" open>
      <summary><strong>Riwayat PTM dan faktor risiko laporan Puskesmas</strong></summary>
      <div class="grid-form health-nested-grid">
        <?php for ($i = 1; $i <= 3; $i++): ?>
          <label>Riwayat PTM Keluarga <?= $i ?><input type="text" name="posbindu[riwayat_keluarga_<?= $i ?>]" maxlength="160" value="<?= rw_esc($posbinduOld['riwayat_keluarga_' . $i] ?? '') ?>"></label>
        <?php endfor; ?>
        <?php for ($i = 1; $i <= 3; $i++): ?>
          <label>Riwayat PTM Diri <?= $i ?><input type="text" name="posbindu[riwayat_diri_<?= $i ?>]" maxlength="160" value="<?= rw_esc($posbinduOld['riwayat_diri_' . $i] ?? '') ?>"></label>
        <?php endfor; ?>
        <?php foreach (['gula_berlebihan' => 'Gula Berlebihan', 'garam_berlebihan' => 'Garam Berlebihan', 'lemak_berlebihan' => 'Lemak Berlebihan', 'konsumsi_alkohol' => 'Konsumsi Alkohol'] as $field => $label): ?>
          <label><?= rw_esc($label) ?><select name="posbindu[<?= rw_esc($field) ?>]"><option value="">Tidak ditanyakan</option><?php foreach ($posbinduYesNoOptions as $value => $optionLabel): ?><option value="<?= rw_esc($value) ?>" <?= is_selected($posbinduOld[$field] ?? '', $value) ?>><?= rw_esc($optionLabel) ?></option><?php endforeach; ?></select></label>
        <?php endforeach; ?>
      </div>
    </details>
    <details class="full health-form-section" data-health-service="posbindu">
      <summary><strong>Diagnosis, terapi, dan edukasi</strong></summary>
      <div class="grid-form health-nested-grid">
        <?php for ($i = 1; $i <= 3; $i++): ?>
          <label>Diagnosis <?= $i ?><input type="text" name="posbindu[diagnosis_<?= $i ?>]" maxlength="160" value="<?= rw_esc($posbinduOld['diagnosis_' . $i] ?? '') ?>"></label>
        <?php endfor; ?>
        <label>Rujuk RS<select name="posbindu[rujuk_rs]"><option value="">Belum ditentukan</option><?php foreach ($posbinduYesNoOptions as $value => $optionLabel): ?><option value="<?= rw_esc($value) ?>" <?= is_selected($posbinduOld['rujuk_rs'] ?? '', $value) ?>><?= rw_esc($optionLabel) ?></option><?php endforeach; ?></select></label>
        <label class="full">Terapi Farmakologi<textarea name="posbindu[terapi_farmakologi]" rows="2" maxlength="2000"><?= rw_esc($posbinduOld['terapi_farmakologi'] ?? '') ?></textarea></label>
        <label class="full">Konseling, Informasi, dan Edukasi Kesehatan<textarea name="posbindu[kie_kesehatan]" rows="2" maxlength="2000"><?= rw_esc($posbinduOld['kie_kesehatan'] ?? '') ?></textarea></label>
      </div>
    </details>
    <details class="full health-form-section" data-health-service="posbindu">
      <summary><strong>Pemeriksaan gangguan indera</strong></summary>
      <div class="grid-form health-nested-grid">
        <?php foreach ([
          'katarak' => ['Katarak', 'Mata'],
          'refraksi' => ['Kelainan Refraksi', 'Mata'],
          'tuli' => ['Curiga Tuli Kongenital', 'Telinga'],
          'omsk' => ['OMSK / Congek', 'Telinga'],
          'serumen' => ['Serumen', 'Telinga'],
        ] as $prefix => [$label, $organ]): ?>
          <div class="full health-form-subheading"><strong><?= rw_esc($label) ?></strong></div>
          <label><?= rw_esc($organ) ?> Kanan<input type="text" name="posbindu[<?= rw_esc($prefix) ?>_<?= $organ === 'Mata' ? 'mata' : 'telinga' ?>_kanan]" maxlength="80" value="<?= rw_esc($posbinduOld[$prefix . '_' . ($organ === 'Mata' ? 'mata' : 'telinga') . '_kanan'] ?? '') ?>"></label>
          <label><?= rw_esc($organ) ?> Kiri<input type="text" name="posbindu[<?= rw_esc($prefix) ?>_<?= $organ === 'Mata' ? 'mata' : 'telinga' ?>_kiri]" maxlength="80" value="<?= rw_esc($posbinduOld[$prefix . '_' . ($organ === 'Mata' ? 'mata' : 'telinga') . '_kiri'] ?? '') ?>"></label>
          <label>Rujuk RS<select name="posbindu[<?= rw_esc($prefix) ?>_rujuk_rs]"><option value="">Belum ditentukan</option><?php foreach ($posbinduYesNoOptions as $value => $optionLabel): ?><option value="<?= rw_esc($value) ?>" <?= is_selected($posbinduOld[$prefix . '_rujuk_rs'] ?? '', $value) ?>><?= rw_esc($optionLabel) ?></option><?php endforeach; ?></select></label>
        <?php endforeach; ?>
      </div>
    </details>
    <details class="full health-form-section" data-health-service="posbindu">
      <summary><strong>Pemeriksaan IVA, SADANIS, dan Form UBM</strong></summary>
      <div class="grid-form health-nested-grid">
        <label>Hasil IVA<input type="text" name="posbindu[hasil_iva]" maxlength="160" value="<?= rw_esc($posbinduOld['hasil_iva'] ?? '') ?>"></label>
        <label>Tindak Lanjut IVA Positif<input type="text" name="posbindu[tindak_lanjut_iva]" maxlength="255" value="<?= rw_esc($posbinduOld['tindak_lanjut_iva'] ?? '') ?>"></label>
        <label>Hasil SADANIS<input type="text" name="posbindu[hasil_sadanis]" maxlength="160" value="<?= rw_esc($posbinduOld['hasil_sadanis'] ?? '') ?>"></label>
        <label>Tindak Lanjut SADANIS<input type="text" name="posbindu[tindak_lanjut_sadanis]" maxlength="255" value="<?= rw_esc($posbinduOld['tindak_lanjut_sadanis'] ?? '') ?>"></label>
        <label>Konseling UBM<input type="text" name="posbindu[ubm_konseling]" maxlength="255" value="<?= rw_esc($posbinduOld['ubm_konseling'] ?? '') ?>"></label>
        <label>CAR<input type="text" name="posbindu[ubm_car]" maxlength="160" value="<?= rw_esc($posbinduOld['ubm_car'] ?? '') ?>"></label>
        <label>Rujuk UBM<input type="text" name="posbindu[ubm_rujuk]" maxlength="160" value="<?= rw_esc($posbinduOld['ubm_rujuk'] ?? '') ?>"></label>
        <label>Kondisi<input type="text" name="posbindu[ubm_kondisi]" maxlength="160" value="<?= rw_esc($posbinduOld['ubm_kondisi'] ?? '') ?>"></label>
      </div>
    </details>
    <div class="full health-form-section"><strong>4–5. Pelayanan, edukasi, validasi, dan tindak lanjut</strong></div>
    <label class="full">Layanan yang Diberikan<textarea name="layanan_diberikan" rows="2" maxlength="2000" placeholder="Contoh: penimbangan, pemeriksaan tekanan darah, PMT, atau pelayanan oleh nakes."><?= rw_esc(old('layanan_diberikan')) ?></textarea></label>
    <label class="full">Edukasi / Konseling<textarea name="edukasi" rows="2" maxlength="2000" placeholder="Tuliskan edukasi yang benar-benar diberikan."><?= rw_esc(old('edukasi')) ?></textarea></label>
    <label>Tindak Lanjut<select name="tindak_lanjut" id="healthFollowup"><?php foreach ($followupOptions as $value => $label): ?><option value="<?= rw_esc($value) ?>" <?= is_selected(old('tindak_lanjut', 'selesai'), $value) ?>><?= rw_esc($label) ?></option><?php endforeach; ?></select></label>
    <label>Jadwal Tindak Lanjut<input type="date" name="tanggal_tindak_lanjut" value="<?= rw_esc(old('tanggal_tindak_lanjut')) ?>"></label>
    <label class="full" data-referral-field>Tujuan Rujukan / Konsultasi<input type="text" name="tujuan_rujukan" maxlength="160" value="<?= rw_esc(old('tujuan_rujukan')) ?>" placeholder="Contoh: Puskesmas Dayeuhkolot"></label>
    <?php if ($canValidateKesehatan ?? true): ?>
      <label>Status Validasi
        <select name="status_validasi">
          <option value="dicatat" <?= is_selected(old('status_validasi', 'dicatat'), 'dicatat') ?>>Dicatat kader</option>
          <option value="divalidasi" <?= is_selected(old('status_validasi', 'dicatat'), 'divalidasi') ?>>Divalidasi nakes/admin</option>
        </select>
      </label>
    <?php else: ?>
      <input type="hidden" name="status_validasi" value="dicatat">
      <p class="full muted">Validasi hasil kunjungan hanya dapat dilakukan oleh nakes/admin, lihat menu <a href="<?= site_url('admin/kesehatan-tindak-lanjut') ?>">Tindak Lanjut &amp; Rujukan</a>.</p>
    <?php endif; ?>
    <label class="full">Catatan Kunjungan<textarea name="catatan_kunjungan" rows="3" maxlength="2000" placeholder="Catatan tindak lanjut non-diagnosis."><?= rw_esc(old('catatan_kunjungan')) ?></textarea></label>
    <div class="full form-actions"><button type="submit">Simpan Kunjungan</button></div>
  </form>
</section>

<section class="panel">
  <h2>Riwayat Kunjungan Terbaru</h2>
  <div class="table-scroll">
    <table>
      <thead><tr><th>Tanggal</th><th>Peserta</th><th>Layanan</th><th>Pengukuran</th><th>Tindak Lanjut</th><th>Validasi</th><th>Catatan</th></tr></thead>
      <tbody>
        <?php foreach ($visits as $visit): ?>
          <tr><td><?= rw_esc(date('d/m/Y', strtotime((string) $visit['tanggal']))) ?><br><small><?= rw_esc($visit['hadir']) ?></small></td><td><?= rw_esc($visit['nama']) ?><br><small><?= rw_esc($lifecycleOptions[$visit['kelompok_siklus'] ?? ''] ?? 'Kelompok belum ditentukan') ?></small></td><td><?= rw_esc($participantTypeOptions[$visit['jenis_layanan'] ?? $visit['jenis']] ?? ($visit['jenis_layanan'] ?? $visit['jenis'])) ?></td><td><?= $visit['berat_kg'] !== null ? 'BB ' . rw_esc($visit['berat_kg']) . ' kg · ' : '' ?><?= $visit['tinggi_cm'] !== null ? 'TB ' . rw_esc($visit['tinggi_cm']) . ' cm · ' : '' ?><?= $visit['lingkar_perut_cm'] !== null ? 'LP ' . rw_esc($visit['lingkar_perut_cm']) . ' cm · ' : '' ?><?= $visit['tekanan_sistolik'] !== null ? 'TD ' . rw_esc($visit['tekanan_sistolik']) . '/' . rw_esc($visit['tekanan_diastolik']) . ' · ' : '' ?><?= $visit['gula_darah'] !== null ? 'Gula ' . rw_esc($visit['gula_darah']) : '-' ?></td><td><?= rw_esc($followupOptions[$visit['tindak_lanjut'] ?? 'selesai'] ?? 'Belum ditentukan') ?><?= ! empty($visit['tanggal_tindak_lanjut']) ? '<br><small>' . rw_esc(format_date_id($visit['tanggal_tindak_lanjut'])) . '</small>' : '' ?></td><td><?= ($visit['status_validasi'] ?? 'dicatat') === 'divalidasi' ? 'Divalidasi' : 'Dicatat kader' ?></td><td><?= nl2br(rw_esc($visit['catatan'] ?? '-')) ?></td></tr>
        <?php endforeach; ?>
        <?php if (empty($visits)): ?><tr><td colspan="7" class="table-empty">Belum ada catatan kunjungan.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</section>
<script>
(() => {
  const visitType = document.getElementById('healthVisitType');
  const participantType = document.getElementById('healthParticipantType');
  const followup = document.getElementById('healthFollowup');
  const syncHealthFields = () => {
    const selected = visitType?.value || 'posyandu';
    document.querySelectorAll('[data-health-service]').forEach((field) => {
      field.hidden = field.dataset.healthService !== selected;
    });
    document.querySelectorAll('[data-referral-field]').forEach((field) => {
      field.hidden = followup?.value !== 'rujuk_puskesmas';
    });
    const participantSelected = participantType?.value || 'posyandu';
    document.querySelectorAll('[data-participant-service]').forEach((field) => {
      field.hidden = field.dataset.participantService !== participantSelected;
    });
  };
  visitType?.addEventListener('change', syncHealthFields);
  participantType?.addEventListener('change', syncHealthFields);
  followup?.addEventListener('change', syncHealthFields);
  syncHealthFields();
})();
</script>
<?= $this->endSection() ?>
