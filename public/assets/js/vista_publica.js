// vista_publica.js (versión robusta - reemplazar archivo entero)
const API_BASE = window.API_BASE || '..';
const ENDPOINT = `${API_BASE}/ver_denuncias.php`;
const UPLOADS = `${API_BASE}/uploads`;

function safeText(v){ return v == null ? '' : String(v); }
function normalize(v){ return safeText(v).trim().toLowerCase(); }

function cardClassByType(tipo){
  const t = normalize(tipo);
  if (t.includes('contamin')) return 'card-contaminacion';
  if (t.includes('mineria') || t.includes('minería')) return 'card-mineria';
  if (t.includes('incendio')) return 'card-incendio';
  return '';
}

async function fetchDenuncias(){
  try {
    const res = await fetch(ENDPOINT, { credentials: 'include' });
    if (!res.ok) throw new Error(`HTTP ${res.status}`);
    const json = await res.json();
    console.debug('fetchDenuncias: recibidos', Array.isArray(json) ? json.length : typeof json, json);
    return Array.isArray(json) ? json : [];
  } catch (err) {
    console.error('Error fetch ver_denuncias:', err);
    return [];
  }
}

function updateCounters(count){
  const el = document.getElementById('totalCount');
  if (el) el.textContent = String(count);
  else console.warn('updateCounters: elemento #totalCount no encontrado en DOM');
}

function createCard(d){
  const card = document.createElement('article');
  card.className = 'denuncia-card ' + cardClassByType(d.tipo);

  const header = document.createElement('div');
  header.style.display = 'flex';
  header.style.justifyContent = 'space-between';
  header.style.alignItems = 'center';

  const left = document.createElement('div');
  const h3 = document.createElement('h3'); h3.textContent = safeText(d.tipo);
  left.appendChild(h3);

  const meta = document.createElement('small');
  meta.className = 'meta';
  meta.textContent = `${safeText(d.ubicacion || '')} • ${safeText(d.fecha || '')}`;
  left.appendChild(meta);

  header.appendChild(left);
  card.appendChild(header);

  const p = document.createElement('p');
  p.style.marginTop = '8px';
  p.textContent = safeText(d.descripcion);
  card.appendChild(p);

  if (d.autor) {
    const autor = document.createElement('div');
    autor.className = 'meta';
    autor.style.marginTop = '8px';
    autor.textContent = `Por: ${safeText(d.autor)}`;
    card.appendChild(autor);
  }

  if (d.imagen) {
    const img = document.createElement('img');
    img.src = `${UPLOADS}/${encodeURIComponent(d.imagen)}`;
    img.alt = 'Evidencia';
    card.appendChild(img);
  }

  return card;
}

async function render(){
  try {
    const catEl = document.getElementById('categoriaFilter');
    const cat = catEl ? catEl.value : 'Todos';
    const container = document.getElementById('publicDenuncias');
    if (!container) {
      console.error('render(): no se encontró #publicDenuncias en el DOM');
      return;
    }
    container.innerHTML = '';

    const all = await fetchDenuncias();

    const validados = all.filter(d => {
      const e = normalize(d && d.estado ? d.estado : '');
      return e.includes('valid');
    });

    updateCounters(validados.length);

    const filtered = validados.filter(d => {
      if (cat && cat !== 'Todos') {
        if (!d.tipo || normalize(d.tipo) !== cat.toLowerCase().trim()) return false;
      }
      return true;
    });

    if (filtered.length === 0) {
      container.innerHTML = `<div class="empty">No hay denuncias registradas</div>`;
      return;
    }

    filtered.forEach(d => container.appendChild(createCard(d)));
  } catch (err) {
    console.error('Error en render():', err);
    const container = document.getElementById('publicDenuncias');
    if (container) container.innerHTML = `<div class="empty">Ocurrió un error al cargar las denuncias</div>`;
  }
}

function safeAddListener(idOrEl, event, handler){
  let el = null;
  if (typeof idOrEl === 'string') el = document.getElementById(idOrEl);
  else el = idOrEl;
  if (!el) {
    console.warn(`safeAddListener: elemento ${typeof idOrEl === 'string' ? '#' + idOrEl : idOrEl} no encontrado. Listener no añadido.`);
    return;
  }
  try {
    el.addEventListener(event, handler);
  } catch (err) {
    console.error('safeAddListener: error añadiendo listener a', el, err);
  }
}

function init(){
  try {
    safeAddListener('refreshBtn', 'click', render);
    safeAddListener('categoriaFilter', 'change', render);
    render();
  } catch (err) {
    console.error('init(): error inesperado', err);
  }
}

if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init);
else init();
