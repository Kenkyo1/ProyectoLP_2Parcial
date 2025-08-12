<?php
session_start();
require '../config/conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre']);
    $email = trim($_POST['email']);
    $password_raw = $_POST['password'];

    if (empty($nombre) || empty($email) || empty($password_raw)) {
        echo "Todos los campos son obligatorios.";
        exit;
    }

    $password = password_hash($password_raw, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO usuarios (nombre, email, password) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $nombre, $email, $password);

    try {
        if ($stmt->execute()) {
            $_SESSION['usuario_id'] = $stmt->insert_id;
            $_SESSION['usuario_nombre'] = $nombre;
            header("Location: ../views/registro.html?error=sin_errores");
            exit;
        }
    } catch (mysqli_sql_exception $e) {
        if ($e->getCode() === 1062) { 
            header("Location: ../views/registro.html?error=email_existe");
        } else {
            echo "Error al registrar usuario: " . htmlspecialchars($e->getMessage());
        }
        exit;
    }
}
?>