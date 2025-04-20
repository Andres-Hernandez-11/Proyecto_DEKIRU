<?php
// Iniciar sesión para poder usar variables de sesión para mensajes
session_start();

require_once '../conexion.php'; 

// --- Inicialización de Variables ---
$mensaje = 'Acción no válida.'; // Mensaje por defecto
$tipo_mensaje = 'error';      // Tipo de mensaje por defecto
$conn_closed = false;         // Bandera para saber si la conexión ya se cerró

// --- Verificar Conexión ---
if (empty($conn) || !($conn instanceof mysqli)) {
    $_SESSION['mensaje'] = "Error crítico: No se pudo conectar a la base de datos.";
    $_SESSION['tipo_mensaje'] = "error";
    header('Location: ../../VISTA/Inventario/InventarioStart.php'); // Ajusta la ruta de redirección
    exit();
}

// --- Procesar Solicitud POST ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 1. Recuperar datos del formulario (comunes para INSERT y UPDATE)
    $id_producto_editar = filter_input(INPUT_POST, 'id_producto', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]); // Validar ID para edición
    $nombre = trim($_POST['nombre'] ?? '');
    $categoria_seleccionada = trim($_POST['categoria'] ?? '');
    $nueva_categoria = trim($_POST['nueva_categoria'] ?? ''); // Solo relevante para INSERT
    $stock = filter_input(INPUT_POST, 'stock', FILTER_VALIDATE_INT);
    $stock_minimo = filter_input(INPUT_POST, 'stock_minimo', FILTER_VALIDATE_INT);
    $unidad_medida = trim($_POST['unidad_medida'] ?? '');
    $precio_unitario_str = str_replace(',', '.', trim($_POST['precio_unitario'] ?? '')); // Reemplazar coma por punto
    $precio_unitario = filter_var($precio_unitario_str, FILTER_VALIDATE_FLOAT);
    $ubicacion = trim($_POST['ubicacion'] ?? '');
    $id_proveedor = filter_input(INPUT_POST, 'id_proveedor', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    $descripcion = trim($_POST['descripcion'] ?? '');

    // 2. Validación básica (común)
    if (empty($nombre)) {
        $mensaje = "El nombre del producto es obligatorio.";
        // No salimos aún, cerramos conexión y redirigimos al final
    } elseif ($stock === false || $stock < 0) { // Validar stock como entero >= 0
        $mensaje = "El stock debe ser un número entero igual o mayor a cero.";
    } elseif ($stock_minimo !== null && ($stock_minimo === false || $stock_minimo < 0)) { // Validar stock mínimo si se proporciona
         $mensaje = "El stock mínimo debe ser un número entero igual o mayor a cero.";
    } elseif ($precio_unitario_str !== '' && $precio_unitario === false) { // Validar precio si se proporciona
         $mensaje = "El precio unitario no es un número válido.";
    } else {
        // Las validaciones básicas pasaron, proceder con INSERT o UPDATE

        // Convertir valores a tipos adecuados o NULL
        $stock_int = ($stock !== false) ? (int)$stock : 0; // Default 0 si falla validación (aunque ya se validó)
        $stock_minimo_int = ($stock_minimo !== null && $stock_minimo !== false) ? (int)$stock_minimo : null; // Permitir NULL
        $precio_decimal = ($precio_unitario !== false && $precio_unitario_str !== '') ? (float)$precio_unitario : null; // Permitir NULL
        $proveedor_id_int = ($id_proveedor !== false && $id_proveedor !== null) ? (int)$id_proveedor : null; // Permitir NULL

        // 3. Determinar si es INSERT o UPDATE
        if ($id_producto_editar) {
            // --- LÓGICA DE ACTUALIZACIÓN (UPDATE) ---
            $mensaje = "Intentando actualizar ítem ID: " . $id_producto_editar; // Mensaje temporal

            // Preparar la consulta UPDATE
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
                    WHERE id_producto = ?"; // Condición WHERE

            $stmt = $conn->prepare($sql);

            if ($stmt === false) {
                $mensaje = "Error al preparar la consulta UPDATE: " . htmlspecialchars($conn->error);
            } else {
                // Vincular parámetros para UPDATE
                // Tipos: s (nombre), s (categoria), s (descripcion), i (stock), d (precio), i (stock_minimo), s (unidad), s (ubicacion), i (id_proveedor), i (id_producto WHERE)
                $types = "sssidissii";
                $stmt->bind_param(
                    $types,
                    $nombre,
                    $categoria_seleccionada, // Usar la categoría seleccionada directamente
                    $descripcion,
                    $stock_int,
                    $precio_decimal,
                    $stock_minimo_int,
                    $unidad_medida,
                    $ubicacion,
                    $proveedor_id_int,
                    $id_producto_editar // ID para el WHERE
                );

                // Ejecutar la consulta UPDATE
                if ($stmt->execute()) {
                    if ($stmt->affected_rows > 0) {
                        $mensaje = "Ítem '" . htmlspecialchars($nombre) . "' (ID: " . $id_producto_editar . ") actualizado exitosamente.";
                        $tipo_mensaje = "success";
                    } else {
                        // No hubo error, pero no se modificó ninguna fila (quizás los datos eran iguales)
                        $mensaje = "No se realizaron cambios en el ítem '" . htmlspecialchars($nombre) . "' (ID: " . $id_producto_editar . "). Los datos pueden ser los mismos.";
                        $tipo_mensaje = "info"; // O 'warning'
                    }
                } else {
                    // Error al ejecutar UPDATE
                    $mensaje = "Error al actualizar el ítem: (" . $stmt->errno . ") " . htmlspecialchars($stmt->error);
                }
                $stmt->close();
            } // Fin if ($stmt)

        } else {
            // --- LÓGICA DE INSERCIÓN (INSERT) ---
            $mensaje = "Intentando agregar nuevo ítem."; // Mensaje temporal

            // Determinar la categoría final (manejo de nueva categoría)
            $categoria_final = '';
            if ($categoria_seleccionada === '__NUEVA__') {
                if (!empty($nueva_categoria)) {
                    $categoria_final = $nueva_categoria;
                } else {
                    $mensaje = "Por favor, especifica el nombre de la nueva categoría.";
                    // Salta a la sección de cierre y redirección
                    goto end_logic; // Usamos goto para evitar anidación excesiva
                }
            } else {
                $categoria_final = $categoria_seleccionada;
            }

            // Preparar la consulta INSERT
            $sql = "INSERT INTO PRODUCTO (nombre, categoria, descripcion, stock, precio_unitario, stock_minimo, unidad_medida, ubicacion, id_proveedor)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $stmt = $conn->prepare($sql);

            if ($stmt === false) {
                $mensaje = "Error al preparar la consulta INSERT: " . htmlspecialchars($conn->error);
            } else {
                // Vincular parámetros para INSERT
                // Tipos: s (nombre), s (categoria), s (descripcion), i (stock), d (precio), i (stock_minimo), s (unidad), s (ubicacion), i (id_proveedor)
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

                // Ejecutar la consulta INSERT
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
            } // Fin if ($stmt)
        } // Fin else (INSERT)
    } // Fin else (Validaciones básicas pasaron)

} else {
    // Si no se accedió por POST
    $mensaje = "Acceso no válido para guardar/editar ítem.";
}

// --- Etiqueta para goto (evita cerrar conexión dos veces si falla validación de categoría) ---
end_logic:

// --- Guardar Mensaje y Cerrar Conexión (si no se cerró antes) ---
$_SESSION['mensaje'] = $mensaje;
$_SESSION['tipo_mensaje'] = $tipo_mensaje;

if (!$conn_closed && $conn) {
    $conn->close();
}

// --- Redirigir Siempre ---
// ¡ASEGÚRATE DE QUE LA RUTA SEA CORRECTA!
header('Location: ../../VISTA/Inventario/InventarioStart.php');
exit();

?>
