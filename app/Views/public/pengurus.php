<?= $this->extend('layouts/public') ?>

<?= $this->section('content') ?>
<?php
$pengurusAktif = is_array($pengurus ?? null) ? $pengurus : [];
$chartGroups = pengurus_chart_groups($pengurusAktif);
$rtList = array_values(array_unique(array_filter(array_map(static fn (array $row): string => trim((string) ($row['rt'] ?? '')), $pengurusAktif))));
$jabatanList = array_values(array_unique(array_filter(array_map(static fn (array $row): string => trim((string) ($row['jabatan'] ?? '')), $pengurusAktif))));
$summaryStats = [
    [
        'value' => (string) count($pengurusAktif),
        'label' => 'Pengurus aktif',
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
      <strong><?= rw_esc((string) count($pengurusAktif)) ?> orang</strong>
      <p>Perubahan nama, jabatan, status, atau urutan langsung memperbarui diagram ini.</p>
    </div>
  </div>
</section>

<section class="section white-section pengurus-section">
  <div class="container">
    <div class="structure-showcase <?= ! empty($strukturPengurusImage) ? 'structure-showcase-official' : 'structure-showcase-auto' ?>">
      <?php if (! empty($strukturPengurusImage)): ?>
        <figure class="structure-frame" data-reveal>
          <div class="structure-image-shell">
            <img src="<?= rw_esc($strukturPengurusImage) ?>" alt="Struktur organisasi kepengurusan RW 05">
          </div>
          <figcaption>
            <strong>Struktur organisasi kepengurusan RW 05</strong>
            <span>Acuan susunan resmi pengurus</span>
          </figcaption>
        </figure>
      <?php else: ?>
      <section class="public-org-chart" data-reveal aria-label="Diagram struktur pengurus RW 05">
        <div class="public-org-chart-head">
          <div><p class="eyebrow">Diagram otomatis</p><h2>Struktur Pengurus RW 05</h2></div>
          <span>Terhubung dengan data admin</span>
        </div>
        <?php if ($chartGroups): ?>
          <div class="org-chart">
            <?php foreach ($chartGroups as $groupKey => $group): ?>
              <section class="org-chart-level org-chart-level-<?= rw_esc($groupKey) ?>">
                <h3><?= rw_esc($group['label']) ?></h3>
                <div class="org-chart-nodes">
                  <?php foreach ($group['items'] as $person): ?>
                    <article class="org-chart-node">
                      <span><?= rw_esc(strtoupper(substr((string) ($person['nama'] ?? 'P'), 0, 1))) ?></span>
                      <div>
                        <strong><?= rw_esc($person['nama'] ?? '') ?></strong>
                        <small><?= rw_esc($person['jabatan'] ?? '') ?><?= ! empty($person['rt']) ? ' · RT ' . rw_esc($person['rt']) : '' ?></small>
                      </div>
                    </article>
                  <?php endforeach; ?>
                </div>
              </section>
            <?php endforeach; ?>
          </div>
        <?php else: ?>
          <p class="empty-state">Data pengurus aktif belum tersedia.</p>
        <?php endif; ?>
      </section>
      <?php endif; ?>

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
          <span>Pembina &amp; Penasihat</span>
          <span>Ketua RW</span>
          <span>Inti &amp; Wilayah</span>
          <span>Bidang</span>
          <span>Unit &amp; Mitra</span>
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
