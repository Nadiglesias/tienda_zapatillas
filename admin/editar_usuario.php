
<?php

// Iniciar la sesión.
session_start();

// Comprobar que solo acceda un administrador.
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

// Comprobar que se ha recibido el ID del usuario.
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    $conexion->close();
    header('Location: usuarios.php');
    exit;
}

$errores = [];

// Obtener los datos del usuario.
$consulta = $conexion->prepare(
    'SELECT nombre, email, rol FROM usuarios WHERE id = ?'
);

$consulta->bind_param('i', $id);
$consulta->execute();
$resultado = $consulta->get_result();
$usuario = $resultado->fetch_assoc();
$consulta->close();

// Si el usuario no existe, volver a la lista.
if (!$usuario) {
    $conexion->close();
    header('Location: usuarios.php');
    exit;
}

// Preparar los datos del formulario.
$nombre = $usuario['nombre'];
$email = $usuario['email'];
$rol = $usuario['rol'];

// Procesar el formulario.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nombre = trim($_POST['nombre'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $rol = $_POST['rol'] ?? '';

    if ($nombre === '') {
        $errores[] = 'El nombre es obligatorio.';
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errores[] = 'Introduce un correo electrónico válido.';
    }

    if (!in_array($rol, ['cliente', 'admin'], true)) {
        $errores[] = 'El rol seleccionado no es válido.';
    }

    // Comprobar que el correo no pertenezca a otra cuenta.
    if (empty($errores)) {

        $consulta = $conexion->prepare(
            'SELECT id FROM usuarios WHERE email = ? AND id != ?'
        );

        $consulta->bind_param('si', $email, $id);
        $consulta->execute();

        $resultado = $consulta->get_result();

        if ($resultado->num_rows > 0) {
            $errores[] = 'Ese correo ya pertenece a otro usuario.';
        }

        $consulta->close();
    }

    // Guardar los cambios.
    if (empty($errores)) {

        // Evitar que el administrador se quite su propio acceso.
        if (
            $id === (int) $_SESSION['usuario_id'] &&
            $rol !== 'admin'
        ) {
            $errores[] = 'No puedes cambiar tu propio rol a cliente.';
        }
    }

    if (empty($errores)) {

        $consulta = $conexion->prepare(
            'UPDATE usuarios
             SET nombre = ?, email = ?, rol = ?
             WHERE id = ?'
        );

        $consulta->bind_param(
            'sssi',
            $nombre,
            $email,
            $rol,
            $id
        );

        if ($consulta->execute()) {
            $consulta->close();
            $conexion->close();

            header('Location: usuarios.php');
            exit;
        }

        $errores[] = 'No se pudieron guardar los cambios.';
        $consulta->close();
    }
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar usuario</title>
    <link rel="stylesheet" href="../assets/css/estilos.css">
</head>
<body>

    <main class="formulario-cuenta">

        <h1>Editar usuario</h1>

        <?php if (!empty($errores)): ?>
            <div class="mensaje-error">
                <ul>
                    <?php foreach ($errores as $error): ?>
                        <li>
                            <?php
                            echo htmlspecialchars(
                                $error,
                                ENT_QUOTES,
                                'UTF-8'
                            );
                            ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST"
              action="editar_usuario.php?id=<?php echo (int) $id; ?>">

            <label for="nombre">Nombre:</label>
            <input
                type="text"
                id="nombre"
                name="nombre"
                value="<?php echo htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8'); ?>"
                required
            >

            <label for="email">Correo electrónico:</label>
            <input
                type="email"
                id="email"
                name="email"
                value="<?php echo htmlspecialchars($email, ENT_QUOTES, 'UTF-8'); ?>"
                required
            >

            <label for="rol">Tipo de usuario:</label>
            <select id="rol" name="rol" required>
                <option value="cliente"
                    <?php echo $rol === 'cliente' ? 'selected' : ''; ?>>
                    Cliente
                </option>

                <option value="admin"
                    <?php echo $rol === 'admin' ? 'selected' : ''; ?>>
                    Administrador
                </option>
            </select>

            <button type="submit" class="boton-catalogo">
                Guardar cambios
            </button>

        </form>

        <p>
            <a href="usuarios.php">Volver a la lista de usuarios</a>
        </p>

    </main>

</body>
</html>
