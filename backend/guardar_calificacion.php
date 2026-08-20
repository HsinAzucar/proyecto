<?php
include("conexion.php");

if(isset($_POST['id_calificacion']) && isset($_POST['calificacion'])){
    $id_calificacion = intval($_POST['id_calificacion']);
    $nota = floatval($_POST['calificacion']);

    $stmt = $conexion->prepare("UPDATE calificaciones SET calificacion = ? WHERE id_calificacion = ?");
    $stmt->bind_param("di", $nota, $id_calificacion);

    if($stmt->execute()){
        echo "ok";
    } else {
        echo "error";
    }
} else {
    echo "faltan_datos";
}
?>
