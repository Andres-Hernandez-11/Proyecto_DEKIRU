<?php
// guardar_item.php (Maneja INSERT/UPDATE y crea Mov. Financiero en INSERT)
session_start();

// Incluir archivo de conexión
require_once '../../MODELO/conexion.php'; // ¡VERIFICA RUTA!

// Inicializar variables para mensaje de feedback
$mensaje = 'Acción no válida o datos no recibidos.';
$tipo_mensaje = 'error';

// Verificar conexión
if (empty($conn) || !($conn instanceof mysqli)) {
    $_SESSION['mensaje'] = "Error crítico: No se pudo conectar a la base de datos.";
    $_SESSION['tipo_mensaje'] = "error";
    header('Location: ../../VISTA/Inventario/InventarioStart.php'); // ¡VERIFICA RUTA!
    exit();
}

// --- Procesar Solicitud POST ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 1. Recuperar y Validar datos del formulario
    $id_producto_editar = filter_input(INPUT_POST, 'id_producto', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    $nombre = trim($_POST['nombre'] ?? '');
    $categoria_seleccionada = trim($_POST['categoria'] ?? '');
    $nueva_categoria = trim($_POST['nueva_categoria'] ?? '');
    $stock = filter_input(INPUT_POST, 'stock', FILTER_VALIDATE_INT);
    $stock_minimo = filter_input(INPUT_POST, 'stock_minimo', FILTER_VALIDATE_INT);
    $unidad_medida = trim($_POST['unidad_medida'] ?? '');
    $precio_unitario_str = trim($_POST['precio_unitario'] ?? '');
    $precio_unitario = null;
    if ($precio_unitario_str !== '') {
        $precio_unitario_str_norm = str_replace(',', '.', $precio_unitario_str);
        $precio_unitario = filter_var($precio_unitario_str_norm, FILTER_VALIDATE_FLOAT);
    }
    $ubicacion = trim($_POST['ubicacion'] ?? '');
    $id_proveedor = filter_input(INPUT_POST, 'id_proveedor', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    $descripcion = trim($_POST['descripcion'] ?? '');

    // 2. Validación de datos
    $errores = [];
    if (empty($nombre)) $errores[] = "El nombre es obligatorio.";
    if ($stock === false || $stock === null || $stock < 0) $errores[] = "El stock debe ser >= 0.";
    if (isset($_POST['stock_minimo']) && $_POST['stock_minimo'] !== '' && ($stock_minimo === false || $stock_minimo === null || $stock_minimo < 0)) $errores[] = "El stock mínimo debe ser >= 0.";
    if ($precio_unitario_str !== '' && ($precio_unitario === false || $precio_unitario < 0)) $errores[] = "El precio unitario no es válido.";
    if ($categoria_seleccionada === '__NUEVA__' && empty($nueva_categoria)) $errores[] = "Especifica la nueva categoría.";

    if (!empty($errores)) {
        $_SESSION['mensaje'] = "Error: <br>" . implode("<br>", $errores);
        $_SESSION['tipo_mensaje'] = "error";
        header('Location: ../VISTA/InventarioStart.php'); // ¡VERIFICA RUTA!
        exit();
    }

    // 3. Preparar variables para BD
    $stock_int = (int)$stock;
    $stock_minimo_int = ($stock_minimo !== null && $stock_minimo !== false) ? (int)$stock_minimo : null;
    $precio_decimal = ($precio_unitario !== false && $precio_unitario !== null) ? (float)$precio_unitario : null;
    $proveedor_id_int = ($id_proveedor !== false && $id_proveedor !== null) ? (int)$id_proveedor : null;
    $categoria_final = ($categoria_seleccionada === '__NUEVA__') ? $nueva_categoria : $categoria_seleccionada;

    // --- Inicio Transacción ---
    $conn->begin_transaction();
    $stmt_prod = null;
    $stmt_mov = null;

    try {
        // 4. Determinar si es INSERT o UPDATE
        if ($id_producto_editar) {
            // --- ES UNA ACTUALIZACIÓN (UPDATE) ---
            $sql_prod = "UPDATE PRODUCTO SET nombre = ?, categoria = ?, descripcion = ?, stock = ?, precio_unitario = ?, stock_minimo = ?, unidad_medida = ?, ubicacion = ?, id_proveedor = ? WHERE id_producto = ?";
            $stmt_prod = $conn->prepare($sql_prod);
            if ($stmt_prod === false) throw new Exception("Error preparando UPDATE: " . $conn->error);

            $types_prod = "sssidissii";
            $stmt_prod->bind_param($types_prod, $nombre, $categoria_seleccionada, $descripcion, $stock_int, $precio_decimal, $stock_minimo_int, $unidad_medida, $ubicacion, $proveedor_id_int, $id_producto_editar);

            if (!$stmt_prod->execute()) throw new Exception("Error al ejecutar UPDATE: (" . $stmt_prod->errno . ") " . $stmt_prod->error);

            if ($stmt_prod->affected_rows > 0) {
                $mensaje = "Ítem '" . htmlspecialchars($nombre) . "' actualizado exitosamente.";
                $tipo_mensaje = "success";
            } else {
                $mensaje = "No se realizaron cambios en el ítem '" . htmlspecialchars($nombre) . "'.";
                $tipo_mensaje = "info";
            }
             // **NO** se crea movimiento financiero al actualizar el producto directamente.
             // Los ajustes de stock que impliquen costo deberían hacerse desde Compras o Ajustes de Inventario.

        } else {
            // --- ES UNA INSERCIÓN (INSERT) ---
            $sql_prod = "INSERT INTO PRODUCTO (nombre, categoria, descripcion, stock, precio_unitario, stock_minimo, unidad_medida, ubicacion, id_proveedor) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt_prod = $conn->prepare($sql_prod);
            if ($stmt_prod === false) throw new Exception("Error al preparar INSERT: " . $conn->error);

            $types_prod = "sssidissi";
            $stmt_prod->bind_param($types_prod, $nombre, $categoria_final, $descripcion, $stock_int, $precio_decimal, $stock_minimo_int, $unidad_medida, $ubicacion, $proveedor_id_int);

            if (!$stmt_prod->execute()) throw new Exception("Error al ejecutar INSERT: (" . $stmt_prod->errno . ") " . $stmt_prod->error);

            if ($stmt_prod->affected_rows > 0) {
                $id_producto_creado = $conn->insert_id; // Obtener el ID del nuevo producto

                // --- INICIO: Crear Movimiento Financiero (Solo si hay stock y precio/costo) ---
                if ($stock_int > 0 && $precio_decimal !== null && $precio_decimal > 0) {
                    $monto_egreso = $stock_int * $precio_decimal; // Asume que precio_unitario es el COSTO
                    $tipo_mov_fin = 'Egreso';
                    $categoria_fin = 'Compra Inventario'; // Categoría financiera
                    $descripcion_fin = "Compra/Registro inicial: " . $stock_int . " x " . htmlspecialchars($nombre) . " (ID Prod: " . $id_producto_creado . ")";
                    $fecha_mov = date('Y-m-d'); // Fecha actual
                    $hora_mov = date('H:i:s'); // Hora actual

                    $sql_mov = "INSERT INTO MOVIMIENTO_FINANCIERO
                                (fecha, hora, tipo, categoria, descripcion, monto)
                                VALUES (?, ?, ?, ?, ?, ?)"; // id_compra_origen se deja NULL
                    $stmt_mov = $conn->prepare($sql_mov);
                    if (!$stmt_mov) throw new Exception("Error preparando Mov. Financiero: " . $conn->error);

                    $types_mov = "sssssd";
                    $stmt_mov->bind_param($types_mov, $fecha_mov, $hora_mov, $tipo_mov_fin, $categoria_fin, $descripcion_fin, $monto_egreso);

                    if (!$stmt_mov->execute()) throw new Exception("Error guardando Mov. Financiero: (" . $stmt_mov->errno . ") " . $stmt_mov->error);

                    $stmt_mov->close(); // Cerrar statement de movimiento

                    $mensaje = "Ítem '" . htmlspecialchars($nombre) . "' agregado y movimiento financiero registrado exitosamente.";
                    $tipo_mensaje = "success";

                } else {
                    // Se agregó el ítem pero sin stock o precio, no se genera movimiento financiero
                    $mensaje = "Ítem '" . htmlspecialchars($nombre) . "' agregado exitosamente (sin movimiento financiero por stock/precio cero o nulo).";
                    $tipo_mensaje = "success"; // Sigue siendo éxito para el producto
                }
                 // --- FIN: Crear Movimiento Financiero ---

            } else {
                 $mensaje = "El ítem no pudo ser agregado (0 filas afectadas).";
                 $tipo_mensaje = "warning";
            }
        }

        // Si todo fue bien, confirmar transacción
        $conn->commit();

    } catch (Exception $e) {
        // Si algo falló, revertir transacción
        $conn->rollback();
        $mensaje = "Error al procesar la solicitud: " . $e->getMessage();
        $tipo_mensaje = "error";
    } finally {
        // Cerrar statement de producto si se abrió
        if ($stmt_prod instanceof mysqli_stmt) {
            $stmt_prod->close();
        }
        // Cerrar statement de movimiento si se abrió (ya se cierra dentro del if)
        // if ($stmt_mov instanceof mysqli_stmt) {
        //     $stmt_mov->close();
        // }
    }

} else {
    $mensaje = "Acceso no válido.";
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
