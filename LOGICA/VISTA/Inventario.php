
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Inventario - Rápidos del Altiplano</title>
    <link rel="stylesheet" href="..\VISTA\EstilosInventario.css" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

</head>
<body>
    <div class="dashboard-container">

        <aside class="sidebar">
             <div class="sidebar-header">
                 <div class="sidebar-logo-container">
                    <img src="..\..\IMAGENES\LogoRapidosDelAltiplano.jpg" alt="Logo Rápidos del Altiplano" class="sidebar-logo" onerror="this.onerror=null; this.src='https://placehold.co/150x50/cccccc/333333?text=Logo+Placeholder';">
                 </div>
                <h2 class="sidebar-title">Menú Principal</h2>
            </div>
            <nav class="sidebar-nav">
                <?php
                    // Definir los enlaces y sus archivos correspondientes
                    $nav_links = [
                        'Dashboard.php' => ['icon' => '../../IMAGENES/Dashboard.png', 'text' => 'Dashboard (Inicio)', 'alt' => 'Icono Dashboard'],
                        'Clientes.php' => ['icon' => '../../IMAGENES/Clientes.png', 'text' => 'Clientes', 'alt' => 'Icono Clientes'], // Asume Clientes.php
                        'InventarioStart.php' => ['icon' => '../../IMAGENES/Inventario.png', 'text' => 'Inventario', 'alt' => 'Icono Inventario'],
                        'Ventas.php' => ['icon' => '../../IMAGENES/Ventas-Compras.png', 'text' => 'Ventas-Compras', 'alt' => 'Icono Ventas'], // Asume Ventas.php
                        'RRHH.php' => ['icon' => '../../IMAGENES/RH.png', 'text' => 'Recursos Humanos', 'alt' => 'Icono Recursos Humanos'], // Asume RRHH.php
                        'Contabilidad.php' => ['icon' => '../../IMAGENES/Contabilidad.png', 'text' => 'Contabilidad', 'alt' => 'Icono Contabilidad'], // Asume Contabilidad.php
                        'Configuracion.php' => ['icon' => '../../IMAGENES/Configuracion.png', 'text' => 'Configuración', 'alt' => 'Icono Configuración'] // Asume Configuracion.php
                    ];
                ?>
                <?php foreach ($nav_links as $file => $link): ?>
                    <a href="<?php echo $file; ?>" class="sidebar-link<?php echo ($current_page == $file) ? ' active' : ''; ?>">
                        <img src="<?php echo htmlspecialchars($link['icon']); ?>" alt="<?php echo htmlspecialchars($link['alt']); ?>" class="sidebar-icon" onerror="this.style.display='none'; this.nextSibling.textContent = '[I] ' + this.nextSibling.textContent.trim();"> <?php echo htmlspecialchars($link['text']); ?>
                    </a>
                <?php endforeach; ?>
            </nav>
        </aside>
        <div class="main-content-area">
            <header class="header">
                 <div class="header-container">
                    <div class="header-left">
                        </div>
                    <div class="header-right">
                        <span class="user-info">
                            Bienvenido, <?php echo htmlspecialchars($_SESSION['nombre_usuario'] ?? 'Usuario'); ?>
                        </span>
                        <a href="../MODELO/CerrarSesion.php" class="logout-button"> <span class="icon"></span>
                            Cerrar Sesión
                        </a>
                    </div>
                </div>
            </header>
            <main class="main-content">
                <div class="main-container">

                    <div class="inventory-header">
                        <h1 class="inventory-title">Gestión de Inventario</h1>
                        <a href="formulario_item.php?accion=crear" class="btn btn-primary"> <span class="icon">[+]</span> Agregar Ítem
                        </a>
                    </div>

                    <form method="GET" action="../MODELO/Inventario.php"> <?php // Asume que este archivo se llama Inventario.php ?>
                        <div class="action-bar">
                            <div class="search-field">
                                <span class="icon">[🔍]</span>
                                <input type="search" name="q" placeholder="Buscar por Nombre, Código..." value="<?php echo htmlspecialchars($busqueda); ?>">
                            </div>
                            <select name="categoria">
                                <option value="">-- Todas las Categorías --</option>
                                <?php foreach ($categorias_disponibles as $cat): ?>
                                    <option value="<?php echo htmlspecialchars($cat); ?>" <?php echo ($categoria_filtro == $cat) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($cat); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <select name="proveedor">
                                <option value="">-- Todos los Proveedores --</option>
                                <?php foreach ($proveedores_disponibles as $prov): ?>
                                     <option value="<?php echo htmlspecialchars($prov); ?>" <?php echo ($proveedor_filtro == $prov) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($prov); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <label class="checkbox-container">
                                <input type="checkbox" name="bajo_minimo" value="1" <?php echo $mostrar_bajo_minimo ? 'checked' : ''; ?>>
                                Mostrar solo ítems bajo mínimo
                            </label>
                            <button type="submit" class="btn btn-secondary">Buscar</button>
                            <a href="../MODELO/Inventario.php" class="btn btn-secondary">Limpiar</a> <?php // Enlace para limpiar filtros ?>
                        </div>
                    </form>

                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>ID/Código</th>
                                    <th>Nombre</th>
                                    <th>Categoría</th>
                                    <th>Stock Actual</th>
                                    <th>Stock Mínimo</th>
                                    <th>Unidad Med.</th>
                                    <th>Precio Unit.</th>
                                    <th>Ubicación</th>
                                    <th>Proveedor</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($inventario_paginado)): ?>
                                    <tr>
                                        <td colspan="10" style="text-align: center; padding: 2rem;">No se encontraron ítems con los criterios seleccionados.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($inventario_paginado as $item): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($item['id']); ?></td>
                                            <td><?php echo htmlspecialchars($item['nombre']); ?></td>
                                            <td><?php echo htmlspecialchars($item['categoria']); ?></td>
                                            <td>
                                                <?php
                                                    $stock_actual = $item['stock'];
                                                    $stock_minimo = $item['minimo'];
                                                    if ($stock_actual <= $stock_minimo) {
                                                        echo '<span class="low-stock"><span class="icon">[!]</span> ' . htmlspecialchars($stock_actual) . '</span>';
                                                    } else {
                                                        echo htmlspecialchars($stock_actual);
                                                    }
                                                ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($item['minimo']); ?></td>
                                            <td><?php echo htmlspecialchars($item['unidad']); ?></td>
                                            <td><?php echo '$' . number_format($item['precio'], 0, ',', '.'); ?></td>
                                            <td><?php echo htmlspecialchars($item['ubicacion']); ?></td>
                                            <td><?php echo htmlspecialchars($item['proveedor']); ?></td>
                                            <td class="actions-cell">
                                                <a href="detalle_item.php?id=<?php echo urlencode($item['id']); ?>" class="action-btn" title="Ver Detalle"><span class="icon">[👁️]</span></a>
                                                <a href="formulario_item.php?accion=editar&id=<?php echo urlencode($item['id']); ?>" class="action-btn" title="Editar"><span class="icon">[✏️]</span></a>
                                                <a href="eliminar_item.php?id=<?php echo urlencode($item['id']); ?>" class="action-btn delete" title="Eliminar" onclick="return confirm('¿Estás seguro de que deseas eliminar este ítem: \'<?php echo htmlspecialchars(addslashes($item['nombre']), ENT_QUOTES); ?>\'?');"><span class="icon">[🗑️]</span></a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="pagination">
                        <div class="page-info">
                            Mostrando <?php echo $total_items > 0 ? $offset + 1 : 0; ?>-<?php echo min($offset + $items_por_pagina, $total_items); ?> de <?php echo $total_items; ?> ítems
                        </div>
                        <div class="page-nav">
                            <?php
                                // Construir URL base para paginación manteniendo filtros
                                $query_params = $_GET; // Copiar filtros actuales
                                unset($query_params['pagina']); // Quitar página anterior
                                $base_url = '../MODELO/Inventario.php?' . http_build_query($query_params);
                                $separator = empty($query_params) ? '' : '&';
                            ?>

                            <?php if ($pagina_actual > 1): ?>
                                <a href="<?php echo $base_url . $separator . 'pagina=' . ($pagina_actual - 1); ?>">Anterior</a>
                            <?php else: ?>
                                <button disabled>Anterior</button>
                            <?php endif; ?>

                            <?php // Opcional: Mostrar números de página ?>
                            <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                                <a href="<?php echo $base_url . $separator . 'pagina=' . $i; ?>" class="page-number <?php echo ($i == $pagina_actual) ? 'active' : ''; ?>">
                                    <?php echo $i; ?>
                                </a>
                            <?php endfor; ?>


                            <?php if ($pagina_actual < $total_paginas): ?>
                                <a href="<?php echo $base_url . $separator . 'pagina=' . ($pagina_actual + 1); ?>">Siguiente</a>
                            <?php else: ?>
                                <button disabled>Siguiente</button>
                            <?php endif; ?>
                        </div>
                    </div>

                </div> </main>
            </div>
        </div> </body>
</html>