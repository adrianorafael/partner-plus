<?php $pageTitle = 'Meus Produtos e Serviços'; ?>
<?php include __DIR__ . '/../../layout/head.php'; ?>
<?php include __DIR__ . '/../../layout/nav.php'; ?>

<main class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <div class="mb-6 flex items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold font-title">Meus Produtos e Serviços</h2>
            <p class="text-slate-500 text-sm mt-1">Cadastre os softwares e serviços que sua empresa oferece.</p>
        </div>
        <a href="<?= APP_URL ?>/novo-produto"
           class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-bold text-void bg-plus-cyan hover:opacity-90 transition-opacity flex-shrink-0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Novo Produto/Serviço
        </a>
    </div>

    <?php include __DIR__ . '/../../layout/flash.php'; ?>

    <?php if (empty($products)): ?>
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm px-6 py-16 text-center">
        <div class="w-16 h-16 rounded-full bg-slate-100 flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10"/>
            </svg>
        </div>
        <p class="text-slate-500 text-sm font-medium mb-1">Nenhum produto ou serviço cadastrado.</p>
        <p class="text-slate-400 text-xs">Adicione seus softwares e serviços para que clientes possam selecioná-los ao criar oportunidades.</p>
    </div>
    <?php else: ?>
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 border-b border-slate-100">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Nome</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Tipo</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Cadastrado</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    <?php foreach ($products as $p): ?>
                    <tr class="hover:bg-slate-50/50 transition-colors <?= !$p['active'] ? 'opacity-50' : '' ?>">
                        <td class="px-6 py-4">
                            <p class="font-medium text-void"><?= Helpers::e($p['name']) ?></p>
                            <?php if ($p['description']): ?>
                            <p class="text-xs text-slate-400 mt-0.5 line-clamp-2"><?= Helpers::e($p['description']) ?></p>
                            <?php endif; ?>
                        </td>
                        <td class="px-4 py-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                <?= $p['type'] === 'software' ? 'bg-blue-100 text-blue-700' : 'bg-purple-100 text-purple-700' ?>">
                                <?= $p['type'] === 'software' ? 'Software' : 'Serviço' ?>
                            </span>
                        </td>
                        <td class="px-4 py-4">
                            <?php if ($p['active']): ?>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">Ativo</span>
                            <?php else: ?>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-500">Inativo</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-4 py-4 text-xs text-slate-400"><?= Helpers::formatDate($p['created_at']) ?></td>
                        <td class="px-4 py-4">
                            <div class="flex items-center gap-2 justify-end">
                                <a href="<?= APP_URL ?>/editar-produto/<?= $p['id'] ?>"
                                   class="px-3 py-1.5 rounded-lg text-xs font-medium text-slate-600 hover:bg-slate-100 transition-colors">
                                    Editar
                                </a>
                                <form method="post" action="<?= APP_URL ?>/remover-produto/<?= $p['id'] ?>"
                                      onsubmit="return confirm('Remover este produto/serviço?')">
                                    <?= CSRF::field() ?>
                                    <button type="submit"
                                        class="px-3 py-1.5 rounded-lg text-xs font-medium text-red-500 hover:bg-red-50 transition-colors">
                                        Remover
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>

</main>
<?php include __DIR__ . '/../../layout/footer.php'; ?>
