<?php
require '../config/auth.php';
require_login();
require '../config/conexion.php';

$usuario_id = $_SESSION['usuario_id'];

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    header('Location: editar_denuncia.php?id=' . (isset($_GET['id']) ? intval($_GET['id']) : ''));
    exit;
}

if (!isset($_GET['id'])) {
    header('Location: dashboard.php');
    exit;
}

$id = intval($_GET['id']);
$stmt = $conn->prepare("SELECT * FROM denuncias WHERE id = ? AND usuario_id = ?");
$stmt->bind_param("ii", $id, $usuario_id);
$stmt->execute();
$res = $stmt->get_result();
if ($res->num_rows !== 1) {
    echo "Denuncia no encontrada o no tienes permiso.";
    exit;
}
$row = $res->fetch_assoc();
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Editar Denuncia - Denuncias Ambientales</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <style>
    body {
      font-family: 'Segoe UI', Arial, sans-serif;
      background: linear-gradient(135deg, #e8f9f3, #eaf4fb);
      margin: 0;
      color: #1f2937;
    }
    .navbar {
      background: #fff;
      display:flex;
      justify-content:space-between;
      align-items:center;
      padding:12px 28px;
      box-shadow:0 2px 5px rgba(0,0,0,0.08);
      position:sticky;
      top:0;
      z-index:100;
    }
    .logo { font-weight:700; color:#2c3e50; display:flex; align-items:center; }
    .logo span { margin-left:8px; }
    .right { display:flex; gap:12px; align-items:center; }

    .tabs { display:flex; justify-content:center; background:#f8f8f8; padding:10px; gap:16px; }
    .tabs a { text-decoration:none; padding:8px 14px; border-radius:20px; background:#f0f0f0; color:#333; font-size:14px; }
    .tabs a.active { background:#fff; font-weight:600; box-shadow:0 2px 5px rgba(0,0,0,0.06); }

    .container { max-width:900px; margin:28px auto; padding:20px; }

    .card {
      background: #fff;
      border-radius:12px;
      padding:22px;
      box-shadow:0 4px 18px rgba(16,24,40,0.04);
    }

    .card h2 { margin:0 0 12px; font-size:20px; color:#0f172a; display:flex; align-items:center; gap:8px; }

    form label { display:block; margin:12px 0 6px; font-weight:600; font-size:14px; }
    select, input[type="text"], textarea, input[type="file"] {
      width:95%; padding:10px 12px; border-radius:8px; border:1px solid #d1d5db; font-size:14px;
      background:#fff;
    }
    textarea { min-height:110px; width: 95%; }

    .form-row { display:flex; gap:12px; align-items:center; flex-wrap:wrap; }
    .img-preview { margin-top:10px; max-width:220px; border-radius:8px; box-shadow:0 2px 8px rgba(0,0,0,0.06); }

    .actions { margin-top:16px; display:flex; gap:12px; align-items:center; flex-wrap:wrap; }
    .btn-primary {
      background: linear-gradient(90deg,#10b981,#059669);
      color:#fff; border:none; padding:10px 14px; border-radius:10px; font-weight:700; cursor:pointer;
      box-shadow:0 6px 18px rgba(16,185,129,0.12);
    }
    .btn-secondary {
      background:#fff; color:#374151; border:1px solid #e5e7eb; padding:10px 14px; border-radius:10px; cursor:pointer;
    }
    .help { color:#6b7280; font-size:13px; margin-top:8px; }

    .small-badge { display:inline-block; padding:6px 10px; border-radius:999px; font-weight:700; font-size:13px; }
    .small-badge.info { background:#eef2ff; color:#3730a3; border:1px solid rgba(99,102,241,0.08); }

    @media (max-width:560px){
      .container{ padding:12px; margin:12px; }
      .img-preview{ max-width:100%; }
    }
  </style>
</head>
<body>
  <div class="navbar">
    <div class="logo">🛡️ <span>Denuncias Ambientales</span></div>
    <div class="right">
      <a href="dashboard.php" class="btn-secondary" style="text-decoration:none;padding:8px 12px;border-radius:8px;">← Volver al panel</a>
    </div>
  </div>

  <div class="container">
    <div class="card">
      <h2>✏️ Editar denuncia</h2>
      <p class="help">Modifica los campos que desees y presiona <strong>Actualizar</strong>. Si no subes nueva imagen, se mantiene la actual.</p>

      <!-- FORMULARIO: ahora envía al archivo backend separado -->
      <form id="formEditar" action="../public/editar_denuncia_action.php" method="POST" enctype="multipart/form-data" class="mt-4">
        <input type="hidden" name="id" value="<?php echo (int)$row['id']; ?>">

        <label for="tipo">Tipo</label>
        <select name="tipo" id="tipo" required>
          <option value="<?php echo htmlspecialchars($row['tipo']); ?>"><?php echo htmlspecialchars($row['tipo']); ?> (actual)</option>
          <option value="Incendio">Incendio</option>
          <option value="Mineria Ilegal">Minería ilegal</option>
          <option value="Contaminacion">Contaminación</option>
        </select>

        <label for="descripcion">Descripción</label>
        <textarea id="descripcion" name="descripcion" required
          placeholder="Ejemplo: Reporto un incendio en el bosque cercano..."><?php echo htmlspecialchars($row['descripcion']); ?></textarea>

        <label for="ubicacion">Ubicación</label>
        <input type="text" name="ubicacion" id="ubicacion" value="<?php echo htmlspecialchars($row['ubicacion']); ?>" required>

        <div style="margin-top:10px;">
          <label>Imagen actual</label>
          <div>
            <?php if ($row['imagen']) : ?>
              <img id="currentImage" src="../public/uploads/<?php echo htmlspecialchars($row['imagen']); ?>" alt="Imagen actual" class="img-preview">
            <?php else: ?>
              <div class="help">Sin imagen</div>
            <?php endif; ?>
          </div>
        </div>

        <label for="imagen" style="margin-top:12px;">Reemplazar imagen (opcional)</label>
        <input type="file" name="imagen" id="imagen" accept="image/*">
        <div id="previewWrap"></div>

        <div class="actions">
          <button type="submit" class="btn-primary">Actualizar</button>
          <a href="dashboard.php" class="btn-secondary" style="text-decoration:none;display:inline-flex;align-items:center;justify-content:center;">Cancelar</a>
          <div style="margin-left:auto;">
            <span class="small-badge info">ID: <?php echo (int)$row['id']; ?></span>
          </div>
        </div>

      </form>
    </div>
  </div>

  <script>
    window.UPLOADS_PATH = '../public/uploads';
  </script>

  <script src="../public/assets/js/editar_denuncia.js" defer></script>
</body>
</html>
