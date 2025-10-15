<?php
/**
 * Teste simples para verificar se o PHP está funcionando
 */

echo "<h1>EstacionaFácil - Teste PHP</h1>";
echo "<p>Servidor PHP funcionando corretamente!</p>";
echo "<p>Data/Hora: " . date('d/m/Y H:i:s') . "</p>";

// Testar sessão
session_start();
echo "<p>Sessão iniciada: " . session_id() . "</p>";

// Testar conexão com banco (simulada)
echo "<p>Sistema pronto para instalação!</p>";

echo '<p><a href="/instalar.php">Ir para Instalador</a></p>';
echo '<p><a href="/login.php">Ir para Login</a></p>';
?>
