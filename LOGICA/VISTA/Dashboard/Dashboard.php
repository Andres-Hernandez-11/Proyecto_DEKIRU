<?php

session_start();

$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Rápidos del Altiplano (CSS Separado)</title>
    
    <link rel="stylesheet" href="..\Dashboard\EstilosDashboard.css" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
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
                    // Definir los enlaces y sus archivos correspondientes
                    $nav_links = [
                        'Dashboard.php' => ['icon' => '../../../IMAGENES/Dashboard.png', 'text' => 'Dashboard (Inicio)', 'alt' => 'Icono Dashboard'],
                        '../Clientes/Clientes.php' => ['icon' => '../../../IMAGENES/Clientes.png', 'text' => 'Clientes', 'alt' => 'Icono Clientes'], // Asume Clientes.php
                        '../Inventario/InventarioStart.php' => ['icon' => '../../../IMAGENES/Inventario.png', 'text' => 'Inventario', 'alt' => 'Icono Inventario'],
                        'Ventas.php' => ['icon' => '../../../IMAGENES/Ventas-Compras.png', 'text' => 'Ventas-Compras', 'alt' => 'Icono Ventas'], // Asume Ventas.php
                        '../RRHH/RRHH_UI.php' => ['icon' => '../../../IMAGENES/RH.png', 'text' => 'Recursos Humanos', 'alt' => 'Icono Recursos Humanos'], // Asume RRHH.php
                        'Contabilidad.php' => ['icon' => '../../../IMAGENES/Contabilidad.png', 'text' => 'Contabilidad', 'alt' => 'Icono Contabilidad'], // Asume Contabilidad.php
                        'Configuracion.php' => ['icon' => '../../../IMAGENES/Configuracion.png', 'text' => 'Configuración', 'alt' => 'Icono Configuración'] // Asume Configuracion.php
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
                        <h1 class="header-title">Panel de Control General</h1>
                        <div class="header-filter">
                            <select>
                                <option>Hoy</option>
                                <option>Últimos 7 días</option>
                                <option selected>Este Mes</option>
                                <option>Este Año</option>
                            </select>
                        </div>
                    </div>
                    <div class="header-right">
                        <span class="user-info">
                            Bienvenido, <?php echo htmlspecialchars($_SESSION['nombre_Invitado'] ?? 'Invitado'); ?>
                        </span>
                        <a href="../../MODELO/CerrarSesion.php" class="logout-button">
                            <span class="icon"></span>
                            Cerrar Sesión
                        </a>
                    </div>
                </div>
            </header>
            <main class="main-content">
                <div class="main-container">
                    <div class="widget-grid">

                        <div class="widget">
                            <h3 class="widget-title">Ventas (Este Mes)</h3>
                            <div class="kpi-grid">
                                <div class="kpi-card bg-blue">
                                    <div class="kpi-value">[Valor]</div>
                                    <div class="kpi-label"><span class="icon">[I]</span> Ingresos Totales</div>
                                </div>
                                <div class="kpi-card bg-green">
                                    <div class="kpi-value">[Número]</div>
                                    <div class="kpi-label"><span class="icon">[I]</span> Tiquetes Vendidos</div>
                                </div>
                            </div>
                            <div class="placeholder-chart">
                                [Gráfico de Ventas Placeholder]
                            </div>
                            <a href="#" class="widget-link">Ver Reporte de Ventas ></a>
                        </div>

                        <div class="widget">
                            <h3 class="widget-title">Inventario</h3>
                            <div class="kpi-grid">
                                <div class="kpi-card bg-red">
                                    <div class="kpi-value">[Número]</div>
                                    <div class="kpi-label"><span class="icon">[I]</span> Items Bajo Mínimo</div>
                                </div>
                                <div class="kpi-card bg-indigo">
                                    <div class="kpi-value">[Valor]</div>
                                    <div class="kpi-label"><span class="icon">[I]</span> Valor Total Inventario</div>
                                </div>
                            </div>
                            <div class="widget-list-container">
                                <p class="widget-list-title">Items Críticos:</p>
                                <ul class="widget-list disc">
                                    <li>[Nombre Item] ([Cantidad])</li>
                                </ul>
                            </div>
                            <a href="../Inventario/InventarioStart.php" class="widget-link">Gestionar Inventario ></a> </div>

                        <div class="widget">
                            <h3 class="widget-title">Clientes</h3>
                             <div class="kpi-grid">
                                <div class="kpi-card bg-yellow">
                                    <div class="kpi-value">[Número]</div>
                                    <div class="kpi-label"><span class="icon">[I]</span> Nuevos Clientes (Mes)</div>
                                </div>
                                <div class="kpi-card bg-purple">
                                    <div class="kpi-value">[Número]</div>
                                    <div class="kpi-label"><span class="icon">[I]</span> Total Clientes Activos</div>
                                </div>
                            </div>
                             <div class="placeholder-content">
                                </div>
                            <a href="#" class="widget-link">Gestionar Clientes ></a>
                        </div>

                        <div class="widget">
                            <h3 class="widget-title">Compras (Este Mes)</h3>
                             <div class="kpi-grid kpi-grid-single">
                                <div class="kpi-card bg-cyan">
                                    <div class="kpi-value">[Valor]</div>
                                    <div class="kpi-label"><span class="icon">[I]</span> Gasto Total</div>
                                </div>
                            </div>
                            <div class="widget-list-container">
                                <p class="widget-list-title">Últimas Compras:</p>
                                <ul class="widget-list">
                                     <li>[Proveedor] - [Valor]</li>
                                </ul>
                            </div>
                            <a href="#" class="widget-link">Gestionar Compras ></a>
                        </div>

                        <div class="widget">
                            <h3 class="widget-title">Recursos Humanos</h3>
                             <div class="kpi-grid">
                                <div class="kpi-card bg-pink">
                                    <div class="kpi-value">[Número]</div>
                                    <div class="kpi-label"><span class="icon">[I]</span> Empleados Activos</div>
                                </div>
                                <div class="kpi-card bg-gray">
                                     <div class="kpi-label" style="font-weight: 500;"><span class="icon">[I]</span> Próxima Nómina:</div>
                                     <div class="kpi-value" style="font-size: 1.25rem; font-weight:600;">[Fecha]</div>
                                </div>
                            </div>
                             <div class="placeholder-content">
                                </div>
                            <a href="../RRHH/RRHH_UI.php" class="widget-link">Gestionar RRHH ></a>
                        </div>

                        <div class="widget">
                            <h3 class="widget-title">Finanzas</h3>
                             <div class="kpi-grid kpi-grid-single">
                                <div class="kpi-card bg-lime">
                                    <div class="kpi-value">[Valor]</div>
                                    <div class="kpi-label"><span class="icon">[I]</span> Saldo en Bancos</div>
                                </div>
                            </div>
                            <div class="placeholder-chart">
                                [Gráfico Ingresos vs Gastos Placeholder]
                            </div>
                            <a href="#" class="widget-link">Ver Contabilidad ></a>
                        </div>

                    </div> </div> </main>
            </div>
        </div> </body>
</html>
