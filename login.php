
<?php
// Iniciar o recuperar la sesión del usuario.
session_start();

// Conectar con la base de datos.
require_once __DIR__ . '/config/conexion.php';

// Preparar las variables del inicio de sesión.
$errores = [];
$email = '';

// Comprobar si se ha enviado el formulario.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Recuperar los datos introducidos.
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // Validar los datos.
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errores[] = 'Introduce un correo electrónico válido.';
    }

    if ($password === '') {
        $errores[] = 'La contraseña es obligatoria.';
    }

    // Buscar al usuario por su correo electrónico.
    if (empty($errores)) {
        $consulta = $conexion->prepare(
            'SELECT id, nombre, email, password, rol
             FROM usuarios
             WHERE email = ?'
        );

        if (!$consulta) {
            $errores[] = 'No se ha podido realizar la consulta.';
        } else {
            $consulta->bind_param('s', $email);
            $consulta->execute();

            $resultado = $consulta->get_result();
            $usuario = $resultado->fetch_assoc();

            $consulta->close();

            // Comprobar usuario y contraseña.
            if (
                !$usuario ||
                !password_verify($password, $usuario['password'])
            ) {
                $errores[] = 'El correo o la contraseña son incorrectos.';
            } else {
                // Renovar el identificador de sesión.
                session_regenerate_id(true);

                // Guardar los datos del usuario.
                $_SESSION['usuario_id'] = $usuario['id'];
                $_SESSION['usuario_nombre'] = $usuario['nombre'];
                $_SESSION['usuario_email'] = $usuario['email'];
                $_SESSION['usuario_rol'] = $usuario['rol'];

                // Redirigir según el rol.
                if ($usuario['rol'] === 'admin') {
                    header('Location: admin/index.php');
                    exit;
                } else {
                    header('Location: index.php');
                    exit;
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar sesión | SNEAKER ZONE</title>
    <link rel="stylesheet" href="assets/css/estilos.css">
</head>
<body>

    <main class="formulario-cuenta">
        <h1>¡Bienvenido de nuevo!</h1>
        <p>Inicia sesión para entrar en tu cuenta.</p>

        <?php foreach ($errores as $error): ?>
            <p class="mensaje-error">
                <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
            </p>
        <?php endforeach; ?>

        <form method="POST" action="login.php">
            <label for="email">Correo electrónico</label>
            <input
                type="email"
                id="email"
                name="email"
                value="<?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?>"
                required
            >

            <label for="password">Contraseña</label>
            <input
                type="password"
                id="password"
                name="password"
                required
            >

            <button type="submit" class="boton-catalogo">
                Iniciar sesión
            </button>
        </form>

        <p>¿Todavía no tienes cuenta? <a href="registro.php">Regístrate aquí</a></p>
        <p><a href="index.php">Volver a la tienda</a></p>
    </main>

</body>
</html>
