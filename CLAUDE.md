# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## ⚠️ Rotina obrigatória — Conselho de Aprovação

**Antes de atender QUALQUER demanda neste projeto, leia o [AGENTS.md](AGENTS.md).**

Todo código criado ou alterado (`public/`, `src/`, `config/`, `database/*.sql`) deve ser
submetido ao **Conselho** definido lá — Agente UX/UI + Agente Funcionalidade — e só é
aceito com **aprovação unânime**. Reprovou, refaz e submete de novo. Código reprovado
não pode ser commitado. O Agente Funcionalidade exige teste unitário em `test/` para
cada função nova/alterada, com a suíte 100% verde. Cada rodada termina com o
Parecer do Conselho (modelo no AGENTS.md). Apenas mudanças exclusivamente de
documentação (`*.md`) dispensam o Conselho.

## O projeto

Teste técnico **PH CORE / Prohall**: mini catálogo de jogos grátis que **consome**
a API FreeToGame (`https://www.freetogame.com/api`, sem chave), **guarda** os jogos
no MySQL e **mostra** tudo numa página única. Sem login. UI e documentação em
**português**. O plano completo (objetivos, critérios de avaliação, etapas) está em
[docs/planejamento.md](docs/planejamento.md) — é a fonte de verdade da ordem de trabalho.

**Stack fixa, sem dependências externas** (regra do teste: "clonou, rodou"):
PHP 8.3 puro (sem framework, sem Composer), MySQL 8.4 (Laragon, `root` sem senha),
HTML5 + CSS3 + JavaScript vanilla (sem npm, sem CDN).

## Comandos

```powershell
php -S localhost:8000 -t public                              # sobe o servidor local
mysql -u root < database/schema.sql                          # cria banco/tabela do zero
mysql -u root < database/dump.sql                            # restaura banco populado (fluxo do avaliador)
php test/run.php                                             # roda a suíte de testes (exit ≠ 0 se falhar)
mysqldump -u root --databases ph_catalogo > database/dump.sql  # regenera o dump p/ entrega
curl http://localhost:8000/api/games.php                     # smoke test: jogos salvos
curl -X POST http://localhost:8000/api/sync.php              # smoke test: importa novos da FreeToGame
```

Testes são PHP puro com runner próprio (sem PHPUnit) — convenções em [AGENTS.md](AGENTS.md):
um `test/XxxTest.php` por arquivo-fonte, sem internet (fixtures para a FreeToGame),
banco de teste `ph_catalogo_test` (nunca o real).

## Arquitetura

Página única com dois endpoints JSON:

1. `public/index.php` carrega; `assets/js/app.js` faz `GET api/games.php` → lê o
   **banco** (nunca a API externa) → renderiza os cards; busca/filtro por gênero
   são client-side.
2. Botão "Sincronizar catálogo" → `POST api/sync.php` → PHP consome a FreeToGame
   via cURL → grava **só os jogos novos** e responde `{inseridos, total}` → JS
   recarrega a lista.

A deduplicação é estrutural: a PK da tabela `games` é o próprio `id` da FreeToGame,
e a gravação usa `INSERT ... ON DUPLICATE KEY UPDATE`.

Separação de camadas:

- `public/` — **única** pasta exposta ao navegador (docroot do `php -S`). Os
  endpoints em `public/api/` são finos: validam a requisição e delegam para `src/`.
- `src/` — lógica fora do docroot: `Database.php` (PDO singleton, utf8mb4),
  `FreeToGameClient.php` (cURL, timeout, tratamento de erro),
  `GameRepository.php` (`findAll()`, `upsertMany()`, `count()`).
- `config/config.php` — credenciais via `.env` com fallback para o padrão Laragon;
  `.env` está no `.gitignore`, o modelo é `.env.example`.
- `test/` — na raiz, **nunca dentro de `public/`** (testes não podem ser acessíveis
  pelo navegador).
- `database/dump.sql` — o dump **completo (estrutura + dados)** é exigência da
  entrega e deve estar sempre commitado e atualizado.

## Critérios que orientam decisões

São os critérios de avaliação do teste (todos com peso máximo): raiz limpa e pastas
com propósito claro; clareza para quem abre o repo; ambientação (contexto e regras
nos arquivos internos); HTML semântico com **hierarquia visual e código limpo
valendo mais que enfeite**. Na dúvida entre sofisticação e simplicidade óbvia,
escolha a simplicidade.
