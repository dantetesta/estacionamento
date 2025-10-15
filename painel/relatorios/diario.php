<?php
/**
 * EstacionaFácil - Relatório Diário
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

$pageTitle = 'Relatório Diário';
$db = getDB();

$data = $_GET['data'] ?? date('Y-m-d');

// Estatísticas
$entradas = intval($db->selectOne("SELECT COUNT(*) as total FROM veiculos WHERE data_entrada = ?", [$data])['total'] ?? 0);
$saidas = intval($db->selectOne("SELECT COUNT(*) as total FROM veiculos WHERE data_saida = ?", [$data])['total'] ?? 0);
$receitas = floatval($db->selectOne("SELECT COALESCE(SUM(valor), 0) as total FROM veiculos WHERE data_saida = ? AND pago = 1", [$data])['total'] ?? 0);
$despesas = floatval($db->selectOne("SELECT COALESCE(SUM(valor), 0) as total FROM despesas WHERE data = ?", [$data])['total'] ?? 0);
$lucro = $receitas - $despesas;

// Veículos por tipo
$porTipo = $db->select(
    "SELECT tipo, COUNT(*) as qtd, COALESCE(SUM(valor), 0) as total 
     FROM veiculos WHERE data_entrada = ? GROUP BY tipo",
    [$data]
);

include __DIR__ . '/../../includes/header.php';
?>

<div class="mb-6 flex justify-between items-center no-print">
    <h2 class="text-2xl font-bold text-gray-800">
        <i class="fas fa-calendar-day mr-2 text-blue-600"></i>
        Relatório Diário
    </h2>
    <div class="flex gap-2">
        <input type="date" value="<?php echo $data; ?>" 
               onchange="window.location.href='?data='+this.value"
               class="px-4 py-2 border rounded-lg">
        <button onclick="printPage()" class="px-4 py-2 bg-blue-600 text-white rounded-lg">
            <i class="fas fa-print mr-2"></i>Imprimir
        </button>
    </div>
</div>

<!-- Cabeçalho para Impressão -->
<div class="hidden print:block mb-6 text-center">
    <h1 class="text-2xl font-bold">Relatório Diário</h1>
    <p class="text-gray-600"><?php echo formatDate($data); ?></p>
</div>

<!-- Cards -->
<div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
    <div class="bg-blue-500 text-white rounded-lg p-4">
        <p class="text-sm opacity-80">Entradas</p>
        <p class="text-3xl font-bold"><?php echo $entradas; ?></p>
    </div>
    <div class="bg-orange-500 text-white rounded-lg p-4">
        <p class="text-sm opacity-80">Saídas</p>
        <p class="text-3xl font-bold"><?php echo $saidas; ?></p>
    </div>
    <div class="bg-green-500 text-white rounded-lg p-4">
        <p class="text-sm opacity-80">Receitas</p>
        <p class="text-xl font-bold"><?php echo formatMoney($receitas); ?></p>
    </div>
    <div class="bg-red-500 text-white rounded-lg p-4">
        <p class="text-sm opacity-80">Despesas</p>
        <p class="text-xl font-bold"><?php echo formatMoney($despesas); ?></p>
    </div>
    <div class="bg-<?php echo $lucro >= 0 ? 'teal' : 'gray'; ?>-500 text-white rounded-lg p-4">
        <p class="text-sm opacity-80">Lucro</p>
        <p class="text-xl font-bold"><?php echo formatMoney($lucro); ?></p>
    </div>
</div>

<!-- Veículos por Tipo -->
<div class="bg-white rounded-lg shadow-lg p-6 mb-6">
    <h3 class="text-xl font-bold text-gray-800 mb-4">Veículos por Tipo</h3>
    <?php if (empty($porTipo)): ?>
        <p class="text-gray-500 text-center py-4">Nenhum veículo registrado</p>
    <?php else: ?>
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-2 text-left">Tipo</th>
                    <th class="px-4 py-2 text-center">Quantidade</th>
                    <th class="px-4 py-2 text-right">Faturamento</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($porTipo as $t): ?>
                    <tr class="border-t">
                        <td class="px-4 py-2"><?php echo getVehicleTypeName($t['tipo']); ?></td>
                        <td class="px-4 py-2 text-center"><?php echo $t['qtd']; ?></td>
                        <td class="px-4 py-2 text-right font-bold text-green-600"><?php echo formatMoney($t['total']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
