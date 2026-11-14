<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Contraseña</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <main class="form-container">
        <h2>Recuperar Contraseña</h2>
        <p class="form-subtitle">Ingresa tu correo electrónico y te enviaremos un enlace para restablecer tu clave.</p>
        
        <div id="alert-message" class="alert" role="alert" style="display: none;"></div>

        <form id="form-forgot" novalidate>
            <div class="form-group">
                <label for="forgot-email">Correo electrónico</label>
                <input type="email" id="forgot-email" name="email" placeholder="ejemplo@correo.com" required autocomplete="email">
            </div>

            <button type="submit" id="btn-forgot" class="btn-submit"><span>Enviar Enlace de Recuperación</span></button>
        </form>

        <p style="text-align: center; margin-top: 20px; font-size: 14px;">
            <a href="login.php">Volver al inicio de sesión</a>
        </p>
    </main>

    <script src="js/app.js"></script>
</body>
</html>