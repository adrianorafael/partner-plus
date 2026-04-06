<?php $pageTitle = 'Página não encontrada'; ?>
<?php if (defined('APP_URL')): ?>
<?php include __DIR__ . '/../layout/head.php'; ?>
<?php endif; ?>
<div class="min-h-screen flex items-center justify-center p-4">
    <div class="text-center">
        <p class="text-8xl font-black font-title text-plus-cyan">404</p>
        <h1 class="text-2xl font-bold font-title mt-2 text-void">Página não encontrada</h1>
        <p class="text-slate-500 mt-2 mb-6">A página que você procura não existe ou foi movida.</p>
        <?php if (defined('APP_URL')): ?>
        <a href="<?= APP_URL ?>/painel"
           class="inline-block px-8 py-3 rounded-xl font-bold text-sm text-void bg-plus-cyan hover:opacity-90 transition-opacity">
            Voltar ao início
        </a>
        <?php endif; ?>
    </div>
</div>
<?php if (defined('APP_URL')): ?>
<?php include __DIR__ . '/../layout/footer.php'; ?>
<?php endif; ?>
