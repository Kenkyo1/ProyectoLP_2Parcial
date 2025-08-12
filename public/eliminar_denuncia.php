<?php
require '../config/auth.php';
require_login();
require '../config/conexion.php';

$usuario_id = $_SESSION['usuario_id'];

if (!isset($_GET['id'])) {
    header('Location: dashboard.php');
    exit;
}

$id = intval($_GET['id']);
$stmt = $conn->prepare("SELECT imagen FROM denuncias WHERE id = ? AND usuario_id = ?");
$stmt->bind_param("ii", $id, $usuario_id);
$stmt->execute();
$res = $stmt->get_result();
if ($res->num_rows !== 1) {
    echo "Denuncia no encontrada o sin permiso.";
    exit;
}
$row = $res->fetch_assoc();
if ($row['imagen'] && file_exists(__DIR__ . '/uploads/' . $row['imagen'])) {
    @unlink(__DIR__ . '/uploads/' . $row['imagen']);
}

$del = $conn->prepare("DELETE FROM denuncias WHERE id = ? AND usuario_id = ?");
$del->bind_param("ii", $id, $usuario_id);
$del->execute();
header('Location: dashboard.php');
exit;
?>