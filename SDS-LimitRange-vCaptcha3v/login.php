<?php
include_once "funciones.php";
# Simple validación
if (strlen($_POST["email"]) < 1 || strlen($_POST["pass"]) < 1) {
    header("Location: index.php?mensaje=Faltan completar datos.");
}
else {
    $email = $_POST["email"];
    $pass = $_POST["pass"];
    $token = $_POST["token"];

    $validarCaptcha = solicitudCaptcha($token);
    if ($validarCaptcha) {
        $valor = hacerLogin($email, $pass);
        switch ($valor) {
            case 0:
                header("Location: acceso_denegado.php");
                break;
            case 1:
                # Correo o contraseña incorrectos
                header("Location: index.php?mensaje=Usuario y/o contraseña incorrectos.");
                break;
            case 2:
                header("Location: index.php?mensaje=Límite de intentos alcanzado. Prueba de nuevo en 3 minutos");
                break;
            case 3:
                iniciarSesionDeUsuario();
                header("Location: usuarios.php");
                break;
            }
    }
    else {
        header("Location: index.php?mensaje=Error al validar Captcha.");
    }

   
}
?>




