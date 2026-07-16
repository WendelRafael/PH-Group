# Decisões técnicas — por quê de cada escolha

Registro das decisões tomadas na construção do catálogo, para quem for
avaliar ou evoluir o projeto entender o raciocínio — não só o resultado.

## PHP puro, sem framework

O teste avalia **como o projeto é montado por dentro**, não domínio de framework.
Com 3 classes e 2 endpoints, um framework só adicionaria ruído e passos de
instalação — e a regra de ouro do teste é "rodar ao clonar, sem ginástica".
Zero dependências = zero `composer install` = menos coisas que podem falhar
na máquina de quem clona.

## Página única, banco na frente

A página **sempre lista o que está no banco** (`GET api/games.php`), e a
sincronização com a FreeToGame é uma ação explícita do usuário
(`POST api/sync.php`). Motivos:

- os dois objetivos do teste ("consome a API" e "exibe os salvos") ficam
  visíveis e distintos na interface;
- o app abre instantâneo mesmo se a FreeToGame estiver fora do ar;
- o dump entregue já vem populado — quem clona vê o catálogo cheio no primeiro acesso.

## Upsert com chave primária = id da FreeToGame

"Gravar os jogos **novos**" exige saber o que é novo. Usar o próprio id da
FreeToGame como PK resolve no nível do banco: `INSERT ... ON DUPLICATE KEY UPDATE`
insere o que não existe e atualiza o que mudou — reimportar nunca duplica,
sem precisar de lógica de comparação em PHP.

A contagem de novos é feita por diferença (`COUNT` antes/depois da transação):
mais simples e à prova das ambiguidades do `rowCount()` nesse tipo de comando
(que retorna 1 para inserção, 2 para atualização e 0 para linha idêntica).

## `public/` como único docroot

Só `public/` é servida ao navegador. Classes, configuração e credenciais ficam
fora do alcance de URL — mesmo rodando no Apache do Laragon apontado para a
pasta errada, ninguém baixa `config.php`. Endpoints são finos: validam o método,
chamam as classes de `src/` e devolvem JSON.

## Runner de testes próprio (sem PHPUnit)

PHPUnit exigiria Composer — quebrando a regra de zero dependências. O runner
(`test/run.php`) cobre o necessário: descobre e executa as funções `test_*`,
asserções com mensagens claras, testes pulados quando o MySQL está desligado
e exit code para CI. Testes de integração usam um banco descartável
(`ph_catalogo_test`), criado e destruído a cada execução — o banco real nunca
é tocado. Até os endpoints de `public/api/` são testados assim: rodam num
subprocesso PHP apontado para o banco de teste via variável de ambiente.

## Segurança num app sem login

Mesmo sem autenticação, o básico é inegociável:

- **SQL injection** — 100% prepared statements (PDO com `EMULATE_PREPARES` off);
- **XSS** — todo dado da API passa pela função `escapar()` antes de entrar no DOM
  (os dados vêm de terceiros — a FreeToGame — e não são confiáveis por definição);
- **credenciais** — `.env` fora do Git (`.gitignore`); `.env.example` documenta o formato.

## Datas normalizadas na borda

`release_date` da API às vezes vem vazia ou inválida. A normalização acontece
no `FreeToGameClient` (vira `NULL` antes do banco), mantendo a coluna `DATE`
tipada de verdade em vez de `VARCHAR` com lixo.

## Dump completo dentro do repositório

Regra explícita do teste. `database/dump.sql` carrega estrutura + dados
(gerado com `mysqldump --databases`, então cria o banco sozinho). O
`database/schema.sql` separado existe como referência limpa da estrutura
e é o que os testes de integração usam para montar o banco descartável.
