<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AutoTech - Acceso No Autorizado</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #1a3a5f;
            --secondary: #f8b400;
            --danger: #e63946;
            --success: #28a745;
            --dark: #343a40;
            --gray: #6c757d;
            --light: #f8f9fa;
            --shadow: 0 0.5rem 2rem rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background: linear-gradient(135deg, var(--primary) 0%, #0f2a46 100%);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        /* Elementos de fondo animados */
        .bg-circles {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: 1;
        }

        .circle {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.03);
            animation: float 20s infinite;
        }

        .circle1 {
            width: 400px;
            height: 400px;
            top: -100px;
            right: -100px;
            animation-delay: 0s;
        }

        .circle2 {
            width: 300px;
            height: 300px;
            bottom: -50px;
            left: -50px;
            animation-delay: 2s;
        }

        .circle3 {
            width: 200px;
            height: 200px;
            bottom: 200px;
            right: 200px;
            animation-delay: 4s;
        }

        .circle4 {
            width: 150px;
            height: 150px;
            top: 200px;
            left: 100px;
            animation-delay: 6s;
        }

        @keyframes float {
            0%, 100% {
                transform: translateY(0) scale(1);
            }
            50% {
                transform: translateY(-30px) scale(1.05);
            }
        }

        /* Contenedor principal */
        .container {
            position: relative;
            z-index: 2;
            width: 100%;
            max-width: 500px;
            padding: 20px;
        }

        /* Card de acceso denegado */
        .access-denied-card {
            background-color: white;
            border-radius: 20px;
            box-shadow: var(--shadow);
            padding: 50px 40px;
            text-align: center;
            animation: slideIn 0.6s ease-out;
            position: relative;
            overflow: hidden;
        }

        /* Barra superior decorativa */
        .access-denied-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 8px;
            background: linear-gradient(90deg, var(--danger), #ff8c94);
        }

        /* Icono de error */
        .icon-wrapper {
            width: 120px;
            height: 120px;
            background: linear-gradient(135deg, #fee, #ffe5e5);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 30px;
            border: 4px solid white;
            box-shadow: 0 10px 20px rgba(230, 57, 70, 0.2);
            animation: pulse 2s infinite;
        }

        .icon-wrapper i {
            font-size: 60px;
            color: var(--danger);
            animation: shake 0.5s ease-in-out;
        }

        /* Títulos */
        h1 {
            font-size: 2.2rem;
            color: var(--danger);
            margin-bottom: 10px;
            font-weight: 700;
            letter-spacing: 1px;
        }

        .subtitle {
            font-size: 1.1rem;
            color: var(--dark);
            margin-bottom: 20px;
            font-weight: 600;
        }

        /* Mensaje de error */
        .error-message {
            background-color: #fff8f8;
            border-left: 4px solid var(--danger);
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 30px;
            text-align: left;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .error-message i {
            color: var(--danger);
            font-size: 1.2rem;
        }

        .error-message p {
            color: var(--dark);
            line-height: 1.6;
            margin: 0;
            font-size: 0.95rem;
        }

        /* Información adicional */
        .info-section {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 30px;
        }

        .info-item {
            background-color: var(--light);
            padding: 12px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: var(--transition);
        }

        .info-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .info-item i {
            color: var(--primary);
            font-size: 1.1rem;
            width: 25px;
        }

        .info-item span {
            color: var(--dark);
            font-size: 0.9rem;
            font-weight: 500;
        }

        /* Botón de volver al login */
        .btn-login {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            background: linear-gradient(135deg, var(--primary) 0%, #0f2a46 100%);
            color: white;
            text-decoration: none;
            padding: 16px 32px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 1.1rem;
            border: none;
            cursor: pointer;
            transition: var(--transition);
            box-shadow: 0 5px 20px rgba(26, 58, 95, 0.3);
            margin-bottom: 20px;
            width: 100%;
            border: 2px solid transparent;
        }

        .btn-login:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(26, 58, 95, 0.4);
            background: white;
            color: var(--primary);
            border-color: var(--primary);
        }

        .btn-login:hover i {
            color: var(--primary);
        }

        .btn-login i {
            font-size: 1.1rem;
            transition: var(--transition);
        }

        /* Enlace de ayuda */
        .help-link {
            color: var(--gray);
            font-size: 0.9rem;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            transition: var(--transition);
        }

        .help-link:hover {
            color: var(--primary);
        }

        .help-link i {
            font-size: 0.9rem;
        }

        /* Logo */
        .logo {
            margin-bottom: 20px;
        }

        .logo i {
            font-size: 2.5rem;
            color: var(--secondary);
            margin-bottom: 5px;
        }

        .logo span {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary);
            display: block;
        }

        /* Footer */
        .footer {
            margin-top: 20px;
            color: rgba(255, 255, 255, 0.6);
            font-size: 0.85rem;
            text-align: center;
        }

        /* Animaciones */
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes pulse {
            0% {
                box-shadow: 0 0 0 0 rgba(230, 57, 70, 0.4);
            }
            70% {
                box-shadow: 0 0 0 20px rgba(230, 57, 70, 0);
            }
            100% {
                box-shadow: 0 0 0 0 rgba(230, 57, 70, 0);
            }
        }

        @keyframes shake {
            0%, 100% {
                transform: translateX(0);
            }
            10%, 30%, 50%, 70%, 90% {
                transform: translateX(-5px);
            }
            20%, 40%, 60%, 80% {
                transform: translateX(5px);
            }
        }

        /* Responsive */
        @media (max-width: 576px) {
            .container {
                padding: 15px;
            }

            .access-denied-card {
                padding: 30px 20px;
            }

            h1 {
                font-size: 1.8rem;
            }

            .subtitle {
                font-size: 1rem;
            }

            .icon-wrapper {
                width: 100px;
                height: 100px;
            }

            .icon-wrapper i {
                font-size: 50px;
            }

            .info-section {
                grid-template-columns: 1fr;
                gap: 10px;
            }

            .btn-login {
                padding: 14px 28px;
                font-size: 1rem;
            }

            .circle1 {
                width: 250px;
                height: 250px;
            }

            .circle2 {
                width: 200px;
                height: 200px;
            }
        }

        @media (max-width: 380px) {
            .access-denied-card {
                padding: 25px 15px;
            }

            h1 {
                font-size: 1.5rem;
            }

            .icon-wrapper {
                width: 80px;
                height: 80px;
                margin-bottom: 20px;
            }

            .icon-wrapper i {
                font-size: 40px;
            }

            .btn-login {
                padding: 12px 24px;
            }
        }
    </style>
</head>
<body>
    <!-- Elementos de fondo animados -->
    <div class="bg-circles">
        <div class="circle circle1"></div>
        <div class="circle circle2"></div>
        <div class="circle circle3"></div>
        <div class="circle circle4"></div>
    </div>

    <!-- Contenedor principal -->
    <div class="container">
        <!-- Card de acceso denegado -->
        <div class="access-denied-card">
            <!-- Logo -->
            <div class="logo">
                <i class="fas fa-car"></i>
                <span>AutoTech</span>
            </div>

            <!-- Icono de error animado -->
            <div class="icon-wrapper">
                <i class="fas fa-lock"></i>
            </div>

            <!-- Títulos -->
            <h1>¡ACCESO NO AUTORIZADO!</h1>
            <div class="subtitle">No tienes permisos para acceder a esta área</div>

            <!-- Mensaje de error -->
            <div class="error-message">
                <i class="fas fa-exclamation-triangle"></i>
                <p>Tu cuenta no tiene los privilegios necesarios para acceder a este recurso. Si crees que esto es un error, por favor contacta al administrador del sistema.</p>
            </div>

            <!-- Información adicional -->
            <div class="info-section">
                <div class="info-item">
                    <i class="fas fa-user-shield"></i>
                    <span>Rol insuficiente</span>
                </div>
                <div class="info-item">
                    <i class="fas fa-ban"></i>
                    <span>Acceso restringido</span>
                </div>
                <div class="info-item">
                    <i class="fas fa-clock"></i>
                    <span>Sesión no válida</span>
                </div>
                <div class="info-item">
                    <i class="fas fa-shield-alt"></i>
                    <span>Área protegida</span>
                </div>
            </div>

            <!-- Botón para volver al login -->
            <a href="index.php" class="btn-login">
                <i class="fas fa-sign-in-alt"></i>
                Volver al Inicio de Sesión
            </a>

            <!-- Enlace de ayuda -->
            <a href="#" class="help-link">
                <i class="fas fa-question-circle"></i>
                ¿Necesitas ayuda? Contacta al administrador
            </a>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>&copy; <?php echo date('Y'); ?> AutoTech - Sistema de Gestión Automotriz. Todos los derechos reservados.</p>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Efecto de shake adicional en el icono al cargar
            setTimeout(() => {
                const icon = document.querySelector('.icon-wrapper i');
                icon.style.animation = 'shake 0.5s ease-in-out';
                setTimeout(() => {
                    icon.style.animation = '';
                }, 500);
            }, 1000);

            // Simular redirección al hacer clic en el botón (para demostración)
            const btnLogin = document.querySelector('.btn-login');
            
            // Prevenir comportamiento por defecto solo para demostración
            btnLogin.addEventListener('click', function(e) {
                // Comentar esta línea para habilitar la redirección real
                // e.preventDefault();
                
                // Agregar efecto de carga al botón
                const originalText = this.innerHTML;
                this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Redirigiendo...';
                
                // Simular tiempo de carga
                setTimeout(() => {
                    this.innerHTML = originalText;
                    // Aquí iría la redirección real
                    console.log('Redirigiendo al login...');
                }, 1000);
            });

            // Efecto hover en las tarjetas de información
            const infoItems = document.querySelectorAll('.info-item');
            infoItems.forEach(item => {
                item.addEventListener('mouseenter', function() {
                    const icon = this.querySelector('i');
                    icon.style.transform = 'scale(1.2)';
                    icon.style.transition = 'transform 0.3s ease';
                });

                item.addEventListener('mouseleave', function() {
                    const icon = this.querySelector('i');
                    icon.style.transform = 'scale(1)';
                });
            });

            // Simulación de verificación de sesión
            console.log('=== VERIFICACIÓN DE SEGURIDAD ===');
            console.log('Estado: ACCESO DENEGADO');
            console.log('Razón: Privilegios insuficientes');
            console.log('Timestamp:', new Date().toLocaleString());
            console.log('================================');

            // Contador regresivo simulado (solo visual)
            let count = 5;
            const countdownInterval = setInterval(() => {
                if (count > 0) {
                    console.log(`Redirección automática en ${count} segundos...`);
                    count--;
                } else {
                    clearInterval(countdownInterval);
                }
            }, 1000);

            // Efecto de pulso en el botón
            setInterval(() => {
                btnLogin.style.transform = 'scale(1.02)';
                setTimeout(() => {
                    btnLogin.style.transform = 'scale(1)';
                }, 200);
            }, 5000);
        });
    </script>
</body>
</html>