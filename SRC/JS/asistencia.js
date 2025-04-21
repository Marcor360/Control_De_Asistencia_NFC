// src/js/asistencia.js

document.addEventListener('DOMContentLoaded', function () {
    // Verificar si estamos en la página de asistencia
    const lectorNFC = document.getElementById('lectorNFC');
    if (lectorNFC) {
        const estado = document.getElementById('estado');
        const infoAlumno = document.getElementById('infoAlumno');

        lectorNFC.addEventListener('click', function () {
            // Obtener el tipo de acceso seleccionado
            const tipoAcceso = document.querySelector('input[name="tipoAcceso"]:checked').value;

            // Actualizar estado a "leyendo"
            estado.textContent = 'Leyendo tarjeta...';
            estado.className = 'asistencia__estado asistencia__leyendo';
            infoAlumno.innerHTML = '';

            // Simular la lectura NFC (en un sistema real, esto sería reemplazado por la detección NFC real)
            setTimeout(function () {
                // En un sistema real, aquí se obtendría el código NFC real
                // Para la simulación, usaremos un código de prueba
                const codigoNFC = 'NFC12345678';

                // Enviar solicitud al servidor usando fetch API
                fetch('asistencia.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `accion=registrarAcceso&codigoNFC=${codigoNFC}&tipoAcceso=${tipoAcceso}`
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.exito) {
                            // Éxito en el registro
                            estado.textContent = data.mensaje;
                            estado.className = 'asistencia__estado asistencia__exito';

                            // Mostrar información del alumno
                            if (data.alumno) {
                                infoAlumno.innerHTML = `
                                <h3 class="asistencia__alumno-nombre">${data.alumno.nombre} ${data.alumno.apellidos}</h3>
                                <div class="asistencia__registro">
                                    <span class="asistencia__hora">${data.hora}</span>
                                    <span class="asistencia__tipo asistencia__tipo--${data.tipoAcceso.toLowerCase()}">${data.tipoAcceso}</span>
                                </div>
                            `;
                            }

                            // Refrescar la página después de 3 segundos para actualizar la tabla
                            setTimeout(() => {
                                window.location.reload();
                            }, 3000);
                        } else {
                            // Error en el registro
                            estado.textContent = data.mensaje;
                            estado.className = 'asistencia__estado asistencia__error';

                            // Si hay información del alumno, mostrarla aún en caso de error
                            if (data.alumno) {
                                infoAlumno.innerHTML = `
                                <h3 class="asistencia__alumno-nombre">${data.alumno.nombre} ${data.alumno.apellidos}</h3>
                                <p class="asistencia__error">Acceso denegado</p>
                            `;
                            }
                        }
                    })
                    .catch(error => {
                        estado.textContent = 'Error de comunicación';
                        estado.className = 'asistencia__estado asistencia__error';
                        console.error('Error:', error);
                    });
            }, 2000); // Simular 2 segundos de lectura
        });
    }
});