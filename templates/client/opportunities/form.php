<?php
// Template compartilhado para criar e editar oportunidades
$isEdit = isset($opportunity);
$pageTitle = $isEdit ? 'Editar Oportunidade' : 'Nova Oportunidade';
$opp = $opportunity ?? [];
?>
<?php include __DIR__ . '/../../layout/head.php'; ?>
<?php include __DIR__ . '/../../layout/nav.php'; ?>

<main class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <div class="mb-6">
        <a href="<?= APP_URL ?>/cliente/oportunidades" class="text-sm text-slate-400 hover:text-plus-cyan flex items-center gap-1 mb-3">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Voltar para oportunidades
        </a>
        <h2 class="text-2xl font-bold font-title"><?= $isEdit ? 'Editar Oportunidade' : 'Nova Oportunidade' ?></h2>
    </div>

    <?php if (!empty($errors)): ?>
    <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl space-y-1">
        <?php foreach ($errors as $err): ?>
            <p class="text-red-700 text-sm">• <?= Helpers::e($err) ?></p>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <form method="post"
          action="<?= $isEdit ? APP_URL . '/cliente/oportunidades/' . $opp['id'] . '/editar' : APP_URL . '/cliente/oportunidades/criar' ?>"
          class="space-y-6">
        <?= CSRF::field() ?>

        <!-- Informações básicas -->
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
            <h3 class="font-bold font-title mb-4">Informações da Oportunidade</h3>

            <div class="space-y-4">
                <!-- Título -->
                <div>
                    <label for="title" class="block text-sm font-medium text-slate-700 mb-1">
                        Nome do Software/Serviço <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="title" name="title" required
                        value="<?= Helpers::e($opp['title'] ?? $_POST['title'] ?? '') ?>"
                        placeholder="Ex: Sistema ERP, Desenvolvimento de App Mobile"
                        class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-plus-cyan">
                </div>

                <!-- Tipo -->
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Tipo <span class="text-red-500">*</span></label>
                    <div class="grid grid-cols-2 gap-3">
                        <?php foreach (['software' => 'Software', 'service' => 'Serviço'] as $val => $label): ?>
                        <?php $checked = ($opp['type'] ?? $_POST['type'] ?? '') === $val; ?>
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

                <!-- Descrição -->
                <div>
                    <label for="description" class="block text-sm font-medium text-slate-700 mb-1">
                        Descrição Detalhada <span class="text-red-500">*</span>
                    </label>
                    <textarea id="description" name="description" required rows="5"
                        placeholder="Descreva os requisitos, escopo, contexto da necessidade..."
                        class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-plus-cyan resize-y"><?= Helpers::e($opp['description'] ?? $_POST['description'] ?? '') ?></textarea>
                </div>

                <!-- Datas -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="start_date" class="block text-sm font-medium text-slate-700 mb-1">Data Inicial <span class="text-red-500">*</span></label>
                        <input type="date" id="start_date" name="start_date" required
                            value="<?= Helpers::e($opp['start_date'] ?? $_POST['start_date'] ?? date('Y-m-d')) ?>"
                            class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-plus-cyan">
                    </div>
                    <div>
                        <label for="end_date" class="block text-sm font-medium text-slate-700 mb-1">Data Final <span class="text-red-500">*</span></label>
                        <input type="date" id="end_date" name="end_date" required
                            value="<?= Helpers::e($opp['end_date'] ?? $_POST['end_date'] ?? '') ?>"
                            class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-plus-cyan">
                    </div>
                </div>
            </div>
        </div>

        <!-- Direcionamento -->
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
            <h3 class="font-bold font-title mb-1">Direcionamento</h3>
            <p class="text-slate-500 text-sm mb-4">Defina se a oportunidade é aberta a todos os fornecedores ou direcionada a um fornecedor específico.</p>

            <div class="space-y-3" id="targeting-section">
                <?php
                $currentTarget = $opp['target_provider'] ?? $_POST['target_provider'] ?? '';
                $isSpecific = !empty($currentTarget);
                ?>
                <label class="flex items-start gap-3 cursor-pointer p-3 rounded-xl border-2 border-slate-200 hover:border-slate-300 transition-colors
                               <?= !$isSpecific ? 'border-plus-cyan bg-cyan-50' : '' ?>" id="label-open">
                    <input type="radio" name="targeting" value="open" class="mt-0.5 accent-[#00E5C8]"
                        <?= !$isSpecific ? 'checked' : '' ?>>
                    <div>
                        <p class="font-semibold text-sm text-void">Aberto — Ampla concorrência</p>
                        <p class="text-xs text-slate-400 mt-0.5">Todos os fornecedores qualificados poderão ver esta oportunidade.</p>
                    </div>
                </label>
                <label class="flex items-start gap-3 cursor-pointer p-3 rounded-xl border-2 border-slate-200 hover:border-slate-300 transition-colors
                               <?= $isSpecific ? 'border-plus-cyan bg-cyan-50' : '' ?>" id="label-specific">
                    <input type="radio" name="targeting" value="specific" class="mt-0.5 accent-[#00E5C8]"
                        <?= $isSpecific ? 'checked' : '' ?>>
                    <div class="w-full">
                        <p class="font-semibold text-sm text-void">Específico — Fornecedor determinado</p>
                        <p class="text-xs text-slate-400 mt-0.5">Somente o fornecedor indicado verá esta oportunidade.</p>
                        <div id="provider-name-field" class="mt-3 <?= $isSpecific ? '' : 'hidden' ?>">
                            <input type="text" name="target_provider" id="target_provider"
                                value="<?= Helpers::e($currentTarget) ?>"
                                placeholder="Nome do fornecedor"
                                class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-plus-cyan">
                        </div>
                    </div>
                </label>
            </div>
        </div>

        <!-- Responsável pelo contato -->
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
            <h3 class="font-bold font-title mb-1">Responsável pelo Contato</h3>
            <p class="text-slate-500 text-sm mb-4">Quem os fornecedores devem contatar sobre esta oportunidade?</p>

            <?php
            $contactType = $opp['contact_person_type'] ?? $_POST['contact_person_type'] ?? 'self';
            ?>
            <div class="space-y-3">
                <label class="flex items-start gap-3 cursor-pointer">
                    <input type="radio" name="contact_person_type" value="self" class="mt-0.5 accent-[#00E5C8]"
                        <?= $contactType === 'self' ? 'checked' : '' ?> id="contact-self">
                    <div>
                        <p class="font-semibold text-sm text-void">Eu mesmo</p>
                        <p class="text-xs text-slate-400 mt-0.5">Usar meus dados do cadastro como contato.</p>
                    </div>
                </label>
                <label class="flex items-start gap-3 cursor-pointer">
                    <input type="radio" name="contact_person_type" value="other" class="mt-0.5 accent-[#00E5C8]"
                        <?= $contactType === 'other' ? 'checked' : '' ?> id="contact-other">
                    <div class="w-full">
                        <p class="font-semibold text-sm text-void">Outro responsável</p>
                        <p class="text-xs text-slate-400 mt-0.5">Indicar dados de contato de outra pessoa.</p>
                    </div>
                </label>
            </div>

            <div id="other-contact-fields" class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-4
                <?= $contactType === 'other' ? '' : 'hidden' ?>">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Nome <span class="text-red-500">*</span></label>
                    <input type="text" name="contact_name"
                        value="<?= Helpers::e($opp['contact_name'] ?? $_POST['contact_name'] ?? '') ?>"
                        class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-plus-cyan">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Cargo</label>
                    <input type="text" name="contact_role"
                        value="<?= Helpers::e($opp['contact_role'] ?? $_POST['contact_role'] ?? '') ?>"
                        class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-plus-cyan">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">E-mail <span class="text-red-500">*</span></label>
                    <input type="email" name="contact_email"
                        value="<?= Helpers::e($opp['contact_email'] ?? $_POST['contact_email'] ?? '') ?>"
                        class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-plus-cyan">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Telefone</label>
                    <input type="tel" name="contact_phone"
                        value="<?= Helpers::e($opp['contact_phone'] ?? $_POST['contact_phone'] ?? '') ?>"
                        placeholder="(11) 99999-9999"
                        class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-plus-cyan">
                </div>
            </div>
        </div>

        <!-- Botões -->
        <div class="flex items-center gap-3 justify-end">
            <a href="<?= APP_URL ?>/cliente/oportunidades"
               class="px-6 py-2.5 rounded-xl text-sm font-medium text-slate-600 hover:bg-slate-100 transition-colors">
                Cancelar
            </a>
            <button type="submit"
                class="px-6 py-2.5 rounded-xl text-sm font-bold text-void bg-plus-cyan hover:opacity-90 transition-opacity">
                <?= $isEdit ? 'Salvar Alterações' : 'Publicar Oportunidade' ?>
            </button>
        </div>
    </form>

</main>

<script>
// Toggle campo fornecedor específico
document.querySelectorAll('[name="targeting"]').forEach(radio => {
    radio.addEventListener('change', function() {
        const field = document.getElementById('provider-name-field');
        field.classList.toggle('hidden', this.value !== 'specific');
        if (this.value !== 'specific') {
            document.getElementById('target_provider').value = '';
        }
        // Atualizar estilos dos labels
        document.getElementById('label-open').classList.toggle('border-plus-cyan', this.value === 'open');
        document.getElementById('label-open').classList.toggle('bg-cyan-50', this.value === 'open');
        document.getElementById('label-specific').classList.toggle('border-plus-cyan', this.value === 'specific');
        document.getElementById('label-specific').classList.toggle('bg-cyan-50', this.value === 'specific');
    });
});

// Toggle campos de contato
document.querySelectorAll('[name="contact_person_type"]').forEach(radio => {
    radio.addEventListener('change', function() {
        document.getElementById('other-contact-fields').classList.toggle('hidden', this.value !== 'other');
    });
});
</script>
<?php include __DIR__ . '/../../layout/footer.php'; ?>
