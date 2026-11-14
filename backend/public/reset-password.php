<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Establecer Nueva Contraseña</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <main class="form-container">
        <h2>Nueva Contraseña</h2>
        <p class="form-subtitle">Debe tener mínimo 8 caracteres, una mayúscula, una minúscula y un número.</p>

        <div id="alert-message" class="alert" role="alert" style="display: none;"></div>

        <form id="form-reset" novalidate>
            <div class="form-group">
                <label for="reset-password">Nueva Contraseña</label>
                <input type="password" id="reset-password" name="password" placeholder="••••••••" required autocomplete="new-password">
            </div>

            <div class="form-group">
                <label for="reset-password-confirmation">Confirmar Nueva Contraseña</label>
                <input type="password" id="reset-password-confirmation" name="password_confirmation" placeholder="••••••••" required autocomplete="new-password">
            </div>

            <button type="submit" id="btn-reset" class="btn-submit"><span>Guardar Nueva Contraseña</span></button>
        </form>

        <p style="text-align: center; margin-top: 20px; font-size: 14px;">
            <a href="login.php">Volver al inicio de sesión</a>
        </p>
    </main>

    <script src="js/app.js"></script>
</body>
</html>