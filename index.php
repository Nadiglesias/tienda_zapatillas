
<?php
// Recuperar la sesión del usuario.
session_start();
// Conectar con la base de datos.
require_once __DIR__ . '/config/conexion.php';

// Consultar los productos disponibles.
$resultado = $conexion->query("SELECT * FROM productos");

if ($resultado === false) {
    die("Error al consultar los productos.");
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/css/estilos.css">
    <title>Tienda de zapatillas</title>
</head>
<body>

<header class="cabecera">
    
    <?php if (isset($_SESSION['usuario_id'])): ?>
        <p>
            Hola,
            <?php
            echo htmlspecialchars(
                $_SESSION['usuario_nombre'],
                ENT_QUOTES,
                'UTF-8'
            );
            ?>
        </p>
    <?php endif; ?>

    <a href="index.php" class="logo">SNEAKER <span>ZONE</span></a>

    <nav class="menu">
        <a href="index.php">Inicio</a>
        <a href="#catalogo">Catálogo</a>
        <a href="login.php">Iniciar sesión</a>
        <a href="carrito.php" class="enlace-carrito">🛒 Carrito</a>
    </nav>
</header>

    
<section class="bienvenida">
    <div class="contenido-bienvenida">
        <span class="subtitulo-bienvenida">ENCUENTRA TU ESTILO</span>

        <h1>Las zapatillas que marcan tu ritmo</h1>

        <p>
            Encuentra tus zapatillas favoritas para cada paso.
        </p>

        <a href="#catalogo" class="boton-catalogo">
            Explorar catálogo
        </a>
    </div>
</section>


    <h2 id="catalogo">Nuestro catálogo</h2>

    <?php if ($resultado->num_rows > 0): ?>

        <?php while ($producto = $resultado->fetch_assoc()): ?>

         
<article class="tarjeta-producto">

    <div class="contenedor-imagen">
        <img
            src="assets/img/<?php echo htmlspecialchars($producto['imagen'], ENT_QUOTES, 'UTF-8'); ?>"
            alt="<?php echo htmlspecialchars($producto['nombre'], ENT_QUOTES, 'UTF-8'); ?>"
            class="imagen-producto"
        >
    </div>

    <div class="informacion-producto">

        <span class="etiqueta-producto">NUEVO</span>

        <h3><?php echo htmlspecialchars($producto['nombre'], ENT_QUOTES, 'UTF-8'); ?></h3>

        <p>
            <?php echo htmlspecialchars($producto['descripcion'], ENT_QUOTES, 'UTF-8'); ?>
        </p>

        <p class="precio-producto">
            <?php echo number_format((float) $producto['precio'], 2, ',', '.'); ?> €
        </p>

<form method="POST" action="carrito.php">
    <input
        type="hidden"
        name="producto_id"
        value="<?php echo (int) $producto['id']; ?>"
    >

    <button type="submit" class="boton-catalogo">
        Añadir al carrito
    </button>
</form>

        <p>
            Talla: <?php echo htmlspecialchars($producto['talla'], ENT_QUOTES, 'UTF-8'); ?>
        </p>

        <p>
            Unidades disponibles: <?php echo (int) $producto['stock']; ?>
        </p>

    </div>

</article>


            <hr>

        <?php endwhile; ?>

    <?php else: ?>

        <p>Actualmente no hay zapatillas disponibles.</p>

    <?php endif; ?>

</body>
</html>

