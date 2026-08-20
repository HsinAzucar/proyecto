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

$id_materia = $_GET['id']; // materia seleccionada
$id_alumno = $_SESSION['id_usuario'];

// Obtener información de la materia
$stmt = $conexion->prepare("SELECT nombre FROM materias WHERE id_materia = ?");
$stmt->bind_param("i", $id_materia);
$stmt->execute();
$stmt->bind_result($nombreMateria);
$stmt->fetch();
$stmt->close();

// Obtener tareas de la materia
$stmt = $conexion->prepare("
    SELECT t.id_tarea, t.titulo, t.descripcion, t.fecha_publicacion, t.fecha_entrega
    FROM tareas t
    WHERE t.id_materia = ?
    ORDER BY t.fecha_publicacion DESC
");
$stmt->bind_param("i", $id_materia);
$stmt->execute();
$result = $stmt->get_result();
$tareas = [];
while($row = $result->fetch_assoc()){
    $tareas[] = $row;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel de Materia</title>
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>
<header class="topbar">
    <h2><?php echo $nombreMateria; ?></h2>
    <div class="usuario">
        <span>Alumno</span>
        <a href="alumno.php" class="btn-salir">Volver</a>
    </div>
</header>

<main class="contenedor">
    <h1>Tareas de <?php echo $nombreMateria; ?></h1>

    <?php foreach($tareas as $t): ?>
        <div class="card">
            <h2><?php echo $t['titulo']; ?></h2>
            <p><?php echo $t['descripcion']; ?></p>
            <p><strong>Publicada:</strong> <?php echo $t['fecha_publicacion']; ?></p>
            <p><strong>Entrega límite:</strong> <?php echo $t['fecha_entrega']; ?></p>

            <!-- Verificar si el alumno ya entregó -->
            <?php
            $stmt2 = $conexion->prepare("SELECT archivo, fecha_entrega, calificacion, observaciones FROM entregas WHERE id_tarea = ? AND id_alumno = ?");
            $stmt2->bind_param("ii", $t['id_tarea'], $id_alumno);
            $stmt2->execute();
            $entrega = $stmt2->get_result()->fetch_assoc();
            ?>

            <?php if($entrega): ?>
                <p>📂 Archivo entregado: <a href="uploads/<?php echo $entrega['archivo']; ?>" target="_blank"><?php echo $entrega['archivo']; ?></a></p>
                <p>📅 Fecha entrega: <?php echo $entrega['fecha_entrega']; ?></p>
                <p>✅ Calificación: <?php echo $entrega['calificacion'] ?? 'Pendiente'; ?></p>
                <p>📝 Observaciones: <?php echo $entrega['observaciones'] ?? 'Ninguna'; ?></p>
            <?php else: ?>
                <!-- Formulario para subir entrega -->
                <form action="backend/subir_entrega.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="id_tarea" value="<?php echo $t['id_tarea']; ?>">
                    <input type="file" name="archivo" accept=".pdf,.doc,.docx,.ppt,.pptx,.txt,.jpg,.png" required>

                    <button type="submit" class="btn btn-primary">Subir Entrega</button>
                </form>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
</main>
</body>
</html>
