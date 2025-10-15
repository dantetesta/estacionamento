<?php
/**
 * EstacionaFácil - Registrar Entrada de Veículo
 * 
 * Página para registrar a entrada de veículos no estacionamento
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
$pageTitle = 'Registrar Entrada';

// Obter banco de dados
$db = getDB();

// Obter configurações (valores das diárias)
$config = $db->selectOne("SELECT * FROM configuracoes LIMIT 1");

// Buscar mensalistas ativos
$mensalistas = $db->select("SELECT * FROM mensalistas WHERE ativo = 1 ORDER BY nome");

// Processar formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $placa = sanitizePlate($_POST['placa'] ?? '');
    $tipo = sanitizeString($_POST['tipo'] ?? '');
    $mensalistaId = !empty($_POST['mensalista_id']) ? (int)$_POST['mensalista_id'] : null;
    $observacoes = sanitizeString($_POST['observacoes'] ?? '');
    
    // Validações
    $errors = [];
    
    if (empty($placa)) {
        $errors[] = 'A placa é obrigatória.';
    } elseif (!validatePlate($placa)) {
        $errors[] = 'Placa inválida.';
    }
    
    if (empty($tipo) || !array_key_exists($tipo, VEHICLE_TYPES)) {
        $errors[] = 'Tipo de veículo inválido.';
    }
    
    // Verificar se o veículo já está no estacionamento
    $veiculoExistente = $db->selectOne(
        "SELECT * FROM veiculos WHERE placa = ? AND data_saida IS NULL",
        [$placa]
    );
    
    if ($veiculoExistente) {
        $errors[] = 'Este veículo já está no estacionamento desde ' . 
                    formatDateTime($veiculoExistente['data_entrada'] . ' ' . $veiculoExistente['hora_entrada']);
    }
    
    if (empty($errors)) {
        // Inserir entrada
        $sql = "INSERT INTO veiculos (placa, tipo, mensalista_id, data_entrada, hora_entrada, observacoes) 
                VALUES (?, ?, ?, CURDATE(), CURTIME(), ?)";
        
        if ($db->execute($sql, [$placa, $tipo, $mensalistaId, $observacoes])) {
            setFlashMessage('success', 'Entrada registrada com sucesso!');
            redirect('painel/veiculos/entrada.php');
        } else {
            $errors[] = 'Erro ao registrar entrada. Tente novamente.';
        }
    }
    
    // Exibir erros
    if (!empty($errors)) {
        setFlashMessage('error', implode('<br>', $errors));
    }
}

// Incluir header
include __DIR__ . '/../../includes/header.php';
?>

<div class="max-w-4xl mx-auto">
    <!-- Card Principal -->
    <div class="bg-white rounded-lg shadow-lg p-6 md:p-8">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-2xl font-bold text-gray-800 flex items-center">
                <i class="fas fa-sign-in-alt mr-3 text-green-600"></i>
                Registrar Entrada de Veículo
            </h2>
            <a href="<?php echo url('painel/veiculos/listar.php'); ?>" 
               class="px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-lg text-sm font-semibold">
                <i class="fas fa-list mr-2"></i>Ver Listagem
            </a>
        </div>
        
        <!-- Formulário -->
        <form method="POST" id="entradaForm" class="space-y-6">
            <!-- Placa -->
            <div>
                <label for="placa" class="block text-gray-700 font-semibold mb-2">
                    <i class="fas fa-id-card mr-2 text-blue-600"></i>Placa do Veículo *
                </label>
                <input 
                    type="text" 
                    id="placa" 
                    name="placa" 
                    required
                    maxlength="8"
                    oninput="formatPlate(this)"
                    placeholder="ABC-1234 ou ABC1D23"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent uppercase"
                >
                <p class="text-sm text-gray-500 mt-1">Formato antigo (ABC-1234) ou Mercosul (ABC1D23)</p>
            </div>
            
            <!-- Tipo de Veículo -->
            <div>
                <label for="tipo" class="block text-gray-700 font-semibold mb-2">
                    <i class="fas fa-car mr-2 text-blue-600"></i>Tipo de Veículo *
                </label>
                <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                    <?php foreach (VEHICLE_TYPES as $key => $label): ?>
                        <label class="relative">
                            <input 
                                type="radio" 
                                name="tipo" 
                                value="<?php echo $key; ?>" 
                                required
                                class="peer sr-only"
                            >
                            <div class="border-2 border-gray-300 rounded-lg p-4 cursor-pointer hover:border-blue-500 peer-checked:border-blue-600 peer-checked:bg-blue-50 transition text-center">
                                <i class="fas fa-car text-2xl text-gray-600 peer-checked:text-blue-600 mb-2"></i>
                                <p class="font-semibold text-gray-800"><?php echo $label; ?></p>
                                <p class="text-sm text-green-600 font-bold mt-1">
                                    <?php echo formatMoney($config['valor_' . $key] ?? 0); ?>
                                </p>
                            </div>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- Mensalista -->
            <div>
                <label for="mensalista_id" class="block text-gray-700 font-semibold mb-2">
                    <i class="fas fa-user mr-2 text-blue-600"></i>Mensalista (Opcional)
                </label>
                <select 
                    id="mensalista_id" 
                    name="mensalista_id"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                >
                    <option value="">Não é mensalista</option>
                    <?php foreach ($mensalistas as $mensalista): ?>
                        <option value="<?php echo $mensalista['id']; ?>">
                            <?php echo htmlspecialchars($mensalista['nome']); ?> - 
                            <?php echo formatPlate($mensalista['placa']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <p class="text-sm text-gray-500 mt-1">
                    Selecione se o veículo pertence a um mensalista cadastrado
                </p>
            </div>
            
            <!-- Observações -->
            <div>
                <label for="observacoes" class="block text-gray-700 font-semibold mb-2">
                    <i class="fas fa-comment mr-2 text-blue-600"></i>Observações (Opcional)
                </label>
                <textarea 
                    id="observacoes" 
                    name="observacoes"
                    rows="3"
                    placeholder="Informações adicionais sobre o veículo..."
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                ></textarea>
            </div>
            
            <!-- Informações Automáticas -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <h3 class="font-semibold text-blue-800 mb-2">
                    <i class="fas fa-info-circle mr-2"></i>Informações Automáticas
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
                    <div>
                        <span class="text-gray-600">Data de Entrada:</span>
                        <span class="font-semibold text-gray-800 ml-2"><?php echo formatDate(date('Y-m-d')); ?></span>
                    </div>
                    <div>
                        <span class="text-gray-600">Hora de Entrada:</span>
                        <span class="font-semibold text-gray-800 ml-2" id="currentTime"><?php echo date('H:i'); ?></span>
                    </div>
                </div>
            </div>
            
            <!-- Botões -->
            <div class="flex flex-col sm:flex-row gap-3">
                <button 
                    type="submit" 
                    class="flex-1 bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-6 rounded-lg transition duration-200 transform hover:scale-105"
                >
                    <i class="fas fa-check mr-2"></i>Registrar Entrada
                </button>
                <a 
                    href="<?php echo url('painel/'); ?>" 
                    class="flex-1 bg-gray-500 hover:bg-gray-600 text-white font-bold py-3 px-6 rounded-lg transition duration-200 text-center"
                >
                    <i class="fas fa-times mr-2"></i>Cancelar
                </a>
            </div>
        </form>
    </div>
    
    <!-- Dicas -->
    <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mt-6 rounded-r-lg">
        <h3 class="font-semibold text-yellow-800 mb-2">
            <i class="fas fa-lightbulb mr-2"></i>Dicas Importantes
        </h3>
        <ul class="text-sm text-yellow-700 space-y-1">
            <li><i class="fas fa-check mr-2"></i>Verifique se a placa está correta antes de confirmar</li>
            <li><i class="fas fa-check mr-2"></i>Mensalistas não pagam na saída</li>
            <li><i class="fas fa-check mr-2"></i>O valor será calculado automaticamente na saída</li>
            <li><i class="fas fa-check mr-2"></i>Data e hora são registradas automaticamente</li>
        </ul>
    </div>
</div>

<script>
// Atualizar hora em tempo real
setInterval(function() {
    const now = new Date();
    const hours = String(now.getHours()).padStart(2, '0');
    const minutes = String(now.getMinutes()).padStart(2, '0');
    document.getElementById('currentTime').textContent = hours + ':' + minutes;
}, 1000);

// Auto-preencher mensalista ao digitar placa
document.getElementById('placa').addEventListener('blur', function() {
    const placa = this.value.replace(/[^A-Z0-9]/g, '');
    const mensalistaSelect = document.getElementById('mensalista_id');
    
    // Buscar mensalista pela placa
    const options = mensalistaSelect.options;
    for (let i = 0; i < options.length; i++) {
        const optionText = options[i].text.toUpperCase().replace(/[^A-Z0-9]/g, '');
        if (optionText.includes(placa) && placa.length >= 7) {
            mensalistaSelect.value = options[i].value;
            showToast('Mensalista identificado automaticamente!', 'info');
            break;
        }
    }
});

// Focus no campo de placa ao carregar
document.getElementById('placa').focus();
</script>

<?php
// Incluir footer
include __DIR__ . '/../../includes/footer.php';
?>
