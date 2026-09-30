<?= $this->extend('layouts/public') ?>

<?= $this->section('content') ?>
<?php
$pengurusAktif = is_array($pengurus ?? null) ? $pengurus : [];
$chartGroups = pengurus_chart_groups($pengurusAktif);
$uniquePeopleCount = pengurus_unique_people_count($pengurusAktif);
$rtList = array_values(array_unique(array_filter(array_map(static fn (array $row): string => trim((string) ($row['rt'] ?? '')), $pengurusAktif))));
$jabatanList = array_values(array_unique(array_filter(array_map(static fn (array $row): string => trim((string) ($row['jabatan'] ?? '')), $pengurusAktif))));
$summaryStats = [
    [
        'value' => (string) $uniquePeopleCount,
        'label' => 'Orang aktif',
    ],
    [
        'value' => $rtList ? (string) count($rtList) : '-',
        'label' => 'RT tercatat',
    ],
    [
        'value' => $jabatanList ? (string) count($jabatanList) : '-',
        'label' => 'Jabatan aktif',
    ],
];
?>
<section class="page-hero">
  <div class="container page-hero-grid">
    <div data-reveal>
      <p class="eyebrow">Pengurus RW</p>
      <h1>Struktur Pengurus Rukun Warga 05.</h1>
      <p class="hero-text">Diagram dibentuk otomatis dari data pengurus aktif dan selalu mengikuti perubahan terbaru dari dashboard admin.</p>
    </div>
    <div class="page-callout" data-reveal>
      <span>Data terhubung otomatis</span>
      <strong><?= rw_esc((string) $uniquePeopleCount) ?> orang</strong>
      <p><?= rw_esc((string) count($pengurusAktif)) ?> posisi aktif. Satu orang dapat menjalankan lebih dari satu amanah tanpa menggandakan jumlah orang.</p>
    </div>
  </div>
</section>

<section class="section white-section pengurus-section">
  <div class="container">
    <div class="structure-showcase structure-showcase-auto">
      <section class="public-org-chart" data-reveal aria-label="Diagram struktur pengurus RW 05">
        <div class="public-org-chart-head">
          <div><p class="eyebrow">Diagram otomatis</p><h2>Struktur Pengurus RW 05 LAMAJANG PEUNTAS</h2></div>
          <span>Terhubung dengan data admin</span>
        </div>
        <?php if ($chartGroups): ?>
          <?= view('components/pengurus_chart', ['chartGroups' => $chartGroups, 'chartVariant' => 'public']) ?>
        <?php else: ?>
          <p class="empty-state">Data pengurus aktif belum tersedia.</p>
        <?php endif; ?>
      </section>

      <aside class="structure-summary-panel" data-reveal>
        <p class="eyebrow">Sumber data pengurus</p>
        <h2>Selalu mengikuti data terbaru.</h2>
        <p>Diagram dan daftar nama memakai sumber data yang sama. Admin cukup mengubah satu data pengurus tanpa membuat ulang gambar.</p>
        <div class="structure-stats">
          <?php foreach ($summaryStats as $stat): ?>
            <div>
              <strong><?= rw_esc($stat['value']) ?></strong>
              <span><?= rw_esc($stat['label']) ?></span>
            </div>
          <?php endforeach; ?>
        </div>
        <div class="structure-path" aria-label="Urutan struktur organisasi">
          <span>Pembina</span>
          <span>Ketua RW</span>
          <span>Penasihat</span>
          <span>Sekretariat</span>
          <span>Koordinator Wilayah</span>
          <span>Bidang</span>
          <span>Unit Pelaksana</span>
          <span>Mitra</span>
        </div>
      </aside>
    </div>

    <?php if (! empty($strukturPengurusDescription)): ?>
      <article class="structure-note" data-reveal>
        <strong>Penjelasan struktur organisasi</strong>
        <p><?= nl2br(rw_esc($strukturPengurusDescription)) ?></p>
      </article>
    <?php endif; ?>

    <div class="org-detail" data-reveal>
      <div class="section-title left">
        <p class="eyebrow">Rincian pengurus</p>
        <h2>Nama dan jabatan pengurus RW 05.</h2>
        <p>Data ini diambil langsung dari dashboard admin, bukan ditulis manual di halaman warga.</p>
      </div>
      <?php if ($pengurusAktif): ?>
        <div class="people-grid">
          <?php foreach ($pengurusAktif as $row): ?>
            <article class="person-card">
              <div class="avatar"><?= rw_esc(strtoupper(substr((string) ($row['nama'] ?? 'P'), 0, 1))) ?></div>
              <div>
                <span class="person-meta"><?= rw_esc($row['jabatan'] ?? '') ?></span>
                <h3><?= rw_esc($row['nama'] ?? '') ?></h3>
                <?php if (! empty($row['rt'])): ?>
                  <p>RT <?= rw_esc($row['rt']) ?></p>
                <?php endif; ?>
                <?php if (! empty($row['tugas'])): ?>
                  <p><?= nl2br(rw_esc($row['tugas'])) ?></p>
                <?php endif; ?>
              </div>
            </article>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <p class="empty-state">Data nama pengurus aktif belum tersedia. Isi dulu dari dashboard admin menu Pengurus.</p>
      <?php endif; ?>
    </div>
  </div>
</section>
<?= $this->endSection() ?>
