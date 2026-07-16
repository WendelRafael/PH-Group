<?php

declare(strict_types=1);

/**
 * Runner de testes — PHP puro, sem PHPUnit e sem dependências (ver AGENTS.md).
 *
 * Contrato: cada `*Test.php` desta pasta define funções `test_*` que usam
 * os helpers de test/helpers.php (assert_true, assert_equals, assert_throws,
 * pular). O runner inclui todos os arquivos e executa cada função `test_*`
 * na ordem em que aparece.
 *
 * Executar:  php test/run.php
 * Saída:     ✓ passou · – pulado · ✗ falhou (exit code 1 se houver falha)
 */

require __DIR__ . '/helpers.php';

$arquivos = glob(__DIR__ . '/*Test.php') ?: [];
sort($arquivos);
foreach ($arquivos as $arquivo) {
    require $arquivo;
}

// Coleta as funções test_* e ordena por arquivo e linha de declaração
$testes = [];
foreach (get_defined_functions()['user'] as $funcao) {
    if (str_starts_with($funcao, 'test_')) {
        $ref = new ReflectionFunction($funcao);
        $testes[] = [$ref->getFileName(), $ref->getStartLine(), $funcao];
    }
}
usort($testes, static fn (array $a, array $b): int => [$a[0], $a[1]] <=> [$b[0], $b[1]]);

$passaram = $pularam = $falharam = 0;
$arquivoAtual = '';

foreach ($testes as [$arquivo, $linha, $funcao]) {
    if ($arquivo !== $arquivoAtual) {
        $arquivoAtual = $arquivo;
        echo PHP_EOL . '── ' . basename($arquivo) . PHP_EOL;
    }

    $nome = str_replace('_', ' ', substr($funcao, strlen('test_')));

    try {
        $funcao();
        echo "  ✓ {$nome}" . PHP_EOL;
        $passaram++;
    } catch (TestePulado $e) {
        echo "  – {$nome} [PULADO: {$e->getMessage()}]" . PHP_EOL;
        $pularam++;
    } catch (Throwable $e) {
        echo "  ✗ {$nome}" . PHP_EOL . "      {$e->getMessage()}" . PHP_EOL;
        $falharam++;
    }
}

echo PHP_EOL . sprintf(
    'Resultado: %d passou(aram), %d pulado(s), %d falhou(aram).',
    $passaram,
    $pularam,
    $falharam
) . PHP_EOL;

exit($falharam > 0 ? 1 : 0);
