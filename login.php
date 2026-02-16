<?php require_once 'conexion/session.php'; ?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AutoTech - Acceso al Sistema</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        :root {
            --primary-color: #1a3a5f;
            --secondary-color: #f8b400;
            --accent-color: #e63946;
            --light-color: #f8f9fa;
            --dark-color: #212529;
            --gray-color: #6c757d;
            --shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
        }

        body {
            background-color: #f5f7fa;
            color: var(--dark-color);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        /* Header */
        header {
            background-color: white;
            box-shadow: var(--shadow);
            padding: 15px 0;
        }

        nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 700;
            font-size: 1.8rem;
            color: var(--primary-color);
            text-decoration: none;
        }

        .logo i {
            color: var(--secondary-color);
        }

        .back-home {
            display: flex;
            align-items: center;
            gap: 8px;
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 600;
            transition: var(--transition);
            padding: 10px 15px;
            border-radius: 5px;
        }

        .back-home:hover {
            background-color: rgba(26, 58, 95, 0.1);
        }

        .menu-toggle {
            display: none;
            flex-direction: column;
            justify-content: space-between;
            width: 30px;
            height: 21px;
            cursor: pointer;
            z-index: 2001;
            transition: var(--transition);
        }

        .menu-toggle span {
            display: block;
            width: 100%;
            height: 3px;
            background-color: var(--primary-color);
            border-radius: 3px;
            transition: var(--transition);
        }

        .menu-toggle.active span:nth-child(1) {
            transform: translateY(9px) rotate(45deg);
        }

        .menu-toggle.active span:nth-child(2) {
            opacity: 0;
        }

        .menu-toggle.active span:nth-child(3) {
            transform: translateY(-9px) rotate(-45deg);
        }

        .menu-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(4px);
            z-index: 1999;
            opacity: 0;
            visibility: hidden;
            transition: var(--transition);
        }

        .menu-overlay.active {
            opacity: 1;
            visibility: visible;
        }

        /* Contenedor principal de login */
        .login-container {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
            position: relative;
            overflow: hidden;
        }

        .login-bg {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(rgba(26, 58, 95, 0.9), rgba(26, 58, 95, 0.8)), url('https://images.unsplash.com/photo-1603575448878-868a20723f5d?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1470&q=80');
            background-size: cover;
            background-position: center;
            z-index: -1;
        }

        .login-wrapper {
            display: flex;
            width: 100%;
            max-width: 1000px;
            background-color: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
            animation: fadeInUp 0.8s ease-out;
        }

        .login-info {
            flex: 1;
            background-color: var(--primary-color);
            color: white;
            padding: 60px 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .login-form-section {
            flex: 1;
            padding: 60px 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .login-info h1 {
            font-size: 2.5rem;
            margin-bottom: 20px;
            color: white;
        }

        .login-info p {
            margin-bottom: 30px;
            line-height: 1.6;
            opacity: 0.9;
        }

        .features-list {
            list-style: none;
            margin-top: 30px;
        }

        .features-list li {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 20px;
        }

        .features-list i {
            color: var(--secondary-color);
            font-size: 1.2rem;
        }

        .login-form-section h2 {
            color: var(--primary-color);
            font-size: 2rem;
            margin-bottom: 10px;
        }

        .login-form-section>p {
            color: var(--gray-color);
            margin-bottom: 30px;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--dark-color);
        }

        .input-with-icon {
            position: relative;
        }

        .input-with-icon i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray-color);
            font-size: 1.1rem;
        }

        .input-with-icon input {
            width: 100%;
            padding: 15px 15px 15px 50px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 1rem;
            transition: var(--transition);
        }

        .input-with-icon input:focus {
            border-color: var(--primary-color);
            outline: none;
            box-shadow: 0 0 0 3px rgba(26, 58, 95, 0.1);
        }

        /* Password Specific Input Padding */
        #password {
            padding-right: 50px;
        }

        .password-toggle {
            position: absolute;
            right: 5px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--gray-color);
            cursor: pointer;
            font-size: 1.1rem;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: var(--transition);
            z-index: 10;
        }

        .password-toggle:hover {
            background-color: rgba(0, 0, 0, 0.05);
            color: var(--primary-color);
        }

        .password-toggle:focus {
            outline: none;
        }

        .remember-forgot {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            flex-wrap: wrap;
            gap: 10px;
        }

        .remember-me {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .remember-me input {
            width: 18px;
            height: 18px;
            cursor: pointer;
        }

        .forgot-password {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 600;
            transition: var(--transition);
        }

        .forgot-password:hover {
            color: var(--secondary-color);
            text-decoration: underline;
        }

        .login-btn {
            width: 100%;
            padding: 16px;
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .login-btn:hover {
            background-color: #0f2a46;
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }

        .login-btn:active {
            transform: translateY(-1px);
        }

        .divider {
            text-align: center;
            margin: 30px 0;
            position: relative;
            color: var(--gray-color);
        }

        .divider::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            width: 45%;
            height: 1px;
            background-color: #ddd;
        }

        .divider::after {
            content: '';
            position: absolute;
            top: 50%;
            right: 0;
            width: 45%;
            height: 1px;
            background-color: #ddd;
        }

        .social-login {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-bottom: 30px;
        }

        .social-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background-color: #f5f5f5;
            color: var(--dark-color);
            font-size: 1.2rem;
            cursor: pointer;
            transition: var(--transition);
            border: 1px solid #ddd;
        }

        .social-btn:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 15px rgba(0, 0, 0, 0.1);
        }

        .social-btn.google:hover {
            background-color: #db4437;
            color: white;
            border-color: #db4437;
        }

        .social-btn.facebook:hover {
            background-color: #4267B2;
            color: white;
            border-color: #4267B2;
        }

        .social-btn.apple:hover {
            background-color: #000;
            color: white;
            border-color: #000;
        }

        .register-link {
            text-align: center;
            margin-top: 30px;
            color: var(--gray-color);
        }

        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 12px;
            animation: slideIn 0.3s ease-out;
        }

        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .register-link a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 600;
            margin-left: 5px;
            transition: var(--transition);
        }

        .register-link a:hover {
            color: var(--secondary-color);
            text-decoration: underline;
        }

        /* Modal de recuperación de contraseña */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.8);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 2000;
            opacity: 0;
            visibility: hidden;
            transition: var(--transition);
        }

        .modal-overlay.active {
            opacity: 1;
            visibility: visible;
        }

        .modal-content {
            background-color: white;
            border-radius: 15px;
            padding: 40px;
            width: 90%;
            max-width: 500px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
            transform: translateY(-50px);
            transition: var(--transition);
        }

        .modal-overlay.active .modal-content {
            transform: translateY(0);
        }

        .modal-content h3 {
            color: var(--primary-color);
            margin-bottom: 20px;
            font-size: 1.8rem;
        }

        .modal-content p {
            color: var(--gray-color);
            margin-bottom: 25px;
            line-height: 1.6;
        }

        .close-modal {
            position: absolute;
            top: 20px;
            right: 20px;
            background: none;
            border: none;
            font-size: 1.5rem;
            color: white;
            cursor: pointer;
            transition: var(--transition);
        }

        .close-modal:hover {
            transform: rotate(90deg);
            color: var(--secondary-color);
        }

        .modal-btn {
            padding: 14px 30px;
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            margin-top: 10px;
            width: 100%;
        }

        .modal-btn:hover {
            background-color: #0f2a46;
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }

        /* Footer */
        footer {
            background-color: var(--primary-color);
            color: var(--light-color);
            padding: 30px 0;
            text-align: center;
        }

        .footer-links {
            display: flex;
            justify-content: center;
            gap: 30px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .footer-links a {
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: var(--transition);
        }

        .footer-links a:hover {
            color: var(--secondary-color);
        }

        .copyright {
            color: rgba(255, 255, 255, 0.6);
            font-size: 0.9rem;
        }

        /* Animaciones */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes shake {

            0%,
            100% {
                transform: translateX(0);
            }

            10%,
            30%,
            50%,
            70%,
            90% {
                transform: translateX(-5px);
            }

            20%,
            40%,
            60%,
            80% {
                transform: translateX(5px);
            }
        }

        /* Mensajes de validación */
        .validation-message {
            color: var(--accent-color);
            font-size: 0.9rem;
            margin-top: 5px;
            display: none;
        }

        .validation-message.show {
            display: block;
            animation: fadeInUp 0.3s ease-out;
        }

        .success-message {
            background-color: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: none;
        }

        .success-message.show {
            display: block;
            animation: fadeInUp 0.5s ease-out;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .menu-toggle {
                display: flex;
            }

            .nav-links {
                position: fixed;
                top: 0;
                right: -300px;
                width: 300px;
                height: 100vh;
                background: rgba(255, 255, 255, 0.9);
                backdrop-filter: blur(15px);
                display: flex;
                flex-direction: column;
                justify-content: center;
                align-items: center;
                padding: 40px;
                z-index: 2000;
                transition: transform 0.4s cubic-bezier(0.77, 0.2, 0.05, 1.0);
                box-shadow: -10px 0 30px rgba(0, 0, 0, 0.1);
            }

            .nav-links.active {
                display: flex;
                transform: translateX(-300px);
            }

            .nav-links .back-home {
                font-size: 1.2rem;
                width: 100%;
                justify-content: center;
                padding: 15px;
            }

            .login-wrapper {
                flex-direction: column;
                max-width: 500px;
            }

            .login-info,
            .login-form-section {
                padding: 40px 30px;
            }

            .login-info h1 {
                font-size: 2rem;
            }

            .login-form-section h2 {
                font-size: 1.8rem;
            }

            .remember-forgot {
                flex-direction: column;
                align-items: flex-start;
            }
        }

        @media (max-width: 480px) {

            .login-info,
            .login-form-section {
                padding: 30px 20px;
            }

            .login-info h1 {
                font-size: 1.8rem;
            }

            .login-form-section h2 {
                font-size: 1.6rem;
            }

            .social-login {
                gap: 10px;
            }

            .social-btn {
                width: 45px;
                height: 45px;
            }
        }
    </style>
</head>

<body>
    <div class="menu-overlay" id="menuOverlay"></div>

    <!-- Header -->
    <header>
        <div class="container">
            <nav>
                <a href="index.php" class="logo">
                    <i class="fas fa-car"></i>
                    <span>AutoTech</span>
                </a>
                <div class="menu-toggle" id="menuToggle">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
                <div class="nav-links" id="navLinks">
                    <a href="index.php" class="back-home">
                        <i class="fas fa-arrow-left"></i> Volver al inicio
                    </a>
                </div>
            </nav>
        </div>
    </header>

    <!-- Contenedor principal de login -->
    <main class="login-container">
        <div class="login-bg"></div>
        <div class="container">
            <div class="login-wrapper">
                <!-- Información del sistema -->
                <div class="login-info">
                    <h1>Acceso al Sistema</h1>
                    <p>Ingresa a tu cuenta para gestionar tus citas, revisar el historial de tu vehículo y acceder a servicios exclusivos.</p>

                    <ul class="features-list">
                        <li>
                            <i class="fas fa-calendar-check"></i>
                            <span>Gestiona tus citas de servicio</span>
                        </li>
                        <li>
                            <i class="fas fa-file-invoice-dollar"></i>
                            <span>Consulta facturas y presupuestos</span>
                        </li>
                        <li>
                            <i class="fas fa-car"></i>
                            <span>Revisa el historial de tu vehículo</span>
                        </li>
                        <li>
                            <i class="fas fa-bell"></i>
                            <span>Recibe notificaciones de mantenimiento</span>
                        </li>
                    </ul>
                </div>

                <!-- Formulario de login -->
                <div class="login-form-section">
                    <h2>Iniciar Sesión</h2>
                    <p>Ingresa tus credenciales para acceder al sistema</p>

                    <form id="loginForm" action="controladores/login.php" method="post">
                        <?php if (isset($_SESSION['errores'])) : ?>
                            <div class=" alert alert-danger">
                                <ul>
                                    <?php foreach ($_SESSION['errores'] as $error) : ?>
                                        <li><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                            <?php unset($_SESSION['errores']); ?>
                        <?php endif; ?>
                        <div class="form-group">
                            <label for="email">Correo Electrónico</label>
                            <div class="input-with-icon">
                                <i class="fas fa-envelope"></i>
                                <input type="email" id="email" name="email" placeholder="ejemplo@correo.com" required>
                            </div>
                            <div class="validation-message" id="emailError">
                                <i class="fas fa-exclamation-circle"></i> Por favor ingresa un correo válido
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="password">Contraseña</label>
                            <div class="input-with-icon">
                                <i class="fas fa-lock"></i>
                                <input type="password" id="password" name="password" placeholder="Ingresa tu contraseña" required>
                                <button type="button" class="password-toggle" id="togglePassword">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <div class="validation-message" id="passwordError">
                                <i class="fas fa-exclamation-circle"></i> La contraseña debe tener al menos 6 caracteres
                            </div>
                        </div>

                        <div class="remember-forgot">
                            <div class="remember-me">
                                <input type="checkbox" id="remember">
                                <label for="remember">Recordar sesión</label>
                            </div>
                            <a href="#" class="forgot-password" id="forgotPassword">
                                ¿Olvidaste tu contraseña?
                            </a>
                        </div>

                        <button type="submit" class="login-btn">
                            <i class="fas fa-sign-in-alt"></i> Iniciar Sesión
                        </button>
                    </form>

                    <div class="divider">o accede con</div>

                    <div class="social-login">
                        <button class="social-btn google" title="Iniciar con Google">
                            <i class="fab fa-google"></i>
                        </button>
                        <button class="social-btn facebook" title="Iniciar con Facebook">
                            <i class="fab fa-facebook-f"></i>
                        </button>
                        <button class="social-btn apple" title="Iniciar con Apple">
                            <i class="fab fa-apple"></i>
                        </button>
                    </div>

                    <div class="register-link">
                        ¿No tienes una cuenta?
                        <a href="#" id="registerLink">Solicita acceso aquí</a>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Modal de recuperación de contraseña -->
    <div class="modal-overlay" id="passwordModal">
        <button class="close-modal" id="closeModal">
            <i class="fas fa-times"></i>
        </button>
        <div class="modal-content">
            <h3>Recuperar Contraseña</h3>
            <p>Ingresa tu correo electrónico y te enviaremos un enlace para restablecer tu contraseña.</p>

            <div class="form-group">
                <label for="recoveryEmail">Correo Electrónico</label>
                <div class="input-with-icon">
                    <i class="fas fa-envelope"></i>
                    <input type="email" id="recoveryEmail" placeholder="ejemplo@correo.com">
                </div>
            </div>

            <button class="modal-btn" id="sendRecovery">
                <i class="fas fa-paper-plane"></i> Enviar enlace de recuperación
            </button>

            <p style="margin-top: 20px; font-size: 0.9rem; color: var(--gray-color);">
                <i class="fas fa-info-circle"></i> Si no recibes el correo en unos minutos, revisa tu carpeta de spam.
            </p>
        </div>
    </div>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="footer-links">
                <a href="#">Términos de Servicio</a>
                <a href="#">Política de Privacidad</a>
                <a href="#">Soporte Técnico</a>
                <a href="#">Contacto</a>
            </div>
            <div class="copyright">
                <p>&copy; 2023 AutoTech Taller Mecánico. Sistema de gestión para clientes.</p>
            </div>
        </div>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const menuToggle = document.getElementById('menuToggle');
            const navLinks = document.getElementById('navLinks');
            const menuOverlay = document.getElementById('menuOverlay');
            const togglePassword = document.getElementById('togglePassword');
            const passwordInput = document.getElementById('password');
            const forgotPassword = document.getElementById('forgotPassword');
            const passwordModal = document.getElementById('passwordModal');
            const closeModal = document.getElementById('closeModal');

            // Responsive Menu Toggle
            if (menuToggle && navLinks && menuOverlay) {
                const toggleMenu = () => {
                    menuToggle.classList.toggle('active');
                    navLinks.classList.toggle('active');
                    menuOverlay.classList.toggle('active');
                    document.body.style.overflow = navLinks.classList.contains('active') ? 'hidden' : 'auto';
                };

                menuToggle.addEventListener('click', toggleMenu);
                menuOverlay.addEventListener('click', toggleMenu);

                // Close menu when clicking on links
                navLinks.querySelectorAll('a').forEach(link => {
                    link.addEventListener('click', () => {
                        if (navLinks.classList.contains('active')) {
                            toggleMenu();
                        }
                    });
                });
            }

            // Password Toggle
            if (togglePassword && passwordInput) {
                togglePassword.addEventListener('click', function(e) {
                    e.preventDefault(); // Prevent any form submission or focus issues
                    const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                    passwordInput.setAttribute('type', type);

                    const icon = this.querySelector('i');
                    if (type === 'text') {
                        icon.classList.remove('fa-eye');
                        icon.classList.add('fa-eye-slash');
                    } else {
                        icon.classList.remove('fa-eye-slash');
                        icon.classList.add('fa-eye');
                    }
                });
            }

            // Modal Controls
            if (forgotPassword) {
                forgotPassword.addEventListener('click', function(e) {
                    e.preventDefault();
                    passwordModal.classList.add('active');
                    document.body.style.overflow = 'hidden';
                });
            }

            if (closeModal) {
                closeModal.addEventListener('click', function() {
                    passwordModal.classList.remove('active');
                    document.body.style.overflow = 'auto';
                });
            }

            window.addEventListener('click', function(e) {
                if (e.target === passwordModal) {
                    passwordModal.classList.remove('active');
                    document.body.style.overflow = 'auto';
                }
            });
        });
    </script>
</body>

</html>