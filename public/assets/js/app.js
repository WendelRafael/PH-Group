/* ============================================================
   PH Catálogo — lógica da página (JavaScript puro, sem libs)

   Fluxo:
     1. Ao abrir, busca os jogos SALVOS NO BANCO em api/games.php.
     2. "Sincronizar catálogo" → POST api/sync.php → importa da
        FreeToGame apenas os jogos novos → recarrega a lista.
     3. Busca e filtros (gênero/plataforma) acontecem no cliente,
        sem nova requisição.
   ============================================================ */

(function () {
  "use strict";

  // ---------- Estado ----------
  const estado = {
    jogos: [],        // catálogo completo vindo do banco
    busca: "",
    genero: "",
    plataforma: "",
  };

  // ---------- Elementos ----------
  const el = {
    total:        document.getElementById("contador-total"),
    sincronizar:  document.getElementById("botao-sincronizar"),
    busca:        document.getElementById("campo-busca"),
    genero:       document.getElementById("filtro-genero"),
    plataforma:   document.getElementById("filtro-plataforma"),
    resultado:    document.getElementById("filtros-resultado"),
    grade:        document.getElementById("grade-jogos"),
    carregando:   document.getElementById("estado-carregando"),
    vazio:        document.getElementById("estado-vazio"),
    erro:         document.getElementById("estado-erro"),
    erroMensagem: document.getElementById("estado-erro-mensagem"),
    aviso:        document.getElementById("aviso"),
  };

  // ---------- Estados visuais (um por vez) ----------
  function mostrarEstado(nome) {
    el.carregando.hidden = nome !== "carregando";
    el.vazio.hidden      = nome !== "vazio";
    el.erro.hidden       = nome !== "erro";
    el.grade.hidden      = nome !== "grade";
  }

  // ---------- Carregar jogos do banco ----------
  async function carregarJogos() {
    mostrarEstado("carregando");
    try {
      const resposta = await fetch("api/games.php");
      const dados = await resposta.json();
      if (!resposta.ok) throw new Error(dados.erro || "Erro ao consultar a API interna.");

      estado.jogos = dados.jogos;
      el.total.textContent = dados.total;
      preencherFiltros();
      renderizar();
    } catch (e) {
      el.erroMensagem.textContent = mensagemDeErro(e);
      mostrarEstado("erro");
    }
  }

  // Erros de rede/parse do navegador vêm em inglês ("Failed to fetch") —
  // traduzimos; mensagens da nossa API (dados.erro) já chegam em português.
  function mensagemDeErro(e) {
    if (e instanceof TypeError || e instanceof SyntaxError) {
      return "Não foi possível falar com o servidor. Verifique se ele está no ar e tente de novo.";
    }
    return e.message;
  }

  // ---------- Sincronizar com a FreeToGame ----------
  async function sincronizar() {
    el.sincronizar.disabled = true;
    el.sincronizar.classList.add("sincronizando");
    try {
      const resposta = await fetch("api/sync.php", { method: "POST" });
      const dados = await resposta.json();
      if (!resposta.ok) throw new Error(dados.erro || "Erro ao sincronizar.");

      avisar(
        dados.inseridos > 0
          ? `Catálogo atualizado: ${dados.inseridos} jogo(s) novo(s).`
          : "Catálogo já estava em dia — nenhum jogo novo.",
        "sucesso"
      );
      await carregarJogos();
    } catch (e) {
      avisar(mensagemDeErro(e), "erro");
    } finally {
      el.sincronizar.disabled = false;
      el.sincronizar.classList.remove("sincronizando");
    }
  }

  // ---------- Filtros ----------
  function preencherFiltros() {
    preencherSelect(el.genero, valoresUnicos("genre"), estado.genero);
    preencherSelect(el.plataforma, valoresUnicos("platform"), estado.plataforma);
  }

  // A API às vezes combina valores num campo só ("PC (Windows), Web Browser"):
  // separamos por vírgula para o filtro enxergar cada um individualmente.
  function valoresDoJogo(texto) {
    return (texto || "").split(",").map((v) => v.trim()).filter(Boolean);
  }

  function valoresUnicos(campo) {
    return [...new Set(estado.jogos.flatMap((j) => valoresDoJogo(j[campo])))].sort();
  }

  function preencherSelect(select, valores, selecionado) {
    const primeira = select.options[0]; // opção "Todos(as)"
    select.replaceChildren(primeira);
    for (const valor of valores) {
      const opcao = new Option(valor, valor, false, valor === selecionado);
      select.add(opcao);
    }
  }

  function jogosFiltrados() {
    const termo = estado.busca.trim().toLowerCase();
    return estado.jogos.filter((jogo) => {
      if (estado.genero && !valoresDoJogo(jogo.genre).includes(estado.genero)) return false;
      if (estado.plataforma && !valoresDoJogo(jogo.platform).includes(estado.plataforma)) return false;
      if (!termo) return true;
      return [jogo.title, jogo.short_description, jogo.developer, jogo.publisher]
        .filter(Boolean)
        .some((texto) => texto.toLowerCase().includes(termo));
    });
  }

  // ---------- Renderização ----------
  function renderizar() {
    if (estado.jogos.length === 0) {
      el.resultado.textContent = "";
      mostrarEstado("vazio");
      return;
    }

    const lista = jogosFiltrados();
    el.resultado.textContent =
      lista.length === estado.jogos.length
        ? `Exibindo todos os ${lista.length} jogos.`
        : `${lista.length} jogo(s) encontrado(s).`;

    const fragmento = document.createDocumentFragment();
    for (const jogo of lista) fragmento.appendChild(criarCard(jogo));
    el.grade.replaceChildren(fragmento);
    mostrarEstado("grade");
  }

  function criarCard(jogo) {
    const item = document.createElement("li");
    item.className = "card";
    item.innerHTML = `
      <img class="card-capa" loading="lazy" alt="Capa de ${escapar(jogo.title)}"
           src="${escapar(jogo.thumbnail || "")}" />
      <div class="card-corpo">
        <div class="card-etiquetas">
          ${jogo.genre ? `<span class="etiqueta">${escapar(jogo.genre)}</span>` : ""}
          ${jogo.platform ? `<span class="etiqueta etiqueta-plataforma">${escapar(jogo.platform)}</span>` : ""}
        </div>
        <h2 class="card-titulo">${escapar(jogo.title)}</h2>
        <p class="card-descricao">${escapar(jogo.short_description || "")}</p>
        <p class="card-meta">${escapar(montarMeta(jogo))}</p>
        <div class="card-rodape">
          ${jogo.game_url ? `<a class="card-jogar" href="${escapar(jogo.game_url)}" target="_blank" rel="noopener">Jogar →</a>` : ""}
          ${jogo.freetogame_profile_url ? `<a class="card-perfil" href="${escapar(jogo.freetogame_profile_url)}" target="_blank" rel="noopener">ficha completa</a>` : ""}
        </div>
      </div>`;
    return item;
  }

  function montarMeta(jogo) {
    const partes = [];
    if (jogo.developer) partes.push(jogo.developer);
    if (jogo.release_date) {
      const [ano, mes, dia] = jogo.release_date.split("-");
      partes.push(`lançado em ${dia}/${mes}/${ano}`);
    }
    return partes.join(" · ");
  }

  // Sempre escapar dados vindos da API antes de inserir no HTML (anti-XSS).
  function escapar(texto) {
    const div = document.createElement("div");
    div.textContent = String(texto);
    return div.innerHTML.replaceAll('"', "&quot;");
  }

  // ---------- Aviso (toast) ----------
  let avisoTimer = null;
  function avisar(mensagem, tipo) {
    el.aviso.textContent = mensagem;
    el.aviso.className = `aviso ${tipo}`;
    el.aviso.hidden = false;
    clearTimeout(avisoTimer);
    avisoTimer = setTimeout(() => { el.aviso.hidden = true; }, 4500);
  }

  // ---------- Eventos ----------
  el.sincronizar.addEventListener("click", sincronizar);

  el.busca.addEventListener("input", () => {
    estado.busca = el.busca.value;
    renderizar();
  });

  el.genero.addEventListener("change", () => {
    estado.genero = el.genero.value;
    renderizar();
  });

  el.plataforma.addEventListener("change", () => {
    estado.plataforma = el.plataforma.value;
    renderizar();
  });

  // ---------- Início ----------
  carregarJogos();
})();
