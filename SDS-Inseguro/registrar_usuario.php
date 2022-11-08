<?php
if (!isset($_POST["email"]) || !isset($_POST["pass1"]) || !isset($_POST["pass2"])) {
    header("Location: formulario_registro.php?mensaje=Faltan Datos.");
    exit;
}
include_once "funciones.php";
if ($_POST["pass1"] !== $_POST["pass2"]) {
    header("Location: formulario_registro.php?mensaje=Las contraseñas no coinciden.");
    exit;
}
registrarUsuario($_POST["email"], $_POST["pass1"]);
header("Location: formulario_login.php?mensaje=Usuario creado, ya puedes iniciar sesión.");
