<?php
$healthPrograms = [
    [
        'number' => '01',
        'id' => 'ibu-anak',
        'title' => 'Posyandu, Ibu & Anak',
        'description' => 'Pusat informasi kegiatan Posyandu, kesehatan ibu, serta pemantauan tumbuh kembang anak di lingkungan RW.',
        'items' => ['Jadwal kegiatan Posyandu', 'Informasi kesehatan ibu dan anak', 'Arah kontak kader lingkungan'],
    ],
    [
        'number' => '02',
        'id' => 'gizi-stunting',
        'title' => 'Gizi Keluarga & Stunting',
        'description' => 'Edukasi gizi keluarga dan dukungan lingkungan untuk membantu pencegahan stunting sejak dini.',
        'items' => ['Edukasi menu bergizi', 'Pemantauan pertumbuhan anak', 'Rujukan informasi dari kader'],
    ],
    [
        'number' => '03',
        'id' => 'lansia',
        'title' => 'Lansia & Posbindu',
        'description' => 'Informasi kegiatan kesehatan dan pendampingan agar warga lanjut usia tetap aktif, aman, dan terhubung.',
        'items' => ['Jadwal Posbindu', 'Kegiatan lansia aktif', 'Pendampingan keluarga'],
    ],
    [
        'number' => '04',
        'id' => 'remaja',
        'title' => 'Remaja & Kesehatan Mental',
        'description' => 'Ruang edukasi yang ramah bagi remaja dan keluarga untuk membangun kebiasaan sehat serta kepedulian bersama.',
        'items' => ['Edukasi kesehatan remaja', 'Dukungan keluarga dan lingkungan', 'Arah bantuan yang tepat'],
    ],
    [
        'number' => '05',
        'id' => 'hidup-sehat',
        'title' => 'Pola Hidup Sehat',
        'description' => 'Ajakan bergerak aktif dan membangun kebiasaan sehat sebagai bagian dari pencegahan penyakit tidak menular.',
        'items' => ['Aktivitas fisik bersama', 'Kebiasaan hidup bersih dan sehat', 'Edukasi pemeriksaan berkala'],
    ],
    [
        'number' => '06',
        'id' => 'rokok-narkoba',
        'title' => 'Pencegahan Rokok & Narkoba',
        'description' => 'Edukasi keluarga dan lingkungan untuk melindungi anak, remaja, dan warga dari rokok serta penyalahgunaan narkoba.',
        'items' => ['Edukasi risiko dan pencegahan', 'Peran keluarga dan tetangga', 'Lingkungan RW yang saling menjaga'],
    ],
];

$healthContactUrl = ! empty($waLink) ? $waLink : site_url('aspirasi');
$healthContactExternal = ! empty($waLink);
?>
<?= $this->extend('layouts/public') ?>

<?= $this->section('content') ?>
<section class="page-hero health-page-hero">
  <div class="container page-hero-grid">
    <div data-reveal>
      <p class="eyebrow">Kesehatan warga</p>
      <h1>Informasi sehat, lebih dekat dari lingkungan sendiri.</h1>
      <p class="hero-text">Satu halaman untuk menemukan program kesehatan keluarga, kegiatan kader, edukasi, dan jalur bantuan warga RW 05.</p>
      <div class="hero-actions">
        <a href="#program-kesehatan" class="btn light">Lihat Program</a>
        <a href="#jadwal-bantuan" class="btn outline-light">Jadwal & Bantuan</a>
      </div>
    </div>
    <aside class="page-callout health-callout" data-reveal aria-label="Ringkasan pusat kesehatan warga">
      <span>Pusat informasi warga</span>
      <strong>6 layanan prioritas</strong>
      <p>Dari ibu dan anak hingga lansia, seluruh informasi utama diringkas agar mudah ditemukan warga.</p>
      <div class="health-callout-tags" aria-label="Kelompok layanan">
        <b>Ibu & Anak</b>
        <b>Remaja</b>
        <b>Keluarga</b>
        <b>Lansia</b>
      </div>
    </aside>
  </div>
</section>

<section class="section health-quick-section" aria-labelledby="health-quick-title">
  <div class="container">
    <div class="section-title" data-reveal>
      <p class="eyebrow">Akses cepat</p>
      <h2 id="health-quick-title">Mulai dari kebutuhan Anda.</h2>
      <p>Pilih informasi yang dicari tanpa perlu membuka banyak halaman.</p>
    </div>
    <div class="health-quick-grid">
      <a href="#program-kesehatan" class="health-quick-card" data-reveal>
        <span>01</span>
        <div>
          <strong>Program Kesehatan</strong>
          <small>Lihat layanan untuk setiap kelompok warga</small>
        </div>
      </a>
      <a href="<?= site_url('kegiatan') ?>" class="health-quick-card" data-reveal>
        <span>02</span>
        <div>
          <strong>Jadwal Kegiatan</strong>
          <small>Periksa agenda terbaru yang diumumkan RW</small>
        </div>
      </a>
      <a href="<?= rw_esc($healthContactUrl) ?>" class="health-quick-card"<?= $healthContactExternal ? ' target="_blank" rel="noopener noreferrer"' : '' ?> data-reveal>
        <span>03</span>
        <div>
          <strong>Hubungi Pengurus</strong>
          <small>Tanyakan kontak kader atau layanan terdekat</small>
        </div>
      </a>
      <a href="<?= site_url('edukasi-kesehatan') ?>" class="health-quick-card" data-reveal>
        <span>04</span>
        <div>
          <strong>Edukasi & Video</strong>
          <small>Baca dan tonton materi kesehatan dari sumber resmi</small>
        </div>
      </a>
    </div>
  </div>
</section>

<section class="section health-program-section" id="program-kesehatan" aria-labelledby="health-program-title">
  <div class="container">
    <div class="health-section-head" data-reveal>
      <div class="section-title">
        <p class="eyebrow">Program prioritas</p>
        <h2 id="health-program-title">Kesehatan untuk setiap tahap kehidupan.</h2>
        <p>Topik kesehatan dikelompokkan supaya menu tetap ringkas, tetapi informasinya tetap lengkap dan relevan untuk lingkungan RW.</p>
      </div>
      <p class="health-section-note">Pilih kartu sesuai kebutuhan keluarga Anda.</p>
    </div>

    <div class="health-program-grid">
      <?php foreach ($healthPrograms as $program): ?>
        <a href="<?= site_url('edukasi-kesehatan') ?>#<?= rw_esc($program['id']) ?>" class="health-program-card" id="<?= rw_esc($program['id']) ?>" aria-label="Buka edukasi <?= rw_esc($program['title']) ?>" data-reveal>
          <div class="health-program-head">
            <span><?= rw_esc($program['number']) ?></span>
            <h3><?= rw_esc($program['title']) ?></h3>
          </div>
          <p><?= rw_esc($program['description']) ?></p>
          <ul>
            <?php foreach ($program['items'] as $item): ?>
              <li><?= rw_esc($item) ?></li>
            <?php endforeach; ?>
          </ul>
          <span class="health-card-link">Buka edukasi & video <b aria-hidden="true">→</b></span>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="section health-support-section" id="jadwal-bantuan" aria-labelledby="health-support-title">
  <div class="container health-support-grid">
    <article class="health-schedule-card" data-reveal>
      <p class="eyebrow">Jadwal & pendampingan</p>
      <h2 id="health-support-title">Tiga langkah sebelum datang.</h2>
      <ol class="health-step-list">
        <li><span>1</span><div><strong>Lihat pengumuman</strong><p>Periksa agenda terbaru pada halaman kegiatan RW.</p></div></li>
        <li><span>2</span><div><strong>Konfirmasi ke pengurus</strong><p>Tanyakan jadwal, lokasi, serta kader yang dapat dihubungi.</p></div></li>
        <li><span>3</span><div><strong>Siapkan kebutuhan</strong><p>Bawa dokumen atau catatan yang diminta oleh petugas kegiatan.</p></div></li>
      </ol>
      <div class="health-support-actions">
        <a href="<?= site_url('kegiatan') ?>" class="btn primary">Lihat Kegiatan</a>
        <a href="<?= rw_esc($healthContactUrl) ?>" class="btn secondary"<?= $healthContactExternal ? ' target="_blank" rel="noopener noreferrer"' : '' ?>>Hubungi Pengurus</a>
      </div>
    </article>

    <aside class="health-help-card" data-reveal>
      <p class="eyebrow">Informasi penting</p>
      <h2>Gunakan informasi kesehatan dengan bijak.</h2>
      <div class="health-help-list">
        <div><strong>Cek sumbernya</strong><p>Utamakan informasi dari fasilitas kesehatan, petugas, dan kader resmi.</p></div>
        <div><strong>Jaga privasi</strong><p>Hindari mengirim data kesehatan pribadi melalui kanal publik.</p></div>
        <div><strong>Butuh pertolongan segera?</strong><p>Hubungi fasilitas kesehatan atau layanan darurat setempat. Informasi di halaman ini bukan pengganti tenaga kesehatan.</p></div>
      </div>
    </aside>
  </div>
</section>
<?= $this->endSection() ?>
