<?php
header('Content-Type: application/json');
require '../config/auth.php'; require_login();
require '../config/conexion.php';

function _is_admin($conn){
  if (!isset($_SESSION['usuario_id'])) return false;
  $uid = (int)$_SESSION['usuario_id'];
  $stmt = $conn->prepare("SELECT * FROM usuarios WHERE id=? LIMIT 1");
  $stmt->bind_param('i', $uid); $stmt->execute();
  $res = $stmt->get_result();
  if ($row = $res->fetch_assoc()) {
    if (isset($row['is_admin']) && ($row['is_admin'] == 1 || $row['is_admin'] === '1')) return true;
    $rol = strtolower(trim($row['rol'] ?? ''));
    if (in_array($rol, ['admin','administrador','system','sistema'])) return true;
  }
  return false;
}
if (!_is_admin($conn)) { http_response_code(403); echo json_encode(['error'=>'forbidden']); exit; }

$raw = file_get_contents('php://input');
$in = json_decode($raw, true);
if (!$in) $in = $_POST;

$id = isset($in['id']) ? (int)$in['id'] : 0;
$estado = isset($in['estado']) ? trim($in['estado']) : '';

if (!$id || !in_array($estado, ['Pendiente','Validado','Rechazado'])) {
  http_response_code(400); echo json_encode(['error'=>'params']); exit;
}

$stmt = $conn->prepare("UPDATE denuncias SET estado=? WHERE id=?");
$stmt->bind_param('si', $estado, $id);
if ($stmt->execute()) {
  echo json_encode(['ok'=>true]);
} else {
  http_response_code(500); echo json_encode(['error'=>'db']);
}
