<?php
$realType      = Auth::type();
$effectiveType = Auth::effectiveType();
$viewAs        = Auth::viewAs();
$links = [];

if ($effectiveType === Auth::TYPE_ADMIN) {
    $links = [
        'Usuários'      => APP_URL . '/gerenciar-usuarios',
        'Oportunidades' => APP_URL . '/gerenciar-oportunidades',
        'Relatórios'    => APP_URL . '/relatorios-conexao',
    ];
} elseif ($effectiveType === Auth::TYPE_CLIENT) {
    $links = [
        'Painel'             => APP_URL . '/painel-cliente',
        'Oportunidades'      => APP_URL . '/minhas-oportunidades',
        'Nova Oportunidade'  => APP_URL . '/nova-oportunidade',
    ];
} elseif ($effectiveType === Auth::TYPE_PROVIDER) {
    $links = [
        'Painel'        => APP_URL . '/painel-parceiro',
        'Oportunidades' => APP_URL . '/oportunidades-disponiveis',
        'Meus Produtos' => APP_URL . '/meus-produtos',
        'Histórico'     => APP_URL . '/historico-parceiro',
    ];
}

$currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$typeLabels  = [
    Auth::TYPE_ADMIN    => 'Admin',
    Auth::TYPE_CLIENT   => 'Cliente',
    Auth::TYPE_PROVIDER => 'Fornecedor',
];
?>

<?php if ($viewAs): ?>
<div class="w-full py-2 px-4 text-center text-sm font-bold flex items-center justify-center gap-3"
     style="background:#B8FF45;color:#06090F;">
    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
    </svg>
    Você está visualizando como <strong><?= $typeLabels[$viewAs] ?? $viewAs ?></strong>
    <a href="<?= APP_URL ?>/encerrar-simulacao"
       class="inline-flex items-center gap-1 px-3 py-0.5 rounded-full text-xs font-bold transition-opacity hover:opacity-80"
       style="background:#06090F;color:#B8FF45;">
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        Voltar ao Admin
    </a>
</div>
<?php endif; ?>

<nav class="bg-void shadow-sm sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">

            <a href="<?= APP_URL ?>/painel" class="flex items-center gap-1 font-black text-xl tracking-tight" style="font-family:Roboto,sans-serif;">
                <span class="text-plus-cyan">Partner</span>
                <span class="text-surge-lime">Plus</span>
                <?php if ($viewAs): ?>
                <span class="ml-2 text-xs font-bold px-2 py-0.5 rounded-full" style="background:#B8FF45;color:#06090F;">
                    <?= $typeLabels[$viewAs] ?>
                </span>
                <?php endif; ?>
            </a>

            <div class="hidden md:flex items-center gap-1">
                <?php foreach ($links as $label => $href): ?>
                    <a href="<?= Helpers::e($href) ?>"
                       class="px-3 py-2 rounded-lg text-sm font-medium transition-colors
                              <?= str_starts_with($currentPath, parse_url($href, PHP_URL_PATH)) ? 'text-void bg-plus-cyan' : 'text-slate-300 hover:text-white hover:bg-white/10' ?>">
                        <?= Helpers::e($label) ?>
                    </a>
                <?php endforeach; ?>

                <?php if ($realType === Auth::TYPE_ADMIN && !$viewAs): ?>
                <div class="relative ml-2" id="view-as-wrapper">
                    <button onclick="document.getElementById('view-as-menu').classList.toggle('hidden')"
                            class="flex items-center gap-1.5 px-3 py-2 rounded-lg text-sm font-medium text-slate-300 hover:text-white hover:bg-white/10 transition-colors border border-dashed border-slate-600">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        Simular visão
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                    <div id="view-as-menu" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-lg border border-slate-100 py-1 z-50">
                        <p class="px-4 py-2 text-xs font-semibold text-slate-400 uppercase tracking-wide">Simular como</p>
                        <form method="post" action="<?= APP_URL ?>/simular-visao">
                            <?= CSRF::field() ?>
                            <input type="hidden" name="type" value="client">
                            <button type="submit" class="w-full text-left px-4 py-2.5 text-sm text-slate-700 hover:bg-cyan-50 hover:text-void flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-blue-400 inline-block"></span>Cliente
                            </button>
                        </form>
                        <form method="post" action="<?= APP_URL ?>/simular-visao">
                            <?= CSRF::field() ?>
                            <input type="hidden" name="type" value="provider">
                            <button type="submit" class="w-full text-left px-4 py-2.5 text-sm text-slate-700 hover:bg-cyan-50 hover:text-void flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-green-400 inline-block"></span>Fornecedor
                            </button>
                        </form>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <div class="flex items-center gap-3">
                <div class="hidden md:block text-right">
                    <p class="text-white text-sm font-medium leading-none"><?= Helpers::e($_SESSION['user_name'] ?? '') ?></p>
                    <p class="text-slate-400 text-xs mt-0.5">
                        <?= $viewAs ? Helpers::e($typeLabels[$viewAs]) . ' (Admin)' : Helpers::e($typeLabels[$realType] ?? $realType) ?>
                    </p>
                </div>
                <div class="relative">
                    <button onclick="this.nextElementSibling.classList.toggle('hidden')"
                            class="w-9 h-9 rounded-full flex items-center justify-center text-void font-bold text-sm"
                            style="background:<?= $viewAs ? '#B8FF45' : '#00E5C8' ?>;">
                        <?= strtoupper(substr($_SESSION['user_name'] ?? 'U', 0, 1)) ?>
                    </button>
                    <div class="hidden absolute right-0 mt-2 w-52 bg-white rounded-xl shadow-lg border border-slate-100 py-1 z-50">
                        <?php if ($viewAs): ?>
                        <div class="px-4 py-2 border-b border-slate-100">
                            <p class="text-xs text-slate-400">Simulando como</p>
                            <p class="text-sm font-bold text-void"><?= $typeLabels[$viewAs] ?></p>
                        </div>
                        <a href="<?= APP_URL ?>/encerrar-simulacao"
                           class="block px-4 py-2.5 text-sm font-semibold hover:bg-slate-50 text-void">
                            ← Voltar ao Painel Admin
                        </a>
                        <hr class="my-1 border-slate-100">
                        <?php endif; ?>
                        <a href="<?= APP_URL ?>/meu-perfil" class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">Meu Perfil</a>
                        <hr class="my-1 border-slate-100">
                        <a href="<?= APP_URL ?>/sair" class="block px-4 py-2 text-sm text-red-600 hover:bg-red-50">Sair</a>
                    </div>
                </div>

                <button id="mobile-menu-btn" class="md:hidden text-slate-300 hover:text-white p-1">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>
            </div>
        </div>

        <div id="mobile-menu" class="hidden md:hidden pb-3">
            <?php foreach ($links as $label => $href): ?>
                <a href="<?= Helpers::e($href) ?>"
                   class="block px-3 py-2 rounded-lg text-sm font-medium text-slate-300 hover:text-white hover:bg-white/10 mb-1">
                    <?= Helpers::e($label) ?>
                </a>
            <?php endforeach; ?>
            <?php if ($realType === Auth::TYPE_ADMIN && !$viewAs): ?>
            <hr class="border-slate-700 my-2">
            <p class="px-3 py-1 text-xs text-slate-500 font-semibold uppercase">Simular visão</p>
            <form method="post" action="<?= APP_URL ?>/simular-visao" class="px-3">
                <?= CSRF::field() ?><input type="hidden" name="type" value="client">
                <button type="submit" class="block w-full text-left py-2 text-sm text-slate-300 hover:text-white">→ Como Cliente</button>
            </form>
            <form method="post" action="<?= APP_URL ?>/simular-visao" class="px-3">
                <?= CSRF::field() ?><input type="hidden" name="type" value="provider">
                <button type="submit" class="block w-full text-left py-2 text-sm text-slate-300 hover:text-white">→ Como Fornecedor</button>
            </form>
            <?php endif; ?>
            <?php if ($viewAs): ?>
            <hr class="border-slate-700 my-2">
            <a href="<?= APP_URL ?>/encerrar-simulacao" class="block px-3 py-2 text-sm font-bold" style="color:#B8FF45;">← Voltar ao Admin</a>
            <?php endif; ?>
            <hr class="border-slate-700 my-2">
            <a href="<?= APP_URL ?>/meu-perfil" class="block px-3 py-2 text-sm text-slate-300 hover:text-white">Meu Perfil</a>
            <a href="<?= APP_URL ?>/sair"       class="block px-3 py-2 text-sm text-red-400 hover:text-red-300">Sair</a>
        </div>
    </div>
</nav>
<script>
document.getElementById('mobile-menu-btn')?.addEventListener('click', () => {
    document.getElementById('mobile-menu').classList.toggle('hidden');
});
document.addEventListener('click', (e) => {
    const wrapper = document.getElementById('view-as-wrapper');
    if (wrapper && !wrapper.contains(e.target)) {
        document.getElementById('view-as-menu')?.classList.add('hidden');
    }
});
</script>
