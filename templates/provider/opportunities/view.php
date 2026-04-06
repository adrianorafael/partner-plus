<?php $pageTitle = Helpers::e($opportunity['title']); ?>
<?php include __DIR__ . '/../../layout/head.php'; ?>
<?php include __DIR__ . '/../../layout/nav.php'; ?>

<main class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <a href="<?= APP_URL ?>/parceiro/oportunidades" class="text-sm text-slate-400 hover:text-plus-cyan flex items-center gap-1 mb-6">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        Voltar para oportunidades
    </a>

    <?php $opp = $opportunity; ?>

    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-8 mb-4">
        <!-- Header da oportunidade -->
        <div class="flex items-start justify-between gap-4 mb-6">
            <div>
                <div class="flex items-center gap-2 flex-wrap mb-1">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                        <?= $opp['type'] === 'software' ? 'bg-blue-100 text-blue-700' : 'bg-purple-100 text-purple-700' ?>">
                        <?= $opp['type'] === 'software' ? 'Software' : 'Serviço' ?>
                    </span>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">
                        Ativa
                    </span>
                </div>
                <h2 class="text-2xl font-bold font-title text-void"><?= Helpers::e($opp['title']) ?></h2>
                <p class="text-slate-400 text-sm mt-1">Publicada por <?= Helpers::e($opp['company_name']) ?></p>
            </div>
        </div>

        <!-- Descrição -->
        <div class="mb-6">
            <h4 class="text-xs font-semibold text-slate-400 uppercase tracking-wide mb-2">Descrição</h4>
            <p class="text-slate-700 text-sm leading-relaxed whitespace-pre-line"><?= Helpers::e($opp['description']) ?></p>
        </div>

        <!-- Validade -->
        <div class="grid grid-cols-2 gap-4 mb-6">
            <div class="bg-slate-50 rounded-xl p-4">
                <p class="text-xs text-slate-400 font-medium mb-1">Data Inicial</p>
                <p class="text-sm font-semibold text-void"><?= Helpers::formatDate($opp['start_date']) ?></p>
            </div>
            <div class="bg-slate-50 rounded-xl p-4">
                <p class="text-xs text-slate-400 font-medium mb-1">Data Final</p>
                <p class="text-sm font-semibold text-void"><?= Helpers::formatDate($opp['end_date']) ?></p>
            </div>
        </div>

        <hr class="border-slate-100 mb-6">

        <!-- Dados de contato -->
        <div>
            <h4 class="text-xs font-semibold text-slate-400 uppercase tracking-wide mb-3">Responsável pelo Contato</h4>

            <?php if ($opp['contact_person_type'] === 'self'): ?>
            <!-- Contato é o próprio usuário -->
            <div class="bg-cyan-50 rounded-xl p-4 space-y-2">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-plus-cyan flex items-center justify-center text-void font-bold text-sm flex-shrink-0">
                        <?= strtoupper(substr($opp['representative_name'], 0, 1)) ?>
                    </div>
                    <div>
                        <p class="font-semibold text-sm text-void"><?= Helpers::e($opp['representative_name']) ?></p>
                        <p class="text-xs text-slate-500"><?= Helpers::e($opp['role']) ?></p>
                    </div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 mt-3">
                    <a href="mailto:<?= Helpers::e($opp['email']) ?>"
                       class="flex items-center gap-2 text-sm text-plus-cyan hover:underline font-medium">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                        <?= Helpers::e($opp['email']) ?>
                    </a>
                    <?php if (!empty($opp['phone'])): ?>
                    <a href="tel:<?= preg_replace('/\D/', '', $opp['phone']) ?>"
                       class="flex items-center gap-2 text-sm text-slate-600 hover:text-plus-cyan font-medium">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                        </svg>
                        <?= Helpers::e(Helpers::formatPhone($opp['phone'])) ?>
                    </a>
                    <?php endif; ?>
                </div>
            </div>
            <?php else: ?>
            <!-- Contato é outra pessoa -->
            <div class="bg-cyan-50 rounded-xl p-4 space-y-2">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-plus-cyan flex items-center justify-center text-void font-bold text-sm flex-shrink-0">
                        <?= strtoupper(substr($opp['contact_name'] ?? 'C', 0, 1)) ?>
                    </div>
                    <div>
                        <p class="font-semibold text-sm text-void"><?= Helpers::e($opp['contact_name']) ?></p>
                        <?php if ($opp['contact_role']): ?>
                        <p class="text-xs text-slate-500"><?= Helpers::e($opp['contact_role']) ?></p>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 mt-3">
                    <?php if ($opp['contact_email']): ?>
                    <a href="mailto:<?= Helpers::e($opp['contact_email']) ?>"
                       class="flex items-center gap-2 text-sm text-plus-cyan hover:underline font-medium">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                        <?= Helpers::e($opp['contact_email']) ?>
                    </a>
                    <?php endif; ?>
                    <?php if ($opp['contact_phone']): ?>
                    <a href="tel:<?= preg_replace('/\D/', '', $opp['contact_phone']) ?>"
                       class="flex items-center gap-2 text-sm text-slate-600 hover:text-plus-cyan font-medium">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                        </svg>
                        <?= Helpers::e(Helpers::formatPhone($opp['contact_phone'])) ?>
                    </a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

</main>
<?php include __DIR__ . '/../../layout/footer.php'; ?>
