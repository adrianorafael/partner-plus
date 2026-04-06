<?php $pageTitle = 'Entrar'; ?>
<?php include __DIR__ . '/../layout/head.php'; ?>

<div class="min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md">

        <!-- Logo -->
        <div class="text-center mb-8">
            <h1 class="text-3xl font-black tracking-tight" style="font-family:Roboto,sans-serif;color:#06090F;">
                Partner <span class="text-plus-cyan">Plus</span>
            </h1>
            <p class="text-slate-500 mt-1 text-sm">Plataforma B2B de Oportunidades</p>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-8">
            <h2 class="text-xl font-bold mb-1 font-title">Bem-vindo de volta</h2>
            <p class="text-slate-500 text-sm mb-6">Acesse sua conta para continuar</p>

            <?php include __DIR__ . '/../layout/flash.php'; ?>

            <?php if (!empty($error)): ?>
            <div class="mb-5 p-4 bg-red-50 border border-red-200 rounded-xl">
                <p class="text-red-700 text-sm"><?= Helpers::e($error) ?></p>
            </div>
            <?php endif; ?>

            <?php if (!empty($blocked)): ?>
            <div class="mb-5 p-4 bg-orange-50 border border-orange-200 rounded-xl">
                <p class="text-orange-700 text-sm font-medium">Conta aguardando aprovação</p>
                <p class="text-orange-600 text-xs mt-1">
                    <?php if ($blocked === 'pending_email'): ?>
                        Confirme seu e-mail através do link enviado no cadastro.
                    <?php else: ?>
                        Seu cadastro está em análise pelo administrador. Você receberá um e-mail quando for aprovado.
                    <?php endif; ?>
                </p>
            </div>
            <?php endif; ?>

            <form method="post" action="<?= APP_URL ?>/entrar" class="space-y-4">
                <?= CSRF::field() ?>

                <div>
                    <label for="email" class="block text-sm font-medium text-slate-700 mb-1">E-mail corporativo</label>
                    <input type="email" id="email" name="email" required autocomplete="email"
                        value="<?= Helpers::e($_POST['email'] ?? '') ?>"
                        class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-plus-cyan transition-shadow"
                        placeholder="voce@empresa.com.br">
                </div>

                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label for="password" class="block text-sm font-medium text-slate-700">Senha</label>
                        <a href="<?= APP_URL ?>/recuperar-senha" class="text-xs text-plus-cyan hover:underline">Esqueceu a senha?</a>
                    </div>
                    <div class="relative">
                        <input type="password" id="password" name="password" required autocomplete="current-password"
                            class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-plus-cyan pr-11 transition-shadow">
                        <button type="button" onclick="togglePassword('password', this)"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <button type="submit"
                    class="w-full py-3 rounded-xl font-bold text-sm text-void bg-plus-cyan hover:opacity-90 transition-opacity mt-2">
                    Entrar
                </button>
            </form>

            <p class="text-center text-sm text-slate-500 mt-6">
                Não tem uma conta?
                <a href="<?= APP_URL ?>/cadastrar" class="text-plus-cyan font-medium hover:underline">Cadastre-se</a>
            </p>
        </div>

        <p class="text-center text-xs text-slate-400 mt-6">Partner Plus © <?= date('Y') ?></p>
    </div>
</div>

<script>
function togglePassword(id, btn) {
    const input = document.getElementById(id);
    input.type = input.type === 'password' ? 'text' : 'password';
}
</script>
<?php include __DIR__ . '/../layout/footer.php'; ?>
