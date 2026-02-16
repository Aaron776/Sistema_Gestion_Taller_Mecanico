 // Escuchar cambios en el selector de tipo de servicio
    document.getElementById('tipo_servicio').addEventListener('change', function() {
        // Obtener la opción seleccionada actualmente
        const selectedOption = this.options[this.selectedIndex];

        // Recuperar el precio base guardado en el atributo data-precio
        const precio = selectedOption.getAttribute('data-precio');
        const precioInput = document.getElementById('precio');

        // Si existe un precio (es decir, si se seleccionó un servicio válido)
        if (precio) {
            // Actualizar el input de precio, formateándolo a 2 decimales
            precioInput.value = parseFloat(precio).toFixed(2);
        } else {
            // Si no hay selección (o es la opción por defecto), poner el precio en 0.00
            precioInput.value = '0.00';
        }
    });

    // Limpiar formulario y resetear valores
    document.getElementById('btnLimpiarFormulario').addEventListener('click', function() {
        document.getElementById('nuevaOrdenForm').reset();
        document.getElementById('precio').value = '0.00';
    });

    // Cancelar y volver a la lista de órdenes
    document.getElementById('btnCancelar').addEventListener('click', function() {
        window.location.href = 'gestion_ordenes_servicios.php';
    });