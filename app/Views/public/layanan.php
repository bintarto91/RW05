<?php
$kebutuhanRw = [
    [
        'id' => 'surat-pengantar',
        'kode' => 'SP',
        'nama' => 'Surat Pengantar',
        'ringkas' => 'Pengantar administrasi warga untuk mengurus kebutuhan lanjutan ke desa, sekolah, kantor, atau instansi terkait.',
        'syarat' => ['Nama dan alamat pemohon', 'RT di wilayah RW 05', 'Keperluan surat yang jelas'],
        'alur' => ['Isi form online', 'Pengurus verifikasi data', 'Surat diterbitkan jika disetujui'],
    ],
    [
        'id' => 'surat-keterangan',
        'kode' => 'SK',
        'nama' => 'Surat Keterangan',
        'ringkas' => 'Keterangan resmi dari RW sesuai data dan kebutuhan warga, misalnya domisili, status, atau keterangan umum.',
        'syarat' => ['Identitas pemohon', 'Keterangan yang ingin diterbitkan', 'Data pendukung bila diminta pengurus'],
        'alur' => ['Ajukan keterangan', 'Admin cek data', 'Ketua RW menyetujui'],
    ],
    [
        'id' => 'surat-undangan',
        'kode' => 'UD',
        'nama' => 'Surat Undangan',
        'ringkas' => 'Undangan kegiatan, rapat, musyawarah, atau koordinasi warga yang diterbitkan oleh pengurus RW.',
        'syarat' => ['Hari dan tanggal', 'Waktu dan tempat', 'Agenda kegiatan'],
        'alur' => ['Isi agenda', 'Pengurus cek jadwal', 'Undangan siap dibagikan'],
    ],
    [
        'id' => 'surat-edaran',
        'kode' => 'ED',
        'nama' => 'Surat Edaran / Pemberitahuan',
        'ringkas' => 'Pemberitahuan kegiatan, informasi lingkungan, imbauan, atau pengumuman resmi untuk warga RW 05.',
        'syarat' => ['Nama kegiatan/informasi', 'Jadwal bila ada', 'Keterangan pemberitahuan'],
        'alur' => ['Tulis informasi', 'Pengurus validasi', 'Edaran diterbitkan'],
    ],
    [
        'id' => 'surat-permohonan',
        'kode' => 'PM',
        'nama' => 'Surat Permohonan',
        'ringkas' => 'Permohonan dukungan, bantuan, fasilitas, atau koordinasi kepada pihak terkait atas nama lingkungan RW.',
        'syarat' => ['Pihak tujuan', 'Jenis permohonan', 'Kegiatan atau keperluan'],
        'alur' => ['Ajukan permohonan', 'Pengurus bahas kebutuhan', 'Surat disetujui dan diterbitkan'],
    ],
    [
        'id' => 'surat-tugas',
        'kode' => 'TM',
        'nama' => 'Surat Tugas / Mandat',
        'ringkas' => 'Penugasan resmi untuk pengurus, panitia, atau warga yang diberi mandat menjalankan kegiatan RW.',
        'syarat' => ['Nama penerima tugas', 'Jabatan atau peran', 'Uraian dan masa tugas'],
        'alur' => ['Isi data tugas', 'Ketua RW meninjau', 'Mandat diterbitkan'],
    ],
    [
        'id' => 'berita-acara',
        'kode' => 'BA',
        'nama' => 'Berita Acara',
        'ringkas' => 'Catatan resmi hasil kegiatan, rapat, musyawarah, kesepakatan, atau kejadian penting di lingkungan RW.',
        'syarat' => ['Nama kegiatan', 'Peserta dan pimpinan', 'Poin hasil kegiatan'],
        'alur' => ['Masukkan hasil kegiatan', 'Pengurus cek isi', 'Berita acara diarsipkan'],
    ],
    [
        'id' => 'surat-keputusan',
        'kode' => 'KEP',
        'nama' => 'Surat Keputusan Ketua RW 05',
        'ringkas' => 'Keputusan Ketua RW untuk penetapan panitia, program, kebijakan lingkungan, atau hasil musyawarah.',
        'syarat' => ['Pokok keputusan', 'Butir keputusan', 'Dasar musyawarah atau kebutuhan organisasi'],
        'alur' => ['Susun butir keputusan', 'Pengurus meninjau', 'Keputusan ditetapkan'],
    ],
];
?>
<?= $this->extend('layouts/public') ?>

<?= $this->section('content') ?>
<section class="page-hero">
  <div class="container page-hero-grid">
    <div data-reveal>
      <p class="eyebrow">Layanan warga</p>
      <h1>Layanan RW yang mudah dipilih.</h1>
      <p class="hero-text">Daftar layanan dibuat jelas, ringkas, dan informatif supaya warga tahu syarat, alur, dan kontak yang harus dihubungi.</p>
    </div>
    <div class="page-callout" data-reveal>
      <span>Layanan utama</span>
      <strong><?= rw_esc(count($kebutuhanRw)) ?> kebutuhan warga</strong>
        <p>Pengantar, keterangan, undangan, edaran, permohonan, tugas, berita acara, dan keputusan tersedia dalam satu halaman.</p>
    </div>
  </div>
</section>

<section class="section service-section">
  <div class="container">
    <div class="service-intro-panel" data-reveal>
      <div>
        <p class="eyebrow">Kebutuhan RW</p>
        <h2>Urus kebutuhan warga tanpa harus menebak alurnya.</h2>
        <p>Mulai dari surat pengantar sampai keputusan RW, warga bisa melihat syarat dasar sebelum mengisi form online atau menghubungi pengurus.</p>
      </div>
      <a href="<?= site_url('layanan-online') ?>" class="btn primary">Ajukan Surat Online</a>
    </div>

    <div class="home-summary service-online-summary" data-reveal>
      <article class="summary-card accent">
        <p class="eyebrow">Surat Online</p>
        <h2>Form surat digabung ke halaman layanan.</h2>
        <p>Supaya menu publik tetap ringkas, pengajuan surat tidak dibuat sebagai menu utama terpisah. Warga cukup masuk dari halaman layanan ini untuk mengurus surat dan administrasi lainnya.</p>
        <a href="<?= site_url('layanan-online') ?>" class="text-link">Buka Form Surat</a>
      </article>
      <article class="summary-card">
        <p class="eyebrow">Cek Pengajuan</p>
        <h2>Status dan unduh PDF tetap di tempat yang sama.</h2>
        <p>Setelah mengirim form, warga menerima kode pengajuan untuk cek status, melihat catatan admin, dan mengunduh PDF surat saat sudah disetujui.</p>
        <a href="<?= site_url('layanan-online') ?>" class="text-link">Cek Status Surat</a>
      </article>
    </div>

    <div class="rw-service-grid">
      <?php foreach ($kebutuhanRw as $item): ?>
        <article class="rw-service-card" id="<?= rw_esc($item['id']) ?>" data-reveal>
          <div class="rw-service-head">
            <span class="service-badge"><?= rw_esc($item['kode']) ?></span>
            <div>
              <h2><?= rw_esc($item['nama']) ?></h2>
              <p><?= rw_esc($item['ringkas']) ?></p>
            </div>
          </div>
          <div class="service-detail-grid">
            <div>
              <strong>Syarat awal</strong>
              <ul class="requirement-list">
                <?php foreach ($item['syarat'] as $syarat): ?>
                  <li><?= rw_esc($syarat) ?></li>
                <?php endforeach; ?>
              </ul>
            </div>
            <div>
              <strong>Alur singkat</strong>
              <ol class="service-flow">
                <?php foreach ($item['alur'] as $alur): ?>
                  <li><?= rw_esc($alur) ?></li>
                <?php endforeach; ?>
              </ol>
            </div>
          </div>
          <a href="<?= site_url('layanan-online') ?>" class="text-link">Ajukan online</a>
        </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="section">
  <div class="container">
    <div class="section-title" data-reveal>
      <p class="eyebrow">Butuh bantuan?</p>
      <h2>Sampaikan pertanyaan ke pengurus.</h2>
      <p>Jika kebutuhan belum pas dengan pilihan surat, warga tetap bisa menuliskannya pada detail tambahan agar pengurus meninjau format yang paling sesuai.</p>
      <a href="<?= site_url('layanan-online') ?>" class="btn primary">Buka Layanan Online</a>
    </div>
  </div>
</section>
<?= $this->endSection() ?>
