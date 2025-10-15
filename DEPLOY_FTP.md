# 🚀 Deploy FTP - EstacionaFácil

**Autor:** [Dante Testa](https://dantetesta.com.br)  
**Data:** 14/10/2025 21:07

## 📋 Sobre

Scripts para fazer upload automático dos arquivos para o servidor FTP após modificações.

## 🔧 Opções de Deploy

### Opção 1: Script PHP (Recomendado)

**Vantagens:**
- ✅ Funciona em qualquer sistema com PHP
- ✅ Não precisa instalar nada adicional
- ✅ Mostra progresso detalhado
- ✅ Exclui arquivos desnecessários automaticamente

**Como usar:**
```bash
php deploy.php
```

### Opção 2: Script Shell (Linux/Mac)

**Vantagens:**
- ✅ Mais rápido (usa lftp)
- ✅ Sincronização bidirecional
- ✅ Deleta arquivos remotos que não existem localmente

**Requisitos:**
```bash
# macOS
brew install lftp

# Linux
sudo apt-get install lftp
```

**Como usar:**
```bash
chmod +x deploy.sh
./deploy.sh
```

### Opção 3: Extensão do IDE (Automático)

**Configuração no `sync_config.jsonc`:**
```json
{
  "Site1": {
    "upload_on_save": true,
    "watch": true
  }
}
```

Isso fará upload automático sempre que você salvar um arquivo.

## 📁 Arquivos Excluídos do Upload

Os scripts **NÃO** enviam:
- ✅ `.git/` - Controle de versão
- ✅ `.gitignore` - Configuração git
- ✅ `node_modules/` - Dependências
- ✅ `sync_config.jsonc` - Configuração FTP (sensível)
- ✅ `deploy.sh` e `deploy.php` - Scripts de deploy
- ✅ `teste-*.php` - Arquivos de teste
- ✅ `*.log` - Logs
- ✅ `*.md` - Documentação

## 🎯 Workflow Recomendado

### Desenvolvimento Local

1. **Eu modifico os arquivos** (correções, novas funcionalidades)
2. **Você testa localmente:** http://localhost:9009
3. **Se estiver OK, você faz o deploy:**
   ```bash
   php deploy.php
   ```
4. **Testa no servidor:** http://seudominio.com.br

### Deploy Automático (Opcional)

Se quiser deploy automático após cada modificação:

1. **Ative o watch mode** no `sync_config.jsonc`:
   ```json
   "watch": true,
   "upload_on_save": true
   ```

2. **Ou crie um alias** no terminal:
   ```bash
   # Adicione no ~/.zshrc ou ~/.bashrc
   alias deploy="cd /Users/dantetesta/Desktop/WINDSURF/projeto2 && php deploy.php"
   ```

3. **Use o alias:**
   ```bash
   deploy
   ```

## 📊 Exemplo de Saída

```
🚀 EstacionaFácil - Deploy FTP
================================

📁 Conectando ao servidor FTP...
Host: 187.33.241.61
User: site1@danteflix.com.br

✅ Conectado com sucesso!

📤 Iniciando upload de arquivos...

📤 Enviando: index.php... ✅
📤 Enviando: login.php... ✅
📁 Entrando em: painel/
📤 Enviando: index.php... ✅
📁 Entrando em: veiculos/
📤 Enviando: listar.php... ✅
⏭️  Ignorando: teste-formatacao.php

================================
✅ Deploy concluído!
📤 Arquivos enviados: 45
⏭️  Arquivos ignorados: 8
================================
```

## ⚠️ Importante

### Segurança
- ✅ Credenciais FTP estão nos scripts (mantenha privado)
- ✅ Não commite os scripts de deploy no Git
- ✅ Arquivos sensíveis são excluídos automaticamente

### Backup
- ✅ Faça backup do servidor antes do primeiro deploy
- ✅ Teste localmente antes de fazer deploy
- ✅ Mantenha uma cópia local sempre atualizada

### Primeira Vez
No primeiro deploy, o script enviará **todos os arquivos**. Isso pode demorar alguns minutos.

## 🔄 Sincronização Bidirecional

Se você modificar arquivos diretamente no servidor e quiser baixá-los:

### Com lftp (Shell):
```bash
lftp -u "site1@danteflix.com.br,{Xt8ht}J#cTYjm{L" 187.33.241.61 <<EOF
mirror --verbose /
bye
EOF
```

### Com FileZilla (GUI):
1. Abra o FileZilla
2. Conecte com as credenciais
3. Arraste os arquivos do servidor para o local

## 📝 Logs

Os scripts mostram em tempo real:
- ✅ Arquivos sendo enviados
- ⏭️ Arquivos ignorados
- ❌ Erros (se houver)
- 📊 Resumo final

## 🆘 Solução de Problemas

### Erro de Conexão
```
❌ Erro ao conectar ao servidor FTP
```
**Solução:** Verifique se o host e porta estão corretos

### Erro de Login
```
❌ Erro ao fazer login no FTP
```
**Solução:** Verifique usuário e senha no script

### Permissões
```
❌ Erro ao enviar arquivo
```
**Solução:** Verifique permissões da pasta no servidor

### Script não executa
```bash
# Dar permissão de execução
chmod +x deploy.sh
chmod +x deploy.php
```

## 🎯 Comandos Úteis

### Deploy Completo
```bash
php deploy.php
```

### Deploy com Log
```bash
php deploy.php > deploy.log 2>&1
```

### Deploy Silencioso
```bash
php deploy.php > /dev/null 2>&1
```

### Verificar Conexão FTP
```bash
ftp 187.33.241.61
# Digite usuário e senha
```

## 📞 Suporte

Se tiver problemas com o deploy:
1. Verifique as credenciais FTP
2. Teste a conexão manualmente
3. Verifique os logs de erro
4. Entre em contato: contato@dantetesta.com.br

---

**Deploy automatizado para facilitar seu desenvolvimento!** 🚀
