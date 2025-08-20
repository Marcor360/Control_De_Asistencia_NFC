// src/js/asistencia.js

document.addEventListener('DOMContentLoaded', function () {
    const lectorNFC = document.getElementById('lectorNFC');
    if (lectorNFC) {
        const estado = document.getElementById('estado');
        const infoAlumno = document.getElementById('infoAlumno');

        lectorNFC.addEventListener('click', async function () {
            const tipoAcceso = document.querySelector('input[name="tipoAcceso"]:checked').value;

            estado.textContent = 'Leyendo tarjeta...';
            estado.className = 'asistencia__estado asistencia__leyendo';
            infoAlumno.innerHTML = '';

            if ('NDEFReader' in window) {
                try {
                    const ndef = new NDEFReader();
                    const controller = new AbortController();
                    await ndef.scan({ signal: controller.signal });

                    ndef.onreading = async event => {
                        controller.abort();
                        const codigoNFC = event.serialNumber;

                        try {
                            const response = await fetch('asistencia.php', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                                body: `accion=registrarAcceso&codigoNFC=${codigoNFC}&tipoAcceso=${tipoAcceso}`
                            });
                            const data = await response.json();

                            if (data.exito) {
                                estado.textContent = data.mensaje;
                                estado.className = 'asistencia__estado asistencia__exito';

                                if (data.alumno) {
                                    infoAlumno.innerHTML = `
                                <h3 class="asistencia__alumno-nombre">${data.alumno.nombre} ${data.alumno.apellidos}</h3>
                                <div class="asistencia__registro">
                                    <span class="asistencia__hora">${data.hora}</span>
                                    <span class="asistencia__tipo asistencia__tipo--${data.tipoAcceso.toLowerCase()}">${data.tipoAcceso}</span>
                                </div>
                            `;
                                }

                                setTimeout(() => {
                                    window.location.reload();
                                }, 3000);
                            } else {
                                estado.textContent = data.mensaje;
                                estado.className = 'asistencia__estado asistencia__error';

                                if (data.alumno) {
                                    infoAlumno.innerHTML = `
                                <h3 class="asistencia__alumno-nombre">${data.alumno.nombre} ${data.alumno.apellidos}</h3>
                                <p class="asistencia__error">Acceso denegado</p>
                            `;
                                }
                            }
                        } catch (error) {
                            estado.textContent = 'Error de comunicación';
                            estado.className = 'asistencia__estado asistencia__error';
                            console.error('Error:', error);
                        }
                    };
                } catch (error) {
                    estado.textContent = 'Lectura NFC no soportada';
                    estado.className = 'asistencia__estado asistencia__error';
                    console.error(error);
                }
            } else {
                estado.textContent = 'Lectura NFC no soportada';
                estado.className = 'asistencia__estado asistencia__error';
            }
        });
    }
});