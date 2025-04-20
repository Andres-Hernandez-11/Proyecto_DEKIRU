<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
// 1. Iniciar sesión (siempre al principio)


// Opcional: Verificar login aquí si no lo haces en la lógica
// if (!isset($_SESSION['id_usuario'])) { ... }

// 2. Incluir y ejecutar el archivo de lógica PRIMERO
// Esto conectará a la BD, procesará filtros, obtendrá datos
// y definirá variables como $inventario_paginado, $total_items, etc.
// Asegúrate de que la ruta sea correcta.
require_once '../../MODELO/Inventario/Inventario.php';

// 3. Incluir el archivo de la vista (HTML) DESPUÉS
// Este archivo usará las variables que se definieron en el paso anterior.
// Asegúrate de que la ruta sea correcta.
require_once 'Inventario.php';

?>





