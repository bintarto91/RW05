<?= $this->extend('layouts/kesehatan_admin') ?>

<?= $this->section('content') ?>
<?php
$isEditing = ! empty($edit);
$selectedParticipant = $selectedParticipant ?? null;
$selectedVisit = $selectedVisit ?? null;
$workspaceService = isset($participantTypeOptions[$filterJenis]) ? $filterJenis : $activityJenis;
$selectedJenis = old('jenis', $edit['jenis'] ?? $workspaceService);
$selectedLifecycle = old('kelompok_siklus', $edit['kelompok_siklus'] ?? 'bayi_balita');
$selectedGender = old('jenis_kelamin', $edit['jenis_kelamin'] ?? '');
$selectedStatus = old('status', $edit['status'] ?? 'aktif');
$selectedVisitType = old('jenis_layanan', $selectedParticipant['jenis'] ?? $workspaceService);
$posbinduOld = old('posbindu');
$posbinduOld = is_array($posbinduOld) ? $posbinduOld : decode_kesehatan_posbindu_details($selectedVisit['posbindu_data_json'] ?? '');
$sasaranOld = old('sasaran');
$sasaranOld = is_array($sasaranOld) ? sanitize_kesehatan_sasaran_details($sasaranOld) : decode_kesehatan_sasaran_details($selectedVisit['sasaran_data_json'] ?? '');
$posbinduYesNoOptions = kesehatan_posbindu_yes_no_options();
$activeStep = (string) service('request')->getGet('tab');
if ($selectedParticipant) {
    $activeStep = 'pemeriksaan';
} elseif ($isEditing) {
    $activeStep = 'peserta';
} elseif (! in_array($activeStep, ['kegiatan', 'peserta', 'pemeriksaan'], true)) {
    $activeStep = 'kegiatan';
}
$workspaceUrl = static fn (string $tab): string => site_url('admin/kesehatan-data?jenis=' . rawurlencode($workspaceService) . '&jenis_kegiatan=' . rawurlencode($workspaceService) . '&tab=' . rawurlencode($tab) . '&tanggal_kegiatan=' . rawurlencode($activityDate));
$visitLifecycle = (string) ($selectedParticipant['kelompok_siklus'] ?? '');
$yesNoUnknownOptions = ['belum_diperiksa' => 'Belum diperiksa', 'ya' => 'Ya', 'tidak' => 'Tidak', 'tidak_berlaku' => 'Tidak berlaku'];
$visitDetailLabels = [
    'suhu_tubuh' => 'Suhu tubuh', 'status_ibu' => 'Status ibu', 'kenaikan_bb' => 'Kenaikan BB', 'asi_eksklusif' => 'ASI eksklusif',
    'pmt_lokal' => 'PMT lokal', 'vitamin_a' => 'Vitamin A', 'obat_cacing' => 'Obat cacing',
    'imunisasi' => 'Imunisasi', 'perkembangan' => 'Perkembangan',
    'status_bb_u' => 'Status BB/U', 'status_pb_u' => 'Status PB/TB-U',
    'status_bb_pb' => 'Status BB/PB-TB', 'status_imt_u' => 'Status IMT/U', 'ttd' => 'Tablet tambah darah',
    'kelas_ibu' => 'Kelas ibu', 'anemia' => 'Skrining anemia', 'kesehatan_jiwa' => 'Kesehatan jiwa',
    'napza' => 'Risiko NAPZA', 'kolesterol' => 'Kolesterol', 'adl' => 'ADL/AKS',
    'skilas' => 'SKILAS', 'lingkar_betis' => 'Lingkar betis', 'tanda_bahaya' => 'Tanda bahaya',
    'gejala_sakit' => 'Gejala/keluhan',
];
$visitDetailValues = [
    'hamil' => 'Hamil', 'nifas_menyusui' => 'Nifas/menyusui', 'naik' => 'Naik', 'tidak_naik' => 'Tidak naik',
    'belum_dinilai' => 'Belum dinilai', 'ya' => 'Ya', 'tidak' => 'Tidak', 'tidak_berlaku' => 'Tidak berlaku',
    'belum_diperiksa' => 'Belum diperiksa', 'lengkap' => 'Lengkap', 'belum_lengkap' => 'Belum lengkap',
    'tidak_diperiksa' => 'Tidak diperiksa', 'sesuai' => 'Sesuai', 'meragukan' => 'Meragukan',
    'penyimpangan' => 'Ada penyimpangan', 'curiga' => 'Curiga anemia', 'normal' => 'Normal',
    'sangat_kurang' => 'Sangat kurang', 'kurang' => 'Kurang', 'risiko_lebih' => 'Risiko berat lebih',
    'sangat_pendek' => 'Sangat pendek', 'pendek' => 'Pendek', 'tinggi' => 'Tinggi',
    'gizi_buruk' => 'Gizi buruk', 'gizi_kurang' => 'Gizi kurang', 'gizi_baik' => 'Gizi baik',
    'gizi_lebih' => 'Gizi lebih', 'obesitas' => 'Obesitas',
    'bermasalah' => 'Ada masalah', 'berisiko' => 'Berisiko', 'mandiri' => 'Mandiri',
    'ketergantungan_ringan' => 'Ketergantungan ringan', 'ketergantungan_sedang' => 'Ketergantungan sedang',
    'ketergantungan_berat' => 'Ketergantungan berat', 'ketergantungan_total' => 'Ketergantungan total',
    'perlu_tindak_lanjut' => 'Perlu tindak lanjut', 'ada' => 'Ada',
];
$visitSummary = static function (?array $visit) use ($visitDetailLabels, $visitDetailValues): array {
    if (! $visit) return [];
    $parts = [];
    foreach ([
        'berat_kg' => ['BB', 'kg'], 'tinggi_cm' => ['TB/PB', 'cm'], 'lingkar_kepala_cm' => ['LK', 'cm'],
        'lingkar_lengan_cm' => ['LILA', 'cm'], 'lingkar_perut_cm' => ['LP', 'cm'],
    ] as $field => [$label, $unit]) {
        if (($visit[$field] ?? null) !== null && (string) $visit[$field] !== '') $parts[] = $label . ' ' . $visit[$field] . ' ' . $unit;
    }
    if (($visit['usia_kehamilan_minggu'] ?? null) !== null) $parts[] = 'Kehamilan ' . $visit['usia_kehamilan_minggu'] . ' minggu';
    if (($visit['tekanan_sistolik'] ?? null) !== null || ($visit['tekanan_diastolik'] ?? null) !== null) $parts[] = 'TD ' . ($visit['tekanan_sistolik'] ?? '-') . '/' . ($visit['tekanan_diastolik'] ?? '-') . ' mmHg';
    if (($visit['gula_darah'] ?? null) !== null) {
        $glucoseLabels = ['sewaktu' => 'sewaktu', 'puasa' => 'puasa', 'dua_jam_pp' => '2 jam setelah makan'];
        $glucoseContext = $glucoseLabels[$visit['jenis_gula_darah'] ?? ''] ?? '';
        $parts[] = 'Gula darah ' . $visit['gula_darah'] . ' mg/dL' . ($glucoseContext !== '' ? ' (' . $glucoseContext . ')' : '');
    }
    if (! empty($visit['faktor_merokok'])) $parts[] = 'Merokok: ' . (['tidak' => 'Tidak', 'ya' => 'Ya', 'berhenti' => 'Sudah berhenti'][$visit['faktor_merokok']] ?? $visit['faktor_merokok']);
    if (! empty($visit['aktivitas_fisik'])) $parts[] = 'Aktivitas fisik: ' . (['cukup' => 'Cukup', 'kurang' => 'Kurang'][$visit['aktivitas_fisik']] ?? $visit['aktivitas_fisik']);
    if (! empty($visit['konsumsi_buah_sayur'])) $parts[] = 'Buah/sayur: ' . (['cukup' => 'Cukup', 'kurang' => 'Kurang'][$visit['konsumsi_buah_sayur']] ?? $visit['konsumsi_buah_sayur']);
    $details = decode_kesehatan_sasaran_details($visit['sasaran_data_json'] ?? '');
    foreach ($details as $field => $value) {
        if ($value === '' || in_array($value, ['belum_diperiksa', 'tidak_diperiksa', 'tidak_berlaku'], true)) continue;
        $suffix = $field === 'kolesterol' ? ' mg/dL' : ($field === 'lingkar_betis' ? ' cm' : ($field === 'suhu_tubuh' ? ' °C' : ''));
        $parts[] = ($visitDetailLabels[$field] ?? $field) . ': ' . ($visitDetailValues[$value] ?? $value) . $suffix;
    }
    foreach (['layanan_diberikan', 'edukasi', 'catatan'] as $field) {
        if (! empty($visit[$field])) $parts[] = (string) $visit[$field];
    }
    return $parts;
};
?>
<div class="section-heading">
  <div>
    <h1><?= $workspaceService === 'posbindu' ? 'Posbindu' : 'Posyandu' ?></h1>
    <p class="muted"><?= $workspaceService === 'posbindu' ? 'Daftarkan peserta dewasa atau lansia, lalu isi hasil pemeriksaannya.' : 'Daftarkan peserta Posyandu, catat kehadiran, pengukuran, dan layanan yang diberikan.' ?></p>
  </div>
  <div class="form-actions">
    <a href="<?= site_url('admin/kesehatan-dashboard') ?>">Dashboard Kesehatan</a>
    <?php if ($workspaceService === 'posbindu'): ?><a href="<?= site_url('admin/posbindu-laporan') ?>">Kembali ke Posbindu</a><?php endif; ?>
    <a href="<?= site_url('admin/kesehatan-tindak-lanjut') ?>">Tindak Lanjut & Rujukan</a>
    <a href="<?= site_url('kesehatan') ?>" target="_blank" rel="noopener noreferrer">Lihat Halaman Warga</a>
  </div>
</div>

<?php if ($success !== ''): ?><div class="alert success"><?= rw_esc($success) ?></div><?php endif; ?>
<?php if ($error !== ''): ?><div class="alert error"><?= rw_esc($error) ?></div><?php endif; ?>

<nav class="health-task-tabs health-quick-actions" aria-label="Langkah kerja <?= rw_esc($workspaceService) ?>">
  <a href="<?= $workspaceUrl('kegiatan') ?>" class="<?= $activeStep === 'kegiatan' ? 'is-active' : '' ?>">
    <span class="health-task-number">1</span><span class="health-task-copy"><strong>Hari Buka &amp; Kehadiran</strong><small>Simpan peserta datang atau tidak datang</small></span><span class="health-task-cta">Klik di sini →</span>
  </a>
  <a href="<?= $workspaceUrl('peserta') ?>" class="<?= $activeStep === 'peserta' ? 'is-active' : '' ?>">
    <span class="health-task-number">2</span><span class="health-task-copy"><strong>Data Sasaran</strong><small>Tambah atau perbaiki identitas sasaran</small></span><span class="health-task-cta">Klik di sini →</span>
  </a>
  <a href="<?= $workspaceUrl('pemeriksaan') ?>" class="<?= $activeStep === 'pemeriksaan' ? 'is-active' : '' ?>">
    <span class="health-task-number">3</span><span class="health-task-copy"><strong>Hasil Pelayanan</strong><small>Isi langkah 2-5 dan edit hasil</small></span><span class="health-task-cta">Klik di sini →</span>
  </a>
</nav>

<?php if ($activeStep === 'kegiatan'): ?>
<?php
$dayCounts = ['target' => count($attendanceParticipants), 'hadir' => 0, 'tidak_hadir' => 0, 'hasil' => 0, 'perhatian' => 0, 'rujukan' => 0];
foreach ($attendanceParticipants as $dayParticipant) {
    $dayVisit = $attendanceMap[(int) $dayParticipant['id']] ?? null;
    $dayStatus = kesehatan_visit_screening_status($dayVisit, $dayParticipant);
    if (($dayVisit['hadir'] ?? '') === 'ya') $dayCounts['hadir']++;
    if (($dayVisit['hadir'] ?? '') === 'tidak') $dayCounts['tidak_hadir']++;
    if (kesehatan_visit_has_results($dayVisit)) $dayCounts['hasil']++;
    if ($dayStatus['key'] === 'perhatian') $dayCounts['perhatian']++;
    if ($dayStatus['key'] === 'rujukan') $dayCounts['rujukan']++;
}
?>
<section class="panel">
  <div class="section-heading">
    <div>
      <h2>Kegiatan Hari Ini</h2>
      <p class="muted">Pilih tanggal kegiatan. Simpan kehadiran lebih dulu, kemudian isi hasil peserta yang hadir.</p>
    </div>
    <div class="form-actions">
      <?php if ($activityJenis === 'posbindu'): ?><a href="<?= site_url('admin/posbindu-laporan?tanggal=' . rawurlencode($activityDate)) ?>">Kelola Laporan Posbindu</a><?php endif; ?>
      <a href="<?= site_url('admin/kesehatan-data?print=1&jenis_kegiatan=' . rawurlencode($activityJenis) . '&tanggal_kegiatan=' . rawurlencode($activityDate)) ?>" target="_blank" rel="noopener noreferrer">Preview / Cetak</a>
    </div>
  </div>
  <form method="get" action="<?= site_url('admin/kesehatan-data') ?>" class="grid-form">
    <input type="hidden" name="jenis" value="<?= rw_esc($workspaceService) ?>">
    <input type="hidden" name="jenis_kegiatan" value="<?= rw_esc($workspaceService) ?>">
    <input type="hidden" name="tab" value="kegiatan">
    <label>Tanggal Kegiatan
      <input type="date" name="tanggal_kegiatan" value="<?= rw_esc($activityDate) ?>" required onchange="this.form.submit()">
    </label>
  </form>
  <div class="health-day-recap" aria-label="Rekap kegiatan tanggal ini">
    <div><strong><?= $dayCounts['target'] ?></strong><span>Sasaran aktif</span></div>
    <div><strong><?= $dayCounts['hadir'] ?></strong><span>Hadir</span></div>
    <div><strong><?= $dayCounts['tidak_hadir'] ?></strong><span>Tidak hadir</span></div>
    <div><strong><?= $dayCounts['hasil'] ?></strong><span>Hasil terisi</span></div>
    <div class="is-warning"><strong><?= $dayCounts['perhatian'] ?></strong><span>Perlu perhatian</span></div>
    <div class="is-danger"><strong><?= $dayCounts['rujukan'] ?></strong><span>Rujukan</span></div>
  </div>
  <div class="health-flow-note"><strong>Apa fungsi Simpan Kehadiran?</strong><span>Tombol ini hanya menyimpan siapa yang hadir atau tidak hadir pada tanggal tersebut. Setelah tersimpan, gunakan tombol <b>Isi hasil</b> untuk mencatat pengukuran dan pelayanan. Menyimpan ulang kehadiran tidak menghapus hasil pemeriksaan yang sudah ada.</span></div>
  <form method="post" action="<?= site_url('admin/kesehatan-data') ?>">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="save_attendance">
    <input type="hidden" name="jenis_kegiatan" value="<?= rw_esc($activityJenis) ?>">
    <input type="hidden" name="tanggal_kegiatan" value="<?= rw_esc($activityDate) ?>">
    <div class="table-scroll">
      <table>
        <thead><tr><th>Hadir</th><th>Peserta</th><th>RT</th><th>Status</th><th>Hasil Hari Ini</th><th>Aksi</th></tr></thead>
        <tbody>
          <?php foreach ($attendanceParticipants as $participant): ?>
            <?php $attendance = $attendanceMap[(int) $participant['id']] ?? null; ?>
            <?php $screeningStatus = kesehatan_visit_screening_status($attendance, $participant); ?>
            <tr>
              <td><input type="checkbox" name="hadir[<?= (int) $participant['id'] ?>]" value="1" <?= ($attendance['hadir'] ?? '') === 'ya' ? 'checked' : '' ?> aria-label="Hadir: <?= rw_esc($participant['nama']) ?>"></td>
              <td><strong><?= rw_esc($participant['nama']) ?></strong><?= ! empty($participant['nama_wali']) ? '<br><small>Wali: ' . rw_esc($participant['nama_wali']) . '</small>' : '' ?></td>
              <td><?= rw_esc($participant['rt'] ?? '-') ?></td>
              <td><span class="health-screening-status is-<?= rw_esc($screeningStatus['key']) ?>" title="<?= rw_esc(implode(' · ', $screeningStatus['reasons'])) ?>"><?= rw_esc($screeningStatus['label']) ?></span><?php if ($screeningStatus['reasons']): ?><small class="health-status-reason"><?= rw_esc(implode(' · ', array_slice($screeningStatus['reasons'], 0, 2))) ?></small><?php endif; ?></td>
              <td>
                <?php $todayNotes = $visitSummary($attendance); ?>
                <?php $todaySummary = $todayNotes ? implode(' · ', $todayNotes) : (($attendance['hadir'] ?? '') === 'ya' ? 'Hadir, hasil belum diisi' : (($attendance['hadir'] ?? '') === 'tidak' ? 'Tidak hadir' : 'Kehadiran belum disimpan')); ?>
                <span class="health-result-summary" title="<?= rw_esc($todaySummary) ?>"><?= rw_esc(str_starts_with($todaySummary, 'Impor laporan Posbindu') ? 'Data hasil impor - klik Edit hasil untuk melengkapi' : $todaySummary) ?></span>
                <?php if ($screeningStatus['bmi'] !== null): ?><small>IMT <?= rw_esc((string) $screeningStatus['bmi']) ?> kg/m²</small><?php endif; ?>
              </td>
              <td><a class="health-row-action" href="<?= rw_esc($workspaceUrl('pemeriksaan') . '&peserta_id=' . (int) $participant['id']) ?>"><?= kesehatan_visit_has_results($attendance) ? 'Edit hasil' : 'Isi hasil' ?></a></td>
            </tr>
          <?php endforeach; ?>
          <?php if (empty($attendanceParticipants)): ?><tr><td colspan="6" class="table-empty">Belum ada peserta aktif untuk jenis layanan ini.</td></tr><?php endif; ?>
        </tbody>
      </table>
    </div>
    <div class="form-actions"><button type="submit"<?= empty($attendanceParticipants) ? ' disabled' : '' ?>>Simpan Kehadiran Tanggal Ini</button></div>
  </form>
</section>
<?php endif; ?>

<?php if ($activeStep === 'peserta'): ?>
<section class="panel">
  <div class="section-heading">
    <div>
      <h2><?= $isEditing ? 'Edit Peserta' : 'Daftarkan Peserta' ?></h2>
      <p class="muted">Simpan data minimum yang diperlukan untuk pelaksanaan kegiatan dan tindak lanjut kader.</p>
    </div>
    <?php if ($isEditing): ?><a href="<?= $workspaceUrl('peserta') ?>">Batal Edit</a><?php endif; ?>
  </div>
  <?php if (! $tableReady): ?>
    <div class="alert warning">Penyimpanan data kader belum siap.</div>
  <?php else: ?>
    <form method="post" action="<?= site_url('admin/kesehatan-data') ?>" class="grid-form">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="save_participant">
      <input type="hidden" name="id" value="<?= rw_esc((string) ($edit['id'] ?? '')) ?>">
      <input type="hidden" name="jenis" id="healthParticipantType" value="<?= rw_esc($selectedJenis) ?>">
      <div class="health-service-choice"><span>Jenis peserta</span><strong><?= rw_esc($participantTypeOptions[$selectedJenis] ?? $selectedJenis) ?></strong></div>
      <label>Kelompok Usia / Sasaran
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
        <input type="date" name="tanggal_lahir" value="<?= rw_esc(old('tanggal_lahir', $edit['tanggal_lahir'] ?? '')) ?>" <?= $workspaceService === 'posbindu' ? 'required' : '' ?>>
      </label>
      <label>Jenis Kelamin
        <select name="jenis_kelamin" <?= $workspaceService === 'posbindu' ? 'required' : '' ?>>
          <option value="">Pilih bila diperlukan</option>
          <?php foreach ($genderOptions as $value => $label): ?>
            <option value="<?= rw_esc($value) ?>" <?= is_selected($selectedGender, $value) ?>><?= rw_esc($label) ?></option>
          <?php endforeach; ?>
        </select>
      </label>
      <details class="full health-form-section" data-participant-service="posbindu">
        <summary><strong>Data tambahan laporan Puskesmas</strong> <span class="muted">(isi jika tersedia)</span></summary>
        <div class="grid-form health-nested-grid">
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
        </div>
      </details>
      <label>Nama Orang Tua / Wali
        <input type="text" name="nama_wali" maxlength="160" value="<?= rw_esc(old('nama_wali', $edit['nama_wali'] ?? '')) ?>">
      </label>
      <label>RT
        <input type="text" name="rt" maxlength="20" value="<?= rw_esc(old('rt', $edit['rt'] ?? '')) ?>" placeholder="Contoh: 01">
      </label>
      <label>No. HP Kontak
        <input type="text" name="no_hp" maxlength="40" value="<?= rw_esc(old('no_hp', $edit['no_hp'] ?? '')) ?>">
      </label>
      <label class="full">Alamat Singkat <?= $workspaceService === 'posbindu' ? '(wajib untuk laporan)' : '' ?>
        <input type="text" name="alamat" maxlength="255" value="<?= rw_esc(old('alamat', $edit['alamat'] ?? '')) ?>" <?= $workspaceService === 'posbindu' ? 'required' : '' ?>>
      </label>
      <?php if ($isEditing): ?>
        <label>Status Peserta
          <select name="status">
            <?php foreach ($statusOptions as $value => $label): ?>
              <option value="<?= rw_esc($value) ?>" <?= is_selected($selectedStatus, $value) ?>><?= rw_esc($label) ?></option>
            <?php endforeach; ?>
          </select>
        </label>
      <?php else: ?>
        <input type="hidden" name="status" value="aktif">
      <?php endif; ?>
      <details class="full health-form-section">
        <summary><strong>Catatan kader</strong> <span class="muted">(opsional)</span></summary>
        <div class="grid-form health-nested-grid">
          <label class="full">Catatan Kader
            <textarea name="catatan" rows="3" maxlength="2000" placeholder="Catatan non-sensitif untuk kebutuhan pendampingan."><?= rw_esc(old('catatan', $edit['catatan'] ?? '')) ?></textarea>
          </label>
        </div>
      </details>
      <label class="full health-consent-check">
        <input type="checkbox" name="persetujuan_data" value="1" <?= ! empty(old('persetujuan_data', $edit['persetujuan_data'] ?? 0)) ? 'checked' : '' ?> <?= $isEditing ? '' : 'required' ?>>
        Peserta atau wali telah diberi tahu bahwa data minimum ini digunakan untuk kegiatan dan tindak lanjut kesehatan RW.
      </label>
      <div class="full form-actions">
        <button type="submit"><?= $isEditing ? 'Simpan Perubahan' : 'Simpan Peserta' ?></button>
        <?php if ($isEditing): ?><a class="btn-light" href="<?= $workspaceUrl('peserta') ?>">Batal</a><?php endif; ?>
      </div>
    </form>
  <?php endif; ?>
</section>

<section class="panel">
  <div class="section-heading">
    <div>
      <h2>Daftar Peserta <?= $workspaceService === 'posbindu' ? 'Posbindu' : 'Posyandu' ?></h2>
      <p class="muted">Gunakan tombol Isi hasil untuk mencatat pengukuran peserta.</p>
    </div>
  </div>
  <div class="table-scroll">
    <table>
      <thead><tr><th>Peserta</th><th>Siklus Hidup</th><th>Kontak Lingkungan</th><th>Status</th><th>Aksi</th></tr></thead>
      <tbody>
        <?php foreach ($participants as $participant): ?>
          <tr>
            <td><strong><?= rw_esc($participant['nama'] ?? '') ?></strong><br><small><?= ! empty($participant['nik']) ? 'NIK ' . rw_esc($participant['nik']) . ' · ' : '' ?><?= rw_esc($participant['tanggal_lahir'] ?? '-') ?><?= ! empty($participant['nama_wali']) ? ' · Wali: ' . rw_esc($participant['nama_wali']) : '' ?></small></td>
            <td><?= rw_esc($lifecycleOptions[$participant['kelompok_siklus'] ?? ''] ?? 'Belum ditentukan') ?></td>
            <td>RT <?= rw_esc($participant['rt'] ?? '-') ?><br><small><?= rw_esc($participant['no_hp'] ?? '') ?></small></td>
            <td><?= rw_esc($statusOptions[$participant['status'] ?? ''] ?? ucfirst((string) ($participant['status'] ?? '-'))) ?></td>
            <td><div class="table-actions"><a href="<?= site_url('admin/kesehatan-data?jenis=' . rawurlencode($workspaceService) . '&jenis_kegiatan=' . rawurlencode($workspaceService) . '&tab=pemeriksaan&peserta_id=' . (int) $participant['id']) ?>">Isi hasil</a><a href="<?= site_url('admin/kesehatan-data?jenis=' . rawurlencode($workspaceService) . '&jenis_kegiatan=' . rawurlencode($workspaceService) . '&tab=peserta&action=edit&id=' . (int) $participant['id']) ?>">Edit peserta</a><form method="post" action="<?= site_url('admin/kesehatan-data') ?>" onsubmit="return confirm('Hapus peserta dan seluruh catatan kunjungannya?')"><?= csrf_field() ?><input type="hidden" name="action" value="delete_participant"><input type="hidden" name="return_service" value="<?= rw_esc($workspaceService) ?>"><input type="hidden" name="id" value="<?= (int) $participant['id'] ?>"><button type="submit" class="btn-link-danger">Hapus</button></form></div></td>
          </tr>
        <?php endforeach; ?>
        <?php if (empty($participants)): ?><tr><td colspan="5" class="table-empty">Belum ada peserta.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</section>
<?php endif; ?>

<?php if ($activeStep === 'pemeriksaan'): ?>
<section class="panel">
  <div class="section-heading">
    <div><h2><?= $workspaceService === 'posbindu' ? 'Isi Pemeriksaan Posbindu' : 'Catat Kunjungan Posyandu' ?></h2><p class="muted">Pilih peserta, lalu isi hanya hasil yang benar-benar diperiksa.</p></div>
    <?php if ($selectedParticipant): ?><strong><?= rw_esc($selectedParticipant['nama']) ?></strong><?php endif; ?>
  </div>
  <?php if (! $selectedParticipant): ?>
    <div class="alert warning">Pilih peserta yang akan diperiksa. Formulir akan menyesuaikan kelompok usia atau sasarannya.</div>
    <div class="health-participant-picker">
      <?php foreach ($participants as $participant): ?>
        <a href="<?= rw_esc($workspaceUrl('pemeriksaan') . '&peserta_id=' . (int) $participant['id']) ?>"><strong><?= rw_esc($participant['nama']) ?></strong><span><?= rw_esc($lifecycleOptions[$participant['kelompok_siklus'] ?? ''] ?? 'Kelompok belum ditentukan') ?></span><b>Isi hasil →</b></a>
      <?php endforeach; ?>
      <?php if (empty($participants)): ?><p class="muted">Belum ada peserta. Tambahkan peserta pada langkah 2 terlebih dahulu.</p><?php endif; ?>
    </div>
  <?php else: ?>
  <form method="post" action="<?= site_url('admin/kesehatan-data') ?>" class="grid-form">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="save_visit">
    <input type="hidden" name="peserta_id" value="<?= (int) $selectedParticipant['id'] ?>">
    <div class="health-selected-participant"><span>Peserta</span><strong><?= rw_esc($selectedParticipant['nama']) ?></strong><small><?= rw_esc($lifecycleOptions[$visitLifecycle] ?? 'Kelompok belum ditentukan') ?></small><a href="<?= $workspaceUrl('pemeriksaan') ?>">Ganti peserta</a></div>
    <?php if ($workspaceService === 'posyandu' && $visitLifecycle === 'bayi_balita' && (empty($selectedParticipant['tanggal_lahir']) || empty($selectedParticipant['jenis_kelamin']))): ?>
      <div class="full alert warning">Lengkapi tanggal lahir dan jenis kelamin pada Data Sasaran sebelum menyalin status pertumbuhan dari Buku KIA/ASIK.</div>
    <?php endif; ?>
    <label>Tanggal Kunjungan
      <input type="date" name="tanggal" value="<?= rw_esc(old('tanggal', $selectedVisit['tanggal'] ?? $activityDate)) ?>" required>
    </label>
    <label>Kehadiran
      <select name="hadir"><option value="ya" <?= is_selected(old('hadir', $selectedVisit['hadir'] ?? 'ya'), 'ya') ?>>Hadir</option><option value="tidak" <?= is_selected(old('hadir', $selectedVisit['hadir'] ?? 'ya'), 'tidak') ?>>Tidak hadir</option></select>
    </label>
    <?php if ($workspaceService === 'posyandu'): ?><label>Suhu Tubuh (°C, bila diukur)
      <input type="number" name="sasaran[suhu_tubuh]" min="30" max="45" step="0.1" value="<?= rw_esc($sasaranOld['suhu_tubuh'] ?? '') ?>">
    </label><?php endif; ?>
    <label>Jenis Layanan Kunjungan
      <input type="hidden" name="jenis_layanan" id="healthVisitType" value="<?= rw_esc($selectedVisitType) ?>">
      <span class="health-locked-value"><?= rw_esc($participantTypeOptions[$selectedVisitType] ?? $selectedVisitType) ?></span>
    </label>
    <div class="full health-form-section"><strong>2. Penimbangan dan pengukuran dasar</strong><p class="muted">Isi hanya pengukuran yang benar-benar dilakukan dengan alat yang tersedia.</p></div>
    <label>Berat (kg)<input type="number" name="berat_kg" min="0" max="300" step="0.01" value="<?= rw_esc(old('berat_kg', $selectedVisit['berat_kg'] ?? '')) ?>"></label>
    <label>Tinggi / Panjang Badan (cm)<input type="number" name="tinggi_cm" min="0" max="250" step="0.01" value="<?= rw_esc(old('tinggi_cm', $selectedVisit['tinggi_cm'] ?? '')) ?>"></label>
    <?php if ($workspaceService === 'posyandu' && $visitLifecycle === 'bayi_balita'): ?>
      <label>Lingkar Kepala (cm)<input type="number" name="lingkar_kepala_cm" min="0" max="100" step="0.01" value="<?= rw_esc(old('lingkar_kepala_cm', $selectedVisit['lingkar_kepala_cm'] ?? '')) ?>"></label>
      <label>Lingkar Lengan / LILA (cm)<input type="number" name="lingkar_lengan_cm" min="0" max="100" step="0.01" value="<?= rw_esc(old('lingkar_lengan_cm', $selectedVisit['lingkar_lengan_cm'] ?? '')) ?>"></label>
      <div class="full health-form-section"><strong>3. Pencatatan tumbuh kembang bayi/balita</strong><p class="muted">Mengikuti komponen Kartu Bantu Kemenkes. Pilih “belum diperiksa” bila layanan tidak dilakukan hari ini.</p></div>
      <label>Kenaikan Berat Badan<select name="sasaran[kenaikan_bb]"><?php foreach (['belum_dinilai' => 'Belum dinilai', 'naik' => 'Naik', 'tidak_naik' => 'Tidak naik'] as $value => $label): ?><option value="<?= $value ?>" <?= is_selected($sasaranOld['kenaikan_bb'] ?? 'belum_dinilai', $value) ?>><?= $label ?></option><?php endforeach; ?></select></label>
      <label>Perkembangan<select name="sasaran[perkembangan]"><?php foreach (['belum_diperiksa' => 'Belum diperiksa', 'sesuai' => 'Sesuai umur', 'meragukan' => 'Meragukan', 'penyimpangan' => 'Ada penyimpangan'] as $value => $label): ?><option value="<?= $value ?>" <?= is_selected($sasaranOld['perkembangan'] ?? 'belum_diperiksa', $value) ?>><?= $label ?></option><?php endforeach; ?></select></label>
      <?php foreach (['asi_eksklusif' => 'ASI Eksklusif', 'pmt_lokal' => 'PMT Lokal', 'vitamin_a' => 'Vitamin A', 'obat_cacing' => 'Obat Cacing'] as $field => $label): ?>
        <label><?= $label ?><select name="sasaran[<?= $field ?>]"><?php foreach ($yesNoUnknownOptions as $value => $optionLabel): ?><option value="<?= $value ?>" <?= is_selected($sasaranOld[$field] ?? 'belum_diperiksa', $value) ?>><?= $optionLabel ?></option><?php endforeach; ?></select></label>
      <?php endforeach; ?>
      <label>Status Imunisasi<select name="sasaran[imunisasi]"><?php foreach (['tidak_diperiksa' => 'Tidak diperiksa', 'lengkap' => 'Lengkap sesuai usia', 'belum_lengkap' => 'Belum lengkap'] as $value => $label): ?><option value="<?= $value ?>" <?= is_selected($sasaranOld['imunisasi'] ?? 'tidak_diperiksa', $value) ?>><?= $label ?></option><?php endforeach; ?></select></label>
      <details class="full health-form-section health-growth-status">
        <summary><strong>Status pertumbuhan dari Buku KIA / ASIK</strong> <span class="muted">(salin bila sudah dinilai)</span></summary>
        <p class="muted">Web tidak menebak status gizi. Pilih hasil yang sudah ditentukan memakai kurva pertumbuhan sesuai umur dan jenis kelamin.</p>
        <div class="grid-form health-nested-grid">
          <label>Berat Badan menurut Umur (BB/U)<select name="sasaran[status_bb_u]"><?php foreach (['belum_dinilai' => 'Belum dinilai', 'sangat_kurang' => 'Berat badan sangat kurang', 'kurang' => 'Berat badan kurang', 'normal' => 'Berat badan normal', 'risiko_lebih' => 'Risiko berat badan lebih'] as $value => $label): ?><option value="<?= $value ?>" <?= is_selected($sasaranOld['status_bb_u'] ?? 'belum_dinilai', $value) ?>><?= $label ?></option><?php endforeach; ?></select></label>
          <label>Panjang/Tinggi menurut Umur (PB/TB-U)<select name="sasaran[status_pb_u]"><?php foreach (['belum_dinilai' => 'Belum dinilai', 'sangat_pendek' => 'Sangat pendek', 'pendek' => 'Pendek', 'normal' => 'Normal', 'tinggi' => 'Tinggi'] as $value => $label): ?><option value="<?= $value ?>" <?= is_selected($sasaranOld['status_pb_u'] ?? 'belum_dinilai', $value) ?>><?= $label ?></option><?php endforeach; ?></select></label>
          <label>Berat menurut Panjang/Tinggi (BB/PB-TB)<select name="sasaran[status_bb_pb]"><?php foreach (['belum_dinilai' => 'Belum dinilai', 'gizi_buruk' => 'Gizi buruk', 'gizi_kurang' => 'Gizi kurang', 'gizi_baik' => 'Gizi baik', 'risiko_lebih' => 'Berisiko gizi lebih', 'gizi_lebih' => 'Gizi lebih', 'obesitas' => 'Obesitas'] as $value => $label): ?><option value="<?= $value ?>" <?= is_selected($sasaranOld['status_bb_pb'] ?? 'belum_dinilai', $value) ?>><?= $label ?></option><?php endforeach; ?></select></label>
          <label>IMT menurut Umur (IMT/U, bila tersedia)<select name="sasaran[status_imt_u]"><?php foreach (['belum_dinilai' => 'Belum dinilai', 'gizi_buruk' => 'Gizi buruk', 'gizi_kurang' => 'Gizi kurang', 'gizi_baik' => 'Gizi baik', 'risiko_lebih' => 'Berisiko gizi lebih', 'gizi_lebih' => 'Gizi lebih', 'obesitas' => 'Obesitas'] as $value => $label): ?><option value="<?= $value ?>" <?= is_selected($sasaranOld['status_imt_u'] ?? 'belum_dinilai', $value) ?>><?= $label ?></option><?php endforeach; ?></select></label>
        </div>
      </details>
    <?php elseif ($workspaceService === 'posyandu' && $visitLifecycle === 'ibu_hamil_nifas'): ?>
      <label>Status Sasaran<select name="sasaran[status_ibu]"><option value="hamil" <?= is_selected($sasaranOld['status_ibu'] ?? 'hamil', 'hamil') ?>>Ibu hamil</option><option value="nifas_menyusui" <?= is_selected($sasaranOld['status_ibu'] ?? 'hamil', 'nifas_menyusui') ?>>Ibu nifas/menyusui</option></select></label>
      <label>Lingkar Lengan / LILA (cm)<input type="number" name="lingkar_lengan_cm" min="0" max="100" step="0.01" value="<?= rw_esc(old('lingkar_lengan_cm', $selectedVisit['lingkar_lengan_cm'] ?? '')) ?>"></label>
      <label>Usia Kehamilan (minggu, bila sedang hamil)<input type="number" name="usia_kehamilan_minggu" min="0" max="45" value="<?= rw_esc(old('usia_kehamilan_minggu', $selectedVisit['usia_kehamilan_minggu'] ?? '')) ?>"></label>
      <label>Tekanan Sistolik<input type="number" name="tekanan_sistolik" min="0" max="300" value="<?= rw_esc(old('tekanan_sistolik', $selectedVisit['tekanan_sistolik'] ?? '')) ?>"></label>
      <label>Tekanan Diastolik<input type="number" name="tekanan_diastolik" min="0" max="300" value="<?= rw_esc(old('tekanan_diastolik', $selectedVisit['tekanan_diastolik'] ?? '')) ?>"></label>
      <div class="full health-form-section"><strong>3. Pemeriksaan ibu hamil/nifas/menyusui</strong><p class="muted">Isi pelayanan yang benar-benar diperiksa atau diberikan pada kunjungan ini.</p></div>
      <?php foreach (['ttd' => 'Tablet Tambah Darah', 'kelas_ibu' => 'Kelas Ibu'] as $field => $label): ?>
        <label><?= $label ?><select name="sasaran[<?= $field ?>]"><?php foreach ($yesNoUnknownOptions as $value => $optionLabel): ?><option value="<?= $value ?>" <?= is_selected($sasaranOld[$field] ?? 'belum_diperiksa', $value) ?>><?= $optionLabel ?></option><?php endforeach; ?></select></label>
      <?php endforeach; ?>
    <?php endif; ?>
    <?php $needsAdultScreening = $workspaceService === 'posbindu' || ($workspaceService === 'posyandu' && in_array($visitLifecycle, ['usia_sekolah_remaja', 'dewasa', 'lansia'], true)); ?>
    <?php if ($needsAdultScreening): ?>
    <label>Lingkar Perut (cm)<input type="number" name="lingkar_perut_cm" min="0" max="250" step="0.01" value="<?= rw_esc(old('lingkar_perut_cm', $selectedVisit['lingkar_perut_cm'] ?? '')) ?>"></label>
    <div class="full health-form-section"><strong>3. Pemeriksaan dan skrining sesuai sasaran</strong><p class="muted">Isi hanya pemeriksaan yang benar-benar dilakukan oleh kader terlatih atau tenaga kesehatan.</p></div>
    <label>Tekanan Sistolik<input type="number" name="tekanan_sistolik" min="0" max="300" value="<?= rw_esc(old('tekanan_sistolik', $selectedVisit['tekanan_sistolik'] ?? '')) ?>"></label>
    <label>Tekanan Diastolik<input type="number" name="tekanan_diastolik" min="0" max="300" value="<?= rw_esc(old('tekanan_diastolik', $selectedVisit['tekanan_diastolik'] ?? '')) ?>"></label>
    <label>Gula Darah (mg/dL)<input type="number" name="gula_darah" min="0" max="1000" step="0.01" value="<?= rw_esc(old('gula_darah', $selectedVisit['gula_darah'] ?? '')) ?>"></label>
    <label>Konteks Gula Darah<select name="jenis_gula_darah"><option value="">Pilih bila diperiksa</option><?php foreach ($glucoseContextOptions as $value => $label): ?><option value="<?= rw_esc($value) ?>" <?= is_selected(old('jenis_gula_darah', $selectedVisit['jenis_gula_darah'] ?? ''), $value) ?>><?= rw_esc($label) ?></option><?php endforeach; ?></select></label>
    <label>Kebiasaan Merokok<select name="faktor_merokok"><option value="" <?= is_selected(old('faktor_merokok', $selectedVisit['faktor_merokok'] ?? ''), '') ?>>Tidak ditanyakan</option><option value="tidak" <?= is_selected(old('faktor_merokok', $selectedVisit['faktor_merokok'] ?? ''), 'tidak') ?>>Tidak</option><option value="ya" <?= is_selected(old('faktor_merokok', $selectedVisit['faktor_merokok'] ?? ''), 'ya') ?>>Ya</option><option value="berhenti" <?= is_selected(old('faktor_merokok', $selectedVisit['faktor_merokok'] ?? ''), 'berhenti') ?>>Sudah berhenti</option></select></label>
    <label>Aktivitas Fisik<select name="aktivitas_fisik"><option value="" <?= is_selected(old('aktivitas_fisik', $selectedVisit['aktivitas_fisik'] ?? ''), '') ?>>Tidak ditanyakan</option><option value="cukup" <?= is_selected(old('aktivitas_fisik', $selectedVisit['aktivitas_fisik'] ?? ''), 'cukup') ?>>Cukup</option><option value="kurang" <?= is_selected(old('aktivitas_fisik', $selectedVisit['aktivitas_fisik'] ?? ''), 'kurang') ?>>Kurang</option></select></label>
    <label>Konsumsi Buah & Sayur<select name="konsumsi_buah_sayur"><option value="" <?= is_selected(old('konsumsi_buah_sayur', $selectedVisit['konsumsi_buah_sayur'] ?? ''), '') ?>>Tidak ditanyakan</option><option value="cukup" <?= is_selected(old('konsumsi_buah_sayur', $selectedVisit['konsumsi_buah_sayur'] ?? ''), 'cukup') ?>>Cukup</option><option value="kurang" <?= is_selected(old('konsumsi_buah_sayur', $selectedVisit['konsumsi_buah_sayur'] ?? ''), 'kurang') ?>>Kurang</option></select></label>
    <?php if ($workspaceService === 'posyandu' && $visitLifecycle === 'usia_sekolah_remaja'): ?>
      <label>Skrining Anemia<select name="sasaran[anemia]"><option value="belum_diperiksa" <?= is_selected($sasaranOld['anemia'] ?? 'belum_diperiksa', 'belum_diperiksa') ?>>Belum diperiksa</option><option value="tidak" <?= is_selected($sasaranOld['anemia'] ?? '', 'tidak') ?>>Tidak ada tanda anemia</option><option value="curiga" <?= is_selected($sasaranOld['anemia'] ?? '', 'curiga') ?>>Curiga anemia</option></select></label>
      <label>Tablet Tambah Darah<select name="sasaran[ttd]"><?php foreach ($yesNoUnknownOptions as $value => $optionLabel): ?><option value="<?= $value ?>" <?= is_selected($sasaranOld['ttd'] ?? 'belum_diperiksa', $value) ?>><?= $optionLabel ?></option><?php endforeach; ?></select></label>
      <label>Skrining Kesehatan Jiwa<select name="sasaran[kesehatan_jiwa]"><option value="belum_diperiksa" <?= is_selected($sasaranOld['kesehatan_jiwa'] ?? 'belum_diperiksa', 'belum_diperiksa') ?>>Belum diperiksa</option><option value="normal" <?= is_selected($sasaranOld['kesehatan_jiwa'] ?? '', 'normal') ?>>Normal</option><option value="bermasalah" <?= is_selected($sasaranOld['kesehatan_jiwa'] ?? '', 'bermasalah') ?>>Ada masalah</option></select></label>
      <label>Risiko NAPZA<select name="sasaran[napza]"><option value="belum_diperiksa" <?= is_selected($sasaranOld['napza'] ?? 'belum_diperiksa', 'belum_diperiksa') ?>>Belum diperiksa</option><option value="tidak" <?= is_selected($sasaranOld['napza'] ?? '', 'tidak') ?>>Tidak berisiko</option><option value="berisiko" <?= is_selected($sasaranOld['napza'] ?? '', 'berisiko') ?>>Berisiko</option></select></label>
    <?php elseif ($workspaceService === 'posyandu' && in_array($visitLifecycle, ['dewasa', 'lansia'], true)): ?>
      <label>Kolesterol (mg/dL, bila diperiksa)<input type="number" name="sasaran[kolesterol]" min="0" max="1000" step="0.01" value="<?= rw_esc($sasaranOld['kolesterol'] ?? '') ?>"></label>
      <?php if ($visitLifecycle === 'lansia'): ?>
        <label>Lingkar Betis (cm, bila digunakan)<input type="number" name="sasaran[lingkar_betis]" min="0" max="100" step="0.01" value="<?= rw_esc($sasaranOld['lingkar_betis'] ?? '') ?>"></label>
        <label>Status Kemandirian ADL/AKS<select name="sasaran[adl]"><?php foreach (['belum_diperiksa' => 'Belum diperiksa', 'mandiri' => 'Mandiri', 'ketergantungan_ringan' => 'Ketergantungan ringan', 'ketergantungan_sedang' => 'Ketergantungan sedang', 'ketergantungan_berat' => 'Ketergantungan berat', 'ketergantungan_total' => 'Ketergantungan total'] as $value => $label): ?><option value="<?= $value ?>" <?= is_selected($sasaranOld['adl'] ?? 'belum_diperiksa', $value) ?>><?= $label ?></option><?php endforeach; ?></select></label>
        <label>Skrining Lansia Sederhana (SKILAS)<select name="sasaran[skilas]"><option value="belum_diperiksa" <?= is_selected($sasaranOld['skilas'] ?? 'belum_diperiksa', 'belum_diperiksa') ?>>Belum diperiksa</option><option value="normal" <?= is_selected($sasaranOld['skilas'] ?? '', 'normal') ?>>Tidak ditemukan penurunan</option><option value="perlu_tindak_lanjut" <?= is_selected($sasaranOld['skilas'] ?? '', 'perlu_tindak_lanjut') ?>>Perlu skrining lanjutan</option></select></label>
      <?php endif; ?>
    <?php endif; ?>
    <?php endif; ?>
    <?php if ($workspaceService === 'posbindu'): ?>
    <div class="full health-guideline-note"><strong>Alur Posbindu:</strong> wawancara faktor risiko → pengukuran → konseling/tindak lanjut. Bagian di bawah hanya dibuka bila datanya memang diperiksa atau diberikan oleh tenaga kesehatan.</div>
    <details class="full health-form-section">
      <summary><strong>Lengkapi riwayat PTM untuk laporan Puskesmas</strong> <span class="muted">(bila ditanyakan)</span></summary>
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
    <details class="full health-form-section">
      <summary><strong>Diagnosis dan terapi dari tenaga kesehatan</strong> <span class="muted">(jangan diisi berdasarkan dugaan kader)</span></summary>
      <div class="grid-form health-nested-grid">
        <?php for ($i = 1; $i <= 3; $i++): ?>
          <label>Diagnosis <?= $i ?><input type="text" name="posbindu[diagnosis_<?= $i ?>]" maxlength="160" value="<?= rw_esc($posbinduOld['diagnosis_' . $i] ?? '') ?>"></label>
        <?php endfor; ?>
        <label>Rujuk RS<select name="posbindu[rujuk_rs]"><option value="">Belum ditentukan</option><?php foreach ($posbinduYesNoOptions as $value => $optionLabel): ?><option value="<?= rw_esc($value) ?>" <?= is_selected($posbinduOld['rujuk_rs'] ?? '', $value) ?>><?= rw_esc($optionLabel) ?></option><?php endforeach; ?></select></label>
        <label class="full">Terapi Farmakologi<textarea name="posbindu[terapi_farmakologi]" rows="2" maxlength="2000"><?= rw_esc($posbinduOld['terapi_farmakologi'] ?? '') ?></textarea></label>
        <label class="full">Konseling, Informasi, dan Edukasi Kesehatan<textarea name="posbindu[kie_kesehatan]" rows="2" maxlength="2000"><?= rw_esc($posbinduOld['kie_kesehatan'] ?? '') ?></textarea></label>
      </div>
    </details>
    <details class="full health-form-section">
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
    <details class="full health-form-section">
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
    <?php endif; ?>
    <div class="full health-form-section"><strong>4–5. Pelayanan, edukasi, validasi, dan tindak lanjut</strong></div>
    <?php if ($workspaceService === 'posyandu'): ?>
      <label>Tanda Bahaya<select name="sasaran[tanda_bahaya]"><option value="belum_diperiksa" <?= is_selected($sasaranOld['tanda_bahaya'] ?? 'belum_diperiksa', 'belum_diperiksa') ?>>Belum diperiksa</option><option value="tidak" <?= is_selected($sasaranOld['tanda_bahaya'] ?? '', 'tidak') ?>>Tidak ditemukan</option><option value="ada" <?= is_selected($sasaranOld['tanda_bahaya'] ?? '', 'ada') ?>>Ada tanda bahaya</option></select></label>
      <label>Gejala / Keluhan Hari Ini<input type="text" name="sasaran[gejala_sakit]" maxlength="255" value="<?= rw_esc($sasaranOld['gejala_sakit'] ?? '') ?>" placeholder="Kosongkan bila tidak ada atau tidak ditanyakan"></label>
    <?php endif; ?>
    <label class="full">Layanan yang Diberikan<textarea name="layanan_diberikan" rows="2" maxlength="2000" placeholder="Contoh: penimbangan, imunisasi, vitamin A, PMT, atau pelayanan oleh tenaga kesehatan."><?= rw_esc(old('layanan_diberikan', $selectedVisit['layanan_diberikan'] ?? '')) ?></textarea></label>
    <label class="full">Edukasi / Konseling<textarea name="edukasi" rows="2" maxlength="2000" placeholder="Tuliskan edukasi yang benar-benar diberikan."><?= rw_esc(old('edukasi', $selectedVisit['edukasi'] ?? '')) ?></textarea></label>
    <label>Tindak Lanjut<select name="tindak_lanjut" id="healthFollowup"><?php foreach ($followupOptions as $value => $label): ?><option value="<?= rw_esc($value) ?>" <?= is_selected(old('tindak_lanjut', $selectedVisit['tindak_lanjut'] ?? 'selesai'), $value) ?>><?= rw_esc($label) ?></option><?php endforeach; ?></select></label>
    <label>Jadwal Tindak Lanjut<input type="date" name="tanggal_tindak_lanjut" value="<?= rw_esc(old('tanggal_tindak_lanjut', $selectedVisit['tanggal_tindak_lanjut'] ?? '')) ?>"></label>
    <label class="full" data-referral-field>Tujuan Rujukan / Konsultasi<input type="text" name="tujuan_rujukan" maxlength="160" value="<?= rw_esc(old('tujuan_rujukan', $selectedVisit['tujuan_rujukan'] ?? '')) ?>" placeholder="Contoh: Puskesmas Dayeuhkolot"></label>
    <?php if ($canValidateKesehatan ?? true): ?>
      <label>Status Validasi
        <select name="status_validasi">
          <option value="dicatat" <?= is_selected(old('status_validasi', $selectedVisit['status_validasi'] ?? 'dicatat'), 'dicatat') ?>>Dicatat kader</option>
          <option value="divalidasi" <?= is_selected(old('status_validasi', $selectedVisit['status_validasi'] ?? 'dicatat'), 'divalidasi') ?>>Divalidasi nakes/admin</option>
        </select>
      </label>
    <?php else: ?>
      <input type="hidden" name="status_validasi" value="dicatat">
      <p class="full muted">Validasi hasil kunjungan hanya dapat dilakukan oleh nakes/admin, lihat menu <a href="<?= site_url('admin/kesehatan-tindak-lanjut') ?>">Tindak Lanjut &amp; Rujukan</a>.</p>
    <?php endif; ?>
    <label class="full">Catatan Kunjungan<textarea name="catatan_kunjungan" rows="3" maxlength="2000" placeholder="Catatan singkat hasil kegiatan hari ini."><?= rw_esc(old('catatan_kunjungan', $selectedVisit['catatan'] ?? '')) ?></textarea></label>
    <div class="full form-actions"><button type="submit">Simpan Hasil Pemeriksaan</button></div>
  </form>
  <?php endif; ?>
</section>
<?php endif; ?>
<script>
(() => {
  const visitType = document.getElementById('healthVisitType');
  const participantType = document.getElementById('healthParticipantType');
  const visitParticipant = document.getElementById('healthVisitParticipant');
  const followup = document.getElementById('healthFollowup');
  const syncHealthFields = () => {
    const selected = visitType?.value || 'posyandu';
    document.querySelectorAll('[data-health-service]').forEach((field) => {
      field.hidden = field.dataset.healthService !== selected;
    });
    const lifecycle = visitParticipant?.selectedOptions?.[0]?.dataset?.lifecycle || '';
    document.querySelectorAll('[data-health-lifecycle]').forEach((field) => {
      const allowed = (field.dataset.healthLifecycle || '').split(',');
      field.hidden = selected !== 'posyandu' || !allowed.includes(lifecycle);
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
  visitParticipant?.addEventListener('change', syncHealthFields);
  followup?.addEventListener('change', syncHealthFields);
  syncHealthFields();
})();
</script>
<?= $this->endSection() ?>
