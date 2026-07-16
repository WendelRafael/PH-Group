<?php

declare(strict_types=1);

/**
 * Conexão PDO com o MySQL — única para toda a requisição.
 */
final class Database
{
    private static ?PDO $conexao = null;

    /**
     * @param array{host: string, port: int, name: string, user: string, pass: string} $db
     */
    public static function conectar(array $db): PDO
    {
        if (self::$conexao === null) {
            $dsn = sprintf(
                'mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4',
                $db['host'],
                $db['port'],
                $db['name']
            );

            self::$conexao = new PDO($dsn, $db['user'], $db['pass'], [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        }

        return self::$conexao;
    }
}
