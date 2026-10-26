<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión | Sistema</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <main class="form-container">
        <h2>Iniciar Sesión</h2>
        <p class="form-subtitle">Por favor, ingrese sus credenciales para acceder</p>
        
        <div id="alert-message" class="alert" role="alert" style="display: none;"></div>

        <form id="form-login" novalidate>
            <div class="form-group">
                <label for="login-email">Correo electrónico</label>
                <input type="email" id="login-email" name="email" placeholder="ejemplo@correo.com" required autocomplete="email">
            </div>

            <div class="form-group">
                <label for="login-password">Contraseña</label>
                <input type="password" id="login-password" name="password" placeholder="••••••••" required autocomplete="current-password">
            </div>

            <button type="submit" id="btn-login" class="btn-submit"><span>Ingresar al Sistema</span></button>
        </form>

        <p>
            ¿No tienes cuenta? <a href="register.php">Regístrate aquí</a>
        </p>
    </main>

    <script src="js/app.js"></script>
</body>
</html>