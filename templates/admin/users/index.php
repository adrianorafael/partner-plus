<?php $pageTitle = 'Gestão de Usuários'; ?>
<?php include __DIR__ . '/../../layout/head.php'; ?>
<?php include __DIR__ . '/../../layout/nav.php'; ?>

<main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <div class="mb-6">
        <h2 class="text-2xl font-bold font-title">Gestão de Usuários</h2>
        <p class="text-slate-500 text-sm mt-1">Aprove cadastros e gerencie usuários da plataforma</p>
    </div>

    <?php include __DIR__ . '/../../layout/flash.php'; ?>

    <!-- Filtros -->
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 mb-4">
        <form method="get" action="<?= APP_URL ?>/admin/users" class="flex flex-wrap gap-3 items-end">
            <div>
                <label class="block text-xs text-slate-500 mb-1">Status</label>
                <select name="status" class="border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-plus-cyan">
                    <option value="">Todos</option>
                    <option value="pending_admin" <?= ($_GET['status'] ?? '') === 'pending_admin' ? 'selected' : '' ?>>Aguardando Aprovação</option>
                    <option value="pending_email" <?= ($_GET['status'] ?? '') === 'pending_email' ? 'selected' : '' ?>>Aguardando E-mail</option>
                    <option value="active"        <?= ($_GET['status'] ?? '') === 'active'        ? 'selected' : '' ?>>Ativos</option>
                </select>
            </div>
            <div>
                <label class="block text-xs text-slate-500 mb-1">Tipo</label>
                <select name="type" class="border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-plus-cyan">
                    <option value="">Todos</option>
                    <option value="client"   <?= ($_GET['type'] ?? '') === 'client'   ? 'selected' : '' ?>>Clientes</option>
                    <option value="provider" <?= ($_GET['type'] ?? '') === 'provider' ? 'selected' : '' ?>>Fornecedores</option>
                </select>
            </div>
            <div>
                <label class="block text-xs text-slate-500 mb-1">Buscar</label>
                <input type="text" name="q" value="<?= Helpers::e($_GET['q'] ?? '') ?>"
                    placeholder="Nome, empresa, CNPJ..."
                    class="border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-plus-cyan w-48">
            </div>
            <button type="submit"
                class="px-4 py-2 rounded-xl text-sm font-medium bg-plus-cyan text-void hover:opacity-90 transition-opacity">
                Filtrar
            </button>
        </form>
    </div>

    <!-- Tabela -->
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <?php if (empty($users)): ?>
        <div class="px-6 py-16 text-center">
            <p class="text-slate-400 text-sm">Nenhum usuário encontrado.</p>
        </div>
        <?php else: ?>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 border-b border-slate-100">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Usuário</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Empresa / CNPJ</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Tipo</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Cadastrado</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    <?php foreach ($users as $u): ?>
                    <tr class="hover:bg-slate-50/50 transition-colors <?= ($u['cnpj_duplicate'] ?? false) ? 'bg-yellow-50/30' : '' ?>">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-2">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <p class="font-medium text-void"><?= Helpers::e($u['representative_name']) ?></p>
                                        <?php if ($u['cnpj_duplicate'] ?? false): ?>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-yellow-100 text-yellow-700"
                                              title="Outro representante com este CNPJ já está cadastrado">⚠ CNPJ duplicado</span>
                                        <?php endif; ?>
                                    </div>
                                    <p class="text-xs text-slate-400"><?= Helpers::e($u['email']) ?></p>
                                    <p class="text-xs text-slate-400"><?= Helpers::e(Helpers::formatPhone($u['phone'])) ?></p>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-4">
                            <p class="text-sm text-void"><?= Helpers::e($u['company_name']) ?></p>
                            <p class="text-xs text-slate-400 font-mono"><?= Helpers::e(Helpers::formatCNPJ($u['cnpj'])) ?></p>
                        </td>
                        <td class="px-4 py-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                <?= $u['type'] === 'client' ? 'bg-blue-100 text-blue-700' : 'bg-green-100 text-green-700' ?>">
                                <?= $u['type'] === 'client' ? 'Cliente' : 'Fornecedor' ?>
                            </span>
                        </td>
                        <td class="px-4 py-4">
                            <?= Helpers::userStatusBadge($u['status']) ?>
                        </td>
                        <td class="px-4 py-4 text-xs text-slate-400"><?= Helpers::formatDate($u['created_at']) ?></td>
                        <td class="px-4 py-4">
                            <div class="flex items-center gap-2 justify-end">
                                <?php if ($u['status'] === 'pending_admin'): ?>
                                <form method="post" action="<?= APP_URL ?>/admin/users/<?= $u['id'] ?>/approve">
                                    <?= CSRF::field() ?>
                                    <button type="submit"
                                        class="px-3 py-1.5 rounded-lg text-xs font-bold text-void bg-plus-cyan hover:opacity-90 transition-opacity">
                                        Aprovar
                                    </button>
                                </form>
                                <form method="post" action="<?= APP_URL ?>/admin/users/<?= $u['id'] ?>/reject"
                                      onsubmit="return confirm('Remover este usuário?')">
                                    <?= CSRF::field() ?>
                                    <button type="submit"
                                        class="px-3 py-1.5 rounded-lg text-xs font-medium text-red-500 hover:bg-red-50 transition-colors">
                                        Rejeitar
                                    </button>
                                </form>
                                <?php elseif ($u['status'] === 'active' && $u['type'] !== 'admin'): ?>
                                <form method="post" action="<?= APP_URL ?>/admin/users/<?= $u['id'] ?>/deactivate"
                                      onsubmit="return confirm('Desativar este usuário?')">
                                    <?= CSRF::field() ?>
                                    <button type="submit"
                                        class="px-3 py-1.5 rounded-lg text-xs font-medium text-slate-500 hover:bg-slate-100 transition-colors">
                                        Desativar
                                    </button>
                                </form>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>

</main>
<?php include __DIR__ . '/../../layout/footer.php'; ?>
