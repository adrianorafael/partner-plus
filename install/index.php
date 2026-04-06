<?php
/**
 * Partner Plus - Wizard de Instalação
 * Guia o administrador pela configuração inicial do sistema.
 * Este arquivo deve ser inacessível após a instalação.
 */
declare(strict_types=1);

session_start();

// Bloquear acesso se já instalado — exceto se for a tela de conclusão
// da instalação atual (flag 'install_complete' na sessão).
if (file_exists(__DIR__ . '/.installed')) {
    $step = (int)($_GET['step'] ?? 0);
    if ($step === 5 && !empty($_SESSION['install_complete'])) {
        // Deixar passar: é o redirect legítimo do step 4
    } else {
        // Acesso direto após instalação: exibir tela informativa
        ?>
        <!DOCTYPE html>
        <html lang="pt-BR">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Sistema já instalado — Partner Plus</title>
            <script src="https://cdn.tailwindcss.com"></script>
            <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@700;900&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
        </head>
        <body class="min-h-screen flex items-center justify-center p-4" style="background:#f8fafc;font-family:'DM Sans',sans-serif;">
            <div class="w-full max-w-md text-center">
                <h1 class="text-2xl font-black mb-1" style="font-family:Roboto,sans-serif;color:#06090F;">
                    Partner <span style="color:#00E5C8;">Plus</span>
                </h1>
                <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-8 mt-6">
                    <div class="w-14 h-14 rounded-full flex items-center justify-center mx-auto mb-4" style="background:#fef3c7;">
                        <svg class="w-7 h-7" style="color:#d97706;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                        </svg>
                    </div>
                    <h2 class="text-lg font-bold mb-2" style="font-family:Roboto,sans-serif;">Sistema já instalado</h2>
                    <p class="text-slate-500 text-sm mb-4">
                        O Partner Plus já foi configurado em
                        <strong><?= htmlspecialchars(file_get_contents(__DIR__ . '/.installed'), ENT_QUOTES) ?></strong>.
                    </p>
                    <p class="text-slate-400 text-xs mb-6">
                        Para reinstalar, remova o arquivo <code class="bg-slate-100 px-1 py-0.5 rounded">install/.installed</code>
                        via gerenciador de arquivos do servidor.
                    </p>
                    <a href="../entrar"
                       class="inline-block px-8 py-3 rounded-xl font-bold text-sm transition-opacity hover:opacity-90"
                       style="background:#00E5C8;color:#06090F;">
                        Acessar a Plataforma
                    </a>
                </div>
            </div>
        </body>
        </html>
        <?php
        exit;
    }
}

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
        // Verificar permissão de escrita em /config antes de prosseguir
        $configDir = dirname(__DIR__) . '/config';
        if (!is_writable($configDir)) {
            $errors[] = 'A pasta /config não tem permissão de escrita. '
                      . 'Defina chmod 755 na pasta config/ no servidor antes de continuar.';
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

        // Remover linhas que são apenas comentários SQL (-- ...) para não
        // interferir na divisão por ponto-e-vírgula. O PDO do MySQL suporta
        // comentários inline, mas blocos de comentário antes de CREATE TABLE
        // fazem o str_starts_with detectar o statement como comentário.
        $lines   = explode("\n", $sql);
        $cleaned = [];
        foreach ($lines as $line) {
            if (!preg_match('/^\s*--/', $line)) {
                $cleaned[] = $line;
            }
        }
        $sql = implode("\n", $cleaned);

        // Executar cada statement separadamente
        foreach (explode(';', $sql) as $statement) {
            $statement = trim($statement);
            if (!empty($statement)) {
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
    $mailHost       = parse_url($appUrl, PHP_URL_HOST) ?: 'partnerplus.local';
    $configContent .= "define('MAIL_FROM',      " . var_export('noreply@' . $mailHost, true) . ");\n";
    $configContent .= "define('MAIL_FROM_NAME', 'Partner Plus');\n\n";
    $configContent .= "define('APP_SECRET', " . var_export($secret, true) . ");\n";

    $configPath = dirname(__DIR__) . '/config/config.php';

    // Checar permissão de escrita nos dois arquivos antes de qualquer coisa
    if (!is_writable(dirname($configPath))) {
        $errors[] = 'Sem permissão de escrita na pasta <code>/config</code>. Defina chmod 755 via FTP/cPanel.';
    } elseif (!is_writable(__DIR__)) {
        $errors[] = 'Sem permissão de escrita na pasta <code>/install</code> (necessário para criar .installed). Defina chmod 755.';
    }

    if (empty($errors)) {
        // 1. Gravar config.php
        if (file_put_contents($configPath, $configContent) === false) {
            $errors[] = 'Falha ao gravar <code>config/config.php</code>. Verifique permissões do servidor.';
        }
    }

    if (empty($errors)) {
        // 2. Validar que o config gerado é legível e define DB_HOST (sanity check)
        ob_start();
        $testLoad = @include $configPath;
        ob_end_clean();
        if ($testLoad === false || !defined('DB_HOST')) {
            @unlink($configPath);
            $errors[] = 'O arquivo config.php foi gravado mas não pôde ser carregado. Verifique se o PHP tem permissão de leitura.';
        }
    }

    if (empty($errors)) {
        // 3. Testar conexão com as credenciais gravadas (confirma que o DB ainda está acessível)
        $dsnFinal = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
        try {
            new PDO($dsnFinal, DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        } catch (PDOException $e) {
            @unlink($configPath);
            $errors[] = 'Configuração gravada, mas a conexão com o banco falhou: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES) . '. O arquivo config.php foi removido.';
        }
    }

    if (empty($errors)) {
        // 4. Criar .installed — a partir daqui o wizard fica bloqueado para acesso externo
        file_put_contents(__DIR__ . '/.installed', date('Y-m-d H:i:s'));

        // Gravar flag de sessão que permite exibir o step 5 mesmo com .installed presente
        $_SESSION['install_complete'] = true;
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
            <?php
            // Limpar flag de sessão — após renderizar, tentativas futuras de acessar
            // /install/?step=5 cairão na tela "já instalado"
            unset($_SESSION['install_complete']);
            // Coletar resumo da instalação para exibir
            $installedAt = trim(@file_get_contents(__DIR__ . '/.installed') ?: date('Y-m-d H:i:s'));
            $configData  = @include dirname(__DIR__) . '/config/config.php';
            ?>
            <div class="text-center">
                <!-- Ícone de sucesso -->
                <div class="w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4" style="background:#B8FF45;">
                    <svg class="w-8 h-8" fill="none" stroke="#06090F" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <h2 class="text-xl font-bold mb-1" style="font-family:Roboto,sans-serif;">Instalação Concluída!</h2>
                <p class="text-slate-400 text-xs mb-6">Concluído em <?= htmlspecialchars($installedAt, ENT_QUOTES) ?></p>

                <!-- Checklist do que foi feito -->
                <div class="bg-slate-50 rounded-xl p-4 text-left space-y-2 mb-6">
                    <div class="flex items-center gap-2 text-sm text-slate-700">
                        <svg class="w-4 h-4 flex-shrink-0" style="color:#00E5C8;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                        </svg>
                        Conexão com o banco de dados verificada
                    </div>
                    <div class="flex items-center gap-2 text-sm text-slate-700">
                        <svg class="w-4 h-4 flex-shrink-0" style="color:#00E5C8;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                        </svg>
                        Tabelas criadas no banco de dados
                    </div>
                    <div class="flex items-center gap-2 text-sm text-slate-700">
                        <svg class="w-4 h-4 flex-shrink-0" style="color:#00E5C8;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                        </svg>
                        Conta de administrador criada
                    </div>
                    <div class="flex items-center gap-2 text-sm text-slate-700">
                        <svg class="w-4 h-4 flex-shrink-0" style="color:#00E5C8;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                        </svg>
                        Arquivo <code class="bg-white border border-slate-200 px-1 rounded text-xs">config/config.php</code> gerado
                    </div>
                    <div class="flex items-center gap-2 text-sm text-slate-700">
                        <svg class="w-4 h-4 flex-shrink-0" style="color:#00E5C8;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                        </svg>
                        Wizard de instalação bloqueado
                    </div>
                </div>

                <a href="../entrar"
                   class="inline-block px-8 py-3 rounded-xl font-bold text-sm transition-opacity hover:opacity-90"
                   style="background:#00E5C8;color:#06090F;">
                    Acessar a Plataforma →
                </a>
            </div>
            <?php endif; ?>

        </div>

        <p class="text-center text-xs text-slate-400 mt-6">Partner Plus © <?= date('Y') ?></p>
    </div>
</body>
</html>
