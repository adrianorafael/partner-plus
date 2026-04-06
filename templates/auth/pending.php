<?php $pageTitle = 'Cadastro Pendente'; ?>
<?php include __DIR__ . '/../layout/head.php'; ?>

<div class="min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md text-center">
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-10">
            <div class="w-16 h-16 rounded-full bg-yellow-100 flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <h2 class="text-xl font-bold mb-2 font-title">Cadastro Recebido!</h2>
            <p class="text-slate-500 text-sm leading-relaxed">
                Enviamos um e-mail de confirmação para <strong><?= Helpers::e($email ?? '') ?></strong>.
                Clique no link para verificar seu endereço e aguarde a aprovação do administrador.
            </p>
            <div class="mt-6 p-4 bg-slate-50 rounded-xl text-left space-y-3">
                <div class="flex items-center gap-3">
                    <div class="w-6 h-6 rounded-full bg-plus-cyan flex items-center justify-center flex-shrink-0 text-void text-xs font-bold">1</div>
                    <p class="text-sm text-slate-600">Confirme seu e-mail clicando no link enviado</p>
                </div>
                <div class="flex items-center gap-3">
                    <div class="w-6 h-6 rounded-full bg-slate-200 flex items-center justify-center flex-shrink-0 text-slate-500 text-xs font-bold">2</div>
                    <p class="text-sm text-slate-600">Aguarde a aprovação do administrador</p>
                </div>
                <div class="flex items-center gap-3">
                    <div class="w-6 h-6 rounded-full bg-slate-200 flex items-center justify-center flex-shrink-0 text-slate-500 text-xs font-bold">3</div>
                    <p class="text-sm text-slate-600">Acesse a plataforma com seu e-mail e senha</p>
                </div>
            </div>
            <a href="<?= APP_URL ?>/entrar" class="inline-block mt-6 text-sm text-plus-cyan hover:underline">← Ir para o Login</a>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../layout/footer.php'; ?>
