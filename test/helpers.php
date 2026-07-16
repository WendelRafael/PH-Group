<?php

declare(strict_types=1);

/**
 * Helpers compartilhados pelos testes (convenções em AGENTS.md):
 *
 *  - asserções: assert_true(), assert_equals(), assert_throws()
 *  - pular(): marca o teste como PULADO (ex.: MySQL desligado) sem falhar
 *  - banco_de_teste(): cria o banco descartável `ph_catalogo_test` a partir
 *    de database/schema.sql — o banco real NUNCA é tocado pelos testes
 *  - executar_endpoint(): roda um endpoint de public/api/ num subprocesso
 *    PHP isolado, apontando para o banco de teste via variáveis de ambiente
 */

final class TestePulado extends Exception
{
}

function pular(string $motivo): never
{
    throw new TestePulado($motivo);
}

function assert_true(bool $condicao, string $mensagem): void
{
    if (!$condicao) {
        throw new AssertionError($mensagem);
    }
}

function assert_equals(mixed $esperado, mixed $obtido, string $mensagem): void
{
    if ($esperado !== $obtido) {
        throw new AssertionError(sprintf(
            '%s (esperado: %s | obtido: %s)',
            $mensagem,
            var_export($esperado, true),
            var_export($obtido, true)
        ));
    }
}

function assert_throws(string $classe, callable $codigo, string $mensagem): void
{
    try {
        $codigo();
    } catch (Throwable $e) {
        assert_true($e instanceof $classe, "{$mensagem} (lançou " . $e::class . ')');
        return;
    }
    throw new AssertionError("{$mensagem} (nenhuma exceção lançada)");
}

// ---------- Banco descartável ----------

/**
 * Cria (do zero) o banco `ph_catalogo_test` com a tabela `games` vazia,
 * usando a mesma estrutura de database/schema.sql. Pula o teste se o
 * MySQL local não estiver rodando.
 */
function banco_de_teste(): PDO
{
    $db = (require dirname(__DIR__) . '/config/config.php')['db'];

    try {
        $pdo = new PDO(
            sprintf('mysql:host=%s;port=%d;charset=utf8mb4', $db['host'], $db['port']),
            $db['user'],
            $db['pass'],
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
    } catch (PDOException $e) {
        pular('MySQL local indisponível — inicie o banco para rodar este teste');
    }

    $pdo->exec('DROP DATABASE IF EXISTS ph_catalogo_test');
    $pdo->exec('CREATE DATABASE ph_catalogo_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
    $pdo->exec('USE ph_catalogo_test');

    // Reaproveita o CREATE TABLE de database/schema.sql (fonte única da estrutura)
    $schema = (string) file_get_contents(dirname(__DIR__) . '/database/schema.sql');
    $pdo->exec(substr($schema, (int) stripos($schema, 'CREATE TABLE')));

    return $pdo;
}

function destruir_banco_de_teste(PDO $pdo): void
{
    $pdo->exec('DROP DATABASE IF EXISTS ph_catalogo_test');
}

// ---------- Endpoints ----------

/**
 * Executa um endpoint de public/api/ num subprocesso PHP (sem servidor web).
 *
 * O corpo da resposta sai pelo stdout; o status HTTP é capturado por uma
 * shutdown function e sai pelo stderr (funciona mesmo quando o endpoint
 * chama exit). `$env` permite apontar o endpoint para o banco de teste,
 * ex.: ['DB_NAME' => 'ph_catalogo_test'].
 *
 * @return array{status: int, json: mixed}
 */
function executar_endpoint(string $arquivoRelativo, string $metodo, array $env = []): array
{
    $arquivo = dirname(__DIR__) . '/' . $arquivoRelativo;

    $codigo = sprintf(
        "\$_SERVER['REQUEST_METHOD'] = %s;"
        . 'register_shutdown_function(static function (): void { fwrite(STDERR, (string) http_response_code()); });'
        . 'require %s;',
        var_export($metodo, true),
        var_export($arquivo, true)
    );

    $processo = proc_open(
        [PHP_BINARY, '-r', $codigo],
        [1 => ['pipe', 'w'], 2 => ['pipe', 'w']],
        $pipes,
        dirname(__DIR__),
        array_merge(getenv(), $env)
    );

    assert_true(is_resource($processo), "Não foi possível executar {$arquivoRelativo}.");

    $corpo = (string) stream_get_contents($pipes[1]);
    $status = trim((string) stream_get_contents($pipes[2]));
    fclose($pipes[1]);
    fclose($pipes[2]);
    proc_close($processo);

    return [
        'status' => $status === '' || $status === 'false' ? 200 : (int) $status,
        'json'   => json_decode($corpo, true),
    ];
}
