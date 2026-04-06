<?php
/**
 * Partner Plus - Wizard de Instalação
 * Guia o administrador pela configuração inicial do sistema.
 * Este arquivo deve ser inacessível após a instalação.
 */
declare(strict_types=1);

// Bloquear acesso se já instalado
if (file_exists(__DIR__ . '/.installed')) {
    http_response_code(403);
    die('<h1>403 - Acesso Negado</h1><p>O sistema já foi instalado. Remova o arquivo <code>/install/.installed</code> apenas se quiser reinstalar.</p>');
}

session_start();

$step    = (int)($_GET['step'] ?? 1);
$errors  = [];
$success = [];

// -------------------------------------------------------
// STEP 1 → Configuração do Banco de Dados
// -------------------------------------------------------
if ($step === 1 && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $dbHost = trim($_POST['db_host'] ?? 'localhost');
    $dbName = trim($_POST['db_name'] ?? '');
    $dbUser = trim($_POST['db_user'] ?? '');
    $dbPass = $_POST['db_pass'] ?? '';

    if (empty($dbName) || empty($dbUser)) {
        $errors[] = 'Nome do banco e usuário são obrigatórios.';
    } else {
        // Testar conexão
        $dsn = "mysql:host={$dbHost};dbname={$dbName};charset=utf8mb4";
        try {
            $pdo = new PDO($dsn, $dbUser, $dbPass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

            // Guardar dados na sessão e avançar
            $_SESSION['install'] = [
                'db_host' => $dbHost,
                'db_name' => $dbName,
                'db_user' => $dbUser,
                'db_pass' => $dbPass,
            ];

            header('Location: ?step=2');
            exit;
        } catch (PDOException $e) {
            $errors[] = 'Falha na conexão: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES);
        }
    }
}

// -------------------------------------------------------
// STEP 2 → Criar Tabelas
// -------------------------------------------------------
if ($step === 2 && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($_SESSION['install'])) {
        header('Location: ?step=1');
        exit;
    }

    $cfg = $_SESSION['install'];
    $dsn = "mysql:host={$cfg['db_host']};dbname={$cfg['db_name']};charset=utf8mb4";

    try {
        $pdo = new PDO($dsn, $cfg['db_user'], $cfg['db_pass'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

        $sql = file_get_contents(__DIR__ . '/schema.sql');
        // Executar cada statement separadamente
        foreach (explode(';', $sql) as $statement) {
            $statement = trim($statement);
            if (!empty($statement) && !str_starts_with($statement, '--')) {
                $pdo->exec($statement);
            }
        }

        header('Location: ?step=3');
        exit;
    } catch (PDOException $e) {
        $errors[] = 'Erro ao criar tabelas: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES);
    }
}

// -------------------------------------------------------
// STEP 3 → Criar Administrador
// -------------------------------------------------------
if ($step === 3 && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($_SESSION['install'])) {
        header('Location: ?step=1');
        exit;
    }

    $adminEmail    = strtolower(trim($_POST['admin_email'] ?? ''));
    $adminPassword = $_POST['admin_password'] ?? '';
    $adminConfirm  = $_POST['admin_confirm'] ?? '';
    $adminName     = trim($_POST['admin_name'] ?? '');
    $appUrl        = rtrim(trim($_POST['app_url'] ?? ''), '/');

    if (!filter_var($adminEmail, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'E-mail inválido.';
    }
    if (strlen($adminPassword) < 8) {
        $errors[] = 'A senha deve ter no mínimo 8 caracteres.';
    }
    if ($adminPassword !== $adminConfirm) {
        $errors[] = 'As senhas não coincidem.';
    }
    if (empty($adminName)) {
        $errors[] = 'Nome do administrador é obrigatório.';
    }

    if (empty($errors)) {
        $cfg = $_SESSION['install'];
        $dsn = "mysql:host={$cfg['db_host']};dbname={$cfg['db_name']};charset=utf8mb4";

        try {
            $pdo = new PDO($dsn, $cfg['db_user'], $cfg['db_pass'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

            // Verificar se já existe admin
            $count = $pdo->query("SELECT COUNT(*) FROM users WHERE type = 'admin'")->fetchColumn();
            if ($count > 0) {
                $errors[] = 'Um administrador já foi criado. Reinstale o sistema se necessário.';
            } else {
                $hash = password_hash($adminPassword, PASSWORD_BCRYPT, ['cost' => 12]);
                $stmt = $pdo->prepare("
                    INSERT INTO users (cnpj, company_name, representative_name, role, email, phone, password_hash, type, status)
                    VALUES ('00000000000000', 'Partner Plus', ?, 'Administrador', ?, '00000000000', ?, 'admin', 'active')
                ");
                $stmt->execute([$adminName, $adminEmail, $hash]);

                $_SESSION['install']['admin_email'] = $adminEmail;
                $_SESSION['install']['app_url']     = $appUrl;

                header('Location: ?step=4');
                exit;
            }
        } catch (PDOException $e) {
            $errors[] = 'Erro ao criar admin: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES);
        }
    }
}

// -------------------------------------------------------
// STEP 4 → Gerar config.php e Finalizar
// -------------------------------------------------------
if ($step === 4 && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($_SESSION['install'])) {
        header('Location: ?step=1');
        exit;
    }

    $cfg    = $_SESSION['install'];
    $appUrl = $cfg['app_url'] ?? 'http://localhost/partner-plus/public';
    $secret = bin2hex(random_bytes(32));

    // Gerar config.php
    $configContent = "<?php\n// Partner Plus - Configuração gerada pelo wizard de instalação em " . date('Y-m-d H:i:s') . "\n\n";
    $configContent .= "define('DB_HOST',    " . var_export($cfg['db_host'], true) . ");\n";
    $configContent .= "define('DB_NAME',    " . var_export($cfg['db_name'], true) . ");\n";
    $configContent .= "define('DB_USER',    " . var_export($cfg['db_user'], true) . ");\n";
    $configContent .= "define('DB_PASS',    " . var_export($cfg['db_pass'], true) . ");\n";
    $configContent .= "define('DB_CHARSET', 'utf8mb4');\n\n";
    $configContent .= "define('APP_NAME', 'Partner Plus');\n";
    $configContent .= "define('APP_URL',  " . var_export($appUrl, true) . ");\n";
    $configContent .= "define('APP_ENV',  'production');\n\n";
    $configContent .= "define('MAIL_FROM',      'noreply@' . parse_url(" . var_export($appUrl, true) . ", PHP_URL_HOST));\n";
    $configContent .= "define('MAIL_FROM_NAME', 'Partner Plus');\n\n";
    $configContent .= "define('APP_SECRET', " . var_export($secret, true) . ");\n";

    $configPath = dirname(__DIR__) . '/config/config.php';

    if (file_put_contents($configPath, $configContent) === false) {
        $errors[] = 'Não foi possível escrever o arquivo config.php. Verifique as permissões da pasta /config.';
    } else {
        // Criar arquivo .installed para bloquear o wizard
        file_put_contents(__DIR__ . '/.installed', date('Y-m-d H:i:s'));

        // Limpar sessão de instalação
        unset($_SESSION['install']);

        header('Location: ?step=5');
        exit;
    }
}

// URL base do wizard para action do form
$wizardUrl = htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instalação - Partner Plus</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        cyan:  { plus: '#00E5C8' },
                        lime:  { surge: '#B8FF45' },
                        void:  '#06090F',
                    },
                    fontFamily: {
                        sans:  ['"DM Sans"', 'sans-serif'],
                        title: ['Roboto', 'sans-serif'],
                    },
                }
            }
        }
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@700;900&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'DM Sans', sans-serif; background: #f8fafc; }
        .step-active { background: #00E5C8; color: #06090F; font-weight: 700; }
        .step-done   { background: #B8FF45; color: #06090F; }
        .step-todo   { background: #e2e8f0; color: #94a3b8; }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-lg">

        <!-- Logo -->
        <div class="text-center mb-8">
            <h1 class="text-3xl font-black tracking-tight" style="font-family:Roboto,sans-serif;color:#06090F;">
                Partner <span style="color:#00E5C8;">Plus</span>
            </h1>
            <p class="text-slate-500 mt-1">Wizard de Instalação</p>
        </div>

        <!-- Steps -->
        <div class="flex items-center justify-center gap-2 mb-8">
            <?php
            $steps = ['Banco de Dados', 'Tabelas', 'Administrador', 'Configurar', 'Concluído'];
            foreach ($steps as $i => $label):
                $n = $i + 1;
                $cls = $n < $step ? 'step-done' : ($n === $step ? 'step-active' : 'step-todo');
            ?>
            <div class="flex flex-col items-center">
                <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold <?= $cls ?>"><?= $n ?></div>
                <span class="text-xs text-slate-500 mt-1 hidden sm:block"><?= $label ?></span>
            </div>
            <?php if ($n < count($steps)): ?>
                <div class="w-8 h-px bg-slate-200 mb-4"></div>
            <?php endif; ?>
            <?php endforeach; ?>
        </div>

        <!-- Card -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-8">

            <?php if (!empty($errors)): ?>
            <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl">
                <?php foreach ($errors as $err): ?>
                    <p class="text-red-700 text-sm"><?= htmlspecialchars($err, ENT_QUOTES) ?></p>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <!-- STEP 1: DB Config -->
            <?php if ($step === 1): ?>
            <h2 class="text-xl font-bold mb-1" style="font-family:Roboto,sans-serif;">Configuração do Banco</h2>
            <p class="text-slate-500 text-sm mb-6">Informe as credenciais MySQL. A conexão será testada antes de prosseguir.</p>
            <form method="post" action="<?= $wizardUrl ?>?step=1" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Host</label>
                    <input type="text" name="db_host" value="<?= htmlspecialchars($_POST['db_host'] ?? 'localhost', ENT_QUOTES) ?>"
                        class="w-full border border-slate-200 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#00E5C8]">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Nome do Banco <span class="text-red-500">*</span></label>
                    <input type="text" name="db_name" value="<?= htmlspecialchars($_POST['db_name'] ?? '', ENT_QUOTES) ?>"
                        placeholder="partner_plus" required
                        class="w-full border border-slate-200 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#00E5C8]">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Usuário <span class="text-red-500">*</span></label>
                    <input type="text" name="db_user" value="<?= htmlspecialchars($_POST['db_user'] ?? '', ENT_QUOTES) ?>"
                        placeholder="root" required
                        class="w-full border border-slate-200 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#00E5C8]">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Senha</label>
                    <input type="password" name="db_pass"
                        class="w-full border border-slate-200 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#00E5C8]">
                </div>
                <button type="submit"
                    class="w-full py-3 rounded-xl font-bold text-sm text-[#06090F] transition-opacity hover:opacity-90"
                    style="background:#00E5C8;">
                    Testar Conexão e Avançar →
                </button>
            </form>

            <!-- STEP 2: Criar Tabelas -->
            <?php elseif ($step === 2): ?>
            <h2 class="text-xl font-bold mb-1" style="font-family:Roboto,sans-serif;">Criar Tabelas</h2>
            <p class="text-slate-500 text-sm mb-6">O schema do banco de dados será criado agora. Clique em continuar para executar o script SQL.</p>
            <div class="bg-slate-50 rounded-xl p-4 mb-6 text-sm text-slate-600 space-y-1">
                <p>✓ Conexão estabelecida com sucesso</p>
                <p>→ Serão criadas as tabelas: <code>users</code>, <code>email_verifications</code>, <code>password_resets</code>, <code>opportunities</code>, <code>opportunity_leads</code></p>
            </div>
            <form method="post" action="<?= $wizardUrl ?>?step=2">
                <button type="submit"
                    class="w-full py-3 rounded-xl font-bold text-sm text-[#06090F] transition-opacity hover:opacity-90"
                    style="background:#00E5C8;">
                    Criar Tabelas e Avançar →
                </button>
            </form>

            <!-- STEP 3: Criar Admin -->
            <?php elseif ($step === 3): ?>
            <h2 class="text-xl font-bold mb-1" style="font-family:Roboto,sans-serif;">Conta do Administrador</h2>
            <p class="text-slate-500 text-sm mb-6">Crie a conta do único administrador do sistema.</p>
            <form method="post" action="<?= $wizardUrl ?>?step=3" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Nome completo <span class="text-red-500">*</span></label>
                    <input type="text" name="admin_name" value="<?= htmlspecialchars($_POST['admin_name'] ?? '', ENT_QUOTES) ?>"
                        required class="w-full border border-slate-200 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#00E5C8]">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">E-mail <span class="text-red-500">*</span></label>
                    <input type="email" name="admin_email" value="<?= htmlspecialchars($_POST['admin_email'] ?? '', ENT_QUOTES) ?>"
                        required class="w-full border border-slate-200 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#00E5C8]">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Senha (mín. 8 caracteres) <span class="text-red-500">*</span></label>
                    <input type="password" name="admin_password" required minlength="8"
                        class="w-full border border-slate-200 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#00E5C8]">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Confirmar Senha <span class="text-red-500">*</span></label>
                    <input type="password" name="admin_confirm" required
                        class="w-full border border-slate-200 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#00E5C8]">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">URL da aplicação</label>
                    <input type="url" name="app_url"
                        value="<?= htmlspecialchars($_POST['app_url'] ?? (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/partner-plus/public', ENT_QUOTES) ?>"
                        class="w-full border border-slate-200 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#00E5C8]">
                    <p class="text-xs text-slate-400 mt-1">Ex: https://seudominio.com/public</p>
                </div>
                <button type="submit"
                    class="w-full py-3 rounded-xl font-bold text-sm text-[#06090F] transition-opacity hover:opacity-90"
                    style="background:#00E5C8;">
                    Criar Administrador e Avançar →
                </button>
            </form>

            <!-- STEP 4: Gerar config.php -->
            <?php elseif ($step === 4): ?>
            <h2 class="text-xl font-bold mb-1" style="font-family:Roboto,sans-serif;">Gerar Configuração</h2>
            <p class="text-slate-500 text-sm mb-6">O arquivo <code>config/config.php</code> será gerado com todas as configurações.</p>
            <div class="bg-slate-50 rounded-xl p-4 mb-6 text-sm text-slate-600 space-y-1">
                <p>✓ Banco de dados configurado</p>
                <p>✓ Tabelas criadas</p>
                <p>✓ Administrador criado</p>
                <p>→ Gerar <code>config/config.php</code> e bloquear o wizard</p>
            </div>
            <form method="post" action="<?= $wizardUrl ?>?step=4">
                <button type="submit"
                    class="w-full py-3 rounded-xl font-bold text-sm text-[#06090F] transition-opacity hover:opacity-90"
                    style="background:#00E5C8;">
                    Finalizar Instalação →
                </button>
            </form>

            <!-- STEP 5: Concluído -->
            <?php elseif ($step === 5): ?>
            <div class="text-center">
                <div class="w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4" style="background:#B8FF45;">
                    <svg class="w-8 h-8" fill="none" stroke="#06090F" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <h2 class="text-xl font-bold mb-2" style="font-family:Roboto,sans-serif;">Instalação Concluída!</h2>
                <p class="text-slate-500 text-sm mb-6">O sistema Partner Plus foi instalado com sucesso. O wizard foi bloqueado.</p>
                <a href="../public/"
                    class="inline-block px-8 py-3 rounded-xl font-bold text-sm text-[#06090F] transition-opacity hover:opacity-90"
                    style="background:#00E5C8;">
                    Acessar a Plataforma →
                </a>
            </div>
            <?php endif; ?>

        </div>

        <p class="text-center text-xs text-slate-400 mt-6">Partner Plus © <?= date('Y') ?></p>
    </div>
</body>
</html>
