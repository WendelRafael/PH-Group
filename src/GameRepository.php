<?php

declare(strict_types=1);

/**
 * Acesso à tabela `games` — todas as consultas SQL do catálogo vivem aqui.
 */
final class GameRepository
{
    private const CAMPOS = [
        'id', 'title', 'thumbnail', 'short_description', 'game_url',
        'genre', 'platform', 'publisher', 'developer', 'release_date',
        'freetogame_profile_url',
    ];

    public function __construct(private readonly PDO $pdo)
    {
    }

    /**
     * Todos os jogos salvos, em ordem alfabética.
     *
     * @return array<int, array<string, mixed>>
     */
    public function listarTodos(): array
    {
        $sql = sprintf(
            'SELECT %s, created_at FROM games ORDER BY title ASC',
            implode(', ', self::CAMPOS)
        );

        return $this->pdo->query($sql)->fetchAll();
    }

    public function contar(): int
    {
        return (int) $this->pdo->query('SELECT COUNT(*) FROM games')->fetchColumn();
    }

    /**
     * Grava os jogos vindos da API: insere os novos e atualiza os existentes
     * (upsert pela chave primária = id da FreeToGame — nunca duplica).
     *
     * @param array<int, array<string, mixed>> $jogos já normalizados pelo FreeToGameClient
     * @return int quantos jogos eram NOVOS (não existiam no banco)
     */
    public function gravarMuitos(array $jogos): int
    {
        if ($jogos === []) {
            return 0;
        }

        $colunas      = implode(', ', self::CAMPOS);
        $placeholders = implode(', ', array_map(fn (string $c): string => ":{$c}", self::CAMPOS));
        // VALUES() está deprecated no MySQL 8+, mas a sintaxe nova ("AS novo")
        // não existe no MariaDB — e o teste aceita os dois bancos. VALUES()
        // funciona em ambos; trocar só quando o MariaDB suportar a nova forma.
        $atualizacoes = implode(', ', array_map(
            fn (string $c): string => "{$c} = VALUES({$c})",
            array_diff(self::CAMPOS, ['id'])
        ));

        $stmt = $this->pdo->prepare(
            "INSERT INTO games ({$colunas}) VALUES ({$placeholders})
             ON DUPLICATE KEY UPDATE {$atualizacoes}"
        );

        $antes = $this->contar();

        $this->pdo->beginTransaction();
        try {
            foreach ($jogos as $jogo) {
                $stmt->execute(array_intersect_key($jogo, array_flip(self::CAMPOS)));
            }
            $this->pdo->commit();
        } catch (Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }

        return $this->contar() - $antes;
    }
}
