<?php

declare(strict_types=1);

/**
 * Espelha public/api/games.php — o endpoint roda num subprocesso PHP
 * isolado, apontado para o banco descartável `ph_catalogo_test` via
 * variável de ambiente (o banco real nunca é tocado).
 */

require_once dirname(__DIR__) . '/src/GameRepository.php';

function test_api_games_recusa_metodo_post_com_405(): void
{
    $resposta = executar_endpoint('public/api/games.php', 'POST');

    assert_equals(405, $resposta['status'], 'POST em games.php deveria devolver 405.');
    assert_true(isset($resposta['json']['erro']), 'Resposta de erro deveria ter a chave "erro".');
}

function test_api_games_lista_os_jogos_salvos_no_banco(): void
{
    $pdo = banco_de_teste();

    try {
        (new GameRepository($pdo))->gravarMuitos([jogo_fake(7, 'Jogo do Endpoint')]);

        $resposta = executar_endpoint('public/api/games.php', 'GET', ['DB_NAME' => 'ph_catalogo_test']);

        assert_equals(200, $resposta['status'], 'GET em games.php deveria devolver 200.');
        assert_equals(1, $resposta['json']['total'], 'total deveria refletir o banco.');
        assert_equals('Jogo do Endpoint', $resposta['json']['jogos'][0]['title'], 'Jogo salvo não apareceu na listagem.');
    } finally {
        destruir_banco_de_teste($pdo);
    }
}

function test_api_games_banco_indisponivel_devolve_500_com_mensagem(): void
{
    // Aponta o endpoint para uma porta sem MySQL: erro tratado, nunca stack trace.
    $resposta = executar_endpoint('public/api/games.php', 'GET', [
        'DB_HOST' => '127.0.0.1',
        'DB_PORT' => '3399',
    ]);

    assert_equals(500, $resposta['status'], 'Banco fora do ar deveria devolver 500.');
    assert_true(isset($resposta['json']['erro']), 'Erro de banco deveria vir como JSON com a chave "erro".');
}
