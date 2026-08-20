<?php
session_start();
include("backend/conexion.php");

// Verificar sesión
if(!isset($_SESSION['id_usuario'])){
    header("Location: ../index.php");
    exit();
}

// Verificar que sea alumno (id_rol = 3)
if($_SESSION['id_rol'] != 3){
    header("Location: ../index.php");
    exit();
}

// Consulta de materias
$stmt = $conexion->prepare("
    SELECT m.id_materia, m.nombre
    FROM inscripciones i
    INNER JOIN asignaciones a ON i.id_grupo = a.id_grupo
    INNER JOIN materias m ON a.id_materia = m.id_materia
    WHERE i.id_alumno = ?
    ORDER BY m.nombre
");
$stmt->bind_param("i", $_SESSION['id_usuario']);
$stmt->execute();
$result = $stmt->get_result();
$materias = [];
while($row = $result->fetch_assoc()){
    $materias[] = $row;
}

// Consulta de calificaciones
// Consulta de promedios de entregas por materia
$stmt2 = $conexion->prepare("
    SELECT m.id_materia, m.nombre AS materia,
           COUNT(t.id_tarea) AS total_tareas,
           SUM(COALESCE(e.calificacion,0)) AS suma
    FROM materias m
    INNER JOIN asignaciones a ON m.id_materia = a.id_materia
    INNER JOIN inscripciones i ON a.id_grupo = i.id_grupo
    INNER JOIN tareas t ON m.id_materia = t.id_materia
    LEFT JOIN entregas e ON t.id_tarea = e.id_tarea AND e.id_alumno = i.id_alumno
    WHERE i.id_alumno = ?
    GROUP BY m.id_materia, m.nombre
    ORDER BY m.nombre
");
$stmt2->bind_param("i", $_SESSION['id_usuario']);
$stmt2->execute();
$result2 = $stmt2->get_result();
$calificaciones = [];
while($row = $result2->fetch_assoc()){
    $promedio = $row['total_tareas'] > 0 ? $row['suma'] / $row['total_tareas'] : null;
    $calificaciones[] = [
        'materia' => $row['materia'],
        'promedio' => $promedio
    ];
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel del Alumno</title>
    <link rel="stylesheet" href="css/admin.css">
    
</head>
<body>

<header class="topbar">
    <h2>Sistema de Gestión Escolar</h2>
    <div class="usuario">
        <span>Alumno</span>
        <a href="chats.php" class="chat-btn">💬 Chat</a>
        <a href="backend/logout.php" class="btn-salir">Cerrar sesión</a>
    </div>
</header>

<main class="contenedor">
    <h1>Bienvenido <?php echo $_SESSION['nombre']; ?> 👋</h1>

    <div class="cards">
        <?php foreach($materias as $m): ?>
            <?php
            // Total de tareas asignadas en la materia
            $stmtT = $conexion->prepare("SELECT COUNT(*) AS total FROM tareas WHERE id_materia = ?");
            $stmtT->bind_param("i", $m['id_materia']);
            $stmtT->execute();
            $total_tareas = $stmtT->get_result()->fetch_assoc()['total'];

            // Total de entregas y suma de calificaciones del alumno en esa materia
            $stmtE = $conexion->prepare("
                SELECT COUNT(e.id_entrega) AS entregadas, SUM(COALESCE(e.calificacion,0)) AS suma
                FROM tareas t
                LEFT JOIN entregas e ON t.id_tarea = e.id_tarea AND e.id_alumno = ?
                WHERE t.id_materia = ?
            ");
            $stmtE->bind_param("ii", $_SESSION['id_usuario'], $m['id_materia']);
            $stmtE->execute();
            $datos = $stmtE->get_result()->fetch_assoc();

            $entregadas = $datos['entregadas'];
            $faltantes = $total_tareas - $entregadas;
            $promedio = $total_tareas > 0 ? $datos['suma'] / $total_tareas : null;
            ?>
            
            <div class="card">
                <div class="icono">📚</div>
                <h2><?php echo $m['nombre']; ?></h2>
                <p><strong>Promedio:</strong> <?php echo $promedio !== null ? number_format($promedio,2) : "Sin tareas"; ?></p>
                <p><strong>Entregadas:</strong> <?php echo $entregadas; ?> / <?php echo $total_tareas; ?></p>
                <p><strong>Faltantes:</strong> <?php echo $faltantes; ?></p>
                <button onclick="location.href='materia.php?id=<?php echo $m['id_materia']; ?>'">Ver Tareas</button>
            </div>
        <?php endforeach; ?>
    </div>
</main>





<!-- Modal Materias -->
<div id="modalMaterias" class="modal">
    <div class="modal-contenido">
        <div class="modal-header">
            <h2>Mis Materias</h2>
            <span class="cerrar">&times;</span>
        </div>
        <ul>
            <?php foreach($materias as $m): ?>
                <li>
                    <a href="materia.php?id=<?php echo $m['id_materia']; ?>">
                        <?php echo $m['nombre']; ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</div>

<!-- Modal Calificaciones -->
<div id="modalCalificaciones" class="modal">
    <div class="modal-contenido">
        <div class="modal-header">
            <h2>Mis Calificaciones</h2>
            <span class="cerrar">&times;</span>
        </div>
        <table>
    <thead>
        <tr>
            <th>Materia</th>
            <th>Promedio de Entregas</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach($calificaciones as $c): ?>
            <tr>
                <td><?php echo $c['materia']; ?></td>
                <td><?php echo $c['promedio'] !== null ? number_format($c['promedio'],2) : "Sin tareas asignadas"; ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

    </div>
</div>

<script>
    // Modal Materias
    const modalMaterias = document.getElementById("modalMaterias");
    const btnMaterias = document.getElementById("btnMaterias");
    const cerrarMaterias = modalMaterias.querySelector(".cerrar");

    btnMaterias.onclick = () => modalMaterias.style.display = "flex";
    cerrarMaterias.onclick = () => modalMaterias.style.display = "none";
    window.onclick = (e) => { if(e.target == modalMaterias) modalMaterias.style.display = "none"; }

    // Modal Calificaciones
    const modalCal = document.getElementById("modalCalificaciones");
    const btnCal = document.getElementById("btnCalificaciones");
    const cerrarCal = modalCal.querySelector(".cerrar");

    btnCal.onclick = () => modalCal.style.display = "flex";
    cerrarCal.onclick = () => modalCal.style.display = "none";
    window.onclick = (e) => { if(e.target == modalCal) modalCal.style.display = "none"; }
</script>

</body>
</html>
