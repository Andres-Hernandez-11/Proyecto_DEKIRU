<?php
session_start();
require_once '../conexion.php'; // Asegúrate que la ruta es correcta

$mensaje = 'Acción no válida.';
$tipo_mensaje = 'error';

// --- Verificar Conexión ---
if (empty($conn) || !($conn instanceof mysqli)) {
    $_SESSION['mensaje'] = "Error crítico: No se pudo conectar a la base de datos.";
    $_SESSION['tipo_mensaje'] = "error";
    header('Location: ../../VISTA/Ventas/Ventas.php');
    exit();
}

// --- Procesar Solicitud POST ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 1. Recuperar datos del formulario (manteniendo la versión raw para strlen)
    $id_cliente_raw = trim($_POST['id_cliente'] ?? '');
    $id_vendedor_raw = trim($_POST['id_vendedor'] ?? '');
    $fecha = trim($_POST['fecha'] ?? '');
    $origen = trim($_POST['origen'] ?? '');
    $destino = trim($_POST['destino'] ?? '');
    $total_raw = trim($_POST['total'] ?? '');
    $metodo_pago = trim($_POST['metodo_pago'] ?? '');
    $asiento = trim($_POST['asiento'] ?? '');
    $hora = trim($_POST['hora'] ?? '');
    $id_bus_raw = trim($_POST['id_bus'] ?? '');

    // 2. Validación de campos obligatorios básicos
    if (empty($id_cliente_raw)) {
        $mensaje = "El ID de Cliente es obligatorio.";
    } elseif (empty($id_vendedor_raw)) {
        $mensaje = "El ID de Vendedor es obligatorio.";
    } elseif (empty($fecha)) {
        $mensaje = "La Fecha es obligatoria.";
    } elseif (empty($origen)) {
        $mensaje = "El Origen es obligatorio.";
    } elseif (empty($destino)) {
        $mensaje = "El Destino es obligatorio.";
    } elseif ($total_raw === '') {
        $mensaje = "El Total es obligatorio.";
    } elseif (empty($metodo_pago)) {
        $mensaje = "El Método de Pago es obligatorio.";
    } elseif (empty($asiento)) {
        $mensaje = "El Asiento es obligatorio.";
    } elseif (empty($hora)) {
        $mensaje = "La Hora es obligatoria.";
    } elseif (empty($id_bus_raw)) {
        $mensaje = "El ID de Bus es obligatorio.";
    } else {
        // 3. Validación de Tipos y Longitudes Numéricas

        // Validar id_cliente
        if (!ctype_digit($id_cliente_raw)) { // Verifica si TODOS son dígitos
            $mensaje = "ID Cliente debe contener solo números.";
        } elseif (strlen($id_cliente_raw) > 10) {
            $mensaje = "ID Cliente no puede tener más de 10 dígitos.";

            // Validar id_vendedor
        } elseif (!ctype_digit($id_vendedor_raw)) {
            $mensaje = "ID Vendedor debe contener solo números.";
        } elseif (strlen($id_vendedor_raw) > 10) {
            $mensaje = "ID Vendedor no puede tener más de 10 dígitos.";

            // Validar total (permite decimales, verifica 6 dígitos enteros)
        } elseif (!is_numeric($total_raw)) {
            $mensaje = "El Total debe ser un valor numérico.";
        } elseif (strpos($total_raw, '.') !== false && strlen(substr($total_raw, 0, strpos($total_raw, '.'))) > 6) {
            $mensaje = "El Total no puede tener más de 6 dígitos antes del punto decimal.";
        } elseif (strpos($total_raw, '.') === false && strlen($total_raw) > 6) {
            $mensaje = "El Total no puede tener más de 6 dígitos.";
        } elseif ((float)$total_raw < 0) {
            $mensaje = "El Total no puede ser negativo.";

            // Validar id_bus
        } elseif (!ctype_digit($id_bus_raw)) {
            $mensaje = "ID Bus debe contener solo números.";
        } elseif (strlen($id_bus_raw) > 4) {
            $mensaje = "ID Bus no puede tener más de 4 dígitos.";

        } else {
            $id_cliente = (int)$id_cliente_raw;
            $id_vendedor = (int)$id_vendedor_raw;
            $total = (float)$total_raw; // Convertir a float para la BD
            $id_bus = (int)$id_bus_raw;

            // 4. Preparar la consulta INSERT
            $sql = "INSERT INTO VENTA (id_cliente, id_vendedor, fecha, hora, origen, destino, total, metodo_pago, id_bus, asiento) 
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $types = "iissssdsss"; // 10 tipos

            $stmt = $conn->prepare($sql);

            if ($stmt === false) {
                $mensaje = "Error al preparar la consulta INSERT: " . htmlspecialchars($conn->error);
            } else {
                $stmt->bind_param(// Vincular parámetros para INSERT
                    $types,
                    $id_cliente, $id_vendedor, $fecha, $hora, $origen, $destino, $total, $metodo_pago, $id_bus, $asiento
                );

                // 5. Ejecutar la consulta
                if ($stmt->execute()) {
                    if ($stmt->affected_rows > 0) {
                        $mensaje = "Venta agregada exitosamente.";
                        $tipo_mensaje = "success";
                    } else {
                        $mensaje = "No se pudo agregar la venta (affected_rows = 0)."; // Mensaje más específico
                        $tipo_mensaje = "warning";
                    }
                } else {
                    // Manejo de errores específicos (ej. Clave duplicada)
                    if ($stmt->errno == 1062) { // Código de error para entrada duplicada
                        $mensaje = "Error: Ya existe un registro con alguna clave única (Verifica IDs, etc.).";
                    } else {
                        $mensaje = "Error al ejecutar la consulta: (" . $stmt->errno . ") " . htmlspecialchars($stmt->error);
                    }
                    $tipo_mensaje = "error";
                }
                $stmt->close();
            }
        } // Fin 'else' de validaciones
    } // Fin 'else' de campos obligatorios
} // Fin IF REQUEST_METHOD

// --- Guardar Mensaje y Cerrar Conexión ---
$_SESSION['mensaje'] = $mensaje;
$_SESSION['tipo_mensaje'] = $tipo_mensaje;
if ($conn) {
    $conn->close();
}
// --- Redirigir ---
header('Location: ../../VISTA/Ventas/Ventas.php');
exit();
?>