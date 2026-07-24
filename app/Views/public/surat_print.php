<?php
$desa = trim((string) ($profil['desa'] ?? 'Citeureup')) ?: 'Citeureup';
$kecamatan = trim((string) ($profil['kecamatan'] ?? '')) ?: '-';
$kabupaten = trim((string) ($profil['kabupaten'] ?? '')) ?: '-';
$profilAlamat = trim((string) ($profil['alamat'] ?? ''));
$alamat = ($profilAlamat !== '' && $profilAlamat !== 'Sekretariat RW 05 Desa Citeureup')
  ? $profilAlamat
  : 'Lamajang Peuntas, Desa Citeureup, Kec. Dayeuhkolot, Kab. Bandung';
$nomorSurat = trim((string) ($pengajuan['nomor_surat'] ?? ''));
$nomorDisplay = $nomorSurat !== '' ? $nomorSurat : '........................................';
$lampiranDisplay = '-';
$jenisSurat = (string) ($pengajuan['jenis_surat'] ?? '');
$perihalMap = [
  'Surat Pengantar' => 'Surat Pengantar',
  'Surat Keterangan' => 'Surat Keterangan',
  'Surat Undangan' => 'Undangan',
  'Surat Edaran / Pemberitahuan' => 'Surat Edaran / Pemberitahuan',
  'Surat Permohonan' => 'Surat Permohonan',
  'Surat Tugas / Mandat' => 'Surat Tugas / Mandat',
  'Berita Acara' => 'Berita Acara',
  'Surat Keputusan Ketua RW 05' => 'Surat Keputusan Ketua RW 05',
];
$perihal = $perihalMap[$jenisSurat] ?? ucwords(strtolower($jenisSurat));
$formatLongDate = static function ($value): string {
  $timestamp = strtotime((string) $value);
  if (! $timestamp) {
    return '';
  }

  $months = [
    1 => 'Januari',
    'Februari',
    'Maret',
    'April',
    'Mei',
    'Juni',
    'Juli',
    'Agustus',
    'September',
    'Oktober',
    'November',
    'Desember',
  ];

  return date('d', $timestamp) . ' ' . $months[(int) date('n', $timestamp)] . ' ' . date('Y', $timestamp);
};
$tanggal = $formatLongDate(date('Y-m-d'));
$statusLabel = surat_status_label($pengajuan['status']);
$forPdf = (bool) ($forPdf ?? false);
$template = surat_template_profile($pengajuan['jenis_surat'] ?? '');
$structuredData = surat_request_data_decode($pengajuan['detail_json'] ?? '');
$cleanPersonName = static function ($name): string {
  $name = trim((string) $name);
  $name = preg_replace('/^\s*(Bpk\.?|Bapak|Pak|Ibu|Bu)\s+/i', '', $name);
  return trim((string) preg_replace('/\s+/', ' ', (string) $name));
};
$rtCode = normalize_rt_code($pengajuan['rt'] ?? '');
$rtKetuaDisplayName = $cleanPersonName($rtKetuaNama ?? '');
$ketuaDisplayName = $cleanPersonName($ketuaNama ?? 'Ketua RW 05');
$templateTokens = [
  '{{site_name}}' => 'RW 05 Desa Citeureup',
  '{{desa}}' => $desa,
  '{{kecamatan}}' => $kecamatan,
  '{{kabupaten}}' => $kabupaten,
  '{{rw}}' => '05',
  '{{tanggal_surat}}' => $tanggal,
  '{{nomor_surat}}' => $nomorDisplay,
  '{{nama}}' => (string) ($pengajuan['nama'] ?? ''),
  '{{rt}}' => $rtCode,
  '{{alamat}}' => (string) ($pengajuan['alamat'] ?? ''),
  '{{keperluan}}' => (string) ($pengajuan['keperluan'] ?? ''),
  '{{jenis_surat}}' => (string) ($pengajuan['jenis_surat'] ?? ''),
  '{{jenis_surat_lower}}' => strtolower((string) ($pengajuan['jenis_surat'] ?? '')),
];
foreach ($structuredData as $key => $value) {
  if (is_scalar($value)) {
    $templateTokens['{{' . $key . '}}'] = (string) $value;
  }
}
$renderTemplateText = static function (string $text) use ($templateTokens): string {
  return trim(strtr($text, $templateTokens));
};
$purposeLabel = (string) ($template['purpose_label'] ?? 'Keperluan Surat');
$detailLabel = (string) ($template['detail_label'] ?? 'Keterangan Tambahan');
$bodyParagraphs = is_array($template['body'] ?? null) ? $template['body'] : [];
$closingText = (string) ($template['closing'] ?? 'Demikian surat ini dibuat agar dapat dipergunakan sebagaimana mestinya.');
$structuredEntries = surat_request_data_entries($pengajuan['jenis_surat'] ?? '', $pengajuan['detail_json'] ?? '');
$hasTemplateRows = array_key_exists('rows', $template);
$templateRows = is_array($template['rows'] ?? null) ? $template['rows'] : [];
$letterTitle = (string) ($template['title'] ?? ($pengajuan['jenis_surat'] ?? 'SURAT'));
$recipientSource = (string) ($structuredData['tujuan_surat'] ?? ($template['recipient_default'] ?? ''));
$recipientName = $renderTemplateText($recipientSource);
$recipientName = $recipientName !== '' ? $recipientName : '................................';
$salutation = $renderTemplateText((string) ($template['salutation'] ?? ''));
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= rw_esc($pengajuan['jenis_surat']) ?> - <?= rw_esc($pengajuan['kode_pengajuan']) ?></title>
  <style>
    @page {
      margin: 14mm 18mm 15mm 18mm;
    }

    * {
      box-sizing: border-box;
    }

    body {
      margin: 0;
      background: <?= $forPdf ? '#ffffff' : '#eef2f4' ?>;
      color: #111827;
      font-family: Arial, "Segoe UI", sans-serif;
      font-size: 11px;
      line-height: 1.35;
    }

    .print-toolbar {
      position: sticky;
      top: 0;
      z-index: 2;
      display: flex;
      justify-content: center;
      gap: 10px;
      padding: 14px;
      background: #082f2a;
    }

    .print-toolbar button,
    .print-toolbar a {
      min-height: 42px;
      padding: 10px 14px;
      border: 0;
      border-radius: 8px;
      color: #082f2a;
      background: #f3d48c;
      font: inherit;
      font-weight: 700;
      text-decoration: none;
      cursor: pointer;
    }

    .page {
      width: <?= $forPdf ? 'auto' : '210mm' ?>;
      min-height: <?= $forPdf ? 'auto' : '297mm' ?>;
      margin: <?= $forPdf ? '0' : '18px auto' ?>;
      padding: <?= $forPdf ? '0' : '14mm 18mm 15mm' ?>;
      background: #ffffff;
      box-shadow: <?= $forPdf ? 'none' : '0 18px 38px rgba(17, 24, 39, 0.16)' ?>;
    }

    .letterhead {
      padding-bottom: 5px;
      text-align: center;
    }

    .letterhead h1,
    .letterhead h2,
    .letterhead p {
      margin: 0;
    }

    .letterhead h1 {
      color: #005c32;
      font-size: 15px;
      line-height: 1.05;
      font-weight: 800;
      text-transform: uppercase;
    }

    .letterhead h2 {
      color: #005c32;
      font-size: 14px;
      line-height: 1.08;
      font-weight: 800;
      text-transform: uppercase;
    }

    .letterhead .region {
      color: #111827;
      font-size: 11px;
      line-height: 1.15;
      font-weight: 800;
      text-transform: uppercase;
    }

    .letterhead .address,
    .letterhead .contact {
      font-size: 7.4px;
      line-height: 1.18;
    }

    .letterhead .motto {
      color: #d18b00;
      font-size: 8px;
      line-height: 1.2;
      font-weight: 800;
      text-transform: uppercase;
    }

    .kop-line {
      height: 3px;
      margin-top: 6px;
      border-top: 1.5px solid #005c32;
      border-bottom: 1px solid #d18b00;
    }

    .top-meta {
      width: 100%;
      margin: 14px 0 10px;
      border-collapse: collapse;
      table-layout: fixed;
      font-size: 10px;
    }

    .top-meta td {
      vertical-align: top;
    }

    .meta-list {
      width: 100%;
      border-collapse: collapse;
    }

    .meta-list td {
      padding: 0 0 3px;
      vertical-align: top;
    }

    .meta-list td:first-child {
      width: 58px;
    }

    .meta-list td:nth-child(2) {
      width: 12px;
      text-align: center;
    }

    .letter-date {
      text-align: right;
      white-space: nowrap;
    }

    .recipient {
      margin: 8px 0 8px;
      font-size: 10px;
    }

    .recipient p {
      margin: 0 0 3px;
    }

    .letter-title {
      margin: 12px 0 14px;
      text-align: center;
    }

    .letter-title h2 {
      margin: 0;
      font-size: 12px;
      font-weight: 800;
      text-transform: uppercase;
    }

    .salutation {
      margin-top: 0;
    }

    .content p {
      margin: 0 0 7px;
      text-align: justify;
    }

    .data-table {
      width: 100%;
      margin: 5px 0 8px;
      border-collapse: collapse;
      font-size: 10px;
    }

    .data-table td {
      padding: 1px 3px 2px;
      vertical-align: top;
    }

    .data-table td:first-child {
      width: 155px;
      font-weight: 400;
    }

    .data-table td:nth-child(2) {
      width: 12px;
      text-align: center;
    }

    .verification {
      margin-top: 16px;
      padding: 10px 12px;
      border: 1px solid #cbd5e1;
      border-radius: 8px;
      background: #f8fafc;
      font-size: 12px;
    }

    .signature-row {
      width: 100%;
      border-collapse: collapse;
      margin-top: 12px;
      table-layout: fixed;
      font-size: 10px;
    }

    .signature-box {
      position: relative;
      height: 110px;
      width: 50%;
      padding: 0 8px;
      text-align: center;
      vertical-align: top;
    }

    .signature-date {
      margin-bottom: 6px;
    }

    .signature-space {
      position: relative;
      height: 58px;
    }

    .stamp {
      position: absolute;
      top: 0;
      left: 44px;
      width: 78px;
      max-height: 78px;
      object-fit: contain;
      opacity: 0.86;
    }

    .signature-img {
      position: absolute;
      top: 8px;
      left: 104px;
      width: 104px;
      max-height: 56px;
      object-fit: contain;
    }

    .signature-name {
      font-weight: 700;
    }

    .signature-placeholder {
      position: absolute;
      right: 18px;
      bottom: 48px;
      left: 18px;
      border-bottom: 1px dashed #94a3b8;
      color: #64748b;
      font-size: 11px;
    }

    .template-footer {
      <?= $forPdf ? 'position: fixed; right: 0; bottom: 0; left: 0;' : '' ?>
      margin-top: <?= $forPdf ? '0' : '48px' ?>;
      padding-top: 6px;
      color: #9ca3af;
      font-size: 7px;
      text-align: center;
    }

    @media print {
      body {
        background: #ffffff;
      }

      .print-toolbar {
        display: none;
      }

      .verification,
      .signature-placeholder {
        display: none;
      }

      .page {
        width: auto;
        min-height: auto;
        margin: 0;
        padding: 0;
        box-shadow: none;
      }
    }
  </style>
</head>
<body>
  <?php if (! $forPdf): ?>
    <div class="print-toolbar">
      <button type="button" onclick="window.print()">Cetak / Simpan PDF</button>
      <a href="<?= site_url('layanan-online?kode=' . rawurlencode($pengajuan['kode_pengajuan'])) ?>">Kembali ke Status</a>
    </div>
  <?php endif; ?>

  <main class="page">
    <header class="letterhead">
      <h1>RUKUN WARGA 05</h1>
      <h2>DESA <?= rw_esc(strtoupper($desa)) ?></h2>
      <p class="region">KECAMATAN <?= rw_esc(strtoupper($kecamatan)) ?> KABUPATEN <?= rw_esc(strtoupper($kabupaten)) ?></p>
      <p class="address">Sekretariat: <?= rw_esc($alamat) ?></p>
      <p class="contact">Website: www.rw05citeureup.my.id | Instagram: @rw05citeureup</p>
      <p class="motto">TRANSPARAN - TERTIB - MELAYANI</p>
      <div class="kop-line"></div>
    </header>

    <table class="top-meta">
      <tr>
        <td>
          <table class="meta-list">
            <tr><td>Nomor</td><td>:</td><td><?= rw_esc($nomorDisplay) ?></td></tr>
            <tr><td>Lampiran</td><td>:</td><td><?= rw_esc($lampiranDisplay) ?></td></tr>
            <tr><td>Perihal</td><td>:</td><td><?= rw_esc($perihal) ?></td></tr>
          </table>
        </td>
        <td class="letter-date"><?= rw_esc($desa) ?>, <?= rw_esc($tanggal) ?></td>
      </tr>
    </table>

    <section class="recipient">
      <p>Kepada Yth.</p>
      <p><?= rw_esc($recipientName) ?></p>
      <p>di</p>
      <p>Tempat</p>
    </section>

    <section class="letter-title">
      <h2><?= rw_esc($letterTitle) ?></h2>
    </section>

    <section class="content">
      <?php if ($salutation !== ''): ?>
        <p class="salutation"><?= rw_esc($salutation) ?></p>
      <?php endif; ?>

      <?php $leadText = $renderTemplateText((string) ($template['lead'] ?? 'Yang bertanda tangan di bawah ini, pengurus {{site_name}}, menerangkan bahwa:')); ?>
      <?php if ($leadText !== ''): ?>
        <p><?= rw_esc($leadText) ?></p>
      <?php endif; ?>

      <?php if ($templateRows !== []): ?>
        <table class="data-table">
          <?php foreach ($templateRows as $row): ?>
            <?php
            $rowLabel = trim((string) ($row['label'] ?? ''));
            $rowValue = $renderTemplateText((string) ($row['value'] ?? ''));
            $rowValue = $rowValue !== '' ? $rowValue : '-';
            ?>
            <?php if ($rowLabel !== ''): ?>
              <tr><td><?= rw_esc($rowLabel) ?></td><td>:</td><td><?= nl2br(rw_esc($rowValue)) ?></td></tr>
            <?php endif; ?>
          <?php endforeach; ?>
        </table>
      <?php elseif (! $hasTemplateRows && $structuredEntries !== []): ?>
        <table class="data-table">
          <tr><td>Nama</td><td>:</td><td><?= rw_esc($pengajuan['nama']) ?></td></tr>
          <tr><td>RT/RW</td><td>:</td><td><?= rw_esc(normalize_rt_code($pengajuan['rt'] ?? '')) ?> / 05</td></tr>
          <tr><td>Alamat</td><td>:</td><td><?= rw_esc($pengajuan['alamat']) ?></td></tr>
          <tr><td><?= rw_esc($purposeLabel) ?></td><td>:</td><td><?= rw_esc($pengajuan['keperluan']) ?></td></tr>
          <?php foreach ($structuredEntries as $entry): ?>
            <?php if (($entry['key'] ?? '') !== 'tujuan_surat'): ?>
              <tr><td><?= rw_esc($entry['label']) ?></td><td>:</td><td><?= nl2br(rw_esc($entry['value'])) ?></td></tr>
            <?php endif; ?>
          <?php endforeach; ?>
        </table>
      <?php endif; ?>

      <?php foreach ($bodyParagraphs as $paragraph): ?>
        <?php
        $paragraphText = $renderTemplateText((string) $paragraph);
        $emptyNumberedLine = (bool) preg_match('/^[0-9]+\\.\\s*$/', $paragraphText);
        $emptyDecisionLine = (bool) preg_match('/^[A-Z]+:\\s*$/', $paragraphText);
        ?>
        <?php if ($paragraphText !== '' && ! $emptyNumberedLine && ! $emptyDecisionLine): ?>
          <p><?= rw_esc($paragraphText) ?></p>
        <?php endif; ?>
      <?php endforeach; ?>

      <?php if (! empty($pengajuan['detail'])): ?>
        <p><strong><?= rw_esc($detailLabel) ?>:</strong> <?= nl2br(rw_esc($pengajuan['detail'])) ?></p>
      <?php endif; ?>

      <?php if (! empty($pengajuan['catatan_admin'])): ?>
        <p>Catatan pengurus: <?= nl2br(rw_esc($pengajuan['catatan_admin'])) ?></p>
      <?php endif; ?>

      <?php if (trim($closingText) !== ''): ?>
        <p><?= rw_esc($renderTemplateText($closingText)) ?></p>
      <?php endif; ?>

      <?php if (! $forPdf): ?>
        <div class="verification">
          Kode pengajuan: <strong><?= rw_esc($pengajuan['kode_pengajuan']) ?></strong> |
          Status: <strong><?= rw_esc($statusLabel) ?></strong> |
          Cek ulang melalui halaman layanan online RW 05.
        </div>
      <?php endif; ?>
    </section>

    <table class="signature-row">
      <tr>
      <td class="signature-box">
        <div class="signature-date">Mengetahui,</div>
        <div>Ketua RT <?= rw_esc($rtCode !== '' ? $rtCode : '.......') ?></div>
        <?php if (! $forPdf): ?>
          <div class="signature-placeholder">Tanda tangan RT</div>
        <?php endif; ?>
        <div class="signature-space"></div>
        <div>( <?= rw_esc($rtKetuaDisplayName !== '' ? $rtKetuaDisplayName : '................................') ?> )</div>
      </td>
      <td class="signature-box">
        <div class="signature-date">Hormat kami,</div>
        <div>Ketua RW 05 Desa <?= rw_esc($desa) ?></div>
        <div class="signature-space">
          <?php if (! empty($stempelUrl)): ?>
            <img src="<?= rw_esc($stempelUrl) ?>" alt="Stempel RW" class="stamp">
          <?php endif; ?>
          <?php if (! empty($ttdUrl)): ?>
            <img src="<?= rw_esc($ttdUrl) ?>" alt="Tanda tangan Ketua RW" class="signature-img">
          <?php endif; ?>
        </div>
        <div class="signature-name"><?= rw_esc($ketuaDisplayName) ?></div>
      </td>
      </tr>
    </table>

    <footer class="template-footer">Transparan • Tertib • Melayani</footer>
  </main>
</body>
</html>
