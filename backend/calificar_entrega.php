<?php
include("conexion.php");

if(isset($_POST['id_entrega']) && isset($_POST['calificacion'])){
    $id_entrega = intval($_POST['id_entrega']);
    $nota = floatval($_POST['calificacion']);
    $obs = $_POST['observaciones'] ?? "";

    $stmt = $conexion->prepare("UPDATE entregas SET calificacion = ?, observaciones = ? WHERE id_entrega = ?");
    $stmt->bind_param("dsi", $nota, $obs, $id_entrega);

    if($stmt->execute()){
        echo "ok";
    } else {
        echo "error";
    }
} else {
    echo "faltan_datos";
}
?>
