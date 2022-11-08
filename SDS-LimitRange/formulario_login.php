<?php include('header.php') ?>
<div class="login-box">
    <img src="img/logo2.png" class="avatar" alt="Avatar Image">
    <h1>Iniciar sesión</h1>
    <form id="form-login" action="login.php" method="POST">

        <!-- USERNAME INPUT -->
        <label for="email">Email</label>
        <div id="input-box">
            <i class="fa fa-envelope"></i>
            <input type="email" name="email" required placeholder="Correo electrónico">
        </div>
        <!-- PASSWORD INPUT -->
        <label for="password">Contraseña</label>
        <div id="input-box">
            <i class="fa fa-key"></i>
            <input type="password" name="pass" required placeholder="Contraseña">
        </div>
        <input type="hidden" name="token" id="token"/>

        <!-- Si hay mensaje mostrarlo -->
        <?php
            if (isset($_GET["mensaje"])) { ?>
                <div class="alert alert-warning" role="alert">
                    <?php echo $_GET["mensaje"] ?>
                </div>
        <?php } ?>

        <input id="entrar" type="submit" value="Ingresar">
        
        <a href="#">¿Olvidó/Perdido su clave? </a><br>
        <a href="formulario_registro.php">¿No tiene una cuenta?</a>
    </form>
</div>
<?php include('footer.php') ?>

