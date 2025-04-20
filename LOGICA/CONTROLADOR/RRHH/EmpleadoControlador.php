<?php
require_once '../../MODELO/RRHH/EmpleadoModelo.php';

if (isset($_GET['accion'])) {
    $accion = $_GET['accion'];

    switch ($accion) {
        case 'crear':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                EmpleadoModelo::guardar($_POST['nombre'], $_POST['apellido'], $_POST['cargo'], $_POST['salario'], $_POST['fecha'], $_POST['estado']);
                header("Location: ../../VISTA/RRHH/RRHH_UI.php");
            }
            break;

        case 'editar':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                EmpleadoModelo::actualizar($_POST['id'], $_POST['nombre'], $_POST['apellido'], $_POST['cargo'], $_POST['salario'], $_POST['fecha'], $_POST['estado']);
                header("Location: ../../VISTA/RRHH/RRHH_UI.php");
            }
            break;

        case 'eliminar':
            if (isset($_GET['id'])) {
                EmpleadoModelo::eliminar($_GET['id']);
                header("Location: ../../VISTA/RRHH/RRHH_UI.php");
            }
            break;
    }
}
?>
