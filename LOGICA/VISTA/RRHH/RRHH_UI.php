<?php
session_start();
require_once '../../MODELO/RRHH/EmpleadoModelo.php';

$current_page = basename($_SERVER['PHP_SELF']);

// Lógica de CRUD (se mantiene igual)
$modo = 'crear';
$empleado = null;

if (isset($_GET['editar'])) {
    $modo = 'editar';
    $empleado = EmpleadoModelo::obtenerPorId($_GET['editar']);
}

if (isset($_GET['eliminar'])) {
    EmpleadoModelo::eliminar($_GET['eliminar']);
    header("Location: RRHH_UI.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($_POST['accion'] === 'crear') {
        EmpleadoModelo::guardar($_POST['nombre'], $_POST['apellido'], $_POST['cargo'], $_POST['salario'], $_POST['fecha'], $_POST['estado']);
    } elseif ($_POST['accion'] === 'editar') {
        EmpleadoModelo::actualizar($_POST['id'], $_POST['nombre'], $_POST['apellido'], $_POST['cargo'], $_POST['salario'], $_POST['fecha'], $_POST['estado']);
    }
    header("Location: RRHH_UI.php");
    exit;
}

$empleados_todos = EmpleadoModelo::obtenerTodos();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>RRHH - Rápidos del Altiplano</title>
    <link rel="stylesheet" href="../Dashboard/EstilosDashboard.css" />
    <link rel="stylesheet" href="EstilosRRHH.css" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" integrity="sha512-9usAa10IRO0HhonpyAIVpjrylPvoDwiPUiKdWk5t3PyolY1cOd4DSE0Ga+ri4AuTroPR5aQvXU9xC6qOPnzFeg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        .search-filter-bar {
            display: flex;
            gap: 20px;
            align-items: center;
        }

        .search-input {
            display: flex;
            align-items: center;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 5px 10px;
            flex-grow: 2;
            background-color: white;
        }

        .search-input i {
            margin-right: 10px;
            color: #777;
        }

        .search-input input[type="text"] {
            border: none;
            outline: none;
            flex-grow: 1;
            font-size: 16px;
        }

        .filter-controls {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .filter-controls select, .filter-controls button {
            padding: 8px 12px;
            border-radius: 5px;
            border: 1px solid #ddd;
            font-size: 16px;
            background-color: white;
        }

        .filter-controls button {
            background-color: #007bff;
            color: white;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .filter-controls button:hover {
            background-color: #0056b3;
        }

        .filter-container {
            border: 1px solid #ccc;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 20px;
            background-color:rgb(255, 255, 255);
        }
    </style>
</head>
<body>
<div class="dashboard-container">

    <aside class="sidebar">
        <div class="sidebar-header">
            <div class="sidebar-logo-container">
                <img src="../../../IMAGENES/LogoRapidosDelAltiplano.jpg" alt="Logo" class="sidebar-logo">
            </div>
            <h2 class="sidebar-title">Menú Principal</h2>
        </div>
        <nav class="sidebar-nav">
            <?php
            $nav_links = [
                '../Dashboard/Dashboard.php' => ['icon' => '../../../IMAGENES/Dashboard.png', 'text' => 'Dashboard (Inicio)', 'alt' => 'Dashboard'],
                '../Clientes/Clientes.php' => ['icon' => '../../../IMAGENES/Clientes.png', 'text' => 'Clientes', 'alt' => 'Clientes'],
                '../Inventario/InventarioStart.php' => ['icon' => '../../../IMAGENES/Inventario.png', 'text' => 'Inventario', 'alt' => 'Inventario'],
                'Ventas.php' => ['icon' => '../../../IMAGENES/Ventas-Compras.png', 'text' => 'Ventas-Compras', 'alt' => 'Ventas'],
                '../RRHH/RRHH_UI.php' => ['icon' => '../../../IMAGENES/RH.png', 'text' => 'Recursos Humanos', 'alt' => 'RRHH'],
                'Contabilidad.php' => ['icon' => '../../../IMAGENES/Contabilidad.png', 'text' => 'Contabilidad', 'alt' => 'Contabilidad'],
                'Configuracion.php' => ['icon' => '../../../IMAGENES/Configuracion.png', 'text' => 'Configuración', 'alt' => 'Configuración']
            ];
            foreach ($nav_links as $file => $link):
            ?>
                <a href="<?= $file ?>" class="sidebar-link<?= ($current_page == basename($file)) ? ' active' : '' ?>">
                    <img src="<?= $link['icon'] ?>" alt="<?= $link['alt'] ?>" class="sidebar-icon"> <?= $link['text'] ?>
                </a>
            <?php endforeach; ?>
        </nav>
    </aside>

    <div class="main-content-area">
        <header class="header">
            <div class="header-container">
                <h1 class="header-title">Recursos Humanos</h1>
                <div class="header-right">
                    <span class="user-info">Bienvenido, <?= htmlspecialchars($_SESSION['nombre_usuario'] ?? 'Usuario') ?></span>
                    <a href="../../MODELO/CerrarSesion.php" class="logout-button">Cerrar Sesión</a>
                </div>
            </div>
        </header>

        <main class="main-content">

            <section class="crud-section">
                <div class="crud-header">
                    <h2 style="font-size: 1.75rem; font-weight: 700; color: #111827;">Gestión de Empleados</h2>
                    <button class="add-new-button" onclick="document.getElementById('modal-form').style.display='block'">
                        <i class="fas fa-plus"></i> Agregar Empleado
                    </button>
                </div>

                <div class="filter-container">
                    <div class="search-filter-bar">
                        <div class="search-input">
                            <i>🔍</i>
                            <input type="text" id="search-input" placeholder="Buscar por Nombre, Apellido, Cargo...">
                        </div>
                        <div class="filter-controls">
                            <select id="filter-cargo">
                                <option value="">-- Todos los Cargos --</option>
                                <?php
                                // Obtener los cargos únicos de los empleados para el filtro
                                $cargos = array();
                                if ($empleados_todos) {
                                    while ($row = $empleados_todos->fetch_assoc()) {
                                        if (!in_array($row['cargo'], $cargos)) {
                                            $cargos[] = $row['cargo'];
                                            echo '<option value="' . htmlspecialchars($row['cargo']) . '">' . htmlspecialchars($row['cargo']) . '</option>';
                                        }
                                    }
                                    $empleados_todos->data_seek(0); // Volver al inicio del resultado
                                }
                                ?>
                            </select>
                            <select id="filter-estado">
                                <option value="">-- Todos los Estados --</option>
                                <?php
                                // Obtener los estados únicos de los empleados para el filtro
                                $estados = array();
                                if ($empleados_todos) {
                                    while ($row = $empleados_todos->fetch_assoc()) {
                                        if (!in_array($row['estado'], $estados)) {
                                            $estados[] = $row['estado'];
                                            echo '<option value="' . htmlspecialchars($row['estado']) . '">' . htmlspecialchars($row['estado']) . '</option>';
                                        }
                                    }
                                    $empleados_todos->data_seek(0); // Volver al inicio del resultado
                                }
                                ?>
                            </select>
                            <button class="filter-button" onclick="filtrarTabla()">Filtrar</button>
                        </div>
                    </div>
                </div>

                <table class="data-table" id="empleados-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Apellido</th>
                            <th>Cargo</th>
                            <th>Salario</th>
                            <th>Fecha de Contratación</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($empleados_todos) {
                            while ($row = $empleados_todos->fetch_assoc()): ?>
                                <tr>
                                    <td><?= $row['id_empleado'] ?></td>
                                    <td><?= $row['nombre'] ?></td>
                                    <td><?= $row['apellido'] ?></td>
                                    <td><?= $row['cargo'] ?></td>
                                    <td>$<?= number_format($row['salario'], 2, ',', '.') ?></td>
                                    <td><?= $row['fecha_contratacion'] ?></td>
                                    <td><?= $row['estado'] ?></td>
                                    <td class="actions">
                                        <a href="RRHH_UI.php?editar=<?= $row['id_empleado'] ?>" class="edit-button"><i class="fas fa-pencil-alt"></i></a>
                                        <a href="#" class="delete-button" onclick="mostrarConfirmacion(<?= $row['id_empleado'] ?>); return false;">
                                            <i class="fas fa-trash-alt"></i>
                                        </a>



                                    </td>
                                </tr>
                            <?php endwhile;
                        } else {
                            echo '<tr><td colspan="8">No se encontraron empleados.</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </section>

            <div id="modal-form" class="modal">
                <form class="modal-content" method="POST">
                    <span onclick="document.getElementById('modal-form').style.display='none'" class="close-button">&times;</span>
                    <h2><?= $modo === 'crear' ? 'Agregar Nuevo Empleado' : 'Editar Empleado' ?></h2>
                    <?php if ($modo === 'editar'): ?>
                        <input type="hidden" name="id" value="<?= $empleado['id_empleado'] ?>">
                    <?php endif; ?>
                    <input type="hidden" name="accion" value="<?= $modo ?>">

                    <label for="nombre">Nombre:</label>
                    <input type="text" id="nombre" name="nombre" value="<?= $empleado['nombre'] ?? '' ?>" required>

                    <label for="apellido">Apellido:</label>
                    <input type="text" id="apellido" name="apellido" value="<?= $empleado['apellido'] ?? '' ?>" required>

                    <label for="cargo">Cargo:</label>
                    <input type="text" id="cargo" name="cargo" value="<?= $empleado['cargo'] ?? '' ?>" required>

                    <label for="salario">Salario:</label>
                    <input type="number" step="0.01" id="salario" name="salario" value="<?= $empleado['salario'] ?? '' ?>" required>

                    <label for="fecha">Fecha de Contratación:</label>
                    <input type="date" id="fecha" name="fecha" value="<?= $empleado['fecha_contratacion'] ?? '' ?>" required>

                    <label for="estado">Estado:</label>
                    <input type="text" id="estado" name="estado" value="<?= $empleado['estado'] ?? '' ?>" required>

                    <div class="modal-actions">
                        <button type="submit" class="btn-primary"><?= $modo === 'crear' ? 'Guardar' : 'Actualizar' ?></button>
                        <button type="button" class="btn-secondary" onclick="document.getElementById('modal-form').style.display='none'">Cancelar</button>
                    </div>
                </form>
            </div>

            <div id="confirmacion-modal" class="modal" style="display:none;">
                <div class="modal-content">
                    <span onclick="cerrarModalConfirmacion()" class="close-button">&times;</span>
                    <h2>¿Estás seguro?</h2>
                    <p>¿Deseas eliminar este empleado? Esta acción no se puede deshacer.</p>
                    <div class="modal-actions">
                        <form method="GET" action="RRHH_UI.php">
                            <input type="hidden" name="eliminar" id="empleado-a-eliminar">
                            <button type="submit" class="btn-delete">Sí, eliminar</button>
                            <button type="button" class="btn-secondary" onclick="cerrarModalConfirmacion()">Cancelar</button>
                        </form>
                    </div>
                </div>
            </div>

        </main>
    </div>
</div>

<script>
    // Script para mostrar el modal en modo edición
    <?php if ($modo === 'editar'): ?>
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('modal-form').style.display = 'block';
        });
    <?php endif; ?>

    const searchInput = document.getElementById('search-input');
    const empleadosTable = document.getElementById('empleados-table').getElementsByTagName('tbody')[0];
    const filterCargo = document.getElementById('filter-cargo');
    const filterEstado = document.getElementById('filter-estado');

    searchInput.addEventListener('keyup', function() {
        filtrarTabla(); // Llama a la función de filtrado en cada cambio del input de búsqueda
    });

    function filtrarTabla() {
        const searchText = searchInput.value.toLowerCase();
        const cargoSeleccionado = filterCargo.value;
        const estadoSeleccionado = filterEstado.value;
        const rows = empleadosTable.getElementsByTagName('tr');

        for (let i = 0; i < rows.length; i++) {
            const rowData = rows[i].getElementsByTagName('td');
            if (rowData.length > 0) {
                const nombreCompleto = (rowData[1].textContent + ' ' + rowData[2].textContent).toLowerCase();
                const cargoTexto = rowData[3].textContent;
                const estadoTexto = rowData[6].textContent;
                let mostrarFila = true;

                // Filtrado por texto de búsqueda (Nombre, Apellido, Cargo)
                if (searchText && !nombreCompleto.includes(searchText) && !rowData[3].textContent.toLowerCase().includes(searchText)) {
                    mostrarFila = false;
                }

                // Filtrado por cargo
                if (cargoSeleccionado && cargoSeleccionado !== '' && cargoTexto !== cargoSeleccionado) {
                    mostrarFila = false;
                }

                // Filtrado por estado
                if (estadoSeleccionado && estadoSeleccionado !== '' && estadoTexto !== estadoSeleccionado) {
                    mostrarFila = false;
                }

                rows[i].style.display = mostrarFila ? '' : 'none';
            }
        }
    }

    function mostrarConfirmacion(idEmpleado) {
        document.getElementById('empleado-a-eliminar').value = idEmpleado;
        document.getElementById('confirmacion-modal').style.display = 'block';
    }

    function cerrarModalConfirmacion() {
        document.getElementById('confirmacion-modal').style.display = 'none';
    }

    // Llama a la función de filtrado inicial y para los cambios en los selectores
    document.addEventListener('DOMContentLoaded', filtrarTabla);
    filterCargo.addEventListener('change', filtrarTabla);
    filterEstado.addEventListener('change', filtrarTabla);
</script>

</body>
</html>