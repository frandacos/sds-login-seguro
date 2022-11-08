<?php include('header.php') ?>
<div class="login-box">
    <img src="img/logo2.png" class="avatar" alt="Avatar Image">
    <h1>Registrarte</h1>
    <form id="form-login" action="registrar_usuario.php" method="POST">

        <!-- USERNAME INPUT -->
        <label for="email">Ingresa un email</label>
        <div id="input-box">
            <i class="fa fa-envelope"></i>
            <input type="email" name="email" required placeholder="Correo electrónico">
        </div>
        <!-- PASSWORD INPUT -->
        <label for="password">Ingresa una contraseña</label>
        <div id="input-box">
            <i class="fa fa-key"></i>
            <input type="password" name="pass1" required placeholder="Contraseña nueva">
        </div>
        <label for="password">Repite la contraseña</label>
        <div id="input-box">
            <i class="fa fa-key"></i>
            <input type="password" name="pass2" required placeholder="Repite contraseña">
        </div>
        <input id="entrar" type="submit" value="Registrarte">
        
        <?php
            # si hay un mensaje, mostrarlo
            if (isset($_GET["mensaje"])) { ?>
                <div class="alert alert-warning" role="alert">
                    <?php echo $_GET["mensaje"] ?>
                </div>
        <?php } ?>
        
        <a href="#">¿Olvidó/Perdido su clave? </a><br>
        <a href="formulario_login.php">¿Ya tienes una cuenta?</a>
    </form>
</div>


<?php include('footer.php') ?>
