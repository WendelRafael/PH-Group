<?php

declare(strict_types=1);

/**
 * Espelha src/Database.php — integração com o MySQL local.
 * Os testes são PULADOS (não falham) se o MySQL não estiver rodando.
 */

require_once dirname(__DIR__) . '/src/Database.php';

/** Conexão com o MySQL local ou pula o teste se indisponível. */
function conexao_ou_pula(): PDO
{
    $config = require dirname(__DIR__) . '/config/config.php';

    try {
        return Database::conectar($config['db']);
    } catch (PDOException $e) {
        pular('MySQL local indisponível — inicie o banco para rodar este teste');
    }
}

function test_database_conecta_no_mysql_configurado(): void
{
    $pdo = conexao_ou_pula();
    assert_equals(1, (int) $pdo->query('SELECT 1')->fetchColumn(), 'SELECT 1 falhou.');
}

function test_database_conexao_usa_utf8mb4(): void
{
    $pdo = conexao_ou_pula();
    $charset = $pdo->query('SELECT @@character_set_connection')->fetchColumn();
    assert_equals('utf8mb4', $charset, 'Charset da conexão diferente de utf8mb4.');
}

function test_database_devolve_sempre_a_mesma_conexao(): void
{
    $config = require dirname(__DIR__) . '/config/config.php';
    try {
        $a = Database::conectar($config['db']);
        $b = Database::conectar($config['db']);
    } catch (PDOException $e) {
        pular('MySQL local indisponível — inicie o banco para rodar este teste');
    }
    assert_true($a === $b, 'Database::conectar criou duas conexões diferentes (deveria ser singleton).');
}
