<?php
include("conexion.php");

$id_materia = $_GET['id_materia'];

// Obtener todas las entregas de esa materia
$sql = "
    SELECT e.id_entrega, e.calificacion, e.observaciones,
           t.titulo AS tarea, 
           u.nombre AS alumno, u.apellido
    FROM entregas e
    INNER JOIN tareas t ON e.id_tarea = t.id_tarea
    INNER JOIN usuarios u ON e.id_alumno = u.id_usuario
    WHERE t.id_materia = ?
";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $id_materia);
$stmt->execute();
$result = $stmt->get_result();

while($row = $result->fetch_assoc()){
    echo "<tr>
            <td>{$row['tarea']}</td>
            <td>{$row['alumno']} {$row['apellido']}</td>
            <td>".($row['calificacion'] !== null ? $row['calificacion'] : "Sin calificar")."</td>
            <td>
                <button onclick=\"editarCalificacionEntrega({$row['id_entrega']})\">Editar</button>
            </td>
          </tr>";
}
?>
