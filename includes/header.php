<?php
/**
 * EstacionaFácil - Header Padrão
 * 
 * Header utilizado em todas as páginas do painel
 * Inclui navegação, menu responsivo e informações do usuário
 * 
 * @author Dante Testa (https://dantetesta.com.br)
 * @version 1.0.0
 * @date 14/10/2025 19:54
 */

// Prevenir acesso direto
if (!defined('ESTACIONAFACIL')) {
    die('Acesso negado');
}

// Verificar autenticação
requireAuth();

// Obter dados do usuário
$user = getLoggedUser();

// Obter configurações do sistema
$db = getDB();
$config = $db->selectOne("SELECT * FROM configuracoes LIMIT 1");
$systemName = $config['nome_estacionamento'] ?? SYSTEM_NAME;

// Determinar página ativa para highlight no menu
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
$currentDir = basename(dirname($_SERVER['PHP_SELF']));
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <meta name="description" content="Sistema de Gestão de Estacionamento">
    <meta name="author" content="<?php echo SYSTEM_AUTHOR; ?>">
    <title><?php echo $pageTitle ?? 'Painel'; ?> - <?php echo htmlspecialchars($systemName); ?></title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Chart.js (para gráficos) -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    
    <!-- CSS Customizado -->
    <style>
        /* Transições suaves */
        * {
            transition: all 0.2s ease;
        }
        
        /* Scrollbar customizada */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        
        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }
        
        ::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 4px;
        }
        
        ::-webkit-scrollbar-thumb:hover {
            background: #555;
        }
        
        /* Animações */
        @keyframes slideIn {
            from {
                transform: translateX(-100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        
        .slide-in {
            animation: slideIn 0.3s ease-out;
        }
        
        /* Print styles */
        @media print {
            .no-print {
                display: none !important;
            }
            
            body {
                background: white;
            }
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen">
    <!-- Overlay para mobile menu -->
    <div id="mobileMenuOverlay" class="fixed inset-0 bg-black bg-opacity-50 z-40 hidden" onclick="toggleMobileMenu()"></div>
    
    <!-- Sidebar -->
    <aside id="sidebar" class="fixed top-0 left-0 h-full w-64 bg-gradient-to-b from-blue-800 to-blue-900 text-white z-50 transform -translate-x-full lg:translate-x-0 transition-transform duration-300 overflow-y-auto no-print">
        <!-- Logo e Nome -->
        <div class="p-6 border-b border-blue-700">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="bg-white rounded-lg p-2">
                        <i class="fas fa-car text-blue-600 text-2xl"></i>
                    </div>
                    <div>
                        <h1 class="text-xl font-bold"><?php echo htmlspecialchars($systemName); ?></h1>
                        <p class="text-xs text-blue-300">v<?php echo SYSTEM_VERSION; ?></p>
                    </div>
                </div>
                <button onclick="toggleMobileMenu()" class="lg:hidden text-white hover:text-blue-300">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
        </div>
        
        <!-- Menu de Navegação -->
        <nav class="p-4">
            <ul class="space-y-2">
                <!-- Dashboard -->
                <li>
                    <a href="<?php echo url('painel/'); ?>" class="flex items-center space-x-3 p-3 rounded-lg hover:bg-blue-700 <?php echo $currentPage === 'index' && $currentDir === 'painel' ? 'bg-blue-700' : ''; ?>">
                        <i class="fas fa-home w-5"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                
                <!-- Veículos -->
                <li>
                    <div class="text-xs text-blue-300 uppercase font-semibold mt-4 mb-2 px-3">Veículos</div>
                </li>
                <li>
                    <a href="<?php echo url('painel/veiculos/entrada.php'); ?>" class="flex items-center space-x-3 p-3 rounded-lg hover:bg-blue-700 <?php echo $currentPage === 'entrada' ? 'bg-blue-700' : ''; ?>">
                        <i class="fas fa-sign-in-alt w-5"></i>
                        <span>Registrar Entrada</span>
                    </a>
                </li>
                <li>
                    <a href="<?php echo url('painel/veiculos/saida.php'); ?>" class="flex items-center space-x-3 p-3 rounded-lg hover:bg-blue-700 <?php echo $currentPage === 'saida' ? 'bg-blue-700' : ''; ?>">
                        <i class="fas fa-sign-out-alt w-5"></i>
                        <span>Registrar Saída</span>
                    </a>
                </li>
                <li>
                    <a href="<?php echo url('painel/veiculos/listar.php'); ?>" class="flex items-center space-x-3 p-3 rounded-lg hover:bg-blue-700 <?php echo $currentPage === 'listar' && $currentDir === 'veiculos' ? 'bg-blue-700' : ''; ?>">
                        <i class="fas fa-list w-5"></i>
                        <span>Listar Veículos</span>
                    </a>
                </li>
                
                <!-- Mensalistas -->
                <li>
                    <div class="text-xs text-blue-300 uppercase font-semibold mt-4 mb-2 px-3">Mensalistas</div>
                </li>
                <li>
                    <a href="<?php echo url('painel/mensalistas/cadastrar.php'); ?>" class="flex items-center space-x-3 p-3 rounded-lg hover:bg-blue-700 <?php echo $currentPage === 'cadastrar' && $currentDir === 'mensalistas' ? 'bg-blue-700' : ''; ?>">
                        <i class="fas fa-user-plus w-5"></i>
                        <span>Cadastrar</span>
                    </a>
                </li>
                <li>
                    <a href="<?php echo url('painel/mensalistas/listar.php'); ?>" class="flex items-center space-x-3 p-3 rounded-lg hover:bg-blue-700 <?php echo $currentPage === 'listar' && $currentDir === 'mensalistas' ? 'bg-blue-700' : ''; ?>">
                        <i class="fas fa-users w-5"></i>
                        <span>Listar Mensalistas</span>
                    </a>
                </li>
                
                <!-- Financeiro -->
                <li>
                    <div class="text-xs text-blue-300 uppercase font-semibold mt-4 mb-2 px-3">Financeiro</div>
                </li>
                <li>
                    <a href="<?php echo url('painel/financeiro/despesas.php'); ?>" class="flex items-center space-x-3 p-3 rounded-lg hover:bg-blue-700 <?php echo $currentPage === 'despesas' ? 'bg-blue-700' : ''; ?>">
                        <i class="fas fa-money-bill-wave w-5"></i>
                        <span>Despesas</span>
                    </a>
                </li>
                <li>
                    <a href="<?php echo url('painel/financeiro/receitas.php'); ?>" class="flex items-center space-x-3 p-3 rounded-lg hover:bg-blue-700 <?php echo $currentPage === 'receitas' ? 'bg-blue-700' : ''; ?>">
                        <i class="fas fa-dollar-sign w-5"></i>
                        <span>Receitas</span>
                    </a>
                </li>
                
                <!-- Relatórios -->
                <li>
                    <div class="text-xs text-blue-300 uppercase font-semibold mt-4 mb-2 px-3">Relatórios</div>
                </li>
                <li>
                    <a href="<?php echo url('painel/relatorios/diario.php'); ?>" class="flex items-center space-x-3 p-3 rounded-lg hover:bg-blue-700 <?php echo $currentPage === 'diario' ? 'bg-blue-700' : ''; ?>">
                        <i class="fas fa-calendar-day w-5"></i>
                        <span>Relatório Diário</span>
                    </a>
                </li>
                <li>
                    <a href="<?php echo url('painel/relatorios/semanal.php'); ?>" class="flex items-center space-x-3 p-3 rounded-lg hover:bg-blue-700 <?php echo $currentPage === 'semanal' ? 'bg-blue-700' : ''; ?>">
                        <i class="fas fa-calendar-week w-5"></i>
                        <span>Relatório Semanal</span>
                    </a>
                </li>
                <li>
                    <a href="<?php echo url('painel/relatorios/mensal.php'); ?>" class="flex items-center space-x-3 p-3 rounded-lg hover:bg-blue-700 <?php echo $currentPage === 'mensal' ? 'bg-blue-700' : ''; ?>">
                        <i class="fas fa-calendar-alt w-5"></i>
                        <span>Relatório Mensal</span>
                    </a>
                </li>
            </ul>
        </nav>
        
        <!-- Informações do Desenvolvedor -->
        <div class="absolute bottom-0 left-0 right-0 p-4 border-t border-blue-700 bg-blue-900">
            <p class="text-xs text-blue-300 text-center">
                Desenvolvido por<br>
                <a href="<?php echo SYSTEM_AUTHOR_URL; ?>" target="_blank" class="text-white hover:text-blue-200 font-semibold">
                    <?php echo SYSTEM_AUTHOR; ?>
                </a>
            </p>
        </div>
    </aside>
    
    <!-- Conteúdo Principal -->
    <div class="lg:ml-64">
        <!-- Top Bar -->
        <header class="bg-white shadow-md sticky top-0 z-30 no-print">
            <div class="flex items-center justify-between p-4">
                <!-- Botão Menu Mobile -->
                <button onclick="toggleMobileMenu()" class="lg:hidden text-gray-600 hover:text-blue-600">
                    <i class="fas fa-bars text-2xl"></i>
                </button>
                
                <!-- Título da Página -->
                <h2 class="text-xl font-bold text-gray-800 hidden sm:block">
                    <?php echo $pageTitle ?? 'Painel'; ?>
                </h2>
                
                <!-- Informações do Usuário -->
                <div class="flex items-center space-x-4">
                    <!-- Data e Hora -->
                    <div class="hidden md:flex items-center text-gray-600 text-sm">
                        <i class="fas fa-calendar-alt mr-2"></i>
                        <span id="currentDateTime"></span>
                    </div>
                    
                    <!-- Dropdown do Usuário -->
                    <div class="relative">
                        <button onclick="toggleUserMenu()" class="flex items-center space-x-2 bg-blue-50 hover:bg-blue-100 rounded-lg px-4 py-2">
                            <div class="w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center text-white font-bold">
                                <?php echo strtoupper(substr($user['nome'], 0, 1)); ?>
                            </div>
                            <div class="hidden sm:block text-left">
                                <p class="text-sm font-semibold text-gray-800"><?php echo htmlspecialchars($user['nome']); ?></p>
                                <p class="text-xs text-gray-500"><?php echo htmlspecialchars($user['usuario']); ?></p>
                            </div>
                            <i class="fas fa-chevron-down text-gray-600 text-sm"></i>
                        </button>
                        
                        <!-- Dropdown Menu -->
                        <div id="userMenu" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg py-2 z-50">
                            <a href="<?php echo url('painel/perfil.php'); ?>" class="flex items-center space-x-2 px-4 py-2 text-gray-700 hover:bg-gray-100">
                                <i class="fas fa-user-circle w-5"></i>
                                <span>Meu Perfil</span>
                            </a>
                            <div class="border-t border-gray-200 my-2"></div>
                            <a href="<?php echo url('logout.php'); ?>" class="flex items-center space-x-2 px-4 py-2 text-red-600 hover:bg-red-50">
                                <i class="fas fa-sign-out-alt w-5"></i>
                                <span>Sair</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </header>
        
        <!-- Área de Conteúdo -->
        <main class="p-4 md:p-6">
            <!-- Mensagens Flash -->
            <?php echo displayFlashMessage(); ?>
