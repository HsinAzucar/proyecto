<?php
include("conexion.php");
$id_materia = $_GET['id_materia'];

$stmt = $conexion->prepare("
    SELECT e.id_entrega, u.nombre, e.archivo, e.fecha_entrega, e.calificacion, e.observaciones, t.titulo
    FROM entregas e
    INNER JOIN usuarios u ON e.id_alumno = u.id_usuario
    INNER JOIN tareas t ON e.id_tarea = t.id_tarea
    WHERE t.id_materia = ? AND e.calificacion IS NULL
");
$stmt->bind_param("i", $id_materia);
$stmt->execute();
$result = $stmt->get_result();

if($result->num_rows > 0){
    while($row = $result->fetch_assoc()){
        echo "<p>📂 <strong>{$row['titulo']}</strong> - Alumno: {$row['nombre']} 
                <a href='uploads/{$row['archivo']}' target='_blank'>Descargar</a> 
                | Fecha: {$row['fecha_entrega']} 
                <button onclick=\"calificarEntrega({$row['id_entrega']})\">Calificar</button>
              </p>";
    }
} else {
    echo "<p>No hay entregas pendientes de calificación ✅</p>";
}
?>
