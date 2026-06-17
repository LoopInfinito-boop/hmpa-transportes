<?php $pageTitle = 'Histórico de corridas'; include VIEWS.'/layout/header.php'; ?>
<div class="card">
  <div class="card-title"><i class="ti ti-history"></i> Histórico de transportes</div>
  <form method="GET" style="display:flex;gap:10px;margin-bottom:16px;flex-wrap:wrap;align-items:flex-end;">
    <div class="f-group"><label class="f-label">Data início</label><input class="f-input" type="date" name="data_inicio" value="<?= h($_GET['data_inicio']??'') ?>" style="width:auto;"/></div>
    <div class="f-group"><label class="f-label">Data fim</label><input class="f-input" type="date" name="data_fim" value="<?= h($_GET['data_fim']??'') ?>" style="width:auto;"/></div>
    <button class="btn btn-sm" type="submit"><i class="ti ti-filter"></i> Filtrar</button>
    <a href="/historico" class="btn-outline btn-sm">Limpar</a>
  </form>
  <?php if(empty($demandas)): ?>
    <div class="empty-state"><i class="ti ti-file-off"></i><p>Nenhum registro encontrado</p></div>
  <?php else: ?>
    <div class="table-wrap"><table>
      <thead><tr><th>Código</th><th>Data/Hora</th><th>Tipo</th><th>Origem</th><th>Destino</th><th>Motorista</th><th>Veículo</th><th>KM Inicial</th><th>KM Final</th><th>KM Rodados</th><th>Status</th></tr></thead>
      <tbody>
        <?php foreach($demandas as $d): ?>
          <tr>
            <td><a href="/demandas/<?= $d['id'] ?>" style="color:var(--green);font-weight:700;"><?= h($d['codigo']) ?></a></td>
            <td style="font-size:12px;color:var(--text2);"><?= formatarData($d['criado_em']) ?></td>
            <td><span class="pill <?= $d['tipo']==='ambulancia'?'pill-red':'pill-blue' ?>"><?= $d['tipo']==='ambulancia'?'🚑':'🚗' ?></span></td>
            <td><?= h($d['origem']) ?></td>
            <td><?= h($d['destino']) ?></td>
            <td><?= h($d['motorista_nome']??'—') ?></td>
            <td><?= h($d['veiculo_nome']??'—') ?></td>
            <td><?= $d['km_inicio']?number_format($d['km_inicio'],1).' km':'—' ?></td>
            <td><?= $d['km_fim']?number_format($d['km_fim'],1).' km':'—' ?></td>
            <td style="color:var(--green);font-weight:700;"><?= $d['km_rodados']?number_format($d['km_rodados'],1).' km':'—' ?></td>
            <td><?= pillStatus($d['status']) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table></div>
  <?php endif; ?>
</div>
<?php include VIEWS.'/layout/footer.php'; ?>
