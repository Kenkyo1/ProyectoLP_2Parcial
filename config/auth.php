<?php
session_start();

function esta_logueado()
{
    return isset($_SESSION['usuario_id']);
}

function require_login()
{
    if (!esta_logueado()) {
        header('Location: ../views/login.html');
        exit;
    }
}
?>