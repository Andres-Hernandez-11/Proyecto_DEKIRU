<?php
// Iniciar la sesión al principio de todo para acceder a las variables de sesión
session_start();

$current_page = basename($_SERVER['PHP_SELF']);
// Opcional: Verificar si el usuario está logueado, si no, redirigir al login
if (!isset($_SESSION['id_usuario'])) {
     header('Location: ../VISTA/Login_UI.php'); // Ajusta la ruta según sea necesario
     exit();
 }

// Incluir archivo de conexión a la base de datos
// require_once '../CONTROLADOR/conexion.php'; // Asegúrate que la ruta sea correcta

/* --- Manejo de Filtros y Búsqueda --- */
$busqueda = trim($_GET['q'] ?? '');
$categoria_filtro = $_GET['categoria'] ?? '';
$proveedor_filtro = $_GET['proveedor'] ?? '';
$mostrar_bajo_minimo = isset($_GET['bajo_minimo']);

/* --- Obtención de Datos (Aquí iría la lógica real de BD) --- */

// Placeholder para categorías (Deberían venir de la BD)
$categorias_disponibles = ['Oficina', 'Impresión', 'Limpieza', 'Cafetería']; // Ejemplo

// Placeholder para proveedores (Deberían venir de la BD)
$proveedores_disponibles = ['Papelera Nacional', 'Tecno Suministros', 'Distribuidora Escritorio', 'Químicos ABC']; // Ejemplo

// Placeholder para datos del inventario (Deberían venir de la BD aplicando filtros)
$inventario_completo = [
    ['id' => 'PROD001', 'nombre' => 'Resma Papel Carta', 'categoria' => 'Oficina', 'stock' => 50, 'minimo' => 20, 'unidad' => 'Unidad', 'precio' => 15000, 'ubicacion' => 'Estante A1', 'proveedor' => 'Papelera Nacional'],
    ['id' => 'PROD002', 'nombre' => 'Tóner XYZ', 'categoria' => 'Impresión', 'stock' => 5, 'minimo' => 10, 'unidad' => 'Unidad', 'precio' => 120000, 'ubicacion' => 'Bodega B', 'proveedor' => 'Tecno Suministros'],
    ['id' => 'PROD003', 'nombre' => 'Caja Bolígrafos Negros', 'categoria' => 'Oficina', 'stock' => 15, 'minimo' => 15, 'unidad' => 'Caja x12', 'precio' => 8000, 'ubicacion' => 'Estante A2', 'proveedor' => 'Distribuidora Escritorio'],
    ['id' => 'PROD004', 'nombre' => 'Limpiador Multiusos', 'categoria' => 'Limpieza', 'stock' => 30, 'minimo' => 10, 'unidad' => 'Litro', 'precio' => 5000, 'ubicacion' => 'Almacén L', 'proveedor' => 'Químicos ABC'],
    // ... más productos de ejemplo
];

// --- Lógica de Filtrado (Simulación - Reemplazar con consulta SQL) ---
$inventario_filtrado = $inventario_completo; // Empezar con todos

// Filtrar por búsqueda (nombre, id/código)
if (!empty($busqueda)) {
    $inventario_filtrado = array_filter($inventario_filtrado, function($item) use ($busqueda) {
        return stripos($item['nombre'], $busqueda) !== false || stripos($item['id'], $busqueda) !== false;
    });
}

// Filtrar por categoría
if (!empty($categoria_filtro)) {
    $inventario_filtrado = array_filter($inventario_filtrado, function($item) use ($categoria_filtro) {
        return $item['categoria'] === $categoria_filtro;
    });
}

// Filtrar por proveedor
if (!empty($proveedor_filtro)) {
    $inventario_filtrado = array_filter($inventario_filtrado, function($item) use ($proveedor_filtro) {
        return $item['proveedor'] === $proveedor_filtro;
    });
}

// Filtrar por bajo mínimo
if ($mostrar_bajo_minimo) {
    $inventario_filtrado = array_filter($inventario_filtrado, function($item) {
        return $item['stock'] <= $item['minimo'];
    });
}

/* --- Lógica de Paginación (Placeholder) --- */
$total_items = count($inventario_filtrado);
$items_por_pagina = 10; // O el número que desees
$pagina_actual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$total_paginas = ceil($total_items / $items_por_pagina);
$offset = ($pagina_actual - 1) * $items_por_pagina;

// Aplicar paginación a los resultados filtrados (Simulación)
// En SQL real, usarías LIMIT y OFFSET
$inventario_paginado = array_slice($inventario_filtrado, $offset, $items_por_pagina);

// Fin del bloque de lógica PHP
?>