<?php
/**
 * Teste de Formatação Monetária BRL
 * 
 * @author Dante Testa (https://dantetesta.com.br)
 * @date 14/10/2025 20:51
 */

define('ESTACIONAFACIL', true);
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/functions.php';

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teste de Formatação - EstacionaFácil</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-8">
    <div class="max-w-4xl mx-auto bg-white rounded-lg shadow-lg p-8">
        <h1 class="text-3xl font-bold text-gray-800 mb-6">
            <i class="fas fa-check-circle text-green-600"></i>
            Teste de Formatação Monetária BRL
        </h1>
        
        <div class="space-y-4">
            <div class="p-4 bg-blue-50 rounded-lg">
                <p class="text-sm text-gray-600 mb-2">Valor: 10</p>
                <p class="text-2xl font-bold text-blue-600"><?php echo formatMoney(10); ?></p>
            </div>
            
            <div class="p-4 bg-green-50 rounded-lg">
                <p class="text-sm text-gray-600 mb-2">Valor: 150.50</p>
                <p class="text-2xl font-bold text-green-600"><?php echo formatMoney(150.50); ?></p>
            </div>
            
            <div class="p-4 bg-purple-50 rounded-lg">
                <p class="text-sm text-gray-600 mb-2">Valor: 1234.56</p>
                <p class="text-2xl font-bold text-purple-600"><?php echo formatMoney(1234.56); ?></p>
            </div>
            
            <div class="p-4 bg-orange-50 rounded-lg">
                <p class="text-sm text-gray-600 mb-2">Valor: 10500.75</p>
                <p class="text-2xl font-bold text-orange-600"><?php echo formatMoney(10500.75); ?></p>
            </div>
            
            <div class="p-4 bg-red-50 rounded-lg">
                <p class="text-sm text-gray-600 mb-2">Valor: 0</p>
                <p class="text-2xl font-bold text-red-600"><?php echo formatMoney(0); ?></p>
            </div>
        </div>
        
        <div class="mt-8 p-6 bg-green-50 border-l-4 border-green-500 rounded-r-lg">
            <h3 class="font-bold text-green-800 mb-2">✅ Formatação Correta BRL</h3>
            <ul class="text-sm text-green-700 space-y-1">
                <li>✓ Símbolo: R$</li>
                <li>✓ Separador decimal: vírgula (,)</li>
                <li>✓ Separador de milhares: ponto (.)</li>
                <li>✓ Casas decimais: 2</li>
                <li>✓ Exemplo: R$ 1.234,56</li>
            </ul>
        </div>
        
        <div class="mt-6 text-center">
            <a href="/painel/" class="inline-block px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-lg">
                Voltar ao Dashboard
            </a>
        </div>
    </div>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</body>
</html>
