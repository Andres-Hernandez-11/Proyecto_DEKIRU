<?php
// Iniciar la sesión al principio para poder usar $_SESSION
session_start();
require_once 'conexion.php'; // Asegúrate que la ruta a tu archivo de conexión sea correcta

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Limpiar datos
    $correo = trim($_POST['correo'] ?? '');
    $password = $_POST['contrasena'] ?? '';

    $_SESSION['formulario'] = 'login'; // Para que regrese al formulario correcto en caso de error

    // Validación básica de campos vacíos
    if (empty($correo) || empty($password)) {
        $_SESSION['mensaje'] = "Por favor completa todos los campos.";
        $_SESSION['tipo_mensaje'] = "error";
        header("Location: ../VISTA/Login_UI.php"); // Redirigir a la página de login
        exit();
    }

    // Consultar usuario por correo en la base de datos
    // Asegúrate que tu tabla se llame USUARIO y las columnas id_usuario, contrasena, nombre, correo
    $sql = "SELECT id_usuario, contrasena, nombre FROM USUARIO WHERE correo = ?";
    $stmt = $conn->prepare($sql);

    // Verificar si la preparación de la consulta fue exitosa
    if ($stmt === false) {
        // Manejo de error - Podrías loggear el error $conn->error
        $_SESSION['mensaje'] = "Error interno del servidor al preparar la consulta.";
        $_SESSION['tipo_mensaje'] = "error";
        header("Location: ../VISTA/Login_UI.php");
        exit();
    }

    $stmt->bind_param("s", $correo);
    $stmt->execute();
    $result = $stmt->get_result();

    // Verificar si se encontró un usuario
    if ($result && $result->num_rows === 1) {
        $usuario = $result->fetch_assoc();

        // Verificar la contraseña usando password_verify
        if (password_verify($password, $usuario['contrasena'])) {

            // ¡Contraseña correcta! Iniciar sesión y guardar datos.

            // Regenerar ID de sesión por seguridad (previene fijación de sesión)
            session_regenerate_id(true);

            // === INICIO: Variables de sesión añadidas ===
            // Guardar datos importantes del usuario en la sesión
            $_SESSION['id_usuario'] = $usuario['id_usuario'];
            $_SESSION['nombre_usuario'] = $usuario['nombre'];
            // Puedes añadir más variables si las necesitas, por ejemplo, el rol:
            // $_SESSION['rol_usuario'] = $usuario['rol']; // Asumiendo que tienes una columna 'rol'
            // === FIN: Variables de sesión añadidas ===

            // Limpiar mensajes de error previos si existieran
            unset($_SESSION['mensaje']);
            unset($_SESSION['tipo_mensaje']);
            unset($_SESSION['formulario']);

            // Redirigir al dashboard o página principal
            header("Location: ../VISTA/Dashboard.php"); // Asegúrate que el dashboard sea .php
            exit; // Detiene el script para que veas la salida

        } else {
            // Contraseña incorrecta
            $_SESSION['mensaje'] = "Correo o contraseña incorrectos.";
            $_SESSION['tipo_mensaje'] = "error";
            header("Location: ../VISTA/Login_UI.php");
            exit();
        }
    } else {
        // Usuario no encontrado (correo incorrecto)
        $_SESSION['mensaje'] = "Correo o contraseña incorrectos.";
        $_SESSION['tipo_mensaje'] = "error";
        header("Location: ../VISTA/Login_UI.php");
        exit();
    }

    // Cerrar statement y conexión
    $stmt->close();
    $conn->close();

} else {
    // Si se intenta acceder al script directamente sin método POST
    $_SESSION['mensaje'] = "Acceso no autorizado.";
    $_SESSION['tipo_mensaje'] = "error";
    header("Location: ../VISTA/Login_UI.php");
    exit();
}
?>
