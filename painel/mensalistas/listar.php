<?php
/**
 * EstacionaFácil - Listar Mensalistas
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

$pageTitle = 'Listar Mensalistas';
$db = getDB();

// Processar ações
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $action = $_GET['action'];
    
    if ($action === 'toggle') {
        $db->execute("UPDATE mensalistas SET ativo = NOT ativo WHERE id = ?", [$id]);
        setFlashMessage('success', 'Status atualizado com sucesso!');
        redirect('painel/mensalistas/listar.php');
    }
}

// Buscar mensalistas
$filtroStatus = $_GET['status'] ?? 'ativos';
$whereStatus = $filtroStatus === 'ativos' ? 'WHERE m.ativo = 1' : ($filtroStatus === 'inativos' ? 'WHERE m.ativo = 0' : '');

$mensalistas = $db->select("
    SELECT m.*, 
           (SELECT COUNT(*) FROM veiculos v WHERE v.mensalista_id = m.id) as total_entradas,
           (SELECT COUNT(*) FROM veiculos v WHERE v.mensalista_id = m.id AND v.data_saida IS NULL) as no_estacionamento
    FROM mensalistas m
    $whereStatus
    ORDER BY m.nome
");

include __DIR__ . '/../../includes/header.php';
?>

<div class="mb-6">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <h2 class="text-2xl font-bold text-gray-800">
            <i class="fas fa-users mr-2 text-blue-600"></i>
            Mensalistas (<?php echo count($mensalistas); ?>)
        </h2>
        <div class="flex gap-2">
            <a href="<?php echo url('painel/mensalistas/cadastrar.php'); ?>" 
               class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-semibold">
                <i class="fas fa-plus mr-2"></i>Novo Mensalista
            </a>
        </div>
    </div>
</div>

<!-- Filtros -->
<div class="bg-white rounded-lg shadow-lg p-4 mb-6">
    <div class="flex gap-2">
        <a href="?status=todos" class="px-4 py-2 <?php echo $filtroStatus === 'todos' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700'; ?> rounded-lg">
            Todos
        </a>
        <a href="?status=ativos" class="px-4 py-2 <?php echo $filtroStatus === 'ativos' ? 'bg-green-600 text-white' : 'bg-gray-200 text-gray-700'; ?> rounded-lg">
            Ativos
        </a>
        <a href="?status=inativos" class="px-4 py-2 <?php echo $filtroStatus === 'inativos' ? 'bg-red-600 text-white' : 'bg-gray-200 text-gray-700'; ?> rounded-lg">
            Inativos
        </a>
    </div>
</div>

<!-- Lista -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    <?php foreach ($mensalistas as $m): ?>
        <div class="bg-white rounded-lg shadow-lg p-6 hover:shadow-xl transition">
            <div class="flex items-start justify-between mb-4">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center mr-3">
                        <i class="fas fa-user text-blue-600 text-xl"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-gray-800"><?php echo htmlspecialchars($m['nome']); ?></h3>
                        <p class="text-sm text-gray-600"><?php echo formatPlate($m['placa']); ?></p>
                    </div>
                </div>
                <span class="px-2 py-1 <?php echo $m['ativo'] ? 'bg-green-100 text-green-600' : 'bg-red-100 text-red-600'; ?> text-xs rounded">
                    <?php echo $m['ativo'] ? 'Ativo' : 'Inativo'; ?>
                </span>
            </div>
            
            <div class="space-y-2 mb-4">
                <?php if ($m['telefone']): ?>
                    <p class="text-sm text-gray-600">
                        <i class="fas fa-phone mr-2"></i><?php echo formatPhone($m['telefone']); ?>
                    </p>
                <?php endif; ?>
                <?php if ($m['email']): ?>
                    <p class="text-sm text-gray-600">
                        <i class="fas fa-envelope mr-2"></i><?php echo htmlspecialchars($m['email']); ?>
                    </p>
                <?php endif; ?>
                <p class="text-sm text-gray-600">
                    <i class="fas fa-dollar-sign mr-2"></i><?php echo formatMoney($m['valor_mensal']); ?>/mês
                </p>
                <p class="text-sm text-gray-600">
                    <i class="fas fa-calendar mr-2"></i>Vencimento: dia <?php echo $m['dia_vencimento']; ?>
                </p>
            </div>
            
            <div class="grid grid-cols-2 gap-2 mb-4">
                <div class="bg-blue-50 p-2 rounded text-center">
                    <p class="text-xs text-blue-600">Entradas</p>
                    <p class="text-lg font-bold text-blue-800"><?php echo $m['total_entradas']; ?></p>
                </div>
                <div class="bg-green-50 p-2 rounded text-center">
                    <p class="text-xs text-green-600">Estacionado</p>
                    <p class="text-lg font-bold text-green-800"><?php echo $m['no_estacionamento']; ?></p>
                </div>
            </div>
            
            <div class="flex gap-2">
                <a href="?action=toggle&id=<?php echo $m['id']; ?>" 
                   onclick="return confirm('Deseja alterar o status deste mensalista?')"
                   class="flex-1 px-3 py-2 <?php echo $m['ativo'] ? 'bg-red-500 hover:bg-red-600' : 'bg-green-500 hover:bg-green-600'; ?> text-white rounded text-sm text-center">
                    <i class="fas fa-<?php echo $m['ativo'] ? 'times' : 'check'; ?> mr-1"></i>
                    <?php echo $m['ativo'] ? 'Desativar' : 'Ativar'; ?>
                </a>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<?php if (empty($mensalistas)): ?>
    <div class="bg-white rounded-lg shadow-lg p-12 text-center">
        <i class="fas fa-inbox text-gray-300 text-6xl mb-4"></i>
        <h3 class="text-xl font-bold text-gray-600 mb-2">Nenhum Mensalista Encontrado</h3>
        <p class="text-gray-500 mb-6">Cadastre o primeiro mensalista</p>
        <a href="<?php echo url('painel/mensalistas/cadastrar.php'); ?>" 
           class="inline-block px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-lg">
            <i class="fas fa-plus mr-2"></i>Cadastrar Mensalista
        </a>
    </div>
<?php endif; ?>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
