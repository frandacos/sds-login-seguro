<?php include('header.php') ?>


<div class="login-box">
    
    <h1>Iniciar sesión</h1>
    <form id="form-login" action="login.php" method="POST">

        <!-- USERNAME INPUT -->
        <label for="email">Email o Usuario</label>
        <input type="email" name="email" required placeholder="Correo electrónico">

        <!-- PASSWORD INPUT -->
        <label for="password">Contraseña</label>
        <input type="password" name="pass" required placeholder="Contraseña">

        <!-- Si hay mensaje mostrarlo -->
        <?php
            if (isset($_GET["mensaje"])) { ?>
                <div class="error">
                    <a> <?php echo $_GET["mensaje"] ?> </a>
                </div>
        <?php } ?>

        <input id="entrar" type="submit" value="Ingresar">
        
        <a href="#">¿Olvidó/Perdido su clave? </a><br>
        <a href="formulario_registro.php">¿No tiene una cuenta?</a>
    </form>
</div>
<?php include('footer.php') ?>

