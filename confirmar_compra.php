
<?php

// Iniciar o recuperar la sesión del usuario.
ini_set('display_errors', '1');
error_reporting(E_ALL);
session_start();

// Conectar con la base de datos.
require_once __DIR__ . '/config/conexion.php';

// Comprobar que el usuario ha iniciado sesión.
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

// Comprobar que la petición procede del formulario de compra.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: carrito.php');
    exit;
}

// Recuperar el carrito de la sesión.
$carrito = $_SESSION['carrito'] ?? [];

// Comprobar que el carrito contiene productos.
if (empty($carrito)) {
    header('Location: carrito.php');
    exit;
}

// Calcular el total de la compra.
$total = 0;

foreach ($carrito as $producto) {
    $cantidad = (int) $producto['cantidad'];
    $precio = (float) $producto['precio'];

    if ($cantidad < 1 || $precio < 0) {
        header('Location: carrito.php');
        exit;
    }

    $total += $precio * $cantidad;
}

// Iniciar una transacción para guardar la compra.
$conexion->begin_transaction();

try {

    // Comprobar el stock actual de cada producto.
    $consultaStock = $conexion->prepare(
        'SELECT stock FROM productos WHERE id = ? FOR UPDATE'
    );

    foreach ($carrito as $producto) {
        $productoId = (int) $producto['id'];
        $cantidad = (int) $producto['cantidad'];

        $consultaStock->bind_param('i', $productoId);
        $consultaStock->execute();

        $resultadoStock = $consultaStock->get_result();
        $filaStock = $resultadoStock->fetch_assoc();

        if (!$filaStock || (int) $filaStock['stock'] < $cantidad) {
            throw new Exception(
                'No hay suficiente stock para completar la compra.'
            );
        }
    }

    $consultaStock->close();

    // Crear el pedido.
    $usuarioId = (int) $_SESSION['usuario_id'];
    $estado = 'Confirmado';

    $consultaPedido = $conexion->prepare(
        'INSERT INTO pedidos (usuario_id, estado, total)
         VALUES (?, ?, ?)'
    );

    $consultaPedido->bind_param(
        'isd',
        $usuarioId,
        $estado,
        $total
    );

    $consultaPedido->execute();

    // Obtener el identificador del pedido creado.
    $pedidoId = $conexion->insert_id;
    $consultaPedido->close();

    // Preparar las consultas para guardar los detalles
    // y descontar las unidades compradas del stock.
    $consultaDetalle = $conexion->prepare(
        'INSERT INTO detalle_pedido
         (pedido_id, producto_id, cantidad, precio_unitario)
         VALUES (?, ?, ?, ?)'
    );

    $consultaActualizarStock = $conexion->prepare(
        'UPDATE productos
         SET stock = stock - ?
         WHERE id = ? AND stock >= ?'
    );

    foreach ($carrito as $producto) {
        $productoId = (int) $producto['id'];
        $cantidad = (int) $producto['cantidad'];
        $precio = (float) $producto['precio'];

        // Guardar el producto dentro del pedido.
        $consultaDetalle->bind_param(
            'iiid',
            $pedidoId,
            $productoId,
            $cantidad,
            $precio
        );

        $consultaDetalle->execute();

        // Descontar las unidades compradas.
        $consultaActualizarStock->bind_param(
            'iii',
            $cantidad,
            $productoId,
            $cantidad
        );

        $consultaActualizarStock->execute();

        if ($consultaActualizarStock->affected_rows !== 1) {
            throw new Exception(
                'No se pudo actualizar el stock del producto.'
            );
        }
    }

    $consultaDetalle->close();
    $consultaActualizarStock->close();

    // Confirmar todos los cambios en la base de datos.
    $conexion->commit();

    // Vaciar el carrito después de guardar correctamente el pedido.
    $_SESSION['carrito'] = [];

    // Guardar el número del pedido para mostrarlo después.
    $_SESSION['ultimo_pedido'] = $pedidoId;

    // Ir a la página de confirmación.
    header('Location: compra_exitosa.php');
    exit;

} catch (Throwable $error) {

    // Deshacer los cambios si ocurre algún problema.
    $conexion->rollback();

    // Guardar un mensaje para mostrarlo en el carrito.
    $_SESSION['error_compra'] = $error instanceof Exception
        ? $error->getMessage()
        : 'Ha ocurrido un error al procesar la compra.';

    header('Location: carrito.php');
    exit;
}

