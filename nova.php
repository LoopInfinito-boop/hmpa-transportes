<?php $pageTitle = 'Cadastros'; include VIEWS.'/layout/header.php'; ?>
<div class="grid-2">
  <!-- SETORES -->
  <div class="card">
    <div class="card-title"><i class="ti ti-building-hospital"></i> Setores / Destinos</div>
    <div class="form-grid" style="margin-bottom:10px;">
      <div class="f-group"><label class="f-label">Nome do setor</label><input class="f-input" id="ns-nome" placeholder="Ex: UTI Neonatal"/></div>
      <div class="f-group"><label class="f-label">Tipo</label><select class="f-select" id="ns-tipo"><option value="interno">Interno</option><option value="externo">Externo</option></select></div>
    </div>
    <div class="f-group" style="margin-bottom:10px;"><label class="f-label">Andar / Endereço</label><input class="f-input" id="ns-info" placeholder="Ex: 2º andar"/></div>
    <button class="btn btn-sm" onclick="addSetor()"><i class="ti ti-plus"></i> Adicionar setor</button>
    <div id="setores-lista" style="margin-top:14px;background:var(--s2);border-radius:8px;overflow:hidden;">
      <?php foreach($setores as $s): ?>
        <div style="display:flex;align-items:center;justify-content:space-between;padding:10px 13px;border-bottom:1px solid var(--border);font-size:13px;">
          <div><div style="font-weight:600;"><?= h($s['nome']) ?></div><div style="font-size:11px;color:var(--text2);"><?= h($s['tipo']) ?><?= $s['info']?' · '.h($s['info']):'' ?></div></div>
          <button class="btn btn-red btn-sm" onclick="delSetor(<?= $s['id'] ?>,this)"><i class="ti ti-trash"></i></button>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
  <!-- MOTORISTAS -->
  <div class="card">
    <div class="card-title"><i class="ti ti-steering-wheel"></i> Motoristas</div>
    <div style="background:var(--s2);border-radius:8px;overflow:hidden;margin-bottom:14px;">
      <?php foreach($motoristas as $m): ?>
        <div style="display:flex;align-items:center;gap:12px;padding:11px 13px;border-bottom:1px solid var(--border);">
          <div style="width:34px;height:34px;border-radius:50%;background:var(--s4);display:flex;align-items:center;justify-content:center;font-weight:800;font-size:12px;flex-shrink:0;"><?= strtoupper(substr($m['nome'],0,1).substr(strstr($m['nome'],' '),1,1)) ?></div>
          <div style="flex:1;"><div style="font-size:13px;font-weight:600;"><?= h($m['nome']) ?></div><div style="font-size:11px;color:var(--text2);">CNH-<?= h($m['cnh_categoria']) ?> · <?= h($m['veiculo_nome']??'—') ?></div></div>
          <span class="pill <?= $m['status']==='disponivel'?'pill-green':'pill-amber' ?>"><?= $m['status'] ?></span>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
  <!-- VEÍCULOS -->
  <div class="card">
    <div class="card-title"><i class="ti ti-car"></i> Veículos</div>
    <div style="background:var(--s2);border-radius:8px;overflow:hidden;">
      <?php foreach($veiculos as $v): ?>
        <div style="display:flex;align-items:center;gap:12px;padding:11px 13px;border-bottom:1px solid var(--border);">
          <span style="font-size:22px;"><?= $v['tipo']==='ambulancia'?'🚑':'🚗' ?></span>
          <div style="flex:1;"><div style="font-size:13px;font-weight:600;"><?= h($v['nome']) ?> — <?= h($v['placa']) ?></div><div style="font-size:11px;color:var(--text2);"><?= h($v['modelo']??'—') ?> · <?= number_format($v['hodometro'],0,'.','.') ?> km</div></div>
          <span class="pill <?= $v['status']==='disponivel'?'pill-green':($v['status']==='em_corrida'?'pill-amber':'pill-gray') ?>"><?= str_replace('_',' ',$v['status']) ?></span>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
  <!-- EMPRESA -->
  <div class="card">
    <div class="card-title"><i class="ti ti-building"></i> Dados da empresa</div>
    <div class="f-group" style="margin-bottom:10px;"><label class="f-label">Nome</label><input class="f-input" value="Hospital da Mulher do Pará — HMPá"/></div>
    <div class="f-group" style="margin-bottom:10px;"><label class="f-label">CNPJ</label><input class="f-input" value="04.913.025/0001-44"/></div>
    <div class="f-group" style="margin-bottom:10px;"><label class="f-label">Endereço</label><input class="f-input" value="Av. Gentil Bittencourt, 2175 — São Brás, Belém/PA"/></div>
    <div class="form-grid" style="margin-bottom:10px;">
      <div class="f-group"><label class="f-label">Custo/km (R$)</label><input class="f-input" value="0,80"/></div>
      <div class="f-group"><label class="f-label">Preço litro</label><input class="f-input" value="R$ 6,89"/></div>
    </div>
    <button class="btn btn-sm"><i class="ti ti-device-floppy"></i> Salvar</button>
  </div>
</div>
<script>
async function addSetor(){
  const nome=document.getElementById('ns-nome').value.trim();
  if(!nome){alert('Informe o nome do setor.');return;}
  const r=await fetch('/api/setores',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({nome,tipo:document.getElementById('ns-tipo').value,info:document.getElementById('ns-info').value})});
  const d=await r.json();
  if(d.ok){location.reload();}
}
async function delSetor(id,btn){
  if(!confirm('Remover este setor?'))return;
  const r=await fetch('/api/setores/'+id,{method:'DELETE'});
  const d=await r.json();
  if(d.ok){btn.closest('div').remove();}
}
</script>
<?php include VIEWS.'/layout/footer.php'; ?>
