<?php
// ============================================================
//  app/Middleware/Auth.php
// ============================================================

class Auth
{
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public static function login(array $usuario): void
    {
        self::start();
        $_SESSION['user_id']    = $usuario['id'];
        $_SESSION['user_nome']  = $usuario['nome'];
        $_SESSION['user_mat']   = $usuario['matricula'];
        $_SESSION['user_perfil']= $usuario['perfil_nome'];
        $_SESSION['login_at']   = time();
        UsuarioModel::updateLastAccess($usuario['id']);
    }

    public static function logout(): void
    {
        self::start();
        session_destroy();
    }

    public static function check(): bool
    {
        self::start();
        if (empty($_SESSION['user_id'])) return false;
        // Verifica TTL da sessão
        if (time() - ($_SESSION['login_at'] ?? 0) > SESSION_TTL) {
            self::logout();
            return false;
        }
        return true;
    }

    public static function require(): void
    {
        if (!self::check()) {
            header('Location: /login');
            exit;
        }
    }

    public static function requireRole(array $roles): void
    {
        self::require();
        if (!in_array(self::perfil(), $roles)) {
            http_response_code(403);
            die('Acesso negado.');
        }
    }

    public static function id(): int    { return (int) ($_SESSION['user_id']    ?? 0); }
    public static function nome(): string { return $_SESSION['user_nome'] ?? ''; }
    public static function matricula(): string { return $_SESSION['user_mat'] ?? ''; }
    public static function perfil(): string { return $_SESSION['user_perfil'] ?? ''; }

    public static function isSolicitante(): bool { return self::perfil() === 'solicitante'; }
    public static function isMotorista(): bool   { return self::perfil() === 'motorista'; }
    public static function isGestor(): bool      { return in_array(self::perfil(), ['gestor','admin']); }
    public static function isAdmin(): bool       { return self::perfil() === 'admin'; }

    public static function user(): ?array
    {
        if (!self::check()) return null;
        return UsuarioModel::findById(self::id());
    }

    public static function initials(): string
    {
        $parts = explode(' ', self::nome());
        return strtoupper(substr($parts[0], 0, 1) . (isset($parts[1]) ? substr($parts[1], 0, 1) : ''));
    }
}

// ──────────────────────────────────────────────
//  HELPERS GLOBAIS
// ──────────────────────────────────────────────

function h(string $str): string
{
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

function redirect(string $path): void
{
    header("Location: $path");
    exit;
}

function json_response(mixed $data, int $code = 200): void
{
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function request_json(): array
{
    $body = file_get_contents('php://input');
    return json_decode($body, true) ?? [];
}

function sanitize(string $value): string
{
    return trim(strip_tags($value));
}

function formatarData(string $dt): string
{
    return date('d/m/Y H:i', strtotime($dt));
}

function pillStatus(string $status): string
{
    $map = [
        'aguardando' => ['pill-amber', 'Aguardando'],
        'andamento'  => ['pill-blue',  'Em andamento'],
        'concluida'  => ['pill-green', 'Concluída'],
        'cancelada'  => ['pill-red',   'Cancelada'],
    ];
    [$cls, $label] = $map[$status] ?? ['pill-gray', $status];
    return "<span class=\"pill $cls\">$label</span>";
}

function pillPrioridade(string $p): string
{
    $map = [
        'normal'     => ['pill-blue',  'Normal'],
        'urgente'    => ['pill-amber', 'Urgente'],
        'emergencia' => ['pill-red',   'Emergência'],
    ];
    [$cls, $label] = $map[$p] ?? ['pill-gray', $p];
    return "<span class=\"pill $cls\">" . strtoupper($label) . "</span>";
}
