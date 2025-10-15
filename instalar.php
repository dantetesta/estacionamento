<?php
/**
 * EstacionaFácil - Instalador do Sistema
 * 
 * Este arquivo realiza a instalação inicial do sistema:
 * - Cria o arquivo de configuração do banco de dados
 * - Cria as tabelas necessárias
 * - Insere dados iniciais
 * - Cria o usuário administrador
 * 
 * IMPORTANTE: Delete este arquivo após a instalação!
 * 
 * @author Dante Testa (https://dantetesta.com.br)
 * @version 1.0.0
 * @date 14/10/2025 19:54
 */

// Definir constante para permitir includes
define('ESTACIONAFACIL', true);

// Verificar se já foi instalado
if (file_exists(__DIR__ . '/config/installed.lock')) {
    die('O sistema já foi instalado. Se deseja reinstalar, delete o arquivo config/installed.lock');
}

// Variáveis de erro e sucesso
$error = '';
$success = '';
$step = isset($_GET['step']) ? (int)$_GET['step'] : 1;

// Processar formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($step === 1) {
        // Validar dados do banco
        $dbHost = trim($_POST['db_host'] ?? '');
        $dbName = trim($_POST['db_name'] ?? '');
        $dbUser = trim($_POST['db_user'] ?? '');
        $dbPass = $_POST['db_pass'] ?? '';
        
        if (empty($dbHost) || empty($dbName) || empty($dbUser)) {
            $error = 'Preencha todos os campos obrigatórios do banco de dados.';
        } else {
            // Testar conexão
            try {
                $pdo = new PDO("mysql:host=$dbHost;charset=utf8mb4", $dbUser, $dbPass);
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                
                // Criar banco de dados se não existir
                $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                $pdo->exec("USE `$dbName`");
                
                // Salvar configurações na sessão
                session_start();
                $_SESSION['install_db'] = [
                    'host' => $dbHost,
                    'name' => $dbName,
                    'user' => $dbUser,
                    'pass' => $dbPass
                ];
                
                // Ir para próximo passo
                header('Location: instalar.php?step=2');
                exit;
                
            } catch (PDOException $e) {
                $error = 'Erro ao conectar com o banco de dados: ' . $e->getMessage();
            }
        }
    } elseif ($step === 2) {
        session_start();
        
        // Validar dados do sistema
        $systemName = trim($_POST['system_name'] ?? '');
        $adminUser = trim($_POST['admin_user'] ?? '');
        $adminPass = $_POST['admin_pass'] ?? '';
        $adminPassConfirm = $_POST['admin_pass_confirm'] ?? '';
        $adminName = trim($_POST['admin_name'] ?? '');
        $adminEmail = trim($_POST['admin_email'] ?? '');
        
        // Valores das diárias
        $pricePequeno = floatval($_POST['price_pequeno'] ?? 10);
        $priceMedio = floatval($_POST['price_medio'] ?? 20);
        $priceGrande = floatval($_POST['price_grande'] ?? 30);
        $priceCaminhao = floatval($_POST['price_caminhao'] ?? 50);
        $priceOnibus = floatval($_POST['price_onibus'] ?? 60);
        
        // Validações
        if (empty($systemName) || empty($adminUser) || empty($adminPass) || empty($adminName)) {
            $error = 'Preencha todos os campos obrigatórios.';
        } elseif ($adminPass !== $adminPassConfirm) {
            $error = 'As senhas não coincidem.';
        } elseif (strlen($adminPass) < 6) {
            $error = 'A senha deve ter no mínimo 6 caracteres.';
        } elseif (!empty($adminEmail) && !filter_var($adminEmail, FILTER_VALIDATE_EMAIL)) {
            $error = 'Email inválido.';
        } else {
            try {
                $dbConfig = $_SESSION['install_db'];
                $pdo = new PDO(
                    "mysql:host={$dbConfig['host']};dbname={$dbConfig['name']};charset=utf8mb4",
                    $dbConfig['user'],
                    $dbConfig['pass']
                );
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                
                // Criar tabelas
                createTables($pdo);
                
                // Inserir configurações
                $stmt = $pdo->prepare("INSERT INTO configuracoes (nome_estacionamento, valor_pequeno, valor_medio, valor_grande, valor_caminhao, valor_onibus) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$systemName, $pricePequeno, $priceMedio, $priceGrande, $priceCaminhao, $priceOnibus]);
                
                // Criar usuário admin
                $passwordHash = password_hash($adminPass, PASSWORD_BCRYPT, ['cost' => 12]);
                $stmt = $pdo->prepare("INSERT INTO usuarios (usuario, senha, nome, email, criado_em) VALUES (?, ?, ?, ?, NOW())");
                $stmt->execute([$adminUser, $passwordHash, $adminName, $adminEmail]);
                
                // Criar arquivo de configuração
                createConfigFile($dbConfig);
                
                // Criar arquivo de lock
                file_put_contents(__DIR__ . '/config/installed.lock', date('Y-m-d H:i:s'));
                
                // Limpar sessão
                unset($_SESSION['install_db']);
                
                // Sucesso
                $success = 'Instalação concluída com sucesso!';
                $step = 3;
                
            } catch (PDOException $e) {
                $error = 'Erro ao criar tabelas: ' . $e->getMessage();
            }
        }
    }
}

/**
 * Cria as tabelas do banco de dados
 */
function createTables($pdo) {
    // Tabela de usuários
    $pdo->exec("CREATE TABLE IF NOT EXISTS usuarios (
        id INT AUTO_INCREMENT PRIMARY KEY,
        usuario VARCHAR(50) NOT NULL UNIQUE,
        senha VARCHAR(255) NOT NULL,
        nome VARCHAR(100) NOT NULL,
        email VARCHAR(100),
        ultimo_login DATETIME,
        criado_em DATETIME NOT NULL,
        INDEX idx_usuario (usuario)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    
    // Tabela de configurações
    $pdo->exec("CREATE TABLE IF NOT EXISTS configuracoes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nome_estacionamento VARCHAR(100) NOT NULL,
        valor_pequeno DECIMAL(10,2) NOT NULL DEFAULT 10.00,
        valor_medio DECIMAL(10,2) NOT NULL DEFAULT 20.00,
        valor_grande DECIMAL(10,2) NOT NULL DEFAULT 30.00,
        valor_caminhao DECIMAL(10,2) NOT NULL DEFAULT 50.00,
        valor_onibus DECIMAL(10,2) NOT NULL DEFAULT 60.00,
        atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    
    // Tabela de mensalistas (deve ser criada antes de veículos por causa da FK)
    $pdo->exec("CREATE TABLE IF NOT EXISTS mensalistas (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nome VARCHAR(100) NOT NULL,
        placa VARCHAR(10) NOT NULL UNIQUE,
        telefone VARCHAR(20),
        email VARCHAR(100),
        valor_mensal DECIMAL(10,2) NOT NULL,
        dia_vencimento INT NOT NULL DEFAULT 10,
        ativo BOOLEAN DEFAULT TRUE,
        observacoes TEXT NULL,
        criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_placa (placa),
        INDEX idx_ativo (ativo)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    
    // Tabela de veículos
    $pdo->exec("CREATE TABLE IF NOT EXISTS veiculos (
        id INT AUTO_INCREMENT PRIMARY KEY,
        placa VARCHAR(10) NOT NULL,
        tipo ENUM('pequeno', 'medio', 'grande', 'caminhao', 'onibus') NOT NULL,
        mensalista_id INT NULL,
        data_entrada DATE NOT NULL,
        hora_entrada TIME NOT NULL,
        data_saida DATE NULL,
        hora_saida TIME NULL,
        valor DECIMAL(10,2) NULL,
        forma_pagamento ENUM('dinheiro', 'pix', 'debito', 'credito') NULL,
        pago BOOLEAN DEFAULT FALSE,
        observacoes TEXT NULL,
        criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_placa (placa),
        INDEX idx_data_entrada (data_entrada),
        INDEX idx_mensalista (mensalista_id),
        INDEX idx_pago (pago),
        FOREIGN KEY (mensalista_id) REFERENCES mensalistas(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    
    // Tabela de despesas
    $pdo->exec("CREATE TABLE IF NOT EXISTS despesas (
        id INT AUTO_INCREMENT PRIMARY KEY,
        categoria ENUM('energia', 'internet', 'agua', 'salario', 'manutencao', 'limpeza', 'seguranca', 'impostos', 'outros') NOT NULL,
        descricao VARCHAR(255) NOT NULL,
        valor DECIMAL(10,2) NOT NULL,
        data DATE NOT NULL,
        recorrente BOOLEAN DEFAULT FALSE,
        criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_data (data),
        INDEX idx_categoria (categoria)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    
    // Tabela de pagamentos de mensalistas
    $pdo->exec("CREATE TABLE IF NOT EXISTS pagamentos_mensalistas (
        id INT AUTO_INCREMENT PRIMARY KEY,
        mensalista_id INT NOT NULL,
        mes_referencia DATE NOT NULL,
        valor DECIMAL(10,2) NOT NULL,
        data_pagamento DATE NULL,
        pago BOOLEAN DEFAULT FALSE,
        forma_pagamento ENUM('dinheiro', 'pix', 'debito', 'credito') NULL,
        observacoes TEXT NULL,
        criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_mensalista (mensalista_id),
        INDEX idx_mes_referencia (mes_referencia),
        INDEX idx_pago (pago),
        FOREIGN KEY (mensalista_id) REFERENCES mensalistas(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
}

/**
 * Cria o arquivo de configuração do banco de dados
 */
function createConfigFile($dbConfig) {
    $content = "<?php
/**
 * EstacionaFácil - Configuração do Banco de Dados
 * Gerado automaticamente pelo instalador
 * Data: " . date('d/m/Y H:i:s') . "
 */

if (!defined('ESTACIONAFACIL')) {
    die('Acesso negado');
}

define('DB_HOST', '{$dbConfig['host']}');
define('DB_NAME', '{$dbConfig['name']}');
define('DB_USER', '{$dbConfig['user']}');
define('DB_PASS', '{$dbConfig['pass']}');
define('DB_CHARSET', 'utf8mb4');
";
    
    // Ler o conteúdo atual do database.php
    $currentFile = file_get_contents(__DIR__ . '/config/database.php');
    
    // Substituir apenas as definições de constantes
    $newFile = preg_replace(
        "/define\('DB_HOST',\s*'[^']*'\);.*?define\('DB_CHARSET',\s*'[^']*'\);/s",
        "define('DB_HOST', '{$dbConfig['host']}');\ndefine('DB_NAME', '{$dbConfig['name']}');\ndefine('DB_USER', '{$dbConfig['user']}');\ndefine('DB_PASS', '{$dbConfig['pass']}');\ndefine('DB_CHARSET', 'utf8mb4');",
        $currentFile
    );
    
    file_put_contents(__DIR__ . '/config/database.php', $newFile);
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Instalação - EstacionaFácil</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gradient-to-br from-blue-50 to-gray-100 min-h-screen">
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-2xl mx-auto">
            <!-- Header -->
            <div class="text-center mb-8">
                <h1 class="text-4xl font-bold text-blue-600 mb-2">
                    <i class="fas fa-car mr-2"></i>EstacionaFácil
                </h1>
                <p class="text-gray-600">Sistema de Gestão de Estacionamento</p>
                <p class="text-sm text-gray-500 mt-2">Versão 1.0.0 - Por <a href="https://dantetesta.com.br" target="_blank" class="text-blue-600 hover:underline">Dante Testa</a></p>
            </div>

            <!-- Progress Steps -->
            <div class="flex justify-between mb-8">
                <div class="flex-1 text-center">
                    <div class="w-10 h-10 mx-auto rounded-full <?php echo $step >= 1 ? 'bg-blue-600 text-white' : 'bg-gray-300 text-gray-600'; ?> flex items-center justify-center font-bold mb-2">
                        <?php echo $step > 1 ? '<i class="fas fa-check"></i>' : '1'; ?>
                    </div>
                    <p class="text-xs text-gray-600">Banco de Dados</p>
                </div>
                <div class="flex-1 border-t-2 border-gray-300 mt-5"></div>
                <div class="flex-1 text-center">
                    <div class="w-10 h-10 mx-auto rounded-full <?php echo $step >= 2 ? 'bg-blue-600 text-white' : 'bg-gray-300 text-gray-600'; ?> flex items-center justify-center font-bold mb-2">
                        <?php echo $step > 2 ? '<i class="fas fa-check"></i>' : '2'; ?>
                    </div>
                    <p class="text-xs text-gray-600">Configuração</p>
                </div>
                <div class="flex-1 border-t-2 border-gray-300 mt-5"></div>
                <div class="flex-1 text-center">
                    <div class="w-10 h-10 mx-auto rounded-full <?php echo $step >= 3 ? 'bg-green-600 text-white' : 'bg-gray-300 text-gray-600'; ?> flex items-center justify-center font-bold mb-2">
                        <?php echo $step >= 3 ? '<i class="fas fa-check"></i>' : '3'; ?>
                    </div>
                    <p class="text-xs text-gray-600">Concluído</p>
                </div>
            </div>

            <!-- Card Principal -->
            <div class="bg-white rounded-lg shadow-lg p-8">
                <?php if ($error): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                        <i class="fas fa-exclamation-circle mr-2"></i><?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                        <i class="fas fa-check-circle mr-2"></i><?php echo htmlspecialchars($success); ?>
                    </div>
                <?php endif; ?>

                <?php if ($step === 1): ?>
                    <!-- Passo 1: Configuração do Banco de Dados -->
                    <h2 class="text-2xl font-bold text-gray-800 mb-6">
                        <i class="fas fa-database mr-2 text-blue-600"></i>Configuração do Banco de Dados
                    </h2>
                    
                    <form method="POST" class="space-y-4">
                        <div>
                            <label class="block text-gray-700 font-semibold mb-2">Host do Banco *</label>
                            <input type="text" name="db_host" value="localhost" required
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <p class="text-sm text-gray-500 mt-1">Geralmente é "localhost"</p>
                        </div>

                        <div>
                            <label class="block text-gray-700 font-semibold mb-2">Nome do Banco *</label>
                            <input type="text" name="db_name" value="estacionafacil" required
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <p class="text-sm text-gray-500 mt-1">Será criado automaticamente se não existir</p>
                        </div>

                        <div>
                            <label class="block text-gray-700 font-semibold mb-2">Usuário do Banco *</label>
                            <input type="text" name="db_user" value="root" required
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>

                        <div>
                            <label class="block text-gray-700 font-semibold mb-2">Senha do Banco</label>
                            <input type="password" name="db_pass"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <p class="text-sm text-gray-500 mt-1">Deixe em branco se não houver senha</p>
                        </div>

                        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg transition duration-200">
                            <i class="fas fa-arrow-right mr-2"></i>Próximo Passo
                        </button>
                    </form>

                <?php elseif ($step === 2): ?>
                    <!-- Passo 2: Configuração do Sistema -->
                    <h2 class="text-2xl font-bold text-gray-800 mb-6">
                        <i class="fas fa-cog mr-2 text-blue-600"></i>Configuração do Sistema
                    </h2>
                    
                    <form method="POST" class="space-y-6">
                        <!-- Dados do Estacionamento -->
                        <div class="border-b pb-4">
                            <h3 class="text-lg font-semibold text-gray-700 mb-4">Dados do Estacionamento</h3>
                            
                            <div>
                                <label class="block text-gray-700 font-semibold mb-2">Nome do Estacionamento *</label>
                                <input type="text" name="system_name" value="Meu Estacionamento" required
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            </div>
                        </div>

                        <!-- Usuário Administrador -->
                        <div class="border-b pb-4">
                            <h3 class="text-lg font-semibold text-gray-700 mb-4">Usuário Administrador</h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-gray-700 font-semibold mb-2">Nome Completo *</label>
                                    <input type="text" name="admin_name" required
                                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                </div>

                                <div>
                                    <label class="block text-gray-700 font-semibold mb-2">Email</label>
                                    <input type="email" name="admin_email"
                                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                </div>

                                <div>
                                    <label class="block text-gray-700 font-semibold mb-2">Usuário de Login *</label>
                                    <input type="text" name="admin_user" value="admin" required
                                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                </div>

                                <div>
                                    <label class="block text-gray-700 font-semibold mb-2">Senha *</label>
                                    <input type="password" name="admin_pass" required minlength="6"
                                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                </div>

                                <div class="md:col-span-2">
                                    <label class="block text-gray-700 font-semibold mb-2">Confirmar Senha *</label>
                                    <input type="password" name="admin_pass_confirm" required minlength="6"
                                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                </div>
                            </div>
                        </div>

                        <!-- Valores das Diárias -->
                        <div>
                            <h3 class="text-lg font-semibold text-gray-700 mb-4">Valores das Diárias</h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-gray-700 font-semibold mb-2">Veículo Pequeno (R$)</label>
                                    <input type="number" name="price_pequeno" value="10.00" step="0.01" min="0" required
                                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                </div>

                                <div>
                                    <label class="block text-gray-700 font-semibold mb-2">Veículo Médio (R$)</label>
                                    <input type="number" name="price_medio" value="20.00" step="0.01" min="0" required
                                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                </div>

                                <div>
                                    <label class="block text-gray-700 font-semibold mb-2">Veículo Grande (R$)</label>
                                    <input type="number" name="price_grande" value="30.00" step="0.01" min="0" required
                                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                </div>

                                <div>
                                    <label class="block text-gray-700 font-semibold mb-2">Caminhão (R$)</label>
                                    <input type="number" name="price_caminhao" value="50.00" step="0.01" min="0" required
                                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                </div>

                                <div class="md:col-span-2">
                                    <label class="block text-gray-700 font-semibold mb-2">Ônibus (R$)</label>
                                    <input type="number" name="price_onibus" value="60.00" step="0.01" min="0" required
                                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-6 rounded-lg transition duration-200">
                            <i class="fas fa-check mr-2"></i>Concluir Instalação
                        </button>
                    </form>

                <?php elseif ($step === 3): ?>
                    <!-- Passo 3: Instalação Concluída -->
                    <div class="text-center">
                        <div class="mb-6">
                            <i class="fas fa-check-circle text-green-600 text-6xl"></i>
                        </div>
                        
                        <h2 class="text-2xl font-bold text-gray-800 mb-4">Instalação Concluída!</h2>
                        
                        <p class="text-gray-600 mb-6">
                            O EstacionaFácil foi instalado com sucesso e está pronto para uso.
                        </p>

                        <div class="bg-yellow-50 border border-yellow-400 text-yellow-800 px-4 py-3 rounded mb-6">
                            <i class="fas fa-exclamation-triangle mr-2"></i>
                            <strong>IMPORTANTE:</strong> Por segurança, delete o arquivo <code class="bg-yellow-200 px-2 py-1 rounded">instalar.php</code> agora!
                        </div>

                        <div class="space-y-3">
                            <a href="login.php" class="block w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg transition duration-200">
                                <i class="fas fa-sign-in-alt mr-2"></i>Ir para o Login
                            </a>
                            
                            <a href="README.md" target="_blank" class="block w-full bg-gray-600 hover:bg-gray-700 text-white font-bold py-3 px-6 rounded-lg transition duration-200">
                                <i class="fas fa-book mr-2"></i>Ler Documentação
                            </a>
                        </div>

                        <div class="mt-8 pt-6 border-t">
                            <p class="text-sm text-gray-500">
                                Desenvolvido por <a href="https://dantetesta.com.br" target="_blank" class="text-blue-600 hover:underline">Dante Testa</a>
                            </p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Footer -->
            <div class="text-center mt-8 text-gray-600 text-sm">
                <p>EstacionaFácil v1.0.0 &copy; 2025 - Todos os direitos reservados</p>
            </div>
        </div>
    </div>
</body>
</html>
