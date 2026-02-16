<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Autotech - Taller Mecánico Especializado</title>
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
            overflow-x: hidden;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        /* Header y navegación */
        header {
            background-color: rgba(255, 255, 255, 0.95);
            box-shadow: var(--shadow);
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
            padding: 15px 0;
            transition: var(--transition);
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
        }

        .logo i {
            color: var(--secondary-color);
        }

        .nav-links {
            display: flex;
            gap: 30px;
            list-style: none;
        }

        .nav-links a {
            text-decoration: none;
            color: var(--dark-color);
            font-weight: 600;
            transition: var(--transition);
            position: relative;
        }

        .nav-links a:hover {
            color: var(--primary-color);
        }

        .nav-links a::after {
            content: '';
            position: absolute;
            width: 0;
            height: 3px;
            background-color: var(--secondary-color);
            bottom: -5px;
            left: 0;
            transition: var(--transition);
        }

        .nav-links a:hover::after {
            width: 100%;
        }

        /* Hero Section */
        .hero {
            height: 100vh;
            display: flex;
            align-items: center;
            position: relative;
            overflow: hidden;
            margin-top: 70px;
        }

        .hero-bg {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)), url('https://images.unsplash.com/photo-1549399542-7e3f8b79c341?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1374&q=80');
            background-size: cover;
            background-position: center;
            z-index: -1;
        }

        .hero-content {
            max-width: 700px;
            color: var(--light-color);
            animation: fadeInUp 1s ease-out;
        }

        .hero h1 {
            font-size: 3.5rem;
            margin-bottom: 20px;
            line-height: 1.2;
        }

        .hero p {
            font-size: 1.2rem;
            margin-bottom: 30px;
            line-height: 1.6;
            opacity: 0.9;
        }

        .hero-btns {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
        }

        .btn {
            padding: 15px 30px;
            border-radius: 50px;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            border: none;
            font-size: 1rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            text-decoration: none;
        }

        .btn-primary {
            background-color: var(--secondary-color);
            color: var(--dark-color);
        }

        .btn-primary:hover {
            background-color: #e6a300;
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
        }

        .btn-secondary {
            background-color: transparent;
            color: var(--light-color);
            border: 2px solid var(--light-color);
        }

        .btn-secondary:hover {
            background-color: rgba(255, 255, 255, 0.1);
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
        }

        /* Sección de servicios */
        .services {
            padding: 100px 0;
            background-color: var(--light-color);
        }

        .section-title {
            text-align: center;
            margin-bottom: 60px;
        }

        .section-title h2 {
            font-size: 2.5rem;
            color: var(--primary-color);
            margin-bottom: 15px;
            position: relative;
            display: inline-block;
        }

        .section-title h2::after {
            content: '';
            position: absolute;
            width: 80px;
            height: 4px;
            background-color: var(--secondary-color);
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
        }

        .services-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 30px;
        }

        .service-card {
            background-color: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: var(--shadow);
            transition: var(--transition);
            padding: 30px;
            text-align: center;
        }

        .service-card:hover {
            transform: translateY(-15px);
            box-shadow: 0 20px 30px rgba(0, 0, 0, 0.15);
        }

        .service-icon {
            font-size: 3rem;
            color: var(--primary-color);
            margin-bottom: 20px;
        }

        .service-card h3 {
            font-size: 1.5rem;
            margin-bottom: 15px;
            color: var(--primary-color);
        }

        .service-card p {
            color: var(--gray-color);
            line-height: 1.6;
        }

        /* Formulario de login */
        .login-overlay {
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

        .login-overlay.active {
            opacity: 1;
            visibility: visible;
        }

        .login-form {
            background-color: white;
            border-radius: 15px;
            padding: 40px;
            width: 90%;
            max-width: 450px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
            transform: translateY(-50px);
            transition: var(--transition);
        }

        .login-overlay.active .login-form {
            transform: translateY(0);
        }

        .login-form h2 {
            text-align: center;
            color: var(--primary-color);
            margin-bottom: 30px;
            font-size: 2rem;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--dark-color);
        }

        .form-group input {
            width: 100%;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 1rem;
            transition: var(--transition);
        }

        .form-group input:focus {
            border-color: var(--primary-color);
            outline: none;
            box-shadow: 0 0 0 3px rgba(26, 58, 95, 0.1);
        }

        .login-btn {
            width: 100%;
            padding: 15px;
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            margin-top: 10px;
        }

        .login-btn:hover {
            background-color: #0f2a46;
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }

        .close-btn {
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

        .close-btn:hover {
            transform: rotate(90deg);
            color: var(--secondary-color);
        }

        /* Footer */
        footer {
            background-color: var(--primary-color);
            color: var(--light-color);
            padding: 60px 0 30px;
        }

        .footer-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 40px;
            margin-bottom: 40px;
        }

        .footer-column h3 {
            font-size: 1.3rem;
            margin-bottom: 20px;
            color: var(--secondary-color);
        }

        .footer-column p,
        .footer-column a {
            color: rgba(255, 255, 255, 0.8);
            line-height: 1.8;
            text-decoration: none;
            transition: var(--transition);
        }

        .footer-column a:hover {
            color: var(--secondary-color);
            padding-left: 5px;
        }

        .social-icons {
            display: flex;
            gap: 15px;
            margin-top: 20px;
        }

        .social-icons a {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            background-color: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            transition: var(--transition);
        }

        .social-icons a:hover {
            background-color: var(--secondary-color);
            transform: translateY(-5px);
        }

        .copyright {
            text-align: center;
            padding-top: 30px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
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

        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        /* Responsive */
        @media (max-width: 992px) {
            .hero h1 {
                font-size: 2.8rem;
            }

            .nav-links {
                gap: 20px;
            }
        }

        @media (max-width: 768px) {
            header {
                padding: 10px 0;
            }

            .hero h1 {
                font-size: 2.5rem;
            }

            .hero p {
                font-size: 1.1rem;
            }

            /* Mobile Navigation Refinement */
            .nav-links {
                position: fixed;
                top: 0;
                right: -100%;
                width: 250px;
                height: 100vh;
                background-color: white;
                flex-direction: column;
                padding: 80px 30px;
                box-shadow: -5px 0 15px rgba(0, 0, 0, 0.1);
                transition: var(--transition);
                z-index: 999;
                display: flex;
            }

            .nav-links.active {
                right: 0;
            }

            .nav-links li {
                width: 100%;
            }

            .nav-links a {
                font-size: 1.1rem;
                display: block;
                padding: 10px 0;
            }

            .menu-toggle {
                display: block;
                font-size: 1.5rem;
                cursor: pointer;
                color: var(--primary-color);
                z-index: 1001;
            }

            #loginBtn {
                display: none;
                /* Hide on mobile to simplify header, but reconsider if needed */
            }

            #loginBtnMobile {
                display: inline-flex;
                margin-top: 20px;
            }

            .services-grid {
                grid-template-columns: 1fr;
            }

            .hero-btns {
                flex-direction: column;
                width: 100%;
            }

            .hero-btns .btn {
                width: 100%;
            }
        }

        /* Efectos de scroll */
        .fade-in {
            opacity: 0;
            transform: translateY(30px);
            transition: var(--transition);
        }

        .fade-in.visible {
            opacity: 1;
            transform: translateY(0);
        }
    </style>
</head>

<body>
    <!-- Header con navegación -->
    <header>
        <div class="container">
            <nav>
                <div class="logo">
                    <i class="fas fa-car"></i>
                    <span>AutoTech</span>
                </div>
                <ul class="nav-links" id="navLinks">
                    <li><a href="#home">Inicio</a></li>
                    <li><a href="#services">Servicios</a></li>
                    <li><a href="#about">Nosotros</a></li>
                    <li><a href="#contact">Contacto</a></li>
                    <li><a href="login.php" class="btn btn-primary" id="loginBtnMobile" style="display: none;">
                            <i class="fas fa-sign-in-alt"></i> Iniciar Sesión
                        </a></li>
                </ul>
                <a href="login.php" type="button" class="btn btn-primary" id="loginBtn">
                    <i class="fas fa-sign-in-alt"></i> Iniciar Sesión
                </a>
                <div class="menu-toggle" id="menuToggle">
                    <i class="fas fa-bars"></i>
                </div>
            </nav>
        </div>
    </header>

    <!-- Sección Hero -->
    <section class="hero" id="home">
        <div class="hero-bg"></div>
        <div class="container">
            <div class="hero-content">
                <h1>Expertos en Mecánica Automotriz</h1>
                <p>En AutoTech nos especializamos en el mantenimiento y reparación de vehículos con la más alta tecnología y personal calificado. Tu auto en las mejores manos.</p>
                <div class="hero-btns">
                    <a href="#services" class="btn btn-primary">
                        <i class="fas fa-tools"></i> Nuestros Servicios
                    </a>
                    <a href="#contact" class="btn btn-secondary">
                        <i class="fas fa-phone-alt"></i> Contactar Ahora
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Sección de Servicios -->
    <section class="services" id="services">
        <div class="container">
            <div class="section-title fade-in">
                <h2>Nuestros Servicios</h2>
                <p>Ofrecemos una amplia gama de servicios para mantener tu vehículo en óptimas condiciones</p>
            </div>
            <div class="services-grid">
                <div class="service-card fade-in">
                    <div class="service-icon">
                        <i class="fas fa-oil-can"></i>
                    </div>
                    <h3>Cambio de Aceite</h3>
                    <p>Servicio rápido y eficiente de cambio de aceite y filtros para prolongar la vida de tu motor.</p>
                </div>
                <div class="service-card fade-in">
                    <div class="service-icon">
                        <i class="fas fa-tachometer-alt"></i>
                    </div>
                    <h3>Diagnóstico Computarizado</h3>
                    <p>Identificamos problemas en sistemas electrónicos y mecánicos con equipos de última generación.</p>
                </div>
                <div class="service-card fade-in">
                    <div class="service-icon">
                        <i class="fas fa-car-battery"></i>
                    </div>
                    <h3>Sistema Eléctrico</h3>
                    <p>Reparación y mantenimiento de alternadores, baterías, arranques y sistema de carga.</p>
                </div>
                <div class="service-card fade-in">
                    <div class="service-icon">
                        <i class="fas fa-tire"></i>
                    </div>
                    <h3>Alineación y Balanceo</h3>
                    <p>Alineación precisa y balanceo de ruedas para mayor seguridad y durabilidad de tus neumáticos.</p>
                </div>
                <div class="service-card fade-in">
                    <div class="service-icon">
                        <i class="fas fa-temperature-high"></i>
                    </div>
                    <h3>Sistema de Enfriamiento</h3>
                    <p>Reparación y mantenimiento de radiadores, termostatos y sistemas de refrigeración.</p>
                </div>
                <div class="service-card fade-in">
                    <div class="service-icon">
                        <i class="fas fa-gas-pump"></i>
                    </div>
                    <h3>Inyección y Combustible</h3>
                    <p>Limpieza y reparación de sistemas de inyección de combustible para un óptimo rendimiento.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Overlay de Login -->
    <div class="login-overlay" id="loginOverlay">
        <button class="close-btn" id="closeLogin">
            <i class="fas fa-times"></i>
        </button>
        <div class="login-form">
            <h2>Iniciar Sesión</h2>
            <form id="loginForm">
                <div class="form-group">
                    <label for="email">Correo Electrónico</label>
                    <input type="email" id="email" placeholder="tucorreo@ejemplo.com" required>
                </div>
                <div class="form-group">
                    <label for="password">Contraseña</label>
                    <input type="password" id="password" placeholder="Ingresa tu contraseña" required>
                </div>
                <button type="submit" class="login-btn">
                    <i class="fas fa-sign-in-alt"></i> Ingresar al Sistema
                </button>
            </form>
            <p style="text-align: center; margin-top: 20px; color: var(--gray-color);">
                ¿No tienes cuenta? <a href="#" style="color: var(--primary-color); font-weight: 600;">Contáctanos</a>
            </p>
        </div>
    </div>

    <!-- Footer -->
    <footer id="contact">
        <div class="container">
            <div class="footer-content">
                <div class="footer-column">
                    <div class="logo" style="color: white; margin-bottom: 20px;">
                        <i class="fas fa-car"></i>
                        <span>AutoTech</span>
                    </div>
                    <p>Especialistas en mecánica automotriz con más de 15 años de experiencia. Calidad, confianza y tecnología para tu vehículo.</p>
                    <div class="social-icons">
                        <a href="#"><i class="fab fa-facebook-f"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-whatsapp"></i></a>
                    </div>
                </div>
                <div class="footer-column">
                    <h3>Horario de Atención</h3>
                    <p>Lunes a Viernes: 8:00 AM - 7:00 PM</p>
                    <p>Sábados: 9:00 AM - 4:00 PM</p>
                    <p>Domingos: Cerrado</p>
                </div>
                <div class="footer-column">
                    <h3>Contacto</h3>
                    <p><i class="fas fa-map-marker-alt"></i> Av. Automotriz 123, Ciudad</p>
                    <p><i class="fas fa-phone"></i> (555) 123-4567</p>
                    <p><i class="fas fa-envelope"></i> info@autotech.com</p>
                </div>
            </div>
            <div class="copyright">
                <p>&copy; 2023 AutoTech Taller Mecánico. Todos los derechos reservados.</p>
            </div>
        </div>
    </footer>

    <script>
        // Control del menú móvil
        const menuToggle = document.getElementById('menuToggle');
        const navLinks = document.getElementById('navLinks');
        const loginBtnMobile = document.getElementById('loginBtnMobile');

        menuToggle.addEventListener('click', () => {
            navLinks.classList.toggle('active');
            const icon = menuToggle.querySelector('i');
            if (navLinks.classList.contains('active')) {
                icon.className = 'fas fa-times';
            } else {
                icon.className = 'fas fa-bars';
            }
        });

        // Mostrar u ocultar el botón de login móvil según el ancho de pantalla
        const handleResize = () => {
            if (window.innerWidth <= 768) {
                loginBtnMobile.style.display = 'inline-flex';
            } else {
                loginBtnMobile.style.display = 'none';
                navLinks.classList.remove('active');
                menuToggle.querySelector('i').className = 'fas fa-bars';
            }
        };

        window.addEventListener('resize', handleResize);
        handleResize(); // Ejecutar al inicio

        // Control del formulario de login
        const loginBtn = document.getElementById('loginBtn');
        const loginOverlay = document.getElementById('loginOverlay');
        const closeLogin = document.getElementById('closeLogin');
        const loginForm = document.getElementById('loginForm');

        // Mostrar formulario de login
        loginBtn.addEventListener('click', () => {
            loginOverlay.classList.add('active');
            document.body.style.overflow = 'hidden';
        });

        // Ocultar formulario de login
        closeLogin.addEventListener('click', () => {
            loginOverlay.classList.remove('active');
            document.body.style.overflow = 'auto';
        });

        // Cerrar al hacer clic fuera del formulario
        loginOverlay.addEventListener('click', (e) => {
            if (e.target === loginOverlay) {
                loginOverlay.classList.remove('active');
                document.body.style.overflow = 'auto';
            }
        });

        // Manejo del envío del formulario
        loginForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;

            // Aquí normalmente se haría una petición al servidor
            // Simulamos un login exitoso
            if (email && password) {
                alert(`Inicio de sesión exitoso para: ${email}`);
                loginOverlay.classList.remove('active');
                document.body.style.overflow = 'auto';
                loginForm.reset();

                // Cambiar el botón de login por uno de perfil
                loginBtn.innerHTML = '<i class="fas fa-user"></i> Mi Perfil';
                loginBtn.style.backgroundColor = '#28a745';
            } else {
                alert('Por favor completa todos los campos');
            }
        });

        // Efecto de scroll para animaciones
        const fadeElements = document.querySelectorAll('.fade-in');

        const fadeInOnScroll = () => {
            fadeElements.forEach(element => {
                const elementTop = element.getBoundingClientRect().top;
                const windowHeight = window.innerHeight;

                if (elementTop < windowHeight - 100) {
                    element.classList.add('visible');
                }
            });
        };

        // Ejecutar al cargar y al hacer scroll
        window.addEventListener('scroll', fadeInOnScroll);
        window.addEventListener('load', fadeInOnScroll);

        // Cambiar estilo del header al hacer scroll
        window.addEventListener('scroll', () => {
            const header = document.querySelector('header');
            if (window.scrollY > 100) {
                header.style.padding = '10px 0';
                header.style.boxShadow = '0 5px 15px rgba(0,0,0,0.1)';
            } else {
                header.style.padding = '15px 0';
                header.style.boxShadow = '0 10px 20px rgba(0,0,0,0.1)';
            }
        });

        // Navegación suave
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();

                const targetId = this.getAttribute('href');
                if (targetId === '#') return;

                const targetElement = document.querySelector(targetId);
                if (targetElement) {
                    window.scrollTo({
                        top: targetElement.offsetTop - 80,
                        behavior: 'smooth'
                    });

                    // Cerrar el menú en móvil al hacer clic en un enlace
                    if (navLinks.classList.contains('active')) {
                        navLinks.classList.remove('active');
                        menuToggle.querySelector('i').className = 'fas fa-bars';
                    }
                }
            });
        });
    </script>
</body>

</html>