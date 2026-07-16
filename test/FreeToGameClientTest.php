<?php

declare(strict_types=1);

/**
 * Espelha src/FreeToGameClient.php — normalização do JSON da API.
 * Roda 100% offline: usa fixtures JSON, nunca chama a API real (AGENTS.md).
 */

require_once dirname(__DIR__) . '/src/FreeToGameClient.php';

/** Fixture: um jogo como a FreeToGame realmente devolve. */
function fixture_jogo(): array
{
    return [
        'id'                     => 540,
        'title'                  => 'Overwatch',
        'thumbnail'              => 'https://www.freetogame.com/g/540/thumbnail.jpg',
        'short_description'      => 'A hero-focused first-person team shooter.',
        'game_url'               => 'https://www.freetogame.com/open/overwatch',
        'genre'                  => 'Shooter',
        'platform'               => 'PC (Windows)',
        'publisher'              => 'Activision Blizzard',
        'developer'              => 'Blizzard Entertainment',
        'release_date'           => '2022-10-04',
        'freetogame_profile_url' => 'https://www.freetogame.com/overwatch',
    ];
}

function test_cliente_normaliza_um_jogo_completo(): void
{
    $jogos = FreeToGameClient::normalizarJson(json_encode([fixture_jogo()]));

    assert_equals(1, count($jogos), 'Deveria normalizar exatamente 1 jogo.');
    assert_equals(540, $jogos[0]['id'], 'id não preservado.');
    assert_equals('Overwatch', $jogos[0]['title'], 'title não preservado.');
    assert_equals('2022-10-04', $jogos[0]['release_date'], 'release_date válida foi alterada.');
}

function test_cliente_descarta_itens_sem_id_ou_sem_title(): void
{
    $semId = fixture_jogo();
    unset($semId['id']);
    $semTitulo = fixture_jogo();
    unset($semTitulo['title']);
    $tituloSoEspacos = fixture_jogo();
    $tituloSoEspacos['title'] = '   ';

    $jogos = FreeToGameClient::normalizarJson(
        json_encode([$semId, $semTitulo, $tituloSoEspacos, fixture_jogo()])
    );

    assert_equals(1, count($jogos), 'Itens incompletos deveriam ser descartados.');
}

function test_cliente_release_date_invalida_vira_null(): void
{
    foreach (['0000-00-00', 'em breve', ''] as $dataRuim) {
        $jogo = fixture_jogo();
        $jogo['release_date'] = $dataRuim;

        $jogos = FreeToGameClient::normalizarJson(json_encode([$jogo]));
        assert_equals(null, $jogos[0]['release_date'], "Data '{$dataRuim}' deveria virar null.");
    }
}

function test_cliente_campos_ausentes_viram_null(): void
{
    $jogos = FreeToGameClient::normalizarJson(json_encode([['id' => 1, 'title' => 'Só o básico']]));

    assert_equals(null, $jogos[0]['genre'], 'genre ausente deveria ser null.');
    assert_equals(null, $jogos[0]['thumbnail'], 'thumbnail ausente deveria ser null.');
}

function test_cliente_apara_espacos_e_string_vazia_vira_null(): void
{
    // Caso real: o jogo "Aberoth" chega da API com genre " MMORPG" (espaço à esquerda),
    // o que duplicava a opção no filtro de gênero da página.
    $jogo = fixture_jogo();
    $jogo['title'] = '  Aberoth ';
    $jogo['genre'] = ' MMORPG';
    $jogo['publisher'] = '   ';

    $jogos = FreeToGameClient::normalizarJson(json_encode([$jogo]));

    assert_equals('Aberoth', $jogos[0]['title'], 'title deveria ser aparado nas bordas.');
    assert_equals('MMORPG', $jogos[0]['genre'], 'genre deveria ser aparado nas bordas.');
    assert_equals(null, $jogos[0]['publisher'], 'String só de espaços deveria virar null.');
}

function test_cliente_json_que_nao_e_lista_lanca_excecao(): void
{
    foreach (['{"erro":"algo"}', 'não é json', '"texto"'] as $jsonRuim) {
        assert_throws(
            RuntimeException::class,
            static fn () => FreeToGameClient::normalizarJson($jsonRuim),
            'JSON inválido deveria lançar RuntimeException'
        );
    }
}
