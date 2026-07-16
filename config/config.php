<?php

declare(strict_types=1);

/**
 * Configuração central da aplicação.
 *
 * Precedência (da mais forte para a mais fraca):
 *   variável de ambiente real  >  arquivo `.env` na raiz  >  padrão Laragon/XAMPP.
 * Sem `.env` e sem variáveis, os padrões abaixo já funcionam no Laragon.
 * (A precedência da variável real é o que permite aos testes apontarem os
 *  endpoints para o banco descartável, sem tocar o banco de verdade.)
 *
 * Uso: $config = require __DIR__ . '/../config/config.php';
 */

$env = [];
$envFile = dirname(__DIR__) . '/.env';

if (is_readable($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $linha) {
        $linha = trim($linha);
        if ($linha === '' || str_starts_with($linha, '#') || !str_contains($linha, '=')) {
            continue;
        }
        [$chave, $valor] = explode('=', $linha, 2);
        $env[trim($chave)] = trim($valor);
    }
}

/** @var callable(string, string): string $ler */
$ler = static function (string $chave, string $padrao) use ($env): string {
    $real = getenv($chave);
    return $real !== false ? $real : ($env[$chave] ?? $padrao);
};

return [
    'db' => [
        'host' => $ler('DB_HOST', '127.0.0.1'),
        'port' => (int) $ler('DB_PORT', '3306'),
        'name' => $ler('DB_NAME', 'ph_catalogo'),
        'user' => $ler('DB_USER', 'root'),
        'pass' => $ler('DB_PASS', ''),
    ],
    'freetogame' => [
        'base_url' => 'https://www.freetogame.com/api',
        'timeout'  => 20, // segundos
    ],
];
