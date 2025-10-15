<?php
/**
 * EstacionaFácil - Logout
 * 
 * Encerra a sessão do usuário e redireciona para o login
 * 
 * @author Dante Testa (https://dantetesta.com.br)
 * @version 1.0.0
 * @date 14/10/2025 19:54
 */

// Definir constante
define('ESTACIONAFACIL', true);

// Incluir arquivos necessários
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

// Realizar logout
logoutUser();

// Redirecionar para login com mensagem
redirect('login.php?logout=1');
