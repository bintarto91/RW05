<?php
$chartGroups = is_array($chartGroups ?? null) ? $chartGroups : [];
$chartVariant = ($chartVariant ?? 'public') === 'admin' ? 'admin' : 'public';
$diagramRows = [
    ['command', ['pembina', 'ketua', 'penasihat']],
    ['core', ['sekretaris', 'bendahara', 'wilayah']],
    ['service', ['pelayanan']],
    ['bidang', ['bidang']],
    ['unit', ['unit']],
    ['support', ['mitra']],
];
$iconFor = static function (string $groupKey, string $title = ''): string {
    $needle = strtolower($title);
    $icon = match (true) {
        $groupKey === 'ketua' => 'group',
        $groupKey === 'sekretaris' => 'document',
        $groupKey === 'bendahara' => 'wallet',
        $groupKey === 'wilayah' => 'home',
        $groupKey === 'pelayanan' => 'monitor',
        str_contains($needle, 'pembangunan') => 'environment',
        str_contains($needle, 'sosial') => 'heart',
        str_contains($needle, 'keamanan') => 'shield',
        str_contains($needle, 'pendidikan') => 'book',
        str_contains($needle, 'ekonomi') => 'growth',
        str_contains($needle, 'humas') => 'megaphone',
        str_contains($needle, 'pkk') => 'group',
        str_contains($needle, 'posyandu') => 'care',
        str_contains($needle, 'posbindu') => 'pulse',
        str_contains($needle, 'karang') => 'group',
        str_contains($needle, 'dkm') => 'mosque',
        str_contains($needle, 'linmas') => 'shield',
        default => 'person',
    };

    $paths = [
        'person' => '<circle cx="24" cy="16" r="8"/><path d="M10 42c1-11 7-17 14-17s13 6 14 17"/>',
        'group' => '<circle cx="24" cy="14" r="7"/><circle cx="10" cy="18" r="5"/><circle cx="38" cy="18" r="5"/><path d="M13 42c0-11 5-18 11-18s11 7 11 18M2 40c0-8 3-14 9-14M46 40c0-8-3-14-9-14"/>',
        'document' => '<path d="M13 5h16l8 8v30H13zM29 5v9h8M18 22h14M18 28h14M18 34h10"/>',
        'wallet' => '<path d="M8 14h31v27H8zM8 18V9h26v5M29 24h13v10H29z"/><circle cx="34" cy="29" r="1.5"/>',
        'home' => '<path d="M5 23 24 7l19 16M10 21v21h28V21M20 42V29h9v13"/>',
        'monitor' => '<rect x="6" y="8" width="36" height="27" rx="2"/><path d="M17 42h14M24 35v7M13 15h22M13 21h9M26 21h9M13 27h22"/>',
        'environment' => '<path d="M7 27c9-1 15-7 17-18 7 5 12 11 12 19 0 8-6 14-14 14S8 36 7 27Z"/><path d="M14 35c5-8 11-13 19-17"/>',
        'heart' => '<path d="M24 42S6 32 6 18c0-10 13-13 18-4 5-9 18-6 18 4 0 14-18 24-18 24Z"/>',
        'shield' => '<path d="M24 4 40 10v12c0 10-6 17-16 22C14 39 8 32 8 22V10z"/><path d="m24 13 3 6 7 1-5 5 1 7-6-3-6 3 1-7-5-5 7-1z"/>',
        'book' => '<path d="M24 13c-5-6-11-7-17-5v29c7-2 12 0 17 5 5-5 10-7 17-5V8c-6-2-12-1-17 5Z"/><path d="M24 13v29"/>',
        'growth' => '<path d="M8 39h34M12 35V25h7v10M22 35V18h7v17M32 35V11h7v24M11 17l10-7 8 4 11-9"/>',
        'megaphone' => '<path d="M7 21v9h8l19 8V13l-19 8zM15 30l4 11h7l-3-9M38 18l5-4M39 25h6M38 32l5 4"/>',
        'care' => '<circle cx="16" cy="12" r="5"/><path d="M9 39V24c0-6 4-9 8-9s8 3 8 9v15M25 26c4-8 16-8 16 1 0 8-8 13-16 17-8-4-15-9-15-17"/>',
        'pulse' => '<path d="M24 42S6 32 6 18c0-10 13-13 18-4 5-9 18-6 18 4 0 14-18 24-18 24Z"/><path d="M9 27h8l3-8 5 15 4-10 3 3h7"/>',
        'mosque' => '<path d="M8 42h32M11 42V23h26v19M17 23c0-6 3-10 7-13 4 3 7 7 7 13M20 42V31h8v11M7 23h4M37 23h4M9 23V12M39 23V12"/>',
    ];

    return '<svg viewBox="0 0 48 48" aria-hidden="true">' . ($paths[$icon] ?? $paths['person']) . '</svg>';
};
?>
<div class="rw-org-diagram rw-org-diagram-<?= rw_esc($chartVariant) ?>" aria-label="Diagram kepengurusan RW 05">
  <header class="rw-org-official-head">
    <h2>Struktur Organisasi Kepengurusan RW 05 LAMAJANG PEUNTAS</h2>
    <p>Desa Citeureup <b>•</b> Kecamatan Dayeuhkolot <b>•</b> Kabupaten Bandung</p>
    <strong>Transparan <b>•</b> Tertib <b>•</b> Melayani</strong>
  </header>
  <?php foreach ($diagramRows as [$rowClass, $groupKeys]): ?>
    <div class="rw-org-row rw-org-row-<?= rw_esc($rowClass) ?>">
      <?php foreach ($groupKeys as $groupKey): ?>
        <?php
          $group = $chartGroups[$groupKey] ?? ['label' => ucfirst($groupKey), 'items' => []];
          $cards = pengurus_chart_cards($groupKey, $group['items'] ?? []);
        ?>
        <section class="rw-org-group rw-org-group-<?= rw_esc($groupKey) ?><?= $cards === [] ? ' is-empty' : '' ?>">
          <h3><?= rw_esc($group['label']) ?></h3>
          <div class="rw-org-cards">
            <?php foreach ($cards as $card): ?>
              <article class="rw-org-card">
                <div class="rw-org-card-title"><?= rw_esc($card['title']) ?></div>
                <div class="rw-org-card-body">
                  <div class="rw-org-card-icon<?= $groupKey === 'wilayah' ? ' is-accent' : '' ?>">
                    <?= $iconFor($groupKey, (string) ($card['title'] ?? '')) ?>
                  </div>
                  <div class="rw-org-people">
                    <?php foreach ($card['people'] as $person): ?>
                      <div class="rw-org-person">
                        <?php if ($groupKey === 'wilayah' && ! empty($person['jabatan'])): ?>
                          <span class="rw-org-person-role"><?= rw_esc($person['jabatan']) ?></span>
                        <?php endif; ?>
                        <strong><?= rw_esc($person['nama'] ?? '') ?></strong>
                      </div>
                    <?php endforeach; ?>
                  </div>
                </div>
                <?php if ($groupKey === 'pelayanan' && ! empty($card['task'])): ?>
                  <div class="rw-org-service-task"><?= nl2br(rw_esc($card['task'])) ?></div>
                <?php endif; ?>
              </article>
            <?php endforeach; ?>
            <?php if ($cards === []): ?><span class="rw-org-empty">Belum diisi</span><?php endif; ?>
          </div>
        </section>
      <?php endforeach; ?>
    </div>
  <?php endforeach; ?>
  <div class="rw-org-legend" aria-label="Keterangan garis diagram">
    <span><i class="solid"></i> Struktur / Komando</span>
    <span><i class="dashed"></i> Koordinasi / Kemitraan</span>
  </div>
</div>
