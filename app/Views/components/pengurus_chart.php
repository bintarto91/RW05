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
?>
<div class="rw-org-diagram rw-org-diagram-<?= rw_esc($chartVariant) ?>" aria-label="Diagram kepengurusan RW 05">
  <header class="rw-org-official-head">
    <h2>Struktur Organisasi Kepengurusan RW 05 LAMAJANG PEUNTAS</h2>
    <p>Desa Citeureup <b>•</b> Kecamatan Dayeuhkolot <b>•</b> Kabupaten Bandung</p>
    <span>Periode 2026–2028</span>
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
                <div class="rw-org-people">
                  <?php foreach ($card['people'] as $person): ?>
                    <div class="rw-org-person">
                      <span aria-hidden="true"><?= rw_esc(strtoupper(substr((string) ($person['nama'] ?? 'P'), 0, 1))) ?></span>
                      <div>
                        <strong><?= rw_esc($person['nama'] ?? '') ?></strong>
                        <?php if (! empty($person['rt'])): ?><small>RT <?= rw_esc($person['rt']) ?></small><?php endif; ?>
                      </div>
                    </div>
                  <?php endforeach; ?>
                </div>
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
