# AGENTS.md — Conselho de Aprovação de Código

> Regras de ambientação para qualquer pessoa ou IA que for mexer neste projeto.
> Contexto geral do projeto: ver [docs/planejamento.md](docs/planejamento.md).

Todo código **criado ou alterado por IA** neste repositório deve ser submetido ao
**Conselho** antes de ser considerado pronto. O Conselho é formado por agentes
avaliadores, cada um com sua especialidade. O código só é aceito com
**aprovação unânime**; se qualquer agente reprovar, o código deve ser **refeito**
atendendo a todos os apontamentos e submetido novamente — quantas vezes for preciso.

---

## Fluxo do Conselho

```
           código novo ou alterado
                     │
                     ▼
        ┌────────────────────────────┐
        │          CONSELHO          │
        │                            │
        │  Agente 1 — UX/UI          │──► APROVADO ou REPROVADO (+ motivos)
        │  Agente 2 — Funcionalidade │──► APROVADO ou REPROVADO (+ motivos)
        └────────────────────────────┘
                     │
          todos aprovaram?
             │              │
            sim            não
             │              │
             ▼              ▼
      código ACEITO   REFAZER o código atendendo
      (pode commitar) TODOS os apontamentos e
                      submeter de novo ao Conselho
```

**Regras do fluxo:**

1. O Conselho se reúne para **toda** criação ou alteração de código
   (`public/`, `src/`, `config/`, `database/*.sql`). Mudanças só de
   documentação (`*.md`) dispensam o Conselho.
2. A aprovação precisa ser **unânime**. Um único REPROVADO já devolve o código.
3. Todo REPROVADO deve vir com **motivos objetivos e acionáveis** — nunca
   "não gostei", sempre "o quê" e "como corrigir".
4. Código reprovado **não pode ser commitado**.
5. Cada rodada termina com um **Parecer do Conselho** (modelo no fim deste arquivo).

---

## Agente 1 — UX/UI

**Missão:** garantir interface e elementos **coesos e fáceis de compreensão**.

Aprova somente se **todos** os itens abaixo forem verdadeiros para o código avaliado:

- [ ] **Hierarquia visual clara** — título, ações e conteúdo em ordem óbvia de importância
      (critério explícito do teste: hierarquia e código limpo valem mais que enfeite).
- [ ] **Coesão** — mesmos espaçamentos, cores, tipografia e padrão de componentes
      em toda a página; nada destoa do restante da interface.
- [ ] **Fácil compreensão** — qualquer pessoa usa sem manual: botões dizem o que
      fazem ("Sincronizar catálogo"), campos têm rótulo/placeholder claros.
- [ ] **Feedback ao usuário** — toda ação tem resposta visível: estado de
      carregando, sucesso ("+12 jogos novos"), lista vazia e erro tratados na tela.
- [ ] **HTML semântico e organizado** — `header`, `main`, `footer`, headings na
      ordem certa, sem `div` sem propósito.
- [ ] **Responsivo** — o grid de cards e os controles funcionam em desktop e mobile.
- [ ] **Textos em português**, diretos e sem jargão técnico.

**Alterações sem impacto visual** (backend puro): o agente verifica apenas se as
mensagens que chegam ao usuário (erros de API, respostas JSON exibidas na tela)
continuam claras e aprova registrando "sem impacto de interface".

---

## Agente 2 — Funcionalidade

**Missão:** validar **toda criação e/ou alteração** de código por meio de testes.

**Obrigações a cada rodada:**

1. Para **cada função nova ou alterada**, criar (ou atualizar) o teste unitário
   correspondente na pasta [test/](test/).
2. Rodar a suíte completa:
   ```powershell
   php test/run.php
   ```
3. **Aprovar somente se 100% dos testes passarem.** Qualquer falha → REPROVADO,
   anexando a saída dos testes que falharam no parecer.

### Convenções da pasta `test/`

O projeto não usa Composer/PHPUnit (regra: clonou, rodou — sem dependências),
então os testes são PHP puro com um runner próprio:

```
test/
├── run.php                  # runner: inclui todos os *Test.php e executa as funções test_*
├── helpers.php              # asserções: assert_equals(), assert_true(), assert_throws()...
├── DatabaseTest.php         # espelha src/Database.php
├── FreeToGameClientTest.php # espelha src/FreeToGameClient.php
├── GameRepositoryTest.php   # espelha src/GameRepository.php
└── ...                      # um *Test.php por arquivo de src/ e public/api/
```

- `test/` fica na **raiz do projeto, nunca dentro de `public/`** — testes não
  podem ficar acessíveis pelo navegador.
- **Um arquivo de teste por arquivo-fonte**: `src/GameRepository.php` →
  `test/GameRepositoryTest.php`.
- **Toda função pública tem ao menos um teste** de caminho feliz, mais casos de
  erro quando existirem (entrada inválida, API fora do ar, banco indisponível).
- `run.php` imprime o resultado de cada teste e termina com **exit code ≠ 0 se
  houver falha** (permite automação).
- **Testes não dependem de internet**: `FreeToGameClient` é testado com
  respostas falsas injetadas (fixtures JSON), nunca chamando a API real.
- **Testes não tocam o banco real**: funções que usam banco rodam contra o banco
  `ph_catalogo_test`, criado a partir de `database/schema.sql` pelo próprio runner.
- **JavaScript**: a lógica do cliente (busca, filtro, montagem de card) deve ser
  escrita em funções puras. Enquanto o projeto não tiver runner JS, o agente
  valida esses fluxos manualmente no navegador e registra o que verificou no parecer.

---

## Parecer do Conselho (modelo)

Ao fim de cada rodada, o Conselho emite o parecer neste formato:

```markdown
### Parecer do Conselho — 2026-07-16
Arquivos avaliados: src/GameRepository.php, test/GameRepositoryTest.php

| Agente         | Veredito  | Observações                                  |
|----------------|-----------|----------------------------------------------|
| UX/UI          | APROVADO  | Sem impacto de interface                      |
| Funcionalidade | REPROVADO | 7/8 testes passaram; upsertMany falha com lista vazia |

**Veredito final: REFAZER**

Pendências:
1. `upsertMany([])` lança PDOException — deve retornar 0 sem tocar o banco.
```

**Veredito final:** `ACEITO` (unanimidade) ou `REFAZER` (qualquer reprovação).
Só depois do `ACEITO` o código pode ser commitado.
