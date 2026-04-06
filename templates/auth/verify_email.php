<?php $pageTitle = 'Verificar E-mail'; ?>
<?php include __DIR__ . '/../layout/head.php'; ?>

<div class="min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md text-center">

        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-10">
            <?php if ($verified): ?>
                <div class="w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4" style="background:#B8FF45;">
                    <svg class="w-8 h-8" fill="none" stroke="#06090F" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <h2 class="text-xl font-bold mb-2 font-title">E-mail confirmado!</h2>
                <p class="text-slate-500 text-sm mb-6">
                    Seu e-mail foi verificado com sucesso. Seu cadastro está em análise e você receberá uma notificação quando for aprovado pelo administrador.
                </p>
                <a href="<?= APP_URL ?>/entrar"
                   class="inline-block px-8 py-3 rounded-xl font-bold text-sm text-void bg-plus-cyan hover:opacity-90 transition-opacity">
                    Ir para o Login
                </a>
            <?php else: ?>
                <div class="w-16 h-16 rounded-full bg-red-100 flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </div>
                <h2 class="text-xl font-bold mb-2 font-title">Link inválido ou expirado</h2>
                <p class="text-slate-500 text-sm mb-6">
                    O link de verificação é inválido ou expirou. Links são válidos por 24 horas.
                </p>
                <a href="<?= APP_URL ?>/criar-conta"
                   class="inline-block px-8 py-3 rounded-xl font-bold text-sm text-void bg-plus-cyan hover:opacity-90 transition-opacity">
                    Fazer novo cadastro
                </a>
            <?php endif; ?>
        </div>

    </div>
</div>
<?php include __DIR__ . '/../layout/footer.php'; ?>
