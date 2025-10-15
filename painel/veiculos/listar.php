<?php
/**
 * EstacionaFácil - Listar Veículos
 * 
 * Página para listar e filtrar veículos com histórico completo
 * 
 * @author Dante Testa (https://dantetesta.com.br)
 * @version 1.0.0
 * @date 14/10/2025 19:54
 */

// Definir constante
define('ESTACIONAFACIL', true);

// Incluir arquivos necessários
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';

// Título da página
$pageTitle = 'Listar Veículos';

// Obter banco de dados
$db = getDB();

// Processar exclusão de veículo
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['deletar_veiculo'])) {
    $veiculoId = (int)($_POST['veiculo_id'] ?? 0);
    $senhaConfirmacao = $_POST['senha_confirmacao'] ?? '';
    
    // Validar senha do usuário logado
    $usuario = $db->selectOne(
        "SELECT * FROM usuarios WHERE id = ?",
        [$_SESSION['user_id']]
    );
    
    if ($usuario && password_verify($senhaConfirmacao, $usuario['senha'])) {
        // Senha correta, deletar veículo
        if ($db->execute("DELETE FROM veiculos WHERE id = ?", [$veiculoId])) {
            setFlashMessage('success', 'Veículo deletado com sucesso!');
        } else {
            setFlashMessage('error', 'Erro ao deletar veículo.');
        }
    } else {
        setFlashMessage('error', 'Senha incorreta! O veículo não foi deletado.');
    }
    
    redirect('painel/veiculos/listar.php');
}

// Filtros
$filtroStatus = $_GET['status'] ?? 'todos';
$filtroPlaca = sanitizePlate($_GET['placa'] ?? '');
$filtroTipo = $_GET['tipo'] ?? '';
$filtroDataInicio = $_GET['data_inicio'] ?? '';
$filtroDataFim = $_GET['data_fim'] ?? '';
$filtroPago = $_GET['pago'] ?? '';

// Paginação
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$perPage = ITEMS_PER_PAGE;
$offset = ($page - 1) * $perPage;

// Construir query
$where = [];
$params = [];

if ($filtroStatus === 'estacionamento') {
    $where[] = "v.data_saida IS NULL";
} elseif ($filtroStatus === 'finalizados') {
    $where[] = "v.data_saida IS NOT NULL";
}

if (!empty($filtroPlaca)) {
    $where[] = "v.placa LIKE ?";
    $params[] = "%$filtroPlaca%";
}

if (!empty($filtroTipo)) {
    $where[] = "v.tipo = ?";
    $params[] = $filtroTipo;
}

if (!empty($filtroDataInicio)) {
    $where[] = "v.data_entrada >= ?";
    $params[] = dateToMysql($filtroDataInicio);
}

if (!empty($filtroDataFim)) {
    $where[] = "v.data_entrada <= ?";
    $params[] = dateToMysql($filtroDataFim);
}

if ($filtroPago !== '') {
    $where[] = "v.pago = ?";
    $params[] = (int)$filtroPago;
}

$whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

// Contar total de registros
$totalSql = "SELECT COUNT(*) as total FROM veiculos v $whereClause";
$total = $db->selectOne($totalSql, $params)['total'] ?? 0;
$totalPages = ceil($total / $perPage);

// Buscar veículos
$sql = "SELECT v.*, m.nome as mensalista_nome
        FROM veiculos v
        LEFT JOIN mensalistas m ON v.mensalista_id = m.id
        $whereClause
        ORDER BY v.data_entrada DESC, v.hora_entrada DESC
        LIMIT $perPage OFFSET $offset";

$veiculos = $db->select($sql, $params);

// Estatísticas rápidas
$stats = [
    'no_estacionamento' => $db->selectOne("SELECT COUNT(*) as total FROM veiculos WHERE data_saida IS NULL")['total'] ?? 0,
    'total_hoje' => $db->selectOne("SELECT COUNT(*) as total FROM veiculos WHERE data_entrada = CURDATE()")['total'] ?? 0,
    'faturamento_hoje' => $db->selectOne("SELECT COALESCE(SUM(valor), 0) as total FROM veiculos WHERE data_saida = CURDATE() AND pago = 1")['total'] ?? 0
];

// Incluir header
include __DIR__ . '/../../includes/header.php';
?>

<!-- Estatísticas Rápidas -->
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
    <div class="bg-blue-500 text-white rounded-lg shadow-lg p-4">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-blue-100 text-sm">No Estacionamento</p>
                <p class="text-3xl font-bold"><?php echo $stats['no_estacionamento']; ?></p>
            </div>
            <i class="fas fa-car text-4xl text-blue-300"></i>
        </div>
    </div>
    
    <div class="bg-green-500 text-white rounded-lg shadow-lg p-4">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-green-100 text-sm">Entradas Hoje</p>
                <p class="text-3xl font-bold"><?php echo $stats['total_hoje']; ?></p>
            </div>
            <i class="fas fa-sign-in-alt text-4xl text-green-300"></i>
        </div>
    </div>
    
    <div class="bg-purple-500 text-white rounded-lg shadow-lg p-4">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-purple-100 text-sm">Faturamento Hoje</p>
                <p class="text-2xl font-bold"><?php echo formatMoney($stats['faturamento_hoje']); ?></p>
            </div>
            <i class="fas fa-dollar-sign text-4xl text-purple-300"></i>
        </div>
    </div>
</div>

<!-- Card de Filtros -->
<div class="bg-white rounded-lg shadow-lg p-6 mb-6">
    <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
        <i class="fas fa-filter mr-2 text-blue-600"></i>
        Filtros
    </h2>
    
    <form method="GET" class="space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Status -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Status</label>
                <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    <option value="todos" <?php echo $filtroStatus === 'todos' ? 'selected' : ''; ?>>Todos</option>
                    <option value="estacionamento" <?php echo $filtroStatus === 'estacionamento' ? 'selected' : ''; ?>>No Estacionamento</option>
                    <option value="finalizados" <?php echo $filtroStatus === 'finalizados' ? 'selected' : ''; ?>>Finalizados</option>
                </select>
            </div>
            
            <!-- Placa -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Placa</label>
                <input 
                    type="text" 
                    name="placa" 
                    value="<?php echo htmlspecialchars($filtroPlaca); ?>"
                    placeholder="ABC-1234"
                    maxlength="8"
                    oninput="formatPlate(this)"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 uppercase"
                >
            </div>
            
            <!-- Tipo -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Tipo</label>
                <select name="tipo" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    <option value="">Todos</option>
                    <?php foreach (VEHICLE_TYPES as $key => $label): ?>
                        <option value="<?php echo $key; ?>" <?php echo $filtroTipo === $key ? 'selected' : ''; ?>>
                            <?php echo $label; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <!-- Pagamento -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Pagamento</label>
                <select name="pago" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    <option value="">Todos</option>
                    <option value="1" <?php echo $filtroPago === '1' ? 'selected' : ''; ?>>Pago</option>
                    <option value="0" <?php echo $filtroPago === '0' ? 'selected' : ''; ?>>Pendente</option>
                </select>
            </div>
            
            <!-- Data Início -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Data Início</label>
                <input 
                    type="date" 
                    name="data_inicio" 
                    value="<?php echo htmlspecialchars($filtroDataInicio); ?>"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                >
            </div>
            
            <!-- Data Fim -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Data Fim</label>
                <input 
                    type="date" 
                    name="data_fim" 
                    value="<?php echo htmlspecialchars($filtroDataFim); ?>"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                >
            </div>
        </div>
        
        <div class="flex flex-col sm:flex-row gap-3">
            <button type="submit" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg">
                <i class="fas fa-search mr-2"></i>Filtrar
            </button>
            <a href="<?php echo url('painel/veiculos/listar.php'); ?>" class="px-6 py-2 bg-gray-500 hover:bg-gray-600 text-white font-semibold rounded-lg text-center">
                <i class="fas fa-redo mr-2"></i>Limpar Filtros
            </a>
            <button type="button" onclick="printPage()" class="px-6 py-2 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg">
                <i class="fas fa-print mr-2"></i>Imprimir
            </button>
        </div>
    </form>
</div>

<!-- Lista de Veículos -->
<div class="bg-white rounded-lg shadow-lg overflow-hidden">
    <div class="p-6 border-b border-gray-200">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-bold text-gray-800">
                <i class="fas fa-list mr-2 text-blue-600"></i>
                Veículos (<?php echo $total; ?> registro<?php echo $total != 1 ? 's' : ''; ?>)
            </h2>
            <div class="flex gap-2">
                <a href="<?php echo url('painel/veiculos/entrada.php'); ?>" 
                   class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg text-sm font-semibold">
                    <i class="fas fa-plus mr-2"></i>Nova Entrada
                </a>
            </div>
        </div>
    </div>
    
    <?php if (empty($veiculos)): ?>
        <div class="p-12 text-center">
            <i class="fas fa-inbox text-gray-300 text-6xl mb-4"></i>
            <h3 class="text-xl font-bold text-gray-600 mb-2">Nenhum Veículo Encontrado</h3>
            <p class="text-gray-500">Tente ajustar os filtros ou registre uma nova entrada</p>
        </div>
    <?php else: ?>
        <!-- Tabela Desktop -->
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full" id="veiculosTable">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Placa</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Tipo</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Entrada</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Saída</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Valor</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase no-print">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php foreach ($veiculos as $veiculo): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center mr-3">
                                        <i class="fas fa-car text-blue-600"></i>
                                    </div>
                                    <div>
                                        <p class="font-bold text-gray-800"><?php echo formatPlate($veiculo['placa']); ?></p>
                                        <?php if ($veiculo['mensalista_id']): ?>
                                            <p class="text-xs text-green-600">
                                                <i class="fas fa-user mr-1"></i><?php echo htmlspecialchars($veiculo['mensalista_nome']); ?>
                                            </p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">
                                <?php echo getVehicleTypeName($veiculo['tipo']); ?>
                            </td>
                            <td class="px-6 py-4 text-sm">
                                <p class="text-gray-800"><?php echo formatDate($veiculo['data_entrada']); ?></p>
                                <p class="text-gray-500"><?php echo formatTime($veiculo['hora_entrada']); ?></p>
                            </td>
                            <td class="px-6 py-4 text-sm">
                                <?php if ($veiculo['data_saida']): ?>
                                    <p class="text-gray-800"><?php echo formatDate($veiculo['data_saida']); ?></p>
                                    <p class="text-gray-500"><?php echo formatTime($veiculo['hora_saida']); ?></p>
                                <?php else: ?>
                                    <span class="px-2 py-1 bg-blue-100 text-blue-600 text-xs rounded">No estacionamento</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4">
                                <?php if ($veiculo['valor']): ?>
                                    <p class="font-bold text-green-600"><?php echo formatMoney($veiculo['valor']); ?></p>
                                    <?php if ($veiculo['forma_pagamento']): ?>
                                        <p class="text-xs text-gray-500"><?php echo getPaymentMethodName($veiculo['forma_pagamento']); ?></p>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="text-gray-400">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4">
                                <?php if ($veiculo['data_saida']): ?>
                                    <?php if ($veiculo['pago']): ?>
                                        <span class="px-2 py-1 bg-green-100 text-green-600 text-xs rounded font-semibold">
                                            <i class="fas fa-check-circle mr-1"></i>Pago
                                        </span>
                                    <?php else: ?>
                                        <span class="px-2 py-1 bg-red-100 text-red-600 text-xs rounded font-semibold">
                                            <i class="fas fa-times-circle mr-1"></i>Pendente
                                        </span>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="px-2 py-1 bg-blue-100 text-blue-600 text-xs rounded font-semibold">
                                        <i class="fas fa-parking mr-1"></i>Estacionado
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 no-print">
                                <div class="flex items-center gap-3">
                                    <?php if (!$veiculo['data_saida']): ?>
                                        <a href="<?php echo url('painel/veiculos/saida.php?placa=' . urlencode($veiculo['placa'])); ?>" 
                                           class="text-orange-600 hover:text-orange-800 font-semibold text-sm">
                                            <i class="fas fa-sign-out-alt mr-1"></i>Saída
                                        </a>
                                    <?php endif; ?>
                                    <button 
                                        onclick="abrirModalDeletar(<?php echo $veiculo['id']; ?>, '<?php echo htmlspecialchars(formatPlate($veiculo['placa'])); ?>')"
                                        class="text-red-600 hover:text-red-800 font-semibold text-sm"
                                        title="Deletar registro">
                                        <i class="fas fa-trash mr-1"></i>Deletar
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Cards Mobile -->
        <div class="md:hidden divide-y divide-gray-200">
            <?php foreach ($veiculos as $veiculo): ?>
                <div class="p-4">
                    <div class="flex items-start justify-between mb-3">
                        <div class="flex items-center">
                            <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center mr-3">
                                <i class="fas fa-car text-blue-600 text-xl"></i>
                            </div>
                            <div>
                                <p class="font-bold text-gray-800 text-lg"><?php echo formatPlate($veiculo['placa']); ?></p>
                                <p class="text-sm text-gray-600"><?php echo getVehicleTypeName($veiculo['tipo']); ?></p>
                            </div>
                        </div>
                        <?php if (!$veiculo['data_saida']): ?>
                            <span class="px-2 py-1 bg-blue-100 text-blue-600 text-xs rounded">Estacionado</span>
                        <?php elseif ($veiculo['pago']): ?>
                            <span class="px-2 py-1 bg-green-100 text-green-600 text-xs rounded">Pago</span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-2 text-sm mb-3">
                        <div>
                            <p class="text-gray-500">Entrada:</p>
                            <p class="font-semibold"><?php echo formatDate($veiculo['data_entrada']); ?> <?php echo formatTime($veiculo['hora_entrada']); ?></p>
                        </div>
                        <?php if ($veiculo['data_saida']): ?>
                            <div>
                                <p class="text-gray-500">Saída:</p>
                                <p class="font-semibold"><?php echo formatDate($veiculo['data_saida']); ?> <?php echo formatTime($veiculo['hora_saida']); ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <?php if ($veiculo['valor']): ?>
                        <div class="mb-3">
                            <p class="text-sm text-gray-500">Valor:</p>
                            <p class="text-lg font-bold text-green-600"><?php echo formatMoney($veiculo['valor']); ?></p>
                        </div>
                    <?php endif; ?>
                    
                    <div class="flex gap-2">
                        <?php if (!$veiculo['data_saida']): ?>
                            <a href="<?php echo url('painel/veiculos/saida.php?placa=' . urlencode($veiculo['placa'])); ?>" 
                               class="flex-1 text-center px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white rounded-lg font-semibold">
                                <i class="fas fa-sign-out-alt mr-2"></i>Saída
                            </a>
                        <?php endif; ?>
                        <button 
                            onclick="abrirModalDeletar(<?php echo $veiculo['id']; ?>, '<?php echo htmlspecialchars(formatPlate($veiculo['placa'])); ?>')"
                            class="<?php echo !$veiculo['data_saida'] ? 'flex-none' : 'flex-1'; ?> px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg font-semibold">
                            <i class="fas fa-trash mr-2"></i>Deletar
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Paginação -->
        <?php if ($totalPages > 1): ?>
            <div class="p-6 border-t border-gray-200">
                <div class="flex items-center justify-between">
                    <p class="text-sm text-gray-600">
                        Página <?php echo $page; ?> de <?php echo $totalPages; ?>
                    </p>
                    
                    <div class="flex gap-2">
                        <?php if ($page > 1): ?>
                            <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>" 
                               class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg">
                                <i class="fas fa-chevron-left"></i>
                            </a>
                        <?php endif; ?>
                        
                        <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                            <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>" 
                               class="px-4 py-2 <?php echo $i === $page ? 'bg-blue-600 text-white' : 'bg-gray-200 hover:bg-gray-300 text-gray-700'; ?> rounded-lg">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>
                        
                        <?php if ($page < $totalPages): ?>
                            <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>" 
                               class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg">
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<!-- Modal de Confirmação de Exclusão -->
<div id="modalDeletar" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-2xl max-w-md w-full p-6 transform transition-all">
        <div class="text-center mb-6">
            <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-red-100 mb-4">
                <i class="fas fa-exclamation-triangle text-red-600 text-3xl"></i>
            </div>
            <h3 class="text-2xl font-bold text-gray-900 mb-2">Confirmar Exclusão</h3>
            <p class="text-gray-600 mb-4">
                Você está prestes a deletar o registro do veículo:
            </p>
            <p class="text-xl font-bold text-red-600" id="placaDeletar"></p>
        </div>
        
        <form method="POST" id="formDeletar">
            <input type="hidden" name="deletar_veiculo" value="1">
            <input type="hidden" name="veiculo_id" id="veiculoIdDeletar">
            
            <div class="mb-6">
                <label for="senha_confirmacao" class="block text-gray-700 font-semibold mb-2">
                    <i class="fas fa-lock mr-2 text-red-600"></i>
                    Digite sua senha para confirmar *
                </label>
                <input 
                    type="password" 
                    id="senha_confirmacao" 
                    name="senha_confirmacao" 
                    required
                    placeholder="Sua senha de acesso"
                    class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent"
                    autofocus
                >
                <p class="text-sm text-gray-500 mt-2">
                    <i class="fas fa-info-circle mr-1"></i>
                    Esta ação não pode ser desfeita!
                </p>
            </div>
            
            <div class="flex gap-3">
                <button 
                    type="button" 
                    onclick="fecharModalDeletar()"
                    class="flex-1 px-6 py-3 bg-gray-500 hover:bg-gray-600 text-white font-bold rounded-lg transition">
                    <i class="fas fa-times mr-2"></i>Cancelar
                </button>
                <button 
                    type="submit" 
                    class="flex-1 px-6 py-3 bg-red-600 hover:bg-red-700 text-white font-bold rounded-lg transition">
                    <i class="fas fa-trash mr-2"></i>Deletar
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Abrir modal de deletar
function abrirModalDeletar(veiculoId, placa) {
    document.getElementById('veiculoIdDeletar').value = veiculoId;
    document.getElementById('placaDeletar').textContent = placa;
    document.getElementById('senha_confirmacao').value = '';
    document.getElementById('modalDeletar').classList.remove('hidden');
    
    // Focus no campo de senha
    setTimeout(() => {
        document.getElementById('senha_confirmacao').focus();
    }, 100);
}

// Fechar modal de deletar
function fecharModalDeletar() {
    document.getElementById('modalDeletar').classList.add('hidden');
}

// Fechar modal ao clicar fora
document.getElementById('modalDeletar').addEventListener('click', function(e) {
    if (e.target === this) {
        fecharModalDeletar();
    }
});

// Fechar modal com ESC
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        fecharModalDeletar();
    }
});

// Validação do formulário
document.getElementById('formDeletar').addEventListener('submit', function(e) {
    const senha = document.getElementById('senha_confirmacao').value;
    
    if (senha.length < 3) {
        e.preventDefault();
        alert('Digite sua senha para confirmar a exclusão.');
        return false;
    }
    
    // Confirmar novamente
    if (!confirm('Tem certeza que deseja deletar este registro? Esta ação não pode ser desfeita!')) {
        e.preventDefault();
        return false;
    }
});
</script>

<?php
// Incluir footer
include __DIR__ . '/../../includes/footer.php';
?>
