<?php
/**
 * EstacionaFácil - Página Inicial
 * 
 * Redireciona para o painel se logado, ou para o login se não estiver logado
 * 
 * @author Dante Testa (https://dantetesta.com.br)
 * @version 1.0.0
 * @date 14/10/2025 19:54
 */

// Definir constante
define('ESTACIONAFACIL', true);

// Incluir configurações
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

// Verificar se está logado
if (isLoggedIn()) {
    // Redirecionar para o painel
    redirect('painel/');
} else {
    // Redirecionar para o login
    redirect('login.php');
}
