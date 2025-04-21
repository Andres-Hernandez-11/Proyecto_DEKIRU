<?php

session_start();


$current_page = basename($_SERVER['PHP_SELF']);
require_once '../../MODELO/conexion.php'; // Asegúrate de que la ruta sea correcta

$total_ventas = 0;// --- Inicializar la variable para el total de ventas ---
// --- Obtener el total de ventas ---
if (!empty($conn) && ($conn instanceof mysqli)) {
    $sql_total = "SELECT SUM(total) as total_acumulado FROM VENTA";
    $result_total = $conn->query($sql_total);


    if ($result_total && $result_total->num_rows > 0) {
        $row_total = $result_total->fetch_assoc();
        $total_ventas = $row_total['total_acumulado'] ?? 0; // Usar ?? 0 para evitar null
    } else {
        // Manejar el error al obtener el total (opcional)
        // Puedes loguear el error o mostrar un mensaje
        echo "";
    }
} else {
    // Manejar el error de conexión (opcional)
    echo "";
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Ventas - Rápidos del Altiplano</title>

    <link rel="stylesheet" href="..\Ventas\EstilosVentas.css"/>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
</head>

<body>

<aside class="sidebar">
    <div class="sidebar-header">
        <div class="sidebar-logo-container">
            <img src="..\..\..\IMAGENES\LogoRapidosDelAltiplano.jpg" alt="Logo Rápidos del Altiplano" class="sidebar-logo" onerror="this.onerror=null; this.src='https://placehold.co/150x50/cccccc/333333?text=Logo'; this.alt='Logo Placeholder';">
        </div>
        <h2 class="sidebar-title">Menú Principal</h2>
    </div>
    <nav class="sidebar-nav">
        <?php
        // Rutas ajustadas asumiendo que VISTA está un nivel dentro del proyecto
        $nav_links = [
            '../Dashboard/Dashboard.php' => ['icon' => '../../../IMAGENES/Dashboard.png', 'text' => 'Dashboard (Inicio)', 'alt' => 'Icono Dashboard'],
            'Clientes.php' => ['icon' => '../../../IMAGENES/Clientes.png', 'text' => 'Clientes', 'alt' => 'Icono Clientes'], // Asume Clientes.php
            '../Inventario/InventarioStart.php' => ['icon' => '../../../IMAGENES/Inventario.png', 'text' => 'Inventario', 'alt' => 'Icono Inventario'],
            'Ventas.php' => ['icon' => '../../../IMAGENES/Ventas-Compras.png', 'text' => 'Ventas-Compras', 'alt' => 'Icono Ventas'], // Asume Ventas.php
            'RRHH.php' => ['icon' => '../../../IMAGENES/RH.png', 'text' => 'Recursos Humanos', 'alt' => 'Icono Recursos Humanos'], // Asume RRHH.php
            'Contabilidad.php' => ['icon' => '../../../IMAGENES/Contabilidad.png', 'text' => 'Contabilidad', 'alt' => 'Icono Contabilidad'], // Asume Contabilidad.php
            'Configuracion.php' => ['icon' => '../../../IMAGENES/Configuracion.png', 'text' => 'Configuración', 'alt' => 'Icono Configuración'] // Asume Configuracion.php
        ];
        ?>
        <?php foreach ($nav_links as $file => $link): ?>
            <a href="<?php echo $file; // Asume que los archivos PHP están en VISTA ?>"
               class="sidebar-link<?php echo ($current_page == $file) ? ' active' : ''; ?>">
                <img src="<?php echo htmlspecialchars($link['icon']); ?>"
                     alt="<?php echo htmlspecialchars($link['alt']); ?>" class="sidebar-icon"
                     onerror="this.style.display='none'; this.parentElement.insertAdjacentText('afterbegin', '[i] ');">
                <?php echo htmlspecialchars($link['text']); ?>
            </a>
        <?php endforeach; ?>
    </nav>
</aside>




<div class="main-content-area">
    <header class="header">
        <div class="header-container">
            <div class="header-left">
                <h1 class="header-title">Ventas</h1>
            </div>
            <div class="header-right">
                         <span class="user-info">
                             Bienvenido, <?php echo isset($_SESSION['nombre_usuario']) ? htmlspecialchars($_SESSION['nombre_usuario']) : 'Invitado'; ?>
                         </span>
                <a href="../../MODELO/CerrarSesion.php" class="logout-button btn ">
                    <span class="icon"></span> Cerrar Sesión
                </a>
            </div>
        </div>
    </header>

    <main class="main-content">
        <div class="main-container">

            <?php if (isset($_SESSION['mensaje'])): ?>
                <div class="feedback-message <?php echo htmlspecialchars($_SESSION['tipo_mensaje'] ?? 'info'); ?>">
                    <?php echo htmlspecialchars($_SESSION['mensaje']); ?>
                    <?php
                    unset($_SESSION['mensaje']);
                    unset($_SESSION['tipo_mensaje']);
                    ?>
                </div>
            <?php endif; ?>

            <div class="ventas-header">
                <h1 class="ventas-title">
                    Total: $
                    <span class="total-ventas">
             <?php echo number_format($total_ventas, 2); ?>
         </span>
                </h1>
                <button type="button" id="btn-abrir-modal-agregar" class="btn btn-primary">
                    <span class="icon">+</span> Nueva venta
                </button>
            </div>

            <div id="modal-agregar-viaje" class="modal">
                <div class="modal-content">
                    <span class="close">&times;</span>
                    <h2>Tiquete</h2>
                    <form id="formulario-agregar-viaje" action="../../MODELO/Ventas/agregar.php" method="POST">


                        <label for="id_venta">ID Venta:</label>
                        <input type="text" id="id_venta" name="id_venta" required><br>

                        <label for="id_cliente">ID Cliente:</label>
                        <input type="text" id="id_cliente" name="id_cliente" required><br>

                        <label for="id_vendedor">ID Vendedor:</label>
                        <input type="text" id="id_vendedor" name="id_vendedor" required><br>

                        <label for="fecha">Fecha:</label>
                        <input type="date" id="fecha" name="fecha" required><br>

                        <label for="origen">Origen:</label>
                        <input type="text" id="origen" name="origen" required><br>

                        <label for="destino">Destino:</label>
                        <input type="text" id="destino" name="destino" required><br>

                        <label for="total">Total:</label>
                        <input type="number" id="total" name="total" step="0.01" required><br>

                        <label for="metodo_pago">Método de Pago:</label>
                        <select id="metodo_pago" name="metodo_pago">
                            <option value="Efectivo">Efectivo</option>
                            <option value="Tarjeta">Tarjeta</option>
                        </select><br>

                        <label for="asiento">Asiento:</label>
                        <input type="text" id="asiento" name="asiento" required><br>

                        <label for="hora">Hora:</label>
                        <input type="time" id="hora" name="hora" required><br>

                        <label for="id_bus">ID Bus:</label>
                        <input type="text" id="id_bus" name="id_bus" required><br>

                        <input type="submit" value="Confirmar venta" class="btn btn-primary">
                    </form>
                </div>
            </div>

            <div class="ventas-table-container">
                <table>
                    <thead>
                    <tr>
                        <th>ID Venta</th>
                        <th>ID Cliente</th>
                        <th>ID Vendedor</th>
                        <th>Fecha</th>
                        <th>Hora</th>
                        <th>Origen</th>
                        <th>Destino</th>
                        <th>Total</th>
                        <th>Método de Pago</th>
                        <th>ID Bus</th>
                        <th>Asiento</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php

                    require_once '../../MODELO/conexion.php';
                    $current_page = basename($_SERVER['PHP_SELF']);
                    // --- Obtener datos de ventas ---
                    $ventas = []; // Inicializar $ventas como un array vacío por defecto

                    if (!empty($conn) && ($conn instanceof mysqli)) {
                        $sql = "SELECT id_venta, id_cliente, id_vendedor, fecha, hora, origen, destino, total, metodo_pago, id_bus, asiento FROM VENTA";
                        $result = $conn->query($sql);

                        if ($result) {
                            if ($result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    $ventas[] = $row;
                                }
                            }
                            $result->free_result(); // Liberar los resultados
                        } else {
                            // Manejar el error de la consulta (log, mensaje, etc.)
                            $_SESSION['mensaje'] = "Error al obtener la lista de ventas: " . $conn->error;
                            $_SESSION['tipo_mensaje'] = "error";
                        }
                        $conn->close();
                    } else {
                        $_SESSION['mensaje'] = "Error de conexión a la base de datos.";
                        $_SESSION['tipo_mensaje'] = "error";
                    }
                    ?>

                    <?php foreach ($ventas as $venta): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($venta['id_venta']); ?></td>
                            <td><?php echo htmlspecialchars($venta['id_cliente']); ?></td>
                            <td><?php echo htmlspecialchars($venta['id_vendedor']); ?></td>
                            <td><?php echo htmlspecialchars($venta['fecha']); ?></td>
                            <td><?php echo htmlspecialchars($venta['hora']); ?></td>
                            <td><?php echo htmlspecialchars($venta['origen']); ?></td>
                            <td><?php echo htmlspecialchars($venta['destino']); ?></td>
                            <td><?php echo htmlspecialchars(number_format($venta['total'], 2)); ?></td>
                            <td><?php echo htmlspecialchars($venta['metodo_pago']); ?></td>
                            <td><?php echo htmlspecialchars($venta['id_bus']); ?></td>
                            <td><?php echo htmlspecialchars($venta['asiento']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

        </div>


    </main>
</div>

<script>
    // Obtener el modal
    var modal = document.getElementById("modal-agregar-viaje");
    var btnAbrirModal = document.getElementById("btn-abrir-modal-agregar");
    var spanCerrarModal = document.getElementsByClassName("close")[0];

    btnAbrirModal.onclick = function() {
        modal.style.display = "block";
    }

    spanCerrarModal.onclick = function() {
        modal.style.display = "none";
    }

    window.onclick = function(event) {
        if (event.target == modal) {
            modal.style.display = "none";
        }
    }
</script>
</body>
</html>