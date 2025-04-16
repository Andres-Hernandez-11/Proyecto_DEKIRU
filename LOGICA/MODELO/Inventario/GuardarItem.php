<?php
// Iniciar sesión para poder usar variables de sesión para mensajes
session_start();

// Incluir archivo de conexión
// ¡ASEGÚRATE DE QUE LA RUTA SEA CORRECTA!
require_once '../conexion.php'; // Asumiendo que está en la misma carpeta (CONTROLADOR)

// Verificar si la conexión se estableció correctamente
if (!$conn) {
    // Guardar mensaje de error en sesión y redirigir
    $_SESSION['mensaje'] = "Error crítico: No se pudo conectar a la base de datos.";
    $_SESSION['tipo_mensaje'] = "error";
    // Redirigir a una página de error o al inventario (ajusta según necesidad)
    header('Location: ../VISTA/InventarioStart.php'); // Ajusta la ruta de redirección
    exit();
}

// Verificar si se recibieron datos por POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 1. Recuperar datos del formulario (usando null coalescing ?? para evitar warnings)
    $nombre = trim($_POST['nombre'] ?? '');
    $categoria_seleccionada = trim($_POST['categoria'] ?? '');
    $nueva_categoria = trim($_POST['nueva_categoria'] ?? '');
    $stock = $_POST['stock'] ?? 0; // Default a 0 si no se envía
    $stock_minimo = $_POST['stock_minimo'] ?? 0; // Default a 0
    $unidad_medida = trim($_POST['unidad_medida'] ?? '');
    $precio_unitario = $_POST['precio_unitario'] ?? null; // Permitir NULL o 0
    $ubicacion = trim($_POST['ubicacion'] ?? '');
    $id_proveedor = $_POST['id_proveedor'] ?? ''; // Puede ser '' si no se selecciona
    $descripcion = trim($_POST['descripcion'] ?? '');

    // 2. Validación básica (añade más validaciones según necesites)
    if (empty($nombre)) {
        $_SESSION['mensaje'] = "El nombre del producto es obligatorio.";
        $_SESSION['tipo_mensaje'] = "error";
        header('Location: ../VISTA/InventarioStart.php'); // Redirigir de vuelta
        exit();
    }

    // 3. Determinar la categoría a usar
    $categoria_final = '';
    if ($categoria_seleccionada === '__NUEVA__') {
        if (!empty($nueva_categoria)) {
            $categoria_final = $nueva_categoria;
            // Opcional: Podrías verificar si esta nueva categoría ya existe
            // y/o añadirla a una tabla separada de categorías si la tuvieras.
        } else {
            // Error si se seleccionó nueva pero no se escribió nada
            $_SESSION['mensaje'] = "Por favor, especifica el nombre de la nueva categoría.";
            $_SESSION['tipo_mensaje'] = "error";
            header('Location: ../VISTA/InventarioStart.php');
            exit();
        }
    } else {
        $categoria_final = $categoria_seleccionada;
    }

    // Validar que la categoría final no esté vacía si es obligatoria en tu lógica
    // if (empty($categoria_final)) { ... }


    // 4. Preparar la consulta SQL INSERT usando sentencias preparadas
    $sql = "INSERT INTO PRODUCTO (nombre, categoria, descripcion, stock, precio_unitario, stock_minimo, unidad_medida, ubicacion, id_proveedor)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        // Error al preparar la consulta
        $_SESSION['mensaje'] = "Error al preparar la consulta: " . $conn->error;
        $_SESSION['tipo_mensaje'] = "error";
        header('Location: ../VISTA/InventarioStart.php');
        exit();
    }

    // 5. Vincular parámetros
    // Determinar el tipo de datos: s=string, i=integer, d=double, b=blob
    $types = "sssidisss"; // Ajusta según los tipos de tus columnas

    // Convertir números y manejar proveedor NULL si está vacío
    $stock_int = (int)$stock;
    $stock_minimo_int = (int)$stock_minimo;
    $precio_decimal = ($precio_unitario !== null && $precio_unitario !== '') ? (float)$precio_unitario : null;
    $proveedor_id = ($id_proveedor !== '') ? (int)$id_proveedor : null; // Convertir a NULL si está vacío

    $stmt->bind_param(
        $types,
        $nombre,
        $categoria_final,
        $descripcion,
        $stock_int,
        $precio_decimal,
        $stock_minimo_int,
        $unidad_medida,
        $ubicacion,
        $proveedor_id
    );

    // 6. Ejecutar la consulta
    if ($stmt->execute()) {
        // Éxito
        if ($stmt->affected_rows > 0) {
            $_SESSION['mensaje'] = "Ítem '" . htmlspecialchars($nombre) . "' agregado exitosamente al inventario.";
            $_SESSION['tipo_mensaje'] = "success";
        } else {
            // No se insertó fila, podría ser un error silencioso o no esperado
             $_SESSION['mensaje'] = "El ítem no pudo ser agregado (0 filas afectadas).";
             $_SESSION['tipo_mensaje'] = "warning";
        }
    } else {
        // Error al ejecutar
        // En producción, loggear el error en lugar de mostrarlo directamente
        // error_log("Error al guardar ítem: " . $stmt->error);
        $_SESSION['mensaje'] = "Error al guardar el ítem: " . $stmt->error; // Mostrar error (cuidado en producción)
        $_SESSION['tipo_mensaje'] = "error";
    }

    // 7. Cerrar statement
    $stmt->close();

} else {
    // Si no se accedió por POST
    $_SESSION['mensaje'] = "Acceso no válido para guardar ítem.";
    $_SESSION['tipo_mensaje'] = "error";
}

// 8. Cerrar conexión
if ($conn) {
    $conn->close();
}

// 9. Redirigir siempre de vuelta a la página de inventario
// Asegúrate que la ruta sea correcta a tu archivo principal de inventario
header('Location: ../VISTA/InventarioStart.php');
exit();

?>
