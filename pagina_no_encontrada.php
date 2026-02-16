<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AutoTech - Página No Encontrada</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #1a3a5f;
            --secondary: #f8b400;
            --danger: #e63946;
            --success: #28a745;
            --warning: #ffc107;
            --info: #17a2b8;
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
            background: linear-gradient(135deg, #1e3c5a 0%, #0a1e2e 100%);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        /* Elementos de fondo animados - Estilo 404 */
        .bg-elements {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: 1;
        }

        .floating-number {
            position: absolute;
            font-size: 15rem;
            font-weight: 900;
            color: rgba(255, 255, 255, 0.03);
            font-family: 'Arial Black', sans-serif;
            user-select: none;
            animation: floatNumber 25s infinite linear;
        }

        .number1 {
            top: -50px;
            left: -50px;
            animation-delay: 0s;
        }

        .number2 {
            bottom: -80px;
            right: -30px;
            font-size: 20rem;
            animation-delay: 2s;
        }

        .number3 {
            top: 30%;
            right: 15%;
            font-size: 12rem;
            animation-delay: 4s;
        }

        .floating-car {
            position: absolute;
            color: rgba(248, 180, 0, 0.03);
            font-size: 10rem;
            bottom: 10%;
            left: 5%;
            animation: drive 30s infinite linear;
        }

        @keyframes floatNumber {
            0% {
                transform: translateY(0) rotate(0deg);
            }
            50% {
                transform: translateY(-30px) rotate(5deg);
            }
            100% {
                transform: translateY(0) rotate(0deg);
            }
        }

        @keyframes drive {
            0% {
                transform: translateX(-100%) rotate(0deg);
            }
            50% {
                transform: translateX(500%) rotate(5deg);
            }
            100% {
                transform: translateX(-100%) rotate(0deg);
            }
        }

        /* Contenedor principal */
        .container {
            position: relative;
            z-index: 2;
            width: 100%;
            max-width: 600px;
            padding: 20px;
        }

        /* Card de 404 */
        .not-found-card {
            background-color: white;
            border-radius: 30px;
            box-shadow: var(--shadow);
            padding: 50px 40px;
            text-align: center;
            animation: slideUp 0.6s ease-out;
            position: relative;
            overflow: hidden;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.2);
        }

        /* Barra superior decorativa */
        .not-found-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 8px;
            background: linear-gradient(90deg, var(--warning), var(--secondary), var(--info));
        }

        /* Número 404 grande de fondo */
        .big-404 {
            position: absolute;
            top: -20px;
            right: 20px;
            font-size: 12rem;
            font-weight: 900;
            color: rgba(26, 58, 95, 0.03);
            font-family: 'Arial Black', sans-serif;
            z-index: 1;
            line-height: 1;
            user-select: none;
        }

        /* Icono animado */
        .icon-wrapper {
            width: 140px;
            height: 140px;
            background: linear-gradient(135deg, #fff5e6, #fff0e0);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 30px;
            border: 6px solid white;
            box-shadow: 0 15px 30px rgba(248, 180, 0, 0.15);
            position: relative;
            z-index: 2;
            animation: bounce 3s infinite ease-in-out;
        }

        .icon-wrapper i {
            font-size: 65px;
            color: var(--secondary);
            animation: spinSlow 10s infinite linear;
        }

        /* Títulos */
        h1 {
            font-size: 8rem;
            font-weight: 900;
            color: var(--primary);
            margin-bottom: 0;
            line-height: 0.8;
            text-shadow: 5px 5px 0 rgba(248, 180, 0, 0.1);
            position: relative;
            z-index: 2;
        }

        .title-404 {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-bottom: 10px;
        }

        .title-404 span {
            font-size: 2rem;
            color: var(--gray);
            font-weight: 400;
        }

        .subtitle {
            font-size: 1.5rem;
            color: var(--dark);
            margin-bottom: 20px;
            font-weight: 600;
            position: relative;
            z-index: 2;
        }

        /* Mensaje de error */
        .error-message {
            background-color: #fff8f0;
            border-left: 4px solid var(--warning);
            padding: 20px 25px;
            border-radius: 12px;
            margin-bottom: 30px;
            text-align: left;
            display: flex;
            align-items: flex-start;
            gap: 15px;
            position: relative;
            z-index: 2;
        }

        .error-message i {
            color: var(--warning);
            font-size: 1.3rem;
            margin-top: 2px;
        }

        .error-message p {
            color: var(--dark);
            line-height: 1.6;
            margin: 0;
            font-size: 0.95rem;
        }

        .error-message strong {
            color: var(--primary);
        }

        /* Sugerencias */
        .suggestions {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin-bottom: 35px;
            position: relative;
            z-index: 2;
        }

        .suggestion-item {
            background-color: var(--light);
            padding: 15px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            gap: 12px;
            transition: var(--transition);
            cursor: pointer;
            border: 2px solid transparent;
        }

        .suggestion-item:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.08);
            border-color: var(--secondary);
            background-color: white;
        }

        .suggestion-item i {
            color: var(--primary);
            font-size: 1.2rem;
            width: 30px;
            text-align: center;
        }

        .suggestion-content {
            text-align: left;
        }

        .suggestion-title {
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 3px;
            font-size: 0.95rem;
        }

        .suggestion-desc {
            color: var(--gray);
            font-size: 0.8rem;
        }

        /* Botones de acción */
        .action-buttons {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 20px;
            position: relative;
            z-index: 2;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            padding: 16px 24px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 1rem;
            border: none;
            cursor: pointer;
            transition: var(--transition);
            text-decoration: none;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary) 0%, #0f2a46 100%);
            color: white;
            box-shadow: 0 5px 20px rgba(26, 58, 95, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(26, 58, 95, 0.4);
            background: white;
            color: var(--primary);
            border: 2px solid var(--primary);
            padding: 14px 22px;
        }

        .btn-secondary {
            background-color: white;
            color: var(--primary);
            border: 2px solid var(--primary);
        }

        .btn-secondary:hover {
            background: linear-gradient(135deg, var(--primary) 0%, #0f2a46 100%);
            color: white;
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(26, 58, 95, 0.3);
            border-color: transparent;
        }

        .btn i {
            font-size: 1rem;
            transition: var(--transition);
        }

        /* Búsqueda */
        .search-section {
            margin-top: 25px;
            padding-top: 25px;
            border-top: 1px solid #e3e6f0;
            position: relative;
            z-index: 2;
        }

        .search-label {
            color: var(--gray);
            font-size: 0.9rem;
            margin-bottom: 12px;
            display: block;
            text-align: left;
        }

        .search-box {
            position: relative;
            display: flex;
            gap: 10px;
        }

        .search-box input {
            flex: 1;
            padding: 14px 20px 14px 50px;
            border: 2px solid #e3e6f0;
            border-radius: 50px;
            font-size: 0.95rem;
            transition: var(--transition);
        }

        .search-box input:focus {
            outline: none;
            border-color: var(--secondary);
            box-shadow: 0 0 0 4px rgba(248, 180, 0, 0.1);
        }

        .search-box i {
            position: absolute;
            left: 20px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray);
            font-size: 1rem;
        }

        .search-btn {
            background-color: var(--secondary);
            color: var(--dark);
            border: none;
            border-radius: 50px;
            padding: 0 25px;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .search-btn:hover {
            background-color: #e0a800;
            transform: translateX(3px);
        }

        /* Footer */
        .footer {
            margin-top: 25px;
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.85rem;
            text-align: center;
            position: relative;
            z-index: 2;
        }

        .footer a {
            color: white;
            text-decoration: none;
            font-weight: 600;
            transition: var(--transition);
        }

        .footer a:hover {
            color: var(--secondary);
            text-decoration: underline;
        }

        /* Animaciones */
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes bounce {
            0%, 100% {
                transform: translateY(0);
            }
            50% {
                transform: translateY(-15px);
            }
        }

        @keyframes spinSlow {
            from {
                transform: rotate(0deg);
            }
            to {
                transform: rotate(360deg);
            }
        }

        /* Responsive */
        @media (max-width: 768px) {
            .container {
                padding: 15px;
            }

            .not-found-card {
                padding: 35px 25px;
            }

            h1 {
                font-size: 6rem;
            }

            .title-404 span {
                font-size: 1.5rem;
            }

            .subtitle {
                font-size: 1.2rem;
            }

            .big-404 {
                font-size: 10rem;
                top: -10px;
                right: 10px;
            }

            .icon-wrapper {
                width: 120px;
                height: 120px;
            }

            .icon-wrapper i {
                font-size: 55px;
            }

            .suggestions {
                grid-template-columns: 1fr;
                gap: 10px;
            }

            .action-buttons {
                grid-template-columns: 1fr;
            }

            .floating-number {
                font-size: 10rem;
            }

            .floating-car {
                font-size: 7rem;
            }
        }

        @media (max-width: 480px) {
            .not-found-card {
                padding: 30px 20px;
            }

            h1 {
                font-size: 4.5rem;
            }

            .title-404 span {
                font-size: 1.2rem;
            }

            .subtitle {
                font-size: 1.1rem;
            }

            .big-404 {
                font-size: 8rem;
                top: -5px;
                right: 5px;
            }

            .icon-wrapper {
                width: 100px;
                height: 100px;
                margin-bottom: 20px;
            }

            .icon-wrapper i {
                font-size: 45px;
            }

            .error-message {
                padding: 15px;
                flex-direction: column;
                align-items: flex-start;
                gap: 8px;
            }

            .search-box {
                flex-direction: column;
            }

            .search-btn {
                padding: 12px 25px;
                width: 100%;
                justify-content: center;
            }

            .floating-number {
                font-size: 7rem;
            }
        }
    </style>
</head>
<body>
    <!-- Elementos de fondo animados -->
    <div class="bg-elements">
        <div class="floating-number number1">404</div>
        <div class="floating-number number2">404</div>
        <div class="floating-number number3">404</div>
        <div class="floating-car">
            <i class="fas fa-car-side"></i>
        </div>
    </div>

    <!-- Contenedor principal -->
    <div class="container">
        <!-- Card de 404 -->
        <div class="not-found-card">
            <!-- Número 404 grande de fondo -->
            <div class="big-404">404</div>

            <!-- Icono animado -->
            <div class="icon-wrapper">
                <i class="fas fa-compass"></i>
            </div>

            <!-- Título 404 -->
            <div class="title-404">
                <h1>404</h1>
                <span>Error</span>
            </div>
            
            <div class="subtitle">
                <i class="fas fa-map-signs" style="color: var(--secondary); margin-right: 8px;"></i>
                Página no encontrada
            </div>

            <!-- Mensaje de error -->
            <div class="error-message">
                <i class="fas fa-road"></i>
                <div>
                    <p><strong>¡Ups! Parece que te has desviado del camino.</strong></p>
                    <p>La página que estás buscando no existe, ha sido movida o está temporalmente fuera de servicio. Hemos registrado este error para revisarlo.</p>
                </div>
            </div>

            <!-- Sugerencias -->
            <div class="suggestions">
                <div class="suggestion-item">
                    <i class="fas fa-home"></i>
                    <div class="suggestion-content">
                        <div class="suggestion-title">Ir al Inicio</div>
                        <div class="suggestion-desc">Volver al dashboard principal</div>
                    </div>
                </div>
                <div class="suggestion-item">
                    <i class="fas fa-clipboard-list"></i>
                    <div class="suggestion-content">
                        <div class="suggestion-title">Ver Órdenes</div>
                        <div class="suggestion-desc">Gestionar órdenes activas</div>
                    </div>
                </div>
                <div class="suggestion-item">
                    <i class="fas fa-users"></i>
                    <div class="suggestion-content">
                        <div class="suggestion-title">Clientes</div>
                        <div class="suggestion-desc">Administrar clientes</div>
                    </div>
                </div>
                <div class="suggestion-item">
                    <i class="fas fa-cog"></i>
                    <div class="suggestion-content">
                        <div class="suggestion-title">Configuración</div>
                        <div class="suggestion-desc">Ajustes del sistema</div>
                    </div>
                </div>
            </div>

            <!-- Botones de acción -->
            <div class="action-buttons">
                <a href="../index.php" class="btn btn-primary">
                    <i class="fas fa-arrow-left"></i>
                    Volver al Login
                </a>
                <a href="#" class="btn btn-secondary">
                    <i class="fas fa-home"></i>
                    Página Principal
                </a>
            </div>

            <!-- Buscador -->
            <div class="search-section">
                <span class="search-label">
                    <i class="fas fa-search" style="margin-right: 5px;"></i>
                    ¿Buscas algo específico?
                </span>
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" placeholder="Buscar páginas, clientes, órdenes...">
                    <button class="search-btn">
                        <i class="fas fa-arrow-right"></i>
                        Buscar
                    </button>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>
                <i class="fas fa-exclamation-triangle" style="margin-right: 5px;"></i>
                Error 404 - Página no encontrada | 
                <a href="#">Reportar este problema</a> | 
                <a href="#">Centro de ayuda</a>
            </p>
            <p style="margin-top: 8px; font-size: 0.8rem;">
                &copy; 2024 AutoTech - Sistema de Gestión Automotriz. Todos los derechos reservados.
            </p>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Elementos del DOM
            const searchInput = document.querySelector('.search-box input');
            const searchBtn = document.querySelector('.search-btn');
            const suggestionItems = document.querySelectorAll('.suggestion-item');
            const homeBtn = document.querySelector('.btn-secondary');
            const loginBtn = document.querySelector('.btn-primary');

            // Efecto de entrada
            console.log('=== ERROR 404 ===');
            console.log('Página no encontrada');
            console.log('URL:', window.location.href);
            console.log('Timestamp:', new Date().toLocaleString());
            console.log('=================');

            // Animación del icono de brújula
            const compassIcon = document.querySelector('.icon-wrapper i');
            let rotation = 0;
            setInterval(() => {
                rotation = (rotation + 1) % 360;
                compassIcon.style.transform = `rotate(${rotation}deg)`;
            }, 50);

            // Búsqueda
            function performSearch() {
                const searchTerm = searchInput.value.trim();
                if (searchTerm) {
                    console.log('Buscando:', searchTerm);
                    alert(`Buscando "${searchTerm}"...\nFuncionalidad de búsqueda en desarrollo.`);
                } else {
                    alert('Por favor ingresa un término de búsqueda');
                    searchInput.focus();
                }
            }

            searchBtn.addEventListener('click', performSearch);
            
            searchInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    performSearch();
                }
            });

            // Sugerencias interactivas
            suggestionItems.forEach(item => {
                item.addEventListener('click', function() {
                    const title = this.querySelector('.suggestion-title').textContent;
                    const desc = this.querySelector('.suggestion-desc').textContent;
                    console.log(`Navegando a: ${title} - ${desc}`);
                    alert(`Redirigiendo a: ${title}\n${desc}`);
                });

                // Animación al pasar el mouse
                item.addEventListener('mouseenter', function() {
                    const icon = this.querySelector('i');
                    icon.style.transform = 'scale(1.2) rotate(10deg)';
                    icon.style.transition = 'transform 0.3s ease';
                });

                item.addEventListener('mouseleave', function() {
                    const icon = this.querySelector('i');
                    icon.style.transform = 'scale(1) rotate(0deg)';
                });
            });

            // Botones de navegación
            loginBtn.addEventListener('click', function(e) {
                e.preventDefault();
                const originalText = this.innerHTML;
                this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Redirigiendo...';
                
                setTimeout(() => {
                    this.innerHTML = originalText;
                    console.log('Redirigiendo al login...');
                    // window.location.href = '../index.php';
                }, 1000);
            });

            homeBtn.addEventListener('click', function(e) {
                e.preventDefault();
                const originalText = this.innerHTML;
                this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Cargando...';
                
                setTimeout(() => {
                    this.innerHTML = originalText;
                    console.log('Redirigiendo a página principal...');
                    alert('Redirigiendo a la página principal');
                }, 1000);
            });

            // Simular contador regresivo para redirección automática (opcional)
            let autoRedirect = false;
            if (autoRedirect) {
                let countdown = 10;
                const footer = document.querySelector('.footer p:first-child');
                
                const interval = setInterval(() => {
                    if (countdown > 0) {
                        footer.innerHTML = `<i class="fas fa-exclamation-triangle" style="margin-right: 5px;"></i>
                            Error 404 - Redirección automática en ${countdown} segundos | 
                            <a href="#">Cancelar</a>`;
                        countdown--;
                    } else {
                        clearInterval(interval);
                        window.location.href = '../index.php';
                    }
                }, 1000);
            }

            // Efecto de escritura en el placeholder del buscador
            const placeholders = [
                'Buscar páginas, clientes, órdenes...',
                'Ej: órdenes de trabajo',
                'Ej: clientes registrados',
                'Ej: facturación mensual'
            ];
            let placeholderIndex = 0;
            
            setInterval(() => {
                placeholderIndex = (placeholderIndex + 1) % placeholders.length;
                searchInput.placeholder = placeholders[placeholderIndex];
            }, 3000);

            // Efecto parallax en números flotantes
            document.addEventListener('mousemove', function(e) {
                const numbers = document.querySelectorAll('.floating-number');
                const mouseX = e.clientX / window.innerWidth;
                const mouseY = e.clientY / window.innerHeight;
                
                numbers.forEach((num, index) => {
                    const speed = (index + 1) * 20;
                    const x = (window.innerWidth * mouseX - num.offsetLeft) / speed;
                    const y = (window.innerHeight * mouseY - num.offsetTop) / speed;
                    num.style.transform = `translate(${x}px, ${y}px)`;
                });
            });

            // Análisis de URL para sugerir páginas similares
            const currentPath = window.location.pathname;
            console.log('Intentando acceder a:', currentPath);
            
            // Sugerir páginas basadas en la URL
            if (currentPath.includes('orden') || currentPath.includes('order')) {
                document.querySelector('.suggestion-item:nth-child(2)').style.backgroundColor = '#fff3cd';
            } else if (currentPath.includes('client')) {
                document.querySelector('.suggestion-item:nth-child(3)').style.backgroundColor = '#fff3cd';
            }
        });
    </script>
</body>
</html>