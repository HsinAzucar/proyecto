<?php
session_start();
include("conexion.php");

if(isset($_POST['destinatario']) && isset($_POST['mensaje'])){
    $id_remitente = $_SESSION['id_usuario'];
    $id_destinatario = intval($_POST['destinatario']);
    $mensaje = trim($_POST['mensaje']);

    if($mensaje !== ""){
        $stmt = $conexion->prepare("INSERT INTO mensajes (id_remitente, id_destinatario, mensaje) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $id_remitente, $id_destinatario, $mensaje);

        if($stmt->execute()){
            echo "ok";
        } else {
            echo "error";
        }
    } else {
        echo "mensaje_vacio";
    }
} else {
    echo "faltan_datos";
}
?>
