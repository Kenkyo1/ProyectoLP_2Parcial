(function () {
  function safe(q) { return document.querySelector(q); }
  const form = safe('#formEditar');
  const descripcion = safe('#descripcion');
  const imagenInput = safe('#imagen');
  const previewWrap = safe('#previewWrap');
  const UPLOADS = window.UPLOADS_PATH || 'uploads';

  function showPreview(file) {
    previewWrap.innerHTML = '';
    if (!file) return;
    const img = document.createElement('img');
    img.className = 'img-preview';
    img.style.maxHeight = '160px';
    img.style.display = 'block';
    img.style.marginTop = '8px';
    img.src = URL.createObjectURL(file);
    img.onload = () => URL.revokeObjectURL(img.src);
    previewWrap.appendChild(img);
  }

  if (imagenInput) {
    imagenInput.addEventListener('change', function (e) {
      const f = e.target.files && e.target.files[0];
      if (!f) {
        previewWrap.innerHTML = '';
        return;
      }
      // validación cliente: tipo y tamaño
      const allowed = ['image/jpeg', 'image/png', 'image/gif'];
      if (!allowed.includes(f.type)) {
        alert('Tipo de imagen no permitido. Use JPG, PNG o GIF.');
        imagenInput.value = '';
        previewWrap.innerHTML = '';
        return;
      }
      if (f.size > 2 * 1024 * 1024) {
        alert('Imagen demasiado grande. Máx 2MB.');
        imagenInput.value = '';
        previewWrap.innerHTML = '';
        return;
      }
      showPreview(f);
    });
  }

  function validarDescripcion(text) {
    const regex = /^[A-Za-z0-9\s.,;:¡!¿?áéíóúÁÉÍÓÚñÑ()\-]{10,}$/;
    return regex.test(text.trim());
  }

  if (form) {
    form.addEventListener('submit', function (e) {
      const desc = descripcion ? descripcion.value : '';
      if (!validarDescripcion(desc)) {
        e.preventDefault();
        alert('La descripción debe tener al menos 10 caracteres y solo puede contener letras, números y signos básicos.');
        if (descripcion) descripcion.focus();
        return false;
      }
    });
  }
})();
