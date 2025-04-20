<?php
session_start();

$current_page = basename($_SERVER['PHP_SELF']);

// **TODO:** Conexión a la base de datos
// ... (Código de conexión a la base de datos)

// Variables para mensajes de error/éxito
$errores = [];
$mensaje_exito = '';

// Procesamiento del formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    //  Validación (¡IMPORTANTE!  Sanitiza y valida los datos antes de guardarlos en la base de datos)
    if (empty($_POST["nombre"])) {
        $errores[] = "El nombre es obligatorio.";
    }
    if (empty($_POST["tipo_cliente"])) {
        $errores[] = "El tipo de cliente es obligatorio.";
    }
    //  Validar el correo electrónico si es necesario (puedes usar filter_var)

    // Si no hay errores, guardar en la base de datos
    if (empty($errores)) {
        $nombre = mysqli_real_escape_string($conn, $_POST["nombre"]);  //  Sanitizar
        $tipo_cliente = mysqli_real_escape_string($conn, $_POST["tipo_cliente"]);
        $contacto = mysqli_real_escape_string($conn, $_POST["contacto"]);
        $email = mysqli_real_escape_string($conn, $_POST["email"]);

        $sql = "INSERT INTO clientes (nombre, tipo_cliente, contacto, email) VALUES ('$nombre', '$tipo_cliente', '$contacto', '$email')";  //  ¡CUIDADO!  Usar sentencias preparadas es más seguro

        if ($conn->query($sql) === TRUE) {
            $mensaje_exito = "Cliente registrado con éxito.";
        } else {
            $errores[] = "Error al registrar el cliente: " . $conn->error;
        }
    }
}

// ... (El resto del código HTML)

?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Cliente - Rápidos del Altiplano</title>
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

                <div class="inventory-header">
                    <h1 class="inventory-title">Registrar Cliente</h1>
                </div>

                <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST"
                      class="form-container">
                    <div class="form-group">
                        <label for="nombre">Nombre:</label>
                        <input type="text" id="nombre" name="nombre" required>
                    </div>

                    <div class="form-group">
                        <label for="tipo_cliente">Tipo de Cliente:</label>
                        <select id="tipo_cliente" name="tipo_cliente" required>
                            <option value="Tipo">Tipo</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="contacto">Contacto:</label>
                        <input type="text" id="contacto" name="contacto">
                    </div>

                    <div class="form-group">
                        <label for="email">Correo Electrónico:</label>
                        <input type="email" id="email" name="email">
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">Registrar</button>
                        <a href="Clientes.php" class="btn btn-secondary">Cancelar y volver</a>
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
        </main>
    </div>
</div>

<script>
    //  Aquí va el JavaScript para la lógica de la página (validación, etc.)
</script>

</body>

</html>