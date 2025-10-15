<?php
/**
 * EstacionaFácil - Funções Auxiliares
 * 
 * Este arquivo contém funções auxiliares utilizadas em todo o sistema
 * para formatação, validação, sanitização e operações comuns.
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
// FUNÇÕES DE FORMATAÇÃO
// ============================================

/**
 * Formata um valor monetário para exibição
 * 
 * @param float $value Valor numérico
 * @param bool $showSymbol Exibir símbolo da moeda
 * @return string Valor formatado
 */
function formatMoney($value, $showSymbol = true) {
    // Garantir que o valor seja numérico
    $value = floatval($value);
    
    // Formatar o valor
    $formatted = number_format(
        $value,
        2,  // Sempre 2 casas decimais
        ',', // Vírgula para decimais
        '.'  // Ponto para milhares
    );
    
    // Retornar com ou sem símbolo
    return $showSymbol ? 'R$ ' . $formatted : $formatted;
}

/**
 * Formata uma data para exibição
 * 
 * @param string $date Data no formato Y-m-d ou Y-m-d H:i:s
 * @param string $format Formato desejado (padrão: DATE_FORMAT)
 * @return string Data formatada
 */
function formatDate($date, $format = DATE_FORMAT) {
    if (empty($date) || $date === '0000-00-00' || $date === '0000-00-00 00:00:00') {
        return '-';
    }
    
    $timestamp = strtotime($date);
    return date($format, $timestamp);
}

/**
 * Formata uma data e hora para exibição
 * 
 * @param string $datetime Data e hora no formato Y-m-d H:i:s
 * @return string Data e hora formatada
 */
function formatDateTime($datetime) {
    return formatDate($datetime, DATETIME_FORMAT);
}

/**
 * Formata uma hora para exibição
 * 
 * @param string $time Hora no formato H:i:s
 * @return string Hora formatada
 */
function formatTime($time) {
    if (empty($time)) {
        return '-';
    }
    
    return date(TIME_FORMAT, strtotime($time));
}

/**
 * Formata uma placa de veículo
 * 
 * @param string $plate Placa sem formatação
 * @return string Placa formatada (ABC-1234 ou ABC1D23)
 */
function formatPlate($plate) {
    $plate = strtoupper(preg_replace('/[^A-Z0-9]/', '', $plate));
    
    // Formato antigo: ABC-1234
    if (preg_match('/^[A-Z]{3}[0-9]{4}$/', $plate)) {
        return substr($plate, 0, 3) . '-' . substr($plate, 3);
    }
    
    // Formato Mercosul: ABC1D23
    if (preg_match('/^[A-Z]{3}[0-9][A-Z][0-9]{2}$/', $plate)) {
        return substr($plate, 0, 3) . substr($plate, 3, 1) . substr($plate, 4, 1) . substr($plate, 5);
    }
    
    return $plate;
}

/**
 * Formata um telefone
 * 
 * @param string $phone Telefone sem formatação
 * @return string Telefone formatado
 */
function formatPhone($phone) {
    $phone = preg_replace('/[^0-9]/', '', $phone);
    
    if (strlen($phone) === 11) {
        // Celular: (11) 98765-4321
        return '(' . substr($phone, 0, 2) . ') ' . substr($phone, 2, 5) . '-' . substr($phone, 7);
    } elseif (strlen($phone) === 10) {
        // Fixo: (11) 3456-7890
        return '(' . substr($phone, 0, 2) . ') ' . substr($phone, 2, 4) . '-' . substr($phone, 6);
    }
    
    return $phone;
}

// ============================================
// FUNÇÕES DE VALIDAÇÃO
// ============================================

/**
 * Valida uma placa de veículo
 * 
 * @param string $plate Placa a validar
 * @return bool True se válida
 */
function validatePlate($plate) {
    $plate = strtoupper(preg_replace('/[^A-Z0-9]/', '', $plate));
    
    // Formato antigo: ABC1234
    if (preg_match('/^[A-Z]{3}[0-9]{4}$/', $plate)) {
        return true;
    }
    
    // Formato Mercosul: ABC1D23
    if (preg_match('/^[A-Z]{3}[0-9][A-Z][0-9]{2}$/', $plate)) {
        return true;
    }
    
    return false;
}

/**
 * Valida um email
 * 
 * @param string $email Email a validar
 * @return bool True se válido
 */
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Valida um telefone brasileiro
 * 
 * @param string $phone Telefone a validar
 * @return bool True se válido
 */
function validatePhone($phone) {
    $phone = preg_replace('/[^0-9]/', '', $phone);
    return strlen($phone) >= 10 && strlen($phone) <= 11;
}

/**
 * Valida uma data no formato brasileiro (dd/mm/aaaa)
 * 
 * @param string $date Data a validar
 * @return bool True se válida
 */
function validateDate($date) {
    if (!preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', $date, $matches)) {
        return false;
    }
    
    return checkdate($matches[2], $matches[1], $matches[3]);
}

// ============================================
// FUNÇÕES DE SANITIZAÇÃO
// ============================================

/**
 * Sanitiza uma string removendo tags HTML e caracteres especiais
 * 
 * @param string $string String a sanitizar
 * @return string String sanitizada
 */
function sanitizeString($string) {
    return htmlspecialchars(strip_tags(trim($string)), ENT_QUOTES, 'UTF-8');
}

/**
 * Sanitiza um email
 * 
 * @param string $email Email a sanitizar
 * @return string Email sanitizado
 */
function sanitizeEmail($email) {
    return filter_var(trim($email), FILTER_SANITIZE_EMAIL);
}

/**
 * Sanitiza um número
 * 
 * @param mixed $number Número a sanitizar
 * @return float Número sanitizado
 */
function sanitizeNumber($number) {
    return floatval(preg_replace('/[^0-9.-]/', '', $number));
}

/**
 * Sanitiza uma placa de veículo
 * 
 * @param string $plate Placa a sanitizar
 * @return string Placa sanitizada
 */
function sanitizePlate($plate) {
    return strtoupper(preg_replace('/[^A-Z0-9]/', '', $plate));
}

// ============================================
// FUNÇÕES DE CONVERSÃO
// ============================================

/**
 * Converte data do formato brasileiro (dd/mm/aaaa) para MySQL (yyyy-mm-dd)
 * 
 * @param string $date Data no formato brasileiro
 * @return string Data no formato MySQL
 */
function dateToMysql($date) {
    if (empty($date)) {
        return null;
    }
    
    if (preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', $date, $matches)) {
        return $matches[3] . '-' . $matches[2] . '-' . $matches[1];
    }
    
    return $date;
}

/**
 * Converte data do formato MySQL (yyyy-mm-dd) para brasileiro (dd/mm/aaaa)
 * 
 * @param string $date Data no formato MySQL
 * @return string Data no formato brasileiro
 */
function dateFromMysql($date) {
    if (empty($date) || $date === '0000-00-00') {
        return '';
    }
    
    if (preg_match('/^(\d{4})-(\d{2})-(\d{2})/', $date, $matches)) {
        return $matches[3] . '/' . $matches[2] . '/' . $matches[1];
    }
    
    return $date;
}

/**
 * Converte valor monetário do formato brasileiro para float
 * 
 * @param string $value Valor no formato brasileiro (1.234,56 ou 10,50 ou 10)
 * @return float Valor numérico
 */
function moneyToFloat($value) {
    // Se já for numérico, retornar direto
    if (is_numeric($value)) {
        return floatval($value);
    }
    
    // Remover símbolo de moeda e espaços
    $value = str_replace(['R$', 'R', '$', ' '], '', $value);
    
    // Remover pontos (separador de milhares)
    $value = str_replace('.', '', $value);
    
    // Substituir vírgula por ponto (separador decimal)
    $value = str_replace(',', '.', $value);
    
    return floatval($value);
}

// ============================================
// FUNÇÕES DE SESSÃO E MENSAGENS
// ============================================

/**
 * Define uma mensagem flash na sessão
 * 
 * @param string $type Tipo da mensagem (success, error, warning, info)
 * @param string $message Mensagem a exibir
 */
function setFlashMessage($type, $message) {
    $_SESSION['flash_message'] = [
        'type' => $type,
        'message' => $message
    ];
}

/**
 * Retorna e remove a mensagem flash da sessão
 * 
 * @return array|null Array com tipo e mensagem ou null
 */
function getFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $message;
    }
    return null;
}

/**
 * Exibe a mensagem flash em HTML
 * 
 * @return string HTML da mensagem ou string vazia
 */
function displayFlashMessage() {
    $flash = getFlashMessage();
    
    if ($flash === null) {
        return '';
    }
    
    $colors = [
        'success' => 'bg-green-100 border-green-400 text-green-700',
        'error' => 'bg-red-100 border-red-400 text-red-700',
        'warning' => 'bg-yellow-100 border-yellow-400 text-yellow-700',
        'info' => 'bg-blue-100 border-blue-400 text-blue-700'
    ];
    
    $color = $colors[$flash['type']] ?? $colors['info'];
    
    return '<div class="' . $color . ' border px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline">' . htmlspecialchars($flash['message']) . '</span>
            </div>';
}

// ============================================
// FUNÇÕES DE SEGURANÇA
// ============================================

/**
 * Gera um token CSRF
 * 
 * @return string Token gerado
 */
function generateCsrfToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Valida um token CSRF
 * 
 * @param string $token Token a validar
 * @return bool True se válido
 */
function validateCsrfToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Gera um hash seguro de senha
 * 
 * @param string $password Senha em texto plano
 * @return string Hash da senha
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
}

/**
 * Verifica se uma senha corresponde ao hash
 * 
 * @param string $password Senha em texto plano
 * @param string $hash Hash armazenado
 * @return bool True se corresponde
 */
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

// ============================================
// FUNÇÕES DE UTILIDADE
// ============================================

/**
 * Retorna o nome do tipo de veículo
 * 
 * @param string $type Código do tipo
 * @return string Nome do tipo
 */
function getVehicleTypeName($type) {
    return VEHICLE_TYPES[$type] ?? $type;
}

/**
 * Retorna o nome da forma de pagamento
 * 
 * @param string $method Código da forma de pagamento
 * @return string Nome da forma de pagamento
 */
function getPaymentMethodName($method) {
    return PAYMENT_METHODS[$method] ?? $method;
}

/**
 * Retorna o nome da categoria de despesa
 * 
 * @param string $category Código da categoria
 * @return string Nome da categoria
 */
function getExpenseCategoryName($category) {
    return EXPENSE_CATEGORIES[$category] ?? $category;
}

/**
 * Calcula a diferença em horas entre duas datas/horas
 * 
 * @param string $start Data/hora inicial
 * @param string $end Data/hora final
 * @return float Diferença em horas
 */
function calculateHoursDifference($start, $end) {
    $startTime = strtotime($start);
    $endTime = strtotime($end);
    $diff = $endTime - $startTime;
    return round($diff / 3600, 2);
}

/**
 * Verifica se o usuário está logado
 * 
 * @return bool True se logado
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Retorna os dados do usuário logado
 * 
 * @return array|null Dados do usuário ou null
 */
function getLoggedUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    return [
        'id' => $_SESSION['user_id'] ?? null,
        'usuario' => $_SESSION['user_name'] ?? null,
        'nome' => $_SESSION['user_full_name'] ?? null
    ];
}

/**
 * Gera um código único
 * 
 * @param int $length Tamanho do código
 * @return string Código gerado
 */
function generateUniqueCode($length = 8) {
    return strtoupper(substr(md5(uniqid(rand(), true)), 0, $length));
}

/**
 * Debuga uma variável (apenas em desenvolvimento)
 * 
 * @param mixed $var Variável a debugar
 * @param bool $die Encerrar execução após debug
 */
function debug($var, $die = false) {
    if (ENVIRONMENT === 'development') {
        echo '<pre>';
        print_r($var);
        echo '</pre>';
        
        if ($die) {
            die();
        }
    }
}
