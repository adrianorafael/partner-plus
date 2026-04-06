<?php $pageTitle = 'Oportunidades'; ?>
<?php include __DIR__ . '/../../layout/head.php'; ?>
<?php include __DIR__ . '/../../layout/nav.php'; ?>

<main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <div class="mb-6">
        <h2 class="text-2xl font-bold font-title">Oportunidades Disponíveis</h2>
        <p class="text-slate-500 text-sm mt-1">Encontre oportunidades de negócio compatíveis com sua empresa</p>
    </div>

    <?php include __DIR__ . '/../../layout/flash.php'; ?>

    <!-- Filtros -->
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 mb-4">
        <form method="get" action="<?= APP_URL ?>/oportunidades-disponiveis" class="flex flex-wrap gap-3 items-end">
            <div>
                <label class="block text-xs text-slate-500 mb-1">Tipo</label>
                <select name="type" class="border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-plus-cyan">
                    <option value="">Todos</option>
                    <option value="software" <?= ($_GET['type'] ?? '') === 'software' ? 'selected' : '' ?>>Software</option>
                    <option value="service"  <?= ($_GET['type'] ?? '') === 'service'  ? 'selected' : '' ?>>Serviço</option>
                </select>
            </div>
            <div>
                <label class="block text-xs text-slate-500 mb-1">Buscar</label>
                <input type="text" name="q" value="<?= Helpers::e($_GET['q'] ?? '') ?>"
                    placeholder="Título ou descrição..."
                    class="border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-plus-cyan w-48">
            </div>
            <button type="submit"
                class="px-4 py-2 rounded-xl text-sm font-medium bg-plus-cyan text-void hover:opacity-90 transition-opacity">
                Filtrar
            </button>
            <?php if (!empty($_GET['type']) || !empty($_GET['q'])): ?>
            <a href="<?= APP_URL ?>/oportunidades-disponiveis" class="text-xs text-slate-400 hover:text-slate-600 self-end mb-2">Limpar</a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Listagem -->
    <?php if (empty($opportunities)): ?>
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm px-6 py-16 text-center">
        <p class="text-slate-400 text-sm">Nenhuma oportunidade disponível no momento.</p>
    </div>
    <?php else: ?>
    <div class="grid grid-cols-1 gap-4">
        <?php foreach ($opportunities as $opp): ?>
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 hover:border-plus-cyan/30 transition-colors">
            <div class="flex items-start justify-between gap-4">
                <div class="min-w-0 flex-1">
                    <div class="flex items-center gap-2 flex-wrap mb-2">
                        <h3 class="font-bold text-void"><?= Helpers::e($opp['title']) ?></h3>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                            <?= $opp['type'] === 'software' ? 'bg-blue-100 text-blue-700' : 'bg-purple-100 text-purple-700' ?>">
                            <?= $opp['type'] === 'software' ? 'Software' : 'Serviço' ?>
                        </span>
                        <?php if (!$opp['viewed']): ?>
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold" style="background:#B8FF45;color:#06090F;">NOVO</span>
                        <?php endif; ?>
                    </div>
                    <p class="text-sm text-slate-500 mb-3 line-clamp-3"><?= Helpers::e($opp['description']) ?></p>
                    <div class="flex flex-wrap gap-4 text-xs text-slate-400">
                        <span>🏢 <?= Helpers::e($opp['company_name']) ?></span>
                        <span>📅 Válido: <?= Helpers::formatDate($opp['start_date']) ?> — <?= Helpers::formatDate($opp['end_date']) ?></span>
                    </div>
                </div>
                <a href="<?= APP_URL ?>/ver-oportunidade/<?= $opp['id'] ?>"
                   class="flex-shrink-0 px-5 py-2.5 rounded-xl text-sm font-bold text-void bg-plus-cyan hover:opacity-90 transition-opacity">
                    Ver Detalhes
                </a>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

</main>
<?php include __DIR__ . '/../../layout/footer.php'; ?>
