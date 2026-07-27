<?= $this->extend('layouts/public') ?>

<?= $this->section('content') ?>
<section class="page-hero education-page-hero education-topic-page-hero">
  <div class="container page-hero-grid">
    <div data-reveal>
      <p class="eyebrow">Edukasi kesehatan <?= rw_esc($topic['number']) ?></p>
      <h1><?= rw_esc($topic['title']) ?></h1>
      <p class="hero-text"><?= rw_esc($topic['description']) ?></p>
      <div class="hero-actions">
        <a href="#materi-topik" class="btn light">Lihat Materi</a>
        <a href="<?= site_url('edukasi-kesehatan') ?>" class="btn outline-light">Semua Topik</a>
      </div>
    </div>
    <aside class="page-callout education-callout" data-reveal>
      <span>Yang dapat dipelajari</span>
      <ul class="education-topic-summary-list">
        <?php foreach ($topic['items'] as $item): ?>
          <li><?= rw_esc($item) ?></li>
        <?php endforeach; ?>
      </ul>
    </aside>
  </div>
</section>

<section class="section education-resources-section" id="materi-topik" aria-labelledby="education-resources-title">
  <div class="container">
    <div class="education-resource-heading" data-reveal>
      <div class="section-title">
        <p class="eyebrow">Perpustakaan materi</p>
        <h2 id="education-resources-title">Poster, video, dan artikel.</h2>
        <p>Pilih jenis materi atau buka langsung karya yang ingin dipelajari.</p>
      </div>
      <a class="education-back-link" href="<?= site_url('edukasi-kesehatan') ?>">← Kembali ke semua topik</a>
    </div>

    <nav class="education-filter-nav" aria-label="Filter jenis materi" data-reveal>
      <a href="<?= site_url('edukasi-kesehatan/' . $category) ?>" class="<?= $selectedType === '' ? 'is-active' : '' ?>">Semua</a>
      <?php foreach ($typeOptions as $value => $label): ?>
        <a href="<?= site_url('edukasi-kesehatan/' . $category) . '?jenis=' . rawurlencode($value) ?>" class="<?= $selectedType === $value ? 'is-active' : '' ?>"><?= rw_esc($label) ?></a>
      <?php endforeach; ?>
    </nav>

    <?php if (! empty($materials)): ?>
      <div class="education-resource-grid">
        <?php foreach ($materials as $material): ?>
          <?php
          $type = (string) ($material['jenis'] ?? '');
          $materialUrl = edukasi_material_public_url($material);
          $hasFile = trim((string) ($material['file_path'] ?? '')) !== '';
          $meta = array_filter([
              trim((string) ($material['institusi'] ?? '')),
              trim((string) ($material['tahun'] ?? '')),
          ], static fn ($value) => $value !== '');
          ?>
          <article class="education-resource-card type-<?= rw_esc($type) ?>" data-reveal>
            <div class="education-resource-head">
              <span class="education-type-label"><?= rw_esc($typeOptions[$type] ?? ucfirst($type)) ?></span>
              <span class="education-resource-number"><?= rw_esc($topic['number']) ?></span>
            </div>
            <div class="education-resource-copy">
              <h2><?= rw_esc($material['judul'] ?? '') ?></h2>
              <?php if (! empty($material['ringkasan'])): ?>
                <p><?= rw_esc($material['ringkasan']) ?></p>
              <?php endif; ?>
            </div>
            <div class="education-resource-author">
              <span>Dosen / Penulis</span>
              <strong><?= rw_esc($material['penulis'] ?? '') ?></strong>
              <?php if (! empty($meta)): ?><small><?= rw_esc(implode(' • ', $meta)) ?></small><?php endif; ?>
            </div>
            <?php if ($materialUrl !== ''): ?>
              <a
                href="<?= rw_esc($materialUrl) ?>"
                class="btn primary education-resource-action education-media-trigger"
                target="_blank"
                rel="noopener noreferrer"
                data-media-type="<?= rw_esc($type) ?>"
                data-media-title="<?= rw_esc($material['judul'] ?? 'Materi Edukasi') ?>"
                data-media-summary="<?= rw_esc($material['ringkasan'] ?? '') ?>"
                data-media-author="<?= rw_esc($material['penulis'] ?? '') ?>"
                data-media-file="<?= $hasFile ? '1' : '0' ?>"
                aria-haspopup="dialog"
              >
                <?= rw_esc(edukasi_material_action_label($type, $hasFile)) ?>
              </a>
            <?php else: ?>
              <span class="education-resource-unavailable">Media belum tersedia</span>
            <?php endif; ?>
          </article>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <div class="education-empty-state" data-reveal>
        <strong>Belum ada materi yang ditayangkan.</strong>
        <p>Coba pilih jenis materi lain atau kembali lagi setelah admin menambahkan karya baru.</p>
        <a href="<?= site_url('edukasi-kesehatan/' . $category) ?>" class="btn secondary">Lihat Semua Jenis</a>
      </div>
    <?php endif; ?>
  </div>
</section>

<div class="education-poster-modal" id="educationMediaModal" hidden aria-hidden="true">
  <button type="button" class="education-poster-backdrop" data-media-close aria-label="Tutup tampilan materi"></button>
  <div class="education-poster-dialog" role="dialog" aria-modal="true" aria-labelledby="educationMediaTitle">
    <header class="education-poster-header">
      <div>
        <span data-media-label>Materi edukasi</span>
        <h2 id="educationMediaTitle">Materi Edukasi</h2>
      </div>
      <button type="button" class="education-poster-close" data-media-close aria-label="Tutup materi">×</button>
    </header>
    <div class="education-poster-canvas" data-media-canvas>
      <img src="" alt="" data-media-image hidden>
      <video controls playsinline preload="metadata" data-media-video hidden></video>
      <iframe src="about:blank" title="Pratinjau materi edukasi" loading="lazy" allow="accelerometer; autoplay; encrypted-media; picture-in-picture" allowfullscreen data-media-frame hidden></iframe>
      <article class="education-media-info" data-media-info hidden>
        <span data-media-info-label>Materi edukasi</span>
        <h3 data-media-info-title>Materi Edukasi</h3>
        <p data-media-info-summary></p>
        <strong data-media-info-author></strong>
        <small data-media-info-note></small>
      </article>
    </div>
    <div class="education-poster-actions">
      <a href="#" class="btn primary" target="_blank" rel="noopener noreferrer" data-media-full>Buka Materi</a>
      <button type="button" class="btn secondary" data-media-close>Tutup</button>
    </div>
  </div>
</div>

<script>
(() => {
  const modal = document.getElementById('educationMediaModal');
  const canvas = modal?.querySelector('[data-media-canvas]');
  const image = modal?.querySelector('[data-media-image]');
  const video = modal?.querySelector('[data-media-video]');
  const frame = modal?.querySelector('[data-media-frame]');
  const info = modal?.querySelector('[data-media-info]');
  const infoLabel = modal?.querySelector('[data-media-info-label]');
  const infoTitle = modal?.querySelector('[data-media-info-title]');
  const infoSummary = modal?.querySelector('[data-media-info-summary]');
  const infoAuthor = modal?.querySelector('[data-media-info-author]');
  const infoNote = modal?.querySelector('[data-media-info-note]');
  const label = modal?.querySelector('[data-media-label]');
  const title = document.getElementById('educationMediaTitle');
  const fullLink = modal?.querySelector('[data-media-full]');
  const closeButton = modal?.querySelector('.education-poster-close');
  const triggers = document.querySelectorAll('.education-media-trigger');
  let lastTrigger = null;

  if (!modal || !canvas || !image || !video || !frame || !info || !label || !title || !fullLink || !closeButton || triggers.length === 0) return;

  const getYoutubeEmbedUrl = (sourceUrl) => {
    try {
      const url = new URL(sourceUrl);
      const host = url.hostname.replace(/^www\./, '');
      let videoId = '';
      if (host === 'youtu.be') {
        videoId = url.pathname.split('/').filter(Boolean)[0] || '';
      } else if (host === 'youtube.com' || host === 'm.youtube.com') {
        if (url.pathname === '/watch') videoId = url.searchParams.get('v') || '';
        if (url.pathname.startsWith('/shorts/') || url.pathname.startsWith('/embed/')) {
          videoId = url.pathname.split('/').filter(Boolean)[1] || '';
        }
      }
      return /^[a-zA-Z0-9_-]{6,20}$/.test(videoId)
        ? `https://www.youtube-nocookie.com/embed/${videoId}?rel=0`
        : '';
    } catch (error) {
      return '';
    }
  };

  const resetMedia = () => {
    image.hidden = true;
    image.src = '';
    image.alt = '';
    video.pause();
    video.hidden = true;
    video.removeAttribute('src');
    video.load();
    frame.hidden = true;
    frame.src = 'about:blank';
    frame.classList.remove('is-document');
    info.hidden = true;
    canvas.classList.remove('is-video', 'is-document', 'is-info');
  };

  const showInfo = (type, materialTitle, summary, author) => {
    infoLabel.textContent = type === 'video' ? 'Video edukasi' : 'Artikel edukasi';
    infoTitle.textContent = materialTitle;
    infoSummary.textContent = summary || (type === 'video'
      ? 'Video tersedia melalui tautan sumber yang dicantumkan.'
      : 'Artikel tersedia melalui tautan sumber yang dicantumkan.');
    infoAuthor.textContent = author;
    infoAuthor.hidden = !author;
    infoNote.textContent = type === 'video'
      ? 'Gunakan tombol di bawah untuk membuka video dari sumbernya.'
      : 'Gunakan tombol di bawah untuk membaca artikel lengkap dari sumbernya.';
    info.hidden = false;
    canvas.classList.add('is-info');
  };

  const closeMedia = () => {
    modal.hidden = true;
    modal.setAttribute('aria-hidden', 'true');
    document.body.classList.remove('poster-modal-open');
    resetMedia();
    if (lastTrigger) lastTrigger.focus();
  };

  triggers.forEach((trigger) => {
    trigger.addEventListener('click', (event) => {
      event.preventDefault();
      const type = trigger.dataset.mediaType || 'artikel';
      const materialTitle = trigger.dataset.mediaTitle || 'Materi Edukasi';
      const summary = trigger.dataset.mediaSummary || '';
      const author = trigger.dataset.mediaAuthor || '';
      const hasFile = trigger.dataset.mediaFile === '1';
      const sourceUrl = trigger.href;
      const sourcePath = new URL(sourceUrl).pathname.toLowerCase();

      lastTrigger = trigger;
      resetMedia();
      label.textContent = type === 'poster' ? 'Poster edukasi' : (type === 'video' ? 'Video edukasi' : 'Artikel edukasi');
      title.textContent = materialTitle;
      fullLink.href = sourceUrl;

      if (type === 'poster') {
        image.src = sourceUrl;
        image.alt = `Poster ${materialTitle}`;
        image.hidden = false;
        fullLink.textContent = 'Buka Ukuran Penuh';
      } else if (type === 'video') {
        const youtubeEmbedUrl = getYoutubeEmbedUrl(sourceUrl);
        if (youtubeEmbedUrl) {
          frame.src = youtubeEmbedUrl;
          frame.title = `Video ${materialTitle}`;
          frame.hidden = false;
          canvas.classList.add('is-video');
        } else if (/\.(mp4|webm)$/.test(sourcePath)) {
          video.src = sourceUrl;
          video.hidden = false;
          canvas.classList.add('is-video');
        } else {
          showInfo(type, materialTitle, summary, author);
        }
        fullLink.textContent = 'Buka Video';
      } else {
        if (hasFile || sourcePath.endsWith('.pdf')) {
          frame.src = sourceUrl;
          frame.title = `Artikel ${materialTitle}`;
          frame.classList.add('is-document');
          frame.hidden = false;
          canvas.classList.add('is-document');
        } else {
          showInfo(type, materialTitle, summary, author);
        }
        fullLink.textContent = hasFile || sourcePath.endsWith('.pdf') ? 'Buka PDF' : 'Baca Artikel';
      }

      modal.hidden = false;
      modal.setAttribute('aria-hidden', 'false');
      document.body.classList.add('poster-modal-open');
      closeButton.focus();
    });
  });

  modal.querySelectorAll('[data-media-close]').forEach((button) => {
    button.addEventListener('click', closeMedia);
  });

  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && !modal.hidden) closeMedia();
  });
})();
</script>

<section class="section education-note-section">
  <div class="container">
    <div class="education-note" data-reveal>
      <div>
        <p class="eyebrow">Catatan penting</p>
        <h2>Gunakan materi kesehatan sebagai edukasi.</h2>
        <p>Materi ini tidak menggantikan diagnosis atau pemeriksaan tenaga kesehatan. Untuk keluhan dan penanganan, hubungi fasilitas kesehatan.</p>
      </div>
      <a href="<?= site_url('kesehatan') ?>#jadwal-bantuan" class="btn primary">Lihat Jalur Bantuan</a>
    </div>
  </div>
</section>
<?= $this->endSection() ?>
