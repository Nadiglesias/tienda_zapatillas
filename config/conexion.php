
<?php

// Datos de conexión con la base de datos.
$servidor = "localhost";
$usuario = "root";
$password = "";
$base_datos = "tienda_zapatillas";

// Crear la conexión con MySQL.
$conexion = new mysqli(
    $servidor,
    $usuario,
    $password,
    $base_datos
);

// Comprobar si hay un error de conexión.
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

// Configurar la codificación de caracteres.
$conexion->set_charset("utf8mb4");
