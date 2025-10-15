<?php
/**
 * EstacionaFácil - Relatório Semanal
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

$pageTitle = 'Relatório Semanal';
$db = getDB();

$dataInicio = $_GET['inicio'] ?? date('Y-m-d', strtotime('monday this week'));
$dataFim = date('Y-m-d', strtotime($dataInicio . ' +6 days'));

$receitas = floatval($db->selectOne(
    "SELECT COALESCE(SUM(valor), 0) as total FROM veiculos 
     WHERE data_saida BETWEEN ? AND ? AND pago = 1",
    [$dataInicio, $dataFim]
)['total'] ?? 0);

$despesas = floatval($db->selectOne(
    "SELECT COALESCE(SUM(valor), 0) as total FROM despesas 
     WHERE data BETWEEN ? AND ?",
    [$dataInicio, $dataFim]
)['total'] ?? 0);

$entradas = intval($db->selectOne(
    "SELECT COUNT(*) as total FROM veiculos WHERE data_entrada BETWEEN ? AND ?",
    [$dataInicio, $dataFim]
)['total'] ?? 0);

include __DIR__ . '/../../includes/header.php';
?>

<div class="mb-6 flex justify-between items-center no-print">
    <h2 class="text-2xl font-bold text-gray-800">
        <i class="fas fa-calendar-week mr-2 text-blue-600"></i>
        Relatório Semanal
    </h2>
    <div class="flex gap-2">
        <input type="date" value="<?php echo $dataInicio; ?>" 
               onchange="window.location.href='?inicio='+this.value"
               class="px-4 py-2 border rounded-lg">
        <button onclick="printPage()" class="px-4 py-2 bg-blue-600 text-white rounded-lg">
            <i class="fas fa-print mr-2"></i>Imprimir
        </button>
    </div>
</div>

<div class="bg-white rounded-lg shadow-lg p-8">
    <h3 class="text-xl font-bold mb-4">
        Período: <?php echo formatDate($dataInicio); ?> a <?php echo formatDate($dataFim); ?>
    </h3>
    
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="text-center p-6 bg-blue-50 rounded-lg">
            <p class="text-gray-600 mb-2">Total de Entradas</p>
            <p class="text-4xl font-bold text-blue-600"><?php echo $entradas; ?></p>
        </div>
        <div class="text-center p-6 bg-green-50 rounded-lg">
            <p class="text-gray-600 mb-2">Receitas</p>
            <p class="text-3xl font-bold text-green-600"><?php echo formatMoney($receitas); ?></p>
        </div>
        <div class="text-center p-6 bg-red-50 rounded-lg">
            <p class="text-gray-600 mb-2">Despesas</p>
            <p class="text-3xl font-bold text-red-600"><?php echo formatMoney($despesas); ?></p>
        </div>
    </div>
    
    <div class="mt-6 pt-6 border-t text-center">
        <p class="text-gray-600 mb-2">Lucro Líquido</p>
        <p class="text-5xl font-bold text-<?php echo ($receitas - $despesas) >= 0 ? 'green' : 'red'; ?>-600">
            <?php echo formatMoney($receitas - $despesas); ?>
        </p>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
