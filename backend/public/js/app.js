const API_BASE_URL = 'http://127.0.0.1:8000/api';

document.addEventListener('DOMContentLoaded', () => {
    // --- PROTECCIÓN DEL DASHBOARD ---
    const userInfoDiv = document.getElementById('user-info');
    if (userInfoDiv) {
        const token = localStorage.getItem('auth_token');
        if (!token) {
            window.location.href = 'login.php';
            return;
        }
        const userName = localStorage.getItem('user_name');
        if (userName) {
            userInfoDiv.textContent = `Bienvenido(a), ${userName}`;
        }
    }

    // --- LÓGICA DE REGISTRO ---
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
                        window.location.href = 'login.php';
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

    // --- LÓGICA DE LOGIN ---
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

                    // Redirección directa al dashboard sin alertas bloqueantes
                    window.location.href = 'dashboard.php';
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

window.App = {
    logout: function() {
        localStorage.removeItem('auth_token');
        localStorage.removeItem('user_name');
        window.location.href = 'login.php';
    }
};