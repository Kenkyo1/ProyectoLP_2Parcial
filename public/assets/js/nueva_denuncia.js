(function () {
  const API_BASE = window.API_BASE || '..';
  const ENDPOINT = `${API_BASE}/registrar_denuncia.php`;
  const form = document.getElementById('formDenuncia');
  const msgEl = document.getElementById('msg');

  function showMessage(text, success = true) {
    msgEl.textContent = text;
    msgEl.style.color = success ? '#059669' : '#c0262e';
  }

  async function handleSubmit(e) {
    e.preventDefault();
    msgEl.textContent = '';
    const formData = new FormData(form);

    if (!formData.get('tipo') || !formData.get('descripcion') || !formData.get('ubicacion')) {
      showMessage('Por favor completa todos los campos obligatorios.', false);
      return;
    }

    try {
      const resp = await fetch(ENDPOINT, {
        method: 'POST',
        body: formData,
        credentials: 'include'
      });

      if (!resp.ok) {
        let errText = `Error ${resp.status}`;
        try {
          const j = await resp.json();
          if (j && j.error) errText += `: ${j.error}`;
        } catch (_) {}
        showMessage(`❌ ${errText}`, false);
        return;
      }

      try {
        const json = await resp.json();
        if (json && json.success === false) {
          showMessage(json.message || '❌ Error al registrar denuncia', false);
          return;
        }
      } catch (_) {
      }

      showMessage('✅ Denuncia registrada correctamente', true);
      form.reset();
    } catch (err) {
      console.error('Error al enviar denuncia:', err);
      showMessage('⚠ No se pudo conectar con el servidor', false);
    }
  }

  if (form) {
    form.addEventListener('submit', handleSubmit);
  } else {
    console.warn('Formulario #formDenuncia no encontrado en la página.');
  }
})();
