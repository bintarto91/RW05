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
      <?php if ($rowClass === 'service'): ?>
        <i class="rw-org-main-trunk" aria-hidden="true"></i>
      <?php endif; ?>
      <?php if ($rowClass === 'command'): ?>
        <i class="rw-org-advisory rw-org-advisory-left" aria-hidden="true"></i>
        <i class="rw-org-advisory rw-org-advisory-right" aria-hidden="true"></i>
      <?php endif; ?>
    </div>
    <?php if ($rowClass !== 'support'): ?>
      <div class="rw-org-link rw-org-link-after-<?= rw_esc($rowClass) ?>" aria-hidden="true">
        <?php if ($rowClass === 'command'): ?>
          <i class="branch branch-left"></i>
          <i class="branch branch-center"></i>
          <i class="branch branch-right"></i>
        <?php elseif ($rowClass === 'core'): ?>
          <i class="branch branch-service"></i>
          <i class="branch branch-main"></i>
        <?php endif; ?>
      </div>
    <?php endif; ?>
  <?php endforeach; ?>
  <div class="rw-org-legend" aria-label="Keterangan garis diagram">
    <span><i class="solid"></i> Struktur / Komando</span>
    <span><i class="dashed"></i> Koordinasi / Kemitraan</span>
  </div>
</div>
