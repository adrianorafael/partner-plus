<?php
// Determina links de nav com base no tipo de usuário logado
$userType = Auth::type();
$links = [];

if ($userType === Auth::TYPE_ADMIN) {
    $links = [
        'Usuários'      => APP_URL . '/admin/users',
        'Oportunidades' => APP_URL . '/admin/opportunities',
        'Relatórios'    => APP_URL . '/admin/reports',
    ];
} elseif ($userType === Auth::TYPE_CLIENT) {
    $links = [
        'Dashboard'     => APP_URL . '/client/dashboard',
        'Oportunidades' => APP_URL . '/client/opportunities',
        'Nova Oportunidade' => APP_URL . '/client/opportunities/create',
    ];
} elseif ($userType === Auth::TYPE_PROVIDER) {
    $links = [
        'Dashboard'     => APP_URL . '/provider/dashboard',
        'Oportunidades' => APP_URL . '/provider/opportunities',
        'Histórico'     => APP_URL . '/provider/history',
    ];
}

$currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
?>
<nav class="bg-void shadow-sm sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">

            <!-- Logo -->
            <a href="<?= APP_URL ?>/dashboard" class="flex items-center gap-1 font-black text-xl tracking-tight" style="font-family:Roboto,sans-serif;">
                <span class="text-plus-cyan">Partner</span>
                <span class="text-surge-lime">Plus</span>
            </a>

            <!-- Links desktop -->
            <div class="hidden md:flex items-center gap-1">
                <?php foreach ($links as $label => $href): ?>
                    <a href="<?= Helpers::e($href) ?>"
                       class="px-3 py-2 rounded-lg text-sm font-medium transition-colors
                              <?= str_starts_with($currentPath, parse_url($href, PHP_URL_PATH)) ? 'text-void bg-plus-cyan' : 'text-slate-300 hover:text-white hover:bg-white/10' ?>">
                        <?= Helpers::e($label) ?>
                    </a>
                <?php endforeach; ?>
            </div>

            <!-- User menu -->
            <div class="flex items-center gap-3">
                <div class="hidden md:block text-right">
                    <p class="text-white text-sm font-medium leading-none"><?= Helpers::e($_SESSION['user_name'] ?? '') ?></p>
                    <p class="text-slate-400 text-xs mt-0.5 capitalize"><?= Helpers::e($userType ?? '') ?></p>
                </div>
                <div class="relative" x-data="{ open: false }">
                    <button onclick="this.nextElementSibling.classList.toggle('hidden')"
                            class="w-9 h-9 rounded-full bg-plus-cyan flex items-center justify-center text-void font-bold text-sm">
                        <?= strtoupper(substr($_SESSION['user_name'] ?? 'U', 0, 1)) ?>
                    </button>
                    <div class="hidden absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-lg border border-slate-100 py-1 z-50">
                        <a href="<?= APP_URL ?>/profile"
                           class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">Meu Perfil</a>
                        <hr class="my-1 border-slate-100">
                        <a href="<?= APP_URL ?>/logout"
                           class="block px-4 py-2 text-sm text-red-600 hover:bg-red-50">Sair</a>
                    </div>
                </div>

                <!-- Mobile menu button -->
                <button id="mobile-menu-btn" class="md:hidden text-slate-300 hover:text-white p-1">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Mobile menu -->
        <div id="mobile-menu" class="hidden md:hidden pb-3">
            <?php foreach ($links as $label => $href): ?>
                <a href="<?= Helpers::e($href) ?>"
                   class="block px-3 py-2 rounded-lg text-sm font-medium text-slate-300 hover:text-white hover:bg-white/10 mb-1">
                    <?= Helpers::e($label) ?>
                </a>
            <?php endforeach; ?>
            <hr class="border-slate-700 my-2">
            <a href="<?= APP_URL ?>/profile" class="block px-3 py-2 text-sm text-slate-300 hover:text-white">Meu Perfil</a>
            <a href="<?= APP_URL ?>/logout"  class="block px-3 py-2 text-sm text-red-400 hover:text-red-300">Sair</a>
        </div>
    </div>
</nav>
<script>
document.getElementById('mobile-menu-btn')?.addEventListener('click', () => {
    document.getElementById('mobile-menu').classList.toggle('hidden');
});
// Fechar dropdown ao clicar fora
document.addEventListener('click', (e) => {
    document.querySelectorAll('[data-dropdown]').forEach(d => {
        if (!d.contains(e.target)) d.querySelector('.dropdown-menu')?.classList.add('hidden');
    });
});
</script>
