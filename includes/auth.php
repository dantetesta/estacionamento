<?php
/**
 * EstacionaFácil - Sistema de Autenticação
 * 
 * Este arquivo contém as funções de autenticação e controle de acesso.
 * Deve ser incluído em todas as páginas que requerem login.
 * 
 * @author Dante Testa (https://dantetesta.com.br)
 * @version 1.0.0
 * @date 14/10/2025 19:54
 */

// Prevenir acesso direto ao arquivo
if (!defined('ESTACIONAFACIL')) {
    die('Acesso negado');
}

/**
 * Inicia a sessão de forma segura
 */
function startSecureSession() {
    // Verificar se a sessão já foi iniciada
    if (session_status() === PHP_SESSION_NONE) {
        // Configurar nome da sessão
        session_name(SESSION_NAME);
        
        // Configurar parâmetros de segurança do cookie
        session_set_cookie_params([
            'lifetime' => SESSION_LIFETIME,
            'path' => '/',
            'domain' => $_SERVER['HTTP_HOST'],
            'secure' => isset($_SERVER['HTTPS']), // True se HTTPS
            'httponly' => true, // Não acessível via JavaScript
            'samesite' => 'Strict' // Proteção CSRF
        ]);
        
        // Iniciar sessão
        session_start();
        
        // Regenerar ID da sessão periodicamente (a cada 30 minutos)
        if (!isset($_SESSION['last_regeneration'])) {
            $_SESSION['last_regeneration'] = time();
        } elseif (time() - $_SESSION['last_regeneration'] > 1800) {
            session_regenerate_id(true);
            $_SESSION['last_regeneration'] = time();
        }
        
        // Verificar timeout de inatividade
        if (isset($_SESSION['last_activity'])) {
            $inactiveTime = time() - $_SESSION['last_activity'];
            
            if ($inactiveTime > SESSION_LIFETIME) {
                // Sessão expirada por inatividade
                logoutUser();
                redirect('login.php?expired=1');
            }
        }
        
        // Atualizar timestamp de última atividade
        $_SESSION['last_activity'] = time();
        
        // Validar IP e User Agent (segurança adicional)
        if (isset($_SESSION['user_id'])) {
            if (!isset($_SESSION['user_ip'])) {
                $_SESSION['user_ip'] = $_SERVER['REMOTE_ADDR'];
            }
            
            if (!isset($_SESSION['user_agent'])) {
                $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
            }
            
            // Verificar se IP ou User Agent mudaram (possível sequestro de sessão)
            if ($_SESSION['user_ip'] !== $_SERVER['REMOTE_ADDR'] || 
                $_SESSION['user_agent'] !== $_SERVER['HTTP_USER_AGENT']) {
                logoutUser();
                redirect('login.php?security=1');
            }
        }
    }
}

/**
 * Verifica se o usuário está autenticado
 * Redireciona para login se não estiver
 */
function requireAuth() {
    if (!isLoggedIn()) {
        redirect('login.php');
    }
}

/**
 * Realiza o login do usuário
 * 
 * @param string $username Nome de usuário
 * @param string $password Senha
 * @return array Array com sucesso e mensagem
 */
function loginUser($username, $password) {
    $db = getDB();
    
    // Sanitizar entrada
    $username = sanitizeString($username);
    
    // Verificar tentativas de login
    if (isLoginBlocked($username)) {
        return [
            'success' => false,
            'message' => 'Muitas tentativas de login. Tente novamente em ' . 
                        ceil((LOGIN_BLOCK_TIME - getLoginBlockTime($username)) / 60) . ' minutos.'
        ];
    }
    
    // Buscar usuário no banco
    $sql = "SELECT * FROM usuarios WHERE usuario = ? LIMIT 1";
    $user = $db->selectOne($sql, [$username]);
    
    if (!$user) {
        // Registrar tentativa falha
        registerLoginAttempt($username, false);
        
        return [
            'success' => false,
            'message' => 'Usuário ou senha incorretos.'
        ];
    }
    
    // Verificar senha
    if (!verifyPassword($password, $user['senha'])) {
        // Registrar tentativa falha
        registerLoginAttempt($username, false);
        
        return [
            'success' => false,
            'message' => 'Usuário ou senha incorretos.'
        ];
    }
    
    // Login bem-sucedido
    // Limpar tentativas de login
    clearLoginAttempts($username);
    
    // Regenerar ID da sessão (prevenir fixação de sessão)
    session_regenerate_id(true);
    
    // Armazenar dados do usuário na sessão
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['usuario'];
    $_SESSION['user_full_name'] = $user['nome'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_ip'] = $_SERVER['REMOTE_ADDR'];
    $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
    $_SESSION['login_time'] = time();
    $_SESSION['last_activity'] = time();
    $_SESSION['last_regeneration'] = time();
    
    // Atualizar último login no banco
    $updateSql = "UPDATE usuarios SET ultimo_login = NOW() WHERE id = ?";
    $db->execute($updateSql, [$user['id']]);
    
    // Registrar log de login (opcional)
    logActivity('login', 'Usuário ' . $username . ' fez login');
    
    return [
        'success' => true,
        'message' => 'Login realizado com sucesso!'
    ];
}

/**
 * Realiza o logout do usuário
 */
function logoutUser() {
    // Registrar log de logout (opcional)
    if (isLoggedIn()) {
        logActivity('logout', 'Usuário ' . $_SESSION['user_name'] . ' fez logout');
    }
    
    // Limpar todas as variáveis de sessão
    $_SESSION = [];
    
    // Destruir o cookie de sessão
    if (isset($_COOKIE[SESSION_NAME])) {
        setcookie(SESSION_NAME, '', time() - 3600, '/');
    }
    
    // Destruir a sessão
    session_destroy();
}

/**
 * Verifica se o login está bloqueado por tentativas excessivas
 * 
 * @param string $username Nome de usuário
 * @return bool True se bloqueado
 */
function isLoginBlocked($username) {
    if (!isset($_SESSION['login_attempts'][$username])) {
        return false;
    }
    
    $attempts = $_SESSION['login_attempts'][$username];
    
    // Verificar número de tentativas
    if ($attempts['count'] >= MAX_LOGIN_ATTEMPTS) {
        // Verificar se o tempo de bloqueio passou
        if (time() - $attempts['last_attempt'] < LOGIN_BLOCK_TIME) {
            return true;
        } else {
            // Tempo de bloqueio passou, limpar tentativas
            clearLoginAttempts($username);
            return false;
        }
    }
    
    return false;
}

/**
 * Retorna o tempo restante de bloqueio em segundos
 * 
 * @param string $username Nome de usuário
 * @return int Tempo em segundos
 */
function getLoginBlockTime($username) {
    if (!isset($_SESSION['login_attempts'][$username])) {
        return 0;
    }
    
    $attempts = $_SESSION['login_attempts'][$username];
    $elapsed = time() - $attempts['last_attempt'];
    
    return max(0, LOGIN_BLOCK_TIME - $elapsed);
}

/**
 * Registra uma tentativa de login
 * 
 * @param string $username Nome de usuário
 * @param bool $success Se foi bem-sucedida
 */
function registerLoginAttempt($username, $success) {
    if (!isset($_SESSION['login_attempts'])) {
        $_SESSION['login_attempts'] = [];
    }
    
    if (!isset($_SESSION['login_attempts'][$username])) {
        $_SESSION['login_attempts'][$username] = [
            'count' => 0,
            'last_attempt' => 0
        ];
    }
    
    if (!$success) {
        $_SESSION['login_attempts'][$username]['count']++;
        $_SESSION['login_attempts'][$username]['last_attempt'] = time();
    }
}

/**
 * Limpa as tentativas de login de um usuário
 * 
 * @param string $username Nome de usuário
 */
function clearLoginAttempts($username) {
    if (isset($_SESSION['login_attempts'][$username])) {
        unset($_SESSION['login_attempts'][$username]);
    }
}

/**
 * Registra uma atividade no log (opcional)
 * 
 * @param string $action Ação realizada
 * @param string $description Descrição da ação
 */
function logActivity($action, $description) {
    // Esta função pode ser expandida para registrar em banco de dados
    // Por enquanto, apenas registra no log de erros do PHP
    if (ENVIRONMENT === 'development') {
        error_log("[EstacionaFácil] $action: $description");
    }
}

/**
 * Verifica permissão de acesso (para expansão futura)
 * 
 * @param string $permission Permissão requerida
 * @return bool True se tem permissão
 */
function hasPermission($permission) {
    // Por enquanto, todos os usuários logados têm todas as permissões
    // Esta função pode ser expandida para implementar níveis de acesso
    return isLoggedIn();
}

/**
 * Valida força da senha
 * 
 * @param string $password Senha a validar
 * @return array Array com válido e mensagem
 */
function validatePasswordStrength($password) {
    $errors = [];
    
    // Mínimo 8 caracteres
    if (strlen($password) < 8) {
        $errors[] = 'A senha deve ter no mínimo 8 caracteres';
    }
    
    // Pelo menos uma letra maiúscula
    if (!preg_match('/[A-Z]/', $password)) {
        $errors[] = 'A senha deve conter pelo menos uma letra maiúscula';
    }
    
    // Pelo menos uma letra minúscula
    if (!preg_match('/[a-z]/', $password)) {
        $errors[] = 'A senha deve conter pelo menos uma letra minúscula';
    }
    
    // Pelo menos um número
    if (!preg_match('/[0-9]/', $password)) {
        $errors[] = 'A senha deve conter pelo menos um número';
    }
    
    // Pelo menos um caractere especial (opcional, comentado)
    // if (!preg_match('/[!@#$%^&*(),.?":{}|<>]/', $password)) {
    //     $errors[] = 'A senha deve conter pelo menos um caractere especial';
    // }
    
    if (empty($errors)) {
        return [
            'valid' => true,
            'message' => 'Senha forte'
        ];
    }
    
    return [
        'valid' => false,
        'message' => implode('. ', $errors)
    ];
}

// Iniciar sessão automaticamente ao incluir este arquivo
startSecureSession();
