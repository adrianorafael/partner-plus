<?php $pageTitle = 'Dashboard'; ?>
<?php include __DIR__ . '/../layout/head.php'; ?>
<?php include __DIR__ . '/../layout/nav.php'; ?>

<main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <div class="mb-8">
        <h2 class="text-2xl font-bold font-title">Olá, <?= Helpers::e($user['representative_name']) ?>!</h2>
        <p class="text-slate-500 text-sm mt-1"><?= Helpers::e($user['company_name']) ?> — <?= Helpers::e($user['role']) ?></p>
    </div>

    <?php include __DIR__ . '/../layout/flash.php'; ?>

    <!-- Cards de resumo -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
        <?php
        $cards = [
            ['label' => 'Oportunidades Ativas',   'value' => $stats['active']  ?? 0, 'color' => 'text-plus-cyan', 'bg' => 'bg-cyan-50'],
            ['label' => 'Oportunidades Encerradas','value' => $stats['closed']  ?? 0, 'color' => 'text-slate-500', 'bg' => 'bg-slate-50'],
            ['label' => 'Total Cadastradas',       'value' => $stats['total']   ?? 0, 'color' => 'text-void',      'bg' => 'bg-white'],
        ];
        ?>
        <?php foreach ($cards as $card): ?>
        <div class="<?= $card['bg'] ?> rounded-2xl border border-slate-100 p-6">
            <p class="text-sm text-slate-500 font-medium"><?= $card['label'] ?></p>
            <p class="text-3xl font-black font-title mt-1 <?= $card['color'] ?>"><?= $card['value'] ?></p>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Oportunidades recentes -->
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <h3 class="font-bold font-title">Oportunidades Recentes</h3>
            <a href="<?= APP_URL ?>/client/opportunities/create"
               class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-bold text-void bg-plus-cyan hover:opacity-90 transition-opacity">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Nova Oportunidade
            </a>
        </div>

        <?php if (empty($opportunities)): ?>
        <div class="px-6 py-12 text-center">
            <div class="w-16 h-16 rounded-full bg-slate-100 flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            <p class="text-slate-400 text-sm">Nenhuma oportunidade cadastrada ainda.</p>
            <a href="<?= APP_URL ?>/client/opportunities/create"
               class="inline-block mt-4 text-sm text-plus-cyan font-medium hover:underline">
                Cadastrar primeira oportunidade →
            </a>
        </div>
        <?php else: ?>
        <div class="divide-y divide-slate-50">
            <?php foreach ($opportunities as $opp): ?>
            <div class="px-6 py-4 flex items-center justify-between gap-4">
                <div class="min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                        <h4 class="font-semibold text-sm text-void truncate"><?= Helpers::e($opp['title']) ?></h4>
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                            <?= $opp['type'] === 'software' ? 'bg-blue-100 text-blue-700' : 'bg-purple-100 text-purple-700' ?>">
                            <?= $opp['type'] === 'software' ? 'Software' : 'Serviço' ?>
                        </span>
                        <?php if ($opp['target_provider']): ?>
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-amber-100 text-amber-700">
                            Direcionado
                        </span>
                        <?php endif; ?>
                    </div>
                    <p class="text-xs text-slate-400 mt-0.5">
                        Válido: <?= Helpers::formatDate($opp['start_date']) ?> — <?= Helpers::formatDate($opp['end_date']) ?>
                    </p>
                </div>
                <div class="flex items-center gap-3 flex-shrink-0">
                    <?= $opp['status'] === 'active'
                        ? '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">Ativa</span>'
                        : '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-500">Encerrada</span>'
                    ?>
                    <a href="<?= APP_URL ?>/client/opportunities/<?= $opp['id'] ?>/edit"
                       class="text-slate-400 hover:text-plus-cyan transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="px-6 py-3 border-t border-slate-100">
            <a href="<?= APP_URL ?>/client/opportunities"
               class="text-sm text-plus-cyan font-medium hover:underline">
                Ver todas as oportunidades →
            </a>
        </div>
        <?php endif; ?>
    </div>

</main>
<?php include __DIR__ . '/../layout/footer.php'; ?>
