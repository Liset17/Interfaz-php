<?php
// =============================================================
// login.php
// -------------------------------------------------------------
// Formulario de inicio de sesion. Procesa el POST contra la
// tabla `profesores`, usa password_verify() y guarda al profesor
// en $_SESSION['user'].
// =============================================================

require __DIR__ . '/includes/auth.php';

// Si ya esta logueado, directo al panel
require_guest();

$error = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = (string)($_POST['password'] ?? '');

    if ($email === '' || $password === '') {
        $error = 'Email y contrasena son obligatorios';
    } else {
        try {
            $stmt = $pdo->prepare(
                'SELECT id, nombre, email, password_hash, rol
                   FROM profesores
                  WHERE email = ?
                  LIMIT 1'
            );
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if (!$user || !password_verify($password, $user['password_hash'])) {
                $error = 'Credenciales incorrectas';
            } else {
                // Regeneramos el id de sesion (buena practica)
                session_regenerate_id(true);

                $_SESSION['user'] = [
                    'id'     => (int)$user['id'],
                    'nombre' => $user['nombre'],
                    'email'  => $user['email'],
                    'rol'    => $user['rol'],
                ];

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
<title>Iniciar sesion - Academia de Teatro</title>
<link rel="stylesheet" href="<?= e(url('assets/style.css')) ?>" />
</head>
<body>
<div class="auth-wrap">
  <div class="auth-card">
    <h1>Iniciar sesion</h1>
    <p class="sub">Academia de Teatro &mdash; panel de profesores</p>

    <form method="post" action="<?= e(url('login.php')) ?>">
      <label>Email</label>
      <input type="email" name="email" value="<?= e($email) ?>" required autocomplete="email" />

      <label>Contrasena</label>
      <input type="password" name="password" required autocomplete="current-password" />

      <button type="submit" class="btn-primary">Entrar</button>
    </form>

    <?php if ($error): ?>
      <p class="error"><?= e($error) ?></p>
    <?php endif; ?>

    <p class="foot">
      No tienes cuenta?
      <a href="<?= e(url('register.php')) ?>">Registrate aqui</a>
    </p>
  </div>
</div>
</body>
</html>
