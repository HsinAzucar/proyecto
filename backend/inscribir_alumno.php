<?php

include("conexion.php");


$id_alumno = $_POST['id_alumno'];
$id_grupo = $_POST['id_grupo'];


// Verificar si ya está inscrito

$sql_verificar = "
SELECT * FROM inscripciones
WHERE id_alumno = ?
";


$stmt = $conexion->prepare($sql_verificar);

$stmt->bind_param("i",$id_alumno);

$stmt->execute();

$resultado = $stmt->get_result();


if($resultado->num_rows > 0){


    // Actualizar grupo

    $sql = "
    UPDATE inscripciones
    SET id_grupo = ?
    WHERE id_alumno = ?
    ";


    $stmt = $conexion->prepare($sql);

    $stmt->bind_param(
        "ii",
        $id_grupo,
        $id_alumno
    );


}else{


    // Crear inscripción nueva

    $sql = "
    INSERT INTO inscripciones
    (id_alumno,id_grupo)
    VALUES (?,?)
    ";


    $stmt = $conexion->prepare($sql);

    $stmt->bind_param(
        "ii",
        $id_alumno,
        $id_grupo
    );

}


$stmt->execute();


header("Location: ../administrador.php");

?>