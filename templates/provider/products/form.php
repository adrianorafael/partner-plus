<?php
$isEdit    = isset($product);
$pageTitle = $isEdit ? 'Editar Produto/Serviço' : 'Novo Produto/Serviço';
$p         = $product ?? [];
?>
<?php include __DIR__ . '/../../layout/head.php'; ?>
<?php include __DIR__ . '/../../layout/nav.php'; ?>

<main class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <div class="mb-6">
        <a href="<?= APP_URL ?>/meus-produtos" class="text-sm text-slate-400 hover:text-plus-cyan flex items-center gap-1 mb-3">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Voltar para meus produtos
        </a>
        <h2 class="text-2xl font-bold font-title"><?= $isEdit ? 'Editar Produto/Serviço' : 'Novo Produto/Serviço' ?></h2>
    </div>

    <?php if (!empty($errors)): ?>
    <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl space-y-1">
        <?php foreach ($errors as $err): ?>
            <p class="text-red-700 text-sm">• <?= Helpers::e($err) ?></p>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <form method="post"
          action="<?= $isEdit ? APP_URL . '/editar-produto/' . ($p['id'] ?? '') : APP_URL . '/novo-produto' ?>"
          class="space-y-6">
        <?= CSRF::field() ?>

        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 space-y-4">

            <div>
                <label for="name" class="block text-sm font-medium text-slate-700 mb-1">
                    Nome <span class="text-red-500">*</span>
                </label>
                <input type="text" id="name" name="name" required
                    value="<?= Helpers::e($p['name'] ?? $_POST['name'] ?? '') ?>"
                    placeholder="Ex: ERP Gestão Total, Desenvolvimento de App"
                    class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-plus-cyan">
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">Tipo <span class="text-red-500">*</span></label>
                <div class="grid grid-cols-2 gap-3">
                    <?php foreach (['software' => 'Software', 'service' => 'Serviço'] as $val => $label): ?>
                    <?php $checked = ($p['type'] ?? $_POST['type'] ?? '') === $val; ?>
                    <label class="cursor-pointer">
                        <input type="radio" name="type" value="<?= $val ?>" class="sr-only peer" required
                            <?= $checked ? 'checked' : '' ?>>
                        <div class="border-2 border-slate-200 rounded-xl p-3 text-center transition-all
                                    peer-checked:border-plus-cyan peer-checked:bg-cyan-50">
                            <p class="font-semibold text-sm text-void"><?= $label ?></p>
                        </div>
                    </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <div>
                <label for="description" class="block text-sm font-medium text-slate-700 mb-1">Descrição</label>
                <textarea id="description" name="description" rows="4"
                    placeholder="Descreva brevemente o produto ou serviço oferecido..."
                    class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-plus-cyan resize-y"><?= Helpers::e($p['description'] ?? $_POST['description'] ?? '') ?></textarea>
            </div>

            <?php if ($isEdit): ?>
            <div class="flex items-center gap-3 pt-1">
                <input type="checkbox" id="active" name="active" value="1"
                    <?= ($p['active'] ?? 1) ? 'checked' : '' ?>
                    class="w-4 h-4 accent-[#00E5C8]">
                <label for="active" class="text-sm font-medium text-slate-700">Produto/serviço ativo (visível para clientes)</label>
            </div>
            <?php endif; ?>
        </div>

        <div class="flex items-center gap-3 justify-end">
            <a href="<?= APP_URL ?>/meus-produtos"
               class="px-6 py-2.5 rounded-xl text-sm font-medium text-slate-600 hover:bg-slate-100 transition-colors">
                Cancelar
            </a>
            <button type="submit"
                class="px-6 py-2.5 rounded-xl text-sm font-bold text-void bg-plus-cyan hover:opacity-90 transition-opacity">
                <?= $isEdit ? 'Salvar Alterações' : 'Cadastrar' ?>
            </button>
        </div>
    </form>

</main>
<?php include __DIR__ . '/../../layout/footer.php'; ?>
