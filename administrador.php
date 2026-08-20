<?php

session_start();
include("backend/conexion.php");


if(!isset($_SESSION['id_usuario'])){

    header("Location: ../index.php");

}

if($_SESSION['id_rol'] != 1){

    header("Location: ../index.php");

}

?>
<?php

$sql = "
SELECT 
    u.id_usuario,
    u.nombre,
    u.apellido,
    u.correo,
    g.nombre AS grupo_actual

FROM usuarios u

LEFT JOIN inscripciones i
ON u.id_usuario = i.id_alumno

LEFT JOIN grupos g
ON i.id_grupo = g.id_grupo

WHERE u.id_rol = 3

ORDER BY u.nombre
";

$resultado = $conexion->query($sql);

$sql_docentes = "
SELECT 
    u.id_usuario,
    u.nombre,
    u.apellido,
    u.correo,
    g.nombre AS grupo_actual

FROM usuarios u

LEFT JOIN asignaciones a
ON u.id_usuario = a.id_docente

LEFT JOIN grupos g
ON a.id_grupo = g.id_grupo

WHERE u.id_rol = 2

GROUP BY u.id_usuario

ORDER BY u.nombre
";


$resultado_docentes = $conexion->query($sql_docentes);

$sql_materias = "
SELECT * FROM materias
ORDER BY nombre
";

$resultado_materias = $conexion->query($sql_materias);
?>



<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel del Administrador</title>
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>

    <header class="topbar"> <h2>Sistema de Gestión Escolar</h2> <div class="usuario"> <span>Administrador</span> <a href="backend/logout.php" class="btn-salir">Cerrar sesión</a> </div> </header>
    <main class="contenedor">

        <h1>Bienvenido <?php  echo $_SESSION['nombre'];?></h1>

        <div class="cards">

            <div class="card">
                <div class="icono">👨‍🎓</div>
                <h2>Alumnos</h2>
                <p>Registrar, editar e inscribir alumnos.</p>
                <button id=btnAlumnos>Administrar</button>
            </div>

            <div class="card">
                <div class="icono">👨‍🏫</div>
                <h2>Docentes</h2>
                <p>Registrar y administrar docentes.</p>
                <button id= btnMaestros>Administrar</button>
            </div>

            <div class="card">
                <div class="icono">📚</div>
                <h2>Materias</h2>
                <p>Asignar materias y gestionar inscripciones.</p>
                <button id="btnMaterias">Administrar</button>
            </div>

        </div>

    </main>


<div id="modalAlumnos" class="modal">

    <div class="modal-contenido">

        <div class="modal-header">

            <h2>Administración de Alumnos</h2>

            <span class="cerrar">&times;</span>

        </div>

        <button class="guardar" onclick="mostrarFormularioAlumno()">
    + Agregar Alumno
</button>

<div id="formAlumno" style="display:none; margin-top:20px;">
    <form action="backend/agregar_alumno.php" method="POST">
        <label>Nombre:</label><br>
        <input type="text" name="nombre" required><br><br>

        <label>Apellido:</label><br>
        <input type="text" name="apellido" required><br><br>

        <label>Correo:</label><br>
        <input type="email" name="correo" required><br><br>

        <label>Contraseña:</label><br>
        <input type="password" name="contrasena" required><br><br>

        <label>Grupo:</label><br>
        <select name="id_grupo">
            <option value="">Seleccionar grupo</option>
            <?php
            $grupos = $conexion->query("SELECT * FROM grupos");
            while($grupo = $grupos->fetch_assoc()){
                echo "<option value='".$grupo['id_grupo']."'>".$grupo['nombre']."</option>";
            }
            ?>
        </select><br><br>

        <button type="submit" class="guardar">Guardar Alumno</button>
    </form>
</div>


        <input
            type="text"
            placeholder="Buscar alumno..."
            class="buscador"
            id="buscadorAlumnos">
            
        <table>

            <thead>

                <tr>

                    <th>Alumno</th>
                    <th>Grupo</th>
                    <th>Grupo</th>
                    <th>Acción</th>

                </tr>

            </thead>

            <tbody>

<?php while($alumno = $resultado->fetch_assoc()) { ?>

<form action="backend/inscribir_alumno.php" method="POST">

<tr>
<form action="backend/inscribir_alumno.php" method="POST">
<td>
    <?php echo $alumno['nombre']." ".$alumno['apellido']; ?>
</td>

<td>

<?php 

if($alumno['grupo_actual']){

    echo $alumno['grupo_actual'];

}else{

    echo "Sin grupo";

}

?>

</td>
<td>

<select name="id_grupo">

<option value="">Seleccionar grupo</option>

<?php

$sql_grupos = "SELECT * FROM grupos";

$resultado_grupos = $conexion->query($sql_grupos);

while($grupo = $resultado_grupos->fetch_assoc()){

?>

<option value="<?php echo $grupo['id_grupo']; ?>">

<?php echo $grupo['nombre']; ?>

</option>

<?php } ?>

</select>

</td>


<td>

<input 
type="hidden" 
name="id_alumno" 
value="<?php echo $alumno['id_usuario']; ?>">


<button type="submit" class="guardar">
Guardar
</button>

</td>

</tr>

</form>

<?php } ?>

</tbody>
        </table>

    </div>

</div>

<div id="modalMaestros" class="modal">

<div class="modal-contenido">

<div class="modal-header">

<h2>Administración de Maestros</h2>



<span class="cerrarMaestros">&times;</span>

</div>
<button class="guardar" onclick="mostrarFormularioDocente()">
    + Agregar Docente
</button>

<div id="formDocente" style="display:none; margin-top:20px;">
    <form action="backend/agregar_docente.php" method="POST">
        <label>Nombre:</label><br>
        <input type="text" name="nombre" required><br><br>

        <label>Apellido:</label><br>
        <input type="text" name="apellido" required><br><br>

        <label>Correo:</label><br>
        <input type="email" name="correo" required><br><br>

        <label>Contraseña:</label><br>
        <input type="password" name="contrasena" required><br><br>

        <button type="submit" class="guardar">Guardar Docente</button>
    </form>
</div>

<br>
<table>

<thead>

<tr>

<th>Docente</th>
<th>Grupo Actual</th>
<th>Nuevo Grupo</th>
<th>Acción</th>

</tr>

</thead>


<tbody>


<?php while($docente = $resultado_docentes->fetch_assoc()){ ?>


<form action="backend/asignar_grupo_docente.php" method="POST">


<tr>


<td>

<?php echo $docente['nombre']." ".$docente['apellido']; ?>

</td>


<td>

<?php

echo $docente['grupo_actual'] 
? $docente['grupo_actual'] 
: "Sin grupo";

?>

</td>


<td>

<select name="id_grupo">

<option value="">Seleccionar grupo</option>


<?php

$grupos = $conexion->query("SELECT * FROM grupos");


while($grupo = $grupos->fetch_assoc()){

?>


<option value="<?= $grupo['id_grupo'] ?>">

<?= $grupo['nombre'] ?>

</option>


<?php } ?>


</select>


</td>


<td>

<input type="hidden" name="id_docente" value="<?= $docente['id_usuario'] ?>">


<button class="guardar">

Guardar

</button>


</td>


</tr>


</form>


<?php } ?>


</tbody>

</table>


</div>


</div>

<div id="modalMaterias" class="modal">

    <div class="modal-contenido">

        <div class="modal-header">

            <h2>Administración de Materias</h2>

            <span class="cerrarMaterias">&times;</span>

        </div>


        <button class="guardar" onclick="mostrarFormularioMateria()">
    + Agregar Materia
</button>

<div id="formMateria" style="display:none; margin-top:20px;">
    <form action="backend/agregar_materia.php" method="POST">
        <label>Nombre de la materia:</label><br>
        <input type="text" name="nombre" required><br><br>

        <label>Descripción:</label><br>
        <textarea name="descripcion" rows="3"></textarea><br><br>

        <button type="submit" class="guardar">Guardar Materia</button>
    </form>
</div>



        <br><br>


        <table>

            <thead>

                <tr>

                    <th>ID</th>
                    <th>Materia</th>
                    <th>Descripción</th>

                </tr>

            </thead>


            <tbody>


            <?php while($materia = $resultado_materias->fetch_assoc()){ ?>


                <tr>

                    <td>
                        <?php echo $materia['id_materia']; ?>
                    </td>


                    <td>
                        <?php echo $materia['nombre']; ?>
                    </td>


                    <td>
                        <?php echo $materia['descripcion']; ?>
                    </td>


                </tr>


            <?php } ?>


            </tbody>


        </table>


    </div>

</div>

<script>


    function mostrarFormularioAlumno(){
    document.getElementById("formAlumno").style.display = "block";
}


function mostrarFormularioDocente(){
    document.getElementById("formDocente").style.display = "block";
}


    function mostrarFormularioMateria(){
    document.getElementById("formMateria").style.display = "block";
}


    const modalMaterias = document.getElementById("modalMaterias");

const botonMaterias = document.getElementById("btnMaterias");

const cerrarMaterias = document.querySelector(".cerrarMaterias");


botonMaterias.onclick=function(){

    modalMaterias.style.display="flex";

}


cerrarMaterias.onclick=function(){

    modalMaterias.style.display="none";

}
    const modalMaestros = document.getElementById("modalMaestros");

const botonMaestros = document.getElementById("btnMaestros");

const cerrarMaestros = document.querySelector(".cerrarMaestros");


botonMaestros.onclick=function(){

    modalMaestros.style.display="flex";

}


cerrarMaestros.onclick=function(){

    modalMaestros.style.display="none";

}

const modal=document.getElementById("modalAlumnos");

const boton=document.getElementById("btnAlumnos");

const cerrar=document.querySelector(".cerrar");

boton.onclick=function(){

    modal.style.display="flex";

}

cerrar.onclick=function(){

    modal.style.display="none";

}

window.onclick=function(e){

    if(e.target==modal){

        modal.style.display="none";

    }

}

// Buscador de alumnos
const buscadorAlumnos = document.getElementById("buscadorAlumnos");
buscadorAlumnos.addEventListener("keyup", function() {
    let filtro = buscadorAlumnos.value.toLowerCase();
    let filas = document.querySelectorAll("#modalAlumnos tbody tr");

    filas.forEach(function(fila) {
        let texto = fila.textContent.toLowerCase();
        if (texto.includes(filtro)) {
            fila.style.display = "";
        } else {
            fila.style.display = "none";
        }
    });
});


</script>
</body>
</html>