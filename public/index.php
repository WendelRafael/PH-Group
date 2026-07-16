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

  <div class="atmosfera" aria-hidden="true"></div>

  <header class="topo">
    <div class="container topo-conteudo">
      <a class="marca" href="./" aria-label="PH Catálogo — início">
        <span class="marca-icone" aria-hidden="true">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
            <path d="M6.6 8.2h10.8a4.4 4.4 0 0 1 4.3 5.35l-.75 3.3A2.55 2.55 0 0 1 17.9 18.8c-.9 0-1.72-.5-2.2-1.28L14.7 16H9.3l-1 1.52c-.48.78-1.3 1.28-2.2 1.28a2.55 2.55 0 0 1-2.5-2.05l-.75-3.3A4.4 4.4 0 0 1 6.6 8.2Z" fill="rgba(255,184,77,.12)"/>
            <path d="M7.2 11.4v3M5.7 12.9h3"/>
            <circle cx="15.6" cy="12" r=".95" fill="currentColor" stroke="none"/>
            <circle cx="17.5" cy="13.6" r=".95" fill="currentColor" stroke="none"/>
          </svg>
        </span>
        <div class="marca-textos">
          <strong>PH Catálogo</strong>
          <small>jogos grátis · FreeToGame</small>
        </div>
      </a>

      <div class="contador" aria-live="polite">
        <span class="contador-rotulo">Catálogo</span>
        <span class="contador-valor"><strong id="contador-total">—</strong> jogos</span>
      </div>

      <button type="button" id="botao-sincronizar" class="botao botao-primario">
        <svg class="botao-icone" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
          <polyline points="23 4 23 10 17 10"/>
          <path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/>
        </svg>
        Sincronizar catálogo
      </button>
    </div>
  </header>

  <main class="container">

    <section class="hero" aria-labelledby="titulo-pagina">
      <p class="eyebrow"><span class="tick" aria-hidden="true">▸</span> Catálogo · FreeToGame</p>
      <h1 id="titulo-pagina">Catálogo de <span class="destaque">jogos grátis</span></h1>
      <p class="hero-sub">Todos os jogos vêm da API FreeToGame e ficam guardados no banco —
        busque, filtre por gênero ou plataforma e sincronize quando quiser.</p>
    </section>

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
