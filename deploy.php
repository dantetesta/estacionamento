#!/usr/bin/env php
<?php
/**
 * EstacionaFácil - Script de Deploy FTP
 * 
 * Execute: php deploy.php
 * 
 * @author Dante Testa (https://dantetesta.com.br)
 * @version 1.0.0
 * @date 14/10/2025 21:07
 */

echo "🚀 EstacionaFácil - Deploy FTP\n";
echo "================================\n\n";

// Configurações FTP
$ftpHost = '187.33.241.61';
$ftpUser = 'site1@danteflix.com.br';
$ftpPass = '{Xt8ht}J#cTYjm{L';
$ftpDir = '/';

// Diretório local
$localDir = __DIR__;

// Arquivos/pastas a excluir
$exclude = [
    '.git',
    '.gitignore',
    'node_modules',
    'sync_config.jsonc',
    'deploy.sh',
    'deploy.php',
    'teste-formatacao.php',
    'teste-conversao-valor.php',
    'test.php',
    '*.log',
    '*.md'
];

echo "📁 Conectando ao servidor FTP...\n";
echo "Host: $ftpHost\n";
echo "User: $ftpUser\n\n";

// Conectar ao FTP
$conn = ftp_connect($ftpHost);
if (!$conn) {
    die("❌ Erro ao conectar ao servidor FTP\n");
}

// Login
$login = ftp_login($conn, $ftpUser, $ftpPass);
if (!$login) {
    ftp_close($conn);
    die("❌ Erro ao fazer login no FTP\n");
}

echo "✅ Conectado com sucesso!\n\n";

// Modo passivo
ftp_pasv($conn, true);

// Mudar para diretório remoto
if (!empty($ftpDir)) {
    ftp_chdir($conn, $ftpDir);
}

/**
 * Verifica se o arquivo/pasta deve ser excluído
 */
function shouldExclude($path, $exclude) {
    $basename = basename($path);
    
    foreach ($exclude as $pattern) {
        // Padrão exato
        if ($basename === $pattern) {
            return true;
        }
        
        // Padrão com wildcard
        if (strpos($pattern, '*') !== false) {
            $regex = '/^' . str_replace('*', '.*', preg_quote($pattern, '/')) . '$/';
            if (preg_match($regex, $basename)) {
                return true;
            }
        }
    }
    
    return false;
}

/**
 * Faz upload recursivo de diretório
 */
function uploadDirectory($conn, $localDir, $remoteDir, $exclude) {
    $uploaded = 0;
    $skipped = 0;
    
    // Criar diretório remoto se não existir
    $currentDir = ftp_pwd($conn);
    if (!@ftp_chdir($conn, $remoteDir)) {
        ftp_mkdir($conn, $remoteDir);
        ftp_chdir($conn, $remoteDir);
    }
    
    // Listar arquivos locais
    $files = scandir($localDir);
    
    foreach ($files as $file) {
        if ($file === '.' || $file === '..') {
            continue;
        }
        
        $localPath = $localDir . '/' . $file;
        
        // Verificar se deve excluir
        if (shouldExclude($localPath, $exclude)) {
            echo "⏭️  Ignorando: $file\n";
            $skipped++;
            continue;
        }
        
        if (is_dir($localPath)) {
            // Diretório - recursivo
            echo "📁 Entrando em: $file/\n";
            list($subUploaded, $subSkipped) = uploadDirectory($conn, $localPath, $file, $exclude);
            $uploaded += $subUploaded;
            $skipped += $subSkipped;
            ftp_cdup($conn);
        } else {
            // Arquivo - fazer upload
            echo "📤 Enviando: $file... ";
            
            if (ftp_put($conn, $file, $localPath, FTP_BINARY)) {
                echo "✅\n";
                $uploaded++;
            } else {
                echo "❌\n";
            }
        }
    }
    
    return [$uploaded, $skipped];
}

// Fazer upload
echo "📤 Iniciando upload de arquivos...\n\n";

list($uploaded, $skipped) = uploadDirectory($conn, $localDir, '.', $exclude);

// Fechar conexão
ftp_close($conn);

echo "\n================================\n";
echo "✅ Deploy concluído!\n";
echo "📤 Arquivos enviados: $uploaded\n";
echo "⏭️  Arquivos ignorados: $skipped\n";
echo "================================\n";
