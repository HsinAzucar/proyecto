<?php
include("conexion.php");
session_start();

// Verificar que sea administrador
if(!isset($_SESSION['id_usuario']) || $_SESSION['id_rol'] != 1){
    header("Location: ../index.php");
    exit();
}

if($_SERVER["REQUEST_METHOD"] == "POST"){
    $nombre = trim($_POST['nombre']);
    $apellido = trim($_POST['apellido']);
    $correo = trim($_POST['correo']);
    $contrasena = trim($_POST['contrasena']);
    $id_grupo = $_POST['id_grupo'];

    if(!empty($nombre) && !empty($apellido) && !empty($correo) && !empty($contrasena)){
        // Encriptar contraseña
        $hash = password_hash($contrasena, PASSWORD_DEFAULT);

        // Insertar alumno en usuarios
        $stmt = $conexion->prepare("INSERT INTO usuarios (nombre, apellido, correo, contrasena, activo, id_rol) VALUES (?, ?, ?, ?, 1, 3)");
        $stmt->bind_param("ssss", $nombre, $apellido, $correo, $hash);

        if($stmt->execute()){
            $id_alumno = $stmt->insert_id;

            // Si seleccionó grupo, inscribirlo
            if(!empty($id_grupo)){
                $stmt2 = $conexion->prepare("INSERT INTO inscripciones (id_alumno, id_grupo) VALUES (?, ?)");
                $stmt2->bind_param("ii", $id_alumno, $id_grupo);
                $stmt2->execute();
            }

            header("Location: ../administrador.php"); // Regresa al panel
            exit();
        } else {
            echo "Error al guardar el alumno.";
        }
    } else {
        echo "Todos los campos son obligatorios.";
    }
}
?>
