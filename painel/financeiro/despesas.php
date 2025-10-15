<?php
/**
 * EstacionaFácil - Controle de Despesas
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

$pageTitle = 'Despesas';
$db = getDB();

// Processar cadastro
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cadastrar'])) {
    $categoria = sanitizeString($_POST['categoria'] ?? '');
    $descricao = sanitizeString($_POST['descricao'] ?? '');
    $valor = moneyToFloat($_POST['valor'] ?? 0);
    $data = dateToMysql($_POST['data'] ?? date('d/m/Y'));
    $recorrente = isset($_POST['recorrente']) ? 1 : 0;
    
    if (!empty($categoria) && !empty($descricao) && $valor > 0 && !empty($data)) {
        $sql = "INSERT INTO despesas (categoria, descricao, valor, data, recorrente) VALUES (?, ?, ?, ?, ?)";
        if ($db->execute($sql, [$categoria, $descricao, $valor, $data, $recorrente])) {
            setFlashMessage('success', 'Despesa registrada com sucesso!');
            redirect('painel/financeiro/despesas.php');
        }
    } else {
        setFlashMessage('error', 'Preencha todos os campos obrigatórios.');
    }
}

// Excluir despesa
if (isset($_GET['excluir'])) {
    $id = (int)$_GET['excluir'];
    if ($db->execute("DELETE FROM despesas WHERE id = ?", [$id])) {
        setFlashMessage('success', 'Despesa excluída com sucesso!');
        redirect('painel/financeiro/despesas.php');
    }
}

// Buscar despesas
$mesAtual = $_GET['mes'] ?? date('Y-m');
$despesas = $db->select(
    "SELECT * FROM despesas WHERE DATE_FORMAT(data, '%Y-%m') = ? ORDER BY data DESC",
    [$mesAtual]
);

$totalDespesas = array_sum(array_column($despesas, 'valor'));

include __DIR__ . '/../../includes/header.php';
?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
    <!-- Formulário -->
    <div class="lg:col-span-2 bg-white rounded-lg shadow-lg p-6">
        <h2 class="text-xl font-bold text-gray-800 mb-4">
            <i class="fas fa-plus-circle mr-2 text-blue-600"></i>
            Registrar Despesa
        </h2>
        
        <form method="POST" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Categoria *</label>
                    <select name="categoria" required class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="">Selecione...</option>
                        <?php foreach (EXPENSE_CATEGORIES as $key => $label): ?>
                            <option value="<?php echo $key; ?>"><?php echo $label; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Valor (R$) *</label>
                    <input type="number" name="valor" step="0.01" min="0" required 
                           class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
                
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Data *</label>
                    <input type="date" name="data" value="<?php echo date('Y-m-d'); ?>" required 
                           class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
                
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Descrição *</label>
                    <input type="text" name="descricao" required placeholder="Ex: Conta de energia elétrica"
                           class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
                
                <div class="md:col-span-2">
                    <label class="flex items-center">
                        <input type="checkbox" name="recorrente" class="mr-2">
                        <span class="text-sm text-gray-700">Despesa recorrente (mensal)</span>
                    </label>
                </div>
            </div>
            
            <button type="submit" name="cadastrar" 
                    class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-lg">
                <i class="fas fa-check mr-2"></i>Registrar Despesa
            </button>
        </form>
    </div>
    
    <!-- Resumo -->
    <div class="bg-gradient-to-br from-red-500 to-red-600 text-white rounded-lg shadow-lg p-6">
        <h3 class="text-lg font-semibold mb-4">Total do Mês</h3>
        <p class="text-4xl font-bold mb-2"><?php echo formatMoney($totalDespesas); ?></p>
        <p class="text-red-100"><?php echo count($despesas); ?> despesa(s)</p>
        
        <div class="mt-6 pt-6 border-t border-red-400">
            <label class="block text-sm mb-2">Filtrar por mês:</label>
            <input type="month" value="<?php echo $mesAtual; ?>" 
                   onchange="window.location.href='?mes='+this.value"
                   class="w-full px-3 py-2 rounded-lg text-gray-800">
        </div>
    </div>
</div>

<!-- Lista de Despesas -->
<div class="bg-white rounded-lg shadow-lg overflow-hidden">
    <div class="p-6 border-b">
        <h3 class="text-xl font-bold text-gray-800">
            <i class="fas fa-list mr-2 text-blue-600"></i>
            Despesas de <?php echo date('m/Y', strtotime($mesAtual . '-01')); ?>
        </h3>
    </div>
    
    <?php if (empty($despesas)): ?>
        <div class="p-12 text-center">
            <i class="fas fa-inbox text-gray-300 text-6xl mb-4"></i>
            <p class="text-gray-600">Nenhuma despesa registrada neste mês</p>
        </div>
    <?php else: ?>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Data</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Categoria</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Descrição</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Valor</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase no-print">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    <?php foreach ($despesas as $d): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-sm"><?php echo formatDate($d['data']); ?></td>
                            <td class="px-6 py-4 text-sm">
                                <?php echo getExpenseCategoryName($d['categoria']); ?>
                                <?php if ($d['recorrente']): ?>
                                    <span class="ml-2 px-2 py-1 bg-blue-100 text-blue-600 text-xs rounded">Recorrente</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 text-sm"><?php echo htmlspecialchars($d['descricao']); ?></td>
                            <td class="px-6 py-4 text-sm font-bold text-red-600"><?php echo formatMoney($d['valor']); ?></td>
                            <td class="px-6 py-4 text-sm no-print">
                                <a href="?excluir=<?php echo $d['id']; ?>" 
                                   onclick="return confirm('Deseja excluir esta despesa?')"
                                   class="text-red-600 hover:text-red-800">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
