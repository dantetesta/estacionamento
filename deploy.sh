#!/bin/bash
# EstacionaFácil - Script de Deploy FTP
# 
# @author Dante Testa (https://dantetesta.com.br)
# @version 1.0.0
# @date 14/10/2025 21:07

echo "🚀 EstacionaFácil - Deploy FTP"
echo "================================"
echo ""

# Configurações FTP
FTP_HOST="187.33.241.61"
FTP_USER="site1@danteflix.com.br"
FTP_PASS="{Xt8ht}J#cTYjm{L"
FTP_DIR="/"

# Diretório local
LOCAL_DIR="/Users/dantetesta/Desktop/WINDSURF/projeto2"

echo "📁 Conectando ao servidor FTP..."
echo "Host: $FTP_HOST"
echo "User: $FTP_USER"
echo ""

# Usar lftp para sincronização
if command -v lftp &> /dev/null; then
    echo "✅ lftp encontrado, iniciando sincronização..."
    
    lftp -u "$FTP_USER,$FTP_PASS" "$FTP_HOST" <<EOF
set ftp:ssl-allow no
set ssl:verify-certificate no
cd $FTP_DIR
lcd $LOCAL_DIR
mirror --reverse --delete --verbose --exclude .git/ --exclude .gitignore --exclude node_modules/ --exclude sync_config.jsonc --exclude deploy.sh --exclude *.log --exclude teste-*.php
bye
EOF
    
    echo ""
    echo "✅ Deploy concluído com sucesso!"
    
else
    echo "❌ lftp não está instalado!"
    echo ""
    echo "Para instalar:"
    echo "  macOS: brew install lftp"
    echo "  Linux: sudo apt-get install lftp"
    echo ""
fi

echo ""
echo "================================"
echo "Deploy finalizado!"
