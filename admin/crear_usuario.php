
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

// Preparar las variables del formulario.
$errores = [];
$nombre = '';
$email = '';
$rol = 'cliente';

// Procesar el formulario cuando se envíe.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nombre = trim($_POST['nombre'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $rol = $_POST['rol'] ?? 'cliente';

    // Validar los datos recibidos.
    if ($nombre === '') {
        $errores[] = 'El nombre es obligatorio.';
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errores[] = 'Introduce un correo electrónico válido.';
    }

    if (strlen($password) < 8) {
        $errores[] = 'La contraseña debe tener al menos 8 caracteres.';
    }

    if (!in_array($rol, ['cliente', 'admin'], true)) {
        $errores[] = 'El rol seleccionado no es válido.';
    }

    // Comprobar que el correo no esté registrado.
    if (empty($errores)) {
        $consulta = $conexion->prepare(
            'SELECT id FROM usuarios WHERE email = ?'
        );

        $consulta->bind_param('s', $email);
        $consulta->execute();

        $resultado = $consulta->get_result();

        if ($resultado->num_rows > 0) {
            $errores[] = 'Ya existe una cuenta con ese correo.';
        }

        $consulta->close();
    }

    // Guardar el usuario si todos los datos son correctos.
    if (empty($errores)) {

        $passwordHash = password_hash(
            $password,
            PASSWORD_DEFAULT
        );

        $consulta = $conexion->prepare(
            'INSERT INTO usuarios (nombre, email, password, rol)
             VALUES (?, ?, ?, ?)'
        );

        $consulta->bind_param(
            'ssss',
            $nombre,
            $email,
            $passwordHash,
            $rol
        );

        if ($consulta->execute()) {
            $consulta->close();
            $conexion->close();

            header('Location: usuarios.php');
            exit;
        } else {
            $errores[] = 'No se pudo crear el usuario.';
        }

        $consulta->close();
    }
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear usuario</title>
    <link rel="stylesheet" href="../assets/css/estilos.css">
</head>
<body>

    <main class="formulario-cuenta">
        <h1>Crear usuario</h1>

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

        <form method="POST" action="crear_usuario.php">

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

            <label for="password">Contraseña:</label>
            <input
                type="password"
                id="password"
                name="password"
                minlength="8"
                required
            >

            <label for="rol">Tipo de usuario:</label>
            <select id="rol" name="rol" required>
                <option value="cliente" <?php echo $rol === 'cliente' ? 'selected' : ''; ?>>
                    Cliente
                </option>
                <option value="admin" <?php echo $rol === 'admin' ? 'selected' : ''; ?>>
                    Administrador
                </option>
            </select>

            <button type="submit" class="boton-catalogo">
                Crear usuario
            </button>

        </form>

        <p>
            <a href="usuarios.php">Volver a la lista de usuarios</a>
        </p>

    </main>

</body>
</html>
