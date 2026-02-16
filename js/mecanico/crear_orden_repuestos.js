document.addEventListener('DOMContentLoaded', function() {
    const tipoServicio = document.getElementById('tipo_servicio');
    const precioInput = document.getElementById('precio_unitario');
    const cantidadInput = document.getElementById('cantidad');
    const nuevaOrdenForm = document.getElementById('nuevaOrdenForm');

    console.log('Script crear_orden_repuestos.js cargado');

    function actualizarPrecio() {
        if (!tipoServicio) return;
        
        const selectedOption = tipoServicio.options[tipoServicio.selectedIndex];
        console.log('Opción seleccionada:', selectedOption.text);

        // Recuperar el precio base y stock guardados en los atributos data
        // Usamos dataset para mayor compatibilidad moderna, con fallback a getAttribute
        const precio = selectedOption.dataset.precio || selectedOption.getAttribute('data-precio');
        const stock = selectedOption.dataset.stock || selectedOption.getAttribute('data-stock');
        
        console.log('Precio:', precio, 'Stock:', stock);

        // Si existe un precio (es decir, si se seleccionó un servicio válido)
        if (precio) {
            precioInput.value = parseFloat(precio).toFixed(2);
            cantidadInput.max = parseInt(stock);
            // Solo resetear cantidad si es mayor al stock disponible
            if (parseInt(cantidadInput.value) > parseInt(stock)) {
                cantidadInput.value = 1;
            }
        } else {
            precioInput.value = '0.00';
            cantidadInput.removeAttribute('max');
            cantidadInput.value = 1;
        }
    }

    // Escuchar cambios en el selector de tipo de servicio
    if (tipoServicio) {
        tipoServicio.addEventListener('change', actualizarPrecio);
        // Intentar actualizar al cargar por si el navegador guardó la selección
        actualizarPrecio(); 
    }

    // Limpiar formulario y resetear valores
    const btnLimpiar = document.getElementById('btnLimpiarFormulario');
    if (btnLimpiar) {
        btnLimpiar.addEventListener('click', function() {
            if (nuevaOrdenForm) {
                nuevaOrdenForm.reset();
                // Esperar un tick para que el reset del form termine antes de poner nuestros valores por defecto
                setTimeout(() => {
                    precioInput.value = '0.00';
                    cantidadInput.removeAttribute('max');
                    cantidadInput.value = 1;
                }, 0);
            }
        });
    }

    // Cancelar y volver a la lista de órdenes
    const btnCancelar = document.getElementById('btnCancelar');
    if (btnCancelar) {
        btnCancelar.addEventListener('click', function() {
            window.location.href = '../mecanico/gestion_ordenes_trabajo_en_proceso.php';
        });
    }
});