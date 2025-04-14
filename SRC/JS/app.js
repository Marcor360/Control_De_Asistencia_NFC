document.addEventListener('DOMContentLoaded', function () {
    // Simulación de lectura de tarjeta NFC
    const lectorNFC = document.querySelector('.asistencia__nfc');
    if (lectorNFC) {
        lectorNFC.addEventListener('click', function () {
            const estadoNFC = document.querySelector('.asistencia__estado');
            estadoNFC.textContent = 'Leyendo tarjeta...';
            estadoNFC.classList.remove('asistencia__exito', 'asistencia__error');
            estadoNFC.classList.add('asistencia__leyendo');

            // Simular tiempo de lectura
            setTimeout(function () {
                estadoNFC.textContent = 'Tarjeta leída correctamente';
                estadoNFC.classList.remove('asistencia__leyendo', 'asistencia__error');
                estadoNFC.classList.add('asistencia__exito');

                // Mostrar datos del alumno
                const alumnoInfo = document.querySelector('.asistencia__alumno');
                if (alumnoInfo) {
                    alumnoInfo.innerHTML = `
                        <h3 class="asistencia__alumno-nombre">Juan Pérez González</h3>
                        <div class="asistencia__registro">
                            <span class="asistencia__hora">08:05</span>
                            <span class="asistencia__tipo asistencia__tipo--entrada">Entrada</span>
                        </div>
                    `;
                }
            }, 2000);
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