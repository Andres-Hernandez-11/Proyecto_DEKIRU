<?php
session_start();
require_once 'conexion.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Limpiar datos
    $correo = trim($_POST['correo'] ?? '');
    $password = $_POST['contrasena'] ?? '';

    $_SESSION['formulario'] = 'login'; // Para que regrese al formulario correcto

    if (empty($correo) || empty($password)) {
        $_SESSION['mensaje'] = "Por favor completa todos los campos.";
        $_SESSION['tipo_mensaje'] = "error";
        header("Location: ../VISTA/Login_UI.php");
        exit();
    }

    // Consultar usuario por correo
    $sql = "SELECT id_usuario, contrasena, nombre FROM USUARIO WHERE correo = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $correo);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows === 1) {
        $usuario = $result->fetch_assoc();

        // Verificar la contraseña
        if (password_verify($password, $usuario['contrasena'])) {

            // Redirigir al dashboard o página principal
            header("Location: ../VISTA/FormTicketes.html");
            exit();
            
        } else {
            $_SESSION['mensaje'] = "Correo o contraseña incorrectos.";
            $_SESSION['tipo_mensaje'] = "error";
            header("Location: ../VISTA/Login_UI.php");
            exit();
        }
    } else {
        $_SESSION['mensaje'] = "Correo o contraseña incorrectos.";
        $_SESSION['tipo_mensaje'] = "error";
        header("Location: ../VISTA/Login_UI.php");
        exit();
    }

    $stmt->close();
    $conn->close();
} else {
    // Si se accede directamente al archivo sin POST
    $_SESSION['mensaje'] = "Acceso no autorizado.";
    $_SESSION['tipo_mensaje'] = "error";
    header("Location: ../VISTA/Login_UI.php");
    exit();
}
?>
