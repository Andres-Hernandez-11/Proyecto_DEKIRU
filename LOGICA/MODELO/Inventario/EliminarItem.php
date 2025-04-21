<?php

session_start();


require_once '../conexion.php';

// Verificar si la conexión se estableció correctamente
if (!$conn) {
    $_SESSION['mensaje'] = "Error crítico: No se pudo conectar a la base de datos.";
    $_SESSION['tipo_mensaje'] = "error";
    header('Location: ../../VISTA/Inventario/InventarioStart.php'); // Ajusta la ruta de redirección
    exit();
}

// Inicializar variables
$id_producto_a_eliminar = null;
$mensaje = '';
$tipo_mensaje = 'error'; 

if (isset($_GET['id']) && filter_var($_GET['id'], FILTER_VALIDATE_INT)) {
    $id_producto_a_eliminar = (int)$_GET['id'];

    
    $sql = "DELETE FROM PRODUCTO WHERE id_producto = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        
        $stmt->bind_param("i", $id_producto_a_eliminar);

       
        if ($stmt->execute()) {
            
            if ($stmt->affected_rows > 0) {
                $mensaje = "Ítem (ID: " . htmlspecialchars($id_producto_a_eliminar) . ") eliminado exitosamente.";
                $tipo_mensaje = "success";
            } else {
                
                $mensaje = "No se encontró ningún ítem con el ID " . htmlspecialchars($id_producto_a_eliminar) . " para eliminar.";
                $tipo_mensaje = "warning";
            }
        } else {
            $mensaje = "Error al intentar eliminar el ítem. Es posible que esté asociado a otros registros (compras, movimientos, etc.). Error: " . $stmt->error; // Mostrar error (cuidado en producción)
            $tipo_mensaje = "error";
        }
        $stmt->close();
    } else {
        $mensaje = "Error al preparar la consulta de eliminación: " . $conn->error;
        $tipo_mensaje = "error";
    }

} else {
    $mensaje = "ID de ítem no válido o no proporcionado para eliminar.";
    $tipo_mensaje = "error";
}

if ($conn) {
    $conn->close();
}
$_SESSION['mensaje'] = $mensaje;
$_SESSION['tipo_mensaje'] = $tipo_mensaje;

header('Location: ../../VISTA/Inventario/InventarioStart.php');
exit();

?>
