<?php $pageTitle = 'Dashboard'; ?>
<?php include __DIR__ . '/../layout/head.php'; ?>
<?php include __DIR__ . '/../layout/nav.php'; ?>

<main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <div class="mb-8">
        <h2 class="text-2xl font-bold font-title">Olá, <?= Helpers::e($user['representative_name']) ?>!</h2>
        <p class="text-slate-500 text-sm mt-1"><?= Helpers::e($user['company_name']) ?> — <?= Helpers::e($user['role']) ?></p>
    </div>

    <?php include __DIR__ . '/../layout/flash.php'; ?>

    <!-- Resumo -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
        <div class="bg-cyan-50 rounded-2xl border border-slate-100 p-6">
            <p class="text-sm text-slate-500 font-medium">Novas Oportunidades</p>
            <p class="text-3xl font-black font-title mt-1 text-plus-cyan"><?= $stats['new'] ?? 0 ?></p>
        </div>
        <div class="bg-white rounded-2xl border border-slate-100 p-6">
            <p class="text-sm text-slate-500 font-medium">Oportunidades Vistas</p>
            <p class="text-3xl font-black font-title mt-1 text-void"><?= $stats['viewed'] ?? 0 ?></p>
        </div>
        <div class="bg-slate-50 rounded-2xl border border-slate-100 p-6">
            <p class="text-sm text-slate-500 font-medium">Total Disponíveis</p>
            <p class="text-3xl font-black font-title mt-1 text-slate-500"><?= $stats['total'] ?? 0 ?></p>
        </div>
    </div>

    <!-- Novas oportunidades -->
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <h3 class="font-bold font-title">Oportunidades Disponíveis</h3>
            <a href="<?= APP_URL ?>/oportunidades-disponiveis"
               class="text-sm text-plus-cyan font-medium hover:underline">Ver todas →</a>
        </div>

        <?php if (empty($opportunities)): ?>
        <div class="px-6 py-12 text-center">
            <div class="w-16 h-16 rounded-full bg-slate-100 flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>
            <p class="text-slate-400 text-sm">Nenhuma oportunidade disponível no momento.</p>
        </div>
        <?php else: ?>
        <div class="divide-y divide-slate-50">
            <?php foreach ($opportunities as $opp): ?>
            <div class="px-6 py-4 hover:bg-slate-50/50 transition-colors">
                <div class="flex items-start justify-between gap-4">
                    <div class="min-w-0">
                        <div class="flex items-center gap-2 flex-wrap mb-1">
                            <h4 class="font-semibold text-sm text-void"><?= Helpers::e($opp['title']) ?></h4>
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                                <?= $opp['type'] === 'software' ? 'bg-blue-100 text-blue-700' : 'bg-purple-100 text-purple-700' ?>">
                                <?= $opp['type'] === 'software' ? 'Software' : 'Serviço' ?>
                            </span>
                            <?php if ($opp['is_new'] ?? false): ?>
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-surge-lime text-void">NOVO</span>
                            <?php endif; ?>
                        </div>
                        <p class="text-xs text-slate-500 line-clamp-2"><?= Helpers::e(substr($opp['description'], 0, 120)) ?>...</p>
                        <p class="text-xs text-slate-400 mt-1">Empresa: <?= Helpers::e($opp['company_name']) ?> · Válido até <?= Helpers::formatDate($opp['end_date']) ?></p>
                    </div>
                    <a href="<?= APP_URL ?>/ver-oportunidade/<?= $opp['id'] ?>"
                       class="flex-shrink-0 px-4 py-2 rounded-xl text-xs font-bold text-void bg-plus-cyan hover:opacity-90 transition-opacity whitespace-nowrap">
                        Ver Detalhes
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

</main>
<?php include __DIR__ . '/../layout/footer.php'; ?>
