<?php
session_start();

require_once '../conexion.php'; 

// --- Inicialización de Variables ---
$mensaje = 'Acción no válida.'; 
$tipo_mensaje = 'error';      
$conn_closed = false;         

// --- Verificar Conexión ---
if (empty($conn) || !($conn instanceof mysqli)) {
    $_SESSION['mensaje'] = "Error crítico: No se pudo conectar a la base de datos.";
    $_SESSION['tipo_mensaje'] = "error";
    header('Location: ../../VISTA/Inventario/InventarioStart.php');
    exit();
}

// --- Procesar Solicitud POST ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    
    $id_producto_editar = filter_input(INPUT_POST, 'id_producto', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    $nombre = trim($_POST['nombre'] ?? '');
    $categoria_seleccionada = trim($_POST['categoria'] ?? '');
    $nueva_categoria = trim($_POST['nueva_categoria'] ?? ''); 
    $stock = filter_input(INPUT_POST, 'stock', FILTER_VALIDATE_INT);
    $stock_minimo = filter_input(INPUT_POST, 'stock_minimo', FILTER_VALIDATE_INT);
    $unidad_medida = trim($_POST['unidad_medida'] ?? '');
    $precio_unitario_str = str_replace(',', '.', trim($_POST['precio_unitario'] ?? '')); 
    $precio_unitario = filter_var($precio_unitario_str, FILTER_VALIDATE_FLOAT);
    $ubicacion = trim($_POST['ubicacion'] ?? '');
    $id_proveedor = filter_input(INPUT_POST, 'id_proveedor', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    $descripcion = trim($_POST['descripcion'] ?? '');

    // 2. Validación básica (común)
    if (empty($nombre)) {
        $mensaje = "El nombre del producto es obligatorio.";
    } elseif ($stock === false || $stock < 0) {
        $mensaje = "El stock debe ser un número entero igual o mayor a cero.";
    } elseif ($stock_minimo !== null && ($stock_minimo === false || $stock_minimo < 0)) {
         $mensaje = "El stock mínimo debe ser un número entero igual o mayor a cero.";
    } elseif ($precio_unitario_str !== '' && $precio_unitario === false) {
         $mensaje = "El precio unitario no es un número válido.";
    } else {

        
        $stock_int = ($stock !== false) ? (int)$stock : 0; 
        $stock_minimo_int = ($stock_minimo !== null && $stock_minimo !== false) ? (int)$stock_minimo : null; 
        $precio_decimal = ($precio_unitario !== false && $precio_unitario_str !== '') ? (float)$precio_unitario : null; 
        $proveedor_id_int = ($id_proveedor !== false && $id_proveedor !== null) ? (int)$id_proveedor : null; 

        // 3. Determinar si es INSERT o UPDATE
        if ($id_producto_editar) {
            $mensaje = "Intentando actualizar ítem ID: " . $id_producto_editar; 


            $sql = "UPDATE PRODUCTO SET
                        nombre = ?,
                        categoria = ?,
                        descripcion = ?,
                        stock = ?,
                        precio_unitario = ?,
                        stock_minimo = ?,
                        unidad_medida = ?,
                        ubicacion = ?,
                        id_proveedor = ?
                    WHERE id_producto = ?"; 

            $stmt = $conn->prepare($sql);

            if ($stmt === false) {
                $mensaje = "Error al preparar la consulta UPDATE: " . htmlspecialchars($conn->error);
            } else {
                $types = "sssidissii";
                $stmt->bind_param(
                    $types,
                    $nombre,
                    $categoria_seleccionada, 
                    $descripcion,
                    $stock_int,
                    $precio_decimal,
                    $stock_minimo_int,
                    $unidad_medida,
                    $ubicacion,
                    $proveedor_id_int,
                    $id_producto_editar 
                );

            
                if ($stmt->execute()) {
                    if ($stmt->affected_rows > 0) {
                        $mensaje = "Ítem '" . htmlspecialchars($nombre) . "' (ID: " . $id_producto_editar . ") actualizado exitosamente.";
                        $tipo_mensaje = "success";
                    } else {
                
                        $mensaje = "No se realizaron cambios en el ítem '" . htmlspecialchars($nombre) . "' (ID: " . $id_producto_editar . "). Los datos pueden ser los mismos.";
                        $tipo_mensaje = "info"; // O 'warning'
                    }
                } else {
                    
                    $mensaje = "Error al actualizar el ítem: (" . $stmt->errno . ") " . htmlspecialchars($stmt->error);
                }
                $stmt->close();
            } 

        } else {
           $mensaje = "Intentando agregar nuevo ítem."; // Mensaje temporal

            
            $categoria_final = '';
            if ($categoria_seleccionada === '__NUEVA__') {
                if (!empty($nueva_categoria)) {
                    $categoria_final = $nueva_categoria;
                } else {
                    $mensaje = "Por favor, especifica el nombre de la nueva categoría.";
                    goto end_logic; 
                }
            } else {
                $categoria_final = $categoria_seleccionada;
            }

            
            $sql = "INSERT INTO PRODUCTO (nombre, categoria, descripcion, stock, precio_unitario, stock_minimo, unidad_medida, ubicacion, id_proveedor)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $stmt = $conn->prepare($sql);

            if ($stmt === false) {
                $mensaje = "Error al preparar la consulta INSERT: " . htmlspecialchars($conn->error);
            } else { 
                $types = "sssidissi";
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
                    $proveedor_id_int
                );

                
                if ($stmt->execute()) {
                    if ($stmt->affected_rows > 0) {
                        $_SESSION['mensaje'] = "Ítem '" . htmlspecialchars($nombre) . "' agregado exitosamente.";
                        $_SESSION['tipo_mensaje'] = "success";
                    } else {
                        $_SESSION['mensaje'] = "El ítem no pudo ser agregado (0 filas afectadas).";
                        $_SESSION['tipo_mensaje'] = "warning";
                    }
                } else {
                    $_SESSION['mensaje'] = "Error al guardar el nuevo ítem: (" . $stmt->errno . ") " . htmlspecialchars($stmt->error);
                    $_SESSION['tipo_mensaje'] = "error";
                }
                $stmt->close();
            } 
        } 
    }

} else {
    $mensaje = "Acceso no válido para guardar/editar ítem.";
}

end_logic:

$_SESSION['mensaje'] = $mensaje;
$_SESSION['tipo_mensaje'] = $tipo_mensaje;

if (!$conn_closed && $conn) {
    $conn->close();
}

header('Location: ../../VISTA/Inventario/InventarioStart.php');
exit();

?>
