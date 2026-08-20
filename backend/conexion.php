<?php

$servidor = "localhost";
$usuario = "root";
$password = "";
$baseDatos = "udc";

$conexion = new mysqli(
    $servidor,
    $usuario,
    $password,
    $baseDatos,
    3307
);
$conexion->set_charset("utf8mb4");

if($conexion->connect_error){
    die("Error de conexión");
}

?>