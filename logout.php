<?php
session_start();

// Destruimos todas las variables de sesión
session_unset();

// Destruimos la sesión
session_destroy();

// Redirigimos al login de la carpeta de clientes
header("Location: login.php");
exit();
?>