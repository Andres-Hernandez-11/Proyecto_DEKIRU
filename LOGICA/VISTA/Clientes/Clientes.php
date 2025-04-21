<?php
$host = 'localhost';
$usuario = 'root';
$contrasena = '';
$base_datos = 'dekirudb';

try {
    $conexion = new PDO("mysql:host=$host;dbname=$base_datos;charset=utf8", $usuario, $contrasena);
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error al conectar con la base de datos: " . $e->getMessage());
}
require_once '../../MODELO/conexion.php'; // Asegúrate que la ruta sea correcta

// Lógica para eliminar cliente
if (isset($_POST['eliminar_cliente']) && isset($_POST['id_eliminar'])) {
    $id_eliminar = filter_input(INPUT_POST, 'id_eliminar', FILTER_SANITIZE_NUMBER_INT);
    if ($id_eliminar) {
        $sql_eliminar = "DELETE FROM cliente WHERE id_cliente = :id";
        try {
            $stmt_eliminar = $conexion->prepare($sql_eliminar);
            $stmt_eliminar->bindParam(':id', $id_eliminar, PDO::PARAM_INT);
            if ($stmt_eliminar->execute()) {
                $_SESSION['mensaje'] = "Cliente eliminado con éxito.";
                $_SESSION['tipo_mensaje'] = "success";
            } else {
                $_SESSION['mensaje'] = "Error al eliminar el cliente.";
                $_SESSION['tipo_mensaje'] = "error";
            }
        } catch (PDOException $e) {
            $_SESSION['mensaje'] = "Error al eliminar el cliente: " . $e->getMessage();
            $_SESSION['tipo_mensaje'] = "error";
        }
        header("Location: Clientes.php"); // Recargar la página para ver los cambios
        exit();
    }
}

// Consulta para obtener los clientes
$sql = "SELECT id_cliente, nombre, apellido, documento, telefono, email
        FROM cliente
        ORDER BY nombre ASC";

try {
    $stmt = $conexion->prepare($sql);
    $stmt->execute();
    $clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $clientes = [];
    $_SESSION['mensaje'] = "Error al obtener los clientes: " . $e->getMessage();
    $_SESSION['tipo_mensaje'] = "error";
}

$current_page = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Clientes - Rápidos del Altiplano</title>
    <link rel="stylesheet" href="../Inventario/EstilosInventario.css" />
    <style>
        .botones-encabezado {
            display: flex;
            gap: 10px;
            align-items: center;
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
            text-align: center;
        }
        .modal button {
            margin: 10px;
            padding: 8px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .modal button.confirmar {
            background-color: #dc3545;
            color: white;
        }
        .modal button.cancelar {
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
                <img src="../../../IMAGENES/LogoRapidosDelAltiplano.jpg" alt="Logo Rápidos del Altiplano" class="sidebar-logo"
                     onerror="this.onerror=null; this.src='https://placehold.co/150x50/cccccc/333333?text=Logo'; this.alt='Logo Placeholder';">
            </div>
            <h2 class="sidebar-title">Menú Principal</h2>
        </div>
        <nav class="sidebar-nav">
            <?php
            $nav_links = [
                '../Dashboard/Dashboard.php' => ['icon' => '../../../IMAGENES/Dashboard.png', 'text' => 'Dashboard', 'alt' => 'Icono Dashboard'],
                '../clientes/Clientes.php' => ['icon' => '../../../IMAGENES/Clientes.png', 'text' => 'Clientes', 'alt' => 'Icono Clientes'],
                '../Inventario/InventarioStart.php' => ['icon' => '../../../IMAGENES/Inventario.png', 'text' => 'Inventario', 'alt' => 'Icono Inventario'],
                'Ventas.php' => ['icon' => '../../../IMAGENES/Ventas-Compras.png', 'text' => 'Ventas-Compras', 'alt' => 'Icono Ventas'],
                '../RRHH/RRHH_UI.php' => ['icon' => '../../../IMAGENES/RH.png', 'text' => 'Recursos Humanos', 'alt' => 'Icono Recursos Humanos'],
                'Contabilidad.php' => ['icon' => '../../../IMAGENES/Contabilidad.png', 'text' => 'Contabilidad', 'alt' => 'Icono Contabilidad'],
                'Configuracion.php' => ['icon' => '../../../IMAGENES/Configuracion.png', 'text' => 'Configuración', 'alt' => 'Icono Configuración']
            ];
            ?>
            <?php foreach ($nav_links as $file => $link): ?>
                <a href="<?php echo $file; ?>" class="sidebar-link<?php echo ($current_page == $file) ? ' active' : ''; ?>">
                    <img src="<?php echo htmlspecialchars($link['icon']); ?>" alt="<?php echo htmlspecialchars($link['alt']); ?>" class="sidebar-icon"
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
                    <h1 class="header-title">Clientes</h1>
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

                <div class="inventory-header">
                    <h1 class="inventory-title">Listado de Clientes</h1>
                    <div class="botones-encabezado">
                        <a href="registrar_cliente.php" class="btn btn-primary"><span class="icon">+</span> Registrar Cliente</a>
                        <a href="editar_cliente.php" class="btn btn-primary"><span class="icon">✏️</span> Editar Cliente</a>
                    </div>
                </div>

                <form method="GET" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                    <div class="action-bar">
                        <div class="search-field">
                            <span class="icon">🔍</span>
                            <input type="search" name="q" placeholder="Buscar por Nombre, Apellido, Documento..." value="<?php echo htmlspecialchars($_GET['q'] ?? ''); ?>">
                        </div>
                        <button type="submit" class="btn btn-secondary">Buscar</button>
                        <a href="<?php echo htmlspecialchars(strtok($_SERVER["REQUEST_URI"], '?')); ?>" class="btn btn-secondary">Limpiar</a>
                    </div>
                </form>

                <div class="table-container">
                    <table>
                        <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Apellido</th>
                            <th>Documento</th>
                            <th>Teléfono</th>
                            <th>Correo Electrónico</th>
                            <th>Acciones</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($clientes)): ?>
                            <tr>
                                <td colspan="7" style="text-align: center; padding: 2rem; color: #6b7280;">
                                    No se encontraron clientes.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($clientes as $cliente): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($cliente['id_cliente']); ?></td>
                                    <td><?php echo htmlspecialchars($cliente['nombre']); ?></td>
                                    <td><?php echo htmlspecialchars($cliente['apellido'] ?? '-'); ?></td>
                                    <td><?php echo htmlspecialchars($cliente['documento'] ?? '-'); ?></td>
                                    <td><?php echo htmlspecialchars($cliente['telefono'] ?? '-'); ?></td>
                                    <td><?php echo htmlspecialchars($cliente['email'] ?? '-'); ?></td>
                                    <td class="actions-cell">
                                        <button type="button" data-id="<?php echo htmlspecialchars($cliente['id_cliente']); ?>" data-nombre="<?php echo htmlspecialchars($cliente['nombre']); ?>" class="action-btn delete btn-eliminar-item" onclick="mostrarModal(<?php echo htmlspecialchars($cliente['id_cliente']); ?>, '<?php echo htmlspecialchars($cliente['nombre']); ?>')"><span class="icon">🗑️</span></button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <div class="pagination">
                </div>

                <div id="eliminarModal" class="modal-overlay">
                    <div class="modal">
                        <h2>Confirmar Eliminación</h2>
                        <p>¿Estás seguro de que deseas eliminar a <span id="nombre-eliminar"></span>?</p>
                        <form method="post" action="">
                            <input type="hidden" name="eliminar_cliente" value="true">
                            <input type="hidden" name="id_eliminar" id="id-eliminar-input" value="">
                            <button type="submit" class="confirmar">Eliminar</button>
                            <button type="button" class="cancelar" onclick="cerrarModal()">Cancelar</button>
                        </form>
                    </div>
                </div>

            </div>
        </main>
    </div>
</div>

<script>
    const modalOverlay = document.getElementById('eliminarModal');
    const nombreEliminarSpan = document.getElementById('nombre-eliminar');
    const idEliminarInput = document.getElementById('id-eliminar-input');

    function mostrarModal(id, nombre) {
        nombreEliminarSpan.textContent = nombre;
        idEliminarInput.value = id;
        modalOverlay.style.display = 'flex';
    }

    function cerrarModal() {
        modalOverlay.style.display = 'none';
    }
</script>

</body>
</html>