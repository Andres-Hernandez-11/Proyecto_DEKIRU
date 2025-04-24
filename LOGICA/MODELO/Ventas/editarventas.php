<?php
session_start();
require_once '../../MODELO/conexion.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Recoger los datos del formulario
    $id_venta = $_POST['id_venta'];
    $id_cliente = $_POST['id_cliente'];
    $id_vendedor = $_POST['id_vendedor'];
    $fecha = $_POST['fecha'];
    $origen = $_POST['origen'];
    $destino = $_POST['destino'];
    $total = $_POST['total'];
    $metodo_pago = $_POST['metodo_pago'];
    $asiento = $_POST['asiento'];
    $hora = substr($_POST['hora'], 0, 5); // Recortar a HH:MM
    $id_bus = $_POST['id_bus'];

    // Log de los datos recibidos
    error_log("Datos recibidos: " . print_r($_POST, true));  // Loguea todos los datos POST


    // Validar los datos (opcional, pero recomendado)
    if (empty($id_venta) || empty($id_cliente) || empty($id_vendedor) || empty($fecha) || empty($origen) || empty($destino) || empty($total) || empty($metodo_pago) || empty($asiento) || empty($hora) || empty($id_bus)) {
        $_SESSION['mensaje'] = "Todos los campos son obligatorios.";
        $_SESSION['tipo_mensaje'] = "error";
        header("Location: ../../VISTA/Ventas/Ventas.php");
        exit;
    }

    // Actualizar la venta en la base de datos
    $sql = "UPDATE VENTA SET 
            id_cliente = ?,
            id_vendedor = ?,
            fecha = ?,
            origen = ?,
            destino = ?,
            total = ?,
            metodo_pago = ?,
            asiento = ?,
            hora = ?,
            id_bus = ?
            WHERE id_venta = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iisssssisii", $id_cliente, $id_vendedor, $fecha, $origen, $destino, $total, $metodo_pago, $asiento, $hora, $id_bus, $id_venta);

    if ($stmt->execute()) {
        $_SESSION['mensaje'] = "Venta actualizada con éxito.";

        // --- Obtener el nuevo total de ventas ---
        $total_ventas = 0;
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
} else {
    // Si no es una solicitud POST, redirigir o mostrar un error
    $_SESSION['mensaje'] = "Acceso no permitido.";
    $_SESSION['tipo_mensaje'] = "error";
    header("Location: ../../VISTA/Ventas/Ventas.php");
    exit;
}
?>