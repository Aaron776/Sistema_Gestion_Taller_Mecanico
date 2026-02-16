<header class="header">
    <div class="header-left">
        <h1>Panel de Control</h1>
    </div>
    <div class="header-right">
        <div class="header-search">
            <i class="fas fa-search"></i>
            <input type="text" placeholder="Buscar...">
        </div>

        <?php if ($_SESSION['rol'] === 'admin'): ?>
            <?php
            // ==========================================
            // LÓGICA PHP (BACKEND)
            // ==========================================
            // 1. Incluimos la conexión a la base de datos
            require_once __DIR__ . '/../conexion/bd.php';

            // 2. Preparamos la consulta para obtener notificaciones NO leídas
            // Filtramos por leido='No' y ordenamos por fecha descendente (más nuevas primero)
            // Limitamos a 5 para no saturar la vista
            $stmt = $conexion->prepare("SELECT * FROM notificaciones WHERE leido = 'No' ORDER BY fecha DESC LIMIT 5");
            $stmt->execute();

            // 3. Obtenemos los resultados como objetos
            $notificaciones = $stmt->fetchAll(PDO::FETCH_OBJ);

            // 4. Contamos el número de notificaciones para el badge rojo
            $count_notif = count($notificaciones);
            ?>

            <!-- ==========================================
                 ESTRUCTURA HTML (INTERFAZ)
                 ========================================== -->
            <div class="header-notifications">
                <!-- Botón de la campana con evento onclick para abrir/cerrar -->
                <button class="notification-btn" id="notificationBtn" onclick="toggleNotifications()">
                    <i class="fas fa-bell"></i>
                    <!-- Solo mostramos el badge rojo si hay notificaciones > 0 -->
                    <?php if ($count_notif > 0): ?>
                        <span class="notification-badge"><?php echo $count_notif; ?></span>
                    <?php endif; ?>
                </button>

                <!-- Menú Desplegable (Oculto por defecto con CSS) -->
                <div class="notification-dropdown" id="notificationDropdown">
                    <div class="notification-header">
                        <h3>Notificaciones</h3>
                        <?php if ($count_notif > 0): ?>
                            <span class="badge badge-warning"><?php echo $count_notif; ?> Nuevas</span>
                        <?php endif; ?>
                    </div>

                    <div class="notification-list">
                        <!-- Si hay notificaciones, las recorremos con un bucle foreach -->
                        <?php if ($count_notif > 0): ?>
                            <?php foreach ($notificaciones as $notif): ?>
                                <!-- Clase dinámica según tipo (warning, success, info) para colores -->
                                <div class="notification-item <?php echo $notif->tipo; ?>">
                                    <div class="notif-icon">
                                        <!-- Icono dinámico según el tipo de notificación -->
                                        <?php if ($notif->tipo == 'warning'): ?>
                                            <i class="fas fa-exclamation-triangle"></i>
                                        <?php elseif ($notif->tipo == 'success'): ?>
                                            <i class="fas fa-check-circle"></i>
                                        <?php else: ?>
                                            <i class="fas fa-info-circle"></i>
                                        <?php endif; ?>
                                    </div>
                                    <div class="notif-content">
                                        <p class="notif-title"><?php echo htmlspecialchars($notif->titulo); ?></p>
                                        <p class="notif-message"><?php echo htmlspecialchars($notif->mensaje); ?></p>
                                        <span class="notif-time"><?php echo date('d/m H:i', strtotime($notif->fecha)); ?></span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <!-- Mensaje si no hay notificaciones nuevas -->
                            <div class="no-notifications">
                                <i class="far fa-bell-slash"></i>
                                <p>No tienes notificaciones nuevas</p>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="notification-footer">
                        <a href="#">Ver todas las acciones</a>
                    </div>
                </div>
            </div>

            <!-- ==========================================
                 ESTILOS CSS (DISEÑO)
                 ========================================== -->
            <style>
                /* Contenedor relativo para posicionar el dropdown absoluto respecto a él */
                .header-notifications {
                    position: relative;
                }

                /* Estilos del menú desplegable */
                .notification-dropdown {
                    display: none;
                    /* Oculto por defecto */
                    position: absolute;
                    top: 100%;
                    /* Justo debajo del botón */
                    right: 0;
                    /* Alineado a la derecha */
                    width: 320px;
                    background: white;
                    border-radius: 8px;
                    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.15);
                    z-index: 1000;
                    /* Por encima de todo */
                    margin-top: 15px;
                    border: 1px solid #e3e6f0;
                    animation: fadeIn 0.2s ease-out;
                    /* Animación de entrada */
                }

                /* Clase que agrega JS para mostrar el menú */
                .notification-dropdown.show {
                    display: block;
                }

                .notification-header {
                    padding: 15px;
                    border-bottom: 1px solid #e3e6f0;
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                }

                .notification-header h3 {
                    font-size: 1rem;
                    margin: 0;
                    color: #1a3a5f;
                    font-weight: 700;
                }

                .notification-list {
                    max-height: 300px;
                    /* Scroll si hay muchas notificaciones */
                    overflow-y: auto;
                }

                .notification-item {
                    padding: 15px;
                    display: flex;
                    gap: 15px;
                    border-bottom: 1px solid #f8f9fc;
                    transition: background-color 0.2s;
                }

                .notification-item:hover {
                    background-color: #f8f9fc;
                }

                /* Colores específicos por tipo de notificación */
                .notification-item.warning .notif-icon {
                    color: #f6c23e;
                    background: rgba(246, 194, 62, 0.1);
                }

                .notification-item.success .notif-icon {
                    color: #1cc88a;
                    background: rgba(28, 200, 138, 0.1);
                }

                .notification-item.info .notif-icon {
                    color: #36b9cc;
                    background: rgba(54, 185, 204, 0.1);
                }

                .notif-icon {
                    width: 40px;
                    height: 40px;
                    border-radius: 50%;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    font-size: 1.2rem;
                    flex-shrink: 0;
                }

                .notif-content {
                    flex: 1;
                }

                .notif-title {
                    font-weight: 600;
                    font-size: 0.9rem;
                    margin-bottom: 3px;
                    color: #343a40;
                }

                .notif-message {
                    font-size: 0.85rem;
                    color: #6c757d;
                    margin-bottom: 5px;
                    line-height: 1.4;
                }

                .notif-time {
                    font-size: 0.75rem;
                    color: #adb5bd;
                }

                .no-notifications {
                    padding: 30px;
                    text-align: center;
                    color: #6c757d;
                }

                .no-notifications i {
                    font-size: 2rem;
                    margin-bottom: 10px;
                    opacity: 0.5;
                }

                .notification-footer {
                    padding: 10px;
                    text-align: center;
                    border-top: 1px solid #e3e6f0;
                    background-color: #f8f9fc;
                    border-bottom-left-radius: 8px;
                    border-bottom-right-radius: 8px;
                }

                .notification-footer a {
                    color: #1a3a5f;
                    font-size: 0.85rem;
                    font-weight: 600;
                    text-decoration: none;
                }

                .notification-footer a:hover {
                    text-decoration: underline;
                }

                @keyframes fadeIn {
                    from {
                        opacity: 0;
                        transform: translateY(-10px);
                    }

                    to {
                        opacity: 1;
                        transform: translateY(0);
                    }
                }
            </style>

            <!-- ==========================================
                 JAVASCRIPT (INTERACTIVIDAD)
                 ========================================== -->
            <script>
                // Función para mostrar/ocultar el dropdown al hacer click
                function toggleNotifications() {
                    const dropdown = document.getElementById('notificationDropdown');
                    const badge = document.querySelector('.notification-badge');
                    const headerBadge = document.querySelector('.notification-header .badge');
                    const isOpening = !dropdown.classList.contains('show');

                    dropdown.classList.toggle('show');

                    // Si se está abriendo el menú y hay notificaciones (badge existe)
                    if (isOpening && badge) {
                        // Llamada AJAX para marcar como leídas
                        fetch('../controladores/marcar_notificaciones_leidas.php', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/x-www-form-urlencoded',
                                }
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    // Ocultar badge del botón
                                    badge.style.display = 'none';
                                    // Actualizar badge del header (opcional, o quitarlo)
                                    if (headerBadge) {
                                        headerBadge.style.display = 'none';
                                        // O cambiar texto: headerBadge.textContent = '0 Nuevas';
                                    }
                                }
                            })
                            .catch(error => console.error('Error al marcar notificaciones:', error));
                    }
                }

                // Event listener para cerrar el menú si se hace click fuera de él
                document.addEventListener('click', function(event) {
                    const dropdown = document.getElementById('notificationDropdown');
                    const btn = document.getElementById('notificationBtn');

                    // Si el click NO fue en el dropdown Y NO fue en el botón...
                    if (!dropdown.contains(event.target) && !btn.contains(event.target)) {
                        // ...entonces cerramos el menú
                        dropdown.classList.remove('show');
                    }
                });
            </script>
        <?php endif; ?>
        <div class="user-menu" id="userMenu">
            <div class="user-avatar"><?php echo $_SESSION['nombre'][0] . $_SESSION['apellido'][0]; ?></div>
            <div class="user-info">
                <div class="user-name"><?php echo $_SESSION['nombre'] . " " . $_SESSION['apellido']; ?></div>
                <div class="user-role"><?php echo ucfirst($_SESSION['rol']); ?></div>
            </div>
        </div>
    </div>
</header>