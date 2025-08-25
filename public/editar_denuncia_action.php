<?php
require '../config/auth.php';
require_login();
require '../config/conexion.php';

$usuario_id = $_SESSION['usuario_id'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../views/dashboard.php');
    exit;
}

$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
$tipo = isset($_POST['tipo']) ? trim($_POST['tipo']) : '';
$descripcion = isset($_POST['descripcion']) ? trim($_POST['descripcion']) : '';
$ubicacion = isset($_POST['ubicacion']) ? trim($_POST['ubicacion']) : '';

if ($id <= 0 || empty($tipo) || empty($descripcion) || empty($ubicacion)) {
    echo "Campos obligatorios faltantes.";
    exit;
}

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
if (isset($_FILES['imagen']) && isset($_FILES['imagen']['error']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
    $allowed = ['image/jpeg', 'image/png', 'image/gif'];
    $mime = $_FILES['imagen']['type'] ?? '';
    if (!in_array($mime, $allowed)) {
        echo "Tipo de imagen no permitido.";
        exit;
    }
    if ($_FILES['imagen']['size'] > 2 * 1024 * 1024) {
        echo "Imagen demasiado grande (max 2MB).";
        exit;
    }
    $ext = pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION);
    try {
        $nombreImg = time() . '_' . bin2hex(random_bytes(5)) . '.' . $ext;
    } catch (Exception $e) {
        $nombreImg = time() . '_' . bin2hex(openssl_random_pseudo_bytes(5)) . '.' . $ext;
    }

    $uploadDir = __DIR__ . '/uploads/';
    if (!is_dir($uploadDir)) {
        @mkdir($uploadDir, 0755, true);
    }
    $dest = $uploadDir . $nombreImg;
    if (move_uploaded_file($_FILES['imagen']['tmp_name'], $dest)) {
        if ($imagen_actual && file_exists($uploadDir . $imagen_actual)) {
            @unlink($uploadDir . $imagen_actual);
        }
        $imagen = $nombreImg;
    } else {
        echo "Error al guardar la nueva imagen.";
        exit;
    }
}

$stmt2 = $conn->prepare("UPDATE denuncias SET tipo = ?, descripcion = ?, ubicacion = ?, imagen = ? WHERE id = ? AND usuario_id = ?");
if ($stmt2 === false) {
    echo "Error en la preparación de la consulta: " . htmlspecialchars($conn->error);
    exit;
}
$stmt2->bind_param("ssssii", $tipo, $descripcion, $ubicacion, $imagen, $id, $usuario_id);
if ($stmt2->execute()) {
    header('Location: ../views/dashboard.php');
    exit;
} else {
    echo "Error al actualizar: " . htmlspecialchars($conn->error);
    exit;
}
