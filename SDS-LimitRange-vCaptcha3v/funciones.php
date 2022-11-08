<?php
# El número de intentos máximos que puede realizar una IP a un usuario existente
define("MAXIMOS_INTENTOS_IPxUSUARIO", 3);
# El número de intentos máximos que puede realizar una IP
define("MAXIMOS_INTENTOS_IP", 12);
# El tiempo (en segundos) que se kickea a una IP por alcanzar el MAXIMOS_INTENTOS_IP
define("TIEMPO_KICK_SEG", 300);
# El tiempo (en segundos) que se kickea a una IP para el usuario en cuestion, por alcanzar el MAXIMOS_INTENTOS_IPxUSUARIO
define("TIEMPO_KICK_IPxUSER_SEG", 180);
# Tiempo que controla el borrado de las peticiones, para no acumular siempre las mismas
define("TIEMPO_BORRADO_PETICIONES", 900);

#################################################
############ Manejo de sesion simple ############
#################################################

function iniciarSesionDeUsuario()
{
    iniciarSesionSiNoEstaIniciada();
    $_SESSION["logueado"] = true;
}

function cerrarSesion()
{
    //iniciarSesionSiNoEstaIniciada();
    session_destroy();
}

function usuarioEstaLogueado()
{
    iniciarSesionSiNoEstaIniciada();
    return isset($_SESSION["logueado"]);
}

function iniciarSesionSiNoEstaIniciada()
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
}

#################################################
############### Registro de usuarios ############
#################################################

function registrarUsuario($email, $pass)
{
    $bd = obtenerBaseDeDatos();
    $passHash = obtenerPassHash($pass);
    $sentencia = $bd->prepare("INSERT INTO usuarios(id, email, pass) VALUES (HEX(UUID_TO_BIN(UUID(), 1)) ,?, ?)");
    $sentencia->execute([$email, $passHash]);
}

function obtenerPassHash($pass) {
    $hash = password_hash($pass,PASSWORD_DEFAULT);
    return $hash;
}

################################################
############### Inicio de Sesion ###############
################################################

/** 
 * Regresa valores numéricos
 * 0 en caso de que una IP haya superado el numero maximo de peticiones
 * 1 en caso de que el usuario no exista o la contraseña sea incorrecta
 * 2 en caso de que haya alcanzado el límite de intentos
 * 3 en caso de que todo esté bien
 */
function hacerLogin($email, $pass)
{
    $bd = obtenerBaseDeDatos();
    $sentencia = $bd->prepare("SELECT id, email, pass FROM usuarios WHERE email = ?");
    $sentencia->execute([$email]);
    $registro = $sentencia->fetchObject();

    resetearPeticionesAutomaticas(); //Si existen peticiones antiguas guardadas las restartea para evitar contar
    determinarTiempo(); //Controla si se llego a los intentos maximos para una IP y si es asi determina el tiempo de kick o valida que el tiempo ya paso
    $obtenerCantidadPeticionesIp = obtenerCantidadPeticionesIp();
    # Compruebo que un determinado usuario no haya realizado un numero de peticiones mayores a la variable MAXIMOS_INTENTOS_IP
    if ($obtenerCantidadPeticionesIp >= MAXIMOS_INTENTOS_IP)
    {
        return 0;
    }
    else {
        if ($registro == null) {
            # No hay registros que coincidan, y no hay a quién culpar (porque el usuario no existe)
            # Guardo en la BD cada IP con la que se intenta iniciar sesion
            insertarPeticionesIp();
            return 1;

        } else {
            # Sí hay registros, pero no sabemos si ya ha alcanzado el límite de intentos o si la contraseña es correcta
            determinarTiempoXUsuario($registro->id);
            $conteoIntentosFallidos = obtenerConteoIntentosFallidos($registro->id);
            if ($conteoIntentosFallidos >= MAXIMOS_INTENTOS_IPxUSUARIO) {
                # Ha superado el límite por usuario, por ejemplo una determinada IP puede hacer en un determinado tiempo 10 peticiones
                # pero si intenta entrar a un determinado usuario mas de 3 veces (sin conseguirlo), ese usuario se bloquea para esa
                # determinada IP
                insertarPeticionesIp();
                return 2;
            }
            else {
                # Extraer la correcta de la base de datos
                $passwordHashada = $registro->pass;
                # Comparar con la proporcionada:
                $passwordCoincidiencia = password_verify($pass, $passwordHashada);
                if ($passwordCoincidiencia) {
                    # Todo correcto. Borramos todos los intentos registrados en la BD de un determinado usuario con una IP.
                    eliminarIntentos($registro->id);
                    //eliminarPeticionesIp(); Si eliminariamos todas las peticiones generariamos una vulnerabilidad ya que 
                    # el atacante con tener un usuario podria reiniciar el bloqueo de su IP. Por esta razon estas se tendrian que ir 
                    # eliminando en un determinado tiempo o bien permitir que pueda realizar ciertas cantidad de peticiones en un tiempo determinado
                    return 3;
                } else {
                    # Agregamos un intento fallido
                    agregarIntentoFallido($registro->id);
                    insertarPeticionesIp();
                    return 1;
                }
            }
        }   
    }
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

function obtenerBaseDeDatos()
{
    # www.000webhost.com
    //$pass = 'Ky=aF[%h}0X*87-G';
    //$user = 'id19819361_php_login_datebase3_user';
    //$dbName = 'id19819361_php_login_datebase3';
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

function oobtenerDireccionIP()
{
    if (!empty($_SERVER ['HTTP_CLIENT_IP'] ))
      $ip=$_SERVER ['HTTP_CLIENT_IP'];
    elseif (!empty($_SERVER ['HTTP_X_FORWARDED_FOR'] ))
      $ip=$_SERVER ['HTTP_X_FORWARDED_FOR'];
    else
      $ip=$_SERVER ['REMOTE_ADDR'];

    return $ip;
}

function obtenerDireccionIP()
{
    $ip = getenv('REMOTE_ADDR', true) ?: getenv('REMOTE_ADDR');
    return $ip;
}

function determinarTiempo() {
    $time = time();
    $bd = obtenerBaseDeDatos();
    $ip = obtenerDireccionIP();
    $intentos = obtenerCantidadPeticionesIp();
    if ($intentos >= MAXIMOS_INTENTOS_IP) {
        $sentencia = $bd->prepare("SELECT timer FROM peticiones_ip WHERE ip = '$ip'");
        $sentencia->execute();
        $registro = $sentencia->fetchObject();
        $timer = $registro->timer;
        if ($timer == 0 )
        {
            $expire = ($time + (TIEMPO_KICK_SEG * 1));
            $sentencia = $bd->prepare("UPDATE peticiones_ip  SET timer = ' $expire ', intentos = intentos WHERE ip = '$ip'");
            $sentencia->execute();
        }
        else {
            if ($timer < $time ) {
                $sentencia = $bd->prepare("UPDATE peticiones_ip  SET timer = 0 , intentos = 0 WHERE ip = '$ip'");
                $sentencia->execute();
            }
        }
    }
}

function obtenerCantidadPeticionesIp() {
    $bd = obtenerBaseDeDatos();
    $ip = obtenerDireccionIP();
    $sentencia = $bd->prepare("SELECT intentos AS conteo FROM peticiones_ip WHERE ip = '$ip'");
    $sentencia->execute();
    $registro = $sentencia->fetchObject();
    $conteo = $registro->conteo;
    if($conteo == 0) {
        $conteo = 0;
    }
    return $conteo;
}

function insertarPeticionesIp() {
    $time = time();
    $bd = obtenerBaseDeDatos();
    $ip = obtenerDireccionIP();
    $sentencia = $bd->prepare("SELECT COUNT(ip) AS cantidad FROM peticiones_ip WHERE ip = '$ip' ");
    $sentencia->execute();
    $registro = $sentencia->fetchObject();
    $cantidad = $registro->cantidad;
    if ($cantidad == 0) {
        $sentencia = $bd->prepare("INSERT INTO peticiones_ip(ip, timer, intentos, ultima_peticion) VALUES ('$ip', 0, 1, '$time')");
        $sentencia->execute();
    }
    else {
        $sentencia = $bd->prepare("UPDATE peticiones_ip  SET timer = 0, intentos = intentos + 1 , ultima_peticion = '$time' WHERE ip = '$ip'");
        $sentencia->execute();
    }
}

function obtenerConteoIntentosFallidos($idUsuario)
{
    $bd = obtenerBaseDeDatos();
    $ip = obtenerDireccionIP();
    $sentencia = $bd->prepare("SELECT intentos FROM intentos_usuarios WHERE id_usuario = ? AND ip = '$ip' ");
    $sentencia->execute([$idUsuario]);
    $registro = $sentencia->fetchObject();
    $intentos = $registro->intentos;
    return $intentos;
}

function eliminarIntentos($idUsuario)
{
    $bd = obtenerBaseDeDatos();
    $ip = obtenerDireccionIP();
    $sentencia = $bd->prepare("DELETE FROM intentos_usuarios WHERE id_usuario = ? AND ip = '$ip' ");
    $sentencia->execute([$idUsuario]);
}

function agregarIntentoFallido($idUsuario)
{
    $bd = obtenerBaseDeDatos();
    $ip = obtenerDireccionIP();
    $sentencia = $bd->prepare("SELECT COUNT(*) AS cantidad FROM intentos_usuarios WHERE ip = '$ip' AND id_usuario = ?");
    $sentencia->execute([$idUsuario]);
    $registro = $sentencia->fetchObject();
    $cantidad = $registro->cantidad;
    if ($cantidad == 0)
    {
        $sentencia = $bd->prepare("INSERT INTO intentos_usuarios(id_usuario, ip, intentos, timer) VALUES (?,'$ip', 1, 0)");
        $sentencia->execute([$idUsuario]);
    }
    else
    {
        $sentencia = $bd->prepare("UPDATE intentos_usuarios SET intentos = intentos + 1, timer = 0 WHERE id_usuario = ? AND ip = '$ip'");
        $sentencia->execute([$idUsuario]);
    }
}

function resetearPeticionesAutomaticas() {
    $time = time();
    $bd = obtenerBaseDeDatos();
    $ip = obtenerDireccionIP();
    $sentencia = $bd->prepare("SELECT ultima_peticion FROM peticiones_ip WHERE ip = '$ip' ");
    $sentencia->execute();
    $registro = $sentencia->fetchObject();
    $ultima_peticion = $registro->ultima_peticion;
    $resta = $time - $ultima_peticion;
    if (($resta > (TIEMPO_BORRADO_PETICIONES * 1)) && ($ultima_peticion != 0))
    {
        $sentencia = $bd->prepare("UPDATE peticiones_ip  SET timer = 0, intentos = 0 , ultima_peticion = '$time' WHERE ip = '$ip'");
        $sentencia->execute();
        //eliminarPeticionesIp();
    }
}

function determinarTiempoXUsuario($idUsuario) {
    $time = time();
    $bd = obtenerBaseDeDatos();
    $ip = obtenerDireccionIP();
    $intentos = obtenerConteoIntentosFallidos($idUsuario);
    if ($intentos >= MAXIMOS_INTENTOS_IPxUSUARIO) {
        $sentencia = $bd->prepare("SELECT timer FROM intentos_usuarios WHERE ip = '$ip' AND id_usuario = ?");
        $sentencia->execute([$idUsuario]);
        $registro = $sentencia->fetchObject();
        $timer = $registro->timer;
        if ($timer == 0 )
        {
            $expire = ($time + (TIEMPO_KICK_IPxUSER_SEG * 1));
            $sentencia = $bd->prepare("UPDATE intentos_usuarios  SET timer = ' $expire ', intentos = intentos WHERE ip = '$ip' AND id_usuario = ?");
            $sentencia->execute([$idUsuario]);
        }
        else {
            if ($timer < $time ) {
                $sentencia = $bd->prepare("UPDATE intentos_usuarios  SET timer = 0 , intentos = 0 WHERE id_usuario = ? AND ip = '$ip'");
                $sentencia->execute([$idUsuario]);
            }
        }
    }
}

function eliminarPeticionesIp() {
    $bd = obtenerBaseDeDatos();
    $ip = obtenerDireccionIP();
    $sentencia = $bd->prepare("DELETE FROM peticiones_ip WHERE ip = '. $ip .' ");
    $sentencia->execute();
}

function solicitudCaptcha($token) {
    $clavePriv='6LdjhdMiAAAAADtZp9QngPyWKRIltv4nVqGPeXQi';
    $url = 'https://www.google.com/recaptcha/api/siteverify';
    $rta = file_get_contents("$url?secret=$clavePriv&response=$token");
    $json = json_decode($rta, true);
    if ($json['success'] === false) {
        return false;
    }
    if ($json['score'] < 0.7) {
        return false;
    }
    return true;
}