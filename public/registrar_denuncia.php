<?php
require '../config/auth.php';
require_login();
require '../config/conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario_id = $_SESSION['usuario_id'];
    $tipo = trim($_POST['tipo']);
    $descripcion = trim($_POST['descripcion']);
    $ubicacion = trim($_POST['ubicacion']);
    $imagen = null;

    if (empty($tipo) || empty($descripcion) || empty($ubicacion)) {
        echo "Campos obligatorios faltantes.";
        exit;
    }

    // Manejo de archivo
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
        $allowed = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($_FILES['imagen']['type'], $allowed)) {
            echo "Tipo de imagen no permitido.";
            exit;
        }
        if ($_FILES['imagen']['size'] > 2 * 1024 * 1024) {
            echo "Imagen demasiado grande (max 2MB).";
            exit;
        }
        $ext = pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION);
        $nombreImg = time() . '_' . bin2hex(random_bytes(5)) . '.' . $ext;
        $dest = __DIR__ . '/uploads/' . $nombreImg;
        if (!move_uploaded_file($_FILES['imagen']['tmp_name'], $dest)) {
            echo "Error al guardar la imagen.";
            exit;
        }
        $imagen = $nombreImg;
    }

    $stmt = $conn->prepare("INSERT INTO denuncias (usuario_id, tipo, descripcion, ubicacion, imagen) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("issss", $usuario_id, $tipo, $descripcion, $ubicacion, $imagen);

    if ($stmt->execute()) {
        header('Location: ../views/dashboard.php');
        exit;
    } else {
        echo "Error al registrar denuncia: " . htmlspecialchars($conn->error);
    }
} else {
    header('Location: ../views/dashboard.php');
    exit;
}
?>