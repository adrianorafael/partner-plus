<?php
/**
 * Auth - Gerenciamento de autenticação e autorização.
 * Centraliza login, logout, verificação de permissões e proteção de sessão.
 */
class Auth
{
    // Tipos de usuário
    const TYPE_ADMIN    = 'admin';
    const TYPE_CLIENT   = 'client';
    const TYPE_PROVIDER = 'provider';

    // Status do usuário
    const STATUS_PENDING_EMAIL = 'pending_email';
    const STATUS_PENDING_ADMIN = 'pending_admin';
    const STATUS_ACTIVE        = 'active';

    // Chave de sessão para modo de visão do admin
    private const VIEW_AS_KEY = '_admin_view_as';

    /**
     * Inicia a sessão com configurações seguras.
     */
    public static function startSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_set_cookie_params([
                'lifetime' => 0,
                'path'     => '/',
                'secure'   => isset($_SERVER['HTTPS']),
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
            session_start();
        }
    }

    /**
     * Autentica o usuário. Retorna array com dados ou null em falha.
     * Inclui proteção contra força bruta via contador na sessão.
     */
    public static function login(string $email, string $password): ?array
    {
        // Proteção força bruta: máximo 5 tentativas em 15 minutos
        $key = 'login_attempts_' . md5($email);
        $attempts = $_SESSION[$key] ?? 0;
        $lastAttempt = $_SESSION[$key . '_time'] ?? 0;

        if ($attempts >= 5 && (time() - $lastAttempt) < 900) {
            return null; // bloqueado
        }

        $stmt = Database::query(
            'SELECT * FROM users WHERE email = ? LIMIT 1',
            [strtolower(trim($email))]
        );
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password_hash'])) {
            $_SESSION[$key] = $attempts + 1;
            $_SESSION[$key . '_time'] = time();
            return null;
        }

        if ($user['status'] !== self::STATUS_ACTIVE) {
            return ['blocked' => true, 'status' => $user['status']];
        }

        // Login bem-sucedido: limpar tentativas e regenerar sessão
        unset($_SESSION[$key], $_SESSION[$key . '_time']);
        session_regenerate_id(true);

        $_SESSION['user_id']   = $user['id'];
        $_SESSION['user_type'] = $user['type'];
        $_SESSION['user_name'] = $user['representative_name'];

        return $user;
    }

    /**
     * Encerra a sessão do usuário.
     */
    public static function logout(): void
    {
        session_unset();
        session_destroy();
        // Apaga o cookie de sessão
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params['path'], $params['domain'],
                $params['secure'], $params['httponly']
            );
        }
    }

    /**
     * Verifica se há usuário logado.
     */
    public static function check(): bool
    {
        return !empty($_SESSION['user_id']);
    }

    /**
     * Retorna o ID do usuário logado ou null.
     */
    public static function id(): ?int
    {
        return isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
    }

    /**
     * Retorna o tipo do usuário logado ou null.
     */
    public static function type(): ?string
    {
        return $_SESSION['user_type'] ?? null;
    }

    /**
     * Retorna os dados completos do usuário logado a partir do banco.
     */
    public static function user(): ?array
    {
        if (!self::check()) return null;
        $stmt = Database::query('SELECT * FROM users WHERE id = ? LIMIT 1', [self::id()]);
        return $stmt->fetch() ?: null;
    }

    /**
     * Exige que o usuário esteja logado; caso contrário redireciona para /login.
     */
    public static function require(): void
    {
        if (!self::check()) {
            header('Location: ' . APP_URL . '/login');
            exit;
        }
    }

    /**
     * Exige um tipo específico de usuário; redireciona se não for.
     * Admin em modo de visão simulada passa pela verificação do tipo simulado.
     */
    public static function requireType(string ...$types): void
    {
        self::require();
        // Admin com view-as ativo pode acessar rotas do tipo simulado
        if (self::isAdmin() && self::viewAs() !== null && in_array(self::viewAs(), $types, true)) {
            return;
        }
        if (!in_array(self::type(), $types, true)) {
            header('Location: ' . APP_URL . '/dashboard');
            exit;
        }
    }

    /**
     * Verifica se o usuário logado é admin.
     */
    public static function isAdmin(): bool
    {
        return self::type() === self::TYPE_ADMIN;
    }

    /**
     * Verifica se o usuário logado é cliente.
     */
    public static function isClient(): bool
    {
        return self::type() === self::TYPE_CLIENT;
    }

    /**
     * Verifica se o usuário logado é fornecedor.
     */
    public static function isProvider(): bool
    {
        return self::type() === self::TYPE_PROVIDER;
    }

    /**
     * Verifica se a sessão está bloqueada por tentativas excessivas.
     */
    public static function isLoginBlocked(string $email): bool
    {
        $key = 'login_attempts_' . md5($email);
        $attempts = $_SESSION[$key] ?? 0;
        $lastAttempt = $_SESSION[$key . '_time'] ?? 0;
        return $attempts >= 5 && (time() - $lastAttempt) < 900;
    }

    /**
     * Redireciona para o dashboard correto conforme o tipo efetivo (considera view-as).
     */
    public static function redirectToDashboard(): void
    {
        $map = [
            self::TYPE_ADMIN    => '/admin/dashboard',
            self::TYPE_CLIENT   => '/client/dashboard',
            self::TYPE_PROVIDER => '/provider/dashboard',
        ];
        $path = $map[self::effectiveType()] ?? '/login';
        header('Location: ' . APP_URL . $path);
        exit;
    }

    // -------------------------------------------------------
    // View-As: Admin simulando perspectiva de outro tipo
    // -------------------------------------------------------

    /**
     * Ativa o modo de visão simulada para o admin.
     * Só aceita 'client' ou 'provider'.
     */
    public static function setViewAs(string $type): void
    {
        if (!self::isAdmin()) return;
        if (!in_array($type, [self::TYPE_CLIENT, self::TYPE_PROVIDER], true)) return;
        $_SESSION[self::VIEW_AS_KEY] = $type;
    }

    /**
     * Desativa o modo de visão simulada.
     */
    public static function clearViewAs(): void
    {
        unset($_SESSION[self::VIEW_AS_KEY]);
    }

    /**
     * Retorna o tipo que está sendo simulado, ou null se não há simulação.
     */
    public static function viewAs(): ?string
    {
        return $_SESSION[self::VIEW_AS_KEY] ?? null;
    }

    /**
     * Retorna o tipo efetivo: tipo simulado (se ativo) ou tipo real do usuário.
     * Use este método nas lógicas de negócio que devem respeitar a visão atual.
     */
    public static function effectiveType(): ?string
    {
        return self::viewAs() ?? self::type();
    }
}
