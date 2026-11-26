<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate, max-age=0">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title>Panel Principal | TaskFlow Pro</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/dashboard.css">
</head>
<body>
    <main class="dash-wrap">
        <header class="dash-top">
            <div class="dash-brand">
                <span class="dash-tag">TaskFlow Pro · Espacio de trabajo</span>
                <h2 id="user-info">Bienvenido(a)</h2>
                <p class="dash-sub">Organiza tus tareas, fechas y progreso desde un solo lugar.</p>
            </div>

            <div class="top-actions">
                
                <button type="button" class="btn-logout" onclick="App.logout()" style ="backgroud-color:red">Cerrar Sesión</button>
            </div>
        </header>

        <div id="alert-message" class="alert" role="alert" style="display: none;"></div>

        <section class="dash-grid" aria-label="Gestión y calendario de tareas">
            <article class="panel panel-form">
                <header class="panel-head">
                    <h3 id="form-title">Nueva Tarea</h3>
                    <span class="panel-hint">* Obligatorio</span>
                </header>

                <form id="form-tarea" novalidate>
                    <input type="hidden" id="t-id">

                    <div class="form-group">
                        <label for="t-nombre">Nombre *</label>
                        <input type="text" id="t-nombre" maxlength="150" placeholder="Ej. Entregar informe Sprint 3" required>
                    </div>

                    <div class="form-group">
                        <label for="t-desc">Descripción</label>
                        <textarea id="t-desc" rows="3" placeholder="Detalles opcionales de la actividad..."></textarea>
                    </div>

                    <div class="row-2">
                        <div class="form-group">
                            <label for="t-inicio">Fecha inicio *</label>
                            <input type="date" id="t-inicio" required>
                        </div>
                        <div class="form-group">
                            <label for="t-fin">Fecha fin *</label>
                            <input type="date" id="t-fin" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="t-estado">Estado *</label>
                        <select id="t-estado" required>
                            <option value="Pendiente">Pendiente</option>
                            <option value="En progreso">En progreso</option>
                            <option value="Completada">Completada</option>
                        </select>
                    </div>

                    <div class="form-actions">
                        <button type="submit" id="btn-save" class="btn-save"><span>Guardar Tarea</span></button>
                        <button type="button" id="btn-cancel" class="btn-cancel" style="display: none;">Cancelar</button>
                    </div>
                </form>
            </article>

            <article class="panel panel-cal">
                <header class="cal-head">
                    <button type="button" id="cal-prev" class="btn-nav" aria-label="Mes anterior">&lt;</button>
                    <h3 id="cal-title">Calendario</h3>
                    <button type="button" id="cal-next" class="btn-nav" aria-label="Mes siguiente">&gt;</button>
                </header>
                <div id="cal-grid" class="cal-grid" aria-live="polite"></div>
            </article>
        </section>

        <section class="panel panel-list" aria-label="Listado de tareas">
            <header class="panel-head">
                <h3>Mis Tareas</h3>
                <span id="task-count" class="panel-hint">0 tareas</span>
            </header>
            <div id="lista-tareas" class="task-list"></div>
        </section>
    </main>

    <script src="js/app.js"></script>
</body>
</html>