<?php $pageTitle = 'Relatórios de Conexão'; ?>
<?php include __DIR__ . '/../layout/head.php'; ?>
<?php include __DIR__ . '/../layout/nav.php'; ?>

<main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <div class="mb-6">
        <h2 class="text-2xl font-bold font-title">Relatórios de Conexão</h2>
        <p class="text-slate-500 text-sm mt-1">Acompanhe quais fornecedores acessaram quais oportunidades</p>
    </div>

    <?php include __DIR__ . '/../layout/flash.php'; ?>

    <!-- Resumo -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <div class="bg-cyan-50 rounded-2xl border border-slate-100 p-5">
            <p class="text-xs text-slate-500 font-medium">Total de Conexões</p>
            <p class="text-3xl font-black font-title mt-1 text-plus-cyan"><?= $totals['connections'] ?? 0 ?></p>
        </div>
        <div class="bg-white rounded-2xl border border-slate-100 p-5">
            <p class="text-xs text-slate-500 font-medium">Fornecedores Ativos</p>
            <p class="text-3xl font-black font-title mt-1 text-void"><?= $totals['active_providers'] ?? 0 ?></p>
        </div>
        <div class="bg-slate-50 rounded-2xl border border-slate-100 p-5">
            <p class="text-xs text-slate-500 font-medium">Oportunidades com Leads</p>
            <p class="text-3xl font-black font-title mt-1 text-slate-500"><?= $totals['opps_with_leads'] ?? 0 ?></p>
        </div>
    </div>

    <!-- Tabela de leads -->
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100">
            <h3 class="font-bold font-title">Log de Acessos</h3>
        </div>

        <?php if (empty($leads)): ?>
        <div class="px-6 py-16 text-center">
            <p class="text-slate-400 text-sm">Nenhum acesso registrado ainda.</p>
        </div>
        <?php else: ?>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 border-b border-slate-100">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Fornecedor</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Oportunidade</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Empresa Cliente</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Tipo</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Acessado em</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    <?php foreach ($leads as $lead): ?>
                    <tr class="hover:bg-slate-50/50 transition-colors">
                        <td class="px-6 py-4">
                            <p class="font-medium text-void"><?= Helpers::e($lead['provider_name']) ?></p>
                            <p class="text-xs text-slate-400"><?= Helpers::e($lead['provider_company']) ?></p>
                        </td>
                        <td class="px-4 py-4 font-medium text-void text-sm"><?= Helpers::e($lead['opp_title']) ?></td>
                        <td class="px-4 py-4 text-xs text-slate-500"><?= Helpers::e($lead['client_company']) ?></td>
                        <td class="px-4 py-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                <?= $lead['opp_type'] === 'software' ? 'bg-blue-100 text-blue-700' : 'bg-purple-100 text-purple-700' ?>">
                                <?= $lead['opp_type'] === 'software' ? 'Software' : 'Serviço' ?>
                            </span>
                        </td>
                        <td class="px-4 py-4 text-xs text-slate-400"><?= Helpers::formatDate($lead['accessed_at']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>

</main>
<?php include __DIR__ . '/../layout/footer.php'; ?>
