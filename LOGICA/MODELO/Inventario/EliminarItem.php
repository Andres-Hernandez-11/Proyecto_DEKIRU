<?php

session_start();


require_once '../conexion.php'; // Asumiendo que está en CONTROLADOR

// Verificar si la conexión se estableció correctamente
if (!$conn) {
    $_SESSION['mensaje'] = "Error crítico: No se pudo conectar a la base de datos.";
    $_SESSION['tipo_mensaje'] = "error";
    header('Location: ../../VISTA/InventarioStart.php'); // Ajusta la ruta de redirección
    exit();
}

// Inicializar variables
$id_producto_a_eliminar = null;
$mensaje = '';
$tipo_mensaje = 'error'; // Por defecto es error

// Verificar si se recibió un ID válido por GET
if (isset($_GET['id']) && filter_var($_GET['id'], FILTER_VALIDATE_INT)) {
    $id_producto_a_eliminar = (int)$_GET['id'];

    // Preparar la consulta DELETE usando sentencias preparadas
    $sql = "DELETE FROM PRODUCTO WHERE id_producto = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        // Vincular el parámetro ID
        $stmt->bind_param("i", $id_producto_a_eliminar);

        // Ejecutar la consulta
        if ($stmt->execute()) {
            // Verificar si se eliminó alguna fila
            if ($stmt->affected_rows > 0) {
                $mensaje = "Ítem (ID: " . htmlspecialchars($id_producto_a_eliminar) . ") eliminado exitosamente.";
                $tipo_mensaje = "success";
            } else {
                // No se eliminó ninguna fila, probablemente el ID no existía
                $mensaje = "No se encontró ningún ítem con el ID " . htmlspecialchars($id_producto_a_eliminar) . " para eliminar.";
                $tipo_mensaje = "warning";
            }
        } else {
            // Error al ejecutar la consulta DELETE
            // Podría ser por restricciones de clave foránea si no usaste ON DELETE CASCADE/SET NULL
            // En producción, loggear el error $stmt->error
            $mensaje = "Error al intentar eliminar el ítem. Es posible que esté asociado a otros registros (compras, movimientos, etc.). Error: " . $stmt->error; // Mostrar error (cuidado en producción)
            $tipo_mensaje = "error";
        }
        // Cerrar statement
        $stmt->close();
    } else {
        // Error al preparar la consulta
        $mensaje = "Error al preparar la consulta de eliminación: " . $conn->error;
        $tipo_mensaje = "error";
    }

} else {
    // ID no válido o no proporcionado
    $mensaje = "ID de ítem no válido o no proporcionado para eliminar.";
    $tipo_mensaje = "error";
}

// Cerrar conexión
if ($conn) {
    $conn->close();
}

// Guardar mensaje en sesión
$_SESSION['mensaje'] = $mensaje;
$_SESSION['tipo_mensaje'] = $tipo_mensaje;

// Redirigir siempre de vuelta a la página de inventario
// ¡ASEGÚRATE DE QUE LA RUTA SEA CORRECTA!
header('Location: ../../VISTA/InventarioStart.php');
exit();

?>
