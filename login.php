<?php $pageTitle = 'Dashboard'; include VIEWS.'/layout/header.php'; ?>

<div class="stats-row">
  <div class="stat-card">
    <div class="stat-val"><?= $stats['total'] ?></div>
    <div class="stat-label">Demandas hoje</div>
  </div>
  <div class="stat-card">
    <div class="stat-val" style="color:var(--green);"><?= $stats['concluida'] ?></div>
    <div class="stat-label">Concluídas</div>
  </div>
  <div class="stat-card">
    <div class="stat-val" style="color:var(--amber);"><?= ($stats['aguardando']+$stats['andamento']) ?></div>
    <div class="stat-label">Em aberto</div>
  </div>
  <div class="stat-card">
    <div class="stat-val" style="color:var(--blue);"><?= number_format($stats['total_km'],1) ?></div>
    <div class="stat-label">Km rodados hoje</div>
  </div>
</div>

<div class="grid-2">
  <div class="card">
    <div class="card-title"><i class="ti ti-list-check"></i> Demandas recentes</div>
    <?php if (empty($recentes)): ?>
      <div class="empty-state"><i class="ti ti-file-off"></i><p>Nenhuma demanda hoje</p></div>
    <?php else: ?>
      <?php foreach (array_slice($recentes,0,5) as $d): ?>
        <div style="display:flex;align-items:center;gap:12px;padding:10px 0;border-bottom:1px solid var(--border);">
          <div style="width:36px;height:36px;border-radius:50%;background:<?= $d['tipo']==='ambulancia'?'rgba(209,41,61,.15)':'rgba(39,110,241,.15)' ?>;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <i class="ti <?= $d['tipo']==='ambulancia'?'ti-ambulance':'ti-car-suv' ?>" style="color:<?= $d['tipo']==='ambulancia'?'var(--red)':'var(--blue)' ?>"></i>
          </div>
          <div style="flex:1;">
            <div style="font-size:13px;font-weight:600;"><?= h($d['origem']) ?> → <?= h($d['destino']) ?></div>
            <div style="font-size:11px;color:var(--text2);"><?= h($d['codigo']) ?> · <?= h($d['solicitante_nome']) ?></div>
          </div>
          <?= pillStatus($d['status']) ?>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
  <div class="card">
    <div class="card-title"><i class="ti ti-steering-wheel"></i> Setores cadastrados</div>
    <div style="display:flex;flex-wrap:wrap;gap:6px;">
      <?php foreach ($setores as $s): ?>
        <span class="pill <?= $s['tipo']==='interno'?'pill-blue':'pill-amber' ?>"><?= h($s['nome']) ?></span>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<?php include VIEWS.'/layout/footer.php'; ?>
