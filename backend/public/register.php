<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Usuario - Producción</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <main class="form-container">
        <h2>Crear Cuenta</h2>
        
        <div id="alert-message" class="alert" role="alert" style="display: none;"></div>

        <form id="form-register" novalidate>
            <div class="form-group">
                <label for="reg-name">Nombre completo</label>
                <input type="text" id="reg-name" name="name" required autocomplete="name">
            </div>

            <div class="form-group">
                <label for="reg-email">Correo electrónico</label>
                <input type="email" id="reg-email" name="email" required autocomplete="email">
            </div>

            <div class="form-group">
                <label for="reg-password">Contraseña</label>
                <input type="password" id="reg-password" name="password" required autocomplete="new-password">
            </div>

            <div class="form-group">
                <label for="reg-password-confirmation">Confirmar contraseña</label>
                <input type="password" id="reg-password-confirmation" name="password_confirmation" required autocomplete="new-password">
            </div>

            <button type="submit" id="btn-register" class="btn-submit"><span>Registrar Cuenta</span></button>
        </form>

        <p style="text-align: center; margin-top: 15px; font-size: 14px;">
            ¿Ya tienes una cuenta? <a href="login.php" style="color: #2563eb; text-decoration: none; font-weight: 600;">Inicia sesión aquí</a>
        </p>
    </main>

    <script src="js/app.js"></script>
</body>
</html>