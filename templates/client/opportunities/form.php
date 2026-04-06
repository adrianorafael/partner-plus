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
        <a href="<?= APP_URL ?>/minhas-oportunidades" class="text-sm text-slate-400 hover:text-plus-cyan flex items-center gap-1 mb-3">
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
          action="<?= $isEdit ? APP_URL . '/editar-oportunidade/' . ($opp['id'] ?? '') : APP_URL . '/nova-oportunidade' ?>"
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
        <?php
        // Agrupa providers e seus produtos em estrutura JS-friendly
        $providersMap = [];
        foreach ($providers ?? [] as $row) {
            $pid = $row['id'];
            if (!isset($providersMap[$pid])) {
                $providersMap[$pid] = ['id' => $pid, 'name' => $row['company_name'], 'products' => []];
            }
            if ($row['prod_id']) {
                $providersMap[$pid]['products'][] = [
                    'id'   => $row['prod_id'],
                    'name' => $row['prod_name'],
                    'type' => $row['prod_type'],
                ];
            }
        }
        $selectedProviderId = (int)($opp['target_provider_id'] ?? $_POST['target_provider_id'] ?? 0);
        $selectedProductId  = (int)($opp['target_product_id']  ?? $_POST['target_product_id']  ?? 0);
        $selectedContract   = $opp['contract_type'] ?? $_POST['contract_type'] ?? '';
        $isSpecific         = $selectedProviderId > 0 || ($_POST['targeting'] ?? '') === 'specific';
        ?>
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
            <h3 class="font-bold font-title mb-1">Direcionamento</h3>
            <p class="text-slate-500 text-sm mb-4">Defina se a oportunidade é aberta a todos os fornecedores ou direcionada a um parceiro específico.</p>

            <div class="space-y-3" id="targeting-section">
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
                        <p class="font-semibold text-sm text-void">Específico — Parceiro determinado</p>
                        <p class="text-xs text-slate-400 mt-0.5">Somente o parceiro indicado verá esta oportunidade.</p>
                    </div>
                </label>
            </div>

            <!-- Campos do direcionamento específico -->
            <div id="specific-fields" class="mt-4 space-y-4 <?= $isSpecific ? '' : 'hidden' ?>">

                <?php if (empty($providersMap)): ?>
                <p class="text-sm text-orange-600 bg-orange-50 rounded-xl px-4 py-3">
                    Nenhum fornecedor parceiro ativo cadastrado na plataforma ainda.
                </p>
                <?php else: ?>

                <!-- Selecionar fornecedor -->
                <div>
                    <label for="target_provider_id" class="block text-sm font-medium text-slate-700 mb-1">
                        Fornecedor Parceiro <span class="text-red-500">*</span>
                    </label>
                    <select id="target_provider_id" name="target_provider_id"
                        class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-plus-cyan">
                        <option value="">Selecione um parceiro...</option>
                        <?php foreach ($providersMap as $pv): ?>
                        <option value="<?= $pv['id'] ?>" <?= $selectedProviderId === $pv['id'] ? 'selected' : '' ?>>
                            <?= Helpers::e($pv['name']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Selecionar produto/serviço -->
                <div id="product-field" class="<?= $selectedProviderId ? '' : 'hidden' ?>">
                    <label for="target_product_id" class="block text-sm font-medium text-slate-700 mb-1">
                        Produto / Serviço <span class="text-xs text-slate-400">(opcional)</span>
                    </label>
                    <select id="target_product_id" name="target_product_id"
                        class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-plus-cyan">
                        <option value="">Selecione um produto ou serviço...</option>
                        <?php
                        $initialProducts = $providersMap[$selectedProviderId]['products'] ?? [];
                        foreach ($initialProducts as $prod): ?>
                        <option value="<?= $prod['id'] ?>" <?= $selectedProductId === (int)$prod['id'] ? 'selected' : '' ?>>
                            <?= Helpers::e($prod['name']) ?> (<?= $prod['type'] === 'software' ? 'Software' : 'Serviço' ?>)
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Tipo de contratação -->
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">
                        Tipo de Contratação <span class="text-red-500">*</span>
                    </label>
                    <div class="grid grid-cols-2 gap-3">
                        <?php
                        $contracts = ['new_contract' => ['Nova Contratação', 'Primeira aquisição deste produto/serviço.'],
                                      'expansion'    => ['Incremento de Contrato', 'Expansão ou renovação de contrato existente.']];
                        foreach ($contracts as $val => [$label, $hint]):
                            $checked = $selectedContract === $val;
                        ?>
                        <label class="cursor-pointer">
                            <input type="radio" name="contract_type" value="<?= $val ?>" class="sr-only peer"
                                <?= $checked ? 'checked' : '' ?>>
                            <div class="border-2 border-slate-200 rounded-xl p-3 transition-all peer-checked:border-plus-cyan peer-checked:bg-cyan-50 h-full">
                                <p class="font-semibold text-sm text-void"><?= $label ?></p>
                                <p class="text-xs text-slate-400 mt-0.5"><?= $hint ?></p>
                            </div>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <?php endif; ?>
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
            <a href="<?= APP_URL ?>/minhas-oportunidades"
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
// Mapa de produtos por fornecedor (PHP → JS)
const providersMap = <?= json_encode(array_values($providersMap ?? []), JSON_UNESCAPED_UNICODE) ?>;

// Toggle seção de direcionamento específico
document.querySelectorAll('[name="targeting"]').forEach(radio => {
    radio.addEventListener('change', function () {
        const specific = this.value === 'specific';
        document.getElementById('specific-fields').classList.toggle('hidden', !specific);
        document.getElementById('label-open').classList.toggle('border-plus-cyan', !specific);
        document.getElementById('label-open').classList.toggle('bg-cyan-50', !specific);
        document.getElementById('label-specific').classList.toggle('border-plus-cyan', specific);
        document.getElementById('label-specific').classList.toggle('bg-cyan-50', specific);
        if (!specific) {
            const sel = document.getElementById('target_provider_id');
            if (sel) sel.value = '';
            document.getElementById('product-field')?.classList.add('hidden');
            const pSel = document.getElementById('target_product_id');
            if (pSel) { pSel.innerHTML = '<option value="">Selecione um produto ou serviço...</option>'; }
        }
    });
});

// Ao mudar o fornecedor, carregar produtos correspondentes
const providerSelect = document.getElementById('target_provider_id');
if (providerSelect) {
    providerSelect.addEventListener('change', function () {
        const pid     = parseInt(this.value);
        const sel     = document.getElementById('target_product_id');
        const wrapper = document.getElementById('product-field');
        sel.innerHTML = '<option value="">Selecione um produto ou serviço...</option>';
        if (!pid) { wrapper.classList.add('hidden'); return; }
        const provider = providersMap.find(p => p.id === pid);
        if (provider && provider.products.length > 0) {
            provider.products.forEach(prod => {
                const opt  = document.createElement('option');
                opt.value  = prod.id;
                opt.text   = prod.name + ' (' + (prod.type === 'software' ? 'Software' : 'Serviço') + ')';
                sel.appendChild(opt);
            });
            wrapper.classList.remove('hidden');
        } else {
            wrapper.classList.add('hidden');
        }
    });
}

// Toggle campos de contato
document.querySelectorAll('[name="contact_person_type"]').forEach(radio => {
    radio.addEventListener('change', function () {
        document.getElementById('other-contact-fields').classList.toggle('hidden', this.value !== 'other');
    });
});
</script>
<?php include __DIR__ . '/../../layout/footer.php'; ?>
