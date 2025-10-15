<?php
/**
 * EstacionaFácil - Página de Login
 * 
 * Página de autenticação do sistema com validação e segurança
 * 
 * @author Dante Testa (https://dantetesta.com.br)
 * @version 1.0.0
 * @date 14/10/2025 19:54
 */

// Definir constante
define('ESTACIONAFACIL', true);

// Incluir arquivos necessários
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

// Se já estiver logado, redirecionar para o painel
if (isLoggedIn()) {
    redirect('painel/');
}

// Variáveis
$error = '';
$username = '';

// Processar formulário de login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    // Validar campos
    if (empty($username) || empty($password)) {
        $error = 'Por favor, preencha todos os campos.';
    } else {
        // Tentar fazer login
        $result = loginUser($username, $password);
        
        if ($result['success']) {
            // Login bem-sucedido, redirecionar para o painel
            redirect('painel/');
        } else {
            // Login falhou, exibir erro
            $error = $result['message'];
        }
    }
}

// Verificar mensagens na URL
$urlMessage = '';
if (isset($_GET['expired'])) {
    $urlMessage = 'Sua sessão expirou. Por favor, faça login novamente.';
} elseif (isset($_GET['security'])) {
    $urlMessage = 'Sua sessão foi encerrada por motivos de segurança.';
} elseif (isset($_GET['logout'])) {
    $urlMessage = 'Você saiu do sistema com sucesso.';
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <meta name="description" content="Sistema de Gestão de Estacionamento">
    <title>Login - EstacionaFácil</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        /* Animações personalizadas */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .fade-in {
            animation: fadeIn 0.5s ease-out;
        }
        
        /* Gradiente animado de fundo */
        @keyframes gradient {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        
        .animated-gradient {
            background: linear-gradient(-45deg, #3B82F6, #2563EB, #1E40AF, #1E3A8A);
            background-size: 400% 400%;
            animation: gradient 15s ease infinite;
        }
    </style>
</head>
<body class="animated-gradient min-h-screen flex items-center justify-center p-4">
    <!-- Container Principal -->
    <div class="w-full max-w-md fade-in">
        <!-- Card de Login -->
        <div class="bg-white rounded-2xl shadow-2xl overflow-hidden">
            <!-- Header -->
            <div class="bg-gradient-to-r from-blue-600 to-blue-700 p-8 text-center">
                <div class="inline-block bg-white rounded-full p-4 mb-4">
                    <i class="fas fa-car text-blue-600 text-4xl"></i>
                </div>
                <h1 class="text-3xl font-bold text-white mb-2">EstacionaFácil</h1>
                <p class="text-blue-100">Sistema de Gestão de Estacionamento</p>
            </div>
            
            <!-- Corpo do Card -->
            <div class="p-8">
                <!-- Mensagens de Erro -->
                <?php if ($error): ?>
                    <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-r" role="alert">
                        <div class="flex items-center">
                            <i class="fas fa-exclamation-circle mr-3 text-xl"></i>
                            <p><?php echo htmlspecialchars($error); ?></p>
                        </div>
                    </div>
                <?php endif; ?>
                
                <!-- Mensagens da URL -->
                <?php if ($urlMessage): ?>
                    <div class="bg-blue-50 border-l-4 border-blue-500 text-blue-700 p-4 mb-6 rounded-r" role="alert">
                        <div class="flex items-center">
                            <i class="fas fa-info-circle mr-3 text-xl"></i>
                            <p><?php echo htmlspecialchars($urlMessage); ?></p>
                        </div>
                    </div>
                <?php endif; ?>
                
                <!-- Formulário de Login -->
                <form method="POST" action="" class="space-y-6">
                    <!-- Campo de Usuário -->
                    <div>
                        <label for="username" class="block text-gray-700 font-semibold mb-2">
                            <i class="fas fa-user mr-2 text-blue-600"></i>Usuário
                        </label>
                        <input 
                            type="text" 
                            id="username" 
                            name="username" 
                            value="<?php echo htmlspecialchars($username); ?>"
                            required 
                            autofocus
                            autocomplete="username"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200"
                            placeholder="Digite seu usuário"
                        >
                    </div>
                    
                    <!-- Campo de Senha -->
                    <div>
                        <label for="password" class="block text-gray-700 font-semibold mb-2">
                            <i class="fas fa-lock mr-2 text-blue-600"></i>Senha
                        </label>
                        <div class="relative">
                            <input 
                                type="password" 
                                id="password" 
                                name="password" 
                                required
                                autocomplete="current-password"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 pr-12"
                                placeholder="Digite sua senha"
                            >
                            <button 
                                type="button" 
                                onclick="togglePassword()"
                                class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700 focus:outline-none"
                                aria-label="Mostrar/Ocultar senha"
                            >
                                <i class="fas fa-eye" id="toggleIcon"></i>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Botão de Login -->
                    <button 
                        type="submit" 
                        class="w-full bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white font-bold py-3 px-6 rounded-lg transition duration-200 transform hover:scale-105 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                    >
                        <i class="fas fa-sign-in-alt mr-2"></i>Entrar
                    </button>
                </form>
            </div>
            
            <!-- Footer do Card -->
            <div class="bg-gray-50 px-8 py-4 border-t border-gray-200">
                <div class="flex items-center justify-between text-sm text-gray-600">
                    <div>
                        <i class="fas fa-shield-alt mr-1 text-green-600"></i>
                        Conexão Segura
                    </div>
                    <div>
                        v<?php echo SYSTEM_VERSION; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Informações Adicionais -->
        <div class="mt-6 text-center text-white text-sm">
            <p class="mb-2">
                <i class="fas fa-info-circle mr-1"></i>
                Sistema desenvolvido por 
                <a href="<?php echo SYSTEM_AUTHOR_URL; ?>" target="_blank" class="font-semibold hover:underline">
                    <?php echo SYSTEM_AUTHOR; ?>
                </a>
            </p>
            <p class="text-blue-100">
                © <?php echo date('Y'); ?> - Todos os direitos reservados
            </p>
        </div>
    </div>
    
    <!-- Scripts -->
    <script>
        /**
         * Alterna a visibilidade da senha
         */
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('toggleIcon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }
        
        /**
         * Adiciona feedback visual ao formulário
         */
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form');
            const submitButton = form.querySelector('button[type="submit"]');
            
            form.addEventListener('submit', function() {
                // Desabilitar botão e mostrar loading
                submitButton.disabled = true;
                submitButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Entrando...';
            });
            
            // Adicionar efeito de foco nos inputs
            const inputs = form.querySelectorAll('input');
            inputs.forEach(input => {
                input.addEventListener('focus', function() {
                    this.parentElement.classList.add('scale-102');
                });
                
                input.addEventListener('blur', function() {
                    this.parentElement.classList.remove('scale-102');
                });
            });
        });
        
        /**
         * Prevenir múltiplos submits
         */
        let formSubmitted = false;
        document.querySelector('form').addEventListener('submit', function(e) {
            if (formSubmitted) {
                e.preventDefault();
                return false;
            }
            formSubmitted = true;
        });
    </script>
</body>
</html>
