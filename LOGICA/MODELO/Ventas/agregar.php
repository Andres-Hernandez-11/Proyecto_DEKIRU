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
    header('Location: ../../VISTA/Ventas/Ventas.php');
    exit();
}
// --- Procesar Solicitud POST ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 1. Recuperar datos del formulario
    $id_venta = trim($_POST['id_venta'] ?? '');
    $id_cliente = trim($_POST['id_cliente'] ?? '');
    $id_vendedor = trim($_POST['id_vendedor'] ?? '');
    $fecha = trim($_POST['fecha'] ?? '');
    $origen = trim($_POST['origen'] ?? '');
    $destino = trim($_POST['destino'] ?? '');
    // Total debe ser validado como número
    $total_str = str_replace(',', '.', trim($_POST['total'] ?? ''));
    $total = filter_var($total_str, FILTER_VALIDATE_FLOAT);
    $metodo_pago = trim($_POST['metodo_pago'] ?? '');
    $asiento = trim($_POST['asiento'] ?? '');
    $hora = trim($_POST['hora'] ?? '');
    $id_bus = trim($_POST['id_bus'] ?? '');
    // 2. Validación básica (¡Similar a tu código!)
    if (empty($id_venta)) {
        $mensaje = "El ID de Venta es obligatorio.";
    } elseif (empty($id_cliente)) {
        $mensaje = "El ID de Cliente es obligatorio.";
    } elseif (empty($id_vendedor)) {
        $mensaje = "El ID de Vendedor es obligatorio.";
    } elseif (empty($fecha)) {
        $mensaje = "La Fecha es obligatoria.";
    } elseif (empty($origen)) {
        $mensaje = "El Origen es obligatorio.";
    } elseif (empty($destino)) {
        $mensaje = "El Destino es obligatorio.";
    } elseif ($total === false) {
        $mensaje = "El Total no es un número válido.";
    } elseif (empty($metodo_pago)) {
        $mensaje = "El Método de Pago es obligatorio.";
    } elseif (empty($asiento)) {
        $mensaje = "El Asiento es obligatorio.";
    } elseif (empty($hora)) {
        $mensaje = "La Hora es obligatoria.";
    } elseif (empty($id_bus)) {
        $mensaje = "El ID de Bus es obligatorio.";
    } else {
        // 3. Preparar la consulta INSERT
        $sql = "INSERT INTO VENTA (id_venta, id_cliente, id_vendedor, fecha, hora, origen, destino, total, metodo_pago, id_bus, asiento) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        if ($stmt === false) {
            $mensaje = "Error al preparar la consulta INSERT: " . htmlspecialchars($conn->error);
        } else {
            // Vincular parámetros para INSERT
            $types = "iiissssdsss";
            $stmt->bind_param(
                $types,
                $id_venta,
                $id_cliente,
                $id_vendedor,
                $fecha,
                $hora,
                $origen,
                $destino,
                $total,
                $metodo_pago,
                $id_bus,
                $asiento
            );
            // 4. Ejecutar la consulta
            if ($stmt->execute()) {
                if ($stmt->affected_rows > 0) {
                    $mensaje = "Venta agregada exitosamente.";
                    $tipo_mensaje = "success";
                } else {
                    $mensaje = "No se pudo agregar la venta.";
                    $tipo_mensaje = "warning";
                }
            } else {
                $mensaje = "Error al ejecutar la consulta: (" . $stmt->errno . ") " . htmlspecialchars($stmt->error);
            }
            $stmt->close();
        }
    }
}
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