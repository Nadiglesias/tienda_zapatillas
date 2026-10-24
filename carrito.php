
<?php

// Iniciar o recuperar la sesión del usuario.
session_start();

// Conectar con la base de datos.
require_once __DIR__ . '/config/conexion.php';

// Crear el carrito si todavía no existe.
if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}

// Comprobar si se ha enviado un producto desde el catálogo.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $productoId = filter_input(
        INPUT_POST,
        'producto_id',
        FILTER_VALIDATE_INT
    );

    if ($productoId !== false && $productoId !== null && $productoId > 0) {
        // Buscar el producto en la base de datos.
        $consulta = $conexion->prepare(
            'SELECT id, nombre, precio, imagen, stock
             FROM productos
             WHERE id = ?'
        );

        $consulta->bind_param('i', $productoId);
        $consulta->execute();

        $resultado = $consulta->get_result();
        $producto = $resultado->fetch_assoc();

        $consulta->close();

        // Comprobar que el producto existe y tiene stock.
        if ($producto && (int) $producto['stock'] > 0) {
            $cantidadActual = $_SESSION['carrito'][$productoId]['cantidad'] ?? 0;

            if ($cantidadActual < (int) $producto['stock']) {
                if (isset($_SESSION['carrito'][$productoId])) {
                    $_SESSION['carrito'][$productoId]['cantidad']++;
                } else {
                    $_SESSION['carrito'][$productoId] = [
                        'id' => (int) $producto['id'],
                        'nombre' => $producto['nombre'],
                        'precio' => (float) $producto['precio'],
                        'imagen' => $producto['imagen'],
                        'cantidad' => 1
                    ];
                }
            }
        }
    }

    // Volver al carrito después de procesar el formulario.
    header('Location: carrito.php');
    exit;
}

// Recuperar los productos guardados en el carrito.
$carrito = $_SESSION['carrito'];

?>



<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi carrito | SNEAKER ZONE</title>
    <link rel="stylesheet" href="assets/css/estilos.css">
</head>
<body>

    <main class="formulario-cuenta">
        <h1>Mi carrito</h1>
        <p>Aquí encontrarás las zapatillas que quieras comprar.</p>

        
<?php if (empty($carrito)): ?>

    <p>Tu carrito está vacío.</p>

<?php else: ?>

    <?php
    $total = 0;
    ?>

    <?php foreach ($carrito as $producto): ?>

        <?php
        $subtotal = $producto['precio'] * $producto['cantidad'];
        $total += $subtotal;
        ?>

        <article class="tarjeta-producto">
            <div class="informacion-producto">

                <h3>
                    <?php
                    echo htmlspecialchars(
                        $producto['nombre'],
                        ENT_QUOTES,
                        'UTF-8'
                    );
                    ?>
                </h3>

                <p>
                    Precio unitario:
                    <?php echo number_format($producto['precio'], 2, ',', '.'); ?> €
                </p>

                <p>
                    Cantidad: <?php echo (int) $producto['cantidad']; ?>
                </p>

                <p>
                    Subtotal:
                    <?php echo number_format($subtotal, 2, ',', '.'); ?> €
                </p>

            </div>
        </article>

    <?php endforeach; ?>

    <h2>
        Total:
        <?php echo number_format($total, 2, ',', '.'); ?> €
    </h2>

<form method="POST" action="confirmar_compra.php">
    <button type="submit" class="boton-catalogo">
        Confirmar compra
    </button>
</form>

<?php endif; ?>


        <p>
            <a href="index.php">Volver a la tienda</a>
        </p>
    </main>

</body>
</html>
