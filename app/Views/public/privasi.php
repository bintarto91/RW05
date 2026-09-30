<?= $this->extend('layouts/public') ?>

<?= $this->section('content') ?>
<section class="page-hero">
  <div class="container">
    <p class="eyebrow">Informasi layanan</p>
    <h1>Kebijakan Privasi</h1>
    <p class="hero-text">Kebijakan ini menjelaskan bagaimana informasi yang dikirim melalui portal RW 05 Lamajang Peuntas digunakan dan dijaga.</p>
  </div>
</section>

<section class="section white-section">
  <div class="container content-block privacy-content">
    <section aria-labelledby="privacy-data-title">
      <h2 id="privacy-data-title">Data yang diproses</h2>
      <p>Form pengajuan surat dapat meminta nama, nomor WhatsApp, RT, alamat, keperluan, dan rincian yang diperlukan untuk menyiapkan surat. Form aspirasi dapat meminta nama, kontak dan RT secara opsional, kategori, serta isi pesan.</p>
    </section>

    <section aria-labelledby="privacy-purpose-title">
      <h2 id="privacy-purpose-title">Tujuan dan akses</h2>
      <p>Data digunakan untuk menindaklanjuti layanan atau aspirasi yang dikirim. Akses operasional diberikan kepada pengurus yang berwenang melalui panel administrasi. Data pemohon tidak ditampilkan pada halaman publik.</p>
    </section>

    <section aria-labelledby="privacy-status-title">
      <h2 id="privacy-status-title">Cek status pengajuan</h2>
      <p>Status surat hanya dibuka setelah pemohon memasukkan kode pengajuan dan empat angka terakhir nomor WhatsApp yang digunakan saat mengajukan. Halaman cek status tidak menampilkan alamat, nama, RT, atau rincian data formulir.</p>
    </section>

    <section aria-labelledby="privacy-storage-title">
      <h2 id="privacy-storage-title">Penyimpanan dan permintaan perubahan</h2>
      <p>Data layanan disimpan pada sistem administrasi RW untuk keperluan tindak lanjut dan pencatatan. Pengurus mengelola akses dan masa simpan sesuai kebutuhan administrasi; portal ini belum memublikasikan jadwal penghapusan otomatis.</p>
      <p>Untuk meminta koreksi atau menanyakan pengelolaan data, hubungi pengurus melalui kontak resmi yang tercantum pada bagian bawah situs.</p>
    </section>

    <section aria-labelledby="privacy-security-title">
      <h2 id="privacy-security-title">Perlindungan informasi</h2>
      <p>Halaman administrasi dibatasi untuk akun yang berwenang. Halaman pengajuan dan status tidak disimpan oleh cache publik. Jangan mengirim NIK, nomor KK lengkap, foto KTP, atau dokumen sensitif melalui form publik.</p>
      <p>Situs memuat font dari penyedia eksternal; saat font dimuat, browser dapat menghubungi layanan penyedia font tersebut.</p>
    </section>

    <p class="form-note">Kebijakan ini berlaku untuk penggunaan portal warga RW 05 Lamajang Peuntas. Pengurus dapat memperbaruinya bila proses pengelolaan data berubah.</p>
  </div>
</section>
<?= $this->endSection() ?>