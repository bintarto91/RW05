<?php
$reportTitle = 'Laporan Data Warga RW 05';
$rtLabel = ($filters['rt'] ?? '') !== '' ? 'RT ' . $filters['rt'] : 'Semua RT';
$statusLabel = ($filters['status_tinggal'] ?? '') !== '' ? warga_option_label($statusOptions, $filters['status_tinggal']) : 'Semua status tinggal';
$kesejahteraanLabel = ($filters['kategori_kesejahteraan'] ?? '') !== '' ? warga_option_label($kesejahteraanOptions, $filters['kategori_kesejahteraan']) : 'Semua kategori kesejahteraan';
$bantuanLabel = ($filters['penerima_bantuan'] ?? '') !== '' ? warga_option_label($bantuanOptions, $filters['penerima_bantuan']) : 'Semua status bantuan';
$showDetailedRows = (bool) ($showDetailedRows ?? false);
$autoPrint = (bool) ($autoPrint ?? false);
$isPreview = (bool) ($isPreview ?? false);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title><?= rw_esc($reportTitle) ?></title>
  <style>
    * { box-sizing: border-box; }
    body {
      margin: 0;
      color: #1f2937;
      font-family: DejaVu Sans, Arial, sans-serif;
      font-size: 11px;
      line-height: 1.45;
    }
    .page {
      padding: 22px 24px 28px;
    }
    .header {
      margin-bottom: 14px;
      padding-bottom: 10px;
      border-bottom: 3px double #1f2937;
    }
    h1, h2 {
      margin: 0;
      color: #0f3b2c;
    }
    h1 {
      font-size: 19px;
      margin-bottom: 6px;
    }
    h2 {
      font-size: 13px;
      margin-bottom: 8px;
    }
    p {
      margin: 4px 0;
    }
    .filter-table,
    .summary-grid,
    .data-table {
      width: 100%;
      border-collapse: collapse;
    }
    .filter-table td,
    .summary-grid td,
    .data-table th,
    .data-table td {
      border: 1px solid #d1d5db;
      vertical-align: top;
    }
    .filter-table td,
    .summary-grid td {
      padding: 8px 10px;
    }
    .summary-grid {
      margin: 12px 0 16px;
    }
    .summary-grid td {
      width: 25%;
    }
    .summary-grid strong {
      display: block;
      margin-top: 6px;
      font-size: 15px;
    }
    .section {
      margin-top: 16px;
    }
    .data-table {
      margin-top: 8px;
    }
    .data-table th,
    .data-table td {
      padding: 7px 6px;
      text-align: left;
    }
    .data-table th {
      background: #eef4ef;
      font-size: 10px;
      text-transform: uppercase;
    }
    .muted {
      color: #4b5563;
    }
    .nowrap {
      white-space: nowrap;
    }
    .preview-toolbar {
      position: sticky;
      top: 0;
      z-index: 100;
      display: flex;
      gap: 8px;
      align-items: center;
      background: #0b2f24;
      color: #fff;
      padding: 12px 16px;
      font-size: 12px;
      box-shadow: 0 8px 22px rgba(15, 59, 44, 0.18);
    }
    .preview-toolbar span {
      flex: 1;
      font-weight: 600;
    }
    .preview-toolbar a,
    .preview-toolbar button {
      display: inline-block;
      padding: 9px 14px;
      border-radius: 8px;
      font-size: 12px;
      font-weight: 600;
      cursor: pointer;
      text-decoration: none;
      border: none;
    }
    .btn-cetak {
      background: #f4d181;
      color: #0b2f24;
      font-weight: 800;
    }
    .btn-excel {
      background: #217346;
      color: #fff;
    }
    .btn-pdf {
      background: #b91c1c;
      color: #fff;
    }
    .btn-tutup {
      background: transparent;
      color: #d1fae5;
      border: 1px solid #4ade80 !important;
    }
    @media print {
      .preview-toolbar { display: none !important; }
    }
  </style>
<?php if ($autoPrint): ?>
  <script>window.addEventListener('load', function () { window.print(); });</script>
<?php endif; ?>
</head>
<body>
<?php if ($isPreview): ?>
  <div class="preview-toolbar">
    <span>Preview: <?= rw_esc($reportTitle) ?> — <?= rw_esc($rtLabel) ?></span>
    <button class="btn-cetak" onclick="window.print()">Cetak Sekarang</button>
    <?php if (! empty($pdfUrl ?? '')): ?>
      <a class="btn-pdf" href="<?= rw_esc($pdfUrl) ?>">Download PDF</a>
    <?php endif; ?>
    <button class="btn-tutup" onclick="window.close()">Tutup</button>
  </div>
<?php endif; ?>
  <main class="page">
    <header class="header">
      <h1><?= rw_esc($reportTitle) ?></h1>
      <p>Dicetak pada: <?= rw_esc(date('d/m/Y H:i')) ?></p>
      <p class="muted">Laporan ini memuat rekap keluarga, kondisi sosial ekonomi, dan status bantuan sesuai filter yang aktif di panel admin.</p>
    </header>

    <table class="filter-table">
      <tr>
        <td><strong>Filter RT</strong><br><?= rw_esc($rtLabel) ?></td>
        <td><strong>Status Tinggal</strong><br><?= rw_esc($statusLabel) ?></td>
        <td><strong>Kesejahteraan</strong><br><?= rw_esc($kesejahteraanLabel) ?></td>
        <td><strong>Penerima Bantuan</strong><br><?= rw_esc($bantuanLabel) ?></td>
      </tr>
    </table>

    <table class="summary-grid">
      <tr>
        <td>Total KK<strong><?= rw_esc((string) ($summary['totalKk'] ?? 0)) ?></strong></td>
        <td>Total Warga<strong><?= rw_esc((string) ($summary['totalWarga'] ?? 0)) ?> jiwa</strong></td>
        <td>Kurang Mampu<strong><?= rw_esc((string) ($summary['kurangMampu'] ?? 0)) ?> KK</strong></td>
        <td>Penerima Bantuan<strong><?= rw_esc((string) ($summary['penerimaBantuan'] ?? 0)) ?> KK</strong></td>
      </tr>
    </table>

    <section class="section">
      <h2><?= $showDetailedRows ? 'Daftar Keluarga' : 'Rekap per RT' ?></h2>
      <?php if (! $showDetailedRows): ?>
        <p class="muted">Rekap berikut dipakai bila laporan dicetak dalam mode ringkas.</p>
        <table class="data-table">
          <thead>
            <tr>
              <th>No</th>
              <th>RT</th>
              <th>Total KK</th>
              <th>Total Warga</th>
              <th>Kurang Mampu</th>
              <th>Penerima Bantuan</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($rtSummaries as $index => $row): ?>
              <tr>
                <td class="nowrap"><?= rw_esc((string) ($index + 1)) ?></td>
                <td><?= $row['rt'] !== '-' ? rw_esc('RT ' . $row['rt']) : '-' ?></td>
                <td><?= rw_esc((string) ($row['totalKk'] ?? 0)) ?></td>
                <td><?= rw_esc((string) ($row['totalWarga'] ?? 0)) ?> jiwa</td>
                <td><?= rw_esc((string) ($row['kurangMampu'] ?? 0)) ?> KK</td>
                <td><?= rw_esc((string) ($row['penerimaBantuan'] ?? 0)) ?> KK</td>
              </tr>
            <?php endforeach; ?>
            <?php if (empty($rtSummaries)): ?>
              <tr><td colspan="6">Belum ada data warga pada filter ini.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      <?php else: ?>
        <table class="data-table">
          <thead>
            <tr>
              <th>No</th>
              <th>Profil KK</th>
              <th>Kontak</th>
              <th>Pekerjaan</th>
              <th>Kesejahteraan</th>
              <th>Bantuan</th>
              <th>Catatan</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($rows as $index => $row): ?>
              <tr>
                <td class="nowrap"><?= rw_esc((string) ($index + 1)) ?></td>
                <td>
                  <strong><?= rw_esc($row['nama_kepala_keluarga'] ?? '') ?></strong><br>
                  <span class="nowrap">RT <?= rw_esc(normalize_rt_code($row['rt'] ?? '')) ?></span> | <?= rw_esc((string) ($row['jumlah_anggota'] ?? 0)) ?> jiwa<br>
                  <span class="muted"><?= rw_esc($row['alamat'] ?: '-') ?></span>
                </td>
                <td>
                  <?= rw_esc($row['no_hp'] ?: '-') ?><br>
                  <span class="muted">Status tinggal: <?= rw_esc(warga_option_label($statusOptions, $row['status_tinggal'] ?? '')) ?></span>
                </td>
                <td><?= rw_esc($row['pekerjaan_kepala_keluarga'] ?: '-') ?></td>
                <td><?= rw_esc(warga_option_label($kesejahteraanOptions, $row['kategori_kesejahteraan'] ?? '')) ?></td>
                <td>
                  <?= rw_esc(warga_option_label($bantuanOptions, $row['penerima_bantuan'] ?? '')) ?><br>
                  <span class="muted"><?= rw_esc($row['jenis_bantuan'] ?: '-') ?></span>
                </td>
                <td>
                  <?= nl2br(rw_esc($row['kondisi_khusus'] ?: '-')) ?>
                  <?php if (! empty($row['keterangan'])): ?>
                    <br><span class="muted"><?= nl2br(rw_esc($row['keterangan'])) ?></span>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
            <?php if (empty($rows)): ?>
              <tr><td colspan="7">Belum ada data warga pada filter ini.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </section>
  </main>
</body>
</html>
