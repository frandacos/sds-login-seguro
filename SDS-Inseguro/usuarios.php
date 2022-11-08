<?php include('header.php') ?>
<?php
include_once "funciones.php";
# Si no hay usuario logueado, salir inmediatamente
if (!usuarioEstaLogueado()) {
    //header("Location: formulario_login.php?mensaje=Inicia sesión para acceder a la página protegida");
    header("Location: index.php");
    die(); // <- Es muy importante terminar el script
}
?>

<div class="login-box">
    <div class="auxiliar">
    <h1>Sesión iniciada correctamente.</h1>
    <a id="cerrar" class="cerrar" href="cerrar_session.php" >Cerrar Sesion</a>
    </div>
    
</div>

<?php include('footer.php') ?>
