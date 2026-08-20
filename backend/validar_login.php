<?php

session_start();

include("conexion.php");

$correo = $_POST['correo'];
$contrasena = $_POST['contrasena'];

$sql = "SELECT *
        FROM usuarios
        WHERE correo = ?
        AND contrasena = ?
        AND activo = 1";

$stmt = $conexion->prepare($sql);

$stmt->bind_param("ss",$correo,$contrasena);

$stmt->execute();

$resultado = $stmt->get_result();

if($resultado->num_rows > 0){

    $usuario = $resultado->fetch_assoc();

    $_SESSION['id_usuario'] = $usuario['id_usuario'];
    $_SESSION['nombre'] = $usuario['nombre'];
    $_SESSION['id_rol'] = $usuario['id_rol'];

    switch($usuario['id_rol']){

        case 1:
            header("Location: ../administrador.php");
            break;

        case 2:
            header("Location: ../profesor.php");
            break;

        case 3:
            header("Location: ../alumno.php");
            break;
    }

}else{

    echo "Correo o contraseña incorrectos.";

}

?>