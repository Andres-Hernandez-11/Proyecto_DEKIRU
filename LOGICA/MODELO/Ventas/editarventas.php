<?php
session_start();
require_once '../../MODELO/conexion.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Recoger los datos del formulario
    $id_venta = $_POST['id_venta'];
    $id_cliente_raw = trim($_POST['id_cliente'] ?? '');
    $id_vendedor_raw = trim($_POST['id_vendedor'] ?? '');
    $fecha = trim($_POST['fecha'] ?? '');
    $origen = trim($_POST['origen'] ?? '');
    $destino = trim($_POST['destino'] ?? '');
    $total_raw = trim($_POST['total'] ?? '');
    $metodo_pago = trim($_POST['metodo_pago'] ?? '');
    $asiento = trim($_POST['asiento'] ?? '');
    $hora = substr($_POST['hora'], 0, 5);
    $id_bus_raw = trim($_POST['id_bus'] ?? '');

    // Validaciones del lado del servidor
    if (empty($id_venta)) {
        $mensaje = "El ID de Venta es obligatorio.";
    } elseif (empty($id_cliente_raw)) {
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
    } elseif (!ctype_digit($id_cliente_raw) || strlen($id_cliente_raw) > 10) {
        $mensaje = "ID Cliente inválido.";
    } elseif (!ctype_digit($id_vendedor_raw) || strlen($id_vendedor_raw) > 10) {
        $mensaje = "ID Vendedor inválido.";
    } elseif (!is_numeric($total_raw) || (strpos($total_raw, '.') !== false && strlen(substr($total_raw, 0, strpos($total_raw, '.'))) > 6) || (strpos($total_raw, '.') === false && strlen($total_raw) > 6) || (float)$total_raw < 0) {
        $mensaje = "Total inválido.";
    } elseif (!ctype_digit($id_bus_raw) || strlen($id_bus_raw) > 4) {
        $mensaje = "ID Bus inválido.";
    } else {
        // Convertir datos
        $id_cliente = (int)$id_cliente_raw;
        $id_vendedor = (int)$id_vendedor_raw;
        $total = (float)$total_raw;
        $id_bus = (int)$id_bus_raw;

        // Actualizar la venta en la base de datos
        $sql = "UPDATE VENTA SET 
                id_cliente = ?, id_vendedor = ?, fecha = ?, origen = ?, destino = ?, total = ?, metodo_pago = ?, asiento = ?, hora = ?, id_bus = ?
                WHERE id_venta = ?";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iisssssisii", $id_cliente, $id_vendedor, $fecha, $origen, $destino, $total, $metodo_pago, $asiento, $hora, $id_bus, $id_venta);

        if ($stmt->execute()) {
            $_SESSION['mensaje'] = "Venta actualizada con éxito.";
            $total_ventas = 0;// --- Obtener el nuevo total de ventas ---
            $sql_total = "SELECT SUM(total) as total_acumulado FROM VENTA";
            $result_total = $conn->query($sql_total);
            if ($result_total && $result_total->num_rows > 0) {
                $row_total = $result_total->fetch_assoc();
                $total_ventas = $row_total['total_acumulado'] ?? 0;
            }

            $_SESSION['newTotal'] = $total_ventas;  // Guardar el nuevo total en la sesión
            $_SESSION['tipo_mensaje'] = "success";
        } else {
            $_SESSION['mensaje'] = "Error al actualizar la venta: " . $stmt->error;
            $_SESSION['tipo_mensaje'] = "error";
        }
        $stmt->close();
        $conn->close();
        header("Location: ../../VISTA/Ventas/Ventas.php");
        exit;
    }
    $_SESSION['mensaje'] = $mensaje; // Si hay errores, enviar el mensaje
    $_SESSION['tipo_mensaje'] = "error";
    header("Location: ../../VISTA/Ventas/Ventas.php");
    exit;
} else {
    // Si no es una solicitud POST, redirigir o mostrar un error
    $_SESSION['mensaje'] = "Acceso no permitido.";
    $_SESSION['tipo_mensaje'] = "error";
    header("Location: ../../VISTA/Ventas/Ventas.php");
    exit;
}
?>