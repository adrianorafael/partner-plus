<?php $pageTitle = 'Meu Perfil'; ?>
<?php include __DIR__ . '/layout/head.php'; ?>
<?php include __DIR__ . '/layout/nav.php'; ?>

<main class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <div class="mb-6">
        <h2 class="text-2xl font-bold font-title">Meu Perfil</h2>
        <p class="text-slate-500 text-sm mt-1">Atualize seus dados cadastrais</p>
    </div>

    <?php include __DIR__ . '/layout/flash.php'; ?>

    <?php if (!empty($errors)): ?>
    <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl space-y-1">
        <?php foreach ($errors as $err): ?>
            <p class="text-red-700 text-sm">• <?= Helpers::e($err) ?></p>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-8">

        <!-- Avatar -->
        <div class="flex items-center gap-4 mb-8 pb-6 border-b border-slate-100">
            <div class="w-16 h-16 rounded-full bg-plus-cyan flex items-center justify-center text-void font-black text-xl">
                <?= strtoupper(substr($user['representative_name'], 0, 1)) ?>
            </div>
            <div>
                <p class="font-bold text-void"><?= Helpers::e($user['representative_name']) ?></p>
                <p class="text-slate-400 text-sm"><?= Helpers::e($user['company_name']) ?></p>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium mt-1
                    <?= $user['type'] === 'client' ? 'bg-blue-100 text-blue-700' : ($user['type'] === 'provider' ? 'bg-green-100 text-green-700' : 'bg-slate-100 text-slate-600') ?>">
                    <?= $user['type'] === 'client' ? 'Cliente' : ($user['type'] === 'provider' ? 'Fornecedor' : 'Administrador') ?>
                </span>
            </div>
        </div>

        <form method="post" action="<?= APP_URL ?>/profile" class="space-y-4">
            <?= CSRF::field() ?>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Nome completo <span class="text-red-500">*</span></label>
                    <input type="text" name="representative_name" required
                        value="<?= Helpers::e($user['representative_name']) ?>"
                        class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-plus-cyan">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Cargo</label>
                    <input type="text" name="role"
                        value="<?= Helpers::e($user['role']) ?>"
                        class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-plus-cyan">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Empresa</label>
                <input type="text" name="company_name" required
                    value="<?= Helpers::e($user['company_name']) ?>"
                    class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-plus-cyan">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">CNPJ</label>
                    <input type="text" value="<?= Helpers::e(Helpers::formatCNPJ($user['cnpj'])) ?>" disabled
                        class="w-full border border-slate-100 rounded-xl px-4 py-2.5 text-sm bg-slate-50 text-slate-400 cursor-not-allowed">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Telefone</label>
                    <input type="tel" name="phone"
                        value="<?= Helpers::e(Helpers::formatPhone($user['phone'])) ?>"
                        class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-plus-cyan">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">E-mail</label>
                <input type="email" value="<?= Helpers::e($user['email']) ?>" disabled
                    class="w-full border border-slate-100 rounded-xl px-4 py-2.5 text-sm bg-slate-50 text-slate-400 cursor-not-allowed">
                <p class="text-xs text-slate-400 mt-1">O e-mail não pode ser alterado.</p>
            </div>

            <hr class="border-slate-100">

            <div>
                <h4 class="font-semibold text-sm text-void mb-3">Alterar Senha</h4>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Nova senha</label>
                        <input type="password" name="new_password" minlength="8"
                            placeholder="Deixe em branco para não alterar"
                            class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-plus-cyan">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Confirmar nova senha</label>
                        <input type="password" name="new_password_confirm"
                            class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-plus-cyan">
                    </div>
                </div>
            </div>

            <div class="flex justify-end pt-2">
                <button type="submit"
                    class="px-8 py-2.5 rounded-xl font-bold text-sm text-void bg-plus-cyan hover:opacity-90 transition-opacity">
                    Salvar Alterações
                </button>
            </div>
        </form>
    </div>

</main>
<?php include __DIR__ . '/layout/footer.php'; ?>
