<?php
session_start();
require '../config/conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        echo "Correo y contraseña son obligatorios.";
        exit;
    }

    $stmt = $conn->prepare("SELECT id, nombre, password FROM usuarios WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado && $resultado->num_rows === 1) {
        $usuario = $resultado->fetch_assoc();
        if (password_verify($password, $usuario['password'])) {
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['usuario_nombre'] = $usuario['nombre'];
            header("Location: dashboard.php");
            exit;
        } else {
            # echo "Contraseña incorrecta.";
            header("Location: ../views/login.html?error=password_incorrecta");
        }
    } else {
        # echo "Usuario no encontrado.";
        header("Location: ../views/login.html?error=usuario_no_encontrado");
    }
}
?>