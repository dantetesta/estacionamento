<?php
/**
 * EstacionaFácil - Cadastrar Mensalista
 * 
 * Página para cadastrar novos mensalistas
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
$pageTitle = 'Cadastrar Mensalista';

// Obter banco de dados
$db = getDB();

// Processar formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = sanitizeString($_POST['nome'] ?? '');
    $placa = sanitizePlate($_POST['placa'] ?? '');
    $telefone = sanitizeString($_POST['telefone'] ?? '');
    $email = sanitizeEmail($_POST['email'] ?? '');
    $valorMensal = moneyToFloat($_POST['valor_mensal'] ?? 0);
    $diaVencimento = (int)($_POST['dia_vencimento'] ?? 10);
    $observacoes = sanitizeString($_POST['observacoes'] ?? '');
    
    // Validações
    $errors = [];
    
    if (empty($nome)) {
        $errors[] = 'O nome é obrigatório.';
    }
    
    if (empty($placa)) {
        $errors[] = 'A placa é obrigatória.';
    } elseif (!validatePlate($placa)) {
        $errors[] = 'Placa inválida.';
    }
    
    if (!empty($telefone) && !validatePhone($telefone)) {
        $errors[] = 'Telefone inválido.';
    }
    
    if (!empty($email) && !validateEmail($email)) {
        $errors[] = 'Email inválido.';
    }
    
    if ($valorMensal <= 0) {
        $errors[] = 'O valor mensal deve ser maior que zero.';
    }
    
    if ($diaVencimento < 1 || $diaVencimento > 31) {
        $errors[] = 'Dia de vencimento inválido.';
    }
    
    // Verificar se a placa já está cadastrada
    if (empty($errors)) {
        $placaExistente = $db->selectOne(
            "SELECT * FROM mensalistas WHERE placa = ?",
            [$placa]
        );
        
        if ($placaExistente) {
            $errors[] = 'Esta placa já está cadastrada para o mensalista: ' . htmlspecialchars($placaExistente['nome']);
        }
    }
    
    if (empty($errors)) {
        // Inserir mensalista
        $sql = "INSERT INTO mensalistas (nome, placa, telefone, email, valor_mensal, dia_vencimento, observacoes, ativo) 
                VALUES (?, ?, ?, ?, ?, ?, ?, 1)";
        
        if ($db->execute($sql, [$nome, $placa, $telefone, $email, $valorMensal, $diaVencimento, $observacoes])) {
            setFlashMessage('success', 'Mensalista cadastrado com sucesso!');
            redirect('painel/mensalistas/listar.php');
        } else {
            $errors[] = 'Erro ao cadastrar mensalista. Tente novamente.';
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
                <i class="fas fa-user-plus mr-3 text-blue-600"></i>
                Cadastrar Mensalista
            </h2>
            <a href="<?php echo url('painel/mensalistas/listar.php'); ?>" 
               class="px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-lg text-sm font-semibold">
                <i class="fas fa-list mr-2"></i>Ver Listagem
            </a>
        </div>
        
        <!-- Formulário -->
        <form method="POST" id="mensalistaForm" class="space-y-6">
            <!-- Dados Pessoais -->
            <div class="border-b pb-6">
                <h3 class="text-lg font-semibold text-gray-700 mb-4">
                    <i class="fas fa-user mr-2 text-blue-600"></i>Dados Pessoais
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Nome -->
                    <div class="md:col-span-2">
                        <label for="nome" class="block text-gray-700 font-semibold mb-2">
                            Nome Completo *
                        </label>
                        <input 
                            type="text" 
                            id="nome" 
                            name="nome" 
                            required
                            placeholder="João da Silva"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        >
                    </div>
                    
                    <!-- Telefone -->
                    <div>
                        <label for="telefone" class="block text-gray-700 font-semibold mb-2">
                            Telefone
                        </label>
                        <input 
                            type="tel" 
                            id="telefone" 
                            name="telefone"
                            placeholder="(11) 98765-4321"
                            oninput="formatPhone(this)"
                            maxlength="15"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        >
                    </div>
                    
                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-gray-700 font-semibold mb-2">
                            Email
                        </label>
                        <input 
                            type="email" 
                            id="email" 
                            name="email"
                            placeholder="joao@email.com"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        >
                    </div>
                </div>
            </div>
            
            <!-- Dados do Veículo -->
            <div class="border-b pb-6">
                <h3 class="text-lg font-semibold text-gray-700 mb-4">
                    <i class="fas fa-car mr-2 text-blue-600"></i>Dados do Veículo
                </h3>
                
                <div>
                    <label for="placa" class="block text-gray-700 font-semibold mb-2">
                        Placa do Veículo *
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
            </div>
            
            <!-- Dados Financeiros -->
            <div class="border-b pb-6">
                <h3 class="text-lg font-semibold text-gray-700 mb-4">
                    <i class="fas fa-dollar-sign mr-2 text-blue-600"></i>Dados Financeiros
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Valor Mensal -->
                    <div>
                        <label for="valor_mensal" class="block text-gray-700 font-semibold mb-2">
                            Valor Mensal (R$) *
                        </label>
                        <input 
                            type="number" 
                            id="valor_mensal" 
                            name="valor_mensal" 
                            required
                            step="0.01"
                            min="0"
                            placeholder="150.00"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        >
                    </div>
                    
                    <!-- Dia de Vencimento -->
                    <div>
                        <label for="dia_vencimento" class="block text-gray-700 font-semibold mb-2">
                            Dia de Vencimento *
                        </label>
                        <select 
                            id="dia_vencimento" 
                            name="dia_vencimento"
                            required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        >
                            <?php for ($i = 1; $i <= 31; $i++): ?>
                                <option value="<?php echo $i; ?>" <?php echo $i === 10 ? 'selected' : ''; ?>>
                                    Dia <?php echo $i; ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                    </div>
                </div>
            </div>
            
            <!-- Observações -->
            <div>
                <label for="observacoes" class="block text-gray-700 font-semibold mb-2">
                    <i class="fas fa-comment mr-2 text-blue-600"></i>Observações (Opcional)
                </label>
                <textarea 
                    id="observacoes" 
                    name="observacoes"
                    rows="4"
                    placeholder="Informações adicionais sobre o mensalista..."
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                ></textarea>
            </div>
            
            <!-- Resumo -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <h3 class="font-semibold text-blue-800 mb-2">
                    <i class="fas fa-info-circle mr-2"></i>Resumo
                </h3>
                <ul class="text-sm text-blue-700 space-y-1">
                    <li><i class="fas fa-check mr-2"></i>O mensalista terá entrada e saída liberadas sem cobrança</li>
                    <li><i class="fas fa-check mr-2"></i>O pagamento mensal deverá ser registrado manualmente</li>
                    <li><i class="fas fa-check mr-2"></i>Histórico de entradas e saídas será mantido</li>
                </ul>
            </div>
            
            <!-- Botões -->
            <div class="flex flex-col sm:flex-row gap-3">
                <button 
                    type="submit" 
                    class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg transition duration-200 transform hover:scale-105"
                >
                    <i class="fas fa-check mr-2"></i>Cadastrar Mensalista
                </button>
                <a 
                    href="<?php echo url('painel/mensalistas/listar.php'); ?>" 
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
            <li><i class="fas fa-check mr-2"></i>Verifique se a placa está correta antes de cadastrar</li>
            <li><i class="fas fa-check mr-2"></i>O valor mensal pode ser alterado posteriormente</li>
            <li><i class="fas fa-check mr-2"></i>Mensalistas não pagam na entrada/saída</li>
            <li><i class="fas fa-check mr-2"></i>Você pode desativar um mensalista sem excluí-lo</li>
        </ul>
    </div>
</div>

<script>
// Validação adicional do formulário
document.getElementById('mensalistaForm').addEventListener('submit', function(e) {
    const nome = document.getElementById('nome').value.trim();
    const placa = document.getElementById('placa').value.trim();
    const valorMensal = parseFloat(document.getElementById('valor_mensal').value);
    
    if (nome.length < 3) {
        e.preventDefault();
        alert('O nome deve ter pelo menos 3 caracteres.');
        return false;
    }
    
    if (placa.length < 7) {
        e.preventDefault();
        alert('Placa inválida.');
        return false;
    }
    
    if (valorMensal <= 0) {
        e.preventDefault();
        alert('O valor mensal deve ser maior que zero.');
        return false;
    }
});

// Focus no campo de nome ao carregar
document.getElementById('nome').focus();
</script>

<?php
// Incluir footer
include __DIR__ . '/../../includes/footer.php';
?>
