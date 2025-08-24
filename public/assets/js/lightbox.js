// Visor flotante (lightbox) 
document.addEventListener("DOMContentLoaded", () => {
  // CREANDO DOM
  const overlay = document.createElement("div");
  overlay.id = "lightbox-overlay";
  overlay.innerHTML = `
    <div id="lightbox-content" role="dialog" aria-modal="true">
      <span id="lightbox-close" aria-label="Cerrar">&times;</span>
      <div class="lb-header" id="lb-header">IMAGEN 1 DE 1</div>
      <div class="lb-subtitle" id="lb-subtitle">IMAGEN ADJUNTA A LA DENUNCIA DE …</div>
      <div class="lb-media"><img id="lb-img" src="" alt=""></div>
    </div>
  `;
  document.body.appendChild(overlay);

  const closeBtn = overlay.querySelector("#lightbox-close");
  const header = overlay.querySelector("#lb-header");
  const subtitle = overlay.querySelector("#lb-subtitle");
  const img = overlay.querySelector("#lb-img");

  // ---------- Utilidades ----------
  // 1) Obtiene el TIPO DE CONTAMINACIÓN desde:
  //    a) el contenedor de la tarjeta: [data-tipo-contaminacion]
  //    b) el <body data-tipo-contaminacion="...">
  //    c) un <input type="hidden" name="tipo_contaminacion" value="...">
/*
  function getTipoContaminacion(fromEl) {
    // a) contenedor cercano
    const card = fromEl.closest("[data-tipo-contaminacion]");
    if (card && card.dataset.tipoContaminacion) return card.dataset.tipoContaminacion;

    // b) body
    const bodyTipo = document.body.dataset.tipoContaminacion;
    if (bodyTipo) return bodyTipo;

    // c) input hidden
    const hidden = document.querySelector('input[name="tipo_contaminacion"]');
    if (hidden && hidden.value) return hidden.value;

    return "CONTAMINACIÓN"; // fallback
  }
    */

  // 2) Calcula índice y total según data-group (si no hay, es 1 de 1)
  function getIndexAndTotal(clicked) {
    const group = clicked.dataset.group;
    if (!group) return { index: 1, total: 1 };

    const allInGroup = Array.from(document.querySelectorAll(`img[data-group="${group}"]`));
    const total = allInGroup.length || 1;
    const index = Math.max(1, allInGroup.indexOf(clicked) + 1);
    return { index, total };
  }

  // ABRIR LA VENTANA FLOTANTE
  document.body.addEventListener("click", (e) => {
    const target = e.target.closest("img");
    // Abre solo si la imagen está dentro de una tarjeta de denuncia o marcada como evidencia
    if (
      target &&
      (target.closest(".denuncia-card") ||
       target.classList.contains("imagen-evidencia") ||
       target.classList.contains("evidencia"))
    ) {
      e.preventDefault();

      const src = target.dataset.full || target.src;
      img.src = src;

      const { index, total } = getIndexAndTotal(target);
      header.textContent = `IMAGEN ${index} DE ${total}`;

      //const tipo = getTipoContaminacion(target).toUpperCase();
      //subtitle.textContent = `IMAGEN ADJUNTA A LA DENUNCIA DE ${tipo}`;
      subtitle.textContent = `IMAGEN ADJUNTA A LA DENUNCIA AMBIENTAL`;
      overlay.style.display = "flex";
    }
  });

  // CERRAR LA VENTANA FLOTANTE
  closeBtn.addEventListener("click", () => (overlay.style.display = "none"));
  overlay.addEventListener("click", (e) => {
    if (e.target === overlay) overlay.style.display = "none";
  });
});
