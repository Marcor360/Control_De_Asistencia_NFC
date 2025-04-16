<?php
// logout.php (en la raíz del proyecto)
require_once 'controladores/AuthController.php';

// Inicializar controlador de autenticación
$auth = new AuthController();

// Ejecutar método de cierre de sesión
$auth->logout();

// Redirigir al login
header('Location: login.php');
exit;
