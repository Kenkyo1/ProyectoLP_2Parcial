<?php
require '../config/auth.php';
require_login();
require '../config/conexion.php';

$usuario_id = $_SESSION['usuario_id'];

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
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
        <title>Editar denuncia</title>
    </head>

    <body>
        <h2>Editar denuncia</h2>
        <form action="editar_denuncia.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
            <label>Tipo<br>
                <select name="tipo" required>
                    <option value="<?php echo htmlspecialchars($row['tipo']); ?>">Mantener el tipo registrado</option>
                    <option value="Incendio">Incendio</option>
                    <option value="Mineria Ilegal">Minería ilegal</option>
                    <option value="Contaminacion">Contaminación</option>
                </select>
            </label><br>
            <label>Descripción<br><textarea name="descripcion" required><?php echo htmlspecialchars($row['descripcion']); ?></textarea></label><br>
            <label>Ubicación<br><input type="text" name="ubicacion" value="<?php echo htmlspecialchars($row['ubicacion']); ?>" required></label><br>
            <p>Imagen actual:<br><?php if ($row['imagen']) {
                                        echo "<img src='uploads/" . htmlspecialchars($row['imagen']) . "' width='180'>";
                                    } else {
                                        echo 'Sin imagen';
                                    } ?></p>
            <label>Reemplazar imagen (opcional)<br><input type="file" name="imagen" accept="image/*"></label><br>
            <button type="submit">Actualizar</button>
        </form>
        <p><a href="dashboard.php">Volver</a></p>
    </body>

    </html>
    <?php
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id']);
    $tipo = trim($_POST['tipo']);
    $descripcion = trim($_POST['descripcion']);
    $ubicacion = trim($_POST['ubicacion']);

    if (empty($tipo) || empty($descripcion) || empty($ubicacion)) {
        echo "Campos obligatorios faltantes.";
        exit;
    }

    // verify ownership
    $stmt = $conn->prepare("SELECT imagen FROM denuncias WHERE id = ? AND usuario_id = ?");
    $stmt->bind_param("ii", $id, $usuario_id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows !== 1) {
        echo "Denuncia no encontrada o sin permiso.";
        exit;
    }
    $row = $res->fetch_assoc();
    $imagen_actual = $row['imagen'];

    $imagen = $imagen_actual;
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION);
        $nombreImg = time() . '_' . bin2hex(random_bytes(5)) . '.' . $ext;
        $dest = __DIR__ . '/uploads/' . $nombreImg;
        if (move_uploaded_file($_FILES['imagen']['tmp_name'], $dest)) {
            // delete old image
            if ($imagen_actual && file_exists(__DIR__ . '/uploads/' . $imagen_actual)) {
                @unlink(__DIR__ . '/uploads/' . $imagen_actual);
            }
            $imagen = $nombreImg;
        } else {
            echo "Error al guardar la nueva imagen.";
            exit;
        }
    }

    $stmt2 = $conn->prepare("UPDATE denuncias SET tipo = ?, descripcion = ?, ubicacion = ?, imagen = ? WHERE id = ? AND usuario_id = ?");
    $stmt2->bind_param("ssssii", $tipo, $descripcion, $ubicacion, $imagen, $id, $usuario_id);
    if ($stmt2->execute()) {
        header('Location: dashboard.php');
        exit;
    } else {
        echo "Error al actualizar: " . htmlspecialchars($conn->error);
    }
}
?>