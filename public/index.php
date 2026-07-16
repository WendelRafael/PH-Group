<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>PH Catálogo — jogos grátis</title>
  <meta name="description" content="Mini catálogo de jogos grátis consumindo a API FreeToGame. Teste técnico PH CORE / Prohall." />
  <meta name="color-scheme" content="dark" />
  <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🎮</text></svg>" />
  <link rel="stylesheet" href="assets/css/style.css" />
</head>
<body>

  <header class="topo">
    <div class="container topo-conteudo">
      <a class="marca" href="./" aria-label="PH Catálogo — início">
        <span class="marca-icone" aria-hidden="true">🎮</span>
        <div class="marca-textos">
          <h1>PH Catálogo</h1>
          <small>jogos grátis · FreeToGame</small>
        </div>
      </a>

      <p class="contador" aria-live="polite">
        <strong id="contador-total">—</strong> jogos no catálogo
      </p>

      <button type="button" id="botao-sincronizar" class="botao botao-primario">
        <span class="botao-icone" aria-hidden="true">⟳</span>
        Sincronizar catálogo
      </button>
    </div>
  </header>

  <main class="container">

    <section class="filtros" aria-label="Busca e filtros">
      <div class="campo campo-busca">
        <label for="campo-busca">Buscar jogo</label>
        <input type="search" id="campo-busca" placeholder="Nome, descrição, desenvolvedor…" autocomplete="off" />
      </div>

      <div class="campo">
        <label for="filtro-genero">Gênero</label>
        <select id="filtro-genero">
          <option value="">Todos os gêneros</option>
        </select>
      </div>

      <div class="campo">
        <label for="filtro-plataforma">Plataforma</label>
        <select id="filtro-plataforma">
          <option value="">Todas as plataformas</option>
        </select>
      </div>

      <p class="filtros-resultado" id="filtros-resultado" aria-live="polite"></p>
    </section>

    <section aria-label="Catálogo de jogos">
      <!-- Estados da página: apenas um fica visível por vez (controlado pelo app.js) -->
      <div id="estado-carregando" class="estado" hidden>
        <span class="spinner" aria-hidden="true"></span>
        <p>Carregando catálogo…</p>
      </div>

      <div id="estado-vazio" class="estado" hidden>
        <p class="estado-icone" aria-hidden="true">🕹️</p>
        <h2>Nenhum jogo por aqui ainda</h2>
        <p>Clique em <strong>Sincronizar catálogo</strong> para importar os jogos grátis da FreeToGame.</p>
      </div>

      <div id="estado-erro" class="estado" hidden>
        <p class="estado-icone" aria-hidden="true">⚠️</p>
        <h2>Não foi possível carregar o catálogo</h2>
        <p id="estado-erro-mensagem"></p>
      </div>

      <ul id="grade-jogos" class="grade" hidden></ul>
    </section>

  </main>

  <footer class="rodape">
    <div class="container">
      <p>
        Dados de <a href="https://www.freetogame.com/api-doc" target="_blank" rel="noopener">FreeToGame API</a>
        · Teste técnico <strong>PH CORE / Prohall</strong>
      </p>
    </div>
  </footer>

  <div id="aviso" class="aviso" role="status" aria-live="polite" hidden></div>

  <script src="assets/js/app.js"></script>
</body>
</html>
