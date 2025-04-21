<?php
session_start();

$current_page = basename($_SERVER['PHP_SELF']);

$servername = "localhost";
$username = "root";
$password = "";
$database = "dekirudb";

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

$errores = [];
$mensaje_exito = '';
$clientes = [];
$cliente_seleccionado = null;
$id_cliente_seleccionado = null;

// Obtener todos los clientes para el selector
$sql_listar = "SELECT id_cliente, nombre, apellido FROM cliente ORDER BY nombre ASC";
$result_listar = $conn->query($sql_listar);

if ($result_listar->num_rows > 0) {
    while ($row = $result_listar->fetch_assoc()) {
        $clientes[] = $row;
    }
}

// Obtener los datos del cliente seleccionado mediante AJAX
if (isset($_GET['id_editar_modal']) && is_numeric($_GET['id_editar_modal'])) {
    $id_cliente_modal = $_GET['id_editar_modal'];
    $sql_cliente_modal = "SELECT id_cliente, nombre, apellido, documento, telefono, email FROM cliente WHERE id_cliente = ?";
    $stmt_cliente_modal = $conn->prepare($sql_cliente_modal);
    $stmt_cliente_modal->bind_param("i", $id_cliente_modal);
    $stmt_cliente_modal->execute();
    $result_cliente_modal = $stmt_cliente_modal->get_result();

    if ($result_cliente_modal->num_rows > 0) {
        echo json_encode($result_cliente_modal->fetch_assoc());
    } else {
        echo json_encode(['error' => 'Cliente no encontrado.']);
    }
    exit; // Important to stop further PHP execution for AJAX request
}

// Procesamiento del formulario de edición desde el modal
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id_cliente_modal_editar'])) {
    $id_editar = filter_input(INPUT_POST, 'id_cliente_modal_editar', FILTER_SANITIZE_NUMBER_INT);
    if ($id_editar) {
        // Validación
        if (empty($_POST["nombre"])) {
            $errores[] = "El nombre es obligatorio.";
        }
        if (empty($_POST["apellido"])) {
            $errores[] = "El apellido es obligatorio.";
        }
        if (empty($_POST["documento"])) {
            $errores[] = "El documento es obligatorio.";
        }

        // Si no hay errores, actualizar en la base de datos
        if (empty($errores)) {
            $nombre = mysqli_real_escape_string($conn, $_POST["nombre"]);
            $apellido = mysqli_real_escape_string($conn, $_POST["apellido"]);
            $documento = mysqli_real_escape_string($conn, $_POST["documento"]);
            $telefono = mysqli_real_escape_string($conn, $_POST["telefono"]);
            $email = mysqli_real_escape_string($conn, $_POST["email"]);

            $sql_update = "UPDATE cliente SET nombre = ?, apellido = ?, documento = ?, telefono = ?, email = ? WHERE id_cliente = ?";
            $stmt_update = $conn->prepare($sql_update);
            $stmt_update->bind_param("sssssi", $nombre, $apellido, $documento, $telefono, $email, $id_editar);

            if ($stmt_update->execute()) {
                $mensaje_exito = "Cliente actualizado con éxito.";
            } else {
                $errores[] = "Error al actualizar el cliente: " . $conn->error;
            }
        }
    }
}

?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Cliente - Rápidos del Altiplano</title>
    <link rel="stylesheet" href="../Inventario/EstilosInventario.css" />
    <style>
        .form-group {
            margin-bottom: 15px;
        }
        .selector-cliente {
            margin-bottom: 20px;
        }
        .selector-cliente label {
            display: block;
            margin-bottom: 5px;
        }
        .selector-cliente select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }
        .modal {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.3);
            width: 80%;
            max-width: 600px;
        }
        .modal h2 {
            margin-top: 0;
        }
        .modal .form-group label {
            display: block;
            margin-bottom: 5px;
        }
        .modal .form-group input {
            width: 100%;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
            margin-bottom: 10px;
        }
        .modal .modal-actions {
            text-align: right;
            margin-top: 15px;
        }
        .modal .modal-actions button {
            padding: 8px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-left: 10px;
        }
        .modal .modal-actions .guardar {
            background-color: #007bff;
            color: white;
        }
        .modal .modal-actions .cancelar {
            background-color: #6c757d;
            color: white;
        }
    </style>
</head>

<body>
<div class="dashboard-container">
    <aside class="sidebar">
        <div class="sidebar-header">
            <div class="sidebar-logo-container">
                <img src="../../../IMAGENES/LogoRapidosDelAltiplano.jpg" alt="Logo Rápidos del Altiplano"
                     class="sidebar-logo"
                     onerror="this.onerror=null; this.src='[https://placehold.co/150x50/cccccc/333333?text=Logo](https://placehold.co/150x50/cccccc/333333?text=Logo)'; this.alt='Logo Placeholder';">
            </div>
            <h2 class="sidebar-title">Menú Principal</h2>
        </div>
        <nav class="sidebar-nav">
            <?php
            $nav_links = [
                '../Dashboard/Dashboard.php' => ['icon' => '../../../IMAGENES/Dashboard.png', 'text' => 'Dashboard', 'alt' => 'Icono Dashboard'],
                'Clientes.php' => ['icon' => '../../../IMAGENES/Clientes.png', 'text' => 'Clientes', 'alt' => 'Icono Clientes'],
                '../Inventario/InventarioStart.php' => ['icon' => '../../../IMAGENES/Inventario.png', 'text' => 'Inventario', 'alt' => 'Icono Inventario'],
                'Ventas.php' => ['icon' => '../../../IMAGENES/Ventas-Compras.png', 'text' => 'Ventas-Compras', 'alt' => 'Icono Ventas'],
                'RRHH.php' => ['icon' => '../../../IMAGENES/RH.png', 'text' => 'Recursos Humanos', 'alt' => 'Icono Recursos Humanos'],
                'Contabilidad.php' => ['icon' => '../../../IMAGENES/Contabilidad.png', 'text' => 'Contabilidad', 'alt' => 'Icono Contabilidad'],
                'Configuracion.php' => ['icon' => '../../../IMAGENES/Configuracion.png', 'text' => 'Configuración', 'alt' => 'Icono Configuración']
            ];
            ?>
            <?php foreach ($nav_links as $file => $link): ?>
                <a href="<?php echo $file; ?>"
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
                    <h1 class="header-title">Gestión de Clientes</h1>
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

                <div class="inventory-header">
                    <h1 class="inventory-title">Editar Cliente</h1>
                </div>
                <div id="mensaje-confirmacion" style="display:none; margin-top: 15px; padding: 10px; background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; border-radius: 4px;">
                </div>

                <div class="selector-cliente">
                    <label for="cliente_id">Seleccionar Cliente a Editar:</label>
                    <div class="selector-cliente">
                        <select id="cliente_id">
                            <option value="">-- Seleccionar Cliente --</option>
                            <?php foreach ($clientes as $cliente): ?>
                                <option value="<?php echo htmlspecialchars($cliente['id_cliente']); ?>">
                                    <?php echo htmlspecialchars($cliente['nombre']) . ' ' . htmlspecialchars($cliente['apellido']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <a href="Clientes.php" class="btn btn-secondary">Cancelar y volver</a>
                </div>


                <div id="editarModal" class="modal-overlay">
                    <div class="modal">
                        <h2>Editar Cliente</h2>
                        <form id="formEditarCliente" method="post" action="">
                            <input type="hidden" name="id_cliente_modal_editar" id="modal_id_cliente">
                            <div class="form-group">
                                <label for="modal_nombre">Nombre:</label>
                                <input type="text" id="modal_nombre" name="nombre" required>
                            </div>
                            <div class="form-group">
                                <label for="modal_apellido">Apellido:</label>
                                <input type="text" id="modal_apellido" name="apellido" required>
                            </div>
                            <div class="form-group">
                                <label for="modal_documento">Documento:</label>
                                <input type="text" id="modal_documento" name="documento" required>
                            </div>
                            <div class="form-group">
                                <label for="modal_telefono">Teléfono:</label>
                                <input type="text" id="modal_telefono" name="telefono">
                            </div>
                            <div class="form-group">
                                <label for="modal_email">Correo Electrónico:</label>
                                <input type="email" id="modal_email" name="email">
                            </div>
                            <div class="modal-actions">
                                <button type="button" class="cancelar" onclick="cerrarModal()">Cancelar</button>
                                <button type="submit" class="guardar">Guardar Cambios</button>
                            </div>
                        </form>
                        <?php if (!empty($errores)): ?>
                            <div class="error-message">
                                <?php foreach ($errores as $error): ?>
                                    <p><?php echo htmlspecialchars($error); ?></p>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                        <?php if ($mensaje_exito): ?>
                            <div class="success-message">
                                <p><?php echo htmlspecialchars($mensaje_exito); ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

            </div>
        </main>
    </div>
</div>

<script>
    const selectCliente = document.getElementById('cliente_id');
    const modalOverlay = document.getElementById('editarModal');
    const modalIdCliente = document.getElementById('modal_id_cliente');
    const modalNombre = document.getElementById('modal_nombre');
    const modalApellido = document.getElementById('modal_apellido');
    const modalDocumento = document.getElementById('modal_documento');
    const modalTelefono = document.getElementById('modal_telefono');
    const modalEmail = document.getElementById('modal_email');
    const formEditarCliente = document.getElementById('formEditarCliente');
    const mensajeConfirmacionDiv = document.getElementById('mensaje-confirmacion');

    // Variable para almacenar el ID del cliente actualmente seleccionado
    let clienteSeleccionadoId = null;

    selectCliente.addEventListener('change', function() {
        const clienteId = this.value;
        clienteSeleccionadoId = clienteId; // Actualizar el ID seleccionado

        if (clienteId) {
            fetch(`editar_cliente.php?id_editar_modal=${clienteId}`)
                .then(response => response.json())
                .then(data => {
                    if (data && !data.error) {
                        modalIdCliente.value = data.id_cliente;
                        modalNombre.value = data.nombre;
                        modalApellido.value = data.apellido;
                        modalDocumento.value = data.documento;
                        modalTelefono.value = data.telefono;
                        modalEmail.value = data.email;

                        // Mostrar el modal
                        modalOverlay.style.display = 'flex';
                    } else {
                        alert(data.error || "Error al cargar datos del cliente.");
                    }
                })
                .catch(error => {
                    console.error("Error al obtener datos del cliente:", error);
                    alert("Ocurrió un error al obtener los datos.");
                });
        }
    });

    function cerrarModal() {
        modalOverlay.style.display = 'none';
    }

    // Mostrar mensaje de confirmación si existe
    <?php if ($mensaje_exito): ?>
    mensajeConfirmacionDiv.textContent = "<?php echo htmlspecialchars($mensaje_exito); ?>";
    mensajeConfirmacionDiv.style.display = "block";
    setTimeout(() => {
        mensajeConfirmacionDiv.style.display = "none";
    }, 5000);
    <?php endif; ?>
</script>