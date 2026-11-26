const API_BASE_URL = 'http://127.0.0.1:8000/api';

document.addEventListener('DOMContentLoaded', () => {
    //  Proteccion del DashBoard
    const userInfoDiv = document.getElementById('user-info');
    if (userInfoDiv) {
        const token = localStorage.getItem('auth_token');
        if (!token) {
            window.location.replace('login.php');
            return;
        }
        const userName = localStorage.getItem('user_name');
        if (userName) {
            userInfoDiv.textContent = `Bienvenido(a), ${userName}`;
        }
    }

    // Limpiar formulario login al cargar (Evita rastros de correo/contraseña anteriores) 
    const loginFormElement = document.getElementById('form-login');
    if (loginFormElement) {
        loginFormElement.reset();
        const emailInput = document.getElementById('login-email');
        const passInput = document.getElementById('login-password');
        if (emailInput) emailInput.value = '';
        if (passInput) passInput.value = '';
    }

    // Logica de Registro
    const registerForm = document.getElementById('form-register');
    if (registerForm) {
        registerForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            clearAlert();

            const btn = document.getElementById('btn-register');
            const name = document.getElementById('reg-name').value.trim();
            const email = document.getElementById('reg-email').value.trim();
            const password = document.getElementById('reg-password').value;
            const password_confirmation = document.getElementById('reg-password-confirmation').value;

            if (!name || !email || !password || !password_confirmation) {
                showAlert('Todos los campos son obligatorios.', 'error');
                return;
            }

            if (password !== password_confirmation) {
                showAlert('Las contraseñas no coinciden.', 'error');
                return;
            }

            try {
                setLoading(btn, true, 'Registrando...');
                showAlert('Guardando datos en la base de datos...', 'info');

                const response = await fetch(`${API_BASE_URL}/register`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ name, email, password, password_confirmation })
                });

                const data = await response.json();

                if (response.status === 201) {
                    showAlert(data.message || '¡Usuario creado con éxito!', 'success');
                    setTimeout(() => {
                        window.location.replace('login.php');
                    }, 2000);
                } else if (response.status === 422) {
                    showAlert(data.message || 'El correo ya se encuentra registrado.', 'error');
                    setLoading(btn, false, 'Registrar Cuenta');
                } else {
                    showAlert(data.message || 'Ocurrió un error en el registro.', 'error');
                    setLoading(btn, false, 'Registrar Cuenta');
                }
            } catch (error) {
                console.error('Error de red:', error);
                showAlert('Error de conexión con el servidor backend.', 'error');
                setLoading(btn, false, 'Registrar Cuenta');
            }
        });
    }

    // Logica del Login
    const loginForm = document.getElementById('form-login');
    if (loginForm) {
        loginForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            e.stopPropagation();
            clearAlert();

            const btn = document.getElementById('btn-login');
            const email = document.getElementById('login-email').value.trim();
            const password = document.getElementById('login-password').value;

            if (!email || !password) {
                showAlert('Por favor, complete todos los campos.', 'error');
                return false;
            }

            try {
                setLoading(btn, true, 'Verificando...');
                showAlert('Consultando credenciales...', 'info');

                const response = await fetch(`${API_BASE_URL}/login`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ email, password })
                });

                const data = await response.json();

                console.log("Estado HTTP recibido:", response.status);
                console.log("Datos recibidos del backend:", data);

                if (response.ok && response.status === 200) {
                    localStorage.setItem('auth_token', data.access_token);
                    localStorage.setItem('user_name', data.user.name);

                    // Redirección directa y limpia al dashboard
                    window.location.replace('dashboard.php');
                    return;

                } else {
                    let errorMessage = data.message || 'Credenciales incorrectas.';
                    
                    if (data.errors) {
                        const firstKey = Object.keys(data.errors)[0];
                        if (firstKey && data.errors[firstKey][0]) {
                            errorMessage = data.errors[firstKey][0];
                        }
                    }

                    showAlert(errorMessage, 'error');
                    setLoading(btn, false, 'Ingresar al Sistema');
                }

            } catch (error) {
                console.error('Error crítico en el login:', error);
                showAlert('Error de conexión con el servidor.', 'error');
                setLoading(btn, false, 'Ingresar al Sistema');
            }

            return false;
        });
    }

    // Tareas y Calendario
    const tareaForm = document.getElementById('form-tarea');
    if (tareaForm) {
        initTareas();
    }
    
});

function showAlert(message, type) {
    const alertDiv = document.getElementById('alert-message');
    if (!alertDiv) return;

    alertDiv.textContent = message;
    alertDiv.style.setProperty('display', 'flex', 'important');

    const styles = {
        error: { bg: '#fef2f2', color: '#991b1b', border: '#fecaca' },
        success: { bg: '#f0fdf4', color: '#166534', border: '#bbf7d0' },
        info: { bg: '#eff6ff', color: '#1e40af', border: '#bfdbfe' }
    };

    const currentStyle = styles[type] || styles.info;
    alertDiv.style.backgroundColor = currentStyle.bg;
    alertDiv.style.color = currentStyle.color;
    alertDiv.style.border = `1px solid ${currentStyle.border}`;
}

function clearAlert() {
    const alertDiv = document.getElementById('alert-message');
    if (alertDiv) {
        alertDiv.textContent = '';
        alertDiv.style.display = 'none';
    }
}

function setLoading(button, isLoading, text) {
    if (!button) return;
    button.disabled = isLoading;
    const span = button.querySelector('span');
    if (span) {
        span.textContent = text;
    } else {
        button.textContent = text;
    }
}

// Logica de recuperacion cumpliendo CA-03.1 y CA-03.2
const forgotForm = document.getElementById('form-forgot');
if (forgotForm) {
    forgotForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        clearAlert();

        const btn = document.getElementById('btn-forgot');
        const email = document.getElementById('forgot-email').value.trim();

        if (!email) {
            showAlert('Por favor ingresa tu correo electrónico.', 'error');
            return;
        }

        try {
            setLoading(btn, true, 'Enviando...');
            showAlert('Procesando solicitud...', 'info');

            const response = await fetch(`${API_BASE_URL}/forgot-password`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ email })
            });

            const data = await response.json();

            if (response.ok) {
                showAlert(data.message, 'success');
                forgotForm.reset();
            } else {
                showAlert(data.message || 'Verifica el formato del correo.', 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            showAlert('Error de conexión con el servidor.', 'error');
        } finally {
            setLoading(btn, false, 'Enviar Enlace de Recuperación');
        }
    });
}

// Logica de reestablecer contraseña cumpliendo CA-03.4 a CA-03.7
const resetForm = document.getElementById('form-reset');
if (resetForm) {
    resetForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        clearAlert();

        const btn = document.getElementById('btn-reset');
        const urlParams = new URLSearchParams(window.location.search);
        const token = urlParams.get('token');
        const email = urlParams.get('email');
        const password = document.getElementById('reset-password').value;
        const password_confirmation = document.getElementById('reset-password-confirmation').value;

        if (!token || !email) {
            showAlert('Enlace de recuperación incompleto o inválido.', 'error');
            return;
        }

        if (password !== password_confirmation) {
            showAlert('Las contraseñas no coinciden.', 'error');
            return;
        }

        try {
            setLoading(btn, true, 'Actualizando...');
            showAlert('Validando y guardando nueva contraseña...', 'info');

            const response = await fetch(`${API_BASE_URL}/reset-password`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ token, email, password, password_confirmation })
            });

            const data = await response.json();

            if (response.ok) {
                showAlert(data.message, 'success');
                setTimeout(() => {
                    window.location.replace('login.php');
                }, 2500);
            } else {
                let errorMsg = data.message || 'Error al restablecer la contraseña.';
                if (data.errors && data.errors.password) {
                    errorMsg = data.errors.password[0];
                }
                showAlert(errorMsg, 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            showAlert('Error de conexión con el servidor.', 'error');
        } finally {
            setLoading(btn, false, 'Guardar Nueva Contraseña');
        }
    });
}

window.App = {
    logout: async function() {
        const token = localStorage.getItem('auth_token');
        
        if (token) {
            try {
                await fetch(`${API_BASE_URL}/logout`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'Authorization': `Bearer ${token}`
                    }
                });
            } catch (error) {
                console.error('Error al notificar cierre de sesión al servidor:', error);
            }
        }

        // Limpiar credenciales locales
        localStorage.removeItem('auth_token');
        localStorage.removeItem('user_name');
        
        // Redirigir limpiando el historial para evitar que el botón atrás recupere datos
        window.location.replace('login.php');
    }
};
let tareas = [];
let calMes = new Date(new Date().getFullYear(), new Date().getMonth(), 1);

function esc(txt) {
    return String(txt ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

function authHeaders() {
    const token = localStorage.getItem('auth_token');
    return {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'Authorization': `Bearer ${token}`
    };
}

function initTareas() {
    document.getElementById('form-tarea').addEventListener('submit', guardarTarea);
    document.getElementById('btn-cancel')?.addEventListener('click', limpiarTareaForm);
    document.getElementById('btn-start')?.addEventListener('click', iniciarTarea);

    document.getElementById('cal-prev')?.addEventListener('click', () => {
        calMes = new Date(calMes.getFullYear(), calMes.getMonth() - 1, 1);
        pintarCal();
    });

    document.getElementById('cal-next')?.addEventListener('click', () => {
        calMes = new Date(calMes.getFullYear(), calMes.getMonth() + 1, 1);
        pintarCal();
    });

    pintarCal();
    pintarTareas();
    cargarTareas();
}

function iniciarTarea() {
    limpiarTareaForm();
    const hoy = new Date().toISOString().slice(0, 10);
    const inicio = document.getElementById('t-inicio');
    const fin = document.getElementById('t-fin');
    if (inicio && !inicio.value) inicio.value = hoy;
    if (fin && !fin.value) fin.value = hoy;
    document.getElementById('t-nombre')?.focus();
}

function limpiarTareaForm() {
    document.getElementById('form-tarea')?.reset();
    const idInput = document.getElementById('t-id');
    const title = document.getElementById('form-title');
    const cancel = document.getElementById('btn-cancel');
    if (idInput) idInput.value = '';
    if (title) title.textContent = 'Nueva Tarea';
    if (cancel) cancel.style.display = 'none';
}

async function cargarTareas() {
    try {
        const res = await fetch(`${API_BASE_URL}/tareas`, { headers: authHeaders() });
        if (res.status === 401) {
            window.App.logout();
            return;
        }
        const json = await res.json();
        tareas = json.data || [];
        pintarTareas();
        pintarCal();
    } catch (err) {
        console.error('Error cargando tareas:', err);
        showAlert('No se pudieron cargar las tareas.', 'error');
    }
}

async function guardarTarea(e) {
    e.preventDefault();
    clearAlert();

    const btn = document.getElementById('btn-save');
    const id = document.getElementById('t-id').value;
    const body = {
        nombre: document.getElementById('t-nombre').value.trim(),
        descripcion: document.getElementById('t-desc').value.trim() || null,
        fecha_inicio: document.getElementById('t-inicio').value,
        fecha_fin: document.getElementById('t-fin').value,
        estado: document.getElementById('t-estado').value
    };

    try {
        setLoading(btn, true, 'Guardando...');
        const url = id ? `${API_BASE_URL}/tareas/${id}` : `${API_BASE_URL}/tareas`;
        const method = id ? 'PUT' : 'POST';

        const res = await fetch(url, {
            method,
            headers: authHeaders(),
            body: JSON.stringify(body)
        });
        const json = await res.json();

        if (!res.ok) {
            const msg = json.errors
                ? Object.values(json.errors).flat().join(' ')
                : (json.message || 'Revisa los datos de la tarea.');
            showAlert(msg, 'error');
            return;
        }

        showAlert(json.message || 'Tarea guardada con éxito.', 'success');
        limpiarTareaForm();
        await cargarTareas();
    } catch (err) {
        console.error('Error guardando tarea:', err);
        showAlert('Error de conexión al guardar la tarea.', 'error');
    } finally {
        setLoading(btn, false, 'Guardar Tarea');
    }
}

function editarTarea(id) {
    const t = tareas.find((item) => item.id === id);
    if (!t) return;

    document.getElementById('t-id').value = t.id;
    document.getElementById('t-nombre').value = t.nombre;
    document.getElementById('t-desc').value = t.descripcion || '';
    document.getElementById('t-inicio').value = String(t.fecha_inicio).slice(0, 10);
    document.getElementById('t-fin').value = String(t.fecha_fin).slice(0, 10);
    document.getElementById('t-estado').value = t.estado;

    const title = document.getElementById('form-title');
    const cancel = document.getElementById('btn-cancel');
    if (title) title.textContent = 'Editar Tarea';
    if (cancel) cancel.style.display = 'inline-block';
    document.getElementById('t-nombre')?.focus();
}

async function borrarTarea(id) {
    if (!confirm('¿Deseas eliminar esta tarea?')) return;
    clearAlert();

    try {
        const res = await fetch(`${API_BASE_URL}/tareas/${id}`, {
            method: 'DELETE',
            headers: authHeaders()
        });
        const json = await res.json();

        if (!res.ok) {
            showAlert(json.message || 'No se pudo eliminar la tarea.', 'error');
            return;
        }

        showAlert(json.message || 'Tarea eliminada.', 'success');
        await cargarTareas();
    } catch (err) {
        console.error('Error eliminando tarea:', err);
        showAlert('Error de conexión al eliminar la tarea.', 'error');
    }
}

function pintarTareas() {
    const box = document.getElementById('lista-tareas');
    const count = document.getElementById('task-count');
    if (!box) return;

    if (count) {
        count.textContent = `${tareas.length} ${tareas.length === 1 ? 'tarea' : 'tareas'}`;
    }

    if (!tareas.length) {
        box.innerHTML = '<div class="task-empty">No hay tareas registradas todavía. Haz clic en <strong>+ Iniciar tarea</strong> para crear la primera.</div>';
        return;
    }

    const badgeMap = {
        'Pendiente': 'badge-pendiente',
        'En progreso': 'badge-progreso',
        'Completada': 'badge-completada'
    };

    box.innerHTML = tareas.map((t) => {
        const ini = String(t.fecha_inicio).slice(0, 10);
        const fin = String(t.fecha_fin).slice(0, 10);
        const id = Number(t.id);
        const badgeCls = badgeMap[t.estado] || 'badge-pendiente';

        return `
            <article class="task-card">
                <div>
                    <div class="task-top">
                        <h4 class="task-title">${esc(t.nombre)}</h4>
                        <span class="badge ${badgeCls}">${esc(t.estado)}</span>
                    </div>
                    <div class="task-dates">Del <strong>${esc(ini)}</strong> al <strong>${esc(fin)}</strong></div>
                    ${t.descripcion ? `<p class="task-desc">${esc(t.descripcion)}</p>` : ''}
                </div>
                <div class="task-actions">
                    <button type="button" class="btn-edit" onclick="App.editar(${id})">Editar</button>
                    <button type="button" class="btn-del" onclick="App.borrar(${id})">Eliminar</button>
                </div>
            </article>
        `;
    }).join('');
}

function pintarCal() {
    const grid = document.getElementById('cal-grid');
    const title = document.getElementById('cal-title');
    if (!grid || !title) return;

    const y = calMes.getFullYear();
    const m = calMes.getMonth();
    const hoy = new Date().toISOString().slice(0, 10);
    title.textContent = calMes.toLocaleDateString('es-ES', { month: 'long', year: 'numeric' });

    const dias = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'];
    let html = dias.map((d) => `<div class="cal-day-name">${d}</div>`).join('');

    const primerDia = new Date(y, m, 1).getDay();
    const totalDias = new Date(y, m + 1, 0).getDate();

    for (let i = 0; i < primerDia; i++) {
        html += '<div class="cal-cell empty"></div>';
    }

    for (let d = 1; d <= totalDias; d++) {
        const fecha = `${y}-${String(m + 1).padStart(2, '0')}-${String(d).padStart(2, '0')}`;
        const delDia = tareas.filter((t) => {
            const ini = String(t.fecha_inicio).slice(0, 10);
            const fin = String(t.fecha_fin).slice(0, 10);
            return ini <= fecha && fecha <= fin;
        });

        const chips = delDia
            .map((t) => `<div class="cal-chip" title="${esc(t.nombre)}">${esc(t.nombre)}</div>`)
            .join('');

        const todayCls = fecha === hoy ? ' today' : '';
        html += `<div class="cal-cell${todayCls}"><strong>${d}</strong>${chips}</div>`;
    }

    grid.innerHTML = html;
}

window.App = window.App || {};
window.App.editar = editarTarea;
window.App.borrar = borrarTarea;