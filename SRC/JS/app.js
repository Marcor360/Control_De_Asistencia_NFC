document.addEventListener('DOMContentLoaded', function () {
    // Lectura real de tarjeta NFC utilizando la API Web NFC
    const lectorNFC = document.querySelector('.asistencia__nfc');
    if (lectorNFC) {
        lectorNFC.addEventListener('click', async function () {
            const estadoNFC = document.querySelector('.asistencia__estado');
            estadoNFC.textContent = 'Leyendo tarjeta...';
            estadoNFC.classList.remove('asistencia__exito', 'asistencia__error');
            estadoNFC.classList.add('asistencia__leyendo');

            if ('NDEFReader' in window) {
                try {
                    const ndef = new NDEFReader();
                    const controller = new AbortController();
                    await ndef.scan({ signal: controller.signal });

                    ndef.onreading = event => {
                        controller.abort();
                        const codigo = event.serialNumber;
                        estadoNFC.textContent = 'Tarjeta leída: ' + codigo;
                        estadoNFC.classList.remove('asistencia__leyendo', 'asistencia__error');
                        estadoNFC.classList.add('asistencia__exito');

                        const input = document.querySelector('input[name="codigo_nfc"]');
                        if (input) {
                            input.value = codigo;
                        }
                    };
                } catch (error) {
                    estadoNFC.textContent = 'Error al leer la tarjeta';
                    estadoNFC.classList.remove('asistencia__leyendo');
                    estadoNFC.classList.add('asistencia__error');
                    console.error(error);
                }
            } else {
                estadoNFC.textContent = 'Lectura NFC no soportada';
                estadoNFC.classList.remove('asistencia__leyendo');
                estadoNFC.classList.add('asistencia__error');
            }
        });
    }

    // Alerta para confirmar eliminación de registros
    const botonesEliminar = document.querySelectorAll('.alumnos__accion--eliminar');
    if (botonesEliminar.length > 0) {
        botonesEliminar.forEach(boton => {
            boton.addEventListener('click', function (e) {
                e.preventDefault();

                if (!confirm('¿Estás seguro de eliminar este registro?')) {
                    return;
                }

                // Aquí iría el código para eliminar el registro
                console.log('Registro eliminado');
            });
        });
    }

    // Mostrar/ocultar formulario de registro de pago
    const botonRegistrarPago = document.querySelector('.pagos__accion--registrar');
    if (botonRegistrarPago) {
        botonRegistrarPago.addEventListener('click', function (e) {
            e.preventDefault();

            const formularioPago = document.querySelector('.pagos__formulario');
            formularioPago.classList.toggle('pagos__formulario--visible');
        });
    }
});