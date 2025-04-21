<?php

header('Content-Type: application/json; charset=utf-8');

require_once '../conexion.php';

$response = []; 
$item = null;    
$error_msg = null;


if (empty($conn) || !($conn instanceof mysqli)) {
    
    $response['error'] = "Error crítico: No se pudo conectar a la base de datos.";
    
    echo json_encode($response);
    exit;
}


if (isset($_GET['id']) && filter_var($_GET['id'], FILTER_VALIDATE_INT) && $_GET['id'] > 0) {
    
    $id_producto_solicitado = (int)$_GET['id'];

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
            $stmt->bind_result(
                $id_producto_db, $nombre_db, $categoria_db, $descripcion_db, $stock_db,
                $precio_unitario_db, $stock_minimo_db, $unidad_medida_db, $ubicacion_db,
                $id_proveedor_db, $nombre_proveedor_db
            );

            
            if ($stmt->fetch()) {
                $item = [
                    'id_producto' => $id_producto_db,
                    'nombre' => $nombre_db,
                    'categoria' => $categoria_db ?? null,
                    'descripcion' => $descripcion_db ?? null,
                    'stock' => $stock_db,
                    'precio_unitario' => $precio_unitario_db, 
                    'stock_minimo' => $stock_minimo_db ?? null,
                    'unidad_medida' => $unidad_medida_db ?? null,
                    'ubicacion' => $ubicacion_db ?? null,
                    'id_proveedor' => $id_proveedor_db, 
                    'nombre_proveedor' => $nombre_proveedor_db 
                ];
                
                $response['item'] = $item;

            } else {
                
                $error_msg = "Ítem no encontrado (ID: " . htmlspecialchars($id_producto_solicitado) . ").";
            }
        } else {
            
            $error_msg = "Error al ejecutar consulta: (" . $stmt->errno . ") " . htmlspecialchars($stmt->error);
        }
        
        $stmt->close();

    } else {
        
        $error_msg = "Error al preparar consulta: (" . $conn->errno . ") " . htmlspecialchars($conn->error);
    }

} else {
    $error_msg = "ID de ítem inválido o no proporcionado.";
}


if ($conn) {
    $conn->close();
}


if ($error_msg !== null) {
    $response['error'] = $error_msg;
} elseif ($item === null && !isset($response['error'])) {
    $response['error'] = "Ítem no encontrado o error desconocido.";
}


if (ob_get_level()) {
    ob_end_clean();
}

echo json_encode($response);

exit;
?>
