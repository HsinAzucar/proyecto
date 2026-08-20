<?php
session_start();
include("conexion.php");

$id_alumno = $_SESSION['id_usuario'];
$id_tarea = $_POST['id_tarea'];

// Validar que se haya subido un archivo
if(!isset($_FILES['archivo']) || $_FILES['archivo']['error'] != 0){
    die("Error al subir archivo.");
}

// Configuración de tipos permitidos
$permitidos = ['pdf','doc','docx','ppt','pptx','txt','jpg','jpeg','png'];
$archivo = $_FILES['archivo']['name'];
$tmp = $_FILES['archivo']['tmp_name'];
$ext = strtolower(pathinfo($archivo, PATHINFO_EXTENSION));

if(!in_array($ext, $permitidos)){
    die("Tipo de archivo no permitido. Solo se aceptan: ".implode(", ", $permitidos));
}

// Validar tamaño máximo (ejemplo: 10 MB)
$maxSize = 10 * 1024 * 1024; 
if($_FILES['archivo']['size'] > $maxSize){
    die("El archivo excede el tamaño máximo permitido (10 MB).");
}

// Crear carpeta si no existe
$destino = "../uploads/";
if(!is_dir($destino)){
    mkdir($destino, 0777, true);
}

// Nombre único para evitar conflictos
$nombreFinal = uniqid()."_".$archivo;
$ruta = $destino.$nombreFinal;

// Mover archivo
if(move_uploaded_file($tmp, $ruta)){
    // Guardar en la base de datos
    $stmt = $conexion->prepare("INSERT INTO entregas (id_tarea, id_alumno, archivo, fecha_entrega) VALUES (?, ?, ?, NOW())");
    $stmt->bind_param("iis", $id_tarea, $id_alumno, $nombreFinal);

    if($stmt->execute()){
        header("Location: ../materia.php?id=".$_POST['id_tarea']);
        exit();
    } else {
        die("Error al guardar entrega en la base de datos.");
    }
} else {
    die("Error al mover archivo al servidor.");
}
?>
