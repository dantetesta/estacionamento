<?php
/**
 * EstacionaFácil - Configurações Gerais do Sistema
 * 
 * Este arquivo contém todas as configurações globais do sistema,
 * incluindo timezone, sessões, URLs e constantes importantes.
 * 
 * @author Dante Testa (https://dantetesta.com.br)
 * @version 1.0.0
 * @date 14/10/2025 19:54
 */

// Prevenir acesso direto ao arquivo
if (!defined('ESTACIONAFACIL')) {
    die('Acesso negado');
}

// ============================================
// CONFIGURAÇÕES GERAIS
// ============================================

// Versão do sistema
define('SYSTEM_VERSION', '1.0.0');

// Nome do sistema
define('SYSTEM_NAME', 'EstacionaFácil');

// Autor do sistema
define('SYSTEM_AUTHOR', 'Dante Testa');
define('SYSTEM_AUTHOR_URL', 'https://dantetesta.com.br');

// Timezone padrão (Brasil)
date_default_timezone_set('America/Sao_Paulo');

// ============================================
// CONFIGURAÇÕES DE AMBIENTE
// ============================================

// Ambiente: 'development' ou 'production'
define('ENVIRONMENT', 'production');

// Exibir erros (apenas em desenvolvimento)
if (ENVIRONMENT === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// ============================================
// CONFIGURAÇÕES DE SESSÃO
// ============================================

// Nome da sessão
define('SESSION_NAME', 'estacionafacil_session');

// Tempo de expiração da sessão (30 minutos em segundos)
define('SESSION_LIFETIME', 1800);

// Configurações de segurança da sessão
ini_set('session.cookie_httponly', 1); // Prevenir acesso via JavaScript
ini_set('session.use_only_cookies', 1); // Usar apenas cookies
ini_set('session.cookie_secure', 0); // Mudar para 1 se usar HTTPS
ini_set('session.cookie_samesite', 'Strict'); // Proteção CSRF

// ============================================
// CONFIGURAÇÕES DE URL
// ============================================

// URL base do sistema (ajustar conforme necessário)
// Exemplo: https://seudominio.com.br ou https://seudominio.com.br/estacionamento
define('BASE_URL', 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['SCRIPT_NAME']));

// Remover barras duplicadas
define('SITE_URL', rtrim(str_replace('//', '/', BASE_URL), '/'));

// ============================================
// CONFIGURAÇÕES DE DIRETÓRIOS
// ============================================

// Diretório raiz do sistema
define('ROOT_PATH', dirname(__DIR__));

// Diretórios principais
define('CONFIG_PATH', ROOT_PATH . '/config');
define('INCLUDES_PATH', ROOT_PATH . '/includes');
define('PAINEL_PATH', ROOT_PATH . '/painel');
define('ASSETS_PATH', ROOT_PATH . '/assets');
define('API_PATH', ROOT_PATH . '/api');

// ============================================
// CONFIGURAÇÕES DE SEGURANÇA
// ============================================

// Chave secreta para hash (gerar uma aleatória em produção)
define('SECRET_KEY', 'EstacionaFacil_2025_SecretKey_' . md5(__DIR__));

// Número de tentativas de login antes de bloquear
define('MAX_LOGIN_ATTEMPTS', 5);

// Tempo de bloqueio após tentativas falhas (15 minutos)
define('LOGIN_BLOCK_TIME', 900);

// ============================================
// CONFIGURAÇÕES DE PAGINAÇÃO
// ============================================

// Itens por página em listagens
define('ITEMS_PER_PAGE', 20);

// ============================================
// CONFIGURAÇÕES DE FORMATO
// ============================================

// Formato de data para exibição
define('DATE_FORMAT', 'd/m/Y');

// Formato de data e hora para exibição
define('DATETIME_FORMAT', 'd/m/Y H:i');

// Formato de hora para exibição
define('TIME_FORMAT', 'H:i');

// Formato de moeda
define('CURRENCY_SYMBOL', 'R$');
define('CURRENCY_DECIMALS', 2);
define('CURRENCY_DECIMAL_SEPARATOR', ',');
define('CURRENCY_THOUSANDS_SEPARATOR', '.');

// ============================================
// TIPOS DE VEÍCULOS E VALORES PADRÃO
// ============================================

// Tipos de veículos disponíveis
define('VEHICLE_TYPES', [
    'pequeno' => 'Pequeno (Carro)',
    'medio' => 'Médio (SUV)',
    'grande' => 'Grande (Van)',
    'caminhao' => 'Caminhão',
    'onibus' => 'Ônibus'
]);

// Valores padrão (podem ser alterados nas configurações)
define('DEFAULT_PRICES', [
    'pequeno' => 10.00,
    'medio' => 20.00,
    'grande' => 30.00,
    'caminhao' => 50.00,
    'onibus' => 60.00
]);

// ============================================
// FORMAS DE PAGAMENTO
// ============================================

define('PAYMENT_METHODS', [
    'dinheiro' => 'Dinheiro',
    'pix' => 'PIX',
    'debito' => 'Cartão de Débito',
    'credito' => 'Cartão de Crédito'
]);

// ============================================
// CATEGORIAS DE DESPESAS
// ============================================

define('EXPENSE_CATEGORIES', [
    'energia' => 'Energia Elétrica',
    'internet' => 'Internet',
    'agua' => 'Água',
    'salario' => 'Salário',
    'manutencao' => 'Manutenção',
    'limpeza' => 'Limpeza',
    'seguranca' => 'Segurança',
    'impostos' => 'Impostos',
    'outros' => 'Outros'
]);

// ============================================
// CONFIGURAÇÕES DE RELATÓRIOS
// ============================================

// Cores para gráficos (Chart.js)
define('CHART_COLORS', [
    'primary' => '#3B82F6',
    'success' => '#10B981',
    'warning' => '#F59E0B',
    'danger' => '#EF4444',
    'info' => '#06B6D4',
    'secondary' => '#6B7280'
]);

// ============================================
// MENSAGENS DO SISTEMA
// ============================================

define('MESSAGES', [
    'success' => 'Operação realizada com sucesso!',
    'error' => 'Ocorreu um erro. Por favor, tente novamente.',
    'unauthorized' => 'Você não tem permissão para acessar esta página.',
    'session_expired' => 'Sua sessão expirou. Por favor, faça login novamente.',
    'invalid_data' => 'Dados inválidos. Por favor, verifique e tente novamente.',
    'not_found' => 'Registro não encontrado.',
    'already_exists' => 'Este registro já existe.',
    'delete_success' => 'Registro excluído com sucesso!',
    'update_success' => 'Registro atualizado com sucesso!',
    'create_success' => 'Registro criado com sucesso!'
]);

// ============================================
// FUNÇÕES AUXILIARES DE CONFIGURAÇÃO
// ============================================

/**
 * Retorna a URL completa do site
 * 
 * @param string $path Caminho relativo
 * @return string URL completa
 */
function url($path = '') {
    $path = ltrim($path, '/');
    $baseUrl = 'http://' . $_SERVER['HTTP_HOST'];
    return $baseUrl . '/' . $path;
}

/**
 * Retorna o caminho completo de um arquivo
 * 
 * @param string $path Caminho relativo
 * @return string Caminho completo
 */
function path($path = '') {
    $path = ltrim($path, '/');
    return ROOT_PATH . '/' . $path;
}

/**
 * Retorna a URL de um asset (CSS, JS, imagem)
 * 
 * @param string $path Caminho do asset
 * @return string URL do asset
 */
function asset($path) {
    $path = ltrim($path, '/');
    return SITE_URL . '/assets/' . $path;
}

/**
 * Redireciona para uma URL
 * 
 * @param string $path Caminho de destino
 * @param int $statusCode Código HTTP de status
 */
function redirect($path, $statusCode = 302) {
    header('Location: ' . url($path), true, $statusCode);
    exit;
}

/**
 * Retorna uma mensagem do sistema
 * 
 * @param string $key Chave da mensagem
 * @return string Mensagem
 */
function message($key) {
    return MESSAGES[$key] ?? 'Mensagem não encontrada';
}
