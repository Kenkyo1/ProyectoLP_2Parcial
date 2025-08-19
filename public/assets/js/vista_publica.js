const API_BASE = window.API_BASE || '..';
const ENDPOINT = `${API_BASE}/ver_denuncias.php`;
const UPLOADS = `${API_BASE}/uploads`;

function safeText(v){ return v == null ? '' : String(v); }
function normalize(v){ return safeText(v).toLowerCase(); }

function cardClassByType(tipo){
  const t = normalize(tipo);
  if (t.includes('contamin')) return 'card-contaminacion';
  if (t.includes('mineria') || t.includes('minería')) return 'card-mineria';
  if (t.includes('incendio')) return 'card-incendio';
  return '';
}

function badgeByState(estado){
  const e = normalize(estado);
  const span = document.createElement('span');
  span.className = 'badge';
  if (e === 'validado') { span.classList.add('validado'); span.textContent = '✔ Validado'; }
  else if (e === 'pendiente') { span.classList.add('pendiente'); span.textContent = '⏳ Pendiente'; }
  else if (e === 'rechazado') { span.classList.add('rechazado'); span.textContent = '✖ Rechazado'; }
  else { span.textContent = safeText(estado); }
  return span;
}

async function fetchDenuncias(){
  try {
    const res = await fetch(ENDPOINT, { credentials: 'include' });
    if (!res.ok) throw new Error(`HTTP ${res.status}`);
    const json = await res.json();
    return Array.isArray(json) ? json : [];
  } catch (err) {
    console.error('Error fetch ver_denuncias:', err);
    return [];
  }
}

function updateCounters(all){
  document.getElementById('totalCount').textContent = all.length;
  document.getElementById('validadosCount').textContent = all.filter(d => normalize(d.estado) === 'validado').length;
  document.getElementById('pendientesCount').textContent = all.filter(d => normalize(d.estado) === 'pendiente').length;
  document.getElementById('rechazadosCount').textContent = all.filter(d => normalize(d.estado) === 'rechazado').length;
}

function createCard(d){
  const card = document.createElement('article');
  card.className = 'denuncia-card ' + cardClassByType(d.tipo);

  // header
  const header = document.createElement('div');
  header.style.display = 'flex';
  header.style.justifyContent = 'space-between';
  header.style.alignItems = 'center';

  const left = document.createElement('div');
  const h3 = document.createElement('h3'); h3.textContent = safeText(d.tipo);
  left.appendChild(h3);
  const meta = document.createElement('small'); meta.className = 'meta'; meta.textContent = `${safeText(d.ubicacion || '')} • ${safeText(d.fecha || '')}`;
  left.appendChild(meta);

  header.appendChild(left);
  header.appendChild(badgeByState(d.estado));
  card.appendChild(header);

  // descripcion
  const p = document.createElement('p'); p.style.marginTop = '8px'; p.textContent = safeText(d.descripcion);
  card.appendChild(p);

  // autor
  if (d.autor) {
    const autor = document.createElement('div'); autor.className = 'meta'; autor.style.marginTop = '8px'; autor.textContent = `Por: ${safeText(d.autor)}`;
    card.appendChild(autor);
  }

  // imagen
  if (d.imagen) {
    const img = document.createElement('img');
    img.src = `${UPLOADS}/${encodeURIComponent(d.imagen)}`;
    img.alt = 'Evidencia';
    card.appendChild(img);
  }

  return card;
}

async function render(){
  const cat = document.getElementById('categoriaFilter').value;
  const estado = document.getElementById('estadoFilter').value;
  const container = document.getElementById('publicDenuncias');
  container.innerHTML = '';

  const all = await fetchDenuncias();
  updateCounters(all);

  const filtered = all.filter(d => {
    if (cat && cat !== 'Todos') {
      if (!d.tipo || d.tipo.toLowerCase() !== cat.toLowerCase()) return false;
    }
    if (estado && estado !== 'Todos') {
      if (!d.estado || d.estado.toLowerCase() !== estado.toLowerCase()) return false;
    }
    return true;
  });

  if (filtered.length === 0) {
    container.innerHTML = `<div class="empty">No hay denuncias registradas</div>`;
    return;
  }

  filtered.forEach(d => container.appendChild( createCard(d) ));
}

function init(){
  document.getElementById('refreshBtn').addEventListener('click', render);
  document.getElementById('categoriaFilter').addEventListener('change', render);
  document.getElementById('estadoFilter').addEventListener('change', render);
  render();
}

if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init);
else init();
