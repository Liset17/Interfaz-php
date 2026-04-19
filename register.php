<?php
// =============================================================
// register.php
// -------------------------------------------------------------
// Formulario de registro de profesor. Tras crear el usuario se
// inicia la sesion automaticamente y se redirige al panel.
// =============================================================

require __DIR__ . '/includes/auth.php';

require_guest();

$error  = '';
$nombre = '';
$email  = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre   = trim($_POST['nombre']   ?? '');
    $email    = trim($_POST['email']    ?? '');
    $password = (string)($_POST['password'] ?? '');

    if ($nombre === '' || $email === '' || $password === '') {
        $error = 'Nombre, email y contrasena son obligatorios';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Email invalido';
    } elseif (strlen($password) < 6) {
        $error = 'La contrasena debe tener al menos 6 caracteres';
    } else {
        try {
            // Evitar duplicados
            $stmt = $pdo->prepare('SELECT id FROM profesores WHERE email = ? LIMIT 1');
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $error = 'Ya existe un profesor con ese email';
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $ins  = $pdo->prepare(
                    'INSERT INTO profesores (nombre, email, password_hash, rol)
                     VALUES (?, ?, ?, "profesor")'
                );
                $ins->execute([$nombre, $email, $hash]);
                $id = (int)$pdo->lastInsertId();

                // Login automatico
                session_regenerate_id(true);
                $_SESSION['user'] = [
                    'id'     => $id,
                    'nombre' => $nombre,
                    'email'  => $email,
                    'rol'    => 'profesor',
                ];

                flash_set('ok', 'Cuenta creada correctamente. Bienvenido/a!');
                redirect(url('home.php'));
            }
        } catch (PDOException $e) {
            $error = 'Error en la base de datos: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>Crear cuenta - Academia de Teatro</title>
<link rel="stylesheet" href="<?= e(url('assets/style.css')) ?>" />
</head>
<body>
<div class="auth-wrap">
  <div class="auth-card">
    <h1>Crear cuenta</h1>
    <p class="sub">Registro de profesores</p>

    <form method="post" action="<?= e(url('register.php')) ?>">
      <label>Nombre</label>
      <input type="text" name="nombre" value="<?= e($nombre) ?>" required />

      <label>Email</label>
      <input type="email" name="email" value="<?= e($email) ?>" required autocomplete="email" />

      <label>Contrasena</label>
      <input type="password" name="password" required minlength="6" autocomplete="new-password" />

      <button type="submit" class="btn-primary">Crear cuenta</button>
    </form>

    <?php if ($error): ?>
      <p class="error"><?= e($error) ?></p>
    <?php endif; ?>

    <p class="foot">
      Ya tienes cuenta?
      <a href="<?= e(url('login.php')) ?>">Inicia sesion</a>
    </p>
  </div>
</div>
</body>
</html>
