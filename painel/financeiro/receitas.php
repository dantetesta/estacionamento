<?php
/**
 * EstacionaFácil - Visualizar Receitas
 * 
 * @author Dante Testa (https://dantetesta.com.br)
 * @version 1.0.0
 * @date 14/10/2025 19:54
 */

define('ESTACIONAFACIL', true);
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';

$pageTitle = 'Receitas';
$db = getDB();

$mesAtual = $_GET['mes'] ?? date('Y-m');

// Receitas por tipo
$receitasPorTipo = $db->select(
    "SELECT tipo, COUNT(*) as quantidade, SUM(valor) as total
     FROM veiculos
     WHERE DATE_FORMAT(data_saida, '%Y-%m') = ? AND pago = 1
     GROUP BY tipo",
    [$mesAtual]
);

// Receitas por forma de pagamento
$receitasPorPagamento = $db->select(
    "SELECT forma_pagamento, COUNT(*) as quantidade, SUM(valor) as total
     FROM veiculos
     WHERE DATE_FORMAT(data_saida, '%Y-%m') = ? AND pago = 1
     GROUP BY forma_pagamento",
    [$mesAtual]
);

// Total
$totalReceitas = array_sum(array_column($receitasPorTipo, 'total'));

include __DIR__ . '/../../includes/header.php';
?>

<div class="mb-6 flex justify-between items-center">
    <h2 class="text-2xl font-bold text-gray-800">
        <i class="fas fa-dollar-sign mr-2 text-green-600"></i>
        Receitas
    </h2>
    <input type="month" value="<?php echo $mesAtual; ?>" 
           onchange="window.location.href='?mes='+this.value"
           class="px-4 py-2 border rounded-lg">
</div>

<!-- Total -->
<div class="bg-gradient-to-br from-green-500 to-green-600 text-white rounded-lg shadow-lg p-8 mb-6">
    <h3 class="text-lg font-semibold mb-2">Total de Receitas</h3>
    <p class="text-5xl font-bold"><?php echo formatMoney($totalReceitas); ?></p>
    <p class="text-green-100 mt-2"><?php echo date('m/Y', strtotime($mesAtual . '-01')); ?></p>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Por Tipo -->
    <div class="bg-white rounded-lg shadow-lg p-6">
        <h3 class="text-xl font-bold text-gray-800 mb-4">
            <i class="fas fa-car mr-2 text-blue-600"></i>
            Receitas por Tipo de Veículo
        </h3>
        
        <?php if (empty($receitasPorTipo)): ?>
            <p class="text-gray-500 text-center py-8">Nenhuma receita neste mês</p>
        <?php else: ?>
            <div class="space-y-3">
                <?php foreach ($receitasPorTipo as $r): ?>
                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                        <div>
                            <p class="font-semibold text-gray-800"><?php echo getVehicleTypeName($r['tipo']); ?></p>
                            <p class="text-sm text-gray-600"><?php echo $r['quantidade']; ?> veículo(s)</p>
                        </div>
                        <p class="text-xl font-bold text-green-600"><?php echo formatMoney($r['total']); ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Por Forma de Pagamento -->
    <div class="bg-white rounded-lg shadow-lg p-6">
        <h3 class="text-xl font-bold text-gray-800 mb-4">
            <i class="fas fa-credit-card mr-2 text-blue-600"></i>
            Receitas por Forma de Pagamento
        </h3>
        
        <?php if (empty($receitasPorPagamento)): ?>
            <p class="text-gray-500 text-center py-8">Nenhuma receita neste mês</p>
        <?php else: ?>
            <div class="space-y-3">
                <?php foreach ($receitasPorPagamento as $r): ?>
                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                        <div>
                            <p class="font-semibold text-gray-800"><?php echo getPaymentMethodName($r['forma_pagamento']); ?></p>
                            <p class="text-sm text-gray-600"><?php echo $r['quantidade']; ?> pagamento(s)</p>
                        </div>
                        <p class="text-xl font-bold text-green-600"><?php echo formatMoney($r['total']); ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
