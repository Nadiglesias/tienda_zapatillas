
<?php

// Iniciar la sesión del usuario.
session_start();

// Comprobar que solo un administrador puede acceder.
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

// Obtener todos los usuarios registrados.
$consulta = $conexion->prepare(
    'SELECT id, nombre, email, rol, fecha_creacion
     FROM usuarios
     ORDER BY id ASC'
);

$consulta->execute();
$resultado = $consulta->get_result();

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de usuarios</title>
    <link rel="stylesheet" href="../assets/css/estilos.css">
</head>
<body>

    <main class="formulario-cuenta">
        <h1>Gestión de usuarios</h1>

        <p>Desde aquí puedes consultar las cuentas registradas.</p>

        <p>
            <a href="index.php" class="boton-catalogo">
                Volver al panel
            </a>
        </p>
        <p><a href="crear_usuario.php" class="boton-catalogo">Añadir usuario</a></p>

        <h2>Usuarios registrados</h2>

        <?php if ($resultado->num_rows > 0): ?>

            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Email</th>
                        <th>Rol</th>
                        <th>Fecha de creación</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>

                    <?php while ($usuario = $resultado->fetch_assoc()): ?>

                        <tr>
                            <td>
                                <?php echo (int) $usuario['id']; ?>
                            </td>
                            <td>
                                <?php
                                echo htmlspecialchars(
                                    $usuario['nombre'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                );
                                ?>
                            </td>
                            <td>
                                <?php
                                echo htmlspecialchars(
                                    $usuario['email'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                );
                                ?>
                            </td>
                            <td>
                                <?php
                                echo htmlspecialchars(
                                    $usuario['rol'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                );
                                ?>
                            </td>
                            <td>
                                <?php
                                echo htmlspecialchars(
                                    $usuario['fecha_creacion'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                );
                                ?>
                            </td>
                            
<td>
    <a href="editar_usuario.php?id=<?php echo (int) $usuario['id']; ?>">
        Editar
    </a>

    <?php if ((int) $usuario['id'] !== (int) $_SESSION['usuario_id']): ?>
        <form method="POST"
              action="eliminar_usuario.php"
              onsubmit="return confirm('¿Seguro que quieres eliminar este usuario?');"
              style="display: inline;">

            <input
                type="hidden"
                name="id"
                value="<?php echo (int) $usuario['id']; ?>"
            >

            <button type="submit">Eliminar</button>
        </form>
    <?php endif; ?>
</td>

                        </tr>

                    <?php endwhile; ?>

                </tbody>
            </table>

        <?php else: ?>

            <p>No hay usuarios registrados.</p>

        <?php endif; ?>

        <?php
        $consulta->close();
        $conexion->close();
        ?>

    </main>

</body>
</html>
