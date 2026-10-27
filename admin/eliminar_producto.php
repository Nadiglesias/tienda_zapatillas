<?php
session_start();

require_once __DIR__ . '/../config/conexion.php';

// Comprobar que el usuario ha iniciado sesión como administrador
if (
    !isset($_SESSION['usuario_id']) ||
    !isset($_SESSION['usuario_rol']) ||
    $_SESSION['usuario_rol'] !== 'admin'
) {
    header('Location: ../login.php');
    exit;
}

// Solo se permite eliminar mediante POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: productos.php');
    exit;
}

// Validar el ID del producto
$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    header('Location: productos.php?error=id_invalido');
    exit;
}

// Comprobar que el producto existe
$stmt = $conexion->prepare(
    "SELECT id FROM productos WHERE id = ?"
);
$stmt->bind_param('i', $id);
$stmt->execute();
$resultado = $stmt->get_result();
$producto = $resultado->fetch_assoc();
$stmt->close();

if (!$producto) {
    $conexion->close();
    header('Location: productos.php?error=no_encontrado');
    exit;
}

// Eliminar el producto
$stmt = $conexion->prepare(
    "DELETE FROM productos WHERE id = ?"
);
$stmt->bind_param('i', $id);

if ($stmt->execute()) {
    $stmt->close();
    $conexion->close();

    header('Location: productos.php?mensaje=eliminado');
    exit;
}

$stmt->close();
$conexion->close();

// Si tiene pedidos asociados, la base de datos podría impedir eliminarlo
header('Location: productos.php?error=no_se_puede_eliminar');
exit;
