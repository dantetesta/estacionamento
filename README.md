# 🚗 EstacionaFácil - Sistema de Gestão de Estacionamento

<div align="center">

[![Download](https://img.shields.io/badge/📥_DOWNLOAD-Sistema_Completo-blue?style=for-the-badge&logo=github&logoColor=white)](https://github.com/dantetesta/estacionamento/archive/refs/heads/main.zip)

**Versão:** 1.0.0  
**Autor:** [Dante Testa](https://dantetesta.com.br)  
**Data:** 14/10/2025 19:54

</div>

---

## 📋 Descrição

Sistema web completo para gestão de estacionamento com controle de entrada/saída de veículos, gestão de mensalistas, controle financeiro e relatórios detalhados.

## 🚀 Funcionalidades

### ✅ Controle de Veículos
- Registro de entrada e saída com data/hora automática
- Suporte para diferentes tipos: Pequeno, Médio, Grande, Caminhão, Ônibus
- Cálculo automático de valores para diaristas
- Identificação de mensalistas

### 💰 Controle Financeiro
- Registro de despesas fixas (energia, internet, salários)
- Controle de receitas por tipo de veículo
- Relatórios de lucro diário, semanal e mensal
- Múltiplas formas de pagamento (Dinheiro, PIX, Cartão)

### 👥 Gestão de Mensalistas
- Cadastro completo (nome, placa, telefone, valor)
- Histórico de entradas e saídas
- Relatórios mensais individualizados

### 📊 Relatórios e Estatísticas
- Quantidade de veículos por dia/período
- Faturamento por tipo de veículo
- Análise de lucro líquido
- Gráficos e visualizações

## 🛠️ Tecnologias

- **Backend:** PHP 8.2+
- **Banco de Dados:** MySQL 5.7+
- **Frontend:** HTML5, CSS3, JavaScript
- **Framework CSS:** Tailwind CSS 3.x
- **Ícones:** Font Awesome 6.x
- **Hospedagem:** cPanel compatível

## 📦 Instalação

### Requisitos
- PHP 8.2 ou superior
- MySQL 5.7 ou superior
- Servidor Apache com mod_rewrite
- Hospedagem com cPanel

### Passo a Passo

1. **Upload dos arquivos**
   - Faça upload de todos os arquivos para o diretório público (public_html)

2. **Configuração do banco de dados**
   - Acesse: `http://seudominio.com.br/instalar.php`
   - Preencha os dados solicitados:
     - Host do banco (geralmente: localhost)
     - Nome do banco de dados
     - Usuário do banco
     - Senha do banco
     - Nome do estacionamento
     - Usuário admin
     - Senha admin
     - Valores das diárias por tipo de veículo

3. **Primeiro acesso**
   - Após a instalação, você será redirecionado para o login
   - Use as credenciais criadas no instalador
   - **IMPORTANTE:** Delete o arquivo `instalar.php` após a instalação

## 🔐 Segurança

- ✅ Autenticação com sessões seguras
- ✅ Proteção contra SQL Injection
- ✅ Proteção contra XSS
- ✅ Senhas criptografadas com bcrypt
- ✅ Timeout de sessão por inatividade
- ✅ Proteção de rotas sem autenticação
- ✅ Não indexado por motores de busca
- ✅ Headers de segurança configurados

## 📱 Responsividade

Sistema desenvolvido com abordagem **Mobile First**:
- ✅ Totalmente responsivo
- ✅ Otimizado para smartphones
- ✅ Adaptável para tablets
- ✅ Interface desktop completa

## ⚡ Performance

- ✅ CSS e JS minificados
- ✅ Carregamento assíncrono de recursos
- ✅ Cache de consultas otimizado
- ✅ Queries SQL otimizadas com índices
- ✅ Lazy loading de imagens

## ♿ Acessibilidade

- ✅ Semântica HTML5
- ✅ ARIA labels
- ✅ Contraste adequado (WCAG 2.1)
- ✅ Navegação por teclado
- ✅ Textos alternativos

## 🎨 Paleta de Cores

- **Primária:** #3B82F6 (Azul)
- **Secundária:** #6B7280 (Cinza)
- **Sucesso:** #10B981 (Verde)
- **Alerta:** #F59E0B (Amarelo)
- **Erro:** #EF4444 (Vermelho)
- **Fundo:** #F9FAFB (Cinza Claro)

## 📁 Estrutura de Arquivos

```
estacionafacil/
├── index.php                 # Redirecionamento para login
├── instalar.php             # Instalador do sistema
├── login.php                # Página de login
├── logout.php               # Logout e destruição de sessão
├── .htaccess                # Configurações Apache
├── robots.txt               # Bloqueio de indexação
├── README.md                # Este arquivo
│
├── config/
│   ├── database.php         # Configuração do banco
│   └── config.php           # Configurações gerais
│
├── includes/
│   ├── auth.php             # Verificação de autenticação
│   ├── functions.php        # Funções auxiliares
│   └── header.php           # Header padrão
│   └── footer.php           # Footer padrão
│
├── painel/
│   ├── index.php            # Dashboard principal
│   ├── veiculos/
│   │   ├── entrada.php      # Registrar entrada
│   │   ├── saida.php        # Registrar saída
│   │   └── listar.php       # Listar veículos
│   ├── mensalistas/
│   │   ├── cadastrar.php    # Cadastrar mensalista
│   │   ├── listar.php       # Listar mensalistas
│   │   └── historico.php    # Histórico individual
│   ├── financeiro/
│   │   ├── despesas.php     # Registrar despesas
│   │   └── receitas.php     # Visualizar receitas
│   └── relatorios/
│       ├── diario.php       # Relatório diário
│       ├── semanal.php      # Relatório semanal
│       └── mensal.php       # Relatório mensal
│
├── assets/
│   ├── css/
│   │   └── custom.css       # Estilos personalizados
│   ├── js/
│   │   └── main.js          # Scripts principais
│   └── img/
│       └── logo.png         # Logo do sistema
│
└── api/
    ├── veiculos.php         # API para veículos
    ├── mensalistas.php      # API para mensalistas
    └── relatorios.php       # API para relatórios
```

## 🗄️ Estrutura do Banco de Dados

### Tabela: usuarios
- id, usuario, senha, nome, email, criado_em

### Tabela: configuracoes
- id, nome_estacionamento, valor_pequeno, valor_medio, valor_grande, valor_caminhao, valor_onibus

### Tabela: veiculos
- id, placa, tipo, mensalista_id, data_entrada, hora_entrada, data_saida, hora_saida, valor, forma_pagamento, pago

### Tabela: mensalistas
- id, nome, placa, telefone, email, valor_mensal, dia_vencimento, ativo, criado_em

### Tabela: despesas
- id, categoria, descricao, valor, data, criado_em

### Tabela: pagamentos_mensalistas
- id, mensalista_id, mes_referencia, valor, data_pagamento, pago

## 🔄 Controle de Versão

**Versão 1.0.0** - 14/10/2025
- ✅ Sistema base completo
- ✅ Controle de veículos
- ✅ Gestão de mensalistas
- ✅ Controle financeiro
- ✅ Relatórios e estatísticas
- ✅ Interface responsiva
- ✅ Sistema de segurança

## 📞 Suporte

Para suporte ou dúvidas, entre em contato:
- **Website:** [dantetesta.com.br](https://dantetesta.com.br)
- **Email:** contato@dantetesta.com.br

## 📄 Licença

Este sistema foi desenvolvido por Dante Testa para uso comercial.
Todos os direitos reservados © 2025

---

**Desenvolvido com ❤️ por [Dante Testa](https://dantetesta.com.br)**
