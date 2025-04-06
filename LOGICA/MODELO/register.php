<?php
session_start();
require_once 'conexion.php';

// Función para limpiar entradas
function limpiar($dato) {
    return htmlspecialchars(trim($dato));
}

// Recuperar y limpiar datos
$nombre = limpiar($_POST['nombre'] ?? '');
$apellido = limpiar($_POST['apellido'] ?? '');
$correo = limpiar($_POST['correo'] ?? '');
$usuario = limpiar($_POST['usuario'] ?? '');
$password = $_POST['password'] ?? '';
$confirmPassword = $_POST['confirm_password'] ?? '';

// Guardar los datos para mostrarlos si hay error
$_SESSION['datos_registro'] = [
    'nombre' => $nombre,
    'apellido' => $apellido,
    'correo' => $correo,
    'usuario' => $usuario
];
$_SESSION['formulario'] = 'register'; // Para que se quede en el formulario de registro

// Validaciones
if (empty($nombre) || empty($apellido) || empty($correo) || empty($usuario) || empty($password) || empty($confirmPassword)) {
    $_SESSION['mensaje'] = "Todos los campos son obligatorios.";
    $_SESSION['tipo_mensaje'] = "error"; // ✅ Tipo de mensaje: error
    header("Location: ../VISTA/Login_UI.php");
    exit();
}


if ($password !== $confirmPassword) {
    $_SESSION['mensaje'] = "Las contraseñas no coinciden.";
    $_SESSION['tipo_mensaje'] = "error"; // ✅ Tipo de mensaje: error
    header("Location: ../VISTA/Login_UI.php");
    exit();
}

// Validar seguridad de la contraseña
if (!preg_match('/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[@$!%*?&.,#\-_=+])[A-Za-z\d@$!%*?&.,#\-_=+]{8,}$/', $password)) {
    $_SESSION['mensaje'] = "La contraseña debe tener al menos 8 caracteres, incluyendo una mayúscula, una minúscula, un número y un carácter especial.";
    $_SESSION['tipo_mensaje'] = "error"; // ✅ Tipo de mensaje: error
    header("Location: ../VISTA/Login_UI.php");
    exit();
}

// Verificar si ya existe el usuario o correo
$sql = "SELECT * FROM USUARIO WHERE correo = ? OR usuario = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $correo, $usuario);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    $_SESSION['mensaje'] = "El correo o nombre de usuario ya está registrado.";
    $_SESSION['tipo_mensaje'] = "error"; // ✅ Tipo de mensaje: error
    $stmt->close();
    header("Location: ../VISTA/Login_UI.php");
    exit();
}
$stmt->close();

// Insertar el nuevo usuario
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

$sql = "INSERT INTO usuario (nombre, apellido, correo, usuario, contrasena) VALUES (?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sssss", $nombre, $apellido, $correo, $usuario, $hashedPassword);

if ($stmt->execute()) {
    unset($_SESSION['datos_registro']);
    $_SESSION['mensaje'] = "Usuario registrado con éxito. Ahora puedes iniciar sesión.";
    $_SESSION['tipo_mensaje'] = "success";
    $_SESSION['formulario'] = 'login';
    header("Location: ../VISTA/Login_UI.php");
    exit();
} else {
    $_SESSION['mensaje'] = "Error al registrar usuario. Intenta nuevamente.";
    $_SESSION['tipo_mensaje'] = "error";
    header("Location: ../VISTA/Login_UI.php");
    exit();
}

