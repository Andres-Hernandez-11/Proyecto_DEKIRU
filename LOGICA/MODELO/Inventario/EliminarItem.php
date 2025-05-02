<?php
// eliminar_item.php (Elimina producto y crea ajuste financiero)
session_start();

require_once '../../MODELO/conexion.php'; // ¡VERIFICA RUTA!

// Inicializar variables
$id_producto_a_eliminar = null;
$mensaje = 'Acción no válida o ID no proporcionado.';
$tipo_mensaje = 'error';

// Verificar conexión
if (empty($conn) || !($conn instanceof mysqli)) {
    $_SESSION['mensaje'] = "Error crítico: Conexión a BD fallida.";
    $_SESSION['tipo_mensaje'] = "error";
    header('Location: ../../VISTA/Inventario/InventarioStart.php'); // ¡VERIFICA RUTA!
    exit();
}

// Verificar si se recibió un ID válido por GET (o POST si cambiaste el form del modal)
if (isset($_REQUEST['id']) && filter_var($_REQUEST['id'], FILTER_VALIDATE_INT)) {
    $id_producto_a_eliminar = (int)$_REQUEST['id'];

    // --- Iniciar Transacción ---
    $conn->begin_transaction();
    $stmt_get = null;
    $stmt_delete = null;
    $stmt_mov = null;

    try {
        // 1. Obtener datos del producto ANTES de eliminarlo (stock y precio/costo)
        $sql_get = "SELECT nombre, stock, precio_unitario FROM PRODUCTO WHERE id_producto = ?";
        $stmt_get = $conn->prepare($sql_get);
        if (!$stmt_get) throw new Exception("Error preparando consulta SELECT: " . $conn->error);

        $stmt_get->bind_param("i", $id_producto_a_eliminar);
        if (!$stmt_get->execute()) throw new Exception("Error ejecutando SELECT: " . $stmt_get->error);

        $nombre_producto = null;
        $stock_actual = 0;
        $precio_costo = 0.00;
        $stmt_get->bind_result($nombre_producto, $stock_actual, $precio_costo);

        if (!$stmt_get->fetch()) {
            // El producto no existe, no hay nada que eliminar ni ajustar
            throw new Exception("No se encontró ningún ítem con el ID " . htmlspecialchars($id_producto_a_eliminar) . ".");
        }
        $stmt_get->close(); // Cerrar statement SELECT

        // Convertir a tipos numéricos correctos
        $stock_actual_int = (int)$stock_actual;
        $precio_costo_float = ($precio_costo !== null) ? (float)$precio_costo : 0.00;
        $valor_ajuste = $stock_actual_int * $precio_costo_float;

        // 2. Eliminar el producto de la tabla PRODUCTO
        $sql_delete = "DELETE FROM PRODUCTO WHERE id_producto = ?";
        $stmt_delete = $conn->prepare($sql_delete);
        if (!$stmt_delete) throw new Exception("Error preparando DELETE: " . $conn->error);

        $stmt_delete->bind_param("i", $id_producto_a_eliminar);

        if (!$stmt_delete->execute()) {
             // Error podría ser por restricciones FK si no tienes ON DELETE CASCADE en tablas dependientes
            throw new Exception("Error al ejecutar DELETE: (" . $stmt_delete->errno . ") " . $stmt_delete->error);
        }

        // Verificar si realmente se eliminó (affected_rows debería ser 1 si existía)
        if ($stmt_delete->affected_rows <= 0) {
             // Esto no debería pasar si fetch() funcionó antes, pero por seguridad
             throw new Exception("No se eliminó el ítem (affected_rows=0), ID: " . htmlspecialchars($id_producto_a_eliminar) . ".");
        }
        $stmt_delete->close(); // Cerrar statement DELETE

        // 3. Crear Movimiento Financiero de Ajuste (si había valor)
        if ($valor_ajuste > 0) {
            $tipo_mov_fin = 'Ingreso'; // O 'Ajuste Positivo' si prefieres
            $categoria_fin = 'Ajuste Inventario'; // Categoría específica
            $descripcion_fin = "Ajuste por eliminación de inventario: " . $stock_actual_int . " x " . htmlspecialchars($nombre_producto) . " (ID Prod: " . $id_producto_a_eliminar . ")";
            $fecha_mov = date('Y-m-d');
            $hora_mov = date('H:i:s');

            $sql_mov = "INSERT INTO MOVIMIENTO_FINANCIERO
                        (fecha, hora, tipo, categoria, descripcion, monto)
                        VALUES (?, ?, ?, ?, ?, ?)";
            $stmt_mov = $conn->prepare($sql_mov);
            if (!$stmt_mov) throw new Exception("Error preparando Mov. Financiero: " . $conn->error);

            $types_mov = "sssssd";
            $stmt_mov->bind_param($types_mov, $fecha_mov, $hora_mov, $tipo_mov_fin, $categoria_fin, $descripcion_fin, $valor_ajuste);

            if (!$stmt_mov->execute()) throw new Exception("Error guardando Mov. Financiero: (" . $stmt_mov->errno . ") " . $stmt_mov->error);

            if ($stmt_mov->affected_rows <= 0) throw new Exception("No se insertó el Mov. Financiero (0 filas afectadas).");

            $stmt_mov->close(); // Cerrar statement de movimiento
        }

        // 4. Si todo fue bien, confirmar la transacción
        $conn->commit();
        $mensaje = "Ítem '" . htmlspecialchars($nombre_producto) . "' (ID: " . $id_producto_a_eliminar . ") eliminado y ajuste financiero registrado exitosamente.";
        $tipo_mensaje = "success";

    } catch (Exception $e) {
        // 5. Si algo falló, revertir la transacción
        $conn->rollback();
        $mensaje = "Error al procesar la eliminación: " . $e->getMessage();
        $tipo_mensaje = "error";
        // Opcional: Loggear el error $e->getMessage()
    } finally {
        // Cerrar statements si aún están abiertos (en caso de error antes del close)
        if ($stmt_get instanceof mysqli_stmt && $stmt_get->errno === 0) @$stmt_get->close(); // @ para suprimir warning si ya está cerrado
        if ($stmt_delete instanceof mysqli_stmt && $stmt_delete->errno === 0) @$stmt_delete->close();
        if ($stmt_mov instanceof mysqli_stmt && $stmt_mov->errno === 0) @$stmt_mov->close();
    }

} else {
    // ID no válido o no proporcionado
    $mensaje = "ID de ítem no válido o no proporcionado para eliminar.";
    $tipo_mensaje = "error";
}

// Cerrar conexión
if (isset($conn) && $conn instanceof mysqli) {
    $conn->close();
}

// Guardar mensaje en sesión y redirigir
$_SESSION['mensaje'] = $mensaje;
$_SESSION['tipo_mensaje'] = $tipo_mensaje;
header('Location: ../../VISTA/Inventario/InventarioStart.php'); // ¡Ajusta la ruta de redirección!
exit();

?>
