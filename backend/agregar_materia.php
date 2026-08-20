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
    $descripcion = trim($_POST['descripcion']);

    if(!empty($nombre)){
        $stmt = $conexion->prepare("INSERT INTO materias (nombre, descripcion) VALUES (?, ?)");
        $stmt->bind_param("ss", $nombre, $descripcion);
        if($stmt->execute()){
            header("Location: ../administrador.php"); // Regresa al panel
            exit();
        } else {
            echo "Error al guardar la materia.";
        }
    } else {
        echo "El nombre de la materia es obligatorio.";
    }
}
?>
