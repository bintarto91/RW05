<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<h1>Keuangan RW dan Unit Lingkungan</h1>
<p class="muted">Catat pemasukan dan pengeluaran berdasarkan unit kas: RW umum, RT, Panitia Agustusan, DKM, Karang Taruna, Posyandu, dan Posbindu.</p>

<?php if ($success): ?>
  <div class="alert success"><?= rw_esc($success) ?></div>
<?php endif; ?>
<?php if ($error): ?>
  <div class="alert error"><?= rw_esc($error) ?></div>
<?php endif; ?>

<section class="panel compact-panel">
  <div class="section-heading compact-heading">
    <div>
      <h2>Periode Laporan</h2>
      <p class="muted">Pilih rentang tanggal untuk melihat saldo, pemasukan, dan pengeluaran setiap unit kas.</p>
    </div>
  </div>

  <form method="get" action="<?= site_url('admin/keuangan') ?>" class="grid-form finance-filter-form">
    <label>Dari Tanggal
      <input type="date" name="start" value="<?= rw_esc($selectedStart) ?>">
    </label>
    <label>Sampai Tanggal
      <input type="date" name="end" value="<?= rw_esc($selectedEnd) ?>">
    </label>
    <label>Filter Unit Kas
      <select name="unit">
        <?php foreach ($unitOptions as $value => $label): ?>
          <option value="<?= rw_esc($value) ?>" <?= is_selected($selectedUnit, $value) ?>><?= rw_esc($label) ?></option>
        <?php endforeach; ?>
      </select>
    </label>
    <div class="full finance-filter-toolbar">
      <div class="finance-filter-actions">
        <button type="submit">Tampilkan</button>
        <a class="btn-light" href="<?= site_url('admin/keuangan') ?>">Reset</a>
        <a class="btn-light" href="<?= site_url('admin/keuangan?start=' . rawurlencode($selectedStart) . '&end=' . rawurlencode($selectedEnd) . ($selectedUnit !== '' ? '&unit=' . rawurlencode($selectedUnit) : '') . '&export=csv') ?>">Export CSV</a>
        <a class="btn-light" href="<?= site_url('admin/keuangan?start=' . rawurlencode($selectedStart) . '&end=' . rawurlencode($selectedEnd) . ($selectedUnit !== '' ? '&unit=' . rawurlencode($selectedUnit) : '') . '&export=pdf') ?>" target="_blank" rel="noopener noreferrer">Export PDF</a>
      </div>
    </div>
  </form>
</section>

<?php if ($selectedUnit !== ''): ?>
  <div class="alert warning">Filter aktif: <strong><?= rw_esc($selectedUnitLabel) ?></strong>. Ringkasan dan riwayat di bawah hanya mengikuti unit kas ini.</div>
<?php endif; ?>

<div class="stat-grid finance-stats-grid">
  <?php foreach ($unitSummaries as $index => $unit): ?>
    <article class="stat dashboard-stat finance-money-card <?= $index % 3 === 0 ? 'stat-letter' : ($index % 3 === 1 ? 'stat-attention' : 'stat-people') ?>">
      <span><?= rw_esc($unit['label']) ?></span>
      <strong class="money-value"><?= rw_esc(fmt_currency($unit['balance'])) ?></strong>
      <div class="money-breakdown" aria-label="Rincian <?= rw_esc($unit['label']) ?>">
        <span><b>Masuk</b><em><?= rw_esc(fmt_currency($unit['income'])) ?></em></span>
        <span><b>Keluar</b><em><?= rw_esc(fmt_currency($unit['expense'])) ?></em></span>
      </div>
    </article>
  <?php endforeach; ?>
</div>

<section class="panel">
  <h2><?= $edit ? 'Edit Transaksi' : 'Tambah Transaksi' ?></h2>
  <form method="post" action="<?= site_url('admin/keuangan?start=' . rawurlencode($selectedStart) . '&end=' . rawurlencode($selectedEnd) . ($selectedUnit !== '' ? '&unit=' . rawurlencode($selectedUnit) : '')) ?>" class="grid-form">
    <input type="hidden" name="id" value="<?= rw_esc($edit['id'] ?? '') ?>">

    <label>Tanggal
      <input type="date" name="tanggal" value="<?= rw_esc($edit['tanggal'] ?? $selectedStart) ?>" required>
    </label>

    <label>Unit Kas
      <?php $selectedLingkup = $edit['lingkup'] ?? 'rw'; ?>
      <select name="lingkup" id="financeScopeSelect" required>
        <?php foreach (keuangan_scope_options() as $value => $label): ?>
          <option value="<?= rw_esc($value) ?>" <?= is_selected($selectedLingkup, $value) ?>><?= rw_esc($label) ?></option>
        <?php endforeach; ?>
      </select>
    </label>

    <label id="financeRtField">RT
      <?php $selectedFormRt = normalize_rt_code($edit['rt'] ?? ''); ?>
      <select name="rt" id="financeRtSelect">
        <option value="">Pilih RT</option>
        <?php foreach ($rtOptions as $value => $label): ?>
          <option value="<?= rw_esc($value) ?>" <?= is_selected($selectedFormRt, $value) ?>><?= rw_esc($label) ?></option>
        <?php endforeach; ?>
      </select>
      <small id="financeRtHint">Pilih RT hanya kalau Unit Kas = Kas RT.</small>
    </label>

    <label>Jenis Transaksi
      <?php $selectedJenis = $edit['jenis'] ?? 'pemasukan'; ?>
      <select name="jenis" required>
        <?php foreach (keuangan_type_options() as $value => $label): ?>
          <option value="<?= rw_esc($value) ?>" <?= is_selected($selectedJenis, $value) ?>><?= rw_esc($label) ?></option>
        <?php endforeach; ?>
      </select>
    </label>

    <label>Kategori
      <input type="text" name="kategori" value="<?= rw_esc($edit['kategori'] ?? '') ?>" placeholder="Contoh: Iuran warga, operasional, konsumsi rapat" required>
    </label>

    <label>Nominal
      <input type="number" name="nominal" min="1" step="1" value="<?= rw_esc($edit['nominal'] ?? '') ?>" placeholder="500000" required>
    </label>

    <label class="full">Keterangan
      <textarea name="keterangan" rows="4" placeholder="Catatan transaksi, sumber dana, atau penggunaan dana."><?= rw_esc($edit['keterangan'] ?? '') ?></textarea>
    </label>

    <div class="full form-actions">
      <button type="submit"><?= $edit ? 'Update Transaksi' : 'Simpan Transaksi' ?></button>
      <?php if ($edit): ?>
        <a class="btn-light" href="<?= site_url('admin/keuangan?start=' . rawurlencode($selectedStart) . '&end=' . rawurlencode($selectedEnd) . ($selectedUnit !== '' ? '&unit=' . rawurlencode($selectedUnit) : '')) ?>">Batal</a>
      <?php endif; ?>
    </div>
  </form>
</section>

<?php
$showRwDetail = $selectedUnit === '' || $selectedUnit === 'rw';
$showRtDetail = $selectedUnit === '' || $selectedRt !== '';
$showPanitiaDetail = $selectedUnit === '' || $selectedUnit === 'panitia';
?>
<div class="dashboard-layout dashboard-layout-wide finance-layout">
  <?php if ($showRtDetail): ?>
  <section class="dashboard-card">
    <div class="section-heading">
      <div>
        <p class="admin-kicker">Rekap RT</p>
        <h2>Kas per RT</h2>
      </div>
    </div>

    <?php if (! empty($rtSummaries)): ?>
      <div class="rt-list finance-rt-list">
        <?php foreach ($rtSummaries as $row): ?>
          <div class="rt-row finance-rt-row">
            <strong>RT <?= rw_esc(normalize_rt_code($row['rt'])) ?></strong>
            <span>Masuk <?= rw_esc(fmt_currency($row['total_pemasukan'])) ?></span>
            <small>Keluar <?= rw_esc(fmt_currency($row['total_pengeluaran'])) ?> | Saldo <?= rw_esc(fmt_currency($row['saldo'])) ?></small>
          </div>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <p class="empty-state">Belum ada transaksi kas RT pada periode ini.</p>
    <?php endif; ?>
  </section>
  <?php endif; ?>

  <?php if ($showRwDetail): ?>
  <section class="dashboard-card">
    <div class="section-heading">
      <div>
        <p class="admin-kicker">Pemasukan RW</p>
        <h2>Daftar Pemasukan RW</h2>
      </div>
    </div>

    <?php if (! empty($rwIncomeRows)): ?>
      <div class="finance-income-list">
        <?php foreach ($rwIncomeRows as $row): ?>
          <article class="work-item finance-income-item">
            <span></span>
            <strong><?= rw_esc($row['kategori']) ?></strong>
            <small><?= rw_esc(fmt_date($row['tanggal'])) ?> | <?= rw_esc(fmt_currency($row['nominal'])) ?></small>
            <?php if (! empty($row['keterangan'])): ?>
              <small><?= nl2br(rw_esc($row['keterangan'])) ?></small>
            <?php endif; ?>
          </article>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <p class="empty-state">Belum ada pemasukan RW pada periode ini.</p>
    <?php endif; ?>
  </section>
  <?php endif; ?>

  <?php if ($showPanitiaDetail): ?>
  <section class="dashboard-card">
    <div class="section-heading">
      <div>
        <p class="admin-kicker">Panitia</p>
        <h2>Transaksi Agustusan</h2>
      </div>
    </div>

    <?php if (! empty($panitiaRows)): ?>
      <div class="finance-income-list">
        <?php foreach ($panitiaRows as $row): ?>
          <article class="work-item finance-income-item">
            <span></span>
            <strong><?= rw_esc($row['kategori']) ?></strong>
            <small><?= rw_esc(fmt_date($row['tanggal'])) ?> | <?= rw_esc(keuangan_type_label($row['jenis'] ?? '')) ?> | <?= rw_esc(fmt_currency($row['nominal'])) ?></small>
            <?php if (! empty($row['keterangan'])): ?>
              <small><?= nl2br(rw_esc($row['keterangan'])) ?></small>
            <?php endif; ?>
          </article>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <p class="empty-state">Belum ada transaksi Panitia Agustusan pada periode ini.</p>
    <?php endif; ?>
  </section>
  <?php endif; ?>
</div>

<section class="dashboard-card table-card">
  <div class="section-heading">
    <div>
      <p class="admin-kicker">Riwayat</p>
      <h2>Transaksi <?= rw_esc($selectedUnitLabel) ?></h2>
    </div>
  </div>

  <table>
    <thead>
      <tr>
        <th>Tanggal</th>
        <th>Lingkup</th>
        <th>RT</th>
        <th>Jenis</th>
        <th>Kategori</th>
        <th>Nominal</th>
        <th>Keterangan</th>
        <th>Aksi</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($rows as $row): ?>
        <tr>
          <td><?= rw_esc(fmt_date($row['tanggal'] ?? '')) ?></td>
          <td><?= rw_esc(keuangan_scope_label($row['lingkup'] ?? 'rw')) ?></td>
          <td><?= ! empty($row['rt']) ? rw_esc('RT ' . normalize_rt_code($row['rt'])) : '-' ?></td>
          <td><span class="badge status-<?= rw_esc($row['jenis'] === 'pemasukan' ? 'selesai' : 'menunggu') ?>"><?= rw_esc(keuangan_type_label($row['jenis'] ?? '')) ?></span></td>
          <td><?= rw_esc($row['kategori'] ?? '') ?></td>
          <td><?= rw_esc(fmt_currency($row['nominal'] ?? 0)) ?></td>
          <td><?= nl2br(rw_esc($row['keterangan'] ?? '')) ?></td>
          <td>
            <a href="<?= site_url('admin/keuangan?action=edit&id=' . ($row['id'] ?? 0) . '&start=' . rawurlencode($selectedStart) . '&end=' . rawurlencode($selectedEnd) . ($selectedUnit !== '' ? '&unit=' . rawurlencode($selectedUnit) : '')) ?>">Edit</a> |
            <a href="<?= site_url('admin/keuangan?action=delete&id=' . ($row['id'] ?? 0) . '&start=' . rawurlencode($selectedStart) . '&end=' . rawurlencode($selectedEnd) . ($selectedUnit !== '' ? '&unit=' . rawurlencode($selectedUnit) : '')) ?>" onclick="return confirm('Hapus transaksi ini?')">Hapus</a>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (empty($rows)): ?>
        <tr><td colspan="8" class="table-empty">Belum ada transaksi keuangan pada periode ini.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</section>
<script>
  (() => {
    const scopeSelect = document.getElementById('financeScopeSelect');
    const rtField = document.getElementById('financeRtField');
    const rtSelect = document.getElementById('financeRtSelect');
    const rtHint = document.getElementById('financeRtHint');

    if (!scopeSelect || !rtSelect) {
      return;
    }

    const syncRtState = () => {
      const isRt = scopeSelect.value === 'rt';
      if (rtField) {
        rtField.style.display = isRt ? '' : 'none';
      }
      rtSelect.disabled = !isRt;
      rtSelect.required = isRt;
      if (!isRt) {
        rtSelect.value = '';
      }
      if (rtHint) {
        rtHint.textContent = isRt
          ? 'Pilih RT untuk transaksi kas RT.'
          : '';
      }
    };

    scopeSelect.addEventListener('change', syncRtState);
    syncRtState();
  })();
</script>
<?= $this->endSection() ?>
