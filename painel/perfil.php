<?php
/**
 * EstacionaFácil - Editar Perfil do Usuário
 * 
 * Página para editar dados do usuário logado (nome, email, senha)
 * 
 * @author Dante Testa (https://dantetesta.com.br)
 * @version 1.0.0
 * @date 14/10/2025 21:12
 */

// Definir constante
define('ESTACIONAFACIL', true);

// Incluir arquivos necessários
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

// Título da página
$pageTitle = 'Meu Perfil';

// Obter banco de dados
$db = getDB();

// Buscar dados do usuário logado
$usuario = $db->selectOne(
    "SELECT * FROM usuarios WHERE id = ?",
    [$_SESSION['user_id']]
);

if (!$usuario) {
    setFlashMessage('error', 'Usuário não encontrado.');
    redirect('painel/');
}

// Processar formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = sanitizeString($_POST['nome'] ?? '');
    $email = sanitizeEmail($_POST['email'] ?? '');
    $senhaAtual = $_POST['senha_atual'] ?? '';
    $senhaNova = $_POST['senha_nova'] ?? '';
    $senhaConfirmar = $_POST['senha_confirmar'] ?? '';
    
    // Validações
    $errors = [];
    
    if (empty($nome)) {
        $errors[] = 'O nome é obrigatório.';
    } elseif (strlen($nome) < 3) {
        $errors[] = 'O nome deve ter pelo menos 3 caracteres.';
    }
    
    if (!empty($email) && !validateEmail($email)) {
        $errors[] = 'Email inválido.';
    }
    
    // Se está tentando alterar a senha
    if (!empty($senhaNova) || !empty($senhaConfirmar)) {
        if (empty($senhaAtual)) {
            $errors[] = 'Digite sua senha atual para alterar a senha.';
        } elseif (!password_verify($senhaAtual, $usuario['senha'])) {
            $errors[] = 'Senha atual incorreta.';
        } elseif (strlen($senhaNova) < 6) {
            $errors[] = 'A nova senha deve ter pelo menos 6 caracteres.';
        } elseif ($senhaNova !== $senhaConfirmar) {
            $errors[] = 'As senhas não coincidem.';
        }
    }
    
    if (empty($errors)) {
        // Atualizar dados
        if (!empty($senhaNova)) {
            // Atualizar com nova senha
            $senhaHash = password_hash($senhaNova, PASSWORD_BCRYPT);
            $sql = "UPDATE usuarios SET nome = ?, email = ?, senha = ? WHERE id = ?";
            $params = [$nome, $email, $senhaHash, $_SESSION['user_id']];
        } else {
            // Atualizar sem alterar senha
            $sql = "UPDATE usuarios SET nome = ?, email = ? WHERE id = ?";
            $params = [$nome, $email, $_SESSION['user_id']];
        }
        
        if ($db->execute($sql, $params)) {
            // Atualizar nome na sessão
            $_SESSION['user_name'] = $nome;
            
            $mensagem = !empty($senhaNova) 
                ? 'Perfil atualizado com sucesso! Senha alterada.' 
                : 'Perfil atualizado com sucesso!';
            
            setFlashMessage('success', $mensagem);
            redirect('painel/perfil.php');
        } else {
            $errors[] = 'Erro ao atualizar perfil. Tente novamente.';
        }
    }
    
    // Exibir erros
    if (!empty($errors)) {
        setFlashMessage('error', implode('<br>', $errors));
    }
}

// Incluir header
include __DIR__ . '/../includes/header.php';
?>

<div class="max-w-4xl mx-auto">
    <!-- Card Principal -->
    <div class="bg-white rounded-lg shadow-lg p-6 md:p-8">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-2xl font-bold text-gray-800 flex items-center">
                <i class="fas fa-user-circle mr-3 text-blue-600"></i>
                Meu Perfil
            </h2>
            <a href="<?php echo url('painel/'); ?>" 
               class="px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-lg text-sm font-semibold">
                <i class="fas fa-arrow-left mr-2"></i>Voltar
            </a>
        </div>
        
        <!-- Informações do Usuário -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-6">
            <div class="flex items-center">
                <div class="w-20 h-20 bg-blue-600 rounded-full flex items-center justify-center mr-4">
                    <i class="fas fa-user text-white text-3xl"></i>
                </div>
                <div>
                    <h3 class="text-xl font-bold text-gray-800"><?php echo htmlspecialchars($usuario['nome']); ?></h3>
                    <p class="text-gray-600">@<?php echo htmlspecialchars($usuario['usuario']); ?></p>
                    <?php if ($usuario['email']): ?>
                        <p class="text-gray-600">
                            <i class="fas fa-envelope mr-1"></i>
                            <?php echo htmlspecialchars($usuario['email']); ?>
                        </p>
                    <?php endif; ?>
                    <?php if ($usuario['ultimo_login']): ?>
                        <p class="text-sm text-gray-500 mt-2">
                            <i class="fas fa-clock mr-1"></i>
                            Último acesso: <?php echo formatDateTime($usuario['ultimo_login']); ?>
                        </p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Formulário de Edição -->
        <form method="POST" id="perfilForm" class="space-y-6">
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
                            value="<?php echo htmlspecialchars($usuario['nome']); ?>"
                            required
                            minlength="3"
                            placeholder="Seu nome completo"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        >
                    </div>
                    
                    <!-- Email -->
                    <div class="md:col-span-2">
                        <label for="email" class="block text-gray-700 font-semibold mb-2">
                            Email
                        </label>
                        <input 
                            type="email" 
                            id="email" 
                            name="email"
                            value="<?php echo htmlspecialchars($usuario['email']); ?>"
                            placeholder="seu@email.com"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        >
                    </div>
                    
                    <!-- Usuário (somente leitura) -->
                    <div class="md:col-span-2">
                        <label for="usuario" class="block text-gray-700 font-semibold mb-2">
                            Usuário (não pode ser alterado)
                        </label>
                        <input 
                            type="text" 
                            id="usuario" 
                            value="<?php echo htmlspecialchars($usuario['usuario']); ?>"
                            readonly
                            disabled
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-gray-100 text-gray-600 cursor-not-allowed"
                        >
                    </div>
                </div>
            </div>
            
            <!-- Alterar Senha -->
            <div class="border-b pb-6">
                <h3 class="text-lg font-semibold text-gray-700 mb-4">
                    <i class="fas fa-lock mr-2 text-blue-600"></i>Alterar Senha (Opcional)
                </h3>
                
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-4">
                    <p class="text-sm text-yellow-800">
                        <i class="fas fa-info-circle mr-2"></i>
                        Deixe os campos em branco se não quiser alterar a senha.
                    </p>
                </div>
                
                <div class="grid grid-cols-1 gap-4">
                    <!-- Senha Atual -->
                    <div>
                        <label for="senha_atual" class="block text-gray-700 font-semibold mb-2">
                            Senha Atual
                        </label>
                        <input 
                            type="password" 
                            id="senha_atual" 
                            name="senha_atual"
                            placeholder="Digite sua senha atual"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        >
                    </div>
                    
                    <!-- Nova Senha -->
                    <div>
                        <label for="senha_nova" class="block text-gray-700 font-semibold mb-2">
                            Nova Senha
                        </label>
                        <input 
                            type="password" 
                            id="senha_nova" 
                            name="senha_nova"
                            minlength="6"
                            placeholder="Digite a nova senha (mínimo 6 caracteres)"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        >
                    </div>
                    
                    <!-- Confirmar Nova Senha -->
                    <div>
                        <label for="senha_confirmar" class="block text-gray-700 font-semibold mb-2">
                            Confirmar Nova Senha
                        </label>
                        <input 
                            type="password" 
                            id="senha_confirmar" 
                            name="senha_confirmar"
                            minlength="6"
                            placeholder="Digite a nova senha novamente"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        >
                    </div>
                </div>
            </div>
            
            <!-- Informações Adicionais -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <h3 class="font-semibold text-blue-800 mb-2">
                    <i class="fas fa-info-circle mr-2"></i>Informações
                </h3>
                <ul class="text-sm text-blue-700 space-y-1">
                    <li><i class="fas fa-check mr-2"></i>Seu nome será exibido no sistema</li>
                    <li><i class="fas fa-check mr-2"></i>O email é opcional mas recomendado</li>
                    <li><i class="fas fa-check mr-2"></i>A senha deve ter no mínimo 6 caracteres</li>
                    <li><i class="fas fa-check mr-2"></i>Use uma senha forte e segura</li>
                </ul>
            </div>
            
            <!-- Botões -->
            <div class="flex flex-col sm:flex-row gap-3">
                <button 
                    type="submit" 
                    class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg transition duration-200 transform hover:scale-105"
                >
                    <i class="fas fa-save mr-2"></i>Salvar Alterações
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
    
    <!-- Dicas de Segurança -->
    <div class="bg-red-50 border-l-4 border-red-400 p-4 mt-6 rounded-r-lg">
        <h3 class="font-semibold text-red-800 mb-2">
            <i class="fas fa-shield-alt mr-2"></i>Dicas de Segurança
        </h3>
        <ul class="text-sm text-red-700 space-y-1">
            <li><i class="fas fa-check mr-2"></i>Nunca compartilhe sua senha com ninguém</li>
            <li><i class="fas fa-check mr-2"></i>Use uma senha diferente para cada sistema</li>
            <li><i class="fas fa-check mr-2"></i>Altere sua senha periodicamente</li>
            <li><i class="fas fa-check mr-2"></i>Não use senhas óbvias como "123456" ou "senha"</li>
        </ul>
    </div>
</div>

<script>
// Validação do formulário
document.getElementById('perfilForm').addEventListener('submit', function(e) {
    const nome = document.getElementById('nome').value.trim();
    const senhaAtual = document.getElementById('senha_atual').value;
    const senhaNova = document.getElementById('senha_nova').value;
    const senhaConfirmar = document.getElementById('senha_confirmar').value;
    
    // Validar nome
    if (nome.length < 3) {
        e.preventDefault();
        alert('O nome deve ter pelo menos 3 caracteres.');
        return false;
    }
    
    // Se está tentando alterar senha
    if (senhaNova || senhaConfirmar) {
        if (!senhaAtual) {
            e.preventDefault();
            alert('Digite sua senha atual para alterar a senha.');
            return false;
        }
        
        if (senhaNova.length < 6) {
            e.preventDefault();
            alert('A nova senha deve ter pelo menos 6 caracteres.');
            return false;
        }
        
        if (senhaNova !== senhaConfirmar) {
            e.preventDefault();
            alert('As senhas não coincidem.');
            return false;
        }
        
        // Confirmar alteração de senha
        if (!confirm('Você está alterando sua senha. Confirma esta ação?')) {
            e.preventDefault();
            return false;
        }
    }
});

// Mostrar/ocultar senha
function togglePassword(inputId) {
    const input = document.getElementById(inputId);
    input.type = input.type === 'password' ? 'text' : 'password';
}
</script>

<?php
// Incluir footer
include __DIR__ . '/../includes/footer.php';
?>
