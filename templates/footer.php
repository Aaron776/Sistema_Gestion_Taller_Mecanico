 </div>

 <!-- Footer -->
 <footer class="footer">
     <p>&copy; 2023 AutoTech - Sistema de Gestión de Taller Mecánico. Todos los derechos reservados.</p>
 </footer>
 </main>
 </div>


 <!-- Scripts -->
 <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
 <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
 <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
 <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
 <script>
     // Toggle Sidebar
     const sidebarToggle = document.getElementById('sidebarToggle');
     const sidebar = document.getElementById('sidebar');
     const main = document.getElementById('main');

     sidebarToggle.addEventListener('click', () => {
         sidebar.classList.toggle('sidebar-collapsed');
         main.classList.toggle('expanded');
     });

     // Submenus del Sidebar
     // Submenus del Sidebar
     const vehiclesMenu = document.getElementById('vehiclesMenu');
     const vehiclesSubmenu = document.getElementById('vehiclesSubmenu');
     const servicesMenu = document.getElementById('servicesMenu');
     const servicesSubmenu = document.getElementById('servicesSubmenu');
     const inventoryMenu = document.getElementById('inventoryMenu');
     const inventorySubmenu = document.getElementById('inventorySubmenu');

     if (vehiclesMenu && vehiclesSubmenu) {
         vehiclesMenu.addEventListener('click', (e) => {
             e.preventDefault();
             vehiclesSubmenu.classList.toggle('show');
         });
     }

     if (servicesMenu && servicesSubmenu) {
         servicesMenu.addEventListener('click', (e) => {
             e.preventDefault();
             servicesMenu.classList.toggle('active');
             servicesSubmenu.classList.toggle('show');
         });
     }

     if (inventoryMenu && inventorySubmenu) {
         inventoryMenu.addEventListener('click', (e) => {
             e.preventDefault();
             inventorySubmenu.classList.toggle('show');
         });
     }

     // Modal de Notificaciones
     const notificationBtn = document.getElementById('notificationBtn');
     const notificationsModal = document.getElementById('notificationsModal');
     const closeNotificationsModal = document.getElementById('closeNotificationsModal');
     const markAllRead = document.getElementById('markAllRead');

     notificationBtn.addEventListener('click', () => {
         notificationsModal.classList.add('show');
     });

     closeNotificationsModal.addEventListener('click', () => {
         notificationsModal.classList.remove('show');
     });

     markAllRead.addEventListener('click', () => {
         const badge = document.querySelector('.notification-badge');
         badge.style.display = 'none';
         notificationsModal.classList.remove('show');
         alert('Todas las notificaciones han sido marcadas como leídas');
     });

     // Modal de Nuevo Servicio
     const addServiceBtn = document.getElementById('addServiceBtn');
     const newServiceModal = document.getElementById('newServiceModal');
     const closeServiceModal = document.getElementById('closeServiceModal');
     const cancelServiceBtn = document.getElementById('cancelServiceBtn');
     const saveServiceBtn = document.getElementById('saveServiceBtn');

     addServiceBtn.addEventListener('click', () => {
         newServiceModal.classList.add('show');
     });

     closeServiceModal.addEventListener('click', () => {
         newServiceModal.classList.remove('show');
     });

     cancelServiceBtn.addEventListener('click', () => {
         newServiceModal.classList.remove('show');
     });

     saveServiceBtn.addEventListener('click', () => {
         const vehicleSelect = document.getElementById('vehicleSelect');
         const serviceType = document.getElementById('serviceType');
         const serviceDate = document.getElementById('serviceDate');

         if (!vehicleSelect.value || !serviceType.value || !serviceDate.value) {
             alert('Por favor complete todos los campos obligatorios');
             return;
         }

         newServiceModal.classList.remove('show');
         alert('Servicio creado exitosamente');
         document.getElementById('newServiceForm').reset();
     });

     // Gráficos con Chart.js
     const servicesCtx = document.getElementById('servicesChart').getContext('2d');
     const vehiclesCtx = document.getElementById('vehiclesChart').getContext('2d');

     // Gráfico de Servicios por Mes
     const servicesChart = new Chart(servicesCtx, {
         type: 'line',
         data: {
             labels: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun'],
             datasets: [{
                 label: 'Servicios Realizados',
                 data: [12, 19, 15, 25, 22, 30],
                 backgroundColor: 'rgba(26, 58, 95, 0.1)',
                 borderColor: 'rgba(26, 58, 95, 1)',
                 borderWidth: 2,
                 tension: 0.4,
                 fill: true
             }]
         },
         options: {
             responsive: true,
             maintainAspectRatio: false,
             plugins: {
                 legend: {
                     display: false
                 }
             },
             scales: {
                 y: {
                     beginAtZero: true,
                     grid: {
                         drawBorder: false
                     }
                 },
                 x: {
                     grid: {
                         display: false
                     }
                 }
             }
         }
     });

     // Gráfico de Estado de Vehículos
     const vehiclesChart = new Chart(vehiclesCtx, {
         type: 'doughnut',
         data: {
             labels: ['En Proceso', 'Pendientes', 'Completados'],
             datasets: [{
                 data: [5, 3, 4],
                 backgroundColor: [
                     'rgba(40, 167, 69, 0.8)',
                     'rgba(255, 193, 7, 0.8)',
                     'rgba(26, 58, 95, 0.8)'
                 ],
                 borderWidth: 0
             }]
         },
         options: {
             responsive: true,
             maintainAspectRatio: false,
             plugins: {
                 legend: {
                     position: 'bottom'
                 }
             },
             cutout: '70%'
         }
     });

     // Menú de Usuario
     const userMenu = document.getElementById('userMenu');
     userMenu.addEventListener('click', () => {
         alert('Menú de usuario - En un sistema real aquí habría opciones de perfil, configuración, etc.');
     });

     // Cerrar modales al hacer clic fuera
     window.addEventListener('click', (e) => {
         if (e.target === notificationsModal) {
             notificationsModal.classList.remove('show');
         }
         if (e.target === newServiceModal) {
             newServiceModal.classList.remove('show');
         }
     });

     // Calendar Navigation
     const calendarPrev = document.querySelector('.calendar-widget .btn:first-child');
     const calendarNext = document.querySelector('.calendar-widget .btn:last-child');

     calendarPrev.addEventListener('click', () => {
         alert('Navegación a mes anterior - En una implementación real esto cambiaría el calendario');
     });

     calendarNext.addEventListener('click', () => {
         alert('Navegación a mes siguiente - En una implementación real esto cambiaría el calendario');
     });

     // Simular carga de datos
     document.addEventListener('DOMContentLoaded', () => {
         // Simular una pequeña animación de carga
         const cards = document.querySelectorAll('.info-card');
         cards.forEach((card, index) => {
             card.style.animation = `fadeIn 0.5s ease ${index * 0.1}s both`;
         });
     });
 </script>
 </body>

 </html>