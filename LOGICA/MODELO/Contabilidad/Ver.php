<?php
// contabilidad_resumen_logica.php (Adaptado a MOVIMIENTO_FINANCIERO)
// Asume que session_start() se llamó en el archivo principal (Start)

// Incluir conexión
// ¡ASEGÚRATE DE QUE LA RUTA SEA CORRECTA desde el archivo que incluye este (Start)!
require_once '../../MODELO/conexion.php';

// --- Inicializar Variables ---
$fecha_inicio = date('Y-m-01'); // Primer día del mes actual por defecto
$fecha_fin = date('Y-m-t');    // Último día del mes actual por defecto
$periodo_rapido = $_GET['periodo'] ?? null;

$total_ingresos = 0.00;
$total_egresos = 0.00;
$saldo_neto = 0.00;
$movimientos_recientes = [];
$egresos_por_categoria = []; // Para el gráfico
$ingresos_vs_egresos_data = []; // Para el gráfico
$error_db = null;

// --- Determinar Rango de Fechas ---
// (Lógica para determinar $fecha_inicio y $fecha_fin basada en $_GET se mantiene igual)
switch ($periodo_rapido) {
    case 'hoy': $fecha_inicio = date('Y-m-d'); $fecha_fin = date('Y-m-d'); break;
    case '7dias': $fecha_inicio = date('Y-m-d', strtotime('-6 days')); $fecha_fin = date('Y-m-d'); break;
    case 'mes': $fecha_inicio = date('Y-m-01'); $fecha_fin = date('Y-m-t'); break;
    case 'ano': $fecha_inicio = date('Y-01-01'); $fecha_fin = date('Y-12-31'); break;
    default:
        $fecha_inicio_get = $_GET['fecha_inicio'] ?? $fecha_inicio;
        $fecha_fin_get = $_GET['fecha_fin'] ?? $fecha_fin;
        if (preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $fecha_inicio_get)) { $fecha_inicio = $fecha_inicio_get; }
        if (preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $fecha_fin_get)) { $fecha_fin = $fecha_fin_get; }
        break;
}
$fecha_inicio_obj = date_create($fecha_inicio);
$fecha_fin_obj = date_create($fecha_fin);
if (!$fecha_inicio_obj || !$fecha_fin_obj || $fecha_inicio_obj > $fecha_fin_obj) {
    $error_db = "Rango de fechas inválido.";
    $fecha_inicio = date('Y-m-01'); $fecha_fin = date('Y-m-t');
} else {
    $fecha_inicio = $fecha_inicio_obj->format('Y-m-d');
    $fecha_fin = $fecha_fin_obj->format('Y-m-d');
}

// --- Consultas a la Base de Datos (Usando MOVIMIENTO_FINANCIERO) ---
if (!$error_db && isset($conn) && $conn instanceof mysqli) {
    try {
        // Calcular Total Ingresos
        $sql_ingresos = "SELECT SUM(monto) as total FROM MOVIMIENTO_FINANCIERO WHERE tipo = 'Ingreso' AND fecha BETWEEN ? AND ?";
        $stmt_ingresos = $conn->prepare($sql_ingresos);
        if (!$stmt_ingresos) throw new Exception("Err Ingresos Prep: " . $conn->error);
        $stmt_ingresos->bind_param("ss", $fecha_inicio, $fecha_fin);
        if (!$stmt_ingresos->execute()) throw new Exception("Err Ingresos Exec: " . $stmt_ingresos->error);
        $total_ingresos_db = null;
        $stmt_ingresos->bind_result($total_ingresos_db);
        $stmt_ingresos->fetch();
        $total_ingresos = $total_ingresos_db ?? 0.00;
        $stmt_ingresos->close();

        // Calcular Total Egresos
        $sql_egresos = "SELECT SUM(monto) as total FROM MOVIMIENTO_FINANCIERO WHERE tipo = 'Egreso' AND fecha BETWEEN ? AND ?";
        $stmt_egresos = $conn->prepare($sql_egresos);
        if (!$stmt_egresos) throw new Exception("Err Egresos Prep: " . $conn->error);
        $stmt_egresos->bind_param("ss", $fecha_inicio, $fecha_fin);
        if (!$stmt_egresos->execute()) throw new Exception("Err Egresos Exec: " . $stmt_egresos->error);
        $total_egresos_db = null;
        $stmt_egresos->bind_result($total_egresos_db);
        $stmt_egresos->fetch();
        $total_egresos = $total_egresos_db ?? 0.00;
        $stmt_egresos->close();

        // Calcular Saldo Neto
        $saldo_neto = $total_ingresos - $total_egresos;

        // Obtener Movimientos Recientes (LIMIT 5)
        $sql_recientes = "SELECT fecha, hora, tipo, categoria, descripcion, monto
                          FROM MOVIMIENTO_FINANCIERO
                          ORDER BY fecha DESC, hora DESC, id_movimiento_fin DESC LIMIT 5";
        $result_recientes = $conn->query($sql_recientes);
        if ($result_recientes) {
            $movimientos_recientes = $result_recientes->fetch_all(MYSQLI_ASSOC);
            $result_recientes->free();
        } else {
            throw new Exception("Err Recientes: " . $conn->error);
        }

        // --- Datos para Gráficos (Adaptados a MOVIMIENTO_FINANCIERO) ---
        // Egresos por categoría
        $sql_graf_cat = "SELECT categoria, SUM(monto) as total_monto
                         FROM MOVIMIENTO_FINANCIERO
                         WHERE tipo = 'Egreso' AND fecha BETWEEN ? AND ?
                         GROUP BY categoria ORDER BY total_monto DESC";
        $stmt_graf_cat = $conn->prepare($sql_graf_cat);
        if(!$stmt_graf_cat) throw new Exception("Err Graf Cat Prep: " . $conn->error);
        $stmt_graf_cat->bind_param("ss", $fecha_inicio, $fecha_fin);
        if(!$stmt_graf_cat->execute()) throw new Exception("Err Graf Cat Exec: " . $stmt_graf_cat->error);
        $result_graf_cat = $stmt_graf_cat->get_result(); // Usar get_result aquí está bien si funciona
        if($result_graf_cat) {
            $egresos_por_categoria = $result_graf_cat->fetch_all(MYSQLI_ASSOC);
            $result_graf_cat->free();
        }
        $stmt_graf_cat->close();

        // Ingresos vs Egresos por día (Ejemplo simplificado)
        $sql_graf_trend = "SELECT DATE(fecha) as dia,
                           SUM(CASE WHEN tipo = 'Ingreso' THEN monto ELSE 0 END) as ingresos,
                           SUM(CASE WHEN tipo = 'Egreso' THEN monto ELSE 0 END) as egresos
                           FROM MOVIMIENTO_FINANCIERO
                           WHERE fecha BETWEEN ? AND ?
                           GROUP BY dia ORDER BY dia ASC";
        $stmt_graf_trend = $conn->prepare($sql_graf_trend);
         if(!$stmt_graf_trend) throw new Exception("Err Graf Trend Prep: " . $conn->error);
        $stmt_graf_trend->bind_param("ss", $fecha_inicio, $fecha_fin);
        if(!$stmt_graf_trend->execute()) throw new Exception("Err Graf Trend Exec: " . $stmt_graf_trend->error);
        $result_graf_trend = $stmt_graf_trend->get_result();
        if($result_graf_trend) {
            $ingresos_vs_egresos_data = $result_graf_trend->fetch_all(MYSQLI_ASSOC);
             $result_graf_trend->free();
        }
        $stmt_graf_trend->close();


    } catch (Exception $e) {
        $error_db = "Error al consultar datos financieros: " . $e->getMessage();
        // Limpiar variables en caso de error
        $total_ingresos = 0.00; $total_egresos = 0.00; $saldo_neto = 0.00;
        $movimientos_recientes = []; $egresos_por_categoria = []; $ingresos_vs_egresos_data = [];
    } finally {
        if (isset($conn) && $conn instanceof mysqli) {
            $conn->close();
        }
    }
} elseif (!$conn && !$error_db) {
     $error_db = "Error crítico: No se pudo establecer la conexión a la base de datos.";
}

// Variables listas para la vista:
// $fecha_inicio, $fecha_fin, $total_ingresos, $total_egresos, $saldo_neto,
// $movimientos_recientes, $egresos_por_categoria, $ingresos_vs_egresos_data, $error_db

?>
