<?php
$reportTitle = 'Laporan Keuangan RW 05';
$filterLabel = $selectedUnitLabel ?? 'Semua Unit Kas';
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title><?= rw_esc($reportTitle) ?></title>
  <style>
    * { box-sizing: border-box; }
    body { margin: 0; color: #1f2937; font-family: DejaVu Sans, Arial, sans-serif; font-size: 12px; line-height: 1.5; }
    .page { padding: 26px 30px 34px; }
    .header { margin-bottom: 18px; padding-bottom: 12px; border-bottom: 3px double #1f2937; }
    h1, h2 { margin: 0; color: #0f3b2c; }
    h1 { font-size: 20px; margin-bottom: 6px; }
    h2 { font-size: 14px; margin-bottom: 8px; }
    p { margin: 4px 0; }
    .summary-grid { width: 100%; margin: 14px 0 18px; border-collapse: collapse; }
    .summary-grid td { width: 33.33%; padding: 10px; border: 1px solid #d1d5db; vertical-align: top; }
    .summary-grid strong { display: block; margin-top: 6px; font-size: 15px; }
    table { width: 100%; border-collapse: collapse; margin-top: 8px; }
    th, td { padding: 8px 7px; border: 1px solid #d1d5db; vertical-align: top; text-align: left; }
    th { background: #eef4ef; font-size: 11px; text-transform: uppercase; }
    .section { margin-top: 18px; }
    .note { color: #4b5563; }
  </style>
</head>
<body>
  <main class="page">
    <header class="header">
      <h1><?= rw_esc($reportTitle) ?></h1>
      <p>Periode: <?= rw_esc($monthLabel) ?></p>
      <p>Filter Unit Kas: <?= rw_esc($filterLabel) ?></p>
      <p class="note">Laporan publik ini menampilkan kas RW, kas per RT, dan unit lingkungan secara terpisah.</p>
    </header>
    <table class="summary-grid">
      <tr>
        <td>Pemasukan RW<strong><?= rw_esc(fmt_currency($summary['rwIncome'])) ?></strong></td>
        <td>Pengeluaran RW<strong><?= rw_esc(fmt_currency($summary['rwExpense'])) ?></strong></td>
        <td>Saldo RW<strong><?= rw_esc(fmt_currency($summary['rwBalance'])) ?></strong></td>
      </tr>
      <tr>
        <td>Pemasukan RT<strong><?= rw_esc(fmt_currency($summary['rtIncome'])) ?></strong></td>
        <td>Pengeluaran RT<strong><?= rw_esc(fmt_currency($summary['rtExpense'])) ?></strong></td>
        <td>Saldo RT<strong><?= rw_esc(fmt_currency($summary['rtBalance'])) ?></strong></td>
      </tr>
      <tr>
        <td>Pemasukan Panitia<strong><?= rw_esc(fmt_currency($summary['panitiaIncome'])) ?></strong></td>
        <td>Pengeluaran Panitia<strong><?= rw_esc(fmt_currency($summary['panitiaExpense'])) ?></strong></td>
        <td>Saldo Panitia<strong><?= rw_esc(fmt_currency($summary['panitiaBalance'])) ?></strong></td>
      </tr>
    </table>
    <section class="section">
      <h2>Saldo per Unit Kas</h2>
      <table>
        <thead><tr><th>Unit Kas</th><th>Pemasukan</th><th>Pengeluaran</th><th>Saldo</th></tr></thead>
        <tbody>
          <?php foreach ($unitSummaries as $row): ?>
            <tr>
              <td><?= rw_esc($row['label']) ?></td>
              <td><?= rw_esc(fmt_currency($row['income'])) ?></td>
              <td><?= rw_esc(fmt_currency($row['expense'])) ?></td>
              <td><?= rw_esc(fmt_currency($row['balance'])) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </section>
    <section class="section">
      <h2>Pemasukan RW</h2>
      <table>
        <thead><tr><th>Tanggal</th><th>Kategori</th><th>Nominal</th><th>Keterangan</th></tr></thead>
        <tbody>
          <?php foreach ($rwIncomeRows as $row): ?>
            <tr>
              <td><?= rw_esc(fmt_date($row['tanggal'])) ?></td>
              <td><?= rw_esc($row['kategori']) ?></td>
              <td><?= rw_esc(fmt_currency($row['nominal'])) ?></td>
              <td><?= nl2br(rw_esc($row['keterangan'] ?? '')) ?></td>
            </tr>
          <?php endforeach; ?>
          <?php if (empty($rwIncomeRows)): ?><tr><td colspan="4">Belum ada pemasukan RW pada periode ini.</td></tr><?php endif; ?>
        </tbody>
      </table>
    </section>
  </main>
</body>
</html>
