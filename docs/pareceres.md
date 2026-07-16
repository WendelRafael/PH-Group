# Pareceres do Conselho de Aprovação de Código

Histórico das rodadas do Conselho de Aprovação de Código — processo interno de
revisão em que todo código criado ou alterado é avaliado por dois agentes
independentes, Agente 1 (UX/UI) e Agente 2 (Funcionalidade), e só é aceito
para commit com aprovação por unanimidade.

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

---

## Parecer do Conselho — 2026-07-16 · Rodada 3

Arquivos avaliados: `public/index.php`, `public/assets/css/style.css`,
`public/api/games.php`, `public/api/sync.php`.

Mudanças da rodada:

1. **Visual** (pedido do dono do projeto): atmosfera de fundo no estilo da página
   do teste (noite violeta + brilhos radiais + grade de pontos), paleta deslocada
   para o violeta, emoji da marca substituído por SVG de gamepad dourado e glifo
   `⟳` do botão substituído por SVG circular de refresh (o antigo renderizava torto).
2. **Endpoints**: uma edição manual fora do Conselho havia trocado o status de erro
   de banco para 404 (games) e 402 (sync) — a suíte acusou a regressão
   (teste do 500 falhou) e os códigos foram restaurados para **HTTP 500**, mantendo
   a nova redação das mensagens com pontuação corrigida.

| Agente         | Veredito | Observações |
|----------------|----------|-------------|
| UX/UI          | APROVADO | Contraste WCAG AA folgado na nova paleta (texto ≈16:1); atmosfera decorativa acessível (`aria-hidden`, z-index -1); animação e responsivo preservados |
| Funcionalidade | APROVADO | Suíte 20✓/1 pulado/0✗; 500 restaurado e confirmado ao vivo; nenhuma lógica alterada além do status |

**Veredito final: ACEITO** — código liberado para commit.

Observações não bloqueantes registradas: alinhar o violeta antigo do fundo da
`.etiqueta` ao novo acento (acatada em seguida, conforme prescrição do próprio
parecer); o ramo de erro de banco do `sync.php` não tem teste direto — sugerida
injeção da URL da FreeToGame via variável de ambiente para testar o fluxo completo
offline em rodada futura. O episódio do 404/402 mostrou o valor da suíte: a
regressão foi pega exatamente pelo teste do endpoint.

---

## Parecer do Conselho — 2026-07-16 · Rodada 4

Arquivos avaliados: `public/index.php`, `public/assets/css/style.css`
(mudanças exclusivamente visuais, pedidas pelo dono do projeto).

Mudanças da rodada:

1. **Hero de abertura**: eyebrow mono, título com gradiente violeta→dourado e
   subtítulo; o `h1` migrou da marca do topo para o hero (continua único e mais
   semântico — título da página).
2. **Cards estilo loot**: borda esquerda violeta 3px, hover com glow violeta e
   zoom sutil na thumbnail.
3. **Contador HUD**: chip com borda — rótulo mono "CATÁLOGO" + valor dourado.
4. **Fonte mono do sistema** nos micro-rótulos (eyebrow, etiquetas, labels,
   contador, rodapé) — sem CDN.

| Agente         | Veredito | Observações |
|----------------|----------|-------------|
| UX/UI          | APROVADO | h1 único confirmado no HTML servido; contraste WCAG com folga (pior caso ≈9:1, texto grande exige 3:1); responsivo verificado até 320px |
| Funcionalidade | APROVADO | Suíte 20✓/1 pulado/0✗; escopo visual confirmado por git diff; os 12 IDs consumidos pelo app.js existem exatamente 1 vez (contrato JS–HTML preservado) |

**Veredito final: ACEITO** — código liberado para commit.

Observação não bloqueante acatada em seguida (prescrição do próprio parecer):
tamanho do rótulo do contador unificado à escala mono (.6rem → .66rem).

---

## Parecer do Conselho — 2026-07-16 · Rodada 5

Arquivo avaliado: `public/assets/css/style.css` (1 linha).

Mudança da rodada: removida a borda esquerda violeta de 3px dos cards
("raridade de loot"), a pedido do dono do projeto. Glow e zoom do hover mantidos.

| Agente         | Veredito | Observações |
|----------------|----------|-------------|
| UX/UI          | APROVADO | Cards mais coesos com a borda uniforme (alinhados a inputs/chip/toast); affordance de hover preservada por 4 sinais (elevação, borda violeta, glow, zoom) |
| Funcionalidade | APROVADO | Suíte 20✓/1 pulado/0✗; escopo confirmado por git diff (só a 1 linha de CSS); zero impacto funcional |

**Veredito final: ACEITO** — código das rodadas 1 a 5 liberado para commit.
