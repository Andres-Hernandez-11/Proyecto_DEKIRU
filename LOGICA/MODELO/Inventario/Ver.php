<?php

header('Content-Type: application/json; charset=utf-8');

require_once '../conexion.php';

// --- Inicialización de Variables ---
$response = []; // Array que contendrá la respuesta JSON final
$item = null;     // Array para guardar los datos del ítem si se encuentra
$error_msg = null; // Variable para almacenar mensajes de error específicos


if (empty($conn) || !($conn instanceof mysqli)) {
    // Error crítico si no hay conexión
    $response['error'] = "Error crítico: No se pudo conectar a la base de datos.";
    // Imprimir respuesta JSON y terminar
    echo json_encode($response);
    exit;
}

// --- Validar ID Recibido ---
if (isset($_GET['id']) && filter_var($_GET['id'], FILTER_VALIDATE_INT) && $_GET['id'] > 0) {
    // El ID existe, es un entero y es positivo
    $id_producto_solicitado = (int)$_GET['id'];

    // --- Preparar Consulta SQL ---
    // Seleccionamos todas las columnas necesarias de PRODUCTO y el nombre del PROVEEDOR
    $sql = "SELECT
                p.id_producto, p.nombre, p.categoria, p.descripcion, p.stock,
                p.precio_unitario, p.stock_minimo, p.unidad_medida, p.ubicacion,
                p.id_proveedor, pr.nombre AS nombre_proveedor
            FROM PRODUCTO p
            LEFT JOIN PROVEEDOR pr ON p.id_proveedor = pr.id_proveedor
            WHERE p.id_producto = ?"; // Usamos un marcador de posición (?)

    $stmt = $conn->prepare($sql);

    // Verificar si la preparación fue exitosa
    if ($stmt) {
        // --- Vincular Parámetro ---
        // "i" indica que el parámetro es de tipo integer
        $stmt->bind_param("i", $id_producto_solicitado);

        // --- Ejecutar Consulta ---
        if ($stmt->execute()) {
            // --- Vincular Resultados ---
            // Vinculamos cada columna del SELECT a una variable PHP
            $stmt->bind_result(
                $id_producto_db, $nombre_db, $categoria_db, $descripcion_db, $stock_db,
                $precio_unitario_db, $stock_minimo_db, $unidad_medida_db, $ubicacion_db,
                $id_proveedor_db, $nombre_proveedor_db
            );

            // --- Obtener Resultados ---
            // fetch() obtiene una fila y la asigna a las variables vinculadas
            if ($stmt->fetch()) {
                // Ítem encontrado, construir el array $item
                $item = [
                    'id_producto' => $id_producto_db,
                    'nombre' => $nombre_db,
                    // Usamos el operador ?? para dar un valor por defecto si el campo es NULL en la BD
                    'categoria' => $categoria_db ?? null,
                    'descripcion' => $descripcion_db ?? null,
                    'stock' => $stock_db,
                    'precio_unitario' => $precio_unitario_db, // El JS lo parseará si es necesario
                    'stock_minimo' => $stock_minimo_db ?? null,
                    'unidad_medida' => $unidad_medida_db ?? null,
                    'ubicacion' => $ubicacion_db ?? null,
                    'id_proveedor' => $id_proveedor_db, // Puede ser NULL
                    'nombre_proveedor' => $nombre_proveedor_db // Puede ser NULL
                ];
                // La respuesta será el ítem encontrado
                $response['item'] = $item;

            } else {
                // No se encontró ninguna fila con ese ID
                $error_msg = "Ítem no encontrado (ID: " . htmlspecialchars($id_producto_solicitado) . ").";
            }
        } else {
            // Error durante la ejecución de la consulta
            $error_msg = "Error al ejecutar consulta: (" . $stmt->errno . ") " . htmlspecialchars($stmt->error);
        }
        // --- Cerrar Statement ---
        $stmt->close();

    } else {
        // Error durante la preparación de la consulta
        $error_msg = "Error al preparar consulta: (" . $conn->errno . ") " . htmlspecialchars($conn->error);
    }

} else {
    // El ID no se recibió, no es un entero, o no es positivo
    $error_msg = "ID de ítem inválido o no proporcionado.";
}

// --- Cerrar Conexión ---
// Es buena práctica cerrar la conexión al final
if ($conn) {
    $conn->close();
}

// --- Preparar Respuesta JSON Final ---

// Si hubo un error en algún punto ($error_msg no es null), lo añadimos a la respuesta
if ($error_msg !== null) {
    $response['error'] = $error_msg;
} elseif ($item === null && !isset($response['error'])) {
    // Caso borde: no hubo error explícito, pero no se encontró ítem (ya manejado arriba, pero por seguridad)
    $response['error'] = "Ítem no encontrado o error desconocido.";
}

// Limpiar cualquier salida inesperada (warnings, notices) antes de enviar JSON
if (ob_get_level()) {
    ob_end_clean();
}

// --- Enviar Respuesta JSON ---
echo json_encode($response);

// Terminar ejecución para asegurar que no se envíe nada más
exit;
?>
