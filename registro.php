
<?php
require_once __DIR__ . '/config/conexion.php';

$errores = [];
$nombre = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmarPassword = $_POST['confirmar_password'] ?? '';

    if ($nombre === '') {
        $errores[] = 'El nombre es obligatorio.';
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errores[] = 'Introduce un correo electrónico válido.';
    }

    if (strlen($password) < 8) {
        $errores[] = 'La contraseña debe tener al menos 8 caracteres.';
    }

    if ($password !== $confirmarPassword) {
        $errores[] = 'Las contraseñas no coinciden.';
    }

    if (empty($errores)) {
        $consulta = $conexion->prepare(
            'SELECT id FROM usuarios WHERE email = ?'
        );
        $consulta->bind_param('s', $email);
        $consulta->execute();
        $consulta->store_result();

        if ($consulta->num_rows > 0) {
            $errores[] = 'Ya existe una cuenta con ese correo.';
        }

        $consulta->close();
    }

    if (empty($errores)) {
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $rol = 'cliente';

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
            header('Location: registro.php?exito=1');
            exit;
        } else {
            $errores[] = 'No se ha podido crear la cuenta. Inténtalo de nuevo.';
        }

        $consulta->close();
    }
}

$registroCorrecto = isset($_GET['exito']);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear cuenta | SNEAKER ZONE</title>
    <link rel="stylesheet" href="assets/css/estilos.css">
</head>
<body>

    <main class="formulario-cuenta">
        <h1>Únete a SNEAKER ZONE</h1>
        <p>Crea tu cuenta para disfrutar de nuestra tienda.</p>

        <?php if ($registroCorrecto): ?>
            <p class="mensaje-exito">
                ¡Tu cuenta se ha creado correctamente!
            </p>
        <?php endif; ?>

        <?php foreach ($errores as $error): ?>
            <p class="mensaje-error">
                <?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
            </p>
        <?php endforeach; ?>

        <?php if (!$registroCorrecto): ?>
            <form method="POST" action="registro.php">
                <label for="nombre">Nombre completo</label>
                <input
                    type="text"
                    id="nombre"
                    name="nombre"
                    value="<?php echo htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8'); ?>"
                    required
                >

                <label for="email">Correo electrónico</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    value="<?php echo htmlspecialchars($email, ENT_QUOTES, 'UTF-8'); ?>"
                    required
                >

                <label for="password">Contraseña (mínimo 8 caracteres)</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    minlength="8"
                    required
                >

                <label for="confirmar_password">Repetir contraseña</label>
                <input
                    type="password"
                    id="confirmar_password"
                    name="confirmar_password"
                    minlength="8"
                    required
                >

                <button type="submit" class="boton-catalogo">
                    Crear cuenta
                </button>
            </form>
        <?php endif; ?>

        <p><a href="index.php">Volver a la tienda</a></p>
    </main>

</body>
</html>
