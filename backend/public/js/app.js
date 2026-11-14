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