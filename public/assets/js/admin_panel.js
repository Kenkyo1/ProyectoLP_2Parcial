document.addEventListener('DOMContentLoaded', () => {
  const API_BASE = window.API_BASE || '..';
  const EP_LIST_DEN = `${API_BASE}/ver_denuncias.php`; 
  const EP_UPD_EST = `${API_BASE}/denuncias_update_estado.php`; 

  const UPLOADS = `${API_BASE}/uploads`;

  const $ = (id) => document.getElementById(id);

  const categoriaFilter = $('categoriaFilter');
  const estadoFilter = $('estadoFilter');
  const refreshBtn = $('refreshBtn');
  const contLista = $('listaDenuncias');

  const totalCount = $('totalCount');
  const pendientesCount = $('pendientesCount');
  const validadosCount = $('validadosCount');
  const rechazadosCount = $('rechazadosCount');

  let allDenuncias = [];

  const safe = (v) => (v == null ? '' : String(v));
  const norm = (v) => safe(v).toLowerCase();

  function cardClassByType(tipo){
    const t = norm(tipo);
    if (t.includes('contamin')) return 'card-contaminacion';
    if (t.includes('mineria') || t.includes('minería')) return 'card-mineria';
    if (t.includes('incendio')) return 'card-incendio';
    return '';
  }
  function badgeByState(estado){
    const e = norm(estado);
    const span = document.createElement('span');
    span.className = 'badge';
    if (e === 'validado') { span.classList.add('validado'); span.textContent = '✔ Validado'; }
    else if (e === 'pendiente') { span.classList.add('pendiente'); span.textContent = '⏳ Pendiente'; }
    else if (e === 'rechazado') { span.classList.add('rechazado'); span.textContent = '✖ Rechazado'; }
    else { span.textContent = safe(estado); }
    return span;
  }

  function updateCounters(list){
    totalCount.textContent = list.length;
    pendientesCount.textContent = list.filter(d => norm(d.estado) === 'pendiente').length;
    validadosCount.textContent = list.filter(d => norm(d.estado) === 'validado').length;
    rechazadosCount.textContent = list.filter(d => norm(d.estado) === 'rechazado').length;
  }

  function createAdminCard(d){
    const card = document.createElement('article');
    card.className = 'denuncia-card ' + cardClassByType(d.tipo);
    card.dataset.id = d.id;

    // header
    const header = document.createElement('div');
    header.style.display = 'flex';
    header.style.justifyContent = 'space-between';
    header.style.alignItems = 'center';

    const left = document.createElement('div');
    const h3 = document.createElement('h3');
    h3.textContent = safe(d.tipo);
    left.appendChild(h3);

    const meta = document.createElement('div');
    meta.className = 'meta';
    meta.textContent = `${safe(d.autor || '')} • ${safe(d.fecha || '')} • 📍 ${safe(d.ubicacion || '')}`;
    left.appendChild(meta);

    header.appendChild(left);
    header.appendChild(badgeByState(d.estado));
    card.appendChild(header);

    // descripción
    if (d.descripcion) {
      const p = document.createElement('p');
      p.style.marginTop = '8px';
      p.textContent = safe(d.descripcion);
      card.appendChild(p);
    }

    // imagen
    if (d.imagen) {
      const img = document.createElement('img');
      img.src = `${UPLOADS}/${encodeURIComponent(d.imagen)}`;
      img.alt = 'Evidencia';
      card.appendChild(img);
    }

    // acciones admin
    if (norm(d.estado) === 'pendiente') {
        const actions = document.createElement('div');
        actions.className = 'card-actions';
        actions.innerHTML = `
        <button class="action-btn ok-btn" title="Validar">✔ Validar</button>
        <button class="action-btn rej-btn" title="Rechazar">✖ Rechazar</button>
        `;
        card.appendChild(actions);
    }

    // id
    const idFoot = document.createElement('div');
    idFoot.className = 'meta';
    idFoot.style.fontSize = '12px';
    idFoot.style.color = '#999';
    idFoot.style.marginTop = '8px';
    idFoot.textContent = `ID: ${safe(d.id)}`;
    card.appendChild(idFoot);

    return card;
  }

  async function loadDenuncias() {
    try {
      contLista.innerHTML = `<p class="empty">Cargando denuncias…</p>`;
      const res = await fetch(EP_LIST_DEN, { credentials: 'include' });
      if (!res.ok) throw new Error('HTTP ' + res.status);
      const json = await res.json();
      allDenuncias = Array.isArray(json) ? json : [];
      applyFiltersAndRender();
    } catch (e) {
      console.error(e);
      contLista.innerHTML = `<p class="empty">No se pudieron cargar las denuncias.</p>`;
    }
  }

  function applyFiltersAndRender(){
    const cat = categoriaFilter ? categoriaFilter.value : 'Todos';
    const est = estadoFilter ? estadoFilter.value : 'Todos';

    let filtered = allDenuncias.slice();
    if (cat && cat !== 'Todos') {
      filtered = filtered.filter(d => d.tipo && d.tipo.toLowerCase() === cat.toLowerCase());
    }
    if (est && est !== 'Todos') {
      filtered = filtered.filter(d => d.estado && d.estado.toLowerCase() === est.toLowerCase());
    }

    updateCounters(filtered);

    if (!filtered.length) {
      contLista.innerHTML = `<p class="empty">No se encontraron denuncias</p>`;
      return;
    }
    contLista.innerHTML = '';
    filtered.forEach(d => contLista.appendChild(createAdminCard(d)));
  }

  if (contLista) {
    contLista.addEventListener('click', async (e) => {
      const card = e.target.closest('.denuncia-card');
      if (!card) return;
      const id = card.dataset.id;

      if (e.target.closest('.ok-btn')) {
        if (!confirm('¿Marcar como VALIDADO?')) return
        await updateEstado(id, 'Validado');
        return;
      }
      if (e.target.closest('.rej-btn')) {
        if (!confirm('¿Marcar como RECHAZADO?')) return;
        await updateEstado(id, 'Rechazado');
        return;
      }
    });
  }

  async function updateEstado(id, estado) {
    try {
      const payload = { id, estado, admin_id: window.USER_ID || null };
      let res = await fetch(EP_UPD_EST, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        credentials: 'include',
        body: JSON.stringify(payload)
      });
      if (!res.ok) throw new Error('HTTP ' + res.status);
      await loadDenuncias();
      alert('Estado actualizado.');
    } catch (e) {
      console.error(e);
      alert('No se pudo actualizar el estado (verifica el endpoint y la columna "estado" en la tabla denuncias).');
    }
  }

  if (categoriaFilter) categoriaFilter.addEventListener('change', applyFiltersAndRender);
  if (estadoFilter) estadoFilter.addEventListener('change', applyFiltersAndRender);
  if (refreshBtn) refreshBtn.addEventListener('click', () => { loadDenuncias(); });

  (async function init(){
    await loadDenuncias();
  })();
});

