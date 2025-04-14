<?php
// Iniciar la sesión para poder manipularla
session_start();

// 1. Desvincular (unset) todas las variables de sesión.
$_SESSION = array();

// 2. Si se desea destruir la sesión completamente, borra también la cookie de sesión.
// Nota: ¡Esto destruirá la sesión, y no solo los datos de la sesión!
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 3. Finalmente, destruir la sesión.
session_destroy();

// 4. Redirigir al usuario a la página de inicio de sesión
// Asegúrate de que la ruta sea correcta según la ubicación de este archivo.
header("Location: ../VISTA/Login_UI.php");
exit(); // Es importante llamar a exit() después de header() para asegurar que el script se detiene.
?>
