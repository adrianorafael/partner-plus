<?php $pageTitle = 'Minhas Oportunidades'; ?>
<?php include __DIR__ . '/../../layout/head.php'; ?>
<?php include __DIR__ . '/../../layout/nav.php'; ?>

<main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-2xl font-bold font-title">Minhas Oportunidades</h2>
            <p class="text-slate-500 text-sm mt-1">Gerencie todas as oportunidades da sua empresa</p>
        </div>
        <a href="<?= APP_URL ?>/client/opportunities/create"
           class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-bold text-void bg-plus-cyan hover:opacity-90 transition-opacity">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Nova Oportunidade
        </a>
    </div>

    <?php include __DIR__ . '/../../layout/flash.php'; ?>

    <!-- Filtros -->
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 mb-4">
        <form method="get" action="<?= APP_URL ?>/client/opportunities" class="flex flex-wrap gap-3 items-end">
            <div>
                <label class="block text-xs text-slate-500 mb-1">Status</label>
                <select name="status" class="border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-plus-cyan">
                    <option value="">Todos</option>
                    <option value="active"  <?= ($_GET['status'] ?? '') === 'active'  ? 'selected' : '' ?>>Ativas</option>
                    <option value="closed"  <?= ($_GET['status'] ?? '') === 'closed'  ? 'selected' : '' ?>>Encerradas</option>
                </select>
            </div>
            <div>
                <label class="block text-xs text-slate-500 mb-1">Tipo</label>
                <select name="type" class="border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-plus-cyan">
                    <option value="">Todos</option>
                    <option value="software" <?= ($_GET['type'] ?? '') === 'software' ? 'selected' : '' ?>>Software</option>
                    <option value="service"  <?= ($_GET['type'] ?? '') === 'service'  ? 'selected' : '' ?>>Serviço</option>
                </select>
            </div>
            <button type="submit"
                class="px-4 py-2 rounded-xl text-sm font-medium bg-slate-100 hover:bg-slate-200 transition-colors">
                Filtrar
            </button>
            <?php if (!empty($_GET['status']) || !empty($_GET['type'])): ?>
            <a href="<?= APP_URL ?>/client/opportunities" class="text-xs text-slate-400 hover:text-slate-600 self-end mb-2">Limpar filtros</a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Tabela -->
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <?php if (empty($opportunities)): ?>
        <div class="px-6 py-16 text-center">
            <p class="text-slate-400 text-sm">Nenhuma oportunidade encontrada.</p>
            <a href="<?= APP_URL ?>/client/opportunities/create"
               class="inline-block mt-4 text-sm text-plus-cyan font-medium hover:underline">
                Cadastrar primeira oportunidade →
            </a>
        </div>
        <?php else: ?>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 border-b border-slate-100">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Oportunidade</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Tipo</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Validade</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Direcionamento</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    <?php foreach ($opportunities as $opp): ?>
                    <tr class="hover:bg-slate-50/50 transition-colors">
                        <td class="px-6 py-4">
                            <p class="font-medium text-void"><?= Helpers::e($opp['title']) ?></p>
                            <p class="text-xs text-slate-400 mt-0.5">Criada em <?= Helpers::formatDate($opp['created_at']) ?></p>
                        </td>
                        <td class="px-4 py-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                <?= $opp['type'] === 'software' ? 'bg-blue-100 text-blue-700' : 'bg-purple-100 text-purple-700' ?>">
                                <?= $opp['type'] === 'software' ? 'Software' : 'Serviço' ?>
                            </span>
                        </td>
                        <td class="px-4 py-4 text-slate-600 text-xs">
                            <?= Helpers::formatDate($opp['start_date']) ?><br>
                            <span class="text-slate-400">até</span> <?= Helpers::formatDate($opp['end_date']) ?>
                        </td>
                        <td class="px-4 py-4">
                            <?= $opp['status'] === 'active'
                                ? '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">Ativa</span>'
                                : '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-500">Encerrada</span>'
                            ?>
                        </td>
                        <td class="px-4 py-4 text-xs text-slate-500">
                            <?= $opp['target_provider']
                                ? '<span class="text-amber-600 font-medium">Específico</span>: ' . Helpers::e($opp['target_provider'])
                                : '<span class="text-slate-400">Aberto</span>'
                            ?>
                        </td>
                        <td class="px-4 py-4">
                            <div class="flex items-center gap-2 justify-end">
                                <a href="<?= APP_URL ?>/client/opportunities/<?= $opp['id'] ?>/edit"
                                   class="p-1.5 rounded-lg text-slate-400 hover:text-plus-cyan hover:bg-cyan-50 transition-colors" title="Editar">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </a>
                                <?php if ($opp['status'] === 'active'): ?>
                                <form method="post" action="<?= APP_URL ?>/client/opportunities/<?= $opp['id'] ?>/close"
                                      onsubmit="return confirm('Encerrar esta oportunidade?')">
                                    <?= CSRF::field() ?>
                                    <button type="submit"
                                        class="p-1.5 rounded-lg text-slate-400 hover:text-red-500 hover:bg-red-50 transition-colors" title="Encerrar">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    </button>
                                </form>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>

</main>
<?php include __DIR__ . '/../../layout/footer.php'; ?>
