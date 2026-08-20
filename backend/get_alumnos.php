<?php
include("conexion.php");
$id_asignacion = $_GET['id_asignacion'];

// Obtener grupo y materia de la asignación
$stmt = $conexion->prepare("
    SELECT id_grupo, id_materia 
    FROM asignaciones 
    WHERE id_asignacion = ?
");
$stmt->bind_param("i", $id_asignacion);
$stmt->execute();
$res = $stmt->get_result()->fetch_assoc();
$id_grupo = $res['id_grupo'];
$id_materia = $res['id_materia'];

// Traer alumnos inscritos en ese grupo
$stmt2 = $conexion->prepare("
    SELECT u.id_usuario, u.nombre, u.apellido
    FROM inscripciones i
    INNER JOIN usuarios u ON i.id_alumno = u.id_usuario
    WHERE i.id_grupo = ?
");
$stmt2->bind_param("i", $id_grupo);
$stmt2->execute();
$result = $stmt2->get_result();

while($row = $result->fetch_assoc()){
    $id_alumno = $row['id_usuario'];

    // Total de tareas asignadas en la materia
    $stmt3 = $conexion->prepare("SELECT COUNT(*) AS total FROM tareas WHERE id_materia = ?");
    $stmt3->bind_param("i", $id_materia);
    $stmt3->execute();
    $total_tareas = $stmt3->get_result()->fetch_assoc()['total'];

    // Suma de calificaciones de entregas del alumno en esa materia
    $stmt4 = $conexion->prepare("
        SELECT SUM(COALESCE(e.calificacion,0)) AS suma
        FROM tareas t
        LEFT JOIN entregas e ON t.id_tarea = e.id_tarea AND e.id_alumno = ?
        WHERE t.id_materia = ?
    ");
    $stmt4->bind_param("ii", $id_alumno, $id_materia);
    $stmt4->execute();
    $suma = $stmt4->get_result()->fetch_assoc()['suma'];

    // Promedio = suma de calificaciones / total de tareas asignadas
    $promedio = $total_tareas > 0 ? $suma / $total_tareas : null;

    echo "<tr>
            <td>{$row['nombre']} {$row['apellido']}</td>
            <td>".($promedio !== null ? number_format($promedio,2) : "Sin tareas asignadas")."</td>
          </tr>";
}
?>
