<?php
include("conexion.php");

$id_docente = $_GET['id_docente'];
$id_materia = $_GET['id_materia'];
$titulo = $_GET['titulo'];
$descripcion = $_GET['descripcion'];
$fecha_entrega = $_GET['fecha_entrega'];

$stmt = $conexion->prepare("INSERT INTO tareas (titulo, descripcion, fecha_publicacion, fecha_entrega, id_docente, id_materia) VALUES (?, ?, NOW(), ?, ?, ?)");
$stmt->bind_param("sssii", $titulo, $descripcion, $fecha_entrega, $id_docente, $id_materia);

if($stmt->execute()){
    echo "ok";
} else {
    echo "error";
}
?>
