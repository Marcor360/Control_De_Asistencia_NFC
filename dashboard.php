<?php
require 'includes/app.php';
incluirTemplate('header');
?>

<div class="dashboard">
    <header class="dashboard__header">
        <div class="dashboard__logo">
            <img src="build/img/logo-utc.png" alt="Logo Universidad Tres Culturas">
        </div>
        <nav class="dashboard__nav">
            <a href="#" class="dashboard__enlace">
                <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-user" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                    <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                    <path d="M8 7a4 4 0 1 0 8 0a4 4 0 0 0 -8 0"></path>
                    <path d="M6 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2"></path>
                </svg>
                Admin
            </a>
            <a href="index.php" class="dashboard__enlace">
                <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-logout" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                    <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                    <path d="M14 8v-2a2 2 0 0 0 -2 -2h-7a2 2 0 0 0 -2 2v12a2 2 0 0 0 2 2h7a2 2 0 0 0 2 -2v-2"></path>
                    <path d="M9 12h12l-3 -3"></path>
                    <path d="M18 15l3 -3"></path>
                </svg>
                Cerrar Sesión
            </a>
        </nav>
    </header>

    <div class="dashboard__grid">
        <aside class="dashboard__sidebar">
            <nav>
                <ul class="dashboard__menu">
                    <li class="dashboard__menu-item">
                        <a href="dashboard.php" class="dashboard__menu-enlace dashboard__menu-enlace--activo">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-layout-dashboard" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                <path d="M4 4h6v8h-6z"></path>
                                <path d="M4 16h6v4h-6z"></path>
                                <path d="M14 12h6v8h-6z"></path>
                                <path d="M14 4h6v4h-6z"></path>
                            </svg>
                            <span class="dashboard__menu-texto">Dashboard</span>
                        </a>
                    </li>
                    <li class="dashboard__menu-item">
                        <a href="asistencia.php" class="dashboard__menu-enlace">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-device-nfc" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                <path d="M11 20a3 3 0 0 1 -3 -3v-11l5 5"></path>
                                <path d="M13 4a3 3 0 0 1 3 3v11l-5 -5"></path>
                                <path d="M4 4m0 3a3 3 0 0 1 3 -3h10a3 3 0 0 1 3 3v10a3 3 0 0 1 -3 3h-10a3 3 0 0 1 -3 -3z"></path>
                            </svg>
                            <span class="dashboard__menu-texto">Registro de Asistencia</span>
                        </a>
                    </li>
                    <li class="dashboard__menu-item">
                        <a href="alumnos.php" class="dashboard__menu-enlace">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-users" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                <path d="M9 7m-4 0a4 4 0 1 0 8 0a4 4 0 1 0 -8 0"></path>
                                <path d="M3 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2"></path>
                                <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                                <path d="M21 21v-2a4 4 0 0 0 -3 -3.85"></path>
                            </svg>
                            <span class="dashboard__menu-texto">Alumnos</span>
                        </a>
                    </li>
                    <li class="dashboard__menu-item">
                        <a href="reportes.php" class="dashboard__menu-enlace">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-chart-bar" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                <path d="M3 12m0 1a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v6a1 1 0 0 1 -1 1h-4a1 1 0 0 1 -1 -1z"></path>
                                <path d="M9 8m0 1a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v10a1 1 0 0 1 -1 1h-4a1 1 0 0 1 -1 -1z"></path>
                                <path d="M15 4m0 1a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v14a1 1 0 0 1 -1 1h-4a1 1 0 0 1 -1 -1z"></path>
                            </svg>
                            <span class="dashboard__menu-texto">Reportes</span>
                        </a>
                    </li>
                    <li class="dashboard__menu-item">
                        <a href="pagos.php" class="dashboard__menu-enlace">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-cash" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                <path d="M7 9m0 2a2 2 0 0 1 2 -2h10a2 2 0 0 1 2 2v6a2 2 0 0 1 -2 2h-10a2 2 0 0 1 -2 -2z"></path>
                                <path d="M14 14m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"></path>
                                <path d="M17 9v-2a2 2 0 0 0 -2 -2h-10a2 2 0 0 0 -2 2v6a2 2 0 0 0 2 2h2"></path>
                            </svg>
                            <span class="dashboard__menu-texto">Pagos</span>
                        </a>
                    </li>
                </ul>
            </nav>
        </aside>

        <main class="dashboard__contenido">
            <h2 class="dashboard__heading">Panel de Control</h2>

            <div class="dashboard__widgets">
                <div class="dashboard__widget">
                    <h3 class="dashboard__widget-titulo">Asistencias Hoy</h3>
                    <p class="dashboard__widget-contenido">156</p>
                </div>

                <div class="dashboard__widget">
                    <h3 class="dashboard__widget-titulo">Alumnos Registrados</h3>
                    <p class="dashboard__widget-contenido">328</p>
                </div>

                <div class="dashboard__widget">
                    <h3 class="dashboard__widget-titulo">Tasa de Asistencia</h3>
                    <p class="dashboard__widget-contenido">85%</p>
                </div>

                <div class="dashboard__widget">
                    <h3 class="dashboard__widget-titulo">Pagos Pendientes</h3>
                    <p class="dashboard__widget-contenido">42</p>
                </div>
            </div>

            <div class="dashboard__contenedor">
                <h2 class="dashboard__heading">Últimas Asistencias</h2>

                <table class="asistencia__tabla">
                    <thead class="asistencia__thead">
                        <tr>
                            <th class="asistencia__th">Nombre</th>
                            <th class="asistencia__th">Carrera</th>
                            <th class="asistencia__th">Tipo</th>
                            <th class="asistencia__th">Hora</th>
                            <th class="asistencia__th">Fecha</th>
                        </tr>
                    </thead>
                    <tbody class="asistencia__tbody">
                        <tr class="asistencia__tr">
                            <td class="asistencia__td">Juan Pérez González</td>
                            <td class="asistencia__td">Ing. en Sistemas</td>
                            <td class="asistencia__td">
                                <span class="asistencia__tipo asistencia__tipo--entrada">Entrada</span>
                            </td>
                            <td class="asistencia__td">08:05</td>
                            <td class="asistencia__td">14/04/2025</td>
                        </tr>
                        <tr class="asistencia__tr">
                            <td class="asistencia__td">María López Rodríguez</td>
                            <td class="asistencia__td">Administración</td>
                            <td class="asistencia__td">
                                <span class="asistencia__tipo asistencia__tipo--entrada">Entrada</span>
                            </td>
                            <td class="asistencia__td">08:12</td>
                            <td class="asistencia__td">14/04/2025</td>
                        </tr>
                        <tr class="asistencia__tr">
                            <td class="asistencia__td">Carlos Santos Meraz</td>
                            <td class="asistencia__td">Contabilidad</td>
                            <td class="asistencia__td">
                                <span class="asistencia__tipo asistencia__tipo--entrada">Entrada</span>
                            </td>
                            <td class="asistencia__td">08:15</td>
                            <td class="asistencia__td">14/04/2025</td>
                        </tr>
                        <tr class="asistencia__tr">
                            <td class="asistencia__td">Diana Martínez Fuentes</td>
                            <td class="asistencia__td">Psicología</td>
                            <td class="asistencia__td">
                                <span class="asistencia__tipo asistencia__tipo--entrada">Entrada</span>
                            </td>
                            <td class="asistencia__td">08:20</td>
                            <td class="asistencia__td">14/04/2025</td>
                        </tr>
                        <tr class="asistencia__tr">
                            <td class="asistencia__td">Roberto Álvarez Juárez</td>
                            <td class="asistencia__td">Derecho</td>
                            <td class="asistencia__td">
                                <span class="asistencia__tipo asistencia__tipo--entrada">Entrada</span>
                            </td>
                            <td class="asistencia__td">08:25</td>
                            <td class="asistencia__td">14/04/2025</td>
                        </tr>
                    </tbody>
                </table>

                <div class="asistencia__paginacion">
                    <a href="#" class="asistencia__enlace asistencia__enlace--actual">1</a>
                    <a href="#" class="asistencia__enlace">2</a>
                    <a href="#" class="asistencia__enlace">3</a>
                    <a href="#" class="asistencia__enlace">4</a>
                    <a href="#" class="asistencia__enlace">5</a>
                </div>
            </div>
        </main>
    </div>
</div>

<?php incluirTemplate('footer'); ?>