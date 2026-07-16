<?php

declare(strict_types=1);

/**
 * GET /api/games.php — lista os jogos SALVOS NO BANCO, em JSON.
 *
 * Resposta 200: { "total": 413, "jogos": [ {...}, ... ] }
 * Erros:  405 método errado · 500 banco indisponível
 */

require_once __DIR__ . '/../../src/Database.php';
require_once __DIR__ . '/../../src/GameRepository.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    header('Allow: GET');
    echo json_encode(['erro' => 'Use GET para listar os jogos.']);
    exit;
}

try {
    $config      = require __DIR__ . '/../../config/config.php';
    $repositorio = new GameRepository(Database::conectar($config['db']));
    $jogos       = $repositorio->listarTodos();

    echo json_encode(
        ['total' => count($jogos), 'jogos' => $jogos],
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
    );
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'erro' => 'Não foi possível ler o banco de dados. Valide se ele está rodando. '
                . 'Veja o passo a passo no README.',
    ], JSON_UNESCAPED_UNICODE);
}
