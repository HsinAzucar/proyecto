<?php
session_start();
include("backend/conexion.php");

// Verificar sesión
if(!isset($_SESSION['id_usuario'])){
    header("Location: ../index.php");
    exit();
}

// Verificar que sea maestro (id_rol = 2)
if($_SESSION['id_rol'] != 2){
    header("Location: ../index.php");
    exit();
}

// Materias que imparte el maestro
$stmt = $conexion->prepare("
    SELECT a.id_asignacion, m.id_materia, m.nombre AS materia, g.nombre AS grupo
    FROM asignaciones a
    INNER JOIN materias m ON a.id_materia = m.id_materia
    INNER JOIN grupos g ON a.id_grupo = g.id_grupo
    WHERE a.id_docente = ?
");
$stmt->bind_param("i", $_SESSION['id_usuario']);
$stmt->execute();
$result = $stmt->get_result();
$materias = [];
while($row = $result->fetch_assoc()){
    $materias[] = $row;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel del Maestro</title>
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>
<header class="topbar">
    <h2>Sistema de Gestión Escolar</h2>
    <div class="usuario">
        <span>Maestro</span>
        <a href="chats.php" class="chats-btn">💬 Chat</a>
        <a href="backend/logout.php" class="btn-salir">Cerrar sesión</a>
    </div>
</header>

<main class="contenedor">
    <h1>Bienvenido Maestro <?php echo $_SESSION['nombre']; ?> 👋</h1>
    <div class="cards">
        <?php foreach($materias as $m): ?>
        <div class="card">
            <div class="icono">📚</div>
            <h2><?php echo $m['materia']; ?> (<?php echo $m['grupo']; ?>)</h2>
            <p>Gestiona alumnos, tareas y calificaciones de esta materia.</p>
            <button onclick="abrirModal(<?php echo $m['id_asignacion']; ?>, <?php echo $m['id_materia']; ?>)">Gestionar</button>
        </div>
        <?php endforeach; ?>
    </div>
</main>

<!-- Modal -->
<div id="modalGestion" class="modal">
    <div class="modal-contenido">
        <div class="modal-header">
            <h2>Gestión de Materia</h2>
            <span class="cerrar">&times;</span>
        </div>
        <div class="tabs">
            <button onclick="mostrarSeccion('alumnos')">👨‍🎓 Alumnos</button>
            <button onclick="mostrarSeccion('tareas')">📝 Tareas</button>
            <button onclick="mostrarSeccion('calificaciones')">📊 Calificaciones</button>
            <button onclick="mostrarSeccion('entregas')">📂 Entregas</button>
        </div>
        <div id="seccion-alumnos" class="seccion">
            <h3>Alumnos inscritos</h3>
            <table>
                <thead><tr><th>Nombre</th><th>Calificacion</th></tr></thead>
                <tbody id="listaAlumnos"></tbody>
            </table>
        </div>
        <div id="seccion-tareas" class="seccion" style="display:none;">
            <h3>Tareas</h3>
            <button class="btn btn-success" onclick="nuevaTarea()">➕ Crear Tarea</button>
            <div id="listaTareas"></div>
        </div>
        <div id="seccion-calificaciones" class="seccion" style="display:none;">
            <h3>Calificaciones</h3>
            <table>
                <thead><tr>
        <th>Tarea</th>
        <th>Alumno</th>
        <th>Calificación</th>
        <th>Acción</th>
    </tr></thead>
                <tbody id="listaCalificaciones"></tbody>
            </table>
        </div>
        <div id="seccion-entregas" class="seccion" style="display:none;">
            <h3>Entregas de Tareas</h3>
            <div id="listaEntregas"></div>
        </div>
    </div>
</div>

<script>
    const modal = document.getElementById("modalGestion");
    const cerrar = modal.querySelector(".cerrar");
    let asignacionActual = null;
    let materiaActual = null;

    function abrirModal(idAsignacion, idMateria){
        asignacionActual = idAsignacion;
        materiaActual = idMateria;
        modal.style.display = "flex";
        mostrarSeccion('alumnos');
        cargarAlumnos(idAsignacion);
        cargarTareas(idMateria);
        cargarCalificaciones(idMateria);
        cargarEntregas(idMateria);
    }
    cerrar.onclick = () => modal.style.display = "none";
    window.onclick = (e) => { if(e.target == modal) modal.style.display = "none"; }

    function mostrarSeccion(seccion){
        document.querySelectorAll(".seccion").forEach(s => s.style.display="none");
        document.getElementById("seccion-"+seccion).style.display="block";
    }

    function cargarAlumnos(id){
        fetch("backend/get_alumnos.php?id_asignacion="+id)
        .then(r=>r.text()).then(html=>{ document.getElementById("listaAlumnos").innerHTML = html; });
    }
    function cargarTareas(idMateria){
        fetch("backend/get_tareas.php?id_materia="+idMateria)
        .then(r=>r.text()).then(html=>{ document.getElementById("listaTareas").innerHTML = html; });
    }
    function cargarCalificaciones(idMateria){
        fetch("backend/get_calificaciones.php?id_materia="+idMateria)
        .then(r=>r.text()).then(html=>{ document.getElementById("listaCalificaciones").innerHTML = html; });
    }
    function cargarEntregas(idMateria){
        fetch("backend/get_entregas.php?id_materia="+idMateria)
        .then(r=>r.text()).then(html=>{ document.getElementById("listaEntregas").innerHTML = html; });
    }
    function nuevaTarea(){
        let titulo = prompt("Título de la tarea:");
        let descripcion = prompt("Descripción:");
        let fechaEntrega = prompt("Fecha de entrega (YYYY-MM-DD):");
        if(titulo && fechaEntrega){
            fetch("backend/crear_tarea.php?id_docente=<?php echo $_SESSION['id_usuario']; ?>&id_materia="+materiaActual+"&titulo="+encodeURIComponent(titulo)+"&descripcion="+encodeURIComponent(descripcion)+"&fecha_entrega="+fechaEntrega)
            .then(()=>cargarTareas(materiaActual));
        }
    }
    
    // Editar calificación de un alumno
    function editarCalificacion(idCalificacion){
        let nuevaNota = prompt("Nueva calificación:");
        if(nuevaNota){
            fetch("backend/guardar_calificacion.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: "id_calificacion="+idCalificacion+"&calificacion="+encodeURIComponent(nuevaNota)
            })
            .then(r=>r.text())
            .then(res=>{
                if(res === "ok"){
                    alert("Calificación actualizada ✅");
                    cargarCalificaciones(materiaActual);
                } else {
                    alert("Error al actualizar ❌");
                }
            });
        }
    }

    // Calificar entrega de tarea
    function calificarEntrega(idEntrega){
        let nota = prompt("Calificación para esta entrega:");
        let obs = prompt("Observaciones:");
        if(nota){
            fetch("backend/calificar_entrega.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: "id_entrega="+idEntrega+"&calificacion="+encodeURIComponent(nota)+"&observaciones="+encodeURIComponent(obs)
            })
            .then(r=>r.text())
            .then(res=>{
                if(res === "ok"){
                    alert("Entrega calificada ✅");
                    cargarEntregas(materiaActual);
                } else {
                    alert("Error al calificar ❌");
                }
            });
        }
    }


</script>
</body>
</html>
