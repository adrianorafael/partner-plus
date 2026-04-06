<?php
/**
 * Database - Singleton PDO wrapper
 * Gerencia a conexão com o banco de dados usando PDO.
 */
class Database
{
    private static ?PDO $instance = null;

    private function __construct() {}
    private function __clone() {}

    /**
     * Retorna a instância única do PDO.
     */
    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            if (!defined('DB_HOST')) {
                throw new RuntimeException('Configuração do banco de dados não encontrada. Execute o wizard de instalação em /install.');
            }

            $dsn = sprintf(
                'mysql:host=%s;dbname=%s;charset=%s',
                DB_HOST,
                DB_NAME,
                DB_CHARSET
            );

            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];

            try {
                self::$instance = new PDO($dsn, DB_USER, DB_PASS, $options);
            } catch (PDOException $e) {
                // Não expor detalhes de erro em produção
                if (defined('APP_ENV') && APP_ENV === 'development') {
                    throw new RuntimeException('Falha na conexão com o banco: ' . $e->getMessage());
                }
                throw new RuntimeException('Erro interno de servidor. Tente novamente mais tarde.');
            }
        }

        return self::$instance;
    }

    /**
     * Atalho para preparar e executar uma query com parâmetros.
     * Retorna o PDOStatement resultante.
     */
    public static function query(string $sql, array $params = []): PDOStatement
    {
        $stmt = self::getInstance()->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    /**
     * Retorna o último ID inserido.
     */
    public static function lastInsertId(): string
    {
        return self::getInstance()->lastInsertId();
    }

    /**
     * Testa a conexão. Usado pelo wizard de instalação.
     */
    public static function testConnection(string $host, string $dbname, string $user, string $pass): bool
    {
        $dsn = "mysql:host={$host};dbname={$dbname};charset=utf8mb4";
        try {
            new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }
}
