
document.addEventListener('DOMContentLoaded', function() {
        // Toggle sidebar
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebar = document.getElementById('sidebar');
        const main = document.getElementById('main');

        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.toggle('sidebar-collapsed');
            main.classList.toggle('expanded');
        });

        // Modal functionality
        const modalCliente = document.getElementById('modalCliente');
        const btnNuevoCliente = document.getElementById('btnNuevoCliente');
        const modalClose = document.getElementById('modalClose');
        const btnCancelar = document.getElementById('btnCancelar');
        const btnGuardar = document.getElementById('btnGuardar');
        const modalTitle = document.getElementById('modalTitle');
        const clienteForm = document.getElementById('clienteForm');

        // Abrir modal para nuevo cliente
        btnNuevoCliente.addEventListener('click', function() {
            modalTitle.textContent = 'Nuevo Cliente';
            clienteForm.reset();
            modalCliente.classList.add('show');
        });

        // Cerrar modal
        modalClose.addEventListener('click', closeModal);
        btnCancelar.addEventListener('click', closeModal);

        function closeModal() {
            modalCliente.classList.remove('show');
        }

        // Cerrar modal al hacer clic fuera
        modalCliente.addEventListener('click', function(e) {
            if (e.target === modalCliente) {
                closeModal();
            }
        });

        // Guardar cliente
        btnGuardar.addEventListener('click', function() {
            if (clienteForm.checkValidity()) {
                // Aquí iría la lógica para guardar el cliente
                alert('Cliente guardado exitosamente');
                closeModal();
            } else {
                alert('Por favor, complete todos los campos requeridos');
            }
        });

        // Funcionalidad de búsqueda
        const searchInput = document.getElementById('searchInput');
        const tableRows = document.querySelectorAll('#clientesTable tbody tr');

        searchInput.addEventListener('keyup', function() {
            const searchTerm = this.value.toLowerCase();

            tableRows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchTerm) ? '' : 'none';
            });
        });

        // Funcionalidad de filtro por estado
        const filterStatus = document.getElementById('filterStatus');

        filterStatus.addEventListener('change', function() {
            const status = this.value;

            tableRows.forEach(row => {
                if (!status) {
                    row.style.display = '';
                    return;
                }

                // Aquí puedes agregar lógica para filtrar por estado si lo implementas
                // Por ahora solo muestra todas las filas
                row.style.display = '';
            });
        });
    });