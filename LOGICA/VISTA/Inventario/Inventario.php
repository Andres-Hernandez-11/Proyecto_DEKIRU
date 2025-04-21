<?php
session_start();

$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Inventario - Rápidos del Altiplano</title>

    <link rel="stylesheet" href="..\Inventario\EstilosInventario.css" />

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    </head>

<body>
<div class="dashboard-container">

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
                    'Ventas.php' => ['icon' => '../../../IMAGENES/Ventas-Compras.png', 'text' => 'Ventas-Compras', 'alt' => 'Icono Ventas'], // Asume Ventas.php
                    '../RRHH/RRHH_UI.php' => ['icon' => '../../../IMAGENES/RH.png', 'text' => 'Recursos Humanos', 'alt' => 'Icono Recursos Humanos'], // Asume RRHH.php
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
                         <h1 class="header-title">Inventario</h1>
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
                        <h1 class="inventory-title">Gestión de Inventario</h1>
                        <button type="button" id="btn-abrir-modal-agregar" class="btn btn-primary">
                            <span class="icon">+</span> Agregar Ítem </button>
                    </div>

                    <form method="GET" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                        <div class="action-bar">
                             <div class="search-field">
                                 <span class="icon">🔍</span>
                                 <input type="search" name="q" placeholder="Buscar por Nombre, ID..."
                                        value="<?php echo htmlspecialchars($busqueda); ?>">
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
                                 Mostrar bajo mínimo
                             </label>
                             <button type="submit" class="btn btn-secondary">Buscar</button>
                             <a href="<?php echo htmlspecialchars(strtok($_SERVER["REQUEST_URI"], '?')); ?>" class="btn btn-secondary">Limpiar</a>
                        </div>
                    </form>

                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>ID</th> <th>Nombre</th> <th>Categoría</th>
                                    <th>Stock</th> <th>Mínimo</th> <th>Unidad</th>
                                    <th>Precio</th> <th>Ubicación</th> <th>Proveedor</th>
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
                                                <?php
                                                    $stock_actual = $item['stock'] ?? 0;
                                                    $stock_minimo = $item['stock_minimo'] ?? null;
                                                    if ($stock_minimo !== null && $stock_actual <= $stock_minimo && $stock_minimo > 0) {
                                                        echo '<span class="low-stock"><span class="icon">!</span> ' . htmlspecialchars($stock_actual) . '</span>';
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
                                                <button type="button" data-id="<?php echo htmlspecialchars($item['id_producto']); ?>" class="action-btn btn-ver-detalle" title="Ver Detalle"><span class="icon">👁️</span></button>
                                                <button type="button" data-id="<?php echo htmlspecialchars($item['id_producto']); ?>" class="action-btn btn-editar-item" title="Editar"><span class="icon">✏️</span></button>
                                                <button type="button" data-id="<?php echo htmlspecialchars($item['id_producto']); ?>" data-nombre="<?php echo htmlspecialchars($item['nombre']); ?>" class="action-btn delete btn-eliminar-item" title="Eliminar"><span class="icon">🗑️</span></button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="pagination">
                         <div class="page-info"> Mostrando <?php echo $total_items > 0 ? $offset + 1 : 0; ?>-<?php echo min($offset + $items_por_pagina, $total_items); ?> de <?php echo $total_items; ?> ítems </div>
                          <?php if ($total_paginas > 1): ?>
                          <div class="page-nav">
                              <?php
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
                                $rango_paginas = 2;
                                $mostrar_inicio_fin = 1;

                                for ($i = 1; $i <= $total_paginas; $i++) {
                                    if ($i <= $mostrar_inicio_fin || $i > $total_paginas - $mostrar_inicio_fin || ($i >= $pagina_actual - $rango_paginas && $i <= $pagina_actual + $rango_paginas) ) {
                                        echo '<a href="' . $base_url . $separator . 'pagina=' . $i . '" class="page-number ' . ($i == $pagina_actual ? 'active' : '') . '">' . $i . '</a>';
                                    }
                                    elseif ($i == $pagina_actual - $rango_paginas - 1 || $i == $pagina_actual + $rango_paginas + 1) {
                                        echo '<button disabled style="border:none; background:none; padding: 0.4rem 0.25rem; cursor: default;">...</button>';
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
                     </div>

                </div> </main>
        </div> </div>

    <div id="modal-agregar-item" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">Agregar Nuevo Ítem</h2>
                <button id="modal-agregar-close-btn" class="modal-close-btn" aria-label="Cerrar">&times;</button>
            </div>
            <form id="form-agregar-item" action="../../MODELO/Inventario/GuardarItem.php" method="POST">
                <div class="modal-body">
                    <div class="modal-form-grid">
                        <div class="modal-form-group"><label for="modal-agregar-nombre">Nombre:</label><input type="text" id="modal-agregar-nombre" name="nombre" required></div>
                        <div class="modal-form-group">
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
                        <div class="modal-form-group"><label for="modal-agregar-stock">Stock Inicial:</label><input type="number" id="modal-agregar-stock" name="stock" min="0" value="0" required></div>
                        <div class="modal-form-group"><label for="modal-agregar-stock_minimo">Stock Mínimo:</label><input type="number" id="modal-agregar-stock_minimo" name="stock_minimo" min="0" value="0"></div>
                        <div class="modal-form-group"><label for="modal-agregar-unidad_medida">Unidad Medida:</label><input type="text" id="modal-agregar-unidad_medida" name="unidad_medida" placeholder="Ej: Unidad, Caja, Kg"></div>
                        <div class="modal-form-group"><label for="modal-agregar-precio_unitario">Precio Unitario:</label><input type="number" id="modal-agregar-precio_unitario" name="precio_unitario" step="any" min="0" placeholder="Ej: 15000.00"></div>
                        <div class="modal-form-group"><label for="modal-agregar-ubicacion">Ubicación:</label><input type="text" id="modal-agregar-ubicacion" name="ubicacion" placeholder="Ej: Estante A5"></div>
                        <div class="modal-form-group">
                            <label for="modal-agregar-id_proveedor">Proveedor:</label>
                            <select id="modal-agregar-id_proveedor" name="id_proveedor">
                                <option value="">Seleccione...</option>
                                <?php foreach ($proveedores_disponibles as $prov): ?>
                                    <option value="<?php echo htmlspecialchars($prov['id_proveedor']); ?>"><?php echo htmlspecialchars($prov['nombre']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="modal-form-group full-width"><label for="modal-agregar-descripcion">Descripción:</label><textarea id="modal-agregar-descripcion" name="descripcion" rows="3"></textarea></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" id="modal-agregar-cancel-btn" class="btn btn-secondary">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar Ítem</button>
                </div>
            </form>
        </div>
    </div>

    <div id="modal-editar-item" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">Editar Ítem</h2>
                <button id="modal-editar-close-btn" class="modal-close-btn" aria-label="Cerrar">&times;</button>
            </div>
             <form id="form-editar-item" action="../../MODELO/Inventario/GuardarItem.php" method="POST">
                 <input type="hidden" id="modal-editar-id_producto" name="id_producto">
                 <div class="modal-body">
                     <div id="modal-editar-loading" class="loading-text" style="display: none; text-align: center; padding: 2rem;">Cargando datos...</div>
                     <div id="modal-editar-error" class="error-text" style="display: none; text-align: center; padding: 1rem;"></div>
                     <div id="modal-editar-form-content" class="modal-form-grid" style="display: none;">
                         <div class="modal-form-group">
                             <label for="modal-editar-nombre">Nombre:</label>
                             <input type="text" id="modal-editar-nombre" name="nombre" required>
                         </div>
                         <div class="modal-form-group">
                             <label for="modal-editar-categoria">Categoría:</label>
                             <select id="modal-editar-categoria" name="categoria">
                                 <option value="">Seleccione...</option>
                                 <?php foreach ($categorias_disponibles as $cat): ?>
                                     <option value="<?php echo htmlspecialchars($cat); ?>"><?php echo htmlspecialchars($cat); ?></option>
                                 <?php endforeach; ?>
                             </select>
                         </div>
                         <div class="modal-form-group">
                             <label for="modal-editar-stock">Stock:</label>
                             <input type="number" id="modal-editar-stock" name="stock" min="0" required>
                         </div>
                         <div class="modal-form-group">
                             <label for="modal-editar-stock_minimo">Stock Mínimo:</label>
                             <input type="number" id="modal-editar-stock_minimo" name="stock_minimo" min="0">
                         </div>
                         <div class="modal-form-group">
                             <label for="modal-editar-unidad_medida">Unidad Medida:</label>
                             <input type="text" id="modal-editar-unidad_medida" name="unidad_medida">
                         </div>
                         <div class="modal-form-group">
                             <label for="modal-editar-precio_unitario">Precio Unitario:</label>
                             <input type="number" id="modal-editar-precio_unitario" name="precio_unitario" step="any" min="0">
                         </div>
                         <div class="modal-form-group">
                             <label for="modal-editar-ubicacion">Ubicación:</label>
                             <input type="text" id="modal-editar-ubicacion" name="ubicacion">
                         </div>
                         <div class="modal-form-group">
                             <label for="modal-editar-id_proveedor">Proveedor:</label>
                             <select id="modal-editar-id_proveedor" name="id_proveedor">
                                 <option value="">Seleccione...</option>
                                 <?php foreach ($proveedores_disponibles as $prov): ?>
                                     <option value="<?php echo htmlspecialchars($prov['id_proveedor']); ?>"><?php echo htmlspecialchars($prov['nombre']); ?></option>
                                 <?php endforeach; ?>
                             </select>
                         </div>
                         <div class="modal-form-group full-width">
                             <label for="modal-editar-descripcion">Descripción:</label>
                             <textarea id="modal-editar-descripcion" name="descripcion" rows="3"></textarea>
                         </div>
                     </div>
                     </div>
                 <div class="modal-footer">
                     <button type="button" id="modal-editar-cancel-btn" class="btn btn-secondary">Cancelar</button>
                     <button type="submit" class="btn btn-primary">Actualizar Ítem</button>
                 </div>
             </form>
        </div>
    </div>

    <div id="modal-ver-item" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modal-ver-title" class="modal-title">Detalles del Ítem</h2>
                <button id="modal-ver-close-btn" class="modal-close-btn" aria-label="Cerrar">&times;</button>
            </div>
            <div id="modal-ver-body" class="modal-body">
                <p class="loading-text">Cargando detalles...</p>
            </div>
            <div class="modal-footer">
                <button type="button" id="modal-ver-cancel-btn" class="btn btn-secondary">Cerrar</button>
            </div>
        </div>
    </div>


    <div id="modal-eliminar-item" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">Confirmar Eliminación</h2>
                <button id="modal-eliminar-close-btn" class="modal-close-btn" aria-label="Cerrar">&times;</button>
            </div>
             <form id="form-eliminar-item">
                 <div class="modal-body">
                     <input type="hidden" id="modal-eliminar-id_producto" name="id_producto">
                     <p class="confirm-text">
                         ¿Estás seguro de eliminar el ítem:
                         <strong id="modal-eliminar-nombre-item" class="item-name"></strong>?
                         <br><br>
                         <strong class="warning-text">¡Esta acción no se puede deshacer!</strong>
                     </p>
                     <div id="modal-eliminar-error" class="error-message" style="display: none;"></div>
                 </div>
                 <div class="modal-footer">
                     <button type="button" id="modal-eliminar-cancel-btn" class="btn btn-secondary">Cancelar</button>
                     <button type="button" id="modal-eliminar-confirm-btn" class="btn btn-danger">Confirmar Eliminación</button>
                 </div>
             </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {

            // --- Funciones auxiliares ---
            function htmlspecialchars(str) {
                if (typeof str !== 'string') str = String(str ?? '');
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
                    const firstFocusable = modalOverlay.querySelector('input:not([type=hidden]):not([disabled]), select:not([disabled]), textarea:not([disabled]), button:not([disabled])');
                    if(firstFocusable) {
                        setTimeout(() => firstFocusable.focus(), 50);
                    }
                }
            }
            function cerrarModal(modalOverlay) {
                if(modalOverlay) {
                    modalOverlay.classList.remove('active');
                    if (!document.querySelector('.modal-overlay.active')) {
                        document.body.classList.remove('modal-open');
                    }
                }
            }

            // --- Lógica Modal AGREGAR ---
            const modalAgregarOverlay = document.getElementById('modal-agregar-item');
            const botonAbrirModalAgregar = document.getElementById('btn-abrir-modal-agregar');
            const modalAgregarCloseBtn = document.getElementById('modal-agregar-close-btn');
            const modalAgregarCancelBtn = document.getElementById('modal-agregar-cancel-btn');
            const itemAgregarForm = document.getElementById('form-agregar-item');
            const categoriaAgregarSelect = document.getElementById('modal-agregar-categoria');
            const nuevaCategoriaAgregarInput = document.getElementById('modal-agregar-nueva_categoria');

            // Abrir modal AGREGAR
            if (botonAbrirModalAgregar && modalAgregarOverlay) {
                botonAbrirModalAgregar.addEventListener('click', (e) => {
                    e.preventDefault();
                    cerrarYResetearModalAgregar(); // Resetea antes de abrir
                    abrirModal(modalAgregarOverlay);
                });
            }

            // Cerrar y resetear modal AGREGAR
            function cerrarYResetearModalAgregar() {
                cerrarModal(modalAgregarOverlay);
                if (itemAgregarForm) itemAgregarForm.reset();
                if (nuevaCategoriaAgregarInput) {
                    nuevaCategoriaAgregarInput.style.display = 'none';
                    nuevaCategoriaAgregarInput.value = '';
                    nuevaCategoriaAgregarInput.required = false;
                }
                if (categoriaAgregarSelect && categoriaAgregarSelect.value === '__NUEVA__') {
                    categoriaAgregarSelect.value = '';
                }
            }
            if(modalAgregarCloseBtn) modalAgregarCloseBtn.addEventListener('click', cerrarYResetearModalAgregar);
            if(modalAgregarCancelBtn) modalAgregarCancelBtn.addEventListener('click', cerrarYResetearModalAgregar);
            if(modalAgregarOverlay) {
                modalAgregarOverlay.addEventListener('click', (e) => {
                    if (e.target === modalAgregarOverlay) cerrarYResetearModalAgregar();
                });
            }

            // Lógica input nueva categoría AGREGAR
            if(categoriaAgregarSelect && nuevaCategoriaAgregarInput) {
                categoriaAgregarSelect.addEventListener('change', () => {
                    const esNueva = categoriaAgregarSelect.value === '__NUEVA__';
                    nuevaCategoriaAgregarInput.style.display = esNueva ? 'block' : 'none';
                    nuevaCategoriaAgregarInput.required = esNueva;
                    if(esNueva) nuevaCategoriaAgregarInput.focus();
                    else nuevaCategoriaAgregarInput.value = '';
                });
            }

            // --- Lógica Modal VER DETALLE ---
            const modalVerOverlay = document.getElementById('modal-ver-item');
            const modalVerBody = document.getElementById('modal-ver-body');
            const modalVerTitle = document.getElementById('modal-ver-title');
            const modalVerCloseBtn = document.getElementById('modal-ver-close-btn');
            const modalVerCancelBtn = document.getElementById('modal-ver-cancel-btn');

             // Cerrar y resetear modal VER
            function cerrarYResetearModalVer() {
                cerrarModal(modalVerOverlay);
                if(modalVerBody) modalVerBody.innerHTML = '<p class="loading-text">Cargando detalles...</p>';
                if(modalVerTitle) modalVerTitle.textContent = 'Detalles del Ítem';
            }
            if (modalVerCloseBtn) modalVerCloseBtn.addEventListener('click', cerrarYResetearModalVer);
            if (modalVerCancelBtn) modalVerCancelBtn.addEventListener('click', cerrarYResetearModalVer);
            if (modalVerOverlay) {
                modalVerOverlay.addEventListener('click', (e) => {
                    if (e.target === modalVerOverlay) cerrarYResetearModalVer();
                });
            }

            // Abrir y cargar datos modal VER
            document.querySelectorAll('.btn-ver-detalle').forEach(button => {
                button.addEventListener('click', async (event) => {
                    event.preventDefault();
                    const itemId = button.getAttribute('data-id');
                    if (!itemId || !modalVerOverlay) return;

                    abrirModal(modalVerOverlay);
                    if(modalVerBody) modalVerBody.innerHTML = '<p class="loading-text">Cargando detalles...</p>';
                    if(modalVerTitle) modalVerTitle.textContent = 'Cargando...';

                    try {
                        // FETCH a VerItem.php
                        const response = await fetch(`../../MODELO/Inventario/Ver.php?id=${encodeURIComponent(itemId)}`);
                        if (!response.ok) {
                            let errorMsg = `Error HTTP ${response.status}: ${response.statusText}`;
                            try { const errorData = await response.json(); if (errorData.error) errorMsg = errorData.error; } catch (e) {}
                            throw new Error(errorMsg);
                        }
                        const data = await response.json();
                        if (data.error) throw new Error(data.error);

                        if (data.item && modalVerBody && modalVerTitle) {
                            const item = data.item;
                            modalVerTitle.textContent = `Detalles de: ${htmlspecialchars(item.nombre ?? '?')}`;
                            const precio = parseFloat(item.precio_unitario ?? 0);
                            const precioFormateado = isNaN(precio) ? 'N/A' : '$' + precio.toLocaleString('es-CO', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
                            let detalleHtml = '<dl class="detail-list">';
                            detalleHtml += `<dt>ID/Código:</dt><dd>${htmlspecialchars(item.id_producto ?? '-')}</dd>`;
                            detalleHtml += `<dt>Nombre:</dt><dd>${htmlspecialchars(item.nombre ?? '-')}</dd>`;
                            detalleHtml += `<dt>Categoría:</dt><dd>${htmlspecialchars(item.categoria ?? '-')}</dd>`;
                            detalleHtml += `<dt>Descripción:</dt><dd>${nl2br(htmlspecialchars(item.descripcion ?? '-')) || '-'}</dd>`;
                            detalleHtml += `<dt>Stock Actual:</dt><dd>${htmlspecialchars(item.stock ?? '0')}</dd>`;
                            detalleHtml += `<dt>Stock Mínimo:</dt><dd>${htmlspecialchars(item.stock_minimo ?? 'N/A')}</dd>`;
                            detalleHtml += `<dt>Unidad Medida:</dt><dd>${htmlspecialchars(item.unidad_medida ?? '-')}</dd>`;
                            detalleHtml += `<dt>Precio Unitario:</dt><dd>${precioFormateado}</dd>`;
                            detalleHtml += `<dt>Ubicación:</dt><dd>${htmlspecialchars(item.ubicacion ?? '-')}</dd>`;
                            detalleHtml += `<dt>Proveedor:</dt><dd>${htmlspecialchars(item.nombre_proveedor ?? 'No asignado')}</dd>`;
                            detalleHtml += '</dl>';
                            modalVerBody.innerHTML = detalleHtml;
                        } else {
                            throw new Error("Respuesta inválida del servidor.");
                        }
                    } catch (error) {
                        console.error("Error al cargar detalles:", error);
                        if(modalVerBody) modalVerBody.innerHTML = `<p class="error-text"><strong>Error:</strong> ${htmlspecialchars(error.message)}</p>`;
                        if(modalVerTitle) modalVerTitle.textContent = 'Error';
                    }
                });
            });

            // --- Lógica Modal EDITAR ---
            const modalEditarOverlay = document.getElementById('modal-editar-item');
            const modalEditarCloseBtn = document.getElementById('modal-editar-close-btn');
            const modalEditarCancelBtn = document.getElementById('modal-editar-cancel-btn');
            const itemEditarForm = document.getElementById('form-editar-item');
            const modalEditarContent = document.getElementById('modal-editar-form-content');
            const modalEditarLoading = document.getElementById('modal-editar-loading');
            const modalEditarError = document.getElementById('modal-editar-error');

            // Cerrar y resetear modal EDITAR
            function cerrarYResetearModalEditar() {
                cerrarModal(modalEditarOverlay);
                if (itemEditarForm) itemEditarForm.reset();
                if (modalEditarLoading) modalEditarLoading.style.display = 'none';
                if (modalEditarError) modalEditarError.style.display = 'none';
                if (modalEditarContent) modalEditarContent.style.display = 'none';
            }
            if (modalEditarCloseBtn) modalEditarCloseBtn.addEventListener('click', cerrarYResetearModalEditar);
            if (modalEditarCancelBtn) modalEditarCancelBtn.addEventListener('click', cerrarYResetearModalEditar);
            if (modalEditarOverlay) {
                modalEditarOverlay.addEventListener('click', (e) => {
                    if (e.target === modalEditarOverlay) cerrarYResetearModalEditar();
                });
            }

            // Abrir y cargar datos modal EDITAR
            document.querySelectorAll('.btn-editar-item').forEach(button => {
                button.addEventListener('click', async (event) => {
                    event.preventDefault();
                    const itemId = button.getAttribute('data-id');
                    if (!itemId || !modalEditarOverlay) return;

                    abrirModal(modalEditarOverlay);
                    if (modalEditarLoading) modalEditarLoading.style.display = 'block';
                    if (modalEditarError) modalEditarError.style.display = 'none';
                    if (modalEditarContent) modalEditarContent.style.display = 'none';

                    try {
                         // FETCH a VerItem.php para obtener datos
                        const response = await fetch(`../../MODELO/Inventario/Ver.php?id=${encodeURIComponent(itemId)}`);
                        if (!response.ok) {
                             let errorMsg = `Error HTTP ${response.status}: ${response.statusText}`;
                             try { const errorData = await response.json(); if (errorData.error) errorMsg = errorData.error; } catch (e) {}
                             throw new Error(errorMsg);
                        }
                        const data = await response.json();
                        if (data.error) throw new Error(data.error);

                        if (data.item && itemEditarForm) {
                            const item = data.item;
                            // Poblar el formulario
                            itemEditarForm.querySelector('#modal-editar-id_producto').value = item.id_producto ?? '';
                            itemEditarForm.querySelector('#modal-editar-nombre').value = item.nombre ?? '';
                            itemEditarForm.querySelector('#modal-editar-categoria').value = item.categoria ?? '';
                            itemEditarForm.querySelector('#modal-editar-stock').value = item.stock ?? 0;
                            itemEditarForm.querySelector('#modal-editar-stock_minimo').value = item.stock_minimo ?? 0;
                            itemEditarForm.querySelector('#modal-editar-unidad_medida').value = item.unidad_medida ?? '';
                            itemEditarForm.querySelector('#modal-editar-precio_unitario').value = item.precio_unitario ?? '';
                            itemEditarForm.querySelector('#modal-editar-ubicacion').value = item.ubicacion ?? '';
                            itemEditarForm.querySelector('#modal-editar-id_proveedor').value = item.id_proveedor ?? '';
                            itemEditarForm.querySelector('#modal-editar-descripcion').value = item.descripcion ?? '';

                            if (modalEditarLoading) modalEditarLoading.style.display = 'none';
                            if (modalEditarContent) modalEditarContent.style.display = 'grid';
                        } else {
                            throw new Error("Datos de ítem no encontrados.");
                        }
                    } catch (error) {
                        console.error("Error al cargar datos para editar:", error);
                        if (modalEditarLoading) modalEditarLoading.style.display = 'none';
                        if (modalEditarError) {
                            modalEditarError.textContent = `Error: ${htmlspecialchars(error.message)}`;
                            modalEditarError.style.display = 'block';
                        }
                    }
                });
            });


            // --- Lógica Modal ELIMINAR ---
            const modalEliminarOverlay = document.getElementById('modal-eliminar-item');
            const modalEliminarCloseBtn = document.getElementById('modal-eliminar-close-btn');
            const modalEliminarCancelBtn = document.getElementById('modal-eliminar-cancel-btn');
            const modalEliminarConfirmBtn = document.getElementById('modal-eliminar-confirm-btn');
            const modalEliminarNombreSpan = document.getElementById('modal-eliminar-nombre-item');
            const modalEliminarIdInput = document.getElementById('modal-eliminar-id_producto');
            const modalEliminarError = document.getElementById('modal-eliminar-error');

            // Cerrar y resetear modal ELIMINAR
            function cerrarYResetearModalEliminar() {
                cerrarModal(modalEliminarOverlay);
                if(modalEliminarNombreSpan) modalEliminarNombreSpan.textContent = '';
                if(modalEliminarIdInput) modalEliminarIdInput.value = '';
                if(modalEliminarError) { modalEliminarError.textContent = ''; modalEliminarError.style.display = 'none';}
                if(modalEliminarConfirmBtn) modalEliminarConfirmBtn.disabled = false;
            }
            if (modalEliminarCloseBtn) modalEliminarCloseBtn.addEventListener('click', cerrarYResetearModalEliminar);
            if (modalEliminarCancelBtn) modalEliminarCancelBtn.addEventListener('click', cerrarYResetearModalEliminar);
            if (modalEliminarOverlay) {
                modalEliminarOverlay.addEventListener('click', (e) => {
                    if (e.target === modalEliminarOverlay) cerrarYResetearModalEliminar();
                });
            }

            // Abrir modal ELIMINAR (llenar datos)
            document.querySelectorAll('.btn-eliminar-item').forEach(button => {
                button.addEventListener('click', (event) => {
                    event.preventDefault();
                    const itemId = button.getAttribute('data-id');
                    const itemName = button.getAttribute('data-nombre');

                    if (itemId && itemName && modalEliminarOverlay && modalEliminarIdInput && modalEliminarNombreSpan) {
                        modalEliminarIdInput.value = itemId;
                        modalEliminarNombreSpan.textContent = itemName;
                        if(modalEliminarError) { modalEliminarError.textContent = ''; modalEliminarError.style.display = 'none';}
                        if(modalEliminarConfirmBtn) modalEliminarConfirmBtn.disabled = false;
                        abrirModal(modalEliminarOverlay);
                    } else {
                        console.error("Faltan datos o elementos para modal eliminar.");
                        alert("Error al preparar eliminación.");
                    }
                });
             });

             // Confirmar ELIMINACIÓN (botón dentro del modal)
             if (modalEliminarConfirmBtn && modalEliminarIdInput) {
                 modalEliminarConfirmBtn.addEventListener('click', async (event) => {
                     event.preventDefault();
                     const itemId = modalEliminarIdInput.value;
                     if (!itemId) {
                         if(modalEliminarError) {
                             modalEliminarError.textContent = 'Error: ID no especificado.';
                             modalEliminarError.style.display = 'block';
                         }
                         return;
                     }

                     modalEliminarConfirmBtn.disabled = true;
                     if(modalEliminarError) { modalEliminarError.textContent = ''; modalEliminarError.style.display = 'none';}

                     // FETCH a EliminarItem.php
                     const url = `../../MODELO/Inventario/EliminarItem.php?id=${encodeURIComponent(itemId)}`;

                     try {
                         const response = await fetch(url, { method: 'GET' });

                         if (!response.ok && response.type !== 'opaqueredirect') {
                             let errorMsg = `Error HTTP ${response.status}: ${response.statusText}`;
                              if(response.type !== 'opaqueredirect'){
                                 try { const errorData = await response.json(); if (errorData.error) errorMsg = errorData.error; } catch(e) {}
                             }
                             throw new Error(errorMsg);
                         }
                         location.reload();

                     } catch (error) {
                         console.error("Error al eliminar ítem:", error);
                         if(modalEliminarError) {
                             modalEliminarError.textContent = `Error: ${htmlspecialchars(error.message)}`;
                             modalEliminarError.style.display = 'block';
                         }
                         modalEliminarConfirmBtn.disabled = false;
                     }
                 });
             }


            // --- Cerrar Modales con Tecla Escape ---
            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape') {
                    const activeModal = document.querySelector('.modal-overlay.active');
                    if (activeModal) {
                        switch (activeModal.id) {
                            case 'modal-agregar-item': cerrarYResetearModalAgregar(); break;
                            case 'modal-ver-item': cerrarYResetearModalVer(); break;
                            case 'modal-editar-item': cerrarYResetearModalEditar(); break;
                            case 'modal-eliminar-item': cerrarYResetearModalEliminar(); break;
                        }
                    }
                }
            });

        }); // Fin de DOMContentLoaded
    </script>

</body>
</html>
