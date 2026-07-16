# Planejamento — Teste Técnico PH CORE / Prohall

> Mini catálogo de jogos grátis consumindo a API FreeToGame.
> Fonte das instruções: https://prohall.app.br/ph-teste.html

---

## 1. O que o teste pede (resumo fiel do site)

**Missão:** montar um projeto web que **consome, guarda e mostra** — um mini catálogo de jogos grátis.

**Três objetivos obrigatórios:**

| # | Objetivo | Detalhe |
|---|----------|---------|
| 01 | **Consome a API** | Puxar os jogos da FreeToGame e listar tudo numa página HTML bem-feita |
| 02 | **Salva no banco** | Gravar os jogos novos consumidos da API **e** exibir no HTML os que já estão salvos |
| 03 | **Sobe no Git** | Publicar num repositório Git **novo** e enviar o link |

**Regras do teste:**
- Stack recomendada: PHP, HTML, JS (outras liberadas) → **usaremos PHP + HTML + JS + CSS**
- Banco: **MySQL ou MariaDB** (o site sugere Laragon)
- O **dump completo do banco deve ir DENTRO do repositório Git** (não em anexo)
- O projeto precisa **rodar de forma simples ao clonar** — "sem ginástica, sem passos secretos"
- Ter **contexto e regras no ambiente interno** do projeto (README, arquivos de contexto p/ quem for mexer)
- IA liberada

**Critérios de avaliação (todos com peso máximo):**
1. 🗂️ **Estrutura de pastas** — propósito claro, nada jogado na raiz
2. 🔍 **Clareza** — outra pessoa (ou IA) abre o repo e entende rápido
3. 🧭 **Ambientação** — ambiente preparado, com contexto e regras para quem for mexer
4. 🎨 **HTML organizado e bonito** — hierarquia visual e código limpo valem mais que enfeite

**Entrega:** link do repositório Git (com dump do banco dentro) para quem enviou o teste.

**Requisitos adicionais definidos por nós:**
- Sem login / sem autenticação
- Interface de fácil manuseio (uma página, ações óbvias)

---

## 2. A API — FreeToGame

- **Base:** `https://www.freetogame.com/api` (aberta, sem chave, sem cadastro)
- **Doc:** `https://www.freetogame.com/api-doc`
- **Endpoint principal:** `GET /games` — retorna JSON com todos os jogos

Campos retornados por jogo (verificado em 16/07/2026):
`id, title, thumbnail, short_description, game_url, genre, platform, publisher, developer, release_date, freetogame_profile_url`

Endpoints úteis opcionais: `/games?category=...`, `/games?platform=...`, `/game?id=...` (detalhe).

---

## 3. Tecnologias requeridas

| Camada | Tecnologia | Situação na máquina (16/07/2026) |
|--------|-----------|----------------------------------|
| Backend | PHP 8.x (puro, sem framework) | ❌ **Instalar** |
| Banco | MySQL / MariaDB | ❌ **Instalar** |
| Frontend | HTML5 + CSS3 + JavaScript vanilla (fetch API) | ✅ Nada a instalar |
| Versionamento | Git | ✅ Já instalado |
| Servidor local | Servidor embutido do PHP (`php -S`) ou Apache do Laragon | vem com o PHP/Laragon |

**Recomendação de ambiente: Laragon** (o próprio teste sugere). Um instalador só traz PHP + MariaDB/MySQL + Apache + HeidiSQL.
Download: https://laragon.org/download — instalar a versão Full.
*Alternativa:* XAMPP (`winget install ApacheFriends.Xampp.8.2`).

Sem dependências externas no projeto: **sem Composer, sem npm, sem CDN** — clonou, rodou.

---

## 4. Arquitetura e fluxo da aplicação

Aplicação de **página única** (sem login):

```
┌─────────────┐   1. abre a página    ┌──────────────────┐
│   Browser    │ ────────────────────▶ │ public/index.php │
│ (HTML/JS/CSS)│                       └──────────────────┘
│              │   2. GET api/games.php ─▶ lê o BANCO ─▶ retorna JSON ─▶ JS renderiza cards
│              │
│              │   3. clique em "Sincronizar catálogo"
│              │      POST api/sync.php ─▶ PHP consome FreeToGame
│              │                          ─▶ INSERE só os jogos NOVOS no banco
│              │                          ─▶ retorna {novos: N, total: M}
│              │   4. JS recarrega a lista (agora vinda do banco)
└─────────────┘
```

Por que assim:
- **Objetivo 01** ✔ o PHP consome a FreeToGame e o resultado aparece listado no HTML.
- **Objetivo 02** ✔ só os jogos novos são gravados (`INSERT ... ON DUPLICATE KEY UPDATE`, chave = id da FreeToGame) e a página sempre exibe o que está salvo no banco.
- **Fácil manuseio** ✔ uma página, um botão de sincronizar, busca e filtro por gênero no cliente.
- Primeira execução: banco já vem populado pelo dump; o botão atualiza quando quiser.

---

## 5. Organização das pastas

```
PHGroup/
├── public/                     # ÚNICA pasta exposta ao navegador (docroot)
│   ├── index.php               # página do catálogo
│   ├── assets/
│   │   ├── css/style.css       # estilos (design próprio, sem CDN)
│   │   └── js/app.js           # fetch, render dos cards, busca/filtro, sync
│   └── api/                    # endpoints internos (retornam JSON)
│       ├── games.php           # GET  → jogos salvos no banco
│       └── sync.php            # POST → importa novos jogos da FreeToGame
│
├── src/                        # lógica PHP (fora do docroot)
│   ├── Database.php            # conexão PDO singleton
│   ├── FreeToGameClient.php    # consumo da API externa (cURL)
│   └── GameRepository.php      # consultas/gravação na tabela games
│
├── config/
│   └── config.php              # carrega credenciais (env → padrão local)
│
├── database/
│   ├── schema.sql              # estrutura (referência de desenvolvimento)
│   └── dump.sql                # DUMP COMPLETO (estrutura + dados) — exigido no Git
│
├── docs/
│   └── decisoes.md             # contexto: por que cada decisão foi tomada
│
├── .env.example                # modelo de credenciais (copiar p/ .env)
├── .gitignore                  # ignora .env e temporários
├── start.bat                   # atalho Windows: sobe o servidor com 2 cliques
├── AGENTS.md                   # regras/contexto p/ IAs que mexerem no projeto ("ambientação")
└── README.md                   # o quê, como rodar (3 passos), estrutura, endpoints
```

- Raiz limpa: só arquivos de entrada/contexto — atende o critério "nada jogado na raiz".
- `public/` como docroot: código de negócio e credenciais ficam inacessíveis via URL.
- `AGENTS.md` + `docs/` + `README.md` cobrem o critério **Ambientação/Clareza**.
- Este `PLANEJAMENTO.md` será movido para `docs/` (ou removido) antes da entrega.

---

## 6. Banco de dados

Banco: `ph_catalogo` · charset `utf8mb4`.

```sql
CREATE TABLE games (
  id                     INT UNSIGNED  PRIMARY KEY,   -- mesmo id da FreeToGame (evita duplicar)
  title                  VARCHAR(255)  NOT NULL,
  thumbnail              VARCHAR(500),
  short_description      TEXT,
  game_url               VARCHAR(500),
  genre                  VARCHAR(100),
  platform               VARCHAR(100),
  publisher              VARCHAR(255),
  developer              VARCHAR(255),
  release_date           DATE NULL,
  freetogame_profile_url VARCHAR(500),
  created_at             TIMESTAMP DEFAULT CURRENT_TIMESTAMP  -- quando entrou no catálogo
);
```

- PK = id da FreeToGame → `INSERT ... ON DUPLICATE KEY UPDATE` garante que **só novos** são inseridos.
- Índice extra em `genre` para o filtro.

---

## 7. Etapas de execução (ordem de trabalho)

### Etapa 0 — Ambiente (pré-requisito)
1. Instalar **Laragon Full** → https://laragon.org/download
2. Iniciar Laragon (sobe MySQL/MariaDB) e conferir:
   ```powershell
   php -v          # PHP 8.x
   mysql --version # MariaDB/MySQL
   ```
   *(Se `php`/`mysql` não entrarem no PATH: Laragon → Menu → Tools → Path → Add Laragon to Path.)*

### Etapa 1 — Banco
1. Escrever `database/schema.sql` (CREATE DATABASE + tabela `games`)
2. Executar:
   ```powershell
   mysql -u root < database/schema.sql
   ```

### Etapa 2 — Backend (PHP)
Ordem de construção:
1. `config/config.php` + `.env.example` — credenciais com padrão Laragon (root/senha vazia)
2. `src/Database.php` — PDO com exceções e utf8mb4
3. `src/FreeToGameClient.php` — cURL para `GET /games`, timeout e tratamento de erro
4. `src/GameRepository.php` — `findAll()`, `upsertMany()` (retorna quantos eram novos), `count()`
5. `public/api/games.php` — GET → JSON dos jogos do banco
6. `public/api/sync.php` — POST → consome API, grava novos, responde `{inseridos, total}`
7. Teste manual de cada endpoint:
   ```powershell
   php -S localhost:8000 -t public   # sobe o servidor
   curl http://localhost:8000/api/games.php
   curl -X POST http://localhost:8000/api/sync.php
   ```

### Etapa 3 — Frontend (HTML/CSS/JS)
1. `public/index.php` — HTML semântico: header (título + botão "Sincronizar catálogo" + contador), campo de busca, filtro por gênero, grid de cards, footer
2. `public/assets/css/style.css` — design limpo (grid responsivo de cards com thumbnail, título, gênero, plataforma, descrição, link "Jogar"); estados de loading/vazio/erro
3. `public/assets/js/app.js` — carrega `api/games.php`, renderiza, busca/filtro client-side, botão de sync com feedback ("+12 jogos novos")
4. Critério do teste: **hierarquia visual e código limpo > enfeite**

### Etapa 4 — Ambientação e documentação
1. `README.md` — descrição, screenshot, **como rodar em 3 passos**, estrutura de pastas, endpoints
2. `AGENTS.md` — regras do projeto para quem (humano ou IA) for mexer
3. `docs/decisoes.md` — justificativa das decisões técnicas
4. `start.bat` — inicia `php -S localhost:8000 -t public` e abre o navegador
5. `.gitignore` — `.env`, arquivos temporários

### Etapa 5 — Dump + Git + entrega
1. Sincronizar uma vez (popular o banco) e gerar o dump **com dados**:
   ```powershell
   mysqldump -u root --databases ph_catalogo > database/dump.sql
   ```
2. Testar o fluxo do avaliador do zero (simular "clonei agora"):
   ```powershell
   git clone <repo> teste-clone; cd teste-clone
   mysql -u root < database/dump.sql
   php -S localhost:8000 -t public
   # abrir http://localhost:8000 → catálogo aparece populado
   ```
3. Publicar (repositório novo, público):
   ```powershell
   git add .
   git commit -m "feat: catálogo de jogos FreeToGame (PHP + MySQL)"
   git remote add origin https://github.com/<usuario>/<repo>.git
   git push -u origin main
   ```
   *(o repositório local em `PHGroup` já está iniciado na branch `main`, sem commits)*
4. Enviar o link do repositório para quem mandou o teste.

---

## 8. Checklist final de entrega

- [ ] Página lista os jogos vindos da API FreeToGame (Obj. 01)
- [ ] Jogos novos são gravados no banco e os salvos aparecem no HTML (Obj. 02)
- [ ] Repositório Git novo publicado com link enviado (Obj. 03)
- [ ] `database/dump.sql` completo (estrutura + dados) dentro do repo
- [ ] Roda ao clonar: dump → `start.bat` (ou `php -S`) → navegador
- [ ] README claro com passos de instalação
- [ ] AGENTS.md / docs com contexto e regras (ambientação)
- [ ] Raiz do projeto limpa, cada pasta com propósito
- [ ] HTML semântico, organizado e bonito
- [ ] Sem login; uso óbvio (1 página, 1 botão de sync, busca e filtro)

---

## 9. Informações pendentes (confirmar antes de executar)

1. **Ambiente local:** posso seguir com a instalação do **Laragon** (recomendado pelo próprio teste)? Ou prefere XAMPP/instalação manual de PHP+MySQL?
2. **Conta GitHub/GitLab:** qual usuário e qual nome dar ao repositório novo? (necessário só na Etapa 5)
3. **Idioma da interface:** assumo **português** para a UI e documentação — ok?
