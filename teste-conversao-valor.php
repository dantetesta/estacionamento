<?php
/**
 * Teste de Conversão de Valores Monetários
 * 
 * @author Dante Testa (https://dantetesta.com.br)
 * @date 14/10/2025 21:03
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
    <title>Teste de Conversão - EstacionaFácil</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100 p-8">
    <div class="max-w-4xl mx-auto bg-white rounded-lg shadow-lg p-8">
        <h1 class="text-3xl font-bold text-gray-800 mb-6">
            <i class="fas fa-calculator text-blue-600 mr-2"></i>
            Teste de Conversão de Valores
        </h1>
        
        <div class="space-y-4">
            <?php
            $testCases = [
                '10' => 'Valor simples: 10',
                '10.00' => 'Valor com decimais (ponto): 10.00',
                '10,00' => 'Valor com decimais (vírgula): 10,00',
                '1.000,00' => 'Valor com milhares: 1.000,00',
                'R$ 10' => 'Com símbolo: R$ 10',
                'R$ 10,50' => 'Com símbolo e decimais: R$ 10,50',
                'R$ 1.234,56' => 'Valor completo: R$ 1.234,56',
                '150.50' => 'Valor decimal ponto: 150.50',
                '150,50' => 'Valor decimal vírgula: 150,50',
            ];
            
            foreach ($testCases as $input => $description) {
                $result = moneyToFloat($input);
                $formatted = formatMoney($result);
                $isCorrect = true;
                
                // Validar se a conversão está correta
                if ($input === '10' && $result != 10) $isCorrect = false;
                if ($input === '10.00' && $result != 10) $isCorrect = false;
                if ($input === '10,00' && $result != 10) $isCorrect = false;
                if ($input === '1.000,00' && $result != 1000) $isCorrect = false;
                if ($input === 'R$ 10' && $result != 10) $isCorrect = false;
                if ($input === 'R$ 10,50' && $result != 10.50) $isCorrect = false;
                if ($input === 'R$ 1.234,56' && $result != 1234.56) $isCorrect = false;
                if ($input === '150.50' && $result != 150.50) $isCorrect = false;
                if ($input === '150,50' && $result != 150.50) $isCorrect = false;
                
                $bgColor = $isCorrect ? 'bg-green-50 border-green-200' : 'bg-red-50 border-red-200';
                $textColor = $isCorrect ? 'text-green-800' : 'text-red-800';
                $icon = $isCorrect ? 'fa-check-circle text-green-600' : 'fa-times-circle text-red-600';
            ?>
                <div class="p-4 <?php echo $bgColor; ?> border rounded-lg">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <p class="text-sm text-gray-600 mb-1"><?php echo $description; ?></p>
                            <div class="grid grid-cols-3 gap-4 mt-2">
                                <div>
                                    <p class="text-xs text-gray-500">Entrada:</p>
                                    <p class="font-mono font-bold <?php echo $textColor; ?>"><?php echo htmlspecialchars($input); ?></p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500">Float:</p>
                                    <p class="font-mono font-bold <?php echo $textColor; ?>"><?php echo $result; ?></p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500">Formatado:</p>
                                    <p class="font-mono font-bold <?php echo $textColor; ?>"><?php echo $formatted; ?></p>
                                </div>
                            </div>
                        </div>
                        <i class="fas <?php echo $icon; ?> text-2xl ml-4"></i>
                    </div>
                </div>
            <?php } ?>
        </div>
        
        <div class="mt-8 p-6 bg-blue-50 border-l-4 border-blue-500 rounded-r-lg">
            <h3 class="font-bold text-blue-800 mb-2">
                <i class="fas fa-info-circle mr-2"></i>
                Como Funciona
            </h3>
            <ul class="text-sm text-blue-700 space-y-1">
                <li>✓ Valores numéricos puros (10, 10.00) são convertidos diretamente</li>
                <li>✓ Valores com vírgula (10,50) têm a vírgula substituída por ponto</li>
                <li>✓ Valores com milhares (1.000,00) têm o ponto removido e vírgula substituída</li>
                <li>✓ Símbolos de moeda (R$) são removidos automaticamente</li>
            </ul>
        </div>
        
        <div class="mt-6 text-center">
            <a href="/painel/" class="inline-block px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-lg">
                <i class="fas fa-arrow-left mr-2"></i>Voltar ao Dashboard
            </a>
        </div>
    </div>
</body>
</html>
