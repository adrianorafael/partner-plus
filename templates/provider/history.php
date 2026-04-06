<?php $pageTitle = 'Histórico'; ?>
<?php include __DIR__ . '/../layout/head.php'; ?>
<?php include __DIR__ . '/../layout/nav.php'; ?>

<main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <div class="mb-6">
        <h2 class="text-2xl font-bold font-title">Histórico de Oportunidades</h2>
        <p class="text-slate-500 text-sm mt-1">Oportunidades que você visualizou</p>
    </div>

    <?php include __DIR__ . '/../layout/flash.php'; ?>

    <?php if (empty($history)): ?>
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm px-6 py-16 text-center">
        <p class="text-slate-400 text-sm">Você ainda não visualizou nenhuma oportunidade.</p>
        <a href="<?= APP_URL ?>/provider/opportunities" class="inline-block mt-4 text-sm text-plus-cyan font-medium hover:underline">
            Ver oportunidades disponíveis →
        </a>
    </div>
    <?php else: ?>
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 border-b border-slate-100">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Oportunidade</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Empresa</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Tipo</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Visualizado em</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Status</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    <?php foreach ($history as $item): ?>
                    <tr class="hover:bg-slate-50/50 transition-colors">
                        <td class="px-6 py-4 font-medium text-void"><?= Helpers::e($item['title']) ?></td>
                        <td class="px-4 py-4 text-slate-500 text-xs"><?= Helpers::e($item['company_name']) ?></td>
                        <td class="px-4 py-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                <?= $item['type'] === 'software' ? 'bg-blue-100 text-blue-700' : 'bg-purple-100 text-purple-700' ?>">
                                <?= $item['type'] === 'software' ? 'Software' : 'Serviço' ?>
                            </span>
                        </td>
                        <td class="px-4 py-4 text-slate-400 text-xs"><?= Helpers::formatDate($item['accessed_at']) ?></td>
                        <td class="px-4 py-4">
                            <?= $item['status'] === 'active' && strtotime($item['end_date']) >= time()
                                ? '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">Ativa</span>'
                                : '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-400">Encerrada</span>'
                            ?>
                        </td>
                        <td class="px-4 py-4">
                            <?php if ($item['status'] === 'active' && strtotime($item['end_date']) >= time()): ?>
                            <a href="<?= APP_URL ?>/provider/opportunities/<?= $item['opportunity_id'] ?>"
                               class="text-xs text-plus-cyan hover:underline font-medium">Ver detalhes</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>

</main>
<?php include __DIR__ . '/../layout/footer.php'; ?>
