<?php $pageTitle = 'Relatório financeiro'; include VIEWS.'/layout/header.php'; ?>
<div class="stats-row">
  <div class="stat-card"><div class="stat-val"><?= $stats['concluida'] ?></div><div class="stat-label">Corridas concluídas</div></div>
  <div class="stat-card"><div class="stat-val" style="color:var(--green);"><?= number_format($stats['total_km'],1) ?></div><div class="stat-label">Km totais</div></div>
  <div class="stat-card"><div class="stat-val" style="color:var(--amber);">R$ <?= number_format($stats['total_km']*0.8,2,',','.') ?></div><div class="stat-label">Custo estimado</div></div>
  <div class="stat-card"><div class="stat-val"><?= $stats['concluida']>0?number_format($stats['total_km']/$stats['concluida'],1):'0' ?></div><div class="stat-label">Km médios/corrida</div></div>
</div>
<div class="card">
  <div class="card-title"><i class="ti ti-chart-bar"></i> Consumo por veículo</div>
  <div class="table-wrap"><table>
    <thead><tr><th>Veículo</th><th>Placa</th><th>Corridas</th><th>Km rodados</th><th>Combustível est.</th><th>Custo est. (R$0,80/km)</th></tr></thead>
    <tbody>
      <?php foreach($relVeiculos as $v): ?>
        <tr>
          <td><b><?= h($v['nome']) ?></b></td>
          <td><?= h($v['placa']) ?></td>
          <td><?= $v['corridas'] ?></td>
          <td style="color:var(--green);font-weight:700;"><?= number_format($v['km_total'],1) ?> km</td>
          <td><?= number_format($v['km_total']/10,1) ?> L</td>
          <td style="color:var(--amber);font-weight:700;">R$ <?= number_format($v['custo_est'],2,',','.') ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table></div>
</div>
<?php include VIEWS.'/layout/footer.php'; ?>
