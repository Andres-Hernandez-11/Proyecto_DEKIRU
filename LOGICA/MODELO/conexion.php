<?php
$servername = "localhost";
$username = "root";
$password = ""; // Cambia esto si tienes una contraseña
$dbname = "DekiruDb";

// Crea la conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Verifica la conexión
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Establece la zona horaria
date_default_timezone_set('America/Bogota'); // Ajusta esto a tu zona horaria

// Alternativa para establecer la zona horaria en MySQL
$conn->query("SET time_zone = '-05:00'"); // O 'SET time_zone = '+00:00''

?>