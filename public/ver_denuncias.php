<?php
header('Content-Type: application/json');
require '../config/conexion.php';

$sql = "SELECT d.*, u.nombre as autor FROM denuncias d JOIN usuarios u ON d.usuario_id = u.id ORDER BY d.fecha DESC";
$res = $conn->query($sql);

$rows = [];
while ($r = $res->fetch_assoc()) {
    $rows[] = $r;
}
echo json_encode($rows);
?>