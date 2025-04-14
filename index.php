<?php
// Corregimos la ruta relativa al directorio includes
require 'includes/app.php';
incluirTemplate('header');
?>

<main class="login">
    <div class="login__contenedor">
        <div class="login__logo">
            <img src="build/img/logo-utc.png" alt="Logo Universidad Tres Culturas">
        </div>

        <h1 class="login__heading">Sistema de Asistencia NFC</h1>

        <form class="login__formulario" method="POST" action="dashboard.php">
            <div class="login__campo">
                <label for="usuario" class="login__label">Usuario</label>
                <input
                    type="text"
                    id="usuario"
                    name="usuario"
                    class="login__input"
                    placeholder="Tu usuario"
                    required>
            </div>

            <div class="login__campo">
                <label for="password" class="login__label">Contraseña</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    class="login__input"
                    placeholder="Tu contraseña"
                    required>
            </div>

            <input type="submit" class="login__submit" value="Iniciar Sesión">
        </form>

        <a href="#" class="login__enlace">¿Olvidaste tu contraseña?</a>
    </div>
</main>

<?php incluirTemplate('footer'); ?>