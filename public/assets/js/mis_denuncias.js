const API_BASE = window.API_BASE || '..';
const ENDPOINT = `${API_BASE}/ver_mis_denuncias.php`;
const UPLOADS_BASE = `${API_BASE}/uploads`;

function safeText(value) {
  if (value === null || value === undefined) return '';
  return String(value);
}

function normalizeEstado(estado) {
  return safeText(estado).toLowerCase();
}

function cardClassPorTipo(tipo) {
  const t = safeText(tipo).toLowerCase();
  if (t.includes('contamin')) return 'card-contaminacion';
  if (t.includes('mineria') || t.includes('minería')) return 'card-mineria';
  if (t.includes('incendio')) return 'card-incendio';
  return '';
}

function badgePorEstado(estado) {
  const e = normalizeEstado(estado);
  const span = document.createElement('span');
  span.classList.add('badge');
  if (e === 'validado') {
    span.classList.add('validado');
    span.textContent = '✔ Validado';
  } else if (e === 'pendiente') {
    span.classList.add('pendiente');
    span.textContent = '⏳ Pendiente';
  } else if (e === 'rechazado') {
    span.classList.add('rechazado');
    span.textContent = '✖ Rechazado';
  } else {
    span.textContent = safeText(estado);
  }
  return span;
}

async function fetchDenuncias() {
  try {
    const resp = await fetch(ENDPOINT, { credentials: 'include' });
    if (!resp.ok) throw new Error(`HTTP ${resp.status}`);
    const data = await resp.json();
    return Array.isArray(data) ? data : [];
  } catch (err) {
    console.error('Error al obtener denuncias:', err);
    return [];
  }
}

function actualizarContadores(denuncias) {
  const total = denuncias.length;
  const pendientes = denuncias.filter(d => normalizeEstado(d.estado) === 'pendiente').length;
  const validados = denuncias.filter(d => normalizeEstado(d.estado) === 'validado').length;
  const rechazados = denuncias.filter(d => normalizeEstado(d.estado) === 'rechazado').length;

  document.getElementById('totalCount').textContent = total;
  document.getElementById('pendientesCount').textContent = pendientes;
  document.getElementById('validadosCount').textContent = validados;
  document.getElementById('rechazadosCount').textContent = rechazados;
}

function crearTarjeta(d) {
  const article = document.createElement('article');
  article.className = 'denuncia-card ' + cardClassPorTipo(d.tipo);

  // Cabecera: título + badge
  const header = document.createElement('div');
  header.style.display = 'flex';
  header.style.justifyContent = 'space-between';
  header.style.alignItems = 'center';

  const titleWrap = document.createElement('div');
  const h3 = document.createElement('h3');
  h3.textContent = safeText(d.tipo);
  titleWrap.appendChild(h3);

  const metaSmall = document.createElement('div');
  metaSmall.className = 'meta';
  metaSmall.textContent = `${safeText(d.autor || '')} • ${safeText(d.fecha || '')}`;
  titleWrap.appendChild(metaSmall);

  header.appendChild(titleWrap);

  const badge = badgePorEstado(d.estado);
  header.appendChild(badge);

  article.appendChild(header);

  // Descripción
  const pDesc = document.createElement('p');
  pDesc.textContent = safeText(d.descripcion);
  pDesc.style.marginTop = '8px';
  article.appendChild(pDesc);

  // Ubicación
  if (d.ubicacion) {
    const pUb = document.createElement('div');
    pUb.className = 'meta';
    pUb.textContent = `📍 ${safeText(d.ubicacion)}`;
    article.appendChild(pUb);
  }

  // Imagen (si existe)
  if (d.imagen) {
    const img = document.createElement('img');
    img.src = `${UPLOADS_BASE}/${encodeURIComponent(d.imagen)}`;
    img.alt = 'Evidencia';
    article.appendChild(img);
  }

  // Acciones (editar / eliminar)
  const actions = document.createElement('div');
  actions.style.marginTop = '10px';
  actions.style.fontSize = '13px';

  const editLink = document.createElement('a');
  editLink.href = `editar_denuncia.php?id=${encodeURIComponent(d.id)}`;
  editLink.textContent = '✏️ Editar';
  editLink.style.marginRight = '12px';

  const delLink = document.createElement('a');
  delLink.href = `${API_BASE}/eliminar_denuncia.php?id=${encodeURIComponent(d.id)}`;
  delLink.textContent = '🗑️ Eliminar';
  delLink.onclick = function (e) {
    return confirm('¿Seguro que deseas eliminar esta denuncia?');
  };

  actions.appendChild(editLink);
  actions.appendChild(delLink);
  article.appendChild(actions);

  // ID (pequeño)
  if (d.id) {
    const idFoot = document.createElement('div');
    idFoot.className = 'meta';
    idFoot.style.fontSize = '12px';
    idFoot.style.color = '#999';
    idFoot.style.marginTop = '8px';
    idFoot.textContent = `ID: ${safeText(d.id)}`;
    article.appendChild(idFoot);
  }

  return article;
}

async function renderizar() {
  const categoriaFilter = document.getElementById('categoriaFilter').value;
  const estadoFilter = document.getElementById('estadoFilter').value;
  const contenedor = document.getElementById('todasDenuncias');
  contenedor.innerHTML = ''; // limpiar

  const data = await fetchDenuncias();
  actualizarContadores(data);

  const filtrado = data.filter(d => {
    if (categoriaFilter && categoriaFilter !== 'Todos') {
      if (!d.tipo || d.tipo.toLowerCase() !== categoriaFilter.toLowerCase()) return false;
    }
    if (estadoFilter && estadoFilter !== 'Todos') {
      if (!d.estado || d.estado.toLowerCase() !== estadoFilter.toLowerCase()) return false;
    }
    return true;
  });

  if (filtrado.length === 0) {
    const empty = document.createElement('p');
    empty.className = 'empty';
    empty.innerHTML = 'No se encontraron denuncias<br><small>Aún no has creado ninguna denuncia.</small>';
    contenedor.appendChild(empty);
    return;
  }

  filtrado.forEach(d => {
    const card = crearTarjeta(d);
    contenedor.appendChild(card);
  });
}

function initMisDenuncias() {
  document.getElementById('refreshBtn').addEventListener('click', renderizar);
  document.getElementById('categoriaFilter').addEventListener('change', renderizar);
  document.getElementById('estadoFilter').addEventListener('change', renderizar);

  renderizar();
}


if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initMisDenuncias);
} else {
  initMisDenuncias();
}
