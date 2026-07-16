<?php

declare(strict_types=1);

/**
 * POST /api/sync.php — importa o catálogo da FreeToGame para o banco.
 *
 * Consome GET /games da API, INSERE os jogos novos e atualiza os já
 * existentes (upsert pelo id — nunca duplica).
 *
 * Resposta 200: { "inseridos": 12, "total": 413 }
 * Erros:  405 método errado · 502 FreeToGame fora · 500 banco indisponível
 */

require_once __DIR__ . '/../../src/Database.php';
require_once __DIR__ . '/../../src/FreeToGameClient.php';
require_once __DIR__ . '/../../src/GameRepository.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    echo json_encode(['erro' => 'Use POST para sincronizar o catálogo.']);
    exit;
}

$config = require __DIR__ . '/../../config/config.php';

try {
    $cliente = new FreeToGameClient(
        $config['freetogame']['base_url'],
        $config['freetogame']['timeout']
    );
    $jogos = $cliente->buscarJogos();
} catch (RuntimeException $e) {
    http_response_code(502);
    echo json_encode(
        ['erro' => 'A API FreeToGame não respondeu: ' . $e->getMessage()],
        JSON_UNESCAPED_UNICODE
    );
    exit;
}

try {
    $repositorio = new GameRepository(Database::conectar($config['db']));
    $inseridos   = $repositorio->gravarMuitos($jogos);

    echo json_encode(
        ['inseridos' => $inseridos, 'total' => $repositorio->contar()],
        JSON_UNESCAPED_UNICODE
    );
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'erro' => 'Falha ao gravar no banco de dados. Valide se ele está rodando. '
                . 'Veja o passo a passo no README.',
    ], JSON_UNESCAPED_UNICODE);
}
