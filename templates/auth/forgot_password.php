<?php $pageTitle = 'Recuperar Senha'; ?>
<?php include __DIR__ . '/../layout/head.php'; ?>

<div class="min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md">

        <div class="text-center mb-8">
            <h1 class="text-3xl font-black font-title" style="color:#06090F;">
                Partner <span class="text-plus-cyan">Plus</span>
            </h1>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-8">

            <?php if ($sent ?? false): ?>
                <div class="text-center">
                    <div class="w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4 bg-cyan-50">
                        <svg class="w-8 h-8 text-plus-cyan" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <h2 class="text-xl font-bold mb-2 font-title">Verifique seu e-mail</h2>
                    <p class="text-slate-500 text-sm">Se o e-mail informado estiver cadastrado, você receberá um link de redefinição em instantes. O link é válido por 1 hora.</p>
                    <a href="<?= APP_URL ?>/login" class="inline-block mt-6 text-sm text-plus-cyan hover:underline">← Voltar ao login</a>
                </div>
            <?php else: ?>
                <h2 class="text-xl font-bold mb-1 font-title">Recuperar senha</h2>
                <p class="text-slate-500 text-sm mb-6">Digite seu e-mail corporativo para receber o link de redefinição.</p>

                <?php if (!empty($error)): ?>
                <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-xl">
                    <p class="text-red-700 text-sm"><?= Helpers::e($error) ?></p>
                </div>
                <?php endif; ?>

                <form method="post" action="<?= APP_URL ?>/forgot-password" class="space-y-4">
                    <?= CSRF::field() ?>
                    <div>
                        <label for="email" class="block text-sm font-medium text-slate-700 mb-1">E-mail corporativo</label>
                        <input type="email" id="email" name="email" required
                            value="<?= Helpers::e($_POST['email'] ?? '') ?>"
                            placeholder="voce@empresa.com.br"
                            class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-plus-cyan">
                    </div>
                    <button type="submit"
                        class="w-full py-3 rounded-xl font-bold text-sm text-void bg-plus-cyan hover:opacity-90 transition-opacity">
                        Enviar Link de Redefinição
                    </button>
                </form>

                <p class="text-center text-sm text-slate-500 mt-6">
                    <a href="<?= APP_URL ?>/login" class="text-plus-cyan hover:underline">← Voltar ao login</a>
                </p>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../layout/footer.php'; ?>
