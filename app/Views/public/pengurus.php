<?= $this->extend('layouts/public') ?>

<?= $this->section('content') ?>
<?php
$pengurusAktif = is_array($pengurus ?? null) ? $pengurus : [];
$hasStructureImage = ! empty($strukturPengurusImage);
$rtList = array_values(array_unique(array_filter(array_map(static fn (array $row): string => trim((string) ($row['rt'] ?? '')), $pengurusAktif))));
$jabatanList = array_values(array_unique(array_filter(array_map(static fn (array $row): string => trim((string) ($row['jabatan'] ?? '')), $pengurusAktif))));
$summaryStats = [
    [
        'value' => $hasStructureImage ? 'Aktif' : (string) count($pengurusAktif),
        'label' => $hasStructureImage ? 'Gambar struktur' : 'Pengurus aktif',
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
      <p class="hero-text">Acuan susunan pengurus mengikuti gambar struktur yang diupload dari dashboard admin.</p>
    </div>
    <div class="page-callout" data-reveal>
      <span><?= $hasStructureImage ? 'Acuan aktif' : 'Pengurus aktif' ?></span>
      <strong><?= $hasStructureImage ? 'Gambar Resmi' : rw_esc((string) count($pengurusAktif)) . ' orang' ?></strong>
      <p><?= $hasStructureImage ? 'Jika gambar diganti dari admin, halaman warga memakai gambar terbaru.' : 'Data dapat diperbarui dari panel admin.' ?></p>
    </div>
  </div>
</section>

<section class="section white-section pengurus-section">
  <div class="container">
    <div class="structure-showcase <?= $hasStructureImage ? 'structure-showcase-official' : '' ?>">
      <?php if ($hasStructureImage): ?>
        <figure class="structure-frame" data-reveal>
          <div class="structure-image-shell">
            <img src="<?= rw_esc($strukturPengurusImage) ?>" alt="Struktur organisasi pengurus RW 05">
          </div>
          <figcaption>
            <strong>Bagan struktur resmi</strong>
            <span>Gambar ini menjadi acuan utama susunan pengurus RW 05.</span>
            <a href="<?= rw_esc($strukturPengurusImage) ?>" target="_blank" rel="noopener noreferrer">Lihat gambar penuh</a>
          </figcaption>
        </figure>
      <?php endif; ?>

      <aside class="structure-summary-panel" data-reveal>
        <p class="eyebrow">Sumber data pengurus</p>
        <h2><?= $hasStructureImage ? 'Mengikuti gambar struktur terbaru.' : 'Belum ada gambar struktur.' ?></h2>
        <p><?= $hasStructureImage ? 'Gambar menjadi acuan struktur, sedangkan rincian nama dan jabatan di bawahnya diambil dari data Pengurus pada dashboard admin.' : 'Upload gambar struktur dari dashboard admin, atau gunakan daftar pengurus aktif di bawah ini sebagai tampilan sementara.' ?></p>
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
          <span>RT</span>
          <span>Bidang</span>
          <span>Unit</span>
        </div>
      </aside>
    </div>

    <?php if (! empty($strukturPengurusDescription)): ?>
      <article class="structure-note" data-reveal>
        <strong>Penjelasan struktur organisasi</strong>
        <p><?= nl2br(rw_esc($strukturPengurusDescription)) ?></p>
      </article>
    <?php endif; ?>

    <article class="structure-note" data-reveal>
      <strong>Catatan tampilan</strong>
      <p>Kalau gambar struktur berubah, sesuaikan juga data nama dan jabatan di dashboard admin menu Pengurus agar rincian di bawah ini ikut benar.</p>
    </article>

    <div class="org-detail" data-reveal>
      <div class="section-title left">
        <p class="eyebrow"><?= $hasStructureImage ? 'Rincian pengurus' : 'Daftar sementara' ?></p>
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
