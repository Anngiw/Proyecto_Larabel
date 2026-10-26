<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Principal | Dashboard</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <main class="auth-container">
        <section class="auth-card">
            <header class="auth-header">
                <h2>¡Bienvenido al Sistema!</h2>
                <p>Sesión iniciada correctamente (Zona Privada)</p>
            </header>

            <div id="user-info" style="margin-bottom: 25px; font-size: 16px; font-weight: 600; color: #1e293b; background: #f8fafc; padding: 12px; border-radius: 8px; border: 1px solid #e2e8f0;"></div>

            <button type="button" class="btn-primary" onclick="App.logout()">Cerrar Sesión</button>
        </section>
    </main>

    <script src="js/app.js"></script>
</body>
</html>