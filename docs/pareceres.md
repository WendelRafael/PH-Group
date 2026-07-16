# Pareceres do Conselho de Aprovação de Código

Histórico das rodadas do Conselho definido em [AGENTS.md](../AGENTS.md) —
Agente 1 (UX/UI) + Agente 2 (Funcionalidade), aprovação por unanimidade.

---

## Parecer do Conselho — 2026-07-16 · Rodada 1

Arquivos avaliados: todo o código inicial do projeto — `config/config.php`, `src/*`,
`public/index.php`, `public/assets/*`, `public/api/*`, `database/schema.sql`, `test/*`.

| Agente         | Veredito  | Observações |
|----------------|-----------|-------------|
| UX/UI          | REPROVADO | Hierarquia, coesão, feedback e responsividade OK; 4 pendências objetivas (abaixo) |
| Funcionalidade | APROVADO  | Suíte 19✓/1 pulado/0✗; convenções de teste, prepared statements, upsert e tratamento de erros verificados |

**Veredito final: REFAZER**

Pendências apontadas (UX/UI):

1. Página sem `h1` — headings começavam em `h2` (`public/index.php`).
2. Filtro de gênero com "MMORPG" duplicado — dado da API vinha com espaço à
   esquerda (`" MMORPG"` no jogo Aberoth) e aparecia como primeira opção do select.
3. Filtro de plataforma com opção combinada "PC (Windows), Web Browser" —
   comparação exata escondia 10 jogos ao filtrar por "Web Browser".
4. Erros do navegador vazavam em inglês para o usuário ("Failed to fetch",
   "Unexpected token…") nos caminhos de falha do `app.js`.

Observações não bloqueantes (Funcionalidade): sintaxe `VALUES()` deprecated no
MySQL 8+ (mantida por compatibilidade com MariaDB — decisão documentada no código);
contagem de novos por COUNT antes/depois não é precisa sob escrita concorrente
(irrelevante no uso atual, endpoint único).

---

## Parecer do Conselho — 2026-07-16 · Rodada 2

Arquivos avaliados: `public/index.php`, `public/assets/css/style.css`,
`public/assets/js/app.js`, `src/FreeToGameClient.php`, `src/GameRepository.php`
(comentário), `test/FreeToGameClientTest.php` (teste novo).

Correções aplicadas desde a rodada 1:

1. Marca virou `<h1>PH Catálogo</h1>` — hierarquia de headings correta.
2. `normalizarJson` apara espaços nas bordas de todos os campos de texto e converte
   vazio em `null` (com teste novo); banco ressincronizado — `" MMORPG"` eliminado.
3. `valoresDoJogo()` divide gênero/plataforma por vírgula: o select não mostra mais
   a opção combinada e o filtro usa `.includes()` — os 10 jogos voltaram a aparecer.
4. `mensagemDeErro()` traduz erros de rede/parse do navegador para português.

| Agente         | Veredito | Observações |
|----------------|----------|-------------|
| UX/UI          | APROVADO | 4 pendências corrigidas e verificadas no app no ar; sem regressões de UX |
| Funcionalidade | APROVADO | Suíte 20✓/1 pulado/0✗; mudança coberta por teste novo; funções JS puras validadas isoladamente (8/8 asserções) |

**Veredito final: ACEITO** — código liberado para commit.

Sugestão não bloqueante acatada em seguida (mudança só em `test/`, que dispensa o
Conselho): asserção explícita para item com `title` só de espaços ser descartado.
