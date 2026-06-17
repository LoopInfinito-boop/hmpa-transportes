<?php $pageTitle = $demanda['codigo']; include VIEWS.'/layout/header.php'; ?>
<div style="display:flex;gap:10px;margin-bottom:16px;align-items:center;">
  <a href="/demandas" class="btn-outline btn-sm"><i class="ti ti-arrow-left"></i> Voltar</a>
  <h2 style="font-size:18px;font-weight:800;"><?= h($demanda['codigo']) ?></h2>
  <?= pillStatus($demanda['status']) ?>
  <?= pillPrioridade($demanda['prioridade']) ?>
  <span class="pill pill-gray"><?= $demanda['tipo']==='ambulancia'?'🚑 Ambulância':'🚗 Carro de apoio' ?></span>
</div>
<div class="grid-2">
  <div class="card">
    <div class="card-title"><i class="ti ti-info-circle"></i> Informações gerais</div>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;font-size:13px;">
      <div><div class="f-label">Solicitante</div><div style="font-weight:600;margin-top:3px;"><?= h($demanda['solicitante_nome']) ?></div></div>
      <div><div class="f-label">Data / Hora</div><div style="font-weight:600;margin-top:3px;"><?= formatarData($demanda['criado_em']) ?></div></div>
      <div><div class="f-label">Origem</div><div style="font-weight:600;margin-top:3px;"><?= h($demanda['origem']) ?></div></div>
      <div><div class="f-label">Destino</div><div style="font-weight:600;margin-top:3px;"><?= h($demanda['destino']) ?></div></div>
      <div><div class="f-label">Motorista</div><div style="font-weight:600;margin-top:3px;"><?= h($demanda['motorista_nome'] ?? 'Não atribuído') ?></div></div>
      <div><div class="f-label">Veículo</div><div style="font-weight:600;margin-top:3px;"><?= h($demanda['veiculo_nome'] ?? '—') ?> <?= $demanda['veiculo_placa']?'('.$demanda['veiculo_placa'].')':'' ?></div></div>
    </div>
    <?php if($demanda['observacoes']): ?>
      <div style="margin-top:12px;padding-top:12px;border-top:1px solid var(--border);font-size:13px;">
        <div class="f-label">Observações</div>
        <div style="margin-top:4px;"><?= h($demanda['observacoes']) ?></div>
      </div>
    <?php endif; ?>
  </div>
  <div class="card">
    <div class="card-title"><i class="ti ti-speedometer"></i> Hodômetro</div>
    <?php if($demanda['km_inicio'] && $demanda['km_fim']): ?>
      <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:10px;text-align:center;">
        <div style="background:var(--s2);border-radius:8px;padding:12px;"><div style="font-size:11px;color:var(--text2);">KM Inicial</div><div style="font-size:20px;font-weight:800;margin-top:4px;"><?= number_format($demanda['km_inicio'],1) ?></div></div>
        <div style="background:var(--s2);border-radius:8px;padding:12px;"><div style="font-size:11px;color:var(--text2);">KM Final</div><div style="font-size:20px;font-weight:800;margin-top:4px;"><?= number_format($demanda['km_fim'],1) ?></div></div>
        <div style="background:rgba(6,193,103,.1);border:1px solid rgba(6,193,103,.3);border-radius:8px;padding:12px;"><div style="font-size:11px;color:var(--green);">KM Rodados</div><div style="font-size:20px;font-weight:800;color:var(--green);margin-top:4px;"><?= number_format($demanda['km_rodados'],1) ?></div></div>
      </div>
    <?php else: ?>
      <div class="empty-state" style="padding:20px;"><i class="ti ti-clock"></i><p>Aguardando conclusão</p></div>
    <?php endif; ?>
  </div>
</div>
<?php if($paciente): ?>
<div class="card">
  <div class="card-title"><i class="ti ti-user"></i> Dados do paciente</div>
  <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;font-size:13px;">
    <div><div class="f-label">Nome</div><div style="font-weight:600;margin-top:3px;"><?= h($paciente['nome']) ?></div></div>
    <div><div class="f-label">Prontuário</div><div style="font-weight:600;margin-top:3px;"><?= h($paciente['prontuario']??'—') ?></div></div>
    <div><div class="f-label">CPF</div><div style="font-weight:600;margin-top:3px;"><?= h($paciente['cpf']??'—') ?></div></div>
    <div><div class="f-label">Data de nascimento</div><div style="font-weight:600;margin-top:3px;"><?= $paciente['nascimento']?date('d/m/Y',strtotime($paciente['nascimento'])):'—' ?></div></div>
    <div><div class="f-label">Estado clínico</div><div style="font-weight:600;margin-top:3px;"><?= h($paciente['estado']??'—') ?></div></div>
    <div><div class="f-label">Suporte</div><div style="font-weight:600;margin-top:3px;"><?= h($paciente['suporte']??'—') ?></div></div>
    <div><div class="f-label">Acompanhante</div><div style="font-weight:600;margin-top:3px;"><?= h($paciente['acompanhante']??'—') ?></div></div>
    <div><div class="f-label">Médico</div><div style="font-weight:600;margin-top:3px;"><?= h($paciente['medico']??'—') ?></div></div>
    <div><div class="f-label">CID</div><div style="font-weight:600;margin-top:3px;"><?= h($paciente['cid']??'—') ?></div></div>
  </div>
</div>
<?php endif; ?>
<?php if(!empty($itens)): ?>
<div class="card">
  <div class="card-title"><i class="ti ti-package"></i> Itens transportados</div>
  <div class="table-wrap"><table>
    <thead><tr><th>Descrição</th><th>Quantidade</th><th>Setor de entrega</th></tr></thead>
    <tbody>
      <?php foreach($itens as $item): ?>
        <tr><td><?= h($item['descricao']??'—') ?></td><td><?= h($item['quantidade']??'—') ?></td><td><?= h($item['setor_entrega']??'—') ?></td></tr>
      <?php endforeach; ?>
    </tbody>
  </table></div>
</div>
<?php endif; ?>
<?php include VIEWS.'/layout/footer.php'; ?>
