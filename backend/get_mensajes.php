<?php
session_start();
include("conexion.php");

$id_usuario = $_SESSION['id_usuario'];
$id_chat = $_GET['id_chat']; // el otro usuario

$stmt = $conexion->prepare("
    SELECT id_mensaje, id_remitente, id_destinatario,
           COALESCE(mensaje, '') AS mensaje,
           COALESCE(fecha, '') AS fecha
    FROM mensajes
    WHERE (id_remitente = ? AND id_destinatario = ?)
       OR (id_remitente = ? AND id_destinatario = ?)
    ORDER BY fecha ASC
");
$stmt->bind_param("iiii", $id_usuario, $id_chat, $id_chat, $id_usuario);
$stmt->execute();
$result = $stmt->get_result();

$mensajes = [];
while($row = $result->fetch_assoc()){
    $mensajes[] = $row;
}

echo json_encode($mensajes);
?>
