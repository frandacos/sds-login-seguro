<?php


function iniciarSesionDeUsuario()
{
    iniciarSesionSiNoEstaIniciada();
    $_SESSION["logueado"] = true;
}
function cerrarSesion()
{
    iniciarSesionSiNoEstaIniciada();
    session_destroy();
}
function usuarioEstaLogueado()
{
    iniciarSesionSiNoEstaIniciada();
    return isset($_SESSION["logueado"]);
}
function obtenerUsuariosConIntentosFallidos()
{
    $bd = obtenerBaseDeDatos();
    $sentencia = $bd->query("SELECT usuarios.id, usuarios.email, (SELECT COUNT(*) FROM intentos_usuarios WHERE id_usuario = usuarios.id) intentos_usuarios FROM usuarios");
    return $sentencia->fetchAll();
}

function iniciarSesionSiNoEstaIniciada()
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
}

function registrarUsuario($email, $pass)
{
    $bd = obtenerBaseDeDatos();
    $sentencia = $bd->prepare("INSERT INTO usuarios(email, pass) VALUES (?, ?)");
    $sentencia->execute([$email, $pass]);
}

function eliminarIntentos($idUsuario)
{
    $bd = obtenerBaseDeDatos();
    $ip = obtenerDireccionIP();
    $sentencia = $bd->prepare("DELETE FROM intentos_usuarios WHERE id_usuario = ? AND ip = '. $ip .' ");
    $sentencia->execute([$idUsuario]);
}

function insertarPeticionesIp() {
    $bd = obtenerBaseDeDatos();
    $ip = obtenerDireccionIP();
    $sentencia = $bd->prepare("INSERT INTO peticiones_ip(ip) VALUES ('. $ip .')");
    $sentencia->execute();
}

function eliminarPeticionesIp() {
    $bd = obtenerBaseDeDatos();
    $ip = obtenerDireccionIP();
    $sentencia = $bd->prepare("DELETE FROM peticiones_ip WHERE ip = '. $ip .' ");
    $sentencia->execute();
}

function obtenerCantidadPeticionesIp() {
    $bd = obtenerBaseDeDatos();
    $ip = obtenerDireccionIP();
    $sentencia = $bd->prepare("SELECT COUNT(*) AS conteo FROM peticiones_ip WHERE ip = '. $ip .'");
    $sentencia->execute();
    $registro = $sentencia->fetchObject();
    $conteo = $registro->conteo;
    return $conteo;
}

/*
Regresa valores numéricos
4 en caso de que una IP haya superado el numero maximo de peticiones
0 en caso de que el usuario no exista
1 en caso de que todo esté bien
3 en caso de que la contraseña sea incorrecta (usuario correcto)
*/
function hacerLogin($email, $pass)
{
    $bd = obtenerBaseDeDatos();
    $sentencia = $bd->prepare("SELECT id, email, pass FROM usuarios WHERE email = ?");
    $sentencia->execute([$email]);
    $registro = $sentencia->fetchObject();
    $obtenerCantidadPeticionesIp = obtenerCantidadPeticionesIp();
    if ($registro == null) {

        return 0;
    }
    else 
    {
        $palabraSecretaCorrecta = $registro->pass;
        if ($palabraSecretaCorrecta === $pass) {
            return 1;
        }
        else 
        {
            return 3;
        }
    }
}   


function obtenerConteoIntentosFallidos($idUsuario)
{
    $bd = obtenerBaseDeDatos();
    $ip = obtenerDireccionIP();
    $sentencia = $bd->prepare("SELECT COUNT(*) AS conteo FROM intentos_usuarios WHERE id_usuario = ? AND ip = '. $ip .' ");
    $sentencia->execute([$idUsuario]);
    $registro = $sentencia->fetchObject();
    $conteo = $registro->conteo;
    return $conteo;
}

function agregarIntentoFallido($idUsuario)
{
    $bd = obtenerBaseDeDatos();
    $ip = obtenerDireccionIP();
    $sentencia = $bd->prepare("INSERT INTO intentos_usuarios(id_usuario, ip) VALUES (?,'. $ip .')");
    $sentencia->execute([$idUsuario]);
}

function obtenerBaseDeDatos()
{
    //$pass = 'VaipG##mK6HWUTuv';
    //$user = 'id19826112_prueba1_user';
    //$dbName = 'id19826112_prueba1';
    //$servername = 'localhost';

    $pass = obtenerVariableDelEntorno("MYSQL_PASSWORD");
    $user = obtenerVariableDelEntorno("MYSQL_USER");
    $dbName = obtenerVariableDelEntorno("MYSQL_DATABASE_NAME");
    $servername = "localhost";
    try {
        $database = new PDO('mysql:host='. $servername. ';dbname=' . $dbName, $user, $pass);
        $database->query("set names utf8;");
        $database->setAttribute(PDO::ATTR_EMULATE_PREPARES, FALSE);
        $database->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $database->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
    }
    catch (PDOException $e) {
        print "¡Error!: " . $e->getMessage() . "<br/>";
        die();
    }
    return $database;
}

function obtenerVariableDelEntorno($clave)
{

    if (defined("_ENV_CACHE")) {
        $vars = _ENV_CACHE;
    } else {
        $archivo = "bd/env.php";
        if (!file_exists($archivo)) {
            throw new Exception("El archivo de las variables de entorno ($archivo) no existe. Favor de crearlo");
        }
        $vars = parse_ini_file($archivo);
        define("_ENV_CACHE", $vars);
    }
    if (isset($vars[$clave])) {
        return $vars[$clave];
    } else {
        throw new Exception("La clave especificada (" . $clave . ") no existe en el archivo de las variables de entorno");
    }
}

function obtenerDireccionIP()
{
    if (!empty($_SERVER ['HTTP_CLIENT_IP'] ))
      $ip=$_SERVER ['HTTP_CLIENT_IP'];
    elseif (!empty($_SERVER ['HTTP_X_FORWARDED_FOR'] ))
      $ip=$_SERVER ['HTTP_X_FORWARDED_FOR'];
    else
      $ip=$_SERVER ['REMOTE_ADDR'];

    return $ip;
}

