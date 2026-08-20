<?php
include("conexion.php");
$id_materia = $_GET['id_materia'];

$stmt = $conexion->prepare("SELECT id_tarea, titulo, descripcion, fecha_entrega FROM tareas WHERE id_materia = ?");
$stmt->bind_param("i", $id_materia);
$stmt->execute();
$result = $stmt->get_result();

while($row = $result->fetch_assoc()){
    echo "<p>📄 <strong>{$row['titulo']}</strong> - {$row['descripcion']} (Entrega: {$row['fecha_entrega']})
            <button onclick=\"verEntregas({$row['id_tarea']})\">Ver Entregas</button>
          </p>";
}
?>
