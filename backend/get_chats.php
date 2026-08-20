<?php
session_start();
include("conexion.php");

$id_usuario = $_SESSION['id_usuario'];
$id_rol = $_SESSION['id_rol'];

if($id_rol == 2){ 
    // PROFESOR: listar alumnos de sus grupos
    $stmt = $conexion->prepare("
        SELECT DISTINCT u.id_usuario, u.nombre, u.id_rol
        FROM asignaciones a
        INNER JOIN inscripciones i ON a.id_grupo = i.id_grupo
        INNER JOIN usuarios u ON i.id_alumno = u.id_usuario
        WHERE a.id_docente = ?
    ");
    $stmt->bind_param("i", $id_usuario);
}
elseif($id_rol == 3){ 
    // ALUMNO: listar profesor de su grupo
    $stmt = $conexion->prepare("
        SELECT DISTINCT u.id_usuario, u.nombre, u.id_rol
        FROM inscripciones i
        INNER JOIN asignaciones a ON i.id_grupo = a.id_grupo
        INNER JOIN usuarios u ON a.id_docente = u.id_usuario
        WHERE i.id_alumno = ?
    ");
    $stmt->bind_param("i", $id_usuario);
}
else{
    echo json_encode([]);
    exit();
}

$stmt->execute();
$result = $stmt->get_result();

$chats = [];
while($row = $result->fetch_assoc()){
    $chats[] = $row;
}

echo json_encode($chats);
?>
