<?php
global $conn;
session_start();
require_once '../conexion.php';

$response = ['success' => false, 'message' => 'Error al eliminar la venta.', 'newTotal' => 0];

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id_venta'])) {
    $id_venta = trim($_POST['id_venta']);

    if (!empty($id_venta) && is_numeric($id_venta)) {
        $sql = "DELETE FROM VENTA WHERE id_venta = ?";
        $stmt = $conn->prepare($sql);

        if ($stmt) {
            $stmt->bind_param('i', $id_venta);
            if ($stmt->execute()) {
                if ($stmt->affected_rows > 0) {
                    $response['success'] = true;
                    $response['message'] = 'Venta eliminada exitosamente.';

                    // Recalcular el total de ventas después de la eliminación
                    $sql_total = "SELECT SUM(total) AS total_acumulado FROM VENTA";  // Alias para claridad
                    $result_total = $conn->query($sql_total);
                    if ($result_total && $result_total->num_rows > 0) {
                        $row_total = $result_total->fetch_assoc();
                        $response['newTotal'] = (float)$row_total['total_acumulado'] ?? 0.00;  // Asegurar float y valor por defecto
                    }

                } else {
                    $response['message'] = 'No se encontró la venta con el ID especificado.';
                }
            } else {
                $response['message'] = 'Error al ejecutar la consulta: ' . $stmt->error;
            }
            $stmt->close();
        } else {
            $response['message'] = 'Error al preparar la consulta: ' . $conn->error;
        }
    } else {
        $response['message'] = 'ID de venta no válido.';
    }
}

if (!empty($conn)) {
    $conn->close();
}

header('Content-Type: application/json');
echo json_encode($response);
exit();
?>