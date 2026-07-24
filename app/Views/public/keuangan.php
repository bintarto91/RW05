<?= $this->extend('layouts/public') ?>

<?= $this->section('content') ?>
<?php
$financeChartRows = [];
foreach ($unitSummaries as $unit) {
    $financeChartRows[] = ['label' => $unit['label'] . ' Masuk', 'value' => (float) ($unit['income'] ?? 0), 'tone' => 'income'];
    $financeChartRows[] = ['label' => $unit['label'] . ' Keluar', 'value' => (float) ($unit['expense'] ?? 0), 'tone' => 'expense'];
}
$chartMax = 1;
foreach ($financeChartRows as $row) {
    $chartMax = max($chartMax, (float) $row['value']);
}
?>
<section class="page-hero">
  <div class="container page-hero-grid">
    <div data-reveal>
      <p class="eyebrow">Transparansi RW</p>
      <h1>Laporan Keuangan RW dan Unit Lingkungan</h1>
      <p class="hero-text">Warga dapat melihat pemasukan, pengeluaran, dan saldo kas RW, RT, Panitia, DKM, Karang Taruna, Posyandu, serta Posbindu secara terpisah.</p>
      <div class="hero-actions" aria-label="Aksi laporan keuangan">
        <a href="<?= site_url('keuangan?start=' . rawurlencode($selectedStart) . '&end=' . rawurlencode($selectedEnd) . ($selectedUnit !== '' ? '&unit=' . rawurlencode($selectedUnit) : '') . '&export=pdf') ?>" class="btn primary" target="_blank" rel="noopener noreferrer">Unduh PDF</a>
        <a href="<?= site_url('aspirasi') ?>" class="btn secondary">Kirim Pertanyaan</a>
      </div>
    </div>

    <aside class="page-callout" data-reveal>
      <span>Periode aktif</span>
      <strong><?= rw_esc($monthLabel) ?></strong>
      <p><?= $selectedUnit !== '' ? rw_esc('Filter ' . $selectedUnitLabel . ' aktif. Data yang tampil hanya mengikuti unit kas ini.') : 'Setiap kas lingkungan ditampilkan terpisah agar mudah dibaca.' ?></p>
    </aside>
  </div>
</section>

<section class="section white-section">
  <div class="container">
    <?php if ($error): ?>
      <div class="alert error"><?= rw_esc($error) ?></div>
    <?php endif; ?>

    <div class="content-block finance-public-filter" data-reveal>
      <div class="section-title compact">
        <p class="eyebrow">Filter laporan</p>
        <h2>Pilih tanggal dan unit kas</h2>
      </div>
      <form method="get" action="<?= site_url('keuangan') ?>" class="form-grid">
        <label>Dari Tanggal
          <input type="date" name="start" value="<?= rw_esc($selectedStart) ?>">
        </label>
        <label>Sampai Tanggal
          <input type="date" name="end" value="<?= rw_esc($selectedEnd) ?>">
        </label>
        <label>Unit Kas
          <select name="unit">
            <?php foreach ($unitOptions as $value => $label): ?>
              <option value="<?= rw_esc($value) ?>" <?= is_selected($selectedUnit, $value) ?>><?= rw_esc($label) ?></option>
            <?php endforeach; ?>
          </select>
        </label>
        <div class="full finance-public-actions">
          <button type="submit" class="btn primary">Tampilkan Laporan</button>
          <a href="<?= site_url('keuangan') ?>" class="btn tertiary">Reset</a>
        </div>
      </form>
    </div>

    <div class="section-title compact finance-separated-head" data-reveal>
      <p class="eyebrow">Saldo per unit</p>
      <h2><?= rw_esc($selectedUnitLabel) ?></h2>
    </div>
    <div class="finance-summary-grid" data-reveal>
      <?php foreach ($unitSummaries as $unit): ?>
        <article class="finance-summary-card">
          <span><?= rw_esc($unit['label']) ?></span>
          <strong class="money-value"><?= rw_esc(fmt_currency($unit['balance'])) ?></strong>
          <div class="money-breakdown" aria-label="Rincian <?= rw_esc($unit['label']) ?>">
            <span><b>Masuk</b><em><?= rw_esc(fmt_currency($unit['income'])) ?></em></span>
            <span><b>Keluar</b><em><?= rw_esc(fmt_currency($unit['expense'])) ?></em></span>
          </div>
        </article>
      <?php endforeach; ?>
    </div>

    <div class="content-block finance-chart-card" data-reveal>
      <div class="section-title compact">
        <p class="eyebrow">Grafik keuangan</p>
        <h2>Uang masuk dan keluar</h2>
        <p>Perbandingan pemasukan dan pengeluaran unit kas pada periode <?= rw_esc($monthLabel) ?>.</p>
      </div>
      <div class="finance-bar-chart">
        <?php foreach ($financeChartRows as $row): ?>
          <?php $width = max(3, min(100, round(($row['value'] / $chartMax) * 100))); ?>
          <div class="finance-bar-row">
            <span><?= rw_esc($row['label']) ?></span>
            <div class="finance-bar-track" aria-label="<?= rw_esc($row['label']) ?> <?= rw_esc(fmt_currency($row['value'])) ?>">
              <i class="finance-bar finance-bar-<?= rw_esc($row['tone']) ?>" style="width: <?= rw_esc((string) $width) ?>%"></i>
            </div>
            <strong><?= rw_esc(fmt_currency($row['value'])) ?></strong>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</section>

<?php
$showRwDetail = $selectedUnit === '' || $selectedUnit === 'rw';
$showRtDetail = $selectedUnit === '' || $selectedRt !== '';
$showPanitiaDetail = $selectedUnit === '' || $selectedUnit === 'panitia';
$selectedDirectScope = keuangan_unit_filter_scope($selectedUnit);
$showOtherUnitDetail = $selectedDirectScope !== '' && ! in_array($selectedDirectScope, ['rw', 'panitia'], true);
?>
<section class="section">
  <div class="container split-stack finance-public-layout">
    <?php if ($showRtDetail): ?>
    <div class="stack-column finance-public-card" data-reveal>
      <div class="section-title left">
        <p class="eyebrow">Kas RT</p>
        <h2>Rekap saldo per RT</h2>
      </div>
      <?php if (! empty($rtSummaries)): ?>
        <div class="finance-rt-list">
          <?php foreach ($rtSummaries as $row): ?>
            <article class="finance-rt-item">
              <strong>RT <?= rw_esc(normalize_rt_code($row['rt'])) ?></strong>
              <span>Masuk <?= rw_esc(fmt_currency($row['total_pemasukan'])) ?></span>
              <small>Keluar <?= rw_esc(fmt_currency($row['total_pengeluaran'])) ?> | Saldo <?= rw_esc(fmt_currency($row['saldo'])) ?></small>
            </article>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <p class="empty-state">Belum ada data kas RT pada periode ini.</p>
      <?php endif; ?>
    </div>
    <?php endif; ?>

    <?php if ($showRwDetail): ?>
    <div class="stack-column finance-public-card" data-reveal>
      <div class="section-title left">
        <p class="eyebrow">Keuangan RW</p>
        <h2>Pemasukan kas umum RW</h2>
      </div>
      <?php if (! empty($rwIncomeRows)): ?>
        <div class="finance-rt-list">
          <?php foreach ($rwIncomeRows as $row): ?>
            <article class="finance-income-public-item">
              <strong><?= rw_esc($row['kategori']) ?></strong>
              <span><?= rw_esc(fmt_date($row['tanggal'])) ?> | <?= rw_esc(fmt_currency($row['nominal'])) ?></span>
              <?php if (! empty($row['keterangan'])): ?>
                <small><?= nl2br(rw_esc($row['keterangan'])) ?></small>
              <?php endif; ?>
            </article>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <p class="empty-state">Belum ada pemasukan RW pada periode ini.</p>
      <?php endif; ?>
    </div>
    <?php endif; ?>

    <?php if ($showPanitiaDetail): ?>
    <div class="stack-column finance-public-card" data-reveal>
      <div class="section-title left">
        <p class="eyebrow">Panitia</p>
        <h2>Kas Panitia Agustusan</h2>
      </div>
      <div class="finance-summary-grid">
        <article class="finance-summary-card">
          <span>Pemasukan</span>
          <strong class="money-value"><?= rw_esc(fmt_currency($summary['panitiaIncome'])) ?></strong>
        </article>
        <article class="finance-summary-card accent-gold">
          <span>Pengeluaran</span>
          <strong class="money-value"><?= rw_esc(fmt_currency($summary['panitiaExpense'])) ?></strong>
        </article>
        <article class="finance-summary-card accent-green">
          <span>Saldo</span>
          <strong class="money-value"><?= rw_esc(fmt_currency($summary['panitiaBalance'])) ?></strong>
        </article>
      </div>
      <?php if (! empty($panitiaRows)): ?>
        <div class="finance-rt-list">
          <?php foreach ($panitiaRows as $row): ?>
            <article class="finance-income-public-item">
              <strong><?= rw_esc($row['kategori']) ?></strong>
              <span><?= rw_esc(fmt_date($row['tanggal'])) ?> | <?= rw_esc(keuangan_type_label($row['jenis'] ?? '')) ?> | <?= rw_esc(fmt_currency($row['nominal'])) ?></span>
              <?php if (! empty($row['keterangan'])): ?>
                <small><?= nl2br(rw_esc($row['keterangan'])) ?></small>
              <?php endif; ?>
            </article>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <p class="empty-state">Belum ada transaksi Panitia Agustusan pada periode ini.</p>
      <?php endif; ?>
    </div>
    <?php endif; ?>

    <?php if ($showOtherUnitDetail): ?>
    <div class="stack-column finance-public-card" data-reveal>
      <div class="section-title left">
        <p class="eyebrow">Unit Lingkungan</p>
        <h2>Transaksi <?= rw_esc($selectedUnitLabel) ?></h2>
      </div>
      <?php if (! empty($rows)): ?>
        <div class="finance-rt-list">
          <?php foreach ($rows as $row): ?>
            <article class="finance-income-public-item">
              <strong><?= rw_esc($row['kategori']) ?></strong>
              <span><?= rw_esc(fmt_date($row['tanggal'])) ?> | <?= rw_esc(keuangan_type_label($row['jenis'] ?? '')) ?> | <?= rw_esc(fmt_currency($row['nominal'])) ?></span>
              <?php if (! empty($row['keterangan'])): ?>
                <small><?= nl2br(rw_esc($row['keterangan'])) ?></small>
              <?php endif; ?>
            </article>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <p class="empty-state">Belum ada transaksi <?= rw_esc($selectedUnitLabel) ?> pada periode ini.</p>
      <?php endif; ?>
    </div>
    <?php endif; ?>
  </div>
</section>

<?php if ($showRwDetail): ?>
<section class="section white-section">
  <div class="container" data-reveal>
    <div class="section-title left">
      <p class="eyebrow">Riwayat RW</p>
      <h2>Transaksi RW umum periode <?= rw_esc($monthLabel) ?></h2>
    </div>
    <div class="content-block finance-public-table-wrap">
      <table class="finance-public-table">
        <thead>
          <tr>
            <th>Tanggal</th>
            <th>Jenis</th>
            <th>Kategori</th>
            <th>Nominal</th>
            <th>Keterangan</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($rwRows as $row): ?>
            <tr>
              <td><?= rw_esc(fmt_date($row['tanggal'] ?? '')) ?></td>
              <td><span class="letter-status-pill status-<?= rw_esc(($row['jenis'] ?? '') === 'pemasukan' ? 'selesai' : 'menunggu') ?>"><?= rw_esc(keuangan_type_label($row['jenis'] ?? '')) ?></span></td>
              <td><?= rw_esc($row['kategori'] ?? '') ?></td>
              <td><?= rw_esc(fmt_currency($row['nominal'] ?? 0)) ?></td>
              <td><?= nl2br(rw_esc($row['keterangan'] ?? '')) ?></td>
            </tr>
          <?php endforeach; ?>
          <?php if (empty($rwRows)): ?>
            <tr><td colspan="5" class="empty-cell">Belum ada transaksi RW pada periode ini.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</section>
<?php endif; ?>

<?php if ($showRtDetail): ?>
<section class="section white-section">
  <div class="container" data-reveal>
    <div class="section-title left">
      <p class="eyebrow">Riwayat RT</p>
      <h2>Transaksi kas RT <?= $selectedRt !== '' ? rw_esc('RT ' . $selectedRt) : rw_esc($monthLabel) ?></h2>
    </div>
    <div class="content-block finance-public-table-wrap">
      <table class="finance-public-table">
        <thead>
          <tr>
            <th>Tanggal</th>
            <th>RT</th>
            <th>Jenis</th>
            <th>Kategori</th>
            <th>Nominal</th>
            <th>Keterangan</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($rtRows as $row): ?>
            <tr>
              <td><?= rw_esc(fmt_date($row['tanggal'] ?? '')) ?></td>
              <td><?= ! empty($row['rt']) ? rw_esc('RT ' . normalize_rt_code($row['rt'])) : '-' ?></td>
              <td><span class="letter-status-pill status-<?= rw_esc(($row['jenis'] ?? '') === 'pemasukan' ? 'selesai' : 'menunggu') ?>"><?= rw_esc(keuangan_type_label($row['jenis'] ?? '')) ?></span></td>
              <td><?= rw_esc($row['kategori'] ?? '') ?></td>
              <td><?= rw_esc(fmt_currency($row['nominal'] ?? 0)) ?></td>
              <td><?= nl2br(rw_esc($row['keterangan'] ?? '')) ?></td>
            </tr>
          <?php endforeach; ?>
          <?php if (empty($rtRows)): ?>
            <tr><td colspan="6" class="empty-cell">Belum ada transaksi kas RT pada periode ini.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</section>
<?php endif; ?>
<?= $this->endSection() ?>
