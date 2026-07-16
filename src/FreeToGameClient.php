<?php

declare(strict_types=1);

/**
 * Cliente HTTP da API FreeToGame (https://www.freetogame.com/api-doc).
 *
 * A API é aberta: sem chave e sem cadastro. Este cliente só faz leitura
 * (GET /games) e normaliza o JSON para o formato usado pelo banco.
 */
final class FreeToGameClient
{
    public function __construct(
        private readonly string $baseUrl,
        private readonly int $timeout = 20,
    ) {
    }

    /**
     * Busca todos os jogos e devolve apenas os campos que o catálogo usa.
     *
     * @return array<int, array<string, mixed>>
     * @throws RuntimeException se a API estiver fora ou responder algo inválido
     */
    public function buscarJogos(): array
    {
        return self::normalizarJson($this->requisitar($this->baseUrl . '/games'));
    }

    /**
     * Valida e normaliza o JSON cru da API (público e puro: testável sem rede).
     *
     * Itens sem `id` ou `title` são descartados; `release_date` inválida vira
     * null; textos são aparados nas bordas (a API às vezes manda gênero com
     * espaço, ex.: " MMORPG") e string vazia vira null.
     *
     * @return array<int, array<string, mixed>>
     * @throws RuntimeException se o JSON não for uma lista de jogos
     */
    public static function normalizarJson(string $json): array
    {
        $dados = json_decode($json, true);

        if (!is_array($dados) || !array_is_list($dados)) {
            throw new RuntimeException('Resposta da FreeToGame não é uma lista de jogos.');
        }

        $texto = static function (mixed $valor): ?string {
            $valor = trim((string) ($valor ?? ''));
            return $valor === '' ? null : $valor;
        };

        $jogos = [];

        foreach ($dados as $item) {
            if (!is_array($item) || !isset($item['id'], $item['title'])) {
                continue;
            }

            $titulo = trim((string) $item['title']);
            if ($titulo === '') {
                continue;
            }

            $data = (string) ($item['release_date'] ?? '');
            $dataValida = preg_match('/^\d{4}-\d{2}-\d{2}$/', $data) === 1 && $data !== '0000-00-00';

            $jogos[] = [
                'id'                     => (int) $item['id'],
                'title'                  => $titulo,
                'thumbnail'              => $texto($item['thumbnail'] ?? null),
                'short_description'      => $texto($item['short_description'] ?? null),
                'game_url'               => $texto($item['game_url'] ?? null),
                'genre'                  => $texto($item['genre'] ?? null),
                'platform'               => $texto($item['platform'] ?? null),
                'publisher'              => $texto($item['publisher'] ?? null),
                'developer'              => $texto($item['developer'] ?? null),
                'release_date'           => $dataValida ? $data : null,
                'freetogame_profile_url' => $texto($item['freetogame_profile_url'] ?? null),
            ];
        }

        return $jogos;
    }

    /**
     * @throws RuntimeException em erro de rede ou status HTTP != 200
     */
    private function requisitar(string $url): string
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT        => $this->timeout,
            CURLOPT_USERAGENT      => 'PH-Catalogo/1.0 (teste tecnico)',
        ]);

        $corpo  = curl_exec($ch);
        $erro   = curl_error($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        curl_close($ch);

        if ($corpo === false) {
            throw new RuntimeException("Falha ao acessar a FreeToGame: {$erro}");
        }
        if ($status !== 200) {
            throw new RuntimeException("FreeToGame respondeu HTTP {$status}.");
        }

        return $corpo;
    }
}
