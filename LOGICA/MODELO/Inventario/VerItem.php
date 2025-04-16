<?php
// Asumiendo que session_start() se llama en un script principal si es necesario para la conexión,
// pero este script probablemente no necesita la sesión directamente.

// --- ¡VERIFICACIÓN CRUCIAL DE RUTA! ---
// Asegúrate de que esta ruta sea correcta DESDE LA UBICACIÓN DE ESTE ARCHIVO (VerItem.php)
// Si VerItem.php está en MODELO/Inventario/ y conexion.php está en MODELO/, la ruta sería '../conexion.php'
// Si VerItem.php está en MODELO/Inventario/ y conexion.php está en la raíz del proyecto, la ruta sería '../../conexion.php'
// Ajusta según tu estructura real.
require_once '../conexion.php'; // Mantengo tu ruta original, ¡VERIFICA ESTO CUIDADOSAMENTE!
// ------------------------------------

// Inicializar variables
$item = null;
$error_msg = null;
$id_producto_solicitado = null;
$response = []; // Array para la respuesta JSON final

// Verificar si la conexión se estableció correctamente
// (Asumiendo que $conn viene de conexion.php)
if (empty($conn) || !($conn instanceof mysqli)) {
    $error_msg = "Error crítico: No se pudo establecer la conexión a la base de datos.";
} else {
    // Verificar si se recibió un ID válido
    if (isset($_GET['id']) && filter_var($_GET['id'], FILTER_VALIDATE_INT) && $_GET['id'] > 0) {
        $id_producto_solicitado = (int)$_GET['id'];

        // Preparar la consulta SQL (tu consulta está bien)
        $sql = "SELECT
                    p.id_producto, p.nombre, p.categoria, p.descripcion, p.stock,
                    p.precio_unitario, p.stock_minimo, p.unidad_medida, p.ubicacion,
                    p.id_proveedor, pr.nombre AS nombre_proveedor
                FROM PRODUCTO p
                LEFT JOIN PROVEEDOR pr ON p.id_proveedor = pr.id_proveedor
                WHERE p.id_producto = ?";

        $stmt = $conn->prepare($sql);

        if ($stmt) {
            $stmt->bind_param("i", $id_producto_solicitado);

            if ($stmt->execute()) {
                // Vincular variables de resultado
                $stmt->bind_result(
                    $id_producto_db, $nombre_db, $categoria_db, $descripcion_db, $stock_db,
                    $precio_unitario_db, $stock_minimo_db, $unidad_medida_db, $ubicacion_db,
                    $id_proveedor_db, $nombre_proveedor_db
                );

                // Obtener el resultado
                if ($stmt->fetch()) {
                    // Ítem encontrado, construir el array $item
                    $item = [
                        'id_producto' => $id_producto_db,
                        'nombre' => $nombre_db,
                        'categoria' => $categoria_db ?? '-', // Usar Null Coalescing Operator para valores NULL
                        'descripcion' => $descripcion_db ?? '-',
                        'stock' => $stock_db,
                        'precio_unitario' => $precio_unitario_db,
                        'stock_minimo' => $stock_minimo_db ?? 0,
                        'unidad_medida' => $unidad_medida_db ?? '-',
                        'ubicacion' => $ubicacion_db ?? '-',
                        'id_proveedor' => $id_proveedor_db, // Puede ser NULL
                        'nombre_proveedor' => $nombre_proveedor_db // Puede ser NULL si no hay proveedor o LEFT JOIN no coincide
                    ];
                } else {
                    // Ítem no encontrado
                    $error_msg = "Ítem no encontrado (ID: " . htmlspecialchars($id_producto_solicitado) . ").";
                }
            } else {
                // Error en la ejecución
                 $error_msg = "Error al ejecutar consulta: (" . $stmt->errno . ") " . htmlspecialchars($stmt->error);
            }
            // Cerrar statement
            $stmt->close();
        } else {
            // Error en la preparación
            $error_msg = "Error al preparar consulta: (" . $conn->errno . ") " . htmlspecialchars($conn->error);
        }
    } else {
        // ID inválido o no proporcionado
        $error_msg = "ID de ítem inválido o no proporcionado.";
    }

    // Cerrar la conexión
    $conn->close();
}

// --- SECCIÓN AÑADIDA: Preparar y Enviar Respuesta JSON ---

// Limpiar cualquier salida anterior (importante si hay warnings/notices)
if (ob_get_level()) {
    ob_end_clean();
}

// Establecer la cabecera de tipo de contenido a JSON
header('Content-Type: application/json; charset=utf-8');

// Construir la respuesta final
if ($item !== null) {
    $response['item'] = $item;
} else {
    // Asegurarnos de que siempre haya un mensaje de error si $item es null
    $response['error'] = $error_msg ?? "Error desconocido al obtener el ítem.";
}

// Codificar la respuesta a JSON y enviarla
echo json_encode($response);

// Terminar la ejecución del script para asegurar que no se envíe nada más
exit;

?>