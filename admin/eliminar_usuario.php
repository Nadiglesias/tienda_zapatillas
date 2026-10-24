
<?php
ini_set('display_errors', '1');
error_reporting(E_ALL);

// Iniciar la sesión.
session_start();

// Comprobar que solo acceda un administrador.
if (
    !isset($_SESSION['usuario_id']) ||
    !isset($_SESSION['usuario_rol']) ||
    $_SESSION['usuario_rol'] !== 'admin'
) {
    header('Location: ../login.php');
    exit;
}

// Conectar con la base de datos.
require_once __DIR__ . '/../config/conexion.php';

// Solo permitir la eliminación mediante una petición POST.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: usuarios.php');
    exit;
}

// Obtener y validar el ID del usuario.
$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    header('Location: usuarios.php');
    exit;
}

// Impedir que el administrador elimine su propia cuenta.
if ($id === (int) $_SESSION['usuario_id']) {
    $conexion->close();
    header('Location: usuarios.php?error=propia');
    exit;
}

// Comprobar si el usuario tiene pedidos asociados.
$consulta = $conexion->prepare(
    'SELECT id FROM pedidos WHERE usuario_id = ? LIMIT 1'
);

$consulta->bind_param('i', $id);
$consulta->execute();
$resultado = $consulta->get_result();
$tienePedidos = $resultado->num_rows > 0;
$consulta->close();

// No eliminar usuarios con pedidos para conservar el historial.
if ($tienePedidos) {
    $conexion->close();
    header('Location: usuarios.php?error=pedidos');
    exit;
}

// Eliminar el usuario con una consulta preparada.
$consulta = $conexion->prepare(
    'DELETE FROM usuarios WHERE id = ?'
);

$consulta->bind_param('i', $id);
$consulta->execute();

$eliminado = $consulta->affected_rows > 0;

$consulta->close();
$conexion->close();

// Volver a la lista mostrando el resultado.
if ($eliminado) {
    header('Location: usuarios.php?eliminado=1');
} else {
    header('Location: usuarios.php?error=noexiste');
}

exit;
