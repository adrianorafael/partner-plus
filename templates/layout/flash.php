<?php
// Exibe mensagens flash (success, error, warning, info)
$flash = Helpers::getAllFlash();
if (empty($flash)) return;

$styles = [
    'success' => ['bg-green-50 border-green-200 text-green-800', 'M5 13l4 4L19 7'],
    'error'   => ['bg-red-50 border-red-200 text-red-800',   'M6 18L18 6M6 6l12 12'],
    'warning' => ['bg-yellow-50 border-yellow-200 text-yellow-800', 'M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z'],
    'info'    => ['bg-blue-50 border-blue-200 text-blue-800', 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
];
?>
<div class="space-y-3 mb-6">
<?php foreach ($flash as $type => $message): ?>
    <?php [$cls, $icon] = $styles[$type] ?? $styles['info']; ?>
    <div class="flex items-start gap-3 p-4 rounded-xl border <?= $cls ?>">
        <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="<?= $icon ?>"/>
        </svg>
        <p class="text-sm font-medium"><?= Helpers::e($message) ?></p>
    </div>
<?php endforeach; ?>
</div>
