<?php
session_start();

require_once __DIR__ . '/../config/conexion.php';

// Comprobar que el usuario es administrador
if (
    !isset($_SESSION['usuario_id']) ||
    !isset($_SESSION['usuario_rol']) ||
    $_SESSION['usuario_rol'] !== 'admin'
) {
    header('Location: ../login.php');
    exit;
}

// Obtener el ID del producto
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    header('Location: productos.php');
    exit;
}

// Buscar el producto
$stmt = $conexion->prepare(
    "SELECT nombre, descripcion, precio, talla, imagen, stock
     FROM productos
     WHERE id = ?"
);
$stmt->bind_param('i', $id);
$stmt->execute();
$resultado = $stmt->get_result();
$producto = $resultado->fetch_assoc();
$stmt->close();

if (!$producto) {
    $conexion->close();
    header('Location: productos.php');
    exit;
}

$errores = [];

$nombre = $producto['nombre'];
$descripcion = $producto['descripcion'] ?? '';
$precio = (string) $producto['precio'];
$talla = (string) $producto['talla'];
$imagen = $producto['imagen'] ?? '';
$stock = (string) $producto['stock'];

// Guardar los cambios
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $precio = trim($_POST['precio'] ?? '');
    $talla = trim($_POST['talla'] ?? '');
    $imagen = trim($_POST['imagen'] ?? '');
    $stock = trim($_POST['stock'] ?? '');

    if ($nombre === '') {
        $errores[] = 'El nombre es obligatorio.';
    }

    if ($precio === '' || !is_numeric($precio) || (float) $precio < 0) {
        $errores[] = 'Introduce un precio válido.';
    }

    if ($talla === '' || !is_numeric($talla) || (int) $talla < 1) {
        $errores[] = 'Introduce una talla válida.';
    }

    if (
        $stock === '' ||
        filter_var($stock, FILTER_VALIDATE_INT) === false ||
        (int) $stock < 0
    ) {
        $errores[] = 'El stock debe ser un número entero igual o superior a cero.';
    }

    if (!$errores) {
        $precioValor = (float) $precio;
        $tallaValor = (int) $talla;
        $stockValor = (int) $stock;

        $stmt = $conexion->prepare(
            "UPDATE productos
             SET nombre = ?, descripcion = ?, precio = ?,
                 talla = ?, imagen = ?, stock = ?
             WHERE id = ?"
        );

        if ($stmt) {
            $stmt->bind_param(
                'ssdisii',
                $nombre,
                $descripcion,
                $precioValor,
                $tallaValor,
                $imagen,
                $stockValor,
                $id
            );

            if ($stmt->execute()) {
                $stmt->close();
                $conexion->close();

                header('Location: productos.php?mensaje=editado');
                exit;
            }

            $errores[] = 'No se han podido guardar los cambios.';
            $stmt->close();
        } else {
            $errores[] = 'No se ha podido preparar la consulta.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar producto</title>
    <link rel="stylesheet" href="../assets/css/estilos.css">
</head>
<body>
<main class="contenedor">
    <h1>Editar producto</h1>

    <?php foreach ($errores as $error): ?>
        <p>
            <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
        </p>
    <?php endforeach; ?>

    <form method="POST" action="editar_producto.php?id=<?= (int) $id ?>">
        <p>
            <label for="nombre">Nombre de la zapatilla:</label><br>
            <input type="text" id="nombre" name="nombre" required
                value="<?= htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8') ?>">
        </p>

        <p>
            <label for="descripcion">Descripción:</label><br>
            <textarea id="descripcion" name="descripcion" rows="4"><?= htmlspecialchars($descripcion, ENT_QUOTES, 'UTF-8') ?></textarea>
        </p>

        <p>
            <label for="precio">Precio (€):</label><br>
            <input type="number" id="precio" name="precio" min="0" step="0.01"
                required value="<?= htmlspecialchars($precio, ENT_QUOTES, 'UTF-8') ?>">
        </p>

        <p>
            <label for="talla">Talla:</label><br>
            <input type="number" id="talla" name="talla" min="1" step="1"
                required value="<?= htmlspecialchars($talla, ENT_QUOTES, 'UTF-8') ?>">
        </p>

        <p>
            <label for="stock">Unidades disponibles:</label><br>
            <input type="number" id="stock" name="stock" min="0" step="1"
                required value="<?= htmlspecialchars($stock, ENT_QUOTES, 'UTF-8') ?>">
        </p>

        <p>
            <label for="imagen">Nombre del archivo de imagen:</label><br>
            <input type="text" id="imagen" name="imagen"
                value="<?= htmlspecialchars($imagen, ENT_QUOTES, 'UTF-8') ?>">
        </p>

        <p>
            <button type="submit" class="boton-catalogo">Guardar cambios</button>
        </p>
    </form>

    <p>
        <a href="productos.php" class="boton-catalogo">
            Volver a gestión de productos
        </a>
    </p>
</main>
</body>
</html>
<?php
$conexion->close();
?>
