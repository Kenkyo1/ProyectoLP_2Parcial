<?php
$host = "localhost";
$user = "root";
$pass = "";
$db = "denuncias_db";
$port = 3306;

$conn = new mysqli($host, $user, $pass, $db, $port);

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}
?>