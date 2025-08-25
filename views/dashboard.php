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
  <title>Dashboard - Denuncias</title>
  <link rel="stylesheet" href="../public/assets/css/lightbox.css">
  <style>
    body {
      font-family: 'Segoe UI', Arial, sans-serif;
      background: linear-gradient(135deg, #e8f9f3, #eaf4fb);
      margin: 0;
    }
    /* NAVBAR SUPERIOR */
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

    /* TABS MENU */
    .tabs {
      display: flex;
      justify-content: center;
      background: #f8f8f8;
      padding: 10px;
      gap: 20px;
    }
    .tabs a {
      text-decoration: none;
      padding: 8px 16px;
      border-radius: 20px;
      color: #333;
      font-size: 14px;
      background: #f0f0f0;
    }
    .tabs a.active {
      background: #fff;
      font-weight: bold;
      box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }

    /* CONTENEDOR PRINCIPAL */
    .container {
      max-width: 1100px;
      margin: 30px auto;
      padding: 0 20px;
    }

    /* SECCION: MIS DENUNCIAS */
    .section {
      background: #fff;
      border-radius: 12px;
      padding: 25px;
      margin-bottom: 25px;
      box-shadow: 0 3px 8px rgba(0,0,0,0.05);
    }
    .section h2 {
      font-size: 18px;
      margin: 0 0 15px;
      color: #2c3e50;
      display: flex;
      align-items: center;
    }

    /* FILTROS + MÉTRICAS */
    .filters {
      display: flex;
      gap: 12px;
      margin-bottom: 12px;
      flex-wrap: wrap;
      align-items: center;
    }
    select {
      padding: 8px;
      border-radius: 8px;
      border: 1px solid #ccc;
    }

    /* Botón actualizar mejorado */
    .btn-refresh {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      background: linear-gradient(90deg,#10b981,#059669);
      color: #fff;
      border: none;
      padding: 8px 12px;
      border-radius: 10px;
      cursor: pointer;
      box-shadow: 0 4px 10px rgba(16,185,129,0.15);
      font-weight: 600;
    }
    .btn-refresh:active { transform: translateY(1px); }

    .stats {
      display: flex;
      gap: 20px;
      margin-top: 10px;
    }
    .stat {
      flex: 1;
      background: #f9fbff;
      padding: 18px;
      border-radius: 10px;
      text-align: center;
      font-weight: bold;  
      font-size: 16px;
    }
    .stat.total { color: #2980b9; }
    .stat.pendientes { color: #e67e22; }
    .stat.validados { color: #27ae60; }
    .stat.rechazados { color: #e74c3c; }

    /* DENUNCIAS */
    .denuncias-list {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
      gap: 20px;
    }
    .denuncia-card {
      background: #fafafa;
      border: 1px solid #ddd;
      border-radius: 12px;
      padding: 15px;
      box-shadow: 0 1px 3px rgba(0,0,0,0.06);
      display: flex;
      flex-direction: column;
    }
    .denuncia-card h3 { margin: 0; font-size: 16px; }
    .denuncia-card .meta { color: #555; margin: 6px 0; font-size: 13px; }
    .denuncia-card img { width: 100%; border-radius: 8px; margin-top: 8px; object-fit: cover; max-height: 160px; }

    /* COLORES POR CATEGORÍA */
    .card-contaminacion { background: linear-gradient(180deg,#f0f8ff,#eef9ff); border-color: #cfefff; }
    .card-mineria { background: linear-gradient(180deg,#fff9ed,#fffbf0); border-color: #fde3a7; }
    .card-incendio { background: linear-gradient(180deg,#fff5f5,#fff7f7); border-color: #ffd6d6; }

    /* BADGE de estado pequeño */
    .badge {
      display: inline-block;
      padding: 4px 8px;
      border-radius: 999px;
      font-size: 12px;
      font-weight: 600;
    }
    .badge.validado { background:#e6ffef; color:#059669; border:1px solid rgba(5,150,105,0.12); }
    .badge.pendiente { background:#fff7e6; color:#b45309; border:1px solid rgba(180,83,9,0.08); }
    .badge.rechazado { background:#fff1f2; color:#c0262e; border:1px solid rgba(192,38,46,0.08); }

    /* MENSAJES VACÍOS */
    .empty {
      text-align: center;
      color: #777;
      font-size: 14px;
      padding: 30px;
    }
  </style>
</head>
<body>
  <!-- NAVBAR -->
  <div class="navbar">
    <div class="logo">🛡️ <span>Denuncias Ambientales</span></div>
    <div class="right">
      Hola, <?php echo htmlspecialchars($usuario_nombre); ?>
      <a href="../public/logout.php"><button class="logout">Cerrar Sesión</button></a>
    </div>
  </div>

  <!-- TABS -->
  <div class="tabs">
    <a href="dashboard.php" class="active">📄 Ver Denuncias</a>
    <a href="tab_nueva_denuncia.php">📍 Nueva Denuncia</a>
    <a href="admin_panel.php">👤 Panel Admin</a>
    <a href="tab_vista_publica.php">🌍 Vista Pública</a>
  </div>

  <div class="container">
    <!-- MIS DENUNCIAS -->
    <div class="section">
      <h2>📄 Mis Denuncias</h2>
      <p>Gestiona y revisa tus denuncias ambientales</p>

      <div class="filters">
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
      </div>

      <div class="stats" style="margin-top:14px;">
        <div class="stat total"><span id="totalCount">0</span><br><small style="font-weight:normal">Total</small></div>
        <div class="stat pendientes"><span id="pendientesCount">0</span><br><small style="font-weight:normal">Pendientes</small></div>
        <div class="stat validados"><span id="validadosCount">0</span><br><small style="font-weight:normal">Validados</small></div>
        <div class="stat rechazados"><span id="rechazadosCount">0</span><br><small style="font-weight:normal">Rechazados</small></div>
      </div>
    </div>

    <!-- LISTADO -->
    <div class="section">
      <div id="todasDenuncias" class="denuncias-list">
        <p class="empty">No se encontraron denuncias<br><small>Aún no has creado ninguna denuncia.</small></p>
      </div>
    </div>
  </div>

  <script>
    window.API_BASE = "../public";
    window.USER_ID = <?php echo json_encode($usuario_id); ?>;
  </script>

  <script src="../public/assets/js/mis_denuncias.js" defer></script>
  <script src="../public/assets/js/lightbox.js" defer></script>
</body>
</html>
