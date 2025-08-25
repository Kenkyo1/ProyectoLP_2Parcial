<?php
require '../config/auth.php';
require_login();
$usuario_id = $_SESSION['usuario_id'];
$usuario_nombre = $_SESSION['usuario_nombre'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Nueva Denuncia - Denuncias Ambientales</title>
  <style>
    body {
      font-family: 'Segoe UI', Arial, sans-serif;
      background: linear-gradient(135deg, #e8f9f3, #eaf4fb);
      margin: 0;
    }
    /* NAVBAR */
    .navbar {
      background: #fff;
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 12px 40px;
      box-shadow: 0 2px 5px rgba(0,0,0,0.1);
      position: sticky;
      top: 0;
      z-index: 100;
    }
    .navbar .logo { font-weight: bold; font-size: 18px; color: #2c3e50; display: flex; align-items: center; }
    .navbar .logo span { margin-left: 8px; }
    .navbar .right { display: flex; align-items: center; gap: 15px; font-size: 14px; }
    .navbar .logout { background: none; border: 1px solid #ccc; padding: 6px 12px; border-radius: 20px; cursor: pointer; }

    /* TABS */
    .tabs { display: flex; justify-content: center; background: #f8f8f8; padding: 10px; gap: 20px; }
    .tabs a { text-decoration: none; padding: 8px 16px; border-radius: 20px; color: #333; font-size: 14px; background: #f0f0f0; }
    .tabs a.active { background: #fff; font-weight: bold; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }

    /* CONTENEDOR */
    .container { max-width: 700px; margin: 40px auto; padding: 20px; }

    /* FORMULARIO */
    .form-card { background: #fff; border-radius: 12px; padding: 30px; box-shadow: 0 3px 8px rgba(0,0,0,0.05); }
    .form-card h2 { font-size: 20px; margin-bottom: 20px; color: #2c3e50; }
    label { display: block; margin: 15px 0 5px; font-weight: 500; }
    input, textarea, select { width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ccc; font-size: 14px; margin-bottom: 10px; }
    textarea { resize: vertical; min-height: 80px; }
    button { width: 100%; padding: 12px; border: none; border-radius: 8px; background: #27ae60; color: white; font-size: 16px; cursor: pointer; margin-top: 10px; }
    button:hover { background: #219150; }
    .msg { text-align: center; margin-top: 10px; font-weight: bold; }
    .logout { display: inline-block; background: none; border: 1px solid #ccc; padding: 6px 12px; border-radius: 20px; cursor: pointer; color: #333; text-decoration: none; }
    .logout:hover { background: #f0f0f0; }
  </style>
</head>
<body>
  <!-- NAVBAR -->
  <div class="navbar">
    <div class="logo">🛡️ <span>Denuncias Ambientales</span></div>
    <div class="right">
      Hola, <?php echo htmlspecialchars($usuario_nombre); ?>
      <a href="../public/logout.php" class="logout">Cerrar Sesión</a>
    </div>
  </div>

  <!-- TABS -->
  <div class="tabs">
    <a href="dashboard.php">📄 Ver Denuncias</a>
    <a href="tab_nueva_denuncia.php" class="active">📍 Nueva Denuncia</a>
    <a href="admin_panel.php">👤 Panel Admin</a>
    <a href="tab_vista_publica.php">🌍 Vista Pública</a>
  </div>

  <!-- FORM -->
  <div class="container">
    <div class="form-card">
      <h2>📍 Registrar Nueva Denuncia</h2>

      <form id="formDenuncia" action="../public/registrar_denuncia.php" method="POST" enctype="multipart/form-data">
        <label for="tipo">Tipo de denuncia</label>
        <select name="tipo" required>
          <option value="">Seleccione...</option>
          <option value="Incendio">🔥 Incendio</option>
          <option value="Mineria Ilegal">⛏️ Minería Ilegal</option>
          <option value="Contaminacion">♻️ Contaminación</option>
        </select>

        <label for="descripcion">Descripción</label>
        <textarea name="descripcion" required></textarea>

        <label for="ubicacion">Ubicación</label>
        <input type="text" name="ubicacion" placeholder="Ej: Parque Metropolitano, Quito" required>

        <label for="imagen">Imagen (opcional)</label>
        <input type="file" name="imagen" accept="image/*">

        <button type="submit">Enviar denuncia</button>
      </form>
      <p class="msg" id="msg"></p>
    </div>
  </div>

  <script>
    window.API_BASE = "../public";
  </script>

  <script src="../public/assets/js/nueva_denuncia.js" defer></script>
</body>
</html>
