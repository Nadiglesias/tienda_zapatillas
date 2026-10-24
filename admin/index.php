
<?php

// Iniciar la sesión del administrador.
session_start();

// Comprobar que el usuario ha iniciado sesión como administrador.
if (
    !isset($_SESSION['usuario_id']) ||
    !isset($_SESSION['usuario_rol']) ||
    $_SESSION['usuario_rol'] !== 'admin'
) {
    header('Location: ../login.php');
    exit;
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de administración</title>
    <link rel="stylesheet" href="../assets/css/estilos.css">
</head>
<body>

    <main class="formulario-cuenta">
        <h1>Panel de administración</h1>

        <p>
            Bienvenido,
            <strong>
                <?php
                echo htmlspecialchars(
                    $_SESSION['usuario_nombre'],
                    ENT_QUOTES,
                    'UTF-8'
                );
                ?>
            </strong>
        </p>

        <p>Desde aquí podrás gestionar la tienda.</p>

        <h2>Gestión de usuarios</h2>
        <p>Crear, editar y eliminar cuentas de clientes y administradores.</p>

        <h2>Gestión de productos</h2>
        <p>Crear, editar y eliminar productos del catálogo.</p>

        <p>
            <a href="../index.php" class="boton-catalogo">
                Volver a la tienda
            </a>
        </p>
    </main>

</body>
</html>
