
<?php
ini_set('display_errors', '1');
error_reporting(E_ALL);
// Recuperar la sesión del usuario.
session_start();
// Comprobar que existe un pedido recién confirmado.
if (!isset($_SESSION['ultimo_pedido'])) {
    header('Location: index.php');
    exit;
}

// Recuperar el número del pedido.
$pedidoId = (int) $_SESSION['ultimo_pedido'];

// Evitar que se vuelva a mostrar como una compra recién confirmada.
unset($_SESSION['ultimo_pedido']);

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Compra realizada - Tienda de zapatillas</title>
    <link rel="stylesheet" href="assets/css/estilos.css">
</head>
<body>

    <main class="formulario-cuenta">
        <h1>¡Compra realizada con éxito!</h1>

        <p>Tu pedido se ha guardado correctamente.</p>

        <p>
            Número de pedido:
            <strong><?php echo $pedidoId; ?></strong>
        </p>

        <p>Gracias por comprar en nuestra tienda de zapatillas.</p>

        <a href="index.php" class="boton-catalogo">
            Volver a la tienda
        </a>
    </main>

</body>
</html>
