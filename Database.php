<?php
// ============================================================
//  app/Controllers/Controllers.php
// ============================================================

// ──────────────────────────────────────────────
//  AUTH CONTROLLER
// ──────────────────────────────────────────────
class AuthController
{
    public static function showLogin(): void
    {
        if (Auth::check()) redirect('/dashboard');
        include VIEWS . '/login.php';
    }

    public static function postLogin(): void
    {
        $matricula = sanitize($_POST['matricula'] ?? '');
        $senha     = $_POST['senha'] ?? '';

        if (!$matricula || !$senha) {
            $_SESSION['flash_error'] = 'Preencha matrícula e senha.';
            redirect('/login');
        }

        $usuario = UsuarioModel::findByMatricula($matricula);

        if (!$usuario || !password_verify($senha, $usuario['senha_hash'])) {
            $_SESSION['flash_error'] = 'Matrícula ou senha incorretos.';
            redirect('/login');
        }

        Auth::login($usuario);
        redirect('/dashboard');
    }

    public static function logout(): void
    {
        Auth::logout();
        redirect('/login');
    }
}

// ──────────────────────────────────────────────
//  DASHBOARD CONTROLLER
// ──────────────────────────────────────────────
class DashboardController
{
    public static function index(): void
    {
        Auth::require();
        $stats    = DemandaModel::stats();
        $recentes = DemandaModel::all(['data_inicio' => date('Y-m-d')]);
        $setores  = SetorModel::all();
        include VIEWS . '/dashboard.php';
    }
}

// ──────────────────────────────────────────────
//  DEMANDA CONTROLLER
// ──────────────────────────────────────────────
class DemandaController
{
    public static function nova(): void
    {
        Auth::requireRole(['solicitante', 'gestor', 'admin']);
        $setores = SetorModel::all();
        include VIEWS . '/demanda/nova.php';
    }

    public static function store(): void
    {
        Auth::requireRole(['solicitante', 'gestor', 'admin']);

        $tipo      = sanitize($_POST['tipo']      ?? '');
        $prioridade= sanitize($_POST['prioridade'] ?? 'normal');
        $origemId  = (int)($_POST['origem_id']   ?? 0);
        $destinoId = (int)($_POST['destino_id']  ?? 0);
        $solNome   = sanitize($_POST['solicitante_nome'] ?? Auth::nome());
        $obs       = sanitize($_POST['observacoes'] ?? '');
        $agendado  = sanitize($_POST['agendado_para'] ?? '');

        if (!$tipo || !$origemId || !$destinoId) {
            $_SESSION['flash_error'] = 'Preencha todos os campos obrigatórios.';
            redirect('/demandas/nova');
        }
        if ($origemId === $destinoId) {
            $_SESSION['flash_error'] = 'Origem e destino não podem ser iguais.';
            redirect('/demandas/nova');
        }

        $demandaId = DemandaModel::create([
            'tipo'            => $tipo,
            'prioridade'      => $prioridade,
            'solicitante_id'  => Auth::id(),
            'solicitante_nome'=> $solNome,
            'origem_id'       => $origemId,
            'destino_id'      => $destinoId,
            'observacoes'     => $obs,
            'agendado_para'   => $agendado ?: null,
        ]);

        // Dados específicos
        if ($tipo === 'ambulancia') {
            PacienteModel::create([
                'demanda_id'  => $demandaId,
                'nome'        => sanitize($_POST['p_nome']         ?? ''),
                'cpf'         => sanitize($_POST['p_cpf']          ?? ''),
                'nascimento'  => sanitize($_POST['p_nascimento']   ?? '') ?: null,
                'prontuario'  => sanitize($_POST['p_prontuario']   ?? ''),
                'cid'         => sanitize($_POST['p_cid']          ?? ''),
                'medico'      => sanitize($_POST['p_medico']       ?? ''),
                'estado'      => sanitize($_POST['p_estado']       ?? 'estavel'),
                'suporte'     => sanitize($_POST['p_suporte']      ?? 'nenhum'),
                'acompanhante'=> sanitize($_POST['p_acompanhante'] ?? 'nao'),
            ]);
        } else {
            // Itens de transporte (array)
            $nomes   = $_POST['item_nome']    ?? [];
            $qtdes   = $_POST['item_qtde']    ?? [];
            $setores = $_POST['item_setor']   ?? [];
            $cat     = sanitize($_POST['categoria_apoio'] ?? '');
            $desc    = sanitize($_POST['descricao_apoio'] ?? '');

            // Item principal
            ItemTransporteModel::create([
                'demanda_id'    => $demandaId,
                'categoria'     => $cat,
                'descricao'     => $desc,
                'quantidade'    => '',
                'setor_entrega' => '',
            ]);

            // Itens adicionais
            foreach ($nomes as $i => $nome) {
                if (empty(trim($nome))) continue;
                ItemTransporteModel::create([
                    'demanda_id'    => $demandaId,
                    'categoria'     => $cat,
                    'descricao'     => sanitize($nome),
                    'quantidade'    => sanitize($qtdes[$i] ?? ''),
                    'setor_entrega' => sanitize($setores[$i] ?? ''),
                ]);
            }
        }

        // Notifica motoristas disponíveis
        $demanda = DemandaModel::findById($demandaId);
        $msg = "Nova demanda {$demanda['prioridade']} — {$demanda['origem']} → {$demanda['destino']}";
        NotificacaoModel::notificarMotoristas($msg, $demandaId);

        $_SESSION['flash_success'] = "Demanda #{$demanda['codigo']} enviada com sucesso!";
        redirect('/demandas');
    }

    public static function index(): void
    {
        Auth::require();
        $filters = [];
        if (Auth::isSolicitante()) {
            $filters['solicitante_id'] = Auth::id();
        }
        if (!empty($_GET['status'])) $filters['status'] = $_GET['status'];
        $demandas = DemandaModel::all($filters);
        $titulo   = Auth::isSolicitante() ? 'Minhas solicitações' : 'Todas as demandas';
        include VIEWS . '/demanda/lista.php';
    }

    public static function show(int $id): void
    {
        Auth::require();
        $demanda  = DemandaModel::findById($id);
        if (!$demanda) { http_response_code(404); die('Demanda não encontrada.'); }
        $paciente = PacienteModel::findByDemanda($id);
        $itens    = ItemTransporteModel::findByDemanda($id);
        include VIEWS . '/demanda/detalhe.php';
    }
}

// ──────────────────────────────────────────────
//  MOTORISTA CONTROLLER
// ──────────────────────────────────────────────
class MotoristaController
{
    public static function painel(): void
    {
        Auth::requireRole(['motorista', 'gestor', 'admin']);
        $motorista    = MotoristaModel::findByUsuario(Auth::id());
        $fila         = DemandaModel::fila();
        $corridaAtiva = null;

        if ($motorista) {
            $corridaAtiva = Database::first(
                "SELECT * FROM v_demandas WHERE status = 'andamento' AND motorista_id = (SELECT id FROM motoristas WHERE usuario_id = ?)",
                [Auth::id()]
            );
            if ($corridaAtiva) {
                $corridaAtiva['paciente'] = PacienteModel::findByDemanda($corridaAtiva['id']);
                $corridaAtiva['itens']    = ItemTransporteModel::findByDemanda($corridaAtiva['id']);
            }
        }

        include VIEWS . '/motorista/painel.php';
    }

    public static function aceitar(): void
    {
        Auth::requireRole(['motorista']);
        $data      = request_json();
        $demandaId = (int)($data['demanda_id'] ?? 0);

        $motorista = MotoristaModel::findByUsuario(Auth::id());
        if (!$motorista) {
            json_response(['ok' => false, 'msg' => 'Motorista não encontrado.'], 400);
        }

        // Verifica se já tem corrida ativa
        $ativa = Database::first(
            "SELECT id FROM demandas WHERE status='andamento' AND motorista_id=?",
            [$motorista['id']]
        );
        if ($ativa) {
            json_response(['ok' => false, 'msg' => 'Você já tem uma corrida ativa.'], 400);
        }

        DemandaModel::aceitar($demandaId, $motorista['id'], $motorista['veiculo_id']);
        VeiculoModel::updateStatus($motorista['veiculo_id'], 'em_corrida');
        MotoristaModel::setStatus($motorista['id'], 'em_corrida');

        json_response(['ok' => true, 'msg' => 'Corrida aceita!']);
    }

    public static function concluir(): void
    {
        Auth::requireRole(['motorista']);
        $data      = request_json();
        $demandaId = (int)($data['demanda_id'] ?? 0);
        $kmInicio  = (float)($data['km_inicio'] ?? 0);
        $kmFim     = (float)($data['km_fim']    ?? 0);

        if (!$kmInicio || !$kmFim) {
            json_response(['ok' => false, 'msg' => 'Informe os quilômetros inicial e final.'], 400);
        }
        if ($kmFim <= $kmInicio) {
            json_response(['ok' => false, 'msg' => 'KM final deve ser maior que KM inicial.'], 400);
        }

        $motorista = MotoristaModel::findByUsuario(Auth::id());
        DemandaModel::concluir($demandaId, $kmInicio, $kmFim);
        VeiculoModel::updateHodometro($motorista['veiculo_id'], $kmFim);
        VeiculoModel::updateStatus($motorista['veiculo_id'], 'disponivel');
        MotoristaModel::setStatus($motorista['id'], 'disponivel');

        $demanda = DemandaModel::findById($demandaId);
        $kmRod   = $kmFim - $kmInicio;
        NotificacaoModel::create($demanda['solicitante_id'] ?? 0, 'concluida',
            "Transporte {$demanda['codigo']} concluído — {$kmRod} km rodados", $demandaId);

        json_response(['ok' => true, 'km_rodados' => $kmRod]);
    }

    public static function cancelar(): void
    {
        Auth::requireRole(['motorista', 'gestor', 'admin']);
        $data      = request_json();
        $demandaId = (int)($data['demanda_id'] ?? 0);

        $motorista = MotoristaModel::findByUsuario(Auth::id());
        if ($motorista) {
            VeiculoModel::updateStatus($motorista['veiculo_id'], 'disponivel');
            MotoristaModel::setStatus($motorista['id'], 'disponivel');
            Database::execute(
                "UPDATE demandas SET status='aguardando', motorista_id=NULL, veiculo_id=NULL WHERE id=?",
                [$demandaId]
            );
        }
        json_response(['ok' => true]);
    }
}

// ──────────────────────────────────────────────
//  ADMIN / GESTOR CONTROLLER
// ──────────────────────────────────────────────
class AdminController
{
    public static function cadastros(): void
    {
        Auth::requireRole(['gestor', 'admin']);
        $setores    = SetorModel::all();
        $motoristas = MotoristaModel::all();
        $veiculos   = VeiculoModel::all();
        include VIEWS . '/admin/cadastros.php';
    }

    public static function historico(): void
    {
        Auth::require();
        $filters = [];
        if (!empty($_GET['data_inicio'])) $filters['data_inicio'] = $_GET['data_inicio'];
        if (!empty($_GET['data_fim']))    $filters['data_fim']    = $_GET['data_fim'];
        $demandas = DemandaModel::all(array_merge($filters, ['status' => 'concluida']));
        $canceladas = DemandaModel::all(array_merge($filters, ['status' => 'cancelada']));
        $demandas = array_merge($demandas, $canceladas);
        include VIEWS . '/admin/historico.php';
    }

    public static function relatorio(): void
    {
        Auth::requireRole(['gestor', 'admin']);
        $stats    = DemandaModel::stats();
        $veiculos = VeiculoModel::all();
        $relVeiculos = Database::query(
            "SELECT v.nome, v.placa,
                COUNT(d.id)                 AS corridas,
                COALESCE(SUM(d.km_fim - d.km_inicio), 0) AS km_total,
                COALESCE(SUM((d.km_fim - d.km_inicio) * 0.8), 0) AS custo_est
             FROM veiculos v
             LEFT JOIN demandas d ON d.veiculo_id = v.id AND d.status = 'concluida'
             GROUP BY v.id, v.nome, v.placa
             ORDER BY km_total DESC"
        );
        include VIEWS . '/admin/relatorio.php';
    }

    // API — setores
    public static function storeSetor(): void
    {
        Auth::requireRole(['gestor', 'admin']);
        $data = request_json();
        $id   = SetorModel::create(
            sanitize($data['nome'] ?? ''),
            sanitize($data['tipo'] ?? 'interno'),
            sanitize($data['info'] ?? '')
        );
        json_response(['ok' => true, 'id' => $id]);
    }

    public static function deleteSetor(int $id): void
    {
        Auth::requireRole(['gestor', 'admin']);
        SetorModel::delete($id);
        json_response(['ok' => true]);
    }

    // API — notificações
    public static function notificacoes(): void
    {
        Auth::require();
        $notifs = NotificacaoModel::forUser(Auth::id());
        $count  = NotificacaoModel::countUnread(Auth::id());
        json_response(['notificacoes' => $notifs, 'nao_lidas' => $count]);
    }

    public static function marcarLida(int $id): void
    {
        Auth::require();
        NotificacaoModel::marcarLida($id, Auth::id());
        json_response(['ok' => true]);
    }

    public static function marcarTodasLidas(): void
    {
        Auth::require();
        NotificacaoModel::marcarTodasLidas(Auth::id());
        json_response(['ok' => true]);
    }
}
