<?php $pageTitle = 'Redefinir Senha'; ?>
<?php include __DIR__ . '/../layout/head.php'; ?>

<div class="min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md">

        <div class="text-center mb-8">
            <h1 class="text-3xl font-black font-title" style="color:#06090F;">
                Partner <span class="text-plus-cyan">Plus</span>
            </h1>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-8">

            <?php if ($invalid ?? false): ?>
                <div class="text-center">
                    <div class="w-16 h-16 rounded-full bg-red-100 flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </div>
                    <h2 class="text-xl font-bold mb-2 font-title">Link inválido</h2>
                    <p class="text-slate-500 text-sm mb-6">Este link de redefinição é inválido ou já expirou. Links são válidos por 1 hora e só podem ser usados uma vez.</p>
                    <a href="<?= APP_URL ?>/recuperar-senha"
                       class="inline-block px-8 py-3 rounded-xl font-bold text-sm text-void bg-plus-cyan hover:opacity-90 transition-opacity">
                        Solicitar novo link
                    </a>
                </div>
            <?php else: ?>
                <h2 class="text-xl font-bold mb-1 font-title">Nova senha</h2>
                <p class="text-slate-500 text-sm mb-6">Defina uma nova senha segura para sua conta.</p>

                <?php if (!empty($errors)): ?>
                <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-xl space-y-1">
                    <?php foreach ($errors as $err): ?>
                        <p class="text-red-700 text-sm">• <?= Helpers::e($err) ?></p>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

                <form method="post" action="<?= APP_URL ?>/redefinir-senha" class="space-y-4">
                    <?= CSRF::field() ?>
                    <input type="hidden" name="token" value="<?= Helpers::e($token ?? '') ?>">
                    <div>
                        <label for="password" class="block text-sm font-medium text-slate-700 mb-1">Nova senha <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <input type="password" id="password" name="password" required minlength="8"
                                class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-plus-cyan pr-11">
                            <button type="button" onclick="togglePassword('password')" tabindex="-1"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </button>
                        </div>
                        <p class="text-xs text-slate-400 mt-1">Mínimo 8 caracteres</p>
                    </div>
                    <div>
                        <label for="password_confirm" class="block text-sm font-medium text-slate-700 mb-1">Confirmar nova senha <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <input type="password" id="password_confirm" name="password_confirm" required
                                class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-plus-cyan pr-11">
                            <button type="button" onclick="togglePassword('password_confirm')" tabindex="-1"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                    <button type="submit"
                        class="w-full py-3 rounded-xl font-bold text-sm text-void bg-plus-cyan hover:opacity-90 transition-opacity">
                        Redefinir Senha
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>
<script>
function togglePassword(id) {
    const input = document.getElementById(id);
    input.type = input.type === 'password' ? 'text' : 'password';
}
</script>
<?php include __DIR__ . '/../layout/footer.php'; ?>
