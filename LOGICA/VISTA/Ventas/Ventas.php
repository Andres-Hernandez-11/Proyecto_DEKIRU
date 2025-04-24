<?php
session_start();
$current_page = basename($_SERVER['PHP_SELF']);
require_once '../../MODELO/conexion.php'; // Asegúrate de que la ruta sea correcta
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
                '../Clientes/Clientes.php' => ['icon' => '../../../IMAGENES/Clientes.png', 'text' => 'Clientes', 'alt' => 'Icono Clientes'], // Asume Clientes.php
                '../Inventario/InventarioStart.php' => ['icon' => '../../../IMAGENES/Inventario.png', 'text' => 'Inventario', 'alt' => 'Icono Inventario'],
                '../Ventas/Ventas.php' => ['icon' => '../../../IMAGENES/Ventas-Compras.png', 'text' => 'Ventas', 'alt' => 'Icono Ventas'], // Asume Ventas.php
                '../RRHH/RRHH_UI.php' => ['icon' => '../../../IMAGENES/RH.png', 'text' => 'Recursos Humanos', 'alt' => 'Icono Recursos Humanos'], // Asume RRHH.php
                //'Contabilidad.php' => ['icon' => '../../../IMAGENES/Contabilidad.png', 'text' => 'Contabilidad', 'alt' => 'Icono Contabilidad'], // Asume Contabilidad.php
                //'Configuracion.php' => ['icon' => '../../../IMAGENES/Configuracion.png', 'text' => 'Configuración', 'alt' => 'Icono Configuración'] // Asume Configuracion.php
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

                <?php
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

                            <div class="modal-footer">
                                <button type="button" id="btn-cancelar-venta" class="btn btn-secondary">Cancelar</button>
                                <input type="submit" value="Confirmar venta" class="btn btn-primary">
                            </div>
                        </form>
                    </div>
                </div>
                <div id="modal-editar-venta" class="modal">
                    <div class="modal-content">
                        <span class="close">&times;</span>
                        <h2>Editar Venta</h2>
                        <form id="formulario-editar-venta" action="../../MODELO/Ventas/editarventas.php" method="POST">
                            <input type="hidden" id="edit_id_venta" name="id_venta">

                            <label for="edit_id_cliente">ID Cliente:</label>
                            <input type="text" id="edit_id_cliente" name="id_cliente" required><br>

                            <label for="edit_id_vendedor">ID Vendedor:</label>
                            <input type="text" id="edit_id_vendedor" name="id_vendedor" required><br>

                            <label for="edit_fecha">Fecha:</label>
                            <input type="date" id="edit_fecha" name="fecha" required><br>

                            <label for="edit_origen">Origen:</label>
                            <input type="text" id="edit_origen" name="origen" required><br>

                            <label for="edit_destino">Destino:</label>
                            <input type="text" id="edit_destino" name="destino" required><br>

                            <label for="edit_total">Total:</label>
                            <input type="number" id="edit_total" name="total" step="0.01" required><br>

                            <label for="edit_metodo_pago">Método de Pago:</label>
                            <select id="edit_metodo_pago" name="metodo_pago">
                                <option value="Efectivo">Efectivo</option>
                                <option value="Tarjeta">Tarjeta</option>
                            </select><br>

                            <label for="edit_asiento">Asiento:</label>
                            <input type="text" id="edit_asiento" name="asiento" required><br>

                            <label for="edit_hora">Hora:</label>
                            <input type="time" id="edit_hora" name="hora" required><br>

                            <label for="edit_id_bus">ID Bus:</label>
                            <input type="text" id="edit_id_bus" name="id_bus" required><br>

                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary btn-cancelar-editar">Cancelar</button>
                                <input type="submit" value="Guardar Cambios" class="btn btn-primary">
                            </div>
                        </form>
                    </div>
                </div>

                <div class="search-bar">
                    <input type="text" id="id_cliente_filter" placeholder="&#x1F50D; Buscar por ID Cliente..." style="padding-left: 30px;">

                    <label for="origen_filter">Origen:</label>
                    <select id="origen_filter">
                        <option value="">Todos</option>
                        <?php
                        // PHP para obtener orígenes únicos de tu base de datos y llenar el select
                        $origenes = [];
                        if (!empty($conn) && ($conn instanceof mysqli)) {
                            $sql_origenes = "SELECT DISTINCT origen FROM VENTA ORDER BY origen ASC";
                            $result_origenes = $conn->query($sql_origenes);
                            if ($result_origenes && $result_origenes->num_rows > 0) {
                                while ($row_origenes = $result_origenes->fetch_assoc()) {
                                    $origenes[] = $row_origenes['origen'];
                                }
                            }
                        }
                        foreach ($origenes as $origen) {
                            echo "<option value='" . htmlspecialchars($origen) . "'>" . htmlspecialchars($origen) . "</option>";
                        }
                        ?>
                    </select>

                    <label for="destino_filter">Destino:</label>
                    <select id="destino_filter">
                        <option value="">Todos</option>
                        <?php
                        // PHP para obtener destinos únicos de tu base de datos y llenar el select
                        $destinos = [];
                        if (!empty($conn) && ($conn instanceof mysqli)) {
                            $sql_destinos = "SELECT DISTINCT destino FROM VENTA ORDER BY destino ASC";
                            $result_destinos = $conn->query($sql_destinos);
                            if ($result_destinos && $result_destinos->num_rows > 0) {
                                while ($row_destinos = $result_destinos->fetch_assoc()) {
                                    $destinos[] = $row_destinos['destino'];
                                }
                            }
                        }
                        foreach ($destinos as $destino) {
                            echo "<option value='" . htmlspecialchars($destino) . "'>" . htmlspecialchars($destino) . "</option>";
                        }
                        ?>
                    </select>
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

                                <td>
                                    <div class="action-cell">
                                        <button class="action-btn btn-editar" data-id="<?php echo htmlspecialchars($venta['id_venta']); ?>" title="Editar">
                                            <span class="icon">✏️</span>
                                        </button>
                                        <button class="action-btn delete btn-eliminar" data-id="<?php echo htmlspecialchars($venta['id_venta']); ?>" title="Eliminar">
                                            <span class="icon">🗑️</span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>



    <div id="modal-eliminar-venta" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Eliminar Venta</h2>
            <p>¿Estás seguro de que deseas eliminar la venta con ID: <span id="venta-a-eliminar"></span>?</p>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-cancelar-eliminar">Cancelar</button>
                <button type="button" class="btn btn-danger btn-confirmar-eliminar">Eliminar</button>
            </div>
        </div>
    </div>


    <script>
        // Modales
        var modalAgregar = document.getElementById("modal-agregar-viaje");
        var modalEliminar = document.getElementById('modal-eliminar-venta');
        var modalEditar = document.getElementById('modal-editar-venta');

        // Botones
        var btnAbrirModalAgregar = document.getElementById("btn-abrir-modal-agregar");
        var botonesEliminar = document.querySelectorAll('.btn-eliminar');
        var btnCancelarVenta = document.getElementById("btn-cancelar-venta");
        var btnCancelarEliminar = modalEliminar.querySelector('.btn-cancelar-eliminar');
        var btnConfirmarEliminar = modalEliminar.querySelector('.btn-confirmar-eliminar');
        var botonesEditar = document.querySelectorAll('.btn-editar');
        var btnCancelarEditar = modalEditar.querySelector('.btn-cancelar-editar');

        // Spans para cerrar modales
        var spanCerrarAgregar = modalAgregar.querySelector('.close');
        var spanCerrarEliminar = modalEliminar.querySelector('.close');
        var spanCerrarEditar = modalEditar.querySelector('.close');

        // Elemento para mostrar el ID de la venta a eliminar
        var ventaAEliminar = document.getElementById('venta-a-eliminar');

        let idVentaAEliminar;
        let filaAEliminar; // Variable para almacenar la fila a eliminar

        // Abrir modal de agregar
        btnAbrirModalAgregar.onclick = function() {
            modalAgregar.style.display = "block";
        }

        // Cerrar modal de agregar
        spanCerrarAgregar.onclick = function() {
            modalAgregar.style.display = "none";
        }

        btnCancelarVenta.onclick = function() {
            modalAgregar.style.display = "none";
        }

        // Cerrar modal al hacer clic fuera
        window.onclick = function(event) {
            if (event.target == modalAgregar) {
                modalAgregar.style.display = "none";
            }
        }

        // Abrir modal de editar
        botonesEditar.forEach(boton => {
            boton.addEventListener('click', function() {
                let idVenta = this.getAttribute('data-id');
                // Obtener los datos de la fila y llenar el modal
                let fila = this.closest('tr');

                //*** Obtén los datos *DENTRO* del event listener ***
                let idCliente = fila.cells[1].textContent;
                let idVendedor = fila.cells[2].textContent;
                let fecha = fila.cells[3].textContent;
                let hora = fila.cells[4].textContent;
                if (hora.length > 5) {
                    hora = hora.substr(0, 5);  // Recortar la hora a HH:MM si tiene segundos
                }
                let origen = fila.cells[5].textContent.trim();  // *** TRIM() aquí ***
                let destino = fila.cells[6].textContent.trim(); // *** Y aquí ***
                let total = parseFloat(fila.cells[7].textContent.replace('$', '').replace(',', '')); // Elimina el símbolo de moneda y las comas
                document.getElementById('edit_total').value = total;
                let metodoPago = fila.cells[8].textContent;
                let idBus = fila.cells[9].textContent;
                let asiento = fila.cells[10].textContent;

                document.getElementById('edit_id_venta').value = idVenta;
                document.getElementById('edit_id_cliente').value = idCliente;
                document.getElementById('edit_id_vendedor').value = idVendedor;
                document.getElementById('edit_fecha').value = fecha;
                document.getElementById('edit_hora').value = hora;
                document.getElementById('edit_origen').value = origen;
                document.getElementById('edit_destino').value = destino;
                document.getElementById('edit_total').value = total;
                document.getElementById('edit_metodo_pago').value = metodoPago;
                document.getElementById('edit_id_bus').value = idBus;
                document.getElementById('edit_asiento').value = asiento;

                modalEditar.style.display = 'block';
            });
        });

        // Cerrar modal de editar
        spanCerrarEditar.onclick = function() {
            modalEditar.style.display = 'none';
        }

        btnCancelarEditar.onclick = function() {
            modalEditar.style.display = 'none';
        }

        window.onclick = function(event) {
            if (event.target == modalEditar) {
                modalEditar.style.display = 'none';
            }
        }

        window.onload = function() {
            const feedbackMessage = document.querySelector('.feedback-message');
            if (feedbackMessage) {
                setTimeout(() => {
                    feedbackMessage.classList.add('fade-out');
                    setTimeout(() => {
                        feedbackMessage.remove();
                    }, 500);
                }, 3000); // 3000 milisegundos = 3 segundos
            }
        };

        // Abrir modal de eliminar y confirmar eliminación
        botonesEliminar.forEach(function(boton) {
            boton.addEventListener('click', function() {
                idVentaAEliminar = this.getAttribute('data-id');
                ventaAEliminar.textContent = idVentaAEliminar;
                modalEliminar.style.display = 'block';
                filaAEliminar = this.closest('tr'); // Guardar la referencia a la fila
            });
        });

        // Confirmar eliminación
        btnConfirmarEliminar.onclick = function() {
            fetch('../../MODELO/Ventas/eliminarventa.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: 'id_venta=' + encodeURIComponent(idVentaAEliminar)
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Eliminar la fila de la tabla
                        if (filaAEliminar) {
                            filaAEliminar.remove();
                        }

                        // Actualizar el total en la página
                        let totalVentasElement = document.querySelector('.total-ventas');  // Selector específico
                        if (totalVentasElement) {
                            totalVentasElement.textContent = data.newTotal.toFixed(2);  // Formatear a 2 decimales
                        }
                        mostrarMensaje(data.message, 'warning');
                    } else {
                        mostrarMensaje(data.message, 'error');
                    }
                    modalEliminar.style.display = 'none';
                })
                .catch(error => {
                    console.error('Error:', error);
                    mostrarMensaje('Error al procesar la solicitud.', 'error');
                    modalEliminar.style.display = 'none';
                });
        };

        function mostrarMensaje(mensaje, tipo) {
            let mensajeDiv = document.createElement('div');
            mensajeDiv.textContent = mensaje;
            mensajeDiv.classList.add('feedback-message', tipo); // 'success', 'error', etc.

            let mainContainer = document.querySelector('.main-container'); // O el contenedor donde quieras mostrar el mensaje
            mainContainer.insertBefore(mensajeDiv, mainContainer.firstChild);

            // Opcional: Desaparecer el mensaje después de un tiempo
            setTimeout(() => {
                mensajeDiv.remove();
            }, 5000); // 5 segundos
        }

        // Cancelar eliminación
        btnCancelarEliminar.onclick = function() {
            modalEliminar.style.display = 'none';
        }

        spanCerrarEliminar.onclick = function() {
            modalEliminar.style.display = 'none';
        }


        const filterInput = document.getElementById('id_cliente_filter');
        const origenFilter = document.getElementById('origen_filter');
        const destinoFilter = document.getElementById('destino_filter');
        const tableRows = document.querySelectorAll('.ventas-table-container tbody tr');

        function applyFilters() {
            const filterValue = filterInput.value.trim();
            const origenValue = origenFilter.value;
            const destinoValue = destinoFilter.value;

            tableRows.forEach(row => {
                const idClienteCell = row.cells[1];
                const origenCell = row.cells[5];
                const destinoCell = row.cells[6];

                const idCliente = idClienteCell.textContent.trim();
                const origen = origenCell.textContent.trim();
                const destino = destinoCell.textContent.trim();

                let shouldShow = true;

                if (filterValue && !idCliente.startsWith(filterValue)) {
                    shouldShow = false;
                }

                if (origenValue && origenValue !== 'Todos' && origen !== origenValue) {
                    shouldShow = false;
                }

                if (destinoValue && destinoValue !== 'Todos' && destino !== destinoValue) {
                    shouldShow = false;
                }

                row.style.display = shouldShow ? '' : 'none';
            });
        }

        filterInput.addEventListener('input', applyFilters);
        origenFilter.addEventListener('change', applyFilters);
        destinoFilter.addEventListener('change', applyFilters);

    </script>
</body>
</html>