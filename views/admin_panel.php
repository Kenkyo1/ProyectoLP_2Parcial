<?php
require '../config/auth.php';
require_login();
$usuario_id = $_SESSION['usuario_id'];
$usuario_nombre = $_SESSION['usuario_nombre'] ?? '';

function is_admin_strict() {
  if (!isset($_SESSION['usuario_id'])) return false;
  $uid = (int)$_SESSION['usuario_id'];
  $candidates = [
    __DIR__ . '/../config/conexion.php',
    dirname(__DIR__) . '/config/conexion.php',
    __DIR__ . '/../../config/conexion.php'
  ];
  $ok = false;
  foreach ($candidates as $p) { if (file_exists($p)) { require_once $p; $ok = true; break; } }
  if (!$ok || !isset($conn)) return false;

  $stmt = $conn->prepare("SELECT * FROM usuarios WHERE id=? LIMIT 1");
  $stmt->bind_param('i', $uid);
  $stmt->execute();
  $res = $stmt->get_result();
  if ($row = $res->fetch_assoc()) {
    if (isset($row['is_admin']) && ($row['is_admin'] == 1 || $row['is_admin'] === '1')) return true;
    $rol = strtolower(trim($row['rol'] ?? ''));
    if ($rol && in_array($rol, ['admin','administrador','system','sistema'])) return true;
  }
  return false;
}

if (!is_admin_strict()) {
  http_response_code(403);
  echo "Acceso denegado";
  exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Panel Admin - Denuncias</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link rel="stylesheet" href="../public/assets/css/lightbox.css">
  <style>
    body { font-family: 'Segoe UI', Arial, sans-serif; background: linear-gradient(135deg, #e8f9f3, #eaf4fb); margin:0; }
    .navbar { background:#fff; display:flex; justify-content:space-between; align-items:center; padding:12px 40px; box-shadow:0 2px 5px rgba(0,0,0,0.1); position:sticky; top:0; z-index:100; }
    .navbar .logo { font-weight:bold; font-size:18px; color:#2c3e50; display:flex; align-items:center; }
    .navbar .logo span { margin-left:8px; }
    .navbar .right { display:flex; align-items:center; gap:15px; font-size:14px; }
    .navbar .logout { background:none; border:1px solid #ccc; padding:6px 12px; border-radius:20px; cursor:pointer; }

    .tabs { display:flex; justify-content:center; background:#f8f8f8; padding:10px; gap:20px; }
    .tabs a { text-decoration:none; padding:8px 16px; border-radius:20px; color:#333; font-size:14px; background:#f0f0f0; }
    .tabs a.active { background:#fff; font-weight:bold; box-shadow:0 2px 5px rgba(0,0,0,0.1); }

    .container { max-width:1100px; margin:30px auto; padding:0 20px; }

    .section { background:#fff; border-radius:12px; padding:25px; margin-bottom:25px; box-shadow:0 3px 8px rgba(0,0,0,0.05); }
    .section h2 { font-size:18px; margin:0 0 15px; color:#2c3e50; display:flex; align-items:center; gap:8px; }

    .filters { display:flex; gap:12px; margin-bottom:12px; flex-wrap:wrap; align-items:center; }
    select { padding:8px; border-radius:8px; border:1px solid #ccc; }
    .btn-refresh { display:inline-flex; align-items:center; gap:8px; background:linear-gradient(90deg,#10b981,#059669); color:#fff; border:none; padding:8px 12px; border-radius:10px; cursor:pointer; box-shadow:0 4px 10px rgba(16,185,129,0.15); font-weight:600; }
    .btn-refresh:active { transform: translateY(1px); }

    .stats { display:flex; gap:20px; margin-top:10px; }
    .stat { flex:1; background:#f9fbff; padding:18px; border-radius:10px; text-align:center; font-weight:bold; font-size:16px; }
    .stat.total { color:#2980b9; } .stat.pendientes { color:#e67e22; } .stat.validados { color:#27ae60; } .stat.rechazados { color:#e74c3c; }

    .denuncias-list { display:grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap:20px; }
    .denuncia-card { background:#fafafa; border:1px solid #ddd; border-radius:12px; padding:15px; box-shadow:0 1px 3px rgba(0,0,0,0.06); display:flex; flex-direction:column; }
    .denuncia-card h3 { margin:0; font-size:16px; }
    .denuncia-card .meta { color:#555; margin:6px 0; font-size:13px; }
    .denuncia-card img { width:100%; border-radius:8px; margin-top:8px; object-fit:cover; max-height:160px; }

    .card-contaminacion { background: linear-gradient(180deg,#f0f8ff,#eef9ff); border-color:#cfefff; }
    .card-mineria { background: linear-gradient(180deg,#fff9ed,#fffbf0); border-color:#fde3a7; }
    .card-incendio { background: linear-gradient(180deg,#fff5f5,#fff7f7); border-color:#ffd6d6; }

    .badge { display:inline-block; padding:4px 8px; border-radius:999px; font-size:12px; font-weight:600; }
    .badge.validado { background:#e6ffef; color:#059669; border:1px solid rgba(5,150,105,0.12); }
    .badge.pendiente { background:#fff7e6; color:#b45309; border:1px solid rgba(180,83,9,0.08); }
    .badge.rechazado { background:#fff1f2; color:#c0262e; border:1px solid rgba(192,38,46,0.08); }

    .empty { text-align:center; color:#777; font-size:14px; padding:30px; }

    /* Botones acción en card */
    .card-actions { display:flex; gap:8px; margin-top:8px; }
    .action-btn { border:1px solid #ddd; background:#fff; border-radius:8px; padding:6px 10px; cursor:pointer; }
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
    <a href="admin_panel.php" class="active">👤 Panel Admin</a>
    <a href="tab_vista_publica.php">🌍 Vista Pública</a>
  </div>

  <div class="container">
    <div class="section">
      <h2>🗂️ Gestión de Denuncias</h2>
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
        <button id="refreshBtn" class="btn-refresh">↻ Actualizar</button>
      </div>
      <div class="stats" style="margin-top:14px;">
        <div class="stat total"><span id="totalCount">0</span><br><small style="font-weight:normal">Total</small></div>
        <div class="stat pendientes"><span id="pendientesCount">0</span><br><small style="font-weight:normal">Pendientes</small></div>
        <div class="stat validados"><span id="validadosCount">0</span><br><small style="font-weight:normal">Validados</small></div>
        <div class="stat rechazados"><span id="rechazadosCount">0</span><br><small style="font-weight:normal">Rechazados</small></div>
      </div>
    </div>

    <div class="section">
      <div id="listaDenuncias" class="denuncias-list">
        <p class="empty">Cargando…</p>
      </div>
    </div>

  </div>

  <script>
    window.API_BASE = "../public";
    window.USER_ID = <?php echo json_encode($usuario_id); ?>;
  </script>
  <script src="../public/assets/js/admin_panel.js" defer></script>
</body>
</html>
