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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
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

    // Si no hay errores, guardar
    if (empty($errores)) {
        $nombre = mysqli_real_escape_string($conn, $_POST["nombre"]);
        $apellido = mysqli_real_escape_string($conn, $_POST["apellido"]);
        $documento = mysqli_real_escape_string($conn, $_POST["documento"]);
        $telefono = mysqli_real_escape_string($conn, $_POST["telefono"]);
        $email = mysqli_real_escape_string($conn, $_POST["email"]);

        $sql = "INSERT INTO cliente (nombre, apellido, documento, telefono, email)
                VALUES ('$nombre', '$apellido', '$documento', '$telefono', '$email')";

        if ($conn->query($sql) === TRUE) {
            $mensaje_exito = "Cliente registrado con éxito.";
        } else {
            $errores[] = "Error al registrar cliente: " . $conn->error;
        }
    }
}
?>
<?php if ($mensaje_exito): ?>
    <div id="popup-exito" class="popup-overlay">
        <div class="popup-content">
            <h2>¡Cliente registrado!</h2>
            <p><?php echo htmlspecialchars($mensaje_exito); ?></p>
            <button onclick="cerrarPopup()">Cerrar</button>
        </div>
    </div>
    <script>
        function cerrarPopup() {
            const popup = document.getElementById("popup-exito");
            popup.style.display = "none";
        }
        // Mostrar el popup automáticamente al cargar la página si $mensaje_exito tiene valor
        window.onload = function() {
            const popup = document.getElementById("popup-exito");
            if (popup) {
                popup.style.display = "flex";
            }
        };
    </script>
<?php endif; ?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Cliente - Rápidos del Altiplano</title>
    <link rel="stylesheet" href="../Inventario/EstilosInventario.css" />
    <style>
        .form-group {
            margin-bottom: 15px;
        }
        .popup-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            display: none; /* Ocultar por defecto */
            align-items: center;
            justify-content: center;
            z-index: 999;
        }

        .popup-content {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
            text-align: center;
            max-width: 400px;
            width: 90%;
        }

        .popup-content button {
            margin-top: 20px;
            padding: 10px 20px;
            border: none;
            background: #007bff;
            color: white;
            border-radius: 5px;
            cursor: pointer;
        }

        .popup-content button:hover {
            background-color: #0056b3;
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
                     onerror="this.onerror=null; this.src='https://placehold.co/150x50/cccccc/333333?text=Logo'; this.alt='Logo Placeholder';">
            </div>
            <h2 class="sidebar-title">Menú Principal</h2>
        </div>
        <nav class="sidebar-nav">
            <?php
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
                    <a href="../../MODELO/CerrarSesion.php" class="logout-button btn">
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
                        <label for="apellido">Apellido:</label>
                        <input type="text" id="apellido" name="apellido" required>
                    </div>

                    <div class="form-group">
                        <label for="documento">Documento:</label>
                        <input type="text" id="documento" name="documento" required>
                    </div>

                    <div class="form-group">
                        <label for="telefono">Teléfono:</label>
                        <input type="text" id="telefono" name="telefono">
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
    function cerrarPopup() {
        const popup = document.getElementById("popup-exito");
        popup.style.display = "none";
    }
</script>
</body>
</html>