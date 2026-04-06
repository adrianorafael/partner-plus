<?php
/**
 * Partner Plus - Entry Point
 * Carrega a configuração, inicializa a sessão e despacha as rotas.
 */
declare(strict_types=1);

// Autoload simples: carrega todas as classes de /src
spl_autoload_register(function (string $class): void {
    $file = dirname(__DIR__) . '/src/' . $class . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

// Carregar configuração (gerada pelo wizard de instalação)
$configFile = dirname(__DIR__) . '/config/config.php';
if (!file_exists($configFile)) {
    // Redirecionar para o wizard se não configurado
    $installUrl = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']
        . str_replace('/public/index.php', '', $_SERVER['SCRIPT_NAME'])
        . '/install/';
    header('Location: ' . $installUrl);
    exit;
}
require_once $configFile;

// Iniciar sessão segura
Auth::startSession();

// Definir base path para o router (remove /public do URI)
$scriptDir   = dirname($_SERVER['SCRIPT_NAME']); // ex: /partner-plus/public
$router = new Router($scriptDir);

// -------------------------------------------------------
// ROTAS PÚBLICAS
// -------------------------------------------------------

// Redirecionar raiz para login ou dashboard
$router->get('/', function () {
    if (Auth::check()) {
        Auth::redirectToDashboard();
    }
    Helpers::redirect('/login');
});

$router->get('/login', function () {
    if (Auth::check()) Auth::redirectToDashboard();
    include dirname(__DIR__) . '/templates/auth/login.php';
});

$router->post('/login', function () {
    CSRF::check();
    $email    = Helpers::sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $result = Auth::login($email, $password);

    if ($result === null) {
        if (Auth::isLoginBlocked($email)) {
            $error = 'Muitas tentativas de login. Aguarde 15 minutos.';
        } else {
            $error = 'E-mail ou senha incorretos.';
        }
        include dirname(__DIR__) . '/templates/auth/login.php';
        return;
    }

    if (isset($result['blocked'])) {
        $blocked = $result['status'];
        include dirname(__DIR__) . '/templates/auth/login.php';
        return;
    }

    Auth::redirectToDashboard();
});

$router->get('/logout', function () {
    Auth::logout();
    Helpers::redirect('/login');
});

$router->get('/register', function () {
    if (Auth::check()) Auth::redirectToDashboard();
    include dirname(__DIR__) . '/templates/auth/register.php';
});

$router->post('/register', function () {
    CSRF::check();

    $errors = [];
    $type              = Helpers::sanitize($_POST['type'] ?? '');
    $cnpj              = Helpers::cleanCNPJ($_POST['cnpj'] ?? '');
    $companyName       = Helpers::sanitize($_POST['company_name'] ?? '');
    $representativeName= Helpers::sanitize($_POST['representative_name'] ?? '');
    $role              = Helpers::sanitize($_POST['role'] ?? '');
    $email             = strtolower(Helpers::sanitize($_POST['email'] ?? ''));
    $phone             = preg_replace('/\D/', '', $_POST['phone'] ?? '');
    $password          = $_POST['password'] ?? '';
    $passwordConfirm   = $_POST['password_confirm'] ?? '';

    // Validações
    if (!in_array($type, ['client', 'provider'])) {
        $errors[] = 'Selecione o tipo de conta.';
    }
    if (!Helpers::validateCNPJ($cnpj)) {
        $errors[] = 'CNPJ inválido.';
    }
    if (empty($companyName)) {
        $errors[] = 'Nome da empresa é obrigatório.';
    }
    if (empty($representativeName)) {
        $errors[] = 'Seu nome é obrigatório.';
    }
    if (empty($role)) {
        $errors[] = 'Cargo é obrigatório.';
    }
    $emailError = Helpers::validateCorporateEmail($email);
    if ($emailError) {
        $errors[] = $emailError;
    }
    if (strlen($phone) < 10) {
        $errors[] = 'Telefone inválido.';
    }
    if (strlen($password) < 8) {
        $errors[] = 'A senha deve ter no mínimo 8 caracteres.';
    }
    if ($password !== $passwordConfirm) {
        $errors[] = 'As senhas não coincidem.';
    }

    if (!empty($errors)) {
        include dirname(__DIR__) . '/templates/auth/register.php';
        return;
    }

    // Verificar duplicidade de e-mail
    $existing = Database::query('SELECT id FROM users WHERE email = ? LIMIT 1', [$email])->fetch();
    if ($existing) {
        $errors[] = 'Este e-mail já está cadastrado.';
        include dirname(__DIR__) . '/templates/auth/register.php';
        return;
    }

    // Criar usuário
    $hash  = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    $token = Helpers::generateToken();

    Database::query(
        'INSERT INTO users (cnpj, company_name, representative_name, role, email, phone, password_hash, type, status)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)',
        [$cnpj, $companyName, $representativeName, $role, $email, $phone, $hash, $type, Auth::STATUS_PENDING_EMAIL]
    );
    $userId = (int)Database::lastInsertId();

    // Token de verificação de e-mail (expira em 24h)
    Database::query(
        'INSERT INTO email_verifications (user_id, token, expires_at) VALUES (?, ?, ?)',
        [$userId, $token, date('Y-m-d H:i:s', strtotime('+24 hours'))]
    );

    // Enviar e-mail de verificação
    Mailer::sendEmailVerification($email, $representativeName, $token);

    Helpers::redirect('/register/pending?email=' . urlencode($email));
});

$router->get('/register/pending', function () {
    $email = Helpers::sanitize($_GET['email'] ?? '');
    include dirname(__DIR__) . '/templates/auth/pending.php';
});

$router->get('/verify-email', function () {
    $token  = Helpers::sanitize($_GET['token'] ?? '');
    $verified = false;

    if ($token) {
        $row = Database::query(
            'SELECT ev.*, u.representative_name, u.email, u.company_name
             FROM email_verifications ev
             JOIN users u ON u.id = ev.user_id
             WHERE ev.token = ? AND ev.expires_at > NOW()
             LIMIT 1',
            [$token]
        )->fetch();

        if ($row) {
            // Atualizar status do usuário
            Database::query(
                "UPDATE users SET status = ? WHERE id = ? AND status = ?",
                [Auth::STATUS_PENDING_ADMIN, $row['user_id'], Auth::STATUS_PENDING_EMAIL]
            );
            // Remover token usado
            Database::query('DELETE FROM email_verifications WHERE id = ?', [$row['id']]);

            $verified = true;

            // Notificar admin
            $admin = Database::query(
                "SELECT email FROM users WHERE type = 'admin' LIMIT 1"
            )->fetch();
            if ($admin) {
                Mailer::sendNewRegistrationAlert($admin['email'], $row['representative_name'], $row['company_name']);
            }
        }
    }

    include dirname(__DIR__) . '/templates/auth/verify_email.php';
});

$router->get('/forgot-password', function () {
    include dirname(__DIR__) . '/templates/auth/forgot_password.php';
});

$router->post('/forgot-password', function () {
    CSRF::check();
    $email = strtolower(Helpers::sanitize($_POST['email'] ?? ''));
    $sent  = true; // Sempre mostrar sucesso (evita enumeração de e-mails)

    $user = Database::query(
        "SELECT id, representative_name FROM users WHERE email = ? AND status = 'active' LIMIT 1",
        [$email]
    )->fetch();

    if ($user) {
        // Invalidar tokens anteriores
        Database::query('DELETE FROM password_resets WHERE user_id = ?', [$user['id']]);

        $token = Helpers::generateToken();
        Database::query(
            'INSERT INTO password_resets (user_id, token, expires_at) VALUES (?, ?, ?)',
            [$user['id'], $token, date('Y-m-d H:i:s', strtotime('+1 hour'))]
        );
        Mailer::sendPasswordReset($email, $user['representative_name'], $token);
    }

    include dirname(__DIR__) . '/templates/auth/forgot_password.php';
});

$router->get('/reset-password', function () {
    $token   = Helpers::sanitize($_GET['token'] ?? '');
    $invalid = false;

    if (!$token) {
        $invalid = true;
    } else {
        $row = Database::query(
            "SELECT id FROM password_resets WHERE token = ? AND used = 0 AND expires_at > NOW() LIMIT 1",
            [$token]
        )->fetch();
        if (!$row) $invalid = true;
    }

    include dirname(__DIR__) . '/templates/auth/reset_password.php';
});

$router->post('/reset-password', function () {
    CSRF::check();
    $token           = Helpers::sanitize($_POST['token'] ?? '');
    $password        = $_POST['password'] ?? '';
    $passwordConfirm = $_POST['password_confirm'] ?? '';
    $errors          = [];
    $invalid         = false;

    $row = Database::query(
        "SELECT * FROM password_resets WHERE token = ? AND used = 0 AND expires_at > NOW() LIMIT 1",
        [$token]
    )->fetch();

    if (!$row) {
        $invalid = true;
        include dirname(__DIR__) . '/templates/auth/reset_password.php';
        return;
    }

    if (strlen($password) < 8) {
        $errors[] = 'A senha deve ter no mínimo 8 caracteres.';
    }
    if ($password !== $passwordConfirm) {
        $errors[] = 'As senhas não coincidem.';
    }

    if (!empty($errors)) {
        include dirname(__DIR__) . '/templates/auth/reset_password.php';
        return;
    }

    $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    Database::query('UPDATE users SET password_hash = ? WHERE id = ?', [$hash, $row['user_id']]);
    Database::query('UPDATE password_resets SET used = 1 WHERE id = ?', [$row['id']]);

    Helpers::flash('success', 'Senha redefinida com sucesso! Faça login com a nova senha.');
    Helpers::redirect('/login');
});

// -------------------------------------------------------
// ROTA GENÉRICA: /dashboard → redireciona conforme tipo efetivo
// -------------------------------------------------------
$router->get('/dashboard', function () {
    Auth::require();
    Auth::redirectToDashboard();
});

// -------------------------------------------------------
// ROTAS DE VIEW-AS (admin simulando perspectiva de outro tipo)
// -------------------------------------------------------

// Ativar modo de visão simulada
$router->post('/admin/view-as', function () {
    Auth::requireType(Auth::TYPE_ADMIN);
    CSRF::check();
    $type = Helpers::sanitize($_POST['type'] ?? '');
    Auth::setViewAs($type);
    Auth::redirectToDashboard();
});

// Desativar modo de visão simulada
$router->get('/admin/view-as/reset', function () {
    Auth::require();
    Auth::clearViewAs();
    Helpers::redirect('/admin/dashboard');
});

// -------------------------------------------------------
// ROTAS DO CLIENTE
// -------------------------------------------------------
$router->get('/client/dashboard', function () {
    Auth::requireType(Auth::TYPE_CLIENT);
    $user  = Auth::user();
    $stats = [
        'active' => Database::query(
            "SELECT COUNT(*) FROM opportunities WHERE client_id = ? AND status = 'active'", [Auth::id()]
        )->fetchColumn(),
        'closed' => Database::query(
            "SELECT COUNT(*) FROM opportunities WHERE client_id = ? AND status = 'closed'", [Auth::id()]
        )->fetchColumn(),
        'total'  => Database::query(
            "SELECT COUNT(*) FROM opportunities WHERE client_id = ?", [Auth::id()]
        )->fetchColumn(),
    ];
    $opportunities = Database::query(
        "SELECT * FROM opportunities WHERE client_id = ? ORDER BY created_at DESC LIMIT 5",
        [Auth::id()]
    )->fetchAll();

    include dirname(__DIR__) . '/templates/client/dashboard.php';
});

$router->get('/client/opportunities', function () {
    Auth::requireType(Auth::TYPE_CLIENT);
    $status = Helpers::sanitize($_GET['status'] ?? '');
    $type   = Helpers::sanitize($_GET['type'] ?? '');

    $sql    = "SELECT * FROM opportunities WHERE client_id = ?";
    $params = [Auth::id()];

    if ($status && in_array($status, ['active', 'closed'])) {
        $sql .= " AND status = ?";
        $params[] = $status;
    }
    if ($type && in_array($type, ['software', 'service'])) {
        $sql .= " AND type = ?";
        $params[] = $type;
    }
    $sql .= " ORDER BY created_at DESC";

    $opportunities = Database::query($sql, $params)->fetchAll();
    include dirname(__DIR__) . '/templates/client/opportunities/index.php';
});

$router->get('/client/opportunities/create', function () {
    Auth::requireType(Auth::TYPE_CLIENT);
    include dirname(__DIR__) . '/templates/client/opportunities/form.php';
});

$router->post('/client/opportunities/create', function () {
    Auth::requireType(Auth::TYPE_CLIENT);
    CSRF::check();

    $errors = [];
    $title            = Helpers::sanitize($_POST['title'] ?? '');
    $type             = Helpers::sanitize($_POST['type'] ?? '');
    $description      = Helpers::sanitize($_POST['description'] ?? '');
    $startDate        = Helpers::sanitize($_POST['start_date'] ?? '');
    $endDate          = Helpers::sanitize($_POST['end_date'] ?? '');
    $targeting        = Helpers::sanitize($_POST['targeting'] ?? 'open');
    $targetProvider   = $targeting === 'specific' ? Helpers::sanitize($_POST['target_provider'] ?? '') : null;
    $contactType      = Helpers::sanitize($_POST['contact_person_type'] ?? 'self');
    $contactName      = $contactType === 'other' ? Helpers::sanitize($_POST['contact_name'] ?? '') : null;
    $contactRole      = $contactType === 'other' ? Helpers::sanitize($_POST['contact_role'] ?? '') : null;
    $contactEmail     = $contactType === 'other' ? strtolower(Helpers::sanitize($_POST['contact_email'] ?? '')) : null;
    $contactPhone     = $contactType === 'other' ? preg_replace('/\D/', '', $_POST['contact_phone'] ?? '') : null;

    if (empty($title)) $errors[] = 'Nome da oportunidade é obrigatório.';
    if (!in_array($type, ['software', 'service'])) $errors[] = 'Selecione o tipo.';
    if (empty($description)) $errors[] = 'Descrição é obrigatória.';
    if (!Helpers::validateDate($startDate)) $errors[] = 'Data inicial inválida.';
    if (!Helpers::validateDate($endDate)) $errors[] = 'Data final inválida.';
    if ($startDate && $endDate && $endDate < $startDate) $errors[] = 'A data final deve ser posterior à inicial.';
    if ($targeting === 'specific' && empty($targetProvider)) $errors[] = 'Informe o nome do fornecedor específico.';
    if ($contactType === 'other') {
        if (empty($contactName)) $errors[] = 'Nome do contato é obrigatório.';
        if (empty($contactEmail) || !filter_var($contactEmail, FILTER_VALIDATE_EMAIL)) $errors[] = 'E-mail do contato inválido.';
    }

    if (!empty($errors)) {
        include dirname(__DIR__) . '/templates/client/opportunities/form.php';
        return;
    }

    Database::query(
        "INSERT INTO opportunities
            (client_id, type, title, description, start_date, end_date, target_provider,
             contact_person_type, contact_name, contact_role, contact_email, contact_phone, status)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active')",
        [Auth::id(), $type, $title, $description, $startDate, $endDate, $targetProvider,
         $contactType, $contactName, $contactRole, $contactEmail, $contactPhone]
    );

    Helpers::flash('success', 'Oportunidade publicada com sucesso!');
    Helpers::redirect('/client/opportunities');
});

$router->get('/client/opportunities/{id}/edit', function (string $id) {
    Auth::requireType(Auth::TYPE_CLIENT);
    $opportunity = Database::query(
        "SELECT * FROM opportunities WHERE id = ? AND client_id = ? LIMIT 1",
        [(int)$id, Auth::id()]
    )->fetch();
    if (!$opportunity) {
        http_response_code(404);
        include dirname(__DIR__) . '/templates/errors/404.php';
        return;
    }
    include dirname(__DIR__) . '/templates/client/opportunities/form.php';
});

$router->post('/client/opportunities/{id}/edit', function (string $id) {
    Auth::requireType(Auth::TYPE_CLIENT);
    CSRF::check();

    $opportunity = Database::query(
        "SELECT * FROM opportunities WHERE id = ? AND client_id = ? LIMIT 1",
        [(int)$id, Auth::id()]
    )->fetch();
    if (!$opportunity) {
        Helpers::redirect('/client/opportunities');
        return;
    }

    $errors = [];
    $title            = Helpers::sanitize($_POST['title'] ?? '');
    $type             = Helpers::sanitize($_POST['type'] ?? '');
    $description      = Helpers::sanitize($_POST['description'] ?? '');
    $startDate        = Helpers::sanitize($_POST['start_date'] ?? '');
    $endDate          = Helpers::sanitize($_POST['end_date'] ?? '');
    $targeting        = Helpers::sanitize($_POST['targeting'] ?? 'open');
    $targetProvider   = $targeting === 'specific' ? Helpers::sanitize($_POST['target_provider'] ?? '') : null;
    $contactType      = Helpers::sanitize($_POST['contact_person_type'] ?? 'self');
    $contactName      = $contactType === 'other' ? Helpers::sanitize($_POST['contact_name'] ?? '') : null;
    $contactRole      = $contactType === 'other' ? Helpers::sanitize($_POST['contact_role'] ?? '') : null;
    $contactEmail     = $contactType === 'other' ? strtolower(Helpers::sanitize($_POST['contact_email'] ?? '')) : null;
    $contactPhone     = $contactType === 'other' ? preg_replace('/\D/', '', $_POST['contact_phone'] ?? '') : null;

    if (empty($title)) $errors[] = 'Nome da oportunidade é obrigatório.';
    if (!in_array($type, ['software', 'service'])) $errors[] = 'Tipo inválido.';
    if (empty($description)) $errors[] = 'Descrição é obrigatória.';
    if (!Helpers::validateDate($startDate)) $errors[] = 'Data inicial inválida.';
    if (!Helpers::validateDate($endDate) || $endDate < $startDate) $errors[] = 'Data final inválida.';

    if (!empty($errors)) {
        include dirname(__DIR__) . '/templates/client/opportunities/form.php';
        return;
    }

    Database::query(
        "UPDATE opportunities SET type=?, title=?, description=?, start_date=?, end_date=?,
         target_provider=?, contact_person_type=?, contact_name=?, contact_role=?,
         contact_email=?, contact_phone=?
         WHERE id = ? AND client_id = ?",
        [$type, $title, $description, $startDate, $endDate, $targetProvider,
         $contactType, $contactName, $contactRole, $contactEmail, $contactPhone,
         (int)$id, Auth::id()]
    );

    Helpers::flash('success', 'Oportunidade atualizada com sucesso!');
    Helpers::redirect('/client/opportunities');
});

$router->post('/client/opportunities/{id}/close', function (string $id) {
    Auth::requireType(Auth::TYPE_CLIENT);
    CSRF::check();
    Database::query(
        "UPDATE opportunities SET status = 'closed' WHERE id = ? AND client_id = ?",
        [(int)$id, Auth::id()]
    );
    Helpers::flash('success', 'Oportunidade encerrada.');
    Helpers::redirect('/client/opportunities');
});

// -------------------------------------------------------
// ROTAS DO FORNECEDOR
// -------------------------------------------------------
$router->get('/provider/dashboard', function () {
    Auth::requireType(Auth::TYPE_PROVIDER);
    $user = Auth::user();

    // Oportunidades disponíveis: ativas, dentro do prazo, não direcionadas a outro
    $total = Database::query(
        "SELECT COUNT(*) FROM opportunities
         WHERE status = 'active' AND end_date >= CURDATE()
         AND (target_provider IS NULL OR target_provider = '')",
        []
    )->fetchColumn();

    $viewed = Database::query(
        "SELECT COUNT(*) FROM opportunity_leads WHERE provider_id = ?",
        [Auth::id()]
    )->fetchColumn();

    $stats = ['total' => $total, 'viewed' => $viewed, 'new' => $total - $viewed];

    // 5 mais recentes disponíveis
    $viewedIds = Database::query(
        "SELECT opportunity_id FROM opportunity_leads WHERE provider_id = ?",
        [Auth::id()]
    )->fetchAll(PDO::FETCH_COLUMN);

    $opportunities = Database::query(
        "SELECT o.*, u.company_name
         FROM opportunities o
         JOIN users u ON u.id = o.client_id
         WHERE o.status = 'active' AND o.end_date >= CURDATE()
         AND (o.target_provider IS NULL OR o.target_provider = '')
         ORDER BY o.created_at DESC LIMIT 5",
        []
    )->fetchAll();

    // Marcar como novo
    foreach ($opportunities as &$opp) {
        $opp['is_new'] = !in_array($opp['id'], $viewedIds);
    }

    include dirname(__DIR__) . '/templates/provider/dashboard.php';
});

$router->get('/provider/opportunities', function () {
    Auth::requireType(Auth::TYPE_PROVIDER);
    $type = Helpers::sanitize($_GET['type'] ?? '');
    $q    = Helpers::sanitize($_GET['q'] ?? '');

    $sql    = "SELECT o.*, u.company_name,
                  (SELECT COUNT(*) FROM opportunity_leads WHERE opportunity_id = o.id AND provider_id = ?) AS viewed
               FROM opportunities o
               JOIN users u ON u.id = o.client_id
               WHERE o.status = 'active' AND o.end_date >= CURDATE()
               AND (o.target_provider IS NULL OR o.target_provider = '')";
    $params = [Auth::id()];

    if ($type && in_array($type, ['software', 'service'])) {
        $sql .= " AND o.type = ?";
        $params[] = $type;
    }
    if ($q) {
        $sql .= " AND (o.title LIKE ? OR o.description LIKE ?)";
        $params[] = '%' . $q . '%';
        $params[] = '%' . $q . '%';
    }
    $sql .= " ORDER BY o.created_at DESC";

    $opportunities = Database::query($sql, $params)->fetchAll();

    include dirname(__DIR__) . '/templates/provider/opportunities/index.php';
});

$router->get('/provider/opportunities/{id}', function (string $id) {
    Auth::requireType(Auth::TYPE_PROVIDER);

    $opportunity = Database::query(
        "SELECT o.*, u.company_name, u.representative_name, u.email, u.phone, u.role
         FROM opportunities o
         JOIN users u ON u.id = o.client_id
         WHERE o.id = ? AND o.status = 'active' AND o.end_date >= CURDATE()
         AND (o.target_provider IS NULL OR o.target_provider = '')
         LIMIT 1",
        [(int)$id]
    )->fetch();

    if (!$opportunity) {
        http_response_code(404);
        include dirname(__DIR__) . '/templates/errors/404.php';
        return;
    }

    // Registrar lead (INSERT IGNORE para não duplicar)
    Database::query(
        "INSERT IGNORE INTO opportunity_leads (opportunity_id, provider_id) VALUES (?, ?)",
        [(int)$id, Auth::id()]
    );

    include dirname(__DIR__) . '/templates/provider/opportunities/view.php';
});

$router->get('/provider/history', function () {
    Auth::requireType(Auth::TYPE_PROVIDER);
    $history = Database::query(
        "SELECT ol.*, o.title, o.type, o.status, o.end_date, u.company_name
         FROM opportunity_leads ol
         JOIN opportunities o ON o.id = ol.opportunity_id
         JOIN users u ON u.id = o.client_id
         WHERE ol.provider_id = ?
         ORDER BY ol.accessed_at DESC",
        [Auth::id()]
    )->fetchAll();

    include dirname(__DIR__) . '/templates/provider/history.php';
});

// -------------------------------------------------------
// ROTAS DO ADMINISTRADOR
// -------------------------------------------------------
$router->get('/admin/dashboard', function () {
    Auth::requireType(Auth::TYPE_ADMIN);

    $stats = [
        'active_users'  => Database::query("SELECT COUNT(*) FROM users WHERE status = 'active' AND type != 'admin'")->fetchColumn(),
        'pending_users' => Database::query("SELECT COUNT(*) FROM users WHERE status = 'pending_admin'")->fetchColumn(),
        'active_opps'   => Database::query("SELECT COUNT(*) FROM opportunities WHERE status = 'active'")->fetchColumn(),
        'total_leads'   => Database::query("SELECT COUNT(*) FROM opportunity_leads")->fetchColumn(),
    ];

    // Usuários pendentes de aprovação com verificação de CNPJ duplicado
    $pendingUsers = Database::query(
        "SELECT u.*,
            (SELECT COUNT(*) FROM users u2 WHERE u2.cnpj = u.cnpj AND u2.id != u.id AND u2.status = 'active') > 0 AS cnpj_duplicate
         FROM users u
         WHERE u.status = 'pending_admin'
         ORDER BY u.created_at ASC"
    )->fetchAll();

    $recentOpps = Database::query(
        "SELECT o.*, u.company_name FROM opportunities o
         JOIN users u ON u.id = o.client_id
         ORDER BY o.created_at DESC LIMIT 5"
    )->fetchAll();

    include dirname(__DIR__) . '/templates/admin/dashboard.php';
});

$router->get('/admin/users', function () {
    Auth::requireType(Auth::TYPE_ADMIN);

    $status = Helpers::sanitize($_GET['status'] ?? '');
    $type   = Helpers::sanitize($_GET['type'] ?? '');
    $q      = Helpers::sanitize($_GET['q'] ?? '');

    $sql    = "SELECT u.*,
                  (SELECT COUNT(*) FROM users u2 WHERE u2.cnpj = u.cnpj AND u2.id != u.id) > 0 AS cnpj_duplicate
               FROM users u WHERE u.type != 'admin'";
    $params = [];

    if ($status) { $sql .= " AND u.status = ?"; $params[] = $status; }
    if ($type)   { $sql .= " AND u.type = ?";   $params[] = $type; }
    if ($q) {
        $sql .= " AND (u.representative_name LIKE ? OR u.company_name LIKE ? OR u.cnpj LIKE ? OR u.email LIKE ?)";
        $like = '%' . $q . '%';
        array_push($params, $like, $like, $like, $like);
    }
    $sql .= " ORDER BY u.created_at DESC";

    $users = Database::query($sql, $params)->fetchAll();
    include dirname(__DIR__) . '/templates/admin/users/index.php';
});

$router->post('/admin/users/{id}/approve', function (string $id) {
    Auth::requireType(Auth::TYPE_ADMIN);
    CSRF::check();

    $user = Database::query(
        "SELECT * FROM users WHERE id = ? AND status = 'pending_admin' LIMIT 1", [(int)$id]
    )->fetch();

    if ($user) {
        Database::query("UPDATE users SET status = 'active' WHERE id = ?", [(int)$id]);
        Mailer::sendApprovalNotification($user['email'], $user['representative_name']);
        Helpers::flash('success', 'Usuário aprovado com sucesso!');
    }

    Helpers::redirect('/admin/users');
});

$router->post('/admin/users/{id}/reject', function (string $id) {
    Auth::requireType(Auth::TYPE_ADMIN);
    CSRF::check();
    Database::query("DELETE FROM users WHERE id = ? AND type != 'admin'", [(int)$id]);
    Helpers::flash('success', 'Usuário removido.');
    Helpers::redirect('/admin/users');
});

$router->post('/admin/users/{id}/deactivate', function (string $id) {
    Auth::requireType(Auth::TYPE_ADMIN);
    CSRF::check();
    Database::query(
        "UPDATE users SET status = 'pending_admin' WHERE id = ? AND type != 'admin'", [(int)$id]
    );
    Helpers::flash('success', 'Usuário desativado.');
    Helpers::redirect('/admin/users');
});

$router->get('/admin/opportunities', function () {
    Auth::requireType(Auth::TYPE_ADMIN);

    $status = Helpers::sanitize($_GET['status'] ?? '');
    $type   = Helpers::sanitize($_GET['type'] ?? '');

    $sql    = "SELECT o.*, u.company_name,
                  (SELECT COUNT(*) FROM opportunity_leads WHERE opportunity_id = o.id) AS leads_count
               FROM opportunities o
               JOIN users u ON u.id = o.client_id WHERE 1=1";
    $params = [];

    if ($status && in_array($status, ['active', 'closed'])) { $sql .= " AND o.status = ?"; $params[] = $status; }
    if ($type   && in_array($type,   ['software', 'service'])){ $sql .= " AND o.type = ?";   $params[] = $type; }
    $sql .= " ORDER BY o.created_at DESC";

    $opportunities = Database::query($sql, $params)->fetchAll();
    include dirname(__DIR__) . '/templates/admin/opportunities/index.php';
});

$router->get('/admin/reports', function () {
    Auth::requireType(Auth::TYPE_ADMIN);

    $totals = [
        'connections'      => Database::query("SELECT COUNT(*) FROM opportunity_leads")->fetchColumn(),
        'active_providers' => Database::query(
            "SELECT COUNT(DISTINCT provider_id) FROM opportunity_leads"
        )->fetchColumn(),
        'opps_with_leads'  => Database::query(
            "SELECT COUNT(DISTINCT opportunity_id) FROM opportunity_leads"
        )->fetchColumn(),
    ];

    $leads = Database::query(
        "SELECT ol.accessed_at,
                uprov.representative_name AS provider_name,
                uprov.company_name AS provider_company,
                o.title AS opp_title, o.type AS opp_type,
                ucli.company_name AS client_company
         FROM opportunity_leads ol
         JOIN users uprov ON uprov.id = ol.provider_id
         JOIN opportunities o ON o.id = ol.opportunity_id
         JOIN users ucli ON ucli.id = o.client_id
         ORDER BY ol.accessed_at DESC"
    )->fetchAll();

    include dirname(__DIR__) . '/templates/admin/reports.php';
});

// -------------------------------------------------------
// PERFIL (todos os usuários logados)
// -------------------------------------------------------
$router->get('/profile', function () {
    Auth::require();
    $user = Auth::user();
    include dirname(__DIR__) . '/templates/profile.php';
});

$router->post('/profile', function () {
    Auth::require();
    CSRF::check();

    $errors  = [];
    $user    = Auth::user();
    $name    = Helpers::sanitize($_POST['representative_name'] ?? '');
    $role    = Helpers::sanitize($_POST['role'] ?? '');
    $company = Helpers::sanitize($_POST['company_name'] ?? '');
    $phone   = preg_replace('/\D/', '', $_POST['phone'] ?? '');
    $newPwd  = $_POST['new_password'] ?? '';
    $newPwdC = $_POST['new_password_confirm'] ?? '';

    if (empty($name))    $errors[] = 'Nome é obrigatório.';
    if (empty($company)) $errors[] = 'Empresa é obrigatória.';

    if (!empty($newPwd)) {
        if (strlen($newPwd) < 8)    $errors[] = 'Nova senha deve ter no mínimo 8 caracteres.';
        if ($newPwd !== $newPwdC)   $errors[] = 'As senhas não coincidem.';
    }

    if (!empty($errors)) {
        include dirname(__DIR__) . '/templates/profile.php';
        return;
    }

    $params = [$name, $role, $company, $phone];
    $sql    = "UPDATE users SET representative_name=?, role=?, company_name=?, phone=?";

    if (!empty($newPwd)) {
        $sql .= ", password_hash=?";
        $params[] = password_hash($newPwd, PASSWORD_BCRYPT, ['cost' => 12]);
    }

    $sql .= " WHERE id=?";
    $params[] = Auth::id();

    Database::query($sql, $params);

    // Atualizar nome na sessão
    $_SESSION['user_name'] = $name;

    Helpers::flash('success', 'Perfil atualizado com sucesso!');
    Helpers::redirect('/profile');
});

// -------------------------------------------------------
// Despachar
// -------------------------------------------------------
$router->dispatch();
