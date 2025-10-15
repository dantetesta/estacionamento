<?php
/**
 * EstacionaFácil - Dashboard Principal
 * 
 * Painel principal com resumo do dia, estatísticas e informações importantes
 * 
 * @author Dante Testa (https://dantetesta.com.br)
 * @version 1.0.0
 * @date 14/10/2025 19:54
 */

// Definir constante
define('ESTACIONAFACIL', true);

// Incluir arquivos necessários
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

// Título da página
$pageTitle = 'Dashboard';

// Obter data atual
$hoje = date('Y-m-d');
$mesAtual = date('Y-m');

// Obter banco de dados
$db = getDB();

// ============================================
// ESTATÍSTICAS DO DIA
// ============================================

// Total de veículos no estacionamento (ainda não saíram)
$veiculosNoEstacionamento = $db->selectOne(
    "SELECT COUNT(*) as total FROM veiculos WHERE data_saida IS NULL"
)['total'] ?? 0;

// Total de entradas hoje
$entradasHoje = $db->selectOne(
    "SELECT COUNT(*) as total FROM veiculos WHERE data_entrada = ?",
    [$hoje]
)['total'] ?? 0;

// Total de saídas hoje
$saidasHoje = $db->selectOne(
    "SELECT COUNT(*) as total FROM veiculos WHERE data_saida = ?",
    [$hoje]
)['total'] ?? 0;

// Faturamento do dia
$faturamentoHoje = floatval($db->selectOne(
    "SELECT COALESCE(SUM(valor), 0) as total FROM veiculos WHERE data_saida = ? AND pago = 1",
    [$hoje]
)['total'] ?? 0);

// Despesas do dia
$despesasHoje = floatval($db->selectOne(
    "SELECT COALESCE(SUM(valor), 0) as total FROM despesas WHERE data = ?",
    [$hoje]
)['total'] ?? 0);

// Lucro do dia
$lucroHoje = $faturamentoHoje - $despesasHoje;

// ============================================
// ESTATÍSTICAS DO MÊS
// ============================================

// Faturamento do mês
$faturamentoMes = floatval($db->selectOne(
    "SELECT COALESCE(SUM(valor), 0) as total FROM veiculos 
     WHERE DATE_FORMAT(data_saida, '%Y-%m') = ? AND pago = 1",
    [$mesAtual]
)['total'] ?? 0);

// Despesas do mês
$despesasMes = floatval($db->selectOne(
    "SELECT COALESCE(SUM(valor), 0) as total FROM despesas 
     WHERE DATE_FORMAT(data, '%Y-%m') = ?",
    [$mesAtual]
)['total'] ?? 0);

// Lucro do mês
$lucroMes = $faturamentoMes - $despesasMes;

// ============================================
// VEÍCULOS POR TIPO (HOJE)
// ============================================

$veiculosPorTipo = $db->select(
    "SELECT tipo, COUNT(*) as quantidade, COALESCE(SUM(valor), 0) as faturamento
     FROM veiculos 
     WHERE data_entrada = ?
     GROUP BY tipo
     ORDER BY quantidade DESC",
    [$hoje]
);

// ============================================
// ÚLTIMAS ENTRADAS (5 mais recentes)
// ============================================

$ultimasEntradas = $db->select(
    "SELECT v.*, m.nome as mensalista_nome
     FROM veiculos v
     LEFT JOIN mensalistas m ON v.mensalista_id = m.id
     WHERE v.data_saida IS NULL
     ORDER BY v.data_entrada DESC, v.hora_entrada DESC
     LIMIT 5"
);

// ============================================
// MENSALISTAS COM PAGAMENTO PENDENTE
// ============================================

$mensalistasPendentes = $db->select(
    "SELECT m.*, 
            (SELECT COUNT(*) FROM pagamentos_mensalistas pm 
             WHERE pm.mensalista_id = m.id AND pm.pago = 0) as pagamentos_pendentes
     FROM mensalistas m
     WHERE m.ativo = 1
     HAVING pagamentos_pendentes > 0
     LIMIT 5"
);

// Incluir header
include __DIR__ . '/../includes/header.php';
?>

<!-- Cards de Estatísticas -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 md:gap-6 mb-6">
    <!-- Veículos no Estacionamento -->
    <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg shadow-lg p-6 text-white transform hover:scale-105 transition-transform">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-blue-100 text-sm font-semibold uppercase">No Estacionamento</p>
                <p class="text-4xl font-bold mt-2"><?php echo $veiculosNoEstacionamento; ?></p>
                <p class="text-blue-100 text-sm mt-2">veículos atualmente</p>
            </div>
            <div class="bg-blue-400 bg-opacity-30 rounded-full p-4">
                <i class="fas fa-car text-4xl"></i>
            </div>
        </div>
    </div>
    
    <!-- Entradas Hoje -->
    <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-lg shadow-lg p-6 text-white transform hover:scale-105 transition-transform">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-green-100 text-sm font-semibold uppercase">Entradas Hoje</p>
                <p class="text-4xl font-bold mt-2"><?php echo $entradasHoje; ?></p>
                <p class="text-green-100 text-sm mt-2">veículos entraram</p>
            </div>
            <div class="bg-green-400 bg-opacity-30 rounded-full p-4">
                <i class="fas fa-sign-in-alt text-4xl"></i>
            </div>
        </div>
    </div>
    
    <!-- Saídas Hoje -->
    <div class="bg-gradient-to-br from-orange-500 to-orange-600 rounded-lg shadow-lg p-6 text-white transform hover:scale-105 transition-transform">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-orange-100 text-sm font-semibold uppercase">Saídas Hoje</p>
                <p class="text-4xl font-bold mt-2"><?php echo $saidasHoje; ?></p>
                <p class="text-orange-100 text-sm mt-2">veículos saíram</p>
            </div>
            <div class="bg-orange-400 bg-opacity-30 rounded-full p-4">
                <i class="fas fa-sign-out-alt text-4xl"></i>
            </div>
        </div>
    </div>
    
    <!-- Faturamento Hoje -->
    <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-lg shadow-lg p-6 text-white transform hover:scale-105 transition-transform">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-purple-100 text-sm font-semibold uppercase">Faturamento Hoje</p>
                <p class="text-3xl font-bold mt-2"><?php echo formatMoney($faturamentoHoje); ?></p>
                <p class="text-purple-100 text-sm mt-2">receita do dia</p>
            </div>
            <div class="bg-purple-400 bg-opacity-30 rounded-full p-4">
                <i class="fas fa-dollar-sign text-4xl"></i>
            </div>
        </div>
    </div>
    
    <!-- Despesas Hoje -->
    <div class="bg-gradient-to-br from-red-500 to-red-600 rounded-lg shadow-lg p-6 text-white transform hover:scale-105 transition-transform">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-red-100 text-sm font-semibold uppercase">Despesas Hoje</p>
                <p class="text-3xl font-bold mt-2"><?php echo formatMoney($despesasHoje); ?></p>
                <p class="text-red-100 text-sm mt-2">gastos do dia</p>
            </div>
            <div class="bg-red-400 bg-opacity-30 rounded-full p-4">
                <i class="fas fa-money-bill-wave text-4xl"></i>
            </div>
        </div>
    </div>
    
    <!-- Lucro Hoje -->
    <div class="bg-gradient-to-br from-<?php echo $lucroHoje >= 0 ? 'teal' : 'gray'; ?>-500 to-<?php echo $lucroHoje >= 0 ? 'teal' : 'gray'; ?>-600 rounded-lg shadow-lg p-6 text-white transform hover:scale-105 transition-transform">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-<?php echo $lucroHoje >= 0 ? 'teal' : 'gray'; ?>-100 text-sm font-semibold uppercase">Lucro Hoje</p>
                <p class="text-3xl font-bold mt-2"><?php echo formatMoney($lucroHoje); ?></p>
                <p class="text-<?php echo $lucroHoje >= 0 ? 'teal' : 'gray'; ?>-100 text-sm mt-2">saldo do dia</p>
            </div>
            <div class="bg-<?php echo $lucroHoje >= 0 ? 'teal' : 'gray'; ?>-400 bg-opacity-30 rounded-full p-4">
                <i class="fas fa-chart-line text-4xl"></i>
            </div>
        </div>
    </div>
</div>

<!-- Resumo Mensal -->
<div class="bg-white rounded-lg shadow-lg p-6 mb-6">
    <h3 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
        <i class="fas fa-calendar-alt mr-2 text-blue-600"></i>
        Resumo do Mês (<?php echo date('m/Y'); ?>)
    </h3>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="text-center p-4 bg-green-50 rounded-lg">
            <p class="text-sm text-gray-600 font-semibold">Faturamento</p>
            <p class="text-2xl font-bold text-green-600 mt-2"><?php echo formatMoney($faturamentoMes); ?></p>
        </div>
        <div class="text-center p-4 bg-red-50 rounded-lg">
            <p class="text-sm text-gray-600 font-semibold">Despesas</p>
            <p class="text-2xl font-bold text-red-600 mt-2"><?php echo formatMoney($despesasMes); ?></p>
        </div>
        <div class="text-center p-4 bg-<?php echo $lucroMes >= 0 ? 'blue' : 'gray'; ?>-50 rounded-lg">
            <p class="text-sm text-gray-600 font-semibold">Lucro</p>
            <p class="text-2xl font-bold text-<?php echo $lucroMes >= 0 ? 'blue' : 'gray'; ?>-600 mt-2"><?php echo formatMoney($lucroMes); ?></p>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Veículos por Tipo -->
    <div class="bg-white rounded-lg shadow-lg p-6">
        <h3 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
            <i class="fas fa-chart-pie mr-2 text-blue-600"></i>
            Veículos por Tipo (Hoje)
        </h3>
        
        <?php if (empty($veiculosPorTipo)): ?>
            <div class="text-center py-8 text-gray-500">
                <i class="fas fa-inbox text-4xl mb-2"></i>
                <p>Nenhum veículo registrado hoje</p>
            </div>
        <?php else: ?>
            <div class="space-y-3">
                <?php foreach ($veiculosPorTipo as $tipo): ?>
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-car text-blue-600"></i>
                            </div>
                            <div>
                                <p class="font-semibold text-gray-800"><?php echo getVehicleTypeName($tipo['tipo']); ?></p>
                                <p class="text-sm text-gray-500"><?php echo $tipo['quantidade']; ?> veículo(s)</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="font-bold text-green-600"><?php echo formatMoney($tipo['faturamento']); ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Últimas Entradas -->
    <div class="bg-white rounded-lg shadow-lg p-6">
        <h3 class="text-xl font-bold text-gray-800 mb-4 flex items-center justify-between">
            <span>
                <i class="fas fa-clock mr-2 text-blue-600"></i>
                Últimas Entradas
            </span>
            <a href="<?php echo url('painel/veiculos/listar.php'); ?>" class="text-sm text-blue-600 hover:underline">
                Ver todos
            </a>
        </h3>
        
        <?php if (empty($ultimasEntradas)): ?>
            <div class="text-center py-8 text-gray-500">
                <i class="fas fa-inbox text-4xl mb-2"></i>
                <p>Nenhum veículo no estacionamento</p>
            </div>
        <?php else: ?>
            <div class="space-y-3">
                <?php foreach ($ultimasEntradas as $veiculo): ?>
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition">
                        <div>
                            <p class="font-bold text-gray-800"><?php echo formatPlate($veiculo['placa']); ?></p>
                            <p class="text-sm text-gray-600">
                                <?php echo getVehicleTypeName($veiculo['tipo']); ?>
                                <?php if ($veiculo['mensalista_id']): ?>
                                    <span class="ml-2 px-2 py-1 bg-blue-100 text-blue-600 text-xs rounded">Mensalista</span>
                                <?php endif; ?>
                            </p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm text-gray-600"><?php echo formatDate($veiculo['data_entrada']); ?></p>
                            <p class="text-sm font-semibold text-gray-800"><?php echo formatTime($veiculo['hora_entrada']); ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Alertas e Pendências -->
<?php if (!empty($mensalistasPendentes)): ?>
    <div class="bg-yellow-50 border-l-4 border-yellow-400 p-6 rounded-lg mt-6">
        <h3 class="text-lg font-bold text-yellow-800 mb-3 flex items-center">
            <i class="fas fa-exclamation-triangle mr-2"></i>
            Mensalistas com Pagamento Pendente
        </h3>
        <div class="space-y-2">
            <?php foreach ($mensalistasPendentes as $mensalista): ?>
                <div class="flex items-center justify-between bg-white p-3 rounded">
                    <div>
                        <p class="font-semibold text-gray-800"><?php echo htmlspecialchars($mensalista['nome']); ?></p>
                        <p class="text-sm text-gray-600"><?php echo formatPlate($mensalista['placa']); ?></p>
                    </div>
                    <a href="<?php echo url('painel/mensalistas/listar.php?id=' . $mensalista['id']); ?>" 
                       class="px-4 py-2 bg-yellow-500 hover:bg-yellow-600 text-white rounded-lg text-sm font-semibold">
                        Ver Detalhes
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>

<!-- Ações Rápidas -->
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-6">
    <a href="<?php echo url('painel/veiculos/entrada.php'); ?>" 
       class="bg-green-500 hover:bg-green-600 text-white rounded-lg p-6 text-center transform hover:scale-105 transition">
        <i class="fas fa-plus-circle text-4xl mb-2"></i>
        <p class="font-semibold">Nova Entrada</p>
    </a>
    
    <a href="<?php echo url('painel/veiculos/saida.php'); ?>" 
       class="bg-orange-500 hover:bg-orange-600 text-white rounded-lg p-6 text-center transform hover:scale-105 transition">
        <i class="fas fa-minus-circle text-4xl mb-2"></i>
        <p class="font-semibold">Registrar Saída</p>
    </a>
    
    <a href="<?php echo url('painel/mensalistas/cadastrar.php'); ?>" 
       class="bg-blue-500 hover:bg-blue-600 text-white rounded-lg p-6 text-center transform hover:scale-105 transition">
        <i class="fas fa-user-plus text-4xl mb-2"></i>
        <p class="font-semibold">Novo Mensalista</p>
    </a>
    
    <a href="<?php echo url('painel/relatorios/diario.php'); ?>" 
       class="bg-purple-500 hover:bg-purple-600 text-white rounded-lg p-6 text-center transform hover:scale-105 transition">
        <i class="fas fa-file-alt text-4xl mb-2"></i>
        <p class="font-semibold">Relatórios</p>
    </a>
</div>

<?php
// Incluir footer
include __DIR__ . '/../includes/footer.php';
?>
