<?php

session_start();

?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Inventario - Rápidos del Altiplano</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="EstilosInventario.css" />

  
</head>

<body>
    <div class="dashboard-container">

        <aside class="sidebar">
            <div class="sidebar-header">
                <div class="sidebar-logo-container">
                    <img src="..\..\IMAGENES\LogoRapidosDelAltiplano.jpg" alt="Logo Rápidos del Altiplano" class="sidebar-logo" onerror="this.onerror=null; this.src='https://placehold.co/150x50/cccccc/333333?text=Logo'; this.alt='Logo Placeholder';">
                </div>
                <h2 class="sidebar-title">Menú Principal</h2>
            </div>
            <nav class="sidebar-nav">
                <?php
                // Definición de los enlaces de navegación
                $nav_links = [
                    'Dashboard.php' => ['icon' => '../../IMAGENES/Dashboard.png', 'text' => 'Dashboard', 'alt' => 'Icono Dashboard'],
                    'Clientes.php' => ['icon' => '../../IMAGENES/Clientes.png', 'text' => 'Clientes', 'alt' => 'Icono Clientes'],
                    'InventarioStart.php' => ['icon' => '../../IMAGENES/Inventario.png', 'text' => 'Inventario', 'alt' => 'Icono Inventario'],
                    'Ventas.php' => ['icon' => '../../IMAGENES/Ventas-Compras.png', 'text' => 'Ventas-Compras', 'alt' => 'Icono Ventas'],
                    'RRHH.php' => ['icon' => '../../IMAGENES/RH.png', 'text' => 'Recursos Humanos', 'alt' => 'Icono RH'],
                    'Contabilidad.php' => ['icon' => '../../IMAGENES/Contabilidad.png', 'text' => 'Contabilidad', 'alt' => 'Icono Contabilidad'],
                    'Configuracion.php' => ['icon' => '../../IMAGENES/Configuracion.png', 'text' => 'Configuración', 'alt' => 'Icono Configuración']
                ];
                ?>
                <?php foreach ($nav_links as $file => $link): ?>
                    <a href="<?php echo $file; ?>" class="sidebar-link<?php echo ($current_page == $file) ? ' active' : ''; ?>">
                        <img src="<?php echo htmlspecialchars($link['icon']); ?>" alt="<?php echo htmlspecialchars($link['alt']); ?>" class="sidebar-icon" onerror="this.style.display='none'; this.nextSibling.textContent = '[i] ' + this.nextSibling.textContent.trim();">
                        <?php echo htmlspecialchars($link['text']); ?>
                    </a>
                <?php endforeach; ?>
            </nav>
        </aside> <div class="main-content-area">

            <header class="header">
                <div class="header-container">
                    <div class="header-left">
                        <h1 class="header-title">Inventario</h1>
                        </div>
                    <div class="header-right">
                        <span class="user-info">
                            Bienvenido, <?php echo htmlspecialchars($_SESSION['nombre_usuario'] ?? 'Usuario'); ?>
                        </span>
                        <a href="../CONTROLADOR/CerrarSesion.php" class="logout-button btn btn-secondary">
                            <span class="icon">[X]</span> Cerrar Sesión
                        </a>
                    </div>
                </div>
            </header> <main class="main-content">
                <div class="main-container">

                    <?php if (isset($_SESSION['mensaje'])): ?>
                        <div class="feedback-message <?php echo htmlspecialchars($_SESSION['tipo_mensaje'] ?? 'info'); ?>">
                            <?php echo htmlspecialchars($_SESSION['mensaje']); ?>
                            <?php
                                // Limpiar mensaje después de mostrarlo
                                unset($_SESSION['mensaje']);
                                unset($_SESSION['tipo_mensaje']);
                            ?>
                        </div>
                    <?php endif; ?>

                    <div class="inventory-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                        <h1 class="inventory-title" style="margin: 0;">Gestión de Inventario</h1>
                        <button id="btn-abrir-modal-agregar" class="btn btn-primary">
                            <span class="icon">[+]</span> Agregar Ítem
                        </button>
                    </div>

                    <form method="GET" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
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
                                    <option value="<?php echo htmlspecialchars($prov['id_proveedor']); ?>" <?php echo ($proveedor_filtro == $prov['id_proveedor']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($prov['nombre']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <label class="checkbox-container">
                                <input type="checkbox" name="bajo_minimo" value="1" <?php echo $mostrar_bajo_minimo ? 'checked' : ''; ?>>
                                Mostrar solo ítems bajo mínimo
                            </label>
                            <button type="submit" class="btn btn-secondary">Buscar</button>
                            <a href="<?php echo htmlspecialchars(strtok($_SERVER["REQUEST_URI"], '?')); ?>" class="btn btn-secondary">Limpiar</a>
                        </div>
                    </form>

                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>ID/Código</th> <th>Nombre</th> <th>Categoría</th>
                                    <th>Stock Actual</th> <th>Stock Mínimo</th> <th>Unidad Med.</th>
                                    <th>Precio Unit.</th> <th>Ubicación</th> <th>Proveedor</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (isset($error_db)): ?>
                                    <tr><td colspan="10" style="color: red; text-align: center; padding: 2rem;"><?php echo htmlspecialchars($error_db); ?></td></tr>
                                <?php elseif (empty($inventario_paginado)): ?>
                                    <tr>
                                        <td colspan="10" style="text-align: center; padding: 2rem; color: #6b7280;">
                                            <?php echo ($busqueda || $categoria_filtro || $proveedor_filtro || $mostrar_bajo_minimo) ? 'No se encontraron ítems con los criterios seleccionados.' : 'El inventario está vacío.'; ?>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($inventario_paginado as $item): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($item['id_producto']); ?></td>
                                            <td><?php echo htmlspecialchars($item['nombre']); ?></td>
                                            <td><?php echo htmlspecialchars($item['categoria'] ?? '-'); ?></td>
                                            <td>
                                                <?php // Resaltar stock bajo
                                                $stock_actual = $item['stock'];
                                                $stock_minimo = $item['stock_minimo'];
                                                if ($stock_minimo !== null && $stock_actual <= $stock_minimo) {
                                                    echo '<span class="low-stock"><span class="icon">[!]</span> ' . htmlspecialchars($stock_actual) . '</span>';
                                                } else {
                                                    echo htmlspecialchars($stock_actual);
                                                }
                                                ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($item['stock_minimo'] ?? 'N/A'); ?></td>
                                            <td><?php echo htmlspecialchars($item['unidad_medida'] ?? '-'); ?></td>
                                            <td><?php echo '$' . number_format($item['precio_unitario'] ?? 0, 0, ',', '.'); ?></td>
                                            <td><?php echo htmlspecialchars($item['ubicacion'] ?? '-'); ?></td>
                                            <td><?php echo htmlspecialchars($item['nombre_proveedor'] ?? '-'); ?></td>
                                            <td class="actions-cell">
                                                <a href="#" data-id="<?php echo htmlspecialchars($item['id_producto']); ?>" class="action-btn btn-ver-detalle" title="Ver Detalle"><span class="icon">[👁️]</span></a>
                                                <a href="formulario_item.php?accion=editar&id=<?php echo urlencode($item['id_producto']); ?>" class="action-btn" title="Editar"><span class="icon">[✏️]</span></a>
                                                <a href="eliminar_item.php?id=<?php echo urlencode($item['id_producto']); ?>" class="action-btn delete" title="Eliminar" onclick="return confirm('¿Estás seguro de que deseas eliminar este ítem: \'<?php echo htmlspecialchars(addslashes($item['nombre']), ENT_QUOTES); ?>\'? Esta acción no se puede deshacer.');"><span class="icon">[🗑️]</span></a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div> <div class="pagination">
                        <div class="page-info">
                            Mostrando <?php echo $total_items > 0 ? $offset + 1 : 0; ?>-<?php echo min($offset + $items_por_pagina, $total_items); ?> de <?php echo $total_items; ?> ítems
                        </div>
                        <?php if ($total_paginas > 1): ?>
                            <div class="page-nav">
                                <?php
                                    // Construir URL base para paginación manteniendo filtros
                                    $query_params = $_GET;
                                    unset($query_params['pagina']);
                                    $base_url = htmlspecialchars($_SERVER['PHP_SELF']) . '?' . http_build_query($query_params);
                                    $separator = empty($query_params) ? '' : '&';
                                ?>
                                <?php if ($pagina_actual > 1): ?>
                                    <a href="<?php echo $base_url . $separator . 'pagina=' . ($pagina_actual - 1); ?>">Anterior</a>
                                <?php else: ?>
                                    <button disabled>Anterior</button>
                                <?php endif; ?>

                                <?php
                                $rango_paginas = 2; // Cuántas páginas mostrar antes y después de la actual
                                $mostrar_inicio_fin = 1; // Cuántas páginas mostrar al inicio y al final siempre

                                for ($i = 1; $i <= $total_paginas; $i++) {
                                    // Mostrar siempre la primera, la última, la actual, y las cercanas a la actual
                                    if ($i == 1 || $i == $total_paginas || ($i >= $pagina_actual - $rango_paginas && $i <= $pagina_actual + $rango_paginas)) {
                                        echo '<a href="' . $base_url . $separator . 'pagina=' . $i . '" class="page-number ' . ($i == $pagina_actual ? 'active' : '') . '">' . $i . '</a>';
                                    } elseif ($i == $pagina_actual - $rango_paginas - 1 || $i == $pagina_actual + $rango_paginas + 1) {
                                        // Mostrar puntos suspensivos
                                        echo '<button disabled>...</button>';
                                    }
                                }
                                ?>

                                <?php if ($pagina_actual < $total_paginas): ?>
                                    <a href="<?php echo $base_url . $separator . 'pagina=' . ($pagina_actual + 1); ?>">Siguiente</a>
                                <?php else: ?>
                                    <button disabled>Siguiente</button>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div> </div> </main> </div> </div> <div id="modal-agregar-item" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">Agregar Nuevo Ítem al Inventario</h2>
                <button id="modal-agregar-close-btn" class="modal-close-btn" aria-label="Cerrar modal">&times;</button>
            </div>
            <form id="form-agregar-item" action="../MODELO/Inventario/GuardarItem.php" method="POST">
                <div class="modal-body">
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="modal-agregar-nombre">Nombre:</label>
                            <input type="text" id="modal-agregar-nombre" name="nombre" required>
                        </div>
                        <div class="form-group">
                            <label for="modal-agregar-categoria">Categoría:</label>
                            <select id="modal-agregar-categoria" name="categoria">
                                <option value="">Seleccione...</option>
                                <?php foreach ($categorias_disponibles as $cat): ?>
                                    <option value="<?php echo htmlspecialchars($cat); ?>"><?php echo htmlspecialchars($cat); ?></option>
                                <?php endforeach; ?>
                                <option value="__NUEVA__">[ Nueva Categoría ]</option>
                            </select>
                            <input type="text" id="modal-agregar-nueva_categoria" name="nueva_categoria" placeholder="Nombre nueva categoría" style="display: none; margin-top: 0.5rem;">
                        </div>
                         <div class="form-group">
                            <label for="modal-agregar-stock">Stock Inicial:</label>
                            <input type="number" id="modal-agregar-stock" name="stock" min="0" value="0" required>
                        </div>
                         <div class="form-group">
                            <label for="modal-agregar-stock_minimo">Stock Mínimo:</label>
                            <input type="number" id="modal-agregar-stock_minimo" name="stock_minimo" min="0" value="0">
                        </div>
                         <div class="form-group">
                            <label for="modal-agregar-unidad_medida">Unidad Medida:</label>
                            <input type="text" id="modal-agregar-unidad_medida" name="unidad_medida" placeholder="Ej: Unidad, Caja, Kg">
                        </div>
                        <div class="form-group">
                            <label for="modal-agregar-precio_unitario">Precio Unitario (Venta):</label>
                            <input type="number" id="modal-agregar-precio_unitario" name="precio_unitario" step="any" min="0" placeholder="Ej: 15000">
                        </div>
                         <div class="form-group">
                            <label for="modal-agregar-ubicacion">Ubicación:</label>
                            <input type="text" id="modal-agregar-ubicacion" name="ubicacion" placeholder="Ej: Estante A5">
                        </div>
                        <div class="form-group">
                            <label for="modal-agregar-id_proveedor">Proveedor:</label>
                            <select id="modal-agregar-id_proveedor" name="id_proveedor">
                                <option value="">Seleccione...</option>
                                <?php foreach ($proveedores_disponibles as $prov): ?>
                                    <option value="<?php echo htmlspecialchars($prov['id_proveedor']); ?>"><?php echo htmlspecialchars($prov['nombre']); ?></option>
                                <?php endforeach; ?>
                                </select>
                        </div>
                        <div class="form-group full-width">
                            <label for="modal-agregar-descripcion">Descripción:</label>
                            <textarea id="modal-agregar-descripcion" name="descripcion" rows="3"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" id="modal-agregar-cancel-btn" class="btn btn-secondary">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar Ítem</button>
                </div>
            </form>
        </div>
    </div> <div id="modal-ver-item" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modal-ver-title" class="modal-title">Detalles del Ítem</h2>
                <button id="modal-ver-close-btn" class="modal-close-btn" aria-label="Cerrar modal">&times;</button>
            </div>
            <div id="modal-ver-body" class="modal-body">
                <p class="loading-text">Cargando detalles...</p>
            </div>
            <div class="modal-footer">
                 <button type="button" id="modal-ver-cancel-btn" class="btn btn-secondary">Cerrar</button>
            </div>
        </div>
    </div> <script>
        // Esperar a que el DOM esté completamente cargado
        document.addEventListener('DOMContentLoaded', () => {

            // --- Funciones auxiliares de JS ---
            function htmlspecialchars(str) {
                if (typeof str !== 'string') str = String(str ?? ''); // Convertir a string, manejar null/undefined
                const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
                return str.replace(/[&<>"']/g, (m) => map[m]);
            }
            function nl2br(str) {
                if (typeof str !== 'string') str = String(str ?? '');
                return str.replace(/(\r\n|\n\r|\r|\n)/g, '<br>');
            }

            // --- Funcionalidad Modal Genérica ---
            function abrirModal(modalOverlay) {
                if(modalOverlay) {
                    modalOverlay.classList.add('active');
                    document.body.classList.add('modal-open');
                    // Enfocar el primer input/select/textarea visible dentro del modal si existe
                    const firstFocusable = modalOverlay.querySelector('input:not([type=hidden]):not([disabled]), select:not([disabled]), textarea:not([disabled]), button:not([disabled])');
                    if(firstFocusable) firstFocusable.focus();
                }
            }

            function cerrarModal(modalOverlay) {
                 if(modalOverlay) {
                    modalOverlay.classList.remove('active');
                    // Solo quitar modal-open si no hay otros modales activos
                    if (!document.querySelector('.modal-overlay.active')) {
                        document.body.classList.remove('modal-open');
                    }
                }
            }

            // --- Lógica Específica: Modal AGREGAR Ítem ---
            const modalAgregarOverlay = document.getElementById('modal-agregar-item');
            const botonAbrirModalAgregar = document.getElementById('btn-abrir-modal-agregar');
            const modalAgregarCloseBtn = document.getElementById('modal-agregar-close-btn');
            const modalAgregarCancelBtn = document.getElementById('modal-agregar-cancel-btn');
            const itemAgregarForm = document.getElementById('form-agregar-item');
            const categoriaAgregarSelect = document.getElementById('modal-agregar-categoria');
            const nuevaCategoriaAgregarInput = document.getElementById('modal-agregar-nueva_categoria');

            if (botonAbrirModalAgregar) {
                botonAbrirModalAgregar.addEventListener('click', (event) => {
                    event.preventDefault();
                    abrirModal(modalAgregarOverlay);
                });
            }

            function cerrarYResetearModalAgregar() {
                cerrarModal(modalAgregarOverlay);
                if (itemAgregarForm) itemAgregarForm.reset(); // Limpiar formulario
                if (nuevaCategoriaAgregarInput) { // Ocultar campo nueva categoría
                    nuevaCategoriaAgregarInput.style.display = 'none';
                    nuevaCategoriaAgregarInput.value = '';
                }
                if (categoriaAgregarSelect && categoriaAgregarSelect.value === '__NUEVA__') { // Resetear select si estaba en nueva
                    categoriaAgregarSelect.value = '';
                }
            }

            if(modalAgregarCloseBtn) modalAgregarCloseBtn.addEventListener('click', cerrarYResetearModalAgregar);
            if(modalAgregarCancelBtn) modalAgregarCancelBtn.addEventListener('click', cerrarYResetearModalAgregar);
            if(modalAgregarOverlay) { // Cerrar al hacer clic fuera del contenido
                modalAgregarOverlay.addEventListener('click', (event) => {
                    if (event.target === modalAgregarOverlay) cerrarYResetearModalAgregar();
                });
            }

            // Mostrar/ocultar input de nueva categoría
            if(categoriaAgregarSelect && nuevaCategoriaAgregarInput) {
                categoriaAgregarSelect.addEventListener('change', () => {
                    const esNueva = categoriaAgregarSelect.value === '__NUEVA__';
                    nuevaCategoriaAgregarInput.style.display = esNueva ? 'block' : 'none';
                    nuevaCategoriaAgregarInput.required = esNueva; // Hacer requerido si se muestra
                    if(esNueva) nuevaCategoriaAgregarInput.focus(); else nuevaCategoriaAgregarInput.value = '';
                });
            }

            // --- Lógica Específica: Modal VER DETALLE Ítem ---
            const modalVerOverlay = document.getElementById('modal-ver-item');
            const modalVerBody = document.getElementById('modal-ver-body');
            const modalVerTitle = document.getElementById('modal-ver-title');
            const modalVerCloseBtn = document.getElementById('modal-ver-close-btn');
            const modalVerCancelBtn = document.getElementById('modal-ver-cancel-btn');
            // const modalVerEditBtn = document.getElementById('modal-ver-edit-btn'); // Si tuvieras botón editar

            function cerrarYResetearModalVer() {
                cerrarModal(modalVerOverlay);
                if(modalVerBody) modalVerBody.innerHTML = '<p class="loading-text">Cargando detalles...</p>'; // Resetear contenido
                if(modalVerTitle) modalVerTitle.textContent = 'Detalles del Ítem'; // Resetear título
                // if(modalVerEditBtn) modalVerEditBtn.href = '#'; // Resetear botón editar
            }

            if (modalVerCloseBtn) modalVerCloseBtn.addEventListener('click', cerrarYResetearModalVer);
            if (modalVerCancelBtn) modalVerCancelBtn.addEventListener('click', cerrarYResetearModalVer);
            if (modalVerOverlay) { // Cerrar al hacer clic fuera
                modalVerOverlay.addEventListener('click', (event) => {
                    if (event.target === modalVerOverlay) cerrarYResetearModalVer();
                });
            }

            // Asignar evento a todos los botones "Ver Detalle" en la tabla
            document.querySelectorAll('.actions-cell a.btn-ver-detalle').forEach(button => {
                button.addEventListener('click', async (event) => {
                    event.preventDefault();
                    const itemId = button.getAttribute('data-id');
                    if (!itemId || !modalVerOverlay || !modalVerBody) return;

                    // Mostrar modal y estado de carga
                    abrirModal(modalVerOverlay);
                    modalVerBody.innerHTML = '<p class="loading-text">Cargando detalles...</p>';
                    if(modalVerTitle) modalVerTitle.textContent = 'Cargando...';
                    // if(modalVerEditBtn) modalVerEditBtn.style.display = 'none';

                    try {
                        // --- ¡IMPORTANTE: Ajusta esta RUTA si es necesario! ---
                        // Esta ruta asume que VerItem.php está en ../MODELO/Inventario/ relativo a este archivo PHP
                        const response = await fetch(`../MODELO/Inventario/VerItem.php?id=${encodeURIComponent(itemId)}`);
                        // ----------------------------------------------------

                        if (!response.ok) {
                            throw new Error(`Error HTTP ${response.status}: ${response.statusText}`);
                        }

                        const data = await response.json();

                        if (data.error) { // Error devuelto por el script PHP
                            throw new Error(data.error);
                        }

                        if (data.item) { // Éxito, mostrar datos
                            const item = data.item;
                            if(modalVerTitle) modalVerTitle.textContent = `Detalles de: ${htmlspecialchars(item.nombre ?? 'Ítem')}`;

                            // Formatear precio (ejemplo Colombia)
                            const precio = parseFloat(item.precio_unitario ?? 0);
                            const precioFormateado = isNaN(precio) ? 'N/A' : precio.toLocaleString('es-CO', { style: 'currency', currency: 'COP', minimumFractionDigits: 0, maximumFractionDigits: 0 });

                            // Construir HTML de detalles
                            let detalleHtml = '<dl class="detail-list">'; // Usar la clase definida en CSS
                            detalleHtml += `<dt>ID/Código:</dt><dd>${htmlspecialchars(item.id_producto ?? '-')}</dd>`;
                            detalleHtml += `<dt>Nombre:</dt><dd>${htmlspecialchars(item.nombre ?? '-')}</dd>`;
                            detalleHtml += `<dt>Categoría:</dt><dd>${htmlspecialchars(item.categoria ?? '-')}</dd>`;
                            detalleHtml += `<dt>Descripción:</dt><dd>${nl2br(htmlspecialchars(item.descripcion ?? '-')) || '-'}</dd>`; // Mostrar '-' si está vacío
                            detalleHtml += `<dt>Stock Actual:</dt><dd>${htmlspecialchars(item.stock ?? '0')}</dd>`;
                            detalleHtml += `<dt>Stock Mínimo:</dt><dd>${htmlspecialchars(item.stock_minimo ?? 'N/A')}</dd>`;
                            detalleHtml += `<dt>Unidad Medida:</dt><dd>${htmlspecialchars(item.unidad_medida ?? '-')}</dd>`;
                            detalleHtml += `<dt>Precio Unitario:</dt><dd>${precioFormateado}</dd>`;
                            detalleHtml += `<dt>Ubicación:</dt><dd>${htmlspecialchars(item.ubicacion ?? '-')}</dd>`;
                            detalleHtml += `<dt>Proveedor:</dt><dd>${htmlspecialchars(item.nombre_proveedor ?? 'No asignado')}</dd>`;
                            // Podrías añadir más campos si existen (fecha creación, última modificación, etc.)
                            // detalleHtml += `<dt>Fecha Creación:</dt><dd>${htmlspecialchars(item.fecha_creacion ?? '-')}</dd>`;
                            detalleHtml += '</dl>';
                            modalVerBody.innerHTML = detalleHtml;

                            // Actualizar y mostrar botón editar si existe
                            // if(modalVerEditBtn) {
                            //     modalVerEditBtn.href = `formulario_item.php?accion=editar&id=${encodeURIComponent(item.id_producto)}`;
                            //     modalVerEditBtn.style.display = 'inline-flex';
                            // }

                        } else {
                            // Respuesta inesperada del servidor
                            throw new Error("Respuesta inválida del servidor.");
                        }
                    } catch (error) {
                        // Mostrar error en el modal
                        console.error("Error al cargar detalles:", error);
                        modalVerBody.innerHTML = `<p class="error-text"><strong>Error al cargar detalles:</strong><br>${htmlspecialchars(error.message)}</p>`;
                        if(modalVerTitle) modalVerTitle.textContent = 'Error';
                    }
                });
            });

            // --- Cerrar Modales con Tecla Escape ---
            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape') {
                    const activeModal = document.querySelector('.modal-overlay.active');
                    if (activeModal) {
                        if (activeModal.id === 'modal-agregar-item') {
                            cerrarYResetearModalAgregar();
                        } else if (activeModal.id === 'modal-ver-item') {
                            cerrarYResetearModalVer();
                        } else {
                            // Lógica para otros modales si los hubiera
                            cerrarModal(activeModal);
                        }
                    }
                }
            });

        }); // Fin de DOMContentLoaded
    </script>

</body>
</html>