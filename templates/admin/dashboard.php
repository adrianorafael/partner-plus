<?php $pageTitle = 'Painel Admin'; ?>
<?php include __DIR__ . '/../layout/head.php'; ?>
<?php include __DIR__ . '/../layout/nav.php'; ?>

<main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <div class="mb-8">
        <h2 class="text-2xl font-bold font-title">Painel Administrativo</h2>
        <p class="text-slate-500 text-sm mt-1">Visão geral da plataforma Partner Plus</p>
    </div>

    <?php include __DIR__ . '/../layout/flash.php'; ?>

    <!-- Cards de resumo -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-8">
        <?php
        $cards = [
            ['label' => 'Usuários Ativos',    'value' => $stats['active_users']    ?? 0, 'color' => 'text-plus-cyan',   'bg' => 'bg-cyan-50'],
            ['label' => 'Pendentes Aprovação','value' => $stats['pending_users']   ?? 0, 'color' => 'text-orange-500',  'bg' => 'bg-orange-50'],
            ['label' => 'Oportunidades Ativas','value'=> $stats['active_opps']     ?? 0, 'color' => 'text-void',        'bg' => 'bg-white'],
            ['label' => 'Conexões Realizadas', 'value'=> $stats['total_leads']     ?? 0, 'color' => 'text-purple-600',  'bg' => 'bg-purple-50'],
        ];
        ?>
        <?php foreach ($cards as $card): ?>
        <div class="<?= $card['bg'] ?> rounded-2xl border border-slate-100 p-5">
            <p class="text-xs text-slate-500 font-medium"><?= $card['label'] ?></p>
            <p class="text-3xl font-black font-title mt-1 <?= $card['color'] ?>"><?= $card['value'] ?></p>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- Cadastros pendentes -->
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm">
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                <h3 class="font-bold font-title">Cadastros Pendentes</h3>
                <?php if (!empty($pendingUsers)): ?>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-700">
                    <?= count($pendingUsers) ?> aguardando
                </span>
                <?php endif; ?>
            </div>

            <?php if (empty($pendingUsers)): ?>
            <div class="px-6 py-8 text-center">
                <p class="text-slate-400 text-sm">Nenhum cadastro pendente.</p>
            </div>
            <?php else: ?>
            <div class="divide-y divide-slate-50">
                <?php foreach (array_slice($pendingUsers, 0, 5) as $u): ?>
                <div class="px-6 py-4 flex items-center justify-between gap-4">
                    <div class="min-w-0">
                        <div class="flex items-center gap-2">
                            <p class="font-medium text-sm text-void truncate"><?= Helpers::e($u['representative_name']) ?></p>
                            <?php if ($u['cnpj_duplicate']): ?>
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-yellow-100 text-yellow-700" title="CNPJ já existe no sistema">
                                ⚠ CNPJ duplicado
                            </span>
                            <?php endif; ?>
                        </div>
                        <p class="text-xs text-slate-400"><?= Helpers::e($u['company_name']) ?> · <?= Helpers::e($u['type'] === 'client' ? 'Cliente' : 'Fornecedor') ?></p>
                        <p class="text-xs text-slate-400"><?= Helpers::e($u['email']) ?></p>
                    </div>
                    <div class="flex items-center gap-2 flex-shrink-0">
                        <form method="post" action="<?= APP_URL ?>/aprovar-usuario/<?= $u['id'] ?>">
                            <?= CSRF::field() ?>
                            <button type="submit"
                                class="px-3 py-1.5 rounded-lg text-xs font-bold text-void bg-plus-cyan hover:opacity-90 transition-opacity">
                                Aprovar
                            </button>
                        </form>
                        <form method="post" action="<?= APP_URL ?>/rejeitar-usuario/<?= $u['id'] ?>"
                              onsubmit="return confirm('Rejeitar e remover este cadastro?')">
                            <?= CSRF::field() ?>
                            <button type="submit"
                                class="px-3 py-1.5 rounded-lg text-xs font-medium text-slate-500 hover:bg-red-50 hover:text-red-600 transition-colors">
                                Rejeitar
                            </button>
                        </form>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <div class="px-6 py-3 border-t border-slate-100">
                <a href="<?= APP_URL ?>/gerenciar-usuarios" class="text-sm text-plus-cyan font-medium hover:underline">
                    Ver todos os usuários →
                </a>
            </div>
            <?php endif; ?>
        </div>

        <!-- Oportunidades recentes -->
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm">
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                <h3 class="font-bold font-title">Oportunidades Recentes</h3>
                <a href="<?= APP_URL ?>/gerenciar-oportunidades" class="text-sm text-plus-cyan font-medium hover:underline">Ver todas →</a>
            </div>

            <?php if (empty($recentOpps)): ?>
            <div class="px-6 py-8 text-center">
                <p class="text-slate-400 text-sm">Nenhuma oportunidade cadastrada ainda.</p>
            </div>
            <?php else: ?>
            <div class="divide-y divide-slate-50">
                <?php foreach ($recentOpps as $opp): ?>
                <div class="px-6 py-4">
                    <div class="flex items-center gap-2 flex-wrap mb-0.5">
                        <p class="font-medium text-sm text-void"><?= Helpers::e($opp['title']) ?></p>
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                            <?= $opp['type'] === 'software' ? 'bg-blue-100 text-blue-700' : 'bg-purple-100 text-purple-700' ?>">
                            <?= $opp['type'] === 'software' ? 'Software' : 'Serviço' ?>
                        </span>
                    </div>
                    <p class="text-xs text-slate-400"><?= Helpers::e($opp['company_name']) ?> · <?= Helpers::formatDate($opp['created_at']) ?></p>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>

    </div>

</main>
<?php include __DIR__ . '/../layout/footer.php'; ?>
