<?php
/**
 * EstacionaFácil - Registrar Saída de Veículo
 * 
 * Página para registrar a saída de veículos com cálculo automático do valor
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
$pageTitle = 'Registrar Saída';

// Obter banco de dados
$db = getDB();

// Obter configurações (valores das diárias)
$config = $db->selectOne("SELECT * FROM configuracoes LIMIT 1");

// Variáveis
$veiculo = null;
$placa = '';

// Buscar veículo pela placa
if (isset($_GET['placa']) || isset($_POST['buscar_placa'])) {
    $placa = sanitizePlate($_GET['placa'] ?? $_POST['buscar_placa'] ?? '');
    
    if (!empty($placa)) {
        $veiculo = $db->selectOne(
            "SELECT v.*, m.nome as mensalista_nome, m.valor_mensal
             FROM veiculos v
             LEFT JOIN mensalistas m ON v.mensalista_id = m.id
             WHERE v.placa = ? AND v.data_saida IS NULL
             ORDER BY v.data_entrada DESC, v.hora_entrada DESC
             LIMIT 1",
            [$placa]
        );
        
        if (!$veiculo) {
            setFlashMessage('error', 'Veículo não encontrado ou já saiu do estacionamento.');
        }
    }
}

// Processar saída
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['registrar_saida'])) {
    $veiculoId = (int)($_POST['veiculo_id'] ?? 0);
    $formaPagamento = sanitizeString($_POST['forma_pagamento'] ?? '');
    $valorPago = moneyToFloat($_POST['valor_pago'] ?? 0);
    
    // Validações
    $errors = [];
    
    if (empty($veiculoId)) {
        $errors[] = 'Veículo não identificado.';
    }
    
    if (empty($formaPagamento) || !array_key_exists($formaPagamento, PAYMENT_METHODS)) {
        $errors[] = 'Forma de pagamento inválida.';
    }
    
    if ($valorPago < 0) {
        $errors[] = 'Valor inválido.';
    }
    
    if (empty($errors)) {
        // Atualizar saída
        $sql = "UPDATE veiculos 
                SET data_saida = CURDATE(), 
                    hora_saida = CURTIME(), 
                    valor = ?, 
                    forma_pagamento = ?, 
                    pago = 1 
                WHERE id = ?";
        
        if ($db->execute($sql, [$valorPago, $formaPagamento, $veiculoId])) {
            setFlashMessage('success', 'Saída registrada com sucesso! Valor: ' . formatMoney($valorPago));
            redirect('painel/veiculos/saida.php');
        } else {
            $errors[] = 'Erro ao registrar saída. Tente novamente.';
        }
    }
    
    // Exibir erros
    if (!empty($errors)) {
        setFlashMessage('error', implode('<br>', $errors));
    }
}

// Calcular valor se veículo foi encontrado
$valorCalculado = 0;
$horasEstacionado = 0;

if ($veiculo) {
    // Calcular horas
    $entrada = strtotime($veiculo['data_entrada'] . ' ' . $veiculo['hora_entrada']);
    $saida = time();
    $horasEstacionado = ceil(($saida - $entrada) / 3600);
    
    // Valor baseado no tipo (se não for mensalista)
    if (!$veiculo['mensalista_id']) {
        $valorCalculado = $config['valor_' . $veiculo['tipo']] ?? 0;
    }
}

// Incluir header
include __DIR__ . '/../../includes/header.php';
?>

<div class="max-w-4xl mx-auto">
    <!-- Card de Busca -->
    <div class="bg-white rounded-lg shadow-lg p-6 md:p-8 mb-6">
        <h2 class="text-2xl font-bold text-gray-800 mb-6 flex items-center">
            <i class="fas fa-search mr-3 text-blue-600"></i>
            Buscar Veículo
        </h2>
        
        <form method="POST" class="flex flex-col sm:flex-row gap-3">
            <input 
                type="text" 
                name="buscar_placa" 
                value="<?php echo htmlspecialchars($placa); ?>"
                placeholder="Digite a placa (ABC-1234)"
                maxlength="8"
                oninput="formatPlate(this)"
                required
                class="flex-1 px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent uppercase"
            >
            <button 
                type="submit" 
                class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-lg transition"
            >
                <i class="fas fa-search mr-2"></i>Buscar
            </button>
        </form>
    </div>
    
    <?php if ($veiculo): ?>
        <!-- Card de Informações do Veículo -->
        <div class="bg-white rounded-lg shadow-lg p-6 md:p-8 mb-6">
            <h3 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                <i class="fas fa-car mr-3 text-green-600"></i>
                Informações do Veículo
            </h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                <div class="bg-gray-50 p-4 rounded-lg">
                    <p class="text-sm text-gray-600 mb-1">Placa</p>
                    <p class="text-2xl font-bold text-gray-800"><?php echo formatPlate($veiculo['placa']); ?></p>
                </div>
                
                <div class="bg-gray-50 p-4 rounded-lg">
                    <p class="text-sm text-gray-600 mb-1">Tipo</p>
                    <p class="text-xl font-semibold text-gray-800"><?php echo getVehicleTypeName($veiculo['tipo']); ?></p>
                </div>
                
                <div class="bg-blue-50 p-4 rounded-lg">
                    <p class="text-sm text-blue-600 mb-1">Data de Entrada</p>
                    <p class="text-lg font-semibold text-blue-800"><?php echo formatDate($veiculo['data_entrada']); ?></p>
                </div>
                
                <div class="bg-blue-50 p-4 rounded-lg">
                    <p class="text-sm text-blue-600 mb-1">Hora de Entrada</p>
                    <p class="text-lg font-semibold text-blue-800"><?php echo formatTime($veiculo['hora_entrada']); ?></p>
                </div>
                
                <div class="bg-purple-50 p-4 rounded-lg">
                    <p class="text-sm text-purple-600 mb-1">Tempo Estacionado</p>
                    <p class="text-lg font-semibold text-purple-800">
                        <?php echo $horasEstacionado; ?> hora(s)
                    </p>
                </div>
                
                <?php if ($veiculo['mensalista_id']): ?>
                    <div class="bg-green-50 p-4 rounded-lg">
                        <p class="text-sm text-green-600 mb-1">Mensalista</p>
                        <p class="text-lg font-semibold text-green-800"><?php echo htmlspecialchars($veiculo['mensalista_nome']); ?></p>
                    </div>
                <?php else: ?>
                    <div class="bg-green-50 p-4 rounded-lg">
                        <p class="text-sm text-green-600 mb-1">Valor a Cobrar</p>
                        <p class="text-2xl font-bold text-green-800"><?php echo formatMoney($valorCalculado); ?></p>
                    </div>
                <?php endif; ?>
            </div>
            
            <?php if ($veiculo['observacoes']): ?>
                <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded-r-lg">
                    <p class="text-sm text-yellow-700 font-semibold mb-1">Observações:</p>
                    <p class="text-yellow-800"><?php echo htmlspecialchars($veiculo['observacoes']); ?></p>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Formulário de Saída -->
        <div class="bg-white rounded-lg shadow-lg p-6 md:p-8">
            <h3 class="text-xl font-bold text-gray-800 mb-6 flex items-center">
                <i class="fas fa-sign-out-alt mr-3 text-orange-600"></i>
                Registrar Saída
            </h3>
            
            <form method="POST" id="saidaForm" class="space-y-6">
                <input type="hidden" name="veiculo_id" value="<?php echo $veiculo['id']; ?>">
                <input type="hidden" name="registrar_saida" value="1">
                
                <?php if ($veiculo['mensalista_id']): ?>
                    <!-- Mensalista - Sem cobrança -->
                    <div class="bg-green-50 border border-green-200 rounded-lg p-6 text-center">
                        <i class="fas fa-check-circle text-green-600 text-5xl mb-3"></i>
                        <h4 class="text-xl font-bold text-green-800 mb-2">Mensalista Identificado</h4>
                        <p class="text-green-700">
                            Este veículo pertence ao mensalista <strong><?php echo htmlspecialchars($veiculo['mensalista_nome']); ?></strong>
                        </p>
                        <p class="text-green-600 mt-2">Saída liberada sem cobrança</p>
                        
                        <input type="hidden" name="valor_pago" value="0">
                        <input type="hidden" name="forma_pagamento" value="mensalista">
                    </div>
                    
                    <button 
                        type="submit" 
                        class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-4 px-6 rounded-lg transition duration-200 transform hover:scale-105"
                    >
                        <i class="fas fa-check mr-2"></i>Liberar Saída (Mensalista)
                    </button>
                    
                <?php else: ?>
                    <!-- Diarista - Com cobrança -->
                    <div>
                        <label for="valor_pago" class="block text-gray-700 font-semibold mb-2">
                            <i class="fas fa-dollar-sign mr-2 text-blue-600"></i>Valor a Cobrar *
                        </label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-600 font-semibold">R$</span>
                            <input 
                                type="number" 
                                id="valor_pago" 
                                name="valor_pago" 
                                value="<?php echo number_format($valorCalculado, 2, '.', ''); ?>"
                                step="0.01"
                                min="0"
                                required
                                class="w-full pl-12 pr-4 py-4 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-2xl font-bold text-green-600"
                            >
                        </div>
                        <p class="text-sm text-gray-500 mt-2">
                            Valor sugerido baseado no tipo de veículo. Você pode alterar se necessário.
                        </p>
                    </div>
                    
                    <div>
                        <label class="block text-gray-700 font-semibold mb-3">
                            <i class="fas fa-credit-card mr-2 text-blue-600"></i>Forma de Pagamento *
                        </label>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                            <?php foreach (PAYMENT_METHODS as $key => $label): ?>
                                <label class="relative">
                                    <input 
                                        type="radio" 
                                        name="forma_pagamento" 
                                        value="<?php echo $key; ?>" 
                                        required
                                        class="peer sr-only"
                                    >
                                    <div class="border-2 border-gray-300 rounded-lg p-4 cursor-pointer hover:border-blue-500 peer-checked:border-blue-600 peer-checked:bg-blue-50 transition text-center">
                                        <i class="fas fa-<?php echo $key === 'dinheiro' ? 'money-bill-wave' : ($key === 'pix' ? 'qrcode' : 'credit-card'); ?> text-2xl text-gray-600 mb-2"></i>
                                        <p class="font-semibold text-gray-800 text-sm"><?php echo $label; ?></p>
                                    </div>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <div class="flex flex-col sm:flex-row gap-3">
                        <button 
                            type="submit" 
                            class="flex-1 bg-orange-600 hover:bg-orange-700 text-white font-bold py-4 px-6 rounded-lg transition duration-200 transform hover:scale-105"
                        >
                            <i class="fas fa-check mr-2"></i>Confirmar Saída e Pagamento
                        </button>
                        <a 
                            href="<?php echo url('painel/veiculos/saida.php'); ?>" 
                            class="flex-1 bg-gray-500 hover:bg-gray-600 text-white font-bold py-4 px-6 rounded-lg transition duration-200 text-center"
                        >
                            <i class="fas fa-times mr-2"></i>Cancelar
                        </a>
                    </div>
                <?php endif; ?>
            </form>
        </div>
        
    <?php else: ?>
        <!-- Veículos no Estacionamento -->
        <?php
        $veiculosNoEstacionamento = $db->select(
            "SELECT v.*, m.nome as mensalista_nome
             FROM veiculos v
             LEFT JOIN mensalistas m ON v.mensalista_id = m.id
             WHERE v.data_saida IS NULL
             ORDER BY v.data_entrada DESC, v.hora_entrada DESC
             LIMIT 10"
        );
        ?>
        
        <?php if (!empty($veiculosNoEstacionamento)): ?>
            <div class="bg-white rounded-lg shadow-lg p-6 md:p-8">
                <h3 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-list mr-3 text-blue-600"></i>
                    Veículos no Estacionamento
                </h3>
                
                <div class="space-y-3">
                    <?php foreach ($veiculosNoEstacionamento as $v): ?>
                        <a href="<?php echo url('painel/veiculos/saida.php?placa=' . urlencode($v['placa'])); ?>" 
                           class="block p-4 bg-gray-50 hover:bg-blue-50 rounded-lg transition border border-gray-200 hover:border-blue-300">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-4">
                                    <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                                        <i class="fas fa-car text-blue-600 text-xl"></i>
                                    </div>
                                    <div>
                                        <p class="font-bold text-gray-800 text-lg"><?php echo formatPlate($v['placa']); ?></p>
                                        <p class="text-sm text-gray-600">
                                            <?php echo getVehicleTypeName($v['tipo']); ?>
                                            <?php if ($v['mensalista_id']): ?>
                                                <span class="ml-2 px-2 py-1 bg-green-100 text-green-600 text-xs rounded">Mensalista</span>
                                            <?php endif; ?>
                                        </p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm text-gray-600"><?php echo formatDate($v['data_entrada']); ?></p>
                                    <p class="font-semibold text-gray-800"><?php echo formatTime($v['hora_entrada']); ?></p>
                                </div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
                
                <div class="mt-4 text-center">
                    <a href="<?php echo url('painel/veiculos/listar.php'); ?>" class="text-blue-600 hover:underline">
                        Ver todos os veículos <i class="fas fa-arrow-right ml-1"></i>
                    </a>
                </div>
            </div>
        <?php else: ?>
            <div class="bg-white rounded-lg shadow-lg p-12 text-center">
                <i class="fas fa-inbox text-gray-300 text-6xl mb-4"></i>
                <h3 class="text-xl font-bold text-gray-600 mb-2">Nenhum Veículo no Estacionamento</h3>
                <p class="text-gray-500 mb-6">Não há veículos para registrar saída no momento</p>
                <a href="<?php echo url('painel/veiculos/entrada.php'); ?>" 
                   class="inline-block px-6 py-3 bg-green-600 hover:bg-green-700 text-white font-bold rounded-lg">
                    <i class="fas fa-plus mr-2"></i>Registrar Entrada
                </a>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<script>
// Formatar valor monetário ao digitar
document.getElementById('valor_pago')?.addEventListener('input', function(e) {
    let value = e.target.value;
    // Permitir apenas números e ponto decimal
    e.target.value = value.replace(/[^0-9.]/g, '');
});

// Confirmação antes de registrar saída
document.getElementById('saidaForm')?.addEventListener('submit', function(e) {
    const valor = document.getElementById('valor_pago')?.value || 0;
    const isMensalista = <?php echo $veiculo && $veiculo['mensalista_id'] ? 'true' : 'false'; ?>;
    
    if (!isMensalista) {
        const confirmMsg = 'Confirmar saída com pagamento de R$ ' + parseFloat(valor).toFixed(2).replace('.', ',') + '?';
        if (!confirm(confirmMsg)) {
            e.preventDefault();
        }
    }
});
</script>

<?php
// Incluir footer
include __DIR__ . '/../../includes/footer.php';
?>
