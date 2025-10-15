<?php
/**
 * EstacionaFácil - Relatório Mensal
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

$pageTitle = 'Relatório Mensal';
$db = getDB();

$mes = $_GET['mes'] ?? date('Y-m');

$receitas = floatval($db->selectOne(
    "SELECT COALESCE(SUM(valor), 0) as total FROM veiculos 
     WHERE DATE_FORMAT(data_saida, '%Y-%m') = ? AND pago = 1",
    [$mes]
)['total'] ?? 0);

$despesas = floatval($db->selectOne(
    "SELECT COALESCE(SUM(valor), 0) as total FROM despesas 
     WHERE DATE_FORMAT(data, '%Y-%m') = ?",
    [$mes]
)['total'] ?? 0);

$entradas = intval($db->selectOne(
    "SELECT COUNT(*) as total FROM veiculos WHERE DATE_FORMAT(data_entrada, '%Y-%m') = ?",
    [$mes]
)['total'] ?? 0);

$porDia = $db->select(
    "SELECT DATE(data_saida) as dia, COUNT(*) as qtd, SUM(valor) as total
     FROM veiculos 
     WHERE DATE_FORMAT(data_saida, '%Y-%m') = ? AND pago = 1
     GROUP BY DATE(data_saida)
     ORDER BY dia",
    [$mes]
);

include __DIR__ . '/../../includes/header.php';
?>

<div class="mb-6 flex justify-between items-center no-print">
    <h2 class="text-2xl font-bold text-gray-800">
        <i class="fas fa-calendar-alt mr-2 text-blue-600"></i>
        Relatório Mensal
    </h2>
    <div class="flex gap-2">
        <input type="month" value="<?php echo $mes; ?>" 
               onchange="window.location.href='?mes='+this.value"
               class="px-4 py-2 border rounded-lg">
        <button onclick="printPage()" class="px-4 py-2 bg-blue-600 text-white rounded-lg">
            <i class="fas fa-print mr-2"></i>Imprimir
        </button>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
    <div class="bg-blue-500 text-white rounded-lg p-6">
        <p class="text-sm opacity-80 mb-2">Total de Entradas</p>
        <p class="text-4xl font-bold"><?php echo $entradas; ?></p>
    </div>
    <div class="bg-green-500 text-white rounded-lg p-6">
        <p class="text-sm opacity-80 mb-2">Receitas</p>
        <p class="text-3xl font-bold"><?php echo formatMoney($receitas); ?></p>
    </div>
    <div class="bg-red-500 text-white rounded-lg p-6">
        <p class="text-sm opacity-80 mb-2">Despesas</p>
        <p class="text-3xl font-bold"><?php echo formatMoney($despesas); ?></p>
    </div>
</div>

<div class="bg-white rounded-lg shadow-lg p-6 mb-6">
    <div class="text-center py-6">
        <p class="text-gray-600 mb-2 text-lg">Lucro Líquido do Mês</p>
        <p class="text-6xl font-bold text-<?php echo ($receitas - $despesas) >= 0 ? 'green' : 'red'; ?>-600">
            <?php echo formatMoney($receitas - $despesas); ?>
        </p>
    </div>
</div>

<div class="bg-white rounded-lg shadow-lg p-6">
    <h3 class="text-xl font-bold text-gray-800 mb-4">Faturamento Diário</h3>
    <?php if (empty($porDia)): ?>
        <p class="text-gray-500 text-center py-8">Nenhum faturamento neste mês</p>
    <?php else: ?>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left">Data</th>
                        <th class="px-4 py-2 text-center">Veículos</th>
                        <th class="px-4 py-2 text-right">Faturamento</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($porDia as $d): ?>
                        <tr class="border-t hover:bg-gray-50">
                            <td class="px-4 py-2"><?php echo formatDate($d['dia']); ?></td>
                            <td class="px-4 py-2 text-center"><?php echo $d['qtd']; ?></td>
                            <td class="px-4 py-2 text-right font-bold text-green-600"><?php echo formatMoney($d['total']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
