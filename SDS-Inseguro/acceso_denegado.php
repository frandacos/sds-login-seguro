<?php include('header.php') ?>
<div class="login-box">
    <img src="img/logo2.png" class="avatar" alt="Avatar Image">
    <h1>Acceso denegado</h1>
    <form id="form-login" action="login.php" method="POST">
        <div class="alert alert-warning" role="alert">
            <p>Ha habido varios intentos fallidos de iniciar sesión desde esta cuenta o dirección IP. Espere un momento y vuelva a intentarlo más tarde.</p>        
        </div>
    </form>
</div>
<?php include('footer.php') ?>
