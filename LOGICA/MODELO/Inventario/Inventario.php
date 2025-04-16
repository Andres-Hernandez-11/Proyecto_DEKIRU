<?php
// Asumiendo que session_start() se llama en el archivo principal (InventarioStart.php)
// Si este archivo se ejecuta directamente, descomenta la siguiente línea:
// session_start();

// --- Configuración y Conexión ---
error_reporting(E_ALL); // Temporal: Mostrar todos los errores durante el desarrollo
ini_set('display_errors', 1); // Temporal: Mostrar errores en pantalla
$current_page = basename($_SERVER['PHP_SELF']);

// Incluir archivo de conexión
// ¡ASEGÚRATE DE QUE LA RUTA SEA CORRECTA DESDE InventarioStart.php!
require_once '..\MODELO\conexion.php';

// Verificar si la conexión se estableció correctamente (la variable $conn viene de conexion.php)
if (!$conn) {
    // Manejar error de conexión de forma más robusta si es necesario
    die("Error crítico: No se pudo establecer la conexión a la base de datos.");
}

$items_por_pagina = 10; // Número de ítems a mostrar por página

// --- Obtener Parámetros de la URL (GET) ---
$busqueda = trim($_GET['q'] ?? '');
$categoria_filtro = $_GET['categoria'] ?? '';
$proveedor_filtro = $_GET['proveedor'] ?? ''; // Espera el ID del proveedor
$mostrar_bajo_minimo = isset($_GET['bajo_minimo']);
$pagina_actual = isset($_GET['pagina']) ? max(1, (int)$_GET['pagina']) : 1; // Asegura que sea >= 1

// --- Inicializar Variables para la Vista ---
$categorias_disponibles = [];
$proveedores_disponibles = [];
$inventario_paginado = [];
$total_items = 0;
$total_paginas = 0;
$error_db = null; // Para almacenar mensajes de error de BD

// --- Obtener Datos para los Filtros (Dropdowns) ---
try {
    // Categorías
    $sql_cat = "SELECT DISTINCT categoria FROM PRODUCTO WHERE categoria IS NOT NULL AND categoria != '' ORDER BY categoria ASC";
    $result_cat = $conn->query($sql_cat);
    if ($result_cat) {
        while ($row_cat = $result_cat->fetch_assoc()) {
            $categorias_disponibles[] = $row_cat['categoria'];
        }
        $result_cat->free();
    } else {
        throw new Exception("Error al obtener categorías: " . $conn->error);
    }

    // Proveedores
    $sql_prov = "SELECT id_proveedor, nombre FROM PROVEEDOR ORDER BY nombre ASC";
    $result_prov = $conn->query($sql_prov);
    if ($result_prov) {
        while ($row_prov = $result_prov->fetch_assoc()) {
            $proveedores_disponibles[] = $row_prov; // Guardar ID y Nombre
        }
        $result_prov->free();
    } else {
        throw new Exception("Error al obtener proveedores: " . $conn->error);
    }

    // --- Construir Consulta SQL Dinámica con Filtros ---
    $sql_base = "SELECT p.id_producto, p.nombre, p.categoria, p.stock, p.stock_minimo, p.unidad_medida, p.precio_unitario, p.ubicacion, pr.nombre AS nombre_proveedor
                 FROM PRODUCTO p
                 LEFT JOIN PROVEEDOR pr ON p.id_proveedor = pr.id_proveedor";

    $where_clauses = [];
    $params = []; // Array para los parámetros a vincular
    $types = ""; // String para los tipos de parámetros (s=string, i=integer)
    

    if (!empty($busqueda)) {
        // Buscar en ID (como string) o nombre
        $where_clauses[] = "(CAST(p.id_producto AS CHAR) LIKE ? OR p.nombre LIKE ?)";
        $search_term = "%" . $busqueda . "%";
        $params[] = $search_term;
        $params[] = $search_term;
        $types .= "ss";
    }
    if (!empty($categoria_filtro)) {
        $where_clauses[] = "p.categoria = ?";
        $params[] = $categoria_filtro;
        $types .= "s";
    }
    if (!empty($proveedor_filtro)) {
        $where_clauses[] = "p.id_proveedor = ?";
        $params[] = (int)$proveedor_filtro; // Asegurar que sea entero
        $types .= "i";
    }
    if ($mostrar_bajo_minimo) {
        $where_clauses[] = "(p.stock <= p.stock_minimo AND p.stock_minimo > 0)"; // Solo si minimo > 0
    }

    $sql_where = "";
    if (!empty($where_clauses)) {
        $sql_where = " WHERE " . implode(" AND ", $where_clauses);
    }

    // --- Paginación: Contar Total de Ítems ---
    $sql_count = "SELECT COUNT(*) as total FROM PRODUCTO p" . $sql_where;
    $stmt_count = $conn->prepare($sql_count);
    if (!$stmt_count) {
        throw new Exception("Error al preparar conteo: " . $conn->error);
    }

    if (!empty($types)) {
        $stmt_count->bind_param($types, ...$params); // Vincular parámetros de filtros
    }
    if (!$stmt_count->execute()) {
         throw new Exception("Error al ejecutar conteo: " . $stmt_count->error);
    }

    $result_count = $stmt_count->get_result();
    $total_items = $result_count->fetch_assoc()['total'] ?? 0;
    $stmt_count->close();

    // Calcular total de páginas y ajustar página actual
    $total_paginas = ($items_por_pagina > 0) ? ceil($total_items / $items_por_pagina) : 0;
    if ($pagina_actual > $total_paginas && $total_paginas > 0) {
        $pagina_actual = $total_paginas; // Ir a la última página si la actual no existe
    }
    $offset = ($pagina_actual - 1) * $items_por_pagina;

    // --- Ejecutar Consulta Principal para Obtener Ítems Paginados ---
    $sql_final = $sql_base . $sql_where . " ORDER BY p.nombre ASC LIMIT ? OFFSET ?";
    $types .= "ii"; // Añadir tipos para LIMIT y OFFSET
    $params[] = $items_por_pagina;
    $params[] = $offset;

    $stmt_main = $conn->prepare($sql_final);
    if (!$stmt_main) {
        throw new Exception("Error al preparar consulta principal: " . $conn->error);
    }

    if (!empty($types)) {
         // Vincular todos los parámetros (filtros + limit + offset)
        $stmt_main->bind_param($types, ...$params);
    }
     if (!$stmt_main->execute()) {
         throw new Exception("Error al ejecutar consulta principal: " . $stmt_main->error);
    }

    $result_main = $stmt_main->get_result();
    if ($result_main) {
        $inventario_paginado = $result_main->fetch_all(MYSQLI_ASSOC);
        $result_main->free();
    }
    $stmt_main->close();

} catch (Exception $e) {
    // Capturar cualquier excepción de base de datos
    $error_db = "Error de Base de Datos: " . $e->getMessage();
    // Opcional: Loggear el error en un archivo en lugar de mostrarlo directamente
    // error_log($error_db);
    // Podrías querer limpiar las variables de resultados si hubo un error
    $inventario_paginado = [];
    $total_items = 0;
    $total_paginas = 0;
}

// --- Cerrar Conexión ---
// Es buena práctica cerrarla aquí si este script termina la interacción con BD
// Si InventarioStart.php hace más cosas después, podrías cerrarla allí.
if ($conn) {
    $conn->close();
}

// --- Variable para Sidebar (si se define aquí) ---
// Si este archivo es incluido por InventarioStart.php, esta línea podría no ser necesaria aquí
// si InventarioStart.php ya la define antes de incluir la vista.
// $current_page = basename($_SERVER['PHP_SELF']); // O el nombre específico si es incluido


// --- Fin del bloque de lógica ---
// Las variables $categorias_disponibles, $proveedores_disponibles, $inventario_paginado,
// $total_items, $total_paginas, $pagina_actual, $items_por_pagina, $error_db,
// y las variables de filtro ($busqueda, $categoria_filtro, etc.)
// están ahora listas para ser usadas por el archivo de vista (inventario_vista.php).

?>
