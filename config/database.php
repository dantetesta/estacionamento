<?php
/**
 * EstacionaFácil - Configuração do Banco de Dados
 * 
 * Este arquivo contém as configurações de conexão com o banco de dados MySQL.
 * As credenciais são definidas durante a instalação do sistema.
 * 
 * @author Dante Testa (https://dantetesta.com.br)
 * @version 1.0.0
 * @date 14/10/2025 19:54
 */

// Prevenir acesso direto ao arquivo
if (!defined('ESTACIONAFACIL')) {
    die('Acesso negado');
}

// Configurações do banco de dados
// Estas constantes são definidas durante a instalação
define('DB_HOST', 'localhost');
define('DB_NAME', 'danteflix_estacionamentov2');
define('DB_USER', 'danteflix_estacionamentov2');
define('DB_PASS', 'danteflix_estacionamentov2');
define('DB_CHARSET', 'utf8mb4');

/**
 * Classe Database
 * 
 * Gerencia a conexão com o banco de dados usando PDO
 * Implementa padrão Singleton para garantir uma única instância
 */
class Database {
    private static $instance = null;
    private $connection;
    
    /**
     * Construtor privado para implementar Singleton
     * Estabelece a conexão com o banco de dados
     */
    private function __construct() {
        try {
            // String de conexão PDO
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            
            // Opções de configuração do PDO
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, // Lançar exceções em erros
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, // Retornar arrays associativos
                PDO::ATTR_EMULATE_PREPARES => false, // Usar prepared statements nativos
                PDO::ATTR_STRINGIFY_FETCHES => false, // Não converter números em strings
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . DB_CHARSET, // Definir charset
            ];
            
            // Criar conexão PDO
            $this->connection = new PDO($dsn, DB_USER, DB_PASS, $options);
            
        } catch (PDOException $e) {
            // Log do erro (em produção, usar arquivo de log)
            error_log("Erro de conexão com banco de dados: " . $e->getMessage());
            
            // Mensagem genérica para o usuário
            die("Erro ao conectar com o banco de dados. Por favor, tente novamente mais tarde.");
        }
    }
    
    /**
     * Prevenir clonagem da instância
     */
    private function __clone() {}
    
    /**
     * Prevenir deserialização da instância
     */
    public function __wakeup() {
        throw new Exception("Não é possível deserializar singleton");
    }
    
    /**
     * Retorna a instância única da classe (Singleton)
     * 
     * @return Database Instância da classe
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Retorna a conexão PDO
     * 
     * @return PDO Objeto de conexão
     */
    public function getConnection() {
        return $this->connection;
    }
    
    /**
     * Executa uma query SELECT e retorna todos os resultados
     * 
     * @param string $sql Query SQL
     * @param array $params Parâmetros para prepared statement
     * @return array Resultados da query
     */
    public function select($sql, $params = []) {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Erro na query SELECT: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Executa uma query SELECT e retorna apenas um resultado
     * 
     * @param string $sql Query SQL
     * @param array $params Parâmetros para prepared statement
     * @return array|false Resultado da query ou false
     */
    public function selectOne($sql, $params = []) {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Erro na query SELECT: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Executa uma query INSERT, UPDATE ou DELETE
     * 
     * @param string $sql Query SQL
     * @param array $params Parâmetros para prepared statement
     * @return bool Sucesso da operação
     */
    public function execute($sql, $params = []) {
        try {
            $stmt = $this->connection->prepare($sql);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            error_log("Erro na query: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Retorna o ID do último registro inserido
     * 
     * @return string ID do último insert
     */
    public function lastInsertId() {
        return $this->connection->lastInsertId();
    }
    
    /**
     * Inicia uma transação
     * 
     * @return bool Sucesso da operação
     */
    public function beginTransaction() {
        return $this->connection->beginTransaction();
    }
    
    /**
     * Confirma uma transação
     * 
     * @return bool Sucesso da operação
     */
    public function commit() {
        return $this->connection->commit();
    }
    
    /**
     * Reverte uma transação
     * 
     * @return bool Sucesso da operação
     */
    public function rollback() {
        return $this->connection->rollback();
    }
}

/**
 * Função auxiliar para obter a instância do banco de dados
 * 
 * @return Database Instância do banco de dados
 */
function getDB() {
    return Database::getInstance();
}
