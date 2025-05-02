<?php
// ContabilidadStart.php (Archivo principal que el usuario visita)

// 1. Iniciar sesión (siempre al principio)
session_start();

// 2. Opcional: Verificar si el usuario está logueado
// if (!isset($_SESSION['id_usuario'])) {
//     header('Location: Login_UI.php'); // Ajusta ruta al login si es necesario
//     exit();
// }

// 3. Incluir y ejecutar el archivo de lógica PRIMERO
// Define las variables necesarias ($total_ingresos, $movimientos_recientes, etc.)
// ¡ASEGÚRATE QUE LA RUTA A TU ARCHIVO DE LÓGICA SEA CORRECTA!
// Ejemplo: Si este archivo está en VISTA/ y la lógica en CONTROLADOR/
require_once '../../MODELO/Contabilidad/ver.php'; // Ajusta esta ruta

// 4. Incluir la vista HTML DESPUÉS
// Usa las variables definidas en el paso anterior para mostrar la página.
// ¡ASEGÚRATE QUE LA RUTA A TU ARCHIVO DE VISTA SEA CORRECTA!
// Ejemplo: Si este archivo y la vista están en la misma carpeta VISTA/
require_once 'Contabilidad.php'; // Ajusta esta ruta

?>
