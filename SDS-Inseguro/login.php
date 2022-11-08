<?php
include_once "funciones.php";
# Simple validación
if (strlen($_POST["email"]) < 1 || strlen($_POST["pass"]) < 1) {
    header("Location: index.php?mensaje=Faltan completar datos.");
    exit("Faltan Datos");
}
else {
    $email = $_POST["email"];
    $pass = $_POST["pass"];
    $valor = hacerLogin($email, $pass);
    if ($valor == 4)
        {   
            header("Location: acceso_denegado.php");
        }
    else {
        if ($valor == 0) {
            # Se informa que no existe un usuario con la dirección ingresada
            #header("Location: index.php?mensaje=Usuario y/o contraseña incorrectos");
            header("Location: index.php?mensaje=Usuario incorrecto.");
        #} else{
            #if ($valor == 2) {
                #header("Location: index.php?mensaje=Límite de intentos alcanzado. Prueba de nuevo mas tarde");
            } else if ($valor == 3) {
                   header("Location: index.php?mensaje=Contraseña incorrecta.");
                } else {
                    #Todo bien. Iniciar sesión y redireccionar a la página
                    iniciarSesionDeUsuario();
                    header("Location: usuarios.php");
            }
        #} 
    }
}




