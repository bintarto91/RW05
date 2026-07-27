<?php
$educationTopics = [
    [
        'number' => '01',
        'id' => 'ibu-anak',
        'title' => 'Posyandu, Ibu & Anak',
        'description' => 'Materi untuk mendampingi kesehatan ibu, bayi, balita, dan pemantauan tumbuh kembang bersama kader Posyandu.',
        'items' => ['Kesehatan ibu sejak masa kehamilan', 'Peran Posyandu dan pemantauan pertumbuhan', 'Dukungan keluarga untuk ibu dan anak'],
        'articleUrl' => 'https://ayosehat.kemkes.go.id/1000-hari-pertama-kehidupan',
        'videoUrl' => 'https://www.youtube.com/@KementerianKesehatanRI/search?query=posyandu%20ibu%20dan%20anak',
        'poster' => 'assets/poster-tumbuh-kembang-anak.png',
        'posterAlt' => 'Poster dukung tumbuh kembang anak dari STIKes Dharma Husada',
    ],
    [
        'number' => '02',
        'id' => 'gizi-stunting',
        'title' => 'Gizi Keluarga & Stunting',
        'description' => 'Panduan awal untuk memahami peran pola asuh, makanan bergizi, dan pemantauan pertumbuhan anak.',
        'items' => ['Pola asuh yang mendukung tumbuh kembang', 'Kebiasaan makan bergizi dalam keluarga', 'Pentingnya pemantauan pertumbuhan'],
        'articleUrl' => 'https://ayosehat.kemkes.go.id/cegah-stunting-dengan-pola-asuh-yang-baik',
        'videoUrl' => 'https://www.youtube.com/@KementerianKesehatanRI/search?query=stunting%20gizi%20anak',
    ],
    [
        'number' => '03',
        'id' => 'lansia',
        'title' => 'Lansia & Posbindu',
        'description' => 'Materi pendampingan untuk membantu warga lanjut usia tetap aktif, terhubung, dan memperoleh informasi kesehatan yang tepat.',
        'items' => ['Aktivitas sehat bagi lansia', 'Dukungan keluarga dan lingkungan', 'Pemanfaatan kegiatan Posbindu'],
        'articleUrl' => 'https://ayosehat.kemkes.go.id/agenda-kegiatan/hari-lanjut-usia-nasional',
        'videoUrl' => 'https://www.youtube.com/@KementerianKesehatanRI/search?query=lansia%20posbindu',
    ],
    [
        'number' => '04',
        'id' => 'remaja',
        'title' => 'Remaja & Kesehatan Mental',
        'description' => 'Edukasi untuk membangun kepedulian, komunikasi yang sehat, dan dukungan keluarga bagi remaja.',
        'items' => ['Mengenali pentingnya kesehatan mental', 'Membangun komunikasi yang saling menghargai', 'Mengetahui kapan perlu mencari bantuan'],
        'articleUrl' => 'https://ayosehat.kemkes.go.id/gangguan-kesehatan-mental',
        'videoUrl' => 'https://www.youtube.com/@KementerianKesehatanRI/search?query=kesehatan%20mental%20remaja',
    ],
    [
        'number' => '05',
        'id' => 'hidup-sehat',
        'title' => 'Pola Hidup Sehat',
        'description' => 'Inspirasi kebiasaan sederhana untuk bergerak aktif serta menjaga kesehatan diri dan lingkungan.',
        'items' => ['Aktivitas fisik sesuai kemampuan', 'Kebiasaan hidup bersih dan sehat', 'Pemeriksaan kesehatan secara berkala'],
        'articleUrl' => 'https://ayosehat.kemkes.go.id/cara-menjaga-kesehatan-jantung',
        'videoUrl' => 'https://www.youtube.com/@KementerianKesehatanRI/search?query=GERMAS%20aktivitas%20fisik',
    ],
    [
        'number' => '06',
        'id' => 'rokok-narkoba',
        'title' => 'Pencegahan Rokok & Narkoba',
        'description' => 'Materi keluarga untuk membangun lingkungan yang saling menjaga dari rokok dan penyalahgunaan narkoba.',
        'items' => ['Risiko rokok bagi diri dan keluarga', 'Pencegahan sejak usia remaja', 'Peran keluarga dan lingkungan bebas asap rokok'],
        'articleUrl' => 'https://ayosehat.kemkes.go.id/rokok-membuat-hidup-jadi-redup',
        'videoUrl' => 'https://www.youtube.com/@KementerianKesehatanRI/search?query=rokok%20narkoba',
    ],
];
?>
<?= $this->extend('layouts/public') ?>

<?= $this->section('content') ?>
<section class="page-hero education-page-hero">
  <div class="container page-hero-grid">
    <div data-reveal>
      <p class="eyebrow">Edukasi kesehatan</p>
      <h1>Belajar sehat dari sumber yang dapat dipercaya.</h1>
      <p class="hero-text">Pilih topik yang dibutuhkan keluarga, lalu baca materi Ayo Sehat atau tonton video pada kanal resmi Kementerian Kesehatan RI.</p>
      <div class="hero-actions">
        <a href="#daftar-edukasi" class="btn light">Pilih Topik</a>
        <a href="<?= site_url('kesehatan') ?>" class="btn outline-light">Kembali ke Kesehatan</a>
      </div>
    </div>
    <aside class="page-callout education-callout" data-reveal>
      <span>Sumber terpilih</span>
      <strong>Artikel dan video resmi</strong>
      <p>Setiap topik mengarah ke media edukasi Kementerian Kesehatan agar informasi lebih aman untuk dibaca dan dibagikan.</p>
      <div class="education-source-badge">Kementerian Kesehatan RI</div>
    </aside>
  </div>
</section>

<section class="section education-index-section" id="daftar-edukasi" aria-labelledby="education-index-title">
  <div class="container">
    <div class="section-title" data-reveal>
      <p class="eyebrow">Pilih topik</p>
      <h2 id="education-index-title">Edukasi untuk seluruh keluarga.</h2>
      <p>Gunakan tombol berikut untuk langsung menuju materi yang ingin dipelajari.</p>
    </div>
    <nav class="education-topic-nav" aria-label="Daftar topik edukasi" data-reveal>
      <?php foreach ($educationTopics as $topic): ?>
        <a href="#<?= rw_esc($topic['id']) ?>"><span><?= rw_esc($topic['number']) ?></span><?= rw_esc($topic['title']) ?></a>
      <?php endforeach; ?>
    </nav>
  </div>
</section>

<section class="section education-list-section" aria-label="Materi edukasi kesehatan">
  <div class="container education-grid">
    <?php foreach ($educationTopics as $topic): ?>
      <article class="education-card" id="<?= rw_esc($topic['id']) ?>" data-reveal>
        <div class="education-card-head">
          <span><?= rw_esc($topic['number']) ?></span>
          <div>
            <small>Materi keluarga</small>
            <h2><?= rw_esc($topic['title']) ?></h2>
          </div>
        </div>
        <p><?= rw_esc($topic['description']) ?></p>
        <div class="education-learn-list">
          <strong>Yang dapat dipelajari</strong>
          <ul>
            <?php foreach ($topic['items'] as $item): ?>
              <li><?= rw_esc($item) ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
        <?php if (! empty($topic['poster'])): ?>
          <figure class="education-poster">
            <a href="<?= base_url($topic['poster']) ?>" target="_blank" rel="noopener noreferrer" aria-label="Buka poster Dukung Tumbuh Kembang Anak ukuran penuh">
              <img src="<?= base_url($topic['poster']) ?>" alt="<?= rw_esc($topic['posterAlt']) ?>" width="1024" height="1536" loading="lazy" decoding="async">
              <span>
                <strong>Poster: Dukung Tumbuh Kembang Anak</strong>
                <small>Klik untuk membaca poster ukuran penuh.</small>
                <b>Lihat poster <i aria-hidden="true">→</i></b>
              </span>
            </a>
            <figcaption>Materi edukasi STIKes Dharma Husada oleh Rai Nurani, S.Kep., Ners., M.Kep.</figcaption>
          </figure>
        <?php endif; ?>
        <div class="education-actions">
          <a href="<?= rw_esc($topic['articleUrl']) ?>" class="btn primary" target="_blank" rel="noopener noreferrer">Baca Artikel Resmi</a>
          <a href="<?= rw_esc($topic['videoUrl']) ?>" class="btn secondary" target="_blank" rel="noopener noreferrer">Tonton Video Kemenkes</a>
        </div>
      </article>
    <?php endforeach; ?>
  </div>
</section>

<section class="section education-note-section">
  <div class="container">
    <div class="education-note" data-reveal>
      <div>
        <p class="eyebrow">Catatan penting</p>
        <h2>Edukasi membantu memahami, bukan menggantikan pemeriksaan.</h2>
        <p>Untuk keluhan, diagnosis, atau penanganan kesehatan, hubungi tenaga dan fasilitas kesehatan. Hindari membagikan data kesehatan pribadi melalui ruang publik.</p>
      </div>
      <a href="<?= site_url('kesehatan') ?>#jadwal-bantuan" class="btn primary">Lihat Jalur Bantuan</a>
    </div>
  </div>
</section>
<?= $this->endSection() ?>
