<?php
require_once '../../MODELO/conexion.php';

class EmpleadoModelo {

    public static function obtenerTodos() {
        global $conn;
        $sql = "SELECT * FROM EMPLEADO";
        $resultado = $conn->query($sql);
        return $resultado;
    }

    public static function guardar($nombre, $apellido, $cargo, $salario, $fecha, $estado) {
        global $conn;
        $stmt = $conn->prepare("INSERT INTO EMPLEADO (nombre, apellido, cargo, salario, fecha_contratacion, estado) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssdss", $nombre, $apellido, $cargo, $salario, $fecha, $estado);
        return $stmt->execute();
    }

    public static function obtenerPorId($id) {
        global $conn;
        $stmt = $conn->prepare("SELECT * FROM EMPLEADO WHERE id_empleado = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public static function actualizar($id, $nombre, $apellido, $cargo, $salario, $fecha, $estado) {
        global $conn;
        $stmt = $conn->prepare("UPDATE EMPLEADO SET nombre=?, apellido=?, cargo=?, salario=?, fecha_contratacion=?, estado=? WHERE id_empleado=?");
        $stmt->bind_param("sssdssi", $nombre, $apellido, $cargo, $salario, $fecha, $estado, $id);
        return $stmt->execute();
    }

    public static function eliminar($id) {
        global $conn;
        $stmt = $conn->prepare("DELETE FROM EMPLEADO WHERE id_empleado = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    public static function buscar($texto) {
        $conexion = Conexion::obtenerConexion();
        $stmt = $conexion->prepare("SELECT * FROM empleados WHERE nombre LIKE ? OR id_empleado = ?");
        $param = "%" . $texto . "%";
        $stmt->bind_param("ss", $param, $texto);
        $stmt->execute();
        return $stmt->get_result();
    }
    
}
?>
