<?= $this->extend('layouts/public') ?>

<?= $this->section('content') ?>
<?php
$activeUnitSummaries = array_values(array_filter(
    $unitSummaries ?? [],
    static fn (array $unit): bool => (int) ($unit['income'] ?? 0) !== 0 || (int) ($unit['expense'] ?? 0) !== 0
));
$overallIncome = array_sum(array_map(static fn (array $unit): int => (int) ($unit['income'] ?? 0), $activeUnitSummaries));
$overallExpense = array_sum(array_map(static fn (array $unit): int => (int) ($unit['expense'] ?? 0), $activeUnitSummaries));
$overallBalance = $overallIncome - $overallExpense;
$transactionCount = count($rows ?? []);
$hasTransactions = $transactionCount > 0;
$lastUpdatedLabel = ! empty($lastUpdatedAt) ? format_date_id($lastUpdatedAt) : '';
$pdfQuery = 'start=' . rawurlencode($selectedStart)
    . '&end=' . rawurlencode($selectedEnd)
    . ($selectedUnit !== '' ? '&unit=' . rawurlencode($selectedUnit) : '')
    . '&export=pdf';
?>

<section class="page-hero finance-page-hero">
  <div class="container page-hero-grid">
    <div data-reveal>
      <p class="eyebrow">Transparansi RW</p>
      <h1>Laporan Keuangan yang Ringkas dan Mudah Dibaca</h1>
      <p class="hero-text">Lihat pemasukan, pengeluaran, dan saldo berdasarkan periode serta unit kas. Rincian baru ditampilkan ketika warga membutuhkannya.</p>
      <div class="hero-actions" aria-label="Aksi laporan keuangan">
        <?php if ($hasTransactions): ?>
          <a href="<?= site_url('keuangan?' . $pdfQuery) ?>" class="btn primary" target="_blank" rel="noopener noreferrer">Unduh PDF</a>
        <?php endif; ?>
        <a href="<?= site_url('aspirasi') ?>" class="btn secondary">Tanyakan ke Pengurus</a>
      </div>
    </div>

    <aside class="page-callout" data-reveal>
      <span>Periode laporan</span>
      <strong><?= rw_esc($monthLabel) ?></strong>
      <p>
        <?= $hasTransactions
            ? rw_esc(($selectedUnit !== '' ? $selectedUnitLabel . ' · ' : '') . $transactionCount . ' transaksi' . ($lastUpdatedLabel !== '' ? ' · diperbarui ' . $lastUpdatedLabel : ''))
            : 'Belum ada transaksi yang diterbitkan untuk pilihan periode ini.' ?>
      </p>
    </aside>
  </div>
</section>

<section class="section white-section finance-overview-section">
  <div class="container">
    <?php if (! empty($error)): ?>
      <div class="alert error"><?= rw_esc($error) ?></div>
    <?php endif; ?>

    <details class="content-block finance-filter-disclosure" data-reveal>
      <summary>
        <span>
          <small>Filter laporan</small>
          <strong><?= rw_esc($monthLabel) ?><?= $selectedUnit !== '' ? rw_esc(' · ' . $selectedUnitLabel) : '' ?></strong>
        </span>
        <em>Ubah periode atau unit</em>
      </summary>
      <form method="get" action="<?= site_url('keuangan') ?>" class="form-grid finance-compact-filter">
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
          <button type="submit" class="btn primary">Tampilkan</button>
          <a href="<?= site_url('keuangan') ?>" class="btn tertiary">Laporan Terbaru</a>
        </div>
      </form>
    </details>

    <?php if (! $hasTransactions): ?>
      <div class="content-block finance-empty-panel" data-reveal>
        <span class="finance-empty-icon" aria-hidden="true">Rp</span>
        <div>
          <p class="eyebrow">Belum diterbitkan</p>
          <h2>Laporan periode ini belum memiliki transaksi.</h2>
          <p>Tidak ada angka nol berulang yang perlu diperiksa. Pilih periode lain atau hubungi pengurus apabila laporan seharusnya sudah tersedia.</p>
        </div>
        <a href="<?= site_url('aspirasi') ?>" class="btn secondary">Kirim Pertanyaan</a>
      </div>
    <?php else: ?>
      <div class="section-title left finance-summary-heading" data-reveal>
        <p class="eyebrow">Ringkasan laporan</p>
        <h2><?= rw_esc($selectedUnitLabel) ?></h2>
        <?php if ($lastUpdatedLabel !== ''): ?>
          <p>Data terakhir pada <?= rw_esc($lastUpdatedLabel) ?>. Gunakan PDF untuk menyimpan laporan periode ini.</p>
        <?php endif; ?>
      </div>

      <div class="finance-summary-grid finance-overview-grid" data-reveal>
        <article class="finance-summary-card finance-overview-card accent-green">
          <span>Total pemasukan</span>
          <strong class="money-value"><?= rw_esc(fmt_currency($overallIncome)) ?></strong>
        </article>
        <article class="finance-summary-card finance-overview-card accent-gold">
          <span>Total pengeluaran</span>
          <strong class="money-value"><?= rw_esc(fmt_currency($overallExpense)) ?></strong>
        </article>
        <article class="finance-summary-card finance-overview-card accent-blue">
          <span>Saldo akhir</span>
          <strong class="money-value"><?= rw_esc(fmt_currency($overallBalance)) ?></strong>
        </article>
        <article class="finance-summary-card finance-overview-card">
          <span>Unit dengan data</span>
          <strong><?= rw_esc((string) count($activeUnitSummaries)) ?></strong>
          <small><?= rw_esc((string) $transactionCount) ?> transaksi pada periode ini</small>
        </article>
      </div>

      <?php if ($activeUnitSummaries): ?>
        <details class="content-block finance-disclosure" data-reveal open>
          <summary>
            <span>
              <small>Ringkasan per unit</small>
              <strong>Hanya unit yang memiliki transaksi</strong>
            </span>
            <em><?= rw_esc((string) count($activeUnitSummaries)) ?> unit</em>
          </summary>
          <div class="finance-summary-grid finance-unit-grid">
            <?php foreach ($activeUnitSummaries as $unit): ?>
              <article class="finance-unit-card">
                <div>
                  <span><?= rw_esc($unit['label']) ?></span>
                  <strong><?= rw_esc(fmt_currency($unit['balance'])) ?></strong>
                </div>
                <p>Masuk <?= rw_esc(fmt_currency($unit['income'])) ?></p>
                <p>Keluar <?= rw_esc(fmt_currency($unit['expense'])) ?></p>
              </article>
            <?php endforeach; ?>
          </div>
        </details>
      <?php endif; ?>

      <details class="content-block finance-disclosure finance-transaction-disclosure" data-reveal>
        <summary>
          <span>
            <small>Rincian transaksi</small>
            <strong>Lihat tanggal, kategori, dan nominal</strong>
          </span>
          <em><?= rw_esc((string) $transactionCount) ?> transaksi</em>
        </summary>
        <div class="finance-transaction-list">
          <?php foreach ($rows as $row): ?>
            <?php
            $scope = (string) ($row['lingkup'] ?? 'rw');
            $unitLabel = $scope === 'rt'
                ? 'RT ' . normalize_rt_code($row['rt'] ?? '')
                : keuangan_scope_summary_label($scope);
            $isIncome = ($row['jenis'] ?? '') === 'pemasukan';
            ?>
            <article class="finance-transaction-item">
              <div class="finance-transaction-main">
                <span class="letter-status-pill status-<?= $isIncome ? 'selesai' : 'menunggu' ?>"><?= rw_esc(keuangan_type_label($row['jenis'] ?? '')) ?></span>
                <div>
                  <strong><?= rw_esc($row['kategori'] ?? 'Transaksi') ?></strong>
                  <small><?= rw_esc($unitLabel) ?> · <?= rw_esc(fmt_date($row['tanggal'] ?? '')) ?></small>
                </div>
              </div>
              <strong class="finance-transaction-value <?= $isIncome ? 'is-income' : 'is-expense' ?>"><?= $isIncome ? '+' : '-' ?><?= rw_esc(fmt_currency($row['nominal'] ?? 0)) ?></strong>
              <?php if (! empty($row['keterangan'])): ?>
                <p><?= nl2br(rw_esc($row['keterangan'])) ?></p>
              <?php endif; ?>
            </article>
          <?php endforeach; ?>
        </div>
      </details>
    <?php endif; ?>

    <div class="finance-privacy-note" data-reveal>
      <strong>Catatan keterbukaan</strong>
      <p>Laporan publik menampilkan kategori dan jumlah transaksi. Data pribadi warga serta informasi rekening tidak ditampilkan.</p>
    </div>
  </div>
</section>
<?= $this->endSection() ?>
