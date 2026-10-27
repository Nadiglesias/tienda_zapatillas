<?php
ini_set('display_errors', '1');
error_reporting(E_ALL);

session_start();

require_once __DIR__ . '/../config/conexion.php';

// Comprobar que el usuario ha iniciado sesión como administrador.
if (
    !isset($_SESSION['usuario_id']) ||
    !isset($_SESSION['usuario_rol']) ||
    $_SESSION['usuario_rol'] !== 'admin'
) {
    header('Location: ../login.php');
    exit;
}


// Consultar los productos de la tienda.
$sql = "SELECT id, nombre, descripcion, precio, talla, stock
        FROM productos
        ORDER BY id DESC";

$resultado = $conexion->query($sql);

if (!$resultado) {
    die('Error al consultar los productos.');
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de productos</title>
    <link rel="stylesheet" href="../assets/css/estilos.css">
</head>
<body>

<main class="contenedor">
    <h1>Gestión de productos</h1>

    <p>Desde aquí puedes administrar los productos de la tienda.</p>

    <p>
        <a href="crear_producto.php" class="boton-catalogo">
            Añadir producto
        </a>
    </p>

    <?php if ($resultado->num_rows > 0): ?>
        <div class="tabla-contenedor">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Precio</th>
                        <th>Talla</th>
                        <th>Stock</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($producto = $resultado->fetch_assoc()): ?>
                        <tr>
                            <td><?= (int) $producto['id'] ?></td>
                            <td>
                                <?= htmlspecialchars($producto['nombre'], ENT_QUOTES, 'UTF-8') ?>
                            </td>
                            <td>
                                <?= number_format((float) $producto['precio'], 2, ',', '.') ?> €
                            </td>
                            <td>
                                <?= htmlspecialchars((string) $producto['talla'], ENT_QUOTES, 'UTF-8') ?>
                            </td>
                            <td><?= (int) $producto['stock'] ?></td>
                            <td>
                                <a href="editar_producto.php?id=<?= (int) $producto['id'] ?>">
                                    Editar
                                </a>

                                <form action="eliminar_producto.php"
                                      method="POST"
                                      style="display:inline"
                                      onsubmit="return confirm('¿Seguro que quieres eliminar este producto?');">

                                    <input type="hidden"
                                           name="id"
                                           value="<?= (int) $producto['id'] ?>">

                                    <button type="submit">Eliminar</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p>Todavía no hay productos registrados.</p>
    <?php endif; ?>

    <p>
        <a href="index.php" class="boton-catalogo">
            Volver al panel de administración
        </a>
    </p>
</main>

</body>
</html>

<?php
$resultado->free();
$conexion->close();
?>

