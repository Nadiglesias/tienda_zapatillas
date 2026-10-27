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

$errores = [];
$nombre = '';
$descripcion = '';
$precio = '';
$talla = '';
$stock = '';
$imagen = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $precio = trim($_POST['precio'] ?? '');
    $talla = trim($_POST['talla'] ?? '');
    $stock = trim($_POST['stock'] ?? '');
    $imagen = trim($_POST['imagen'] ?? '');

    if ($nombre === '') {
        $errores[] = 'El nombre es obligatorio.';
    }

    if ($precio === '' || !is_numeric($precio) || (float)$precio < 0) {
        $errores[] = 'Introduce un precio válido.';
    }

    if ($talla === '' || !is_numeric($talla)) {
        $errores[] = 'Introduce una talla válida.';
    }

    if (
        $stock === '' ||
        filter_var($stock, FILTER_VALIDATE_INT) === false ||
        (int)$stock < 0
    ) {
        $errores[] = 'El stock debe ser un número entero igual o superior a cero.';
    }

    if (!$errores) {
        $sql = "INSERT INTO productos
                (nombre, descripcion, precio, talla, imagen, stock)
                VALUES (?, ?, ?, ?, ?, ?)";

        $stmt = $conexion->prepare($sql);

        if ($stmt) {
            $precioDecimal = (float)$precio;
            $tallaValor = (int)$talla;
            $stockValor = (int)$stock;

            $stmt->bind_param(
                'ssdisi',
                $nombre,
                $descripcion,
                $precioDecimal,
                $tallaValor,
                $imagen,
                $stockValor
            );

            if ($stmt->execute()) {
                $stmt->close();
                $conexion->close();

                header('Location: productos.php?mensaje=creado');
                exit;
            } else {
                $errores[] = 'No se ha podido guardar el producto.';
            }

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
    <title>Añadir producto</title>
    <link rel="stylesheet" href="../assets/css/estilos.css">
</head>
<body>

<main class="contenedor">
    <h1>Añadir producto</h1>

    <?php if ($errores): ?>
        <div class="errores">
            <?php foreach ($errores as $error): ?>
                <p><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="crear_producto.php">
        <p>
            <label for="nombre">Nombre de la zapatilla:</label><br>
            <input
                type="text"
                id="nombre"
                name="nombre"
                required
                value="<?= htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8') ?>"
            >
        </p>

        <p>
            <label for="descripcion">Descripción:</label><br>
            <textarea
                id="descripcion"
                name="descripcion"
                rows="4"
            ><?= htmlspecialchars($descripcion, ENT_QUOTES, 'UTF-8') ?></textarea>
        </p>

        <p>
            <label for="precio">Precio (€):</label><br>
            <input
                type="number"
                id="precio"
                name="precio"
                min="0"
                step="0.01"
                required
                value="<?= htmlspecialchars($precio, ENT_QUOTES, 'UTF-8') ?>"
            >
        </p>

        <p>
            <label for="talla">Talla:</label><br>
            <input
                type="number"
                id="talla"
                name="talla"
                min="1"
                step="1"
                required
                value="<?= htmlspecialchars($talla, ENT_QUOTES, 'UTF-8') ?>"
            >
        </p>

        <p>
            <label for="stock">Unidades disponibles:</label><br>
            <input
                type="number"
                id="stock"
                name="stock"
                min="0"
                step="1"
                required
                value="<?= htmlspecialchars($stock, ENT_QUOTES, 'UTF-8') ?>"
            >
        </p>

        <p>
            <label for="imagen">Nombre del archivo de imagen (opcional):</label><br>
            <input
                type="text"
                id="imagen"
                name="imagen"
                value="<?= htmlspecialchars($imagen, ENT_QUOTES, 'UTF-8') ?>"
            >
        </p>

        <p>
            <button type="submit" class="boton-catalogo">
                Guardar producto
            </button>
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

