<?php $pageTitle = $titulo ?? 'Demandas'; include VIEWS.'/layout/header.php'; ?>
<div class="card">
  <div class="card-title"><i class="ti ti-file-text"></i> <?= h($titulo ?? 'Demandas') ?></div>
  <div style="display:flex;gap:8px;margin-bottom:16px;flex-wrap:wrap;">
    <?php foreach([''=>'Todas','aguardando'=>'Aguardando','andamento'=>'Em andamento','concluida'=>'Concluídas','cancelada'=>'Canceladas'] as $val=>$lbl): ?>
      <a href="?status=<?= $val ?>" class="<?= ($_GET['status']??'')===$val ? 'btn btn-sm' : 'btn-outline btn-sm' ?>"><?= $lbl ?></a>
    <?php endforeach; ?>
    <a href="/demandas/nova" class="btn btn-sm" style="margin-left:auto;"><i class="ti ti-plus"></i> Nova</a>
  </div>
  <?php if(empty($demandas)): ?>
    <div class="empty-state"><i class="ti ti-file-off"></i><p>Nenhuma demanda encontrada</p></div>
  <?php else: ?>
    <div class="table-wrap">
      <table>
        <thead><tr><th>Código</th><th>Tipo</th><th>Origem</th><th>Destino</th><th>Prioridade</th><th>Motorista</th><th>KM Rodados</th><th>Status</th><th>Data</th><th></th></tr></thead>
        <tbody>
          <?php foreach($demandas as $d): ?>
            <tr>
              <td><b><?= h($d['codigo']) ?></b></td>
              <td><span class="pill <?= $d['tipo']==='ambulancia'?'pill-red':'pill-blue' ?>"><?= $d['tipo']==='ambulancia'?'🚑 Amb.':'🚗 Apoio' ?></span></td>
              <td><?= h($d['origem']) ?></td>
              <td><?= h($d['destino']) ?></td>
              <td><?= pillPrioridade($d['prioridade']) ?></td>
              <td><?= h($d['motorista_nome'] ?? '—') ?></td>
              <td style="color:var(--green);font-weight:700;"><?= $d['km_rodados'] ? number_format($d['km_rodados'],1).' km' : '—' ?></td>
              <td><?= pillStatus($d['status']) ?></td>
              <td style="font-size:12px;color:var(--text2);"><?= formatarData($d['criado_em']) ?></td>
              <td><a href="/demandas/<?= $d['id'] ?>" class="btn-outline btn-sm"><i class="ti ti-eye"></i></a></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>
<?php include VIEWS.'/layout/footer.php'; ?>
