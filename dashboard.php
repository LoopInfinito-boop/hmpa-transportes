<?php
// ============================================================
//  app/Models/Models.php — Todos os Models
// ============================================================

// ──────────────────────────────────────────────
//  USUÁRIO
// ──────────────────────────────────────────────
class UsuarioModel
{
    public static function findByMatricula(string $matricula): ?array
    {
        return Database::first(
            'SELECT u.*, p.nome AS perfil_nome
             FROM usuarios u
             JOIN perfis p ON u.perfil_id = p.id
             WHERE u.matricula = ? AND u.ativo = TRUE',
            [$matricula]
        );
    }

    public static function findById(int $id): ?array
    {
        return Database::first(
            'SELECT u.*, p.nome AS perfil_nome
             FROM usuarios u
             JOIN perfis p ON u.perfil_id = p.id
             WHERE u.id = ?',
            [$id]
        );
    }

    public static function updateLastAccess(int $id): void
    {
        Database::execute(
            'UPDATE usuarios SET ultimo_acesso = NOW() WHERE id = ?',
            [$id]
        );
    }
}

// ──────────────────────────────────────────────
//  SETOR
// ──────────────────────────────────────────────
class SetorModel
{
    public static function all(): array
    {
        return Database::query(
            'SELECT * FROM setores WHERE ativo = TRUE ORDER BY tipo, nome'
        );
    }

    public static function create(string $nome, string $tipo, string $info): int
    {
        return Database::insert(
            'INSERT INTO setores (nome, tipo, info) VALUES (?, ?, ?)',
            [$nome, $tipo, $info]
        );
    }

    public static function delete(int $id): void
    {
        Database::execute(
            'UPDATE setores SET ativo = FALSE WHERE id = ?',
            [$id]
        );
    }
}

// ──────────────────────────────────────────────
//  VEÍCULO
// ──────────────────────────────────────────────
class VeiculoModel
{
    public static function all(): array
    {
        return Database::query(
            'SELECT * FROM veiculos WHERE ativo = TRUE ORDER BY nome'
        );
    }

    public static function available(): array
    {
        return Database::query(
            "SELECT * FROM veiculos WHERE status = 'disponivel' AND ativo = TRUE"
        );
    }

    public static function create(array $data): int
    {
        return Database::insert(
            'INSERT INTO veiculos (nome, tipo, placa, modelo, hodometro)
             VALUES (:nome, :tipo, :placa, :modelo, :hodometro)',
            $data
        );
    }

    public static function updateHodometro(int $id, float $km): void
    {
        Database::execute(
            'UPDATE veiculos SET hodometro = ? WHERE id = ?',
            [$km, $id]
        );
    }

    public static function updateStatus(int $id, string $status): void
    {
        Database::execute(
            'UPDATE veiculos SET status = ? WHERE id = ?',
            [$status, $id]
        );
    }
}

// ──────────────────────────────────────────────
//  MOTORISTA
// ──────────────────────────────────────────────
class MotoristaModel
{
    public static function all(): array
    {
        return Database::query(
            'SELECT m.*, u.nome, u.matricula, v.nome AS veiculo_nome
             FROM motoristas m
             JOIN usuarios u ON m.usuario_id = u.id
             LEFT JOIN veiculos v ON m.veiculo_id = v.id
             ORDER BY u.nome'
        );
    }

    public static function findByUsuario(int $userId): ?array
    {
        return Database::first(
            'SELECT m.*, v.nome AS veiculo_nome, v.placa
             FROM motoristas m
             LEFT JOIN veiculos v ON m.veiculo_id = v.id
             WHERE m.usuario_id = ?',
            [$userId]
        );
    }

    public static function setStatus(int $id, string $status): void
    {
        Database::execute(
            'UPDATE motoristas SET status = ? WHERE id = ?',
            [$status, $id]
        );
    }

    public static function create(array $data): int
    {
        return Database::insert(
            'INSERT INTO motoristas (usuario_id, cnh_numero, cnh_categoria, veiculo_id, turno)
             VALUES (:usuario_id, :cnh_numero, :cnh_categoria, :veiculo_id, :turno)',
            $data
        );
    }
}

// ──────────────────────────────────────────────
//  DEMANDA
// ──────────────────────────────────────────────
class DemandaModel
{
    public static function all(array $filters = []): array
    {
        $where = ['1=1'];
        $params = [];

        if (!empty($filters['status'])) {
            $where[] = 'd.status = ?';
            $params[] = $filters['status'];
        }
        if (!empty($filters['tipo'])) {
            $where[] = 'd.tipo = ?';
            $params[] = $filters['tipo'];
        }
        if (!empty($filters['solicitante_id'])) {
            $where[] = 'd.solicitante_id = ?';
            $params[] = $filters['solicitante_id'];
        }
        if (!empty($filters['data_inicio'])) {
            $where[] = 'd.criado_em >= ?';
            $params[] = $filters['data_inicio'];
        }
        if (!empty($filters['data_fim'])) {
            $where[] = 'd.criado_em <= ?';
            $params[] = $filters['data_fim'] . ' 23:59:59';
        }

        $sql = 'SELECT * FROM v_demandas d WHERE ' . implode(' AND ', $where) . ' ORDER BY d.criado_em DESC';
        return Database::query($sql, $params);
    }

    public static function findById(int $id): ?array
    {
        return Database::first('SELECT * FROM v_demandas WHERE id = ?', [$id]);
    }

    public static function findByCodigo(string $codigo): ?array
    {
        return Database::first('SELECT * FROM v_demandas WHERE codigo = ?', [$codigo]);
    }

    public static function fila(): array
    {
        return Database::query(
            "SELECT * FROM v_demandas
             WHERE status IN ('aguardando','andamento')
             ORDER BY
               CASE prioridade
                 WHEN 'emergencia' THEN 1
                 WHEN 'urgente'    THEN 2
                 ELSE 3
               END,
               criado_em ASC"
        );
    }

    public static function create(array $data): int
    {
        return Database::insert(
            'INSERT INTO demandas
                (tipo, prioridade, solicitante_id, solicitante_nome,
                 origem_id, destino_id, observacoes, agendado_para)
             VALUES
                (:tipo, :prioridade, :solicitante_id, :solicitante_nome,
                 :origem_id, :destino_id, :observacoes, :agendado_para)',
            $data
        );
    }

    public static function aceitar(int $id, int $motoristaId, int $veiculoId): void
    {
        Database::execute(
            "UPDATE demandas
             SET status = 'andamento', motorista_id = ?, veiculo_id = ?, aceito_em = NOW(), atualizado_em = NOW()
             WHERE id = ?",
            [$motoristaId, $veiculoId, $id]
        );
    }

    public static function concluir(int $id, float $kmInicio, float $kmFim): void
    {
        Database::execute(
            "UPDATE demandas
             SET status = 'concluida', km_inicio = ?, km_fim = ?,
                 concluido_em = NOW(), atualizado_em = NOW()
             WHERE id = ?",
            [$kmInicio, $kmFim, $id]
        );
    }

    public static function cancelar(int $id): void
    {
        Database::execute(
            "UPDATE demandas
             SET status = 'cancelada', atualizado_em = NOW()
             WHERE id = ?",
            [$id]
        );
    }

    public static function stats(): array
    {
        return Database::first(
            "SELECT
                COUNT(*) FILTER (WHERE status = 'aguardando')  AS aguardando,
                COUNT(*) FILTER (WHERE status = 'andamento')   AS andamento,
                COUNT(*) FILTER (WHERE status = 'concluida')   AS concluida,
                COUNT(*) FILTER (WHERE status = 'cancelada')   AS cancelada,
                COUNT(*) AS total,
                COALESCE(SUM(km_fim - km_inicio) FILTER (WHERE status = 'concluida'), 0) AS total_km
             FROM demandas
             WHERE criado_em >= CURRENT_DATE"
        );
    }
}

// ──────────────────────────────────────────────
//  PACIENTE
// ──────────────────────────────────────────────
class PacienteModel
{
    public static function findByDemanda(int $demandaId): ?array
    {
        return Database::first(
            'SELECT * FROM pacientes WHERE demanda_id = ?',
            [$demandaId]
        );
    }

    public static function create(array $data): int
    {
        return Database::insert(
            'INSERT INTO pacientes
                (demanda_id, nome, cpf, nascimento, prontuario, cid, medico, estado, suporte, acompanhante)
             VALUES
                (:demanda_id, :nome, :cpf, :nascimento, :prontuario, :cid, :medico, :estado, :suporte, :acompanhante)',
            $data
        );
    }
}

// ──────────────────────────────────────────────
//  ITEM DE TRANSPORTE
// ──────────────────────────────────────────────
class ItemTransporteModel
{
    public static function findByDemanda(int $demandaId): array
    {
        return Database::query(
            'SELECT * FROM itens_transporte WHERE demanda_id = ?',
            [$demandaId]
        );
    }

    public static function create(array $data): int
    {
        return Database::insert(
            'INSERT INTO itens_transporte (demanda_id, categoria, descricao, quantidade, setor_entrega)
             VALUES (:demanda_id, :categoria, :descricao, :quantidade, :setor_entrega)',
            $data
        );
    }
}

// ──────────────────────────────────────────────
//  NOTIFICAÇÃO
// ──────────────────────────────────────────────
class NotificacaoModel
{
    public static function forUser(int $userId, bool $naolidas = false): array
    {
        $sql = 'SELECT * FROM notificacoes WHERE usuario_id = ?';
        $params = [$userId];
        if ($naolidas) { $sql .= ' AND lida = FALSE'; }
        $sql .= ' ORDER BY criado_em DESC LIMIT 50';
        return Database::query($sql, $params);
    }

    public static function countUnread(int $userId): int
    {
        $row = Database::first(
            'SELECT COUNT(*) AS total FROM notificacoes WHERE usuario_id = ? AND lida = FALSE',
            [$userId]
        );
        return (int) ($row['total'] ?? 0);
    }

    public static function create(int $userId, string $tipo, string $msg, ?int $demandaId = null): void
    {
        Database::execute(
            'INSERT INTO notificacoes (usuario_id, tipo, mensagem, demanda_id) VALUES (?, ?, ?, ?)',
            [$userId, $tipo, $msg, $demandaId]
        );
    }

    public static function marcarLida(int $id, int $userId): void
    {
        Database::execute(
            'UPDATE notificacoes SET lida = TRUE WHERE id = ? AND usuario_id = ?',
            [$id, $userId]
        );
    }

    public static function marcarTodasLidas(int $userId): void
    {
        Database::execute(
            'UPDATE notificacoes SET lida = TRUE WHERE usuario_id = ?',
            [$userId]
        );
    }

    // Notifica todos os motoristas disponíveis
    public static function notificarMotoristas(string $msg, int $demandaId): void
    {
        $motoristas = Database::query(
            "SELECT u.id FROM usuarios u
             JOIN motoristas m ON m.usuario_id = u.id
             WHERE m.status = 'disponivel' AND u.ativo = TRUE"
        );
        foreach ($motoristas as $m) {
            self::create($m['id'], 'demanda', $msg, $demandaId);
        }
    }
}
