<?php $pageTitle = 'Cadastro'; ?>
<?php include __DIR__ . '/../layout/head.php'; ?>

<div class="min-h-screen flex items-center justify-center p-4 py-12">
    <div class="w-full max-w-lg">

        <!-- Logo -->
        <div class="text-center mb-8">
            <h1 class="text-3xl font-black tracking-tight font-title" style="color:#06090F;">
                Partner <span class="text-plus-cyan">Plus</span>
            </h1>
            <p class="text-slate-500 mt-1 text-sm">Plataforma B2B de Oportunidades</p>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-8">
            <h2 class="text-xl font-bold mb-1 font-title">Criar conta</h2>
            <p class="text-slate-500 text-sm mb-6">Preencha os dados abaixo para se cadastrar</p>

            <?php if (!empty($errors)): ?>
            <div class="mb-5 p-4 bg-red-50 border border-red-200 rounded-xl space-y-1">
                <?php foreach ($errors as $err): ?>
                    <p class="text-red-700 text-sm">• <?= Helpers::e($err) ?></p>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <form method="post" action="<?= APP_URL ?>/criar-conta" class="space-y-4" id="register-form">
                <?= CSRF::field() ?>

                <!-- Tipo de conta -->
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Tipo de conta <span class="text-red-500">*</span></label>
                    <div class="grid grid-cols-2 gap-3">
                        <?php foreach (['client' => ['Cliente', 'Empresa que busca soluções'], 'provider' => ['Fornecedor', 'Empresa que oferece soluções']] as $val => [$label, $desc]): ?>
                        <label class="cursor-pointer">
                            <input type="radio" name="type" value="<?= $val ?>" class="sr-only peer" required
                                <?= (($_POST['type'] ?? '') === $val) ? 'checked' : '' ?>>
                            <div class="border-2 border-slate-200 rounded-xl p-3 text-center transition-all
                                        peer-checked:border-plus-cyan peer-checked:bg-cyan-50">
                                <p class="font-semibold text-sm text-void"><?= $label ?></p>
                                <p class="text-xs text-slate-400 mt-0.5"><?= $desc ?></p>
                            </div>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- CNPJ -->
                    <div>
                        <label for="cnpj" class="block text-sm font-medium text-slate-700 mb-1">CNPJ <span class="text-red-500">*</span></label>
                        <input type="text" id="cnpj" name="cnpj" required maxlength="18"
                            value="<?= Helpers::e($_POST['cnpj'] ?? '') ?>"
                            placeholder="00.000.000/0000-00"
                            class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-plus-cyan">
                        <p id="cnpj-feedback" class="text-xs mt-1 hidden"></p>
                    </div>

                    <!-- Telefone -->
                    <div>
                        <label for="phone" class="block text-sm font-medium text-slate-700 mb-1">Telefone <span class="text-red-500">*</span></label>
                        <input type="tel" id="phone" name="phone" required
                            value="<?= Helpers::e($_POST['phone'] ?? '') ?>"
                            placeholder="(11) 99999-9999"
                            class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-plus-cyan">
                    </div>
                </div>

                <!-- Empresa -->
                <div>
                    <label for="company_name" class="block text-sm font-medium text-slate-700 mb-1">Razão Social / Nome da Empresa <span class="text-red-500">*</span></label>
                    <input type="text" id="company_name" name="company_name" required
                        value="<?= Helpers::e($_POST['company_name'] ?? '') ?>"
                        class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-plus-cyan">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Nome do representante -->
                    <div>
                        <label for="representative_name" class="block text-sm font-medium text-slate-700 mb-1">Seu Nome <span class="text-red-500">*</span></label>
                        <input type="text" id="representative_name" name="representative_name" required
                            value="<?= Helpers::e($_POST['representative_name'] ?? '') ?>"
                            class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-plus-cyan">
                    </div>

                    <!-- Cargo -->
                    <div>
                        <label for="role" class="block text-sm font-medium text-slate-700 mb-1">Cargo <span class="text-red-500">*</span></label>
                        <input type="text" id="role" name="role" required
                            value="<?= Helpers::e($_POST['role'] ?? '') ?>"
                            placeholder="Ex: Diretor, Gerente de TI"
                            class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-plus-cyan">
                    </div>
                </div>

                <!-- E-mail -->
                <div>
                    <label for="email" class="block text-sm font-medium text-slate-700 mb-1">E-mail Corporativo <span class="text-red-500">*</span></label>
                    <input type="email" id="email" name="email" required autocomplete="email"
                        value="<?= Helpers::e($_POST['email'] ?? '') ?>"
                        placeholder="voce@empresa.com.br"
                        class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-plus-cyan">
                    <p class="text-xs text-slate-400 mt-1">E-mails de provedores gratuitos (Gmail, Outlook, etc.) não são aceitos.</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Senha -->
                    <div>
                        <label for="password" class="block text-sm font-medium text-slate-700 mb-1">Senha <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <input type="password" id="password" name="password" required minlength="8"
                                class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-plus-cyan pr-11">
                            <button type="button" onclick="togglePassword('password', this)" tabindex="-1"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </button>
                        </div>
                        <p class="text-xs text-slate-400 mt-1">Mínimo 8 caracteres</p>
                    </div>

                    <!-- Confirmar senha -->
                    <div>
                        <label for="password_confirm" class="block text-sm font-medium text-slate-700 mb-1">Confirmar Senha <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <input type="password" id="password_confirm" name="password_confirm" required
                                class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-plus-cyan pr-11">
                            <button type="button" onclick="togglePassword('password_confirm', this)" tabindex="-1"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <button type="submit"
                    class="w-full py-3 rounded-xl font-bold text-sm text-void bg-plus-cyan hover:opacity-90 transition-opacity mt-2">
                    Criar Conta
                </button>
            </form>

            <p class="text-center text-sm text-slate-500 mt-6">
                Já tem uma conta?
                <a href="<?= APP_URL ?>/entrar" class="text-plus-cyan font-medium hover:underline">Entrar</a>
            </p>
        </div>
    </div>
</div>

<script>
function togglePassword(id) {
    const input = document.getElementById(id);
    input.type = input.type === 'password' ? 'text' : 'password';
}

// Máscara CNPJ
document.getElementById('cnpj').addEventListener('input', function() {
    let v = this.value.replace(/\D/g, '').substring(0, 14);
    v = v.replace(/^(\d{2})(\d)/, '$1.$2');
    v = v.replace(/^(\d{2})\.(\d{3})(\d)/, '$1.$2.$3');
    v = v.replace(/\.(\d{3})(\d)/, '.$1/$2');
    v = v.replace(/(\d{4})(\d)/, '$1-$2');
    this.value = v;
});

// Máscara telefone
document.getElementById('phone').addEventListener('input', function() {
    let v = this.value.replace(/\D/g, '').substring(0, 11);
    if (v.length <= 10) {
        v = v.replace(/^(\d{2})(\d{4})(\d{0,4})/, '($1) $2-$3');
    } else {
        v = v.replace(/^(\d{2})(\d{5})(\d{0,4})/, '($1) $2-$3');
    }
    this.value = v;
});
</script>
<?php include __DIR__ . '/../layout/footer.php'; ?>
