<?php
// --- Inicio Bloque PHP (Lógica - Placeholder) ---

// --- Variables definidas por la lógica (con valores por defecto) ---
$current_page = basename($_SERVER['PHP_SELF']); // Para resaltar sidebar
$_SESSION['nombre_usuario'] = $_SESSION['nombre_usuario'] ?? 'Usuario'; // Para el header



?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contabilidad: Resumen Financiero - Rápidos del Altiplano</title>

    <link rel="stylesheet" href="..\Contabilidad\EstilosContabilidad.css" /> <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

   
</head>

<body>
    <div class="dashboard-container">

        <aside class="sidebar">
             <div class="sidebar-header">
             <div class="sidebar-logo-container">
                    <img src="..\..\..\IMAGENES\LogoRapidosDelAltiplano.jpg" alt="Logo Rápidos del Altiplano" class="sidebar-logo" onerror="this.onerror=null; this.src='https://placehold.co/150x50/cccccc/333333?text=Logo+Placeholder';">
                 </div>
                <h2 class="sidebar-title">Menú Principal</h2>
            </div>
            <nav class="sidebar-nav">
                <?php
                $nav_links = [
                    '../Dashboard/Dashboard.php' => ['icon' => '../../../IMAGENES/Dashboard.png', 'text' => 'Dashboard (Inicio)', 'alt' => 'Icono Dashboard'],
                    '../Clientes/Clientes.php' => ['icon' => '../../../IMAGENES/Clientes.png', 'text' => 'Clientes', 'alt' => 'Icono Clientes'], // Asume Clientes.php
                    '../Inventario/InventarioStart.php' => ['icon' => '../../../IMAGENES/Inventario.png', 'text' => 'Inventario', 'alt' => 'Icono Inventario'],
                    '../Ventas/Ventas.php' => ['icon' => '../../../IMAGENES/Ventas-Compras.png', 'text' => 'Ventas', 'alt' => 'Icono Ventas'], // Asume Ventas.php
                    '../RRHH/RRHH_UI.php' => ['icon' => '../../../IMAGENES/RH.png', 'text' => 'Recursos Humanos', 'alt' => 'Icono Recursos Humanos'], // Asume RRHH.php
                    '../Contabilidad/Contabilidad.php' => ['icon' => '../../../IMAGENES/Contabilidad.png', 'text' => 'Contabilidad', 'alt' => 'Icono Contabilidad'], // Asume Contabilidad.php
                    //'Configuracion.php' => ['icon' => '../../../IMAGENES/Configuracion.png', 'text' => 'Configuración', 'alt' => 'Icono Configuración'] // Asume Configuracion.php
                ];
                ?>
                <?php foreach ($nav_links as $file => $link): ?>
                    <a href="<?php echo $file; ?>" class="sidebar-link<?php echo ($current_page == $file) ? ' active' : ''; ?>">
                        <img src="<?php echo htmlspecialchars($link['icon']); ?>" alt="<?php echo htmlspecialchars($link['alt']); ?>" class="sidebar-icon" onerror="this.style.display='none'; this.parentElement.insertAdjacentText('afterbegin', '[i] ');">
                        <?php echo htmlspecialchars($link['text']); ?>
                    </a>
                <?php endforeach; ?>
            </nav>
        </aside>

        <div class="main-content-area">
            <header class="header">
                 <div class="header-container">
                    <div class="header-left">
                        <h1 class="header-title">Contabilidad</h1>
                    </div>
                    <div class="header-right">
                        <span class="user-info">
                            Bienvenido, <?php echo htmlspecialchars($_SESSION['nombre_usuario']); ?>
                        </span>
                        <a href="../CONTROLADOR/CerrarSesion.php" class="logout-button btn btn-secondary">
                            <span class="icon">[X]</span> Cerrar Sesión
                        </a>
                    </div>
                </div>
            </header>

            <main class="main-content">
                <div class="main-container">

                    <h1 class="page-title">Resumen Financiero</h1>

                     <?php if (isset($error_db)): ?>
                        <div class="feedback-message error">
                            <?php echo htmlspecialchars($error_db); ?>
                        </div>
                    <?php endif; ?>

                    <form method="GET" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                        <div class="filter-section">
                            <label for="fecha_inicio">Periodo:</label>
                            <div class="date-range">
                                <input type="date" id="fecha_inicio" name="fecha_inicio" value="<?php echo htmlspecialchars($fecha_inicio); ?>" required>
                                <span>hasta</span>
                                <input type="date" id="fecha_fin" name="fecha_fin" value="<?php echo htmlspecialchars($fecha_fin); ?>" required>
                            </div>
                            <button type="submit" class="btn btn-secondary btn-sm">Aplicar</button>
                            <div class="period-buttons" style="margin-left: auto;">
                                <a href="?periodo=hoy" class="btn btn-secondary btn-sm">Hoy</a>
                                <a href="?periodo=7dias" class="btn btn-secondary btn-sm">7 días</a>
                                <a href="?periodo=mes" class="btn btn-secondary btn-sm">Mes</a>
                                <a href="?periodo=ano" class="btn btn-secondary btn-sm">Año</a>
                            </div>
                        </div>
                    </form>

                    <div class="kpi-grid">
                        <div class="kpi-card">
                            <span class="icon income">↑</span>
                            <div class="value positive"><?php echo '$' . number_format($total_ingresos, 0, ',', '.'); ?></div>
                            <div class="label">Total Ingresos (Periodo)</div>
                        </div>
                        <div class="kpi-card">
                            <span class="icon expense">↓</span>
                            <div class="value negative"><?php echo '$' . number_format($total_egresos, 0, ',', '.'); ?></div>
                            <div class="label">Total Egresos (Periodo)</div>
                        </div>
                        <div class="kpi-card">
                            <span class="icon balance">=</span>
                            <div class="value <?php echo ($saldo_neto >= 0) ? 'positive' : 'negative'; ?>">
                                <?php echo '$' . number_format($saldo_neto, 0, ',', '.'); ?>
                            </div>
                            <div class="label">Saldo Neto (Periodo)</div>
                        </div>
                    </div>

                    <div class="charts-section">
                        <div class="chart-container">
                            <h3 class="chart-title">Egresos por Categoría (Periodo)</h3>
                            <div class="chart-placeholder">[Placeholder para Gráfico de Torta]</div>
                            </div>
                        <div class="chart-container">
                            <h3 class="chart-title">Ingresos vs. Egresos (Periodo)</h3>
                            <div class="chart-placeholder">[Placeholder para Gráfico de Línea/Barras]</div>
                            </div>
                    </div>

                    <div class="recent-movements">
                        <div class="recent-movements-header">
                            <h3 class="recent-movements-title">Movimientos Recientes</h3>
                            <a href="formulario_movimiento.php" class="btn btn-primary btn-sm">
                                <span class="icon">+</span> Registrar Movimiento
                            </a>
                        </div>
                        <table>
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Tipo</th>
                                    <th>Cuenta/Categoría</th>
                                    <th>Descripción</th>
                                    <th>Monto</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($movimientos_recientes)): ?>
                                    <tr><td colspan="5" style="text-align: center; padding: 1rem; color: #6b7280;">No hay movimientos recientes.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($movimientos_recientes as $mov): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($mov['fecha']))); ?></td>
                                            <td><?php echo htmlspecialchars($mov['tipo']); ?></td>
                                            <td><?php echo htmlspecialchars($mov['categoria'] ?? '-'); // Usamos 'categoria' que creamos en la lógica ?></td>
                                            <td><?php echo htmlspecialchars($mov['descripcion']); ?></td>
                                            <td class="<?php echo ($mov['tipo'] == 'Ingreso') ? 'amount-income' : 'amount-expense'; ?>">
                                                <?php echo ($mov['tipo'] == 'Ingreso' ? '+' : '-') . '$' . number_format($mov['monto'], 0, ',', '.'); ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                         <div style="text-align: right; margin-top: 1rem;">
                             <a href="ContabilidadDetalle.php" class="btn btn-secondary btn-sm">Ver Todos los Movimientos ></a>
                         </div>
                    </div>

                </div> </main>
        </div> </div> </body>
</html>
