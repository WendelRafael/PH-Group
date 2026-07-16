<?php

declare(strict_types=1);

/**
 * Espelha src/GameRepository.php — integração com o banco descartável
 * `ph_catalogo_test` (criado por banco_de_teste(); o real nunca é tocado).
 * Os testes são PULADOS se o MySQL não estiver rodando.
 */

require_once dirname(__DIR__) . '/src/GameRepository.php';

/** Fixture: jogo mínimo já normalizado, pronto para o repositório. */
function jogo_fake(int $id, string $titulo): array
{
    return [
        'id'                     => $id,
        'title'                  => $titulo,
        'thumbnail'              => null,
        'short_description'      => 'Jogo de teste.',
        'game_url'               => null,
        'genre'                  => 'Teste',
        'platform'               => 'PC (Windows)',
        'publisher'              => null,
        'developer'              => null,
        'release_date'           => '2026-01-01',
        'freetogame_profile_url' => null,
    ];
}

function test_repositorio_grava_apenas_os_novos_e_nao_duplica(): void
{
    $pdo  = banco_de_teste();
    $repo = new GameRepository($pdo);

    try {
        $novos = $repo->gravarMuitos([jogo_fake(1, 'Alfa'), jogo_fake(2, 'Beta')]);
        assert_equals(2, $novos, 'Primeira gravação deveria inserir 2 jogos.');

        $novos = $repo->gravarMuitos([jogo_fake(1, 'Alfa'), jogo_fake(2, 'Beta'), jogo_fake(3, 'Gama')]);
        assert_equals(1, $novos, 'Segunda gravação deveria inserir só o jogo novo.');
        assert_equals(3, $repo->contar(), 'Total após as duas gravações deveria ser 3.');
    } finally {
        destruir_banco_de_teste($pdo);
    }
}

function test_repositorio_upsert_atualiza_jogo_existente(): void
{
    $pdo  = banco_de_teste();
    $repo = new GameRepository($pdo);

    try {
        $repo->gravarMuitos([jogo_fake(1, 'Nome antigo')]);
        $repo->gravarMuitos([jogo_fake(1, 'Nome novo')]);

        $jogos = $repo->listarTodos();
        assert_equals(1, count($jogos), 'Upsert do mesmo id não pode duplicar.');
        assert_equals('Nome novo', $jogos[0]['title'], 'Upsert deveria atualizar o título.');
    } finally {
        destruir_banco_de_teste($pdo);
    }
}

function test_repositorio_lista_em_ordem_alfabetica(): void
{
    $pdo  = banco_de_teste();
    $repo = new GameRepository($pdo);

    try {
        $repo->gravarMuitos([jogo_fake(1, 'Zelda-like'), jogo_fake(2, 'Aventura'), jogo_fake(3, 'Corrida')]);

        $titulos = array_column($repo->listarTodos(), 'title');
        assert_equals(['Aventura', 'Corrida', 'Zelda-like'], $titulos, 'Ordem alfabética incorreta.');
    } finally {
        destruir_banco_de_teste($pdo);
    }
}

function test_repositorio_lista_vazia_nao_faz_nada(): void
{
    $pdo  = banco_de_teste();
    $repo = new GameRepository($pdo);

    try {
        assert_equals(0, $repo->gravarMuitos([]), 'Lista vazia deveria inserir 0.');
        assert_equals(0, $repo->contar(), 'Banco deveria continuar vazio.');
    } finally {
        destruir_banco_de_teste($pdo);
    }
}
