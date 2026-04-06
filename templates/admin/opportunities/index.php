<?php $pageTitle = 'Oportunidades — Admin'; ?>
<?php include __DIR__ . '/../../layout/head.php'; ?>
<?php include __DIR__ . '/../../layout/nav.php'; ?>

<main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <div class="mb-6">
        <h2 class="text-2xl font-bold font-title">Todas as Oportunidades</h2>
        <p class="text-slate-500 text-sm mt-1">Visualize todas as oportunidades cadastradas na plataforma</p>
    </div>

    <?php include __DIR__ . '/../../layout/flash.php'; ?>

    <!-- Filtros -->
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 mb-4">
        <form method="get" action="<?= APP_URL ?>/admin/opportunities" class="flex flex-wrap gap-3 items-end">
            <div>
                <label class="block text-xs text-slate-500 mb-1">Status</label>
                <select name="status" class="border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-plus-cyan">
                    <option value="">Todos</option>
                    <option value="active" <?= ($_GET['status'] ?? '') === 'active' ? 'selected' : '' ?>>Ativas</option>
                    <option value="closed" <?= ($_GET['status'] ?? '') === 'closed' ? 'selected' : '' ?>>Encerradas</option>
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
                class="px-4 py-2 rounded-xl text-sm font-medium bg-plus-cyan text-void hover:opacity-90 transition-opacity">
                Filtrar
            </button>
        </form>
    </div>

    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <?php if (empty($opportunities)): ?>
        <div class="px-6 py-16 text-center">
            <p class="text-slate-400 text-sm">Nenhuma oportunidade encontrada.</p>
        </div>
        <?php else: ?>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 border-b border-slate-100">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Oportunidade</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Empresa</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Tipo</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Validade</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Leads</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    <?php foreach ($opportunities as $opp): ?>
                    <tr class="hover:bg-slate-50/50 transition-colors">
                        <td class="px-6 py-4">
                            <p class="font-medium text-void"><?= Helpers::e($opp['title']) ?></p>
                            <?php if ($opp['target_provider']): ?>
                            <p class="text-xs text-amber-600 mt-0.5">→ <?= Helpers::e($opp['target_provider']) ?></p>
                            <?php endif; ?>
                        </td>
                        <td class="px-4 py-4 text-slate-500 text-xs"><?= Helpers::e($opp['company_name']) ?></td>
                        <td class="px-4 py-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                <?= $opp['type'] === 'software' ? 'bg-blue-100 text-blue-700' : 'bg-purple-100 text-purple-700' ?>">
                                <?= $opp['type'] === 'software' ? 'Software' : 'Serviço' ?>
                            </span>
                        </td>
                        <td class="px-4 py-4 text-xs text-slate-500">
                            <?= Helpers::formatDate($opp['start_date']) ?> —<br>
                            <?= Helpers::formatDate($opp['end_date']) ?>
                        </td>
                        <td class="px-4 py-4">
                            <span class="font-semibold text-void"><?= (int)$opp['leads_count'] ?></span>
                            <span class="text-xs text-slate-400"> fornecedores</span>
                        </td>
                        <td class="px-4 py-4">
                            <?= $opp['status'] === 'active'
                                ? '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">Ativa</span>'
                                : '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-500">Encerrada</span>'
                            ?>
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
