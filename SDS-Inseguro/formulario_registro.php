<?php include('header.php') ?>
<div class="login-box">
    <h1>Registrarte</h1>
    <form id="form-login" action="registrar_usuario.php" method="POST">

        <!-- USERNAME INPUT -->
        <label for="email">Ingresa un email</label>
        <input type="email" name="email" required placeholder="Correo electrónico">

        <!-- PASSWORD INPUT -->
        <label for="password">Ingresa una contraseña</label>
        <input type="password" name="pass1" required placeholder="Contraseña nueva">

        <label for="password">Repite la contraseña</label>
        <input type="password" name="pass2" required placeholder="Repite contraseña">

        <input id="entrar" type="submit" value="Registrarte">
        <?php
            # si hay un mensaje, mostrarlo
            if (isset($_GET["mensaje"])) { ?>
                <div class="alert alert-light" role="alert">
                    <?php echo $_GET["mensaje"] ?>
                </div>
        <?php } ?>
        
        <a href="#">¿Olvidó/Perdido su clave? </a><br>
        <a href="formulario_login.php">¿Ya tienes una cuenta?</a>
    </form>
</div>
<?php include('footer.php') ?>
