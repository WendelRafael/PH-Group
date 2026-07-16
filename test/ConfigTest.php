<?php

declare(strict_types=1);

/**
 * Espelha config/config.php — estrutura e valores padrão.
 */

function test_config_retorna_as_chaves_db_e_freetogame(): void
{
    $config = require dirname(__DIR__) . '/config/config.php';

    assert_true(isset($config['db']), 'Falta a chave "db" na configuração.');
    assert_true(isset($config['freetogame']), 'Falta a chave "freetogame" na configuração.');
}

function test_config_bloco_db_tem_todos_os_campos_de_conexao(): void
{
    $config = require dirname(__DIR__) . '/config/config.php';

    foreach (['host', 'port', 'name', 'user', 'pass'] as $campo) {
        assert_true(array_key_exists($campo, $config['db']), "Falta db.{$campo} na configuração.");
    }
    assert_true(is_int($config['db']['port']), 'db.port deve ser inteiro.');
}

function test_config_url_da_freetogame_aponta_para_a_api_oficial(): void
{
    $config = require dirname(__DIR__) . '/config/config.php';

    assert_equals(
        'https://www.freetogame.com/api',
        $config['freetogame']['base_url'],
        'URL base da FreeToGame diferente da esperada.'
    );
    assert_true($config['freetogame']['timeout'] > 0, 'Timeout deve ser positivo.');
}
