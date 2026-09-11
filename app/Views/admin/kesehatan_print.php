<?php
$reportTitle = 'Laporan ' . $reportType;
$presentCount = 0;
foreach ($reportRows as $row) {
    if (($row['hadir'] ?? '') === 'ya') {
        $presentCount++;
    }
}
$autoPrint = (bool) ($autoPrint ?? false);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= rw_esc($reportTitle) ?></title>
  <style>
    * { box-sizing: border-box; }
    body { margin: 0; color: #172b27; font-family: Arial, sans-serif; font-size: 12px; }
    .toolbar { display: flex; gap: 8px; align-items: center; padding: 12px 18px; color: #fff; background: #0b2f24; }
    .toolbar strong { flex: 1; }
    .toolbar button, .toolbar a { padding: 8px 12px; border: 0; border-radius: 5px; color: #0b2f24; background: #f4d181; font-weight: 700; text-decoration: none; cursor: pointer; }
    .page { padding: 26px; }
    header { margin-bottom: 18px; padding-bottom: 12px; border-bottom: 3px double #172b27; }
    h1, h2 { margin: 0 0 6px; color: #0b2f24; }
    h1 { font-size: 22px; }
    h2 { margin-top: 22px; font-size: 15px; }
    p { margin: 4px 0; }
    .summary { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin: 16px 0; }
    .summary div { padding: 10px; border: 1px solid #cbded5; background: #f5faf7; }
    .summary strong { display: block; margin-top: 4px; font-size: 16px; }
    table { width: 100%; border-collapse: collapse; }
    th, td { padding: 8px; border: 1px solid #cbd5d1; text-align: left; vertical-align: top; }
    th { color: #0b2f24; background: #e8f2ed; }
    .muted { color: #5c6b65; }
    @media print { .toolbar { display: none; } .page { padding: 0; } }
  </style>
<?php if ($autoPrint): ?><script>window.addEventListener('load', function () { window.print(); });</script><?php endif; ?>
</head>
<body>
<?php if (! $autoPrint): ?>
  <div class="toolbar"><strong><?= rw_esc($reportTitle) ?> · <?= rw_esc(date('d/m/Y', strtotime($reportDate))) ?></strong><button onclick="window.print()">Cetak</button><button onclick="window.close()">Tutup</button></div>
<?php endif; ?>
  <main class="page">
    <header>
      <h1><?= rw_esc($reportTitle) ?></h1>
      <p>Tanggal kegiatan: <?= rw_esc(date('d/m/Y', strtotime($reportDate))) ?></p>
      <p class="muted">Laporan internal kader. Tidak untuk dipublikasikan tanpa kewenangan.</p>
    </header>
    <div class="summary">
      <div>Total peserta<strong><?= rw_esc((string) count($reportRows)) ?></strong></div>
      <div>Hadir<strong><?= rw_esc((string) $presentCount) ?></strong></div>
      <div>Tidak hadir<strong><?= rw_esc((string) (count($reportRows) - $presentCount)) ?></strong></div>
    </div>
    <h2>Daftar Kehadiran dan Pemantauan</h2>
    <table>
      <thead><tr><th>No.</th><th>Nama Peserta</th><th>Siklus Hidup</th><th>RT</th><th>Status</th><th>Pengukuran</th><th>Tindak Lanjut</th><th>Catatan</th></tr></thead>
      <tbody>
        <?php foreach ($reportRows as $index => $row): ?>
          <tr>
            <td><?= rw_esc((string) ($index + 1)) ?></td>
            <td><strong><?= rw_esc($row['nama']) ?></strong><?= ! empty($row['nama_wali']) ? '<br><small>Wali: ' . rw_esc($row['nama_wali']) . '</small>' : '' ?></td>
            <td><?= rw_esc($lifecycleOptions[$row['kelompok_siklus'] ?? ''] ?? 'Belum ditentukan') ?></td>
            <td><?= rw_esc($row['rt'] ?? '-') ?></td>
            <td><?= ($row['hadir'] ?? '') === 'ya' ? 'Hadir' : 'Tidak hadir' ?></td>
            <td><?= $row['berat_kg'] !== null ? 'BB ' . rw_esc($row['berat_kg']) . ' kg; ' : '' ?><?= $row['tinggi_cm'] !== null ? 'TB ' . rw_esc($row['tinggi_cm']) . ' cm; ' : '' ?><?= $row['lingkar_perut_cm'] !== null ? 'LP ' . rw_esc($row['lingkar_perut_cm']) . ' cm; ' : '' ?><?= $row['tekanan_sistolik'] !== null ? 'TD ' . rw_esc($row['tekanan_sistolik']) . '/' . rw_esc($row['tekanan_diastolik']) . '; ' : '' ?><?= $row['gula_darah'] !== null ? 'Gula ' . rw_esc($row['gula_darah']) : '-' ?></td>
            <td><?= rw_esc($followupOptions[$row['tindak_lanjut'] ?? 'selesai'] ?? '-') ?><?= ! empty($row['tujuan_rujukan']) ? '<br><small>' . rw_esc($row['tujuan_rujukan']) . '</small>' : '' ?></td>
            <td><?= nl2br(rw_esc($row['catatan_kunjungan'] ?? '-')) ?></td>
          </tr>
        <?php endforeach; ?>
        <?php if (empty($reportRows)): ?><tr><td colspan="8">Belum ada peserta aktif pada layanan ini.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </main>
</body>
</html>
