<?php
session_start();

$current_page = basename($_SERVER['PHP_SELF']);

// **TODO:** Conexión a la base de datos y lógica para obtener la lista de clientes
// $clientes = ... (Resultado de la consulta a la base de datos)

?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Clientes - Rápidos del Altiplano</title>
    <link rel="stylesheet" href="../Inventario/EstilosInventario.css" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
</head>

<body>
<div class="dashboard-container">
    <aside class="sidebar">
        <div class="sidebar-header">
            <div class="sidebar-logo-container">
                <img src="../../../IMAGENES/LogoRapidosDelAltiplano.jpg" alt="Logo Rápidos del Altiplano"
                     class="sidebar-logo"
                     onerror="this.onerror=null; this.src='https://placehold.co/150x50/cccccc/333333?text=Logo'; this.alt='Logo Placeholder';">
            </div>
            <h2 class="sidebar-title">Menú Principal</h2>
        </div>
        <nav class="sidebar-nav">
            <?php
            // Rutas ajustadas
            $nav_links = [
                '../Dashboard/Dashboard.php' => ['icon' => '../../../IMAGENES/Dashboard.png', 'text' => 'Dashboard (Inicio)', 'alt' => 'Icono Dashboard'],
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
                    <a href="registrar_cliente.php" class="btn btn-primary">
                        <span class="icon">+</span> Registrar Cliente
                    </a>
                </div>

                <form method="GET" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                    <div class="action-bar">
                        <div class="search-field">
                            <span class="icon">🔍</span>
                            <input type="search" name="q" placeholder="Buscar por Nombre, ID..."
                                   value="<?php echo htmlspecialchars($busqueda ?? ''); ?>">
                        </div>
                        <select name="tipo_cliente">
                            <option value="">-- Todos los Tipos --</option>
                        </select>
                        <select name="vendedor_asignado">
                            <option value="">-- Todos los Vendedores --</option>
                        </select>
                        <button type="submit" class="btn btn-secondary">Buscar</button>
                        <a href="<?php echo htmlspecialchars(strtok($_SERVER["REQUEST_URI"], '?')); ?>"
                           class="btn btn-secondary">Limpiar</a>
                    </div>
                </form>

                <div class="table-container">
                    <table>
                        <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Tipo</th>
                            <th>Contacto</th>
                            <th>Correo Electrónico</th>
                            <th>Vendedor Asignado</th>
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
                                    <td><?php echo htmlspecialchars($cliente['tipo_cliente'] ?? '-'); ?></td>
                                    <td><?php echo htmlspecialchars($cliente['contacto'] ?? '-'); ?></td>
                                    <td><?php echo htmlspecialchars($cliente['email'] ?? '-'); ?></td>
                                    <td><?php echo htmlspecialchars($cliente['nombre_vendedor'] ?? 'Sin Asignar'); ?></td>
                                    <td class="actions-cell">
                                        <a href="detalle_cliente.php?id=<?php echo htmlspecialchars($cliente['id_cliente']); ?>"
                                           class="action-btn btn-ver-detalle" title="Ver Detalle"><span
                                                    class="icon">👁️</span></a>
                                        <a href="editar_cliente.php?id=<?php echo htmlspecialchars($cliente['id_cliente']); ?>"
                                           class="action-btn btn-editar-item" title="Editar"><span
                                                    class="icon">✏️</span></a>
                                        <button type="button" data-id="<?php echo htmlspecialchars($cliente['id_cliente']); ?>"
                                                data-nombre="<?php echo htmlspecialchars($cliente['nombre']); ?>"
                                                class="action-btn delete btn-eliminar-item" title="Eliminar"><span
                                                    class="icon">🗑️</span></button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <div class="pagination">
                </div>

            </div>
        </main>
    </div>
</div>

<script>
    // Aquí va el JavaScript para la lógica de la página (modales, etc.)
</script>

</body>

</html>