# 🎮 PH Catálogo — jogos grátis

Mini catálogo web de jogos gratuitos que **consome** a [API FreeToGame](https://www.freetogame.com/api-doc),
**guarda** os jogos num banco MySQL e **mostra** tudo numa página única — sem login, sem framework,
sem dependências externas. Feito para o teste técnico **PH CORE / Prohall**.

| Objetivo do teste | Onde acontece |
|---|---|
| 01 · Consumir a API | `src/FreeToGameClient.php` → botão **Sincronizar catálogo** na página |
| 02 · Salvar no banco (só os novos) e exibir os salvos | `src/GameRepository.php` (upsert por id) → `public/api/games.php` |
| 03 · Subir no Git | este repositório, com `database/dump.sql` incluído |

**Stack:** PHP 8 puro · MySQL 8 (ou MariaDB) · HTML + CSS + JavaScript vanilla · zero Composer/npm/CDN.

---

## 🚀 Como rodar (3 passos)

> Pré-requisitos: **PHP 8+** e **MySQL/MariaDB** locais — [Laragon](https://laragon.org) ou XAMPP resolvem os dois.
> No Windows com Laragon: basta **dois cliques em `start.bat`**, que faz os passos 2 e 3 sozinho.

```bash
# 1. Clone e entre na pasta
git clone https://github.com/WendelRafael/PH-Group.git
cd <pasta-do-repositorio>

# 2. Importe o banco (estrutura + dados já populados)
mysql -u root < database/dump.sql

# 3. Suba o servidor embutido do PHP
php -S localhost:8000 -t public
```

Abra **http://localhost:8000** — o catálogo já aparece populado.
Clique em **Sincronizar catálogo** para buscar jogos novos na FreeToGame a qualquer momento.

> 🔑 MySQL com usuário/senha diferentes de `root` sem senha?
> Copie `.env.example` para `.env` e ajuste — nada mais é preciso.

---

## 📁 Estrutura de pastas

```
├── public/            # única pasta exposta ao navegador (docroot)
│   ├── index.php      #   página do catálogo
│   ├── assets/        #   css e js próprios (sem CDN)
│   └── api/           #   endpoints internos em JSON (games.php, sync.php)
├── src/               # lógica PHP: conexão, cliente da API, repositório
├── config/            # configuração (lê .env, com padrões do Laragon)
├── database/          # schema.sql (estrutura) e dump.sql (estrutura + dados)
├── test/              # testes em PHP puro — php test/run.php
├── docs/              # decisões técnicas e planejamento
├── start.bat          # atalho Windows: importa o banco (se preciso) e sobe o servidor
└── AGENTS.md          # regras para quem (humano ou IA) mexer no código
```

## 🔌 API interna

| Endpoint | Método | Faz o quê | Resposta |
|---|---|---|---|
| `/api/games.php` | GET | Lista os jogos **salvos no banco** | `{ "total": 413, "jogos": [...] }` |
| `/api/sync.php` | POST | Importa da FreeToGame; **insere só os novos** | `{ "inseridos": 12, "total": 425 }` |

## 🧪 Testes

```bash
php test/run.php
```

Runner próprio em PHP puro (sem PHPUnit): testes de unidade rodam offline;
os de integração — incluindo os endpoints de `public/api/` — usam um banco
descartável (`ph_catalogo_test`) e são pulados se o MySQL estiver desligado.

## 📚 Mais contexto

- [docs/decisoes.md](docs/decisoes.md) — por que cada decisão técnica foi tomada
- [docs/planejamento.md](docs/planejamento.md) — planejamento original do projeto
- [AGENTS.md](AGENTS.md) — regras de contribuição (humanos e IAs)

---

Dados fornecidos por [FreeToGame](https://www.freetogame.com) · Teste técnico PH CORE / Prohall · 2026
