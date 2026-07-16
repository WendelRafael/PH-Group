<?php

declare(strict_types=1);

/**
 * Espelha public/api/sync.php.
 *
 * O caminho feliz (importar da FreeToGame e gravar) NÃO é testado aqui:
 * exigiria internet, e a regra do AGENTS.md é testes 100% offline. As duas
 * metades desse fluxo já têm cobertura própria — a normalização do JSON em
 * FreeToGameClientTest (fixtures) e a gravação em GameRepositoryTest
 * (banco descartável). Aqui fica o contrato HTTP do endpoint.
 */

function test_api_sync_recusa_metodo_get_com_405(): void
{
    $resposta = executar_endpoint('public/api/sync.php', 'GET');

    assert_equals(405, $resposta['status'], 'GET em sync.php deveria devolver 405.');
    assert_true(isset($resposta['json']['erro']), 'Resposta de erro deveria ter a chave "erro".');
}

function test_api_sync_importacao_completa_e_validada_no_navegador(): void
{
    pular('depende de internet — cobertura offline em FreeToGameClientTest + GameRepositoryTest');
}
