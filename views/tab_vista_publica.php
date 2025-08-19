<?php
require '../config/auth.php'; 
require_login();
$usuario_nombre = $_SESSION['usuario_nombre'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Vista Pública - Denuncias Ambientales</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <style>
    body { font-family: 'Segoe UI', Arial, sans-serif; background: linear-gradient(135deg, #e8f9f3, #eaf4fb); margin: 0; }
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
    .navbar .logo {
      font-weight: bold;
      font-size: 18px;
      color: #2c3e50;
      display: flex;
      align-items: center;
    }
    .navbar .logo span { margin-left: 8px; }
    .navbar .right { display: flex; align-items: center; gap: 15px; font-size: 14px; }
    .navbar .logout {
      background: none;
      border: 1px solid #ccc;
      padding: 6px 12px;
      border-radius: 20px;
      cursor: pointer;
    }

    /* TABS */
    .tabs { display:flex; justify-content:center; background:#f8f8f8; padding:10px; gap:20px; }
    .tabs a { text-decoration:none; padding:8px 16px; border-radius:20px; color:#333; font-size:14px; background:#f0f0f0; }
    .tabs a.active { background:#fff; font-weight:bold; box-shadow:0 2px 5px rgba(0,0,0,0.1); }

    /* CONTENEDOR */
    .container { max-width:1100px; margin:30px auto; padding:20px; }

    /* filtros/contadores */
    .controls { display:flex; flex-wrap:wrap; gap:12px; align-items:center; margin-bottom:18px; }
    select { padding:8px; border-radius:8px; border:1px solid #ccc; }
    .btn-refresh { display:inline-flex; align-items:center; gap:8px; background:linear-gradient(90deg,#10b981,#059669); color:#fff; border:none; padding:8px 12px; border-radius:10px; cursor:pointer; box-shadow:0 4px 10px rgba(16,185,129,0.12); font-weight:600; }
    .stats { display:flex; gap:12px; margin-top:8px; }
    .stat { flex:1; background:#fff; border-radius:10px; padding:12px; text-align:center; box-shadow:0 1px 3px rgba(0,0,0,0.05); }
    .stat small { display:block; color:#666; font-weight:normal; margin-top:6px; }

    /* grid/cards */
    .grid { display:grid; grid-template-columns:repeat(auto-fill, minmax(280px, 1fr)); gap:20px; }
    .denuncia-card { background:#fff; border-radius:12px; padding:16px; box-shadow:0 3px 8px rgba(0,0,0,0.05); display:flex; flex-direction:column; }
    .denuncia-card h3 { margin:0; font-size:18px; color:#1f2937; }
    .denuncia-card .meta { color:#555; margin-top:6px; font-size:13px; }
    .denuncia-card img { width:100%; margin-top:10px; border-radius:8px; object-fit:cover; max-height:180px; }

    /* colores por tipo */
    .card-contaminacion { background: linear-gradient(180deg,#f0f8ff,#eef9ff); border:1px solid #cfefff; }
    .card-mineria { background: linear-gradient(180deg,#fff9ed,#fffbf0); border:1px solid #fde3a7; }
    .card-incendio { background: linear-gradient(180deg,#fff5f5,#fff7f7); border:1px solid #ffd6d6; }

    /* badge */
    .badge { display:inline-block; padding:5px 10px; border-radius:999px; font-weight:700; font-size:12px; }
    .badge.validado { background:#e6ffef; color:#059669; border:1px solid rgba(5,150,105,0.12); }
    .badge.pendiente { background:#fff7e6; color:#b45309; border:1px solid rgba(180,83,9,0.08); }
    .badge.rechazado { background:#fff1f2; color:#c0262e; border:1px solid rgba(192,38,46,0.08); }

    .empty { text-align:center; color:#888; font-size:16px; padding:40px; }
  </style>
</head>
<body>
  <div class="navbar">
    <div class="logo">🛡️ <span>Denuncias Ambientales</span></div>
    <div class="right">
      <div>Hola, <?php echo htmlspecialchars($usuario_nombre); ?></div>
      <a href="../public/logout.php"><button class="logout">Cerrar Sesión</button></a>
    </div>
  </div>

  <div class="tabs">
    <a href="dashboard.php">📄 Ver Denuncias</a>
    <a href="tab_nueva_denuncia.php">📍 Nueva Denuncia</a>
    <a href="#">👤 Panel Admin</a>
    <a href="tab_vista_publica.php" class="active">🌍 Vista Pública</a>
  </div>

  <div class="container">
    <h2>🌍 Denuncias Públicas</h2>

    <div class="controls">
      <label>
        <select id="categoriaFilter">
          <option value="Todos">Todas las categorías</option>
          <option value="Contaminacion">Contaminación</option>
          <option value="Mineria Ilegal">Minería Ilegal</option>
          <option value="Incendio">Incendio</option>
        </select>
      </label>

      <label>
        <select id="estadoFilter">
          <option value="Todos">Todos los estados</option>
          <option value="Pendiente">Pendiente</option>
          <option value="Validado">Validado</option>
          <option value="Rechazado">Rechazado</option>
        </select>
      </label>

      <button id="refreshBtn" class="btn-refresh" title="Actualizar denuncias">↻ Actualizar</button>

      <div style="flex:1"></div>

      <div class="stats" style="max-width:520px;">
        <div class="stat"><strong id="totalCount">0</strong><small>Total</small></div>
        <div class="stat"><strong id="validadosCount">0</strong><small>Validados</small></div>
        <div class="stat"><strong id="pendientesCount">0</strong><small>Pendientes</small></div>
        <div class="stat"><strong id="rechazadosCount">0</strong><small>Rechazados</small></div>
      </div>
    </div>

    <div id="publicDenuncias" class="grid"></div>
  </div>

  <script>
    window.API_BASE = "../public";
  </script>

  <script src="../public/assets/js/vista_publica.js" defer></script>
</body>
</html>
